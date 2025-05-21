<?php
require 'dbconfig.php';

// Set JSON header
header('Content-Type: application/json');

// Allowed courses for validation
$allowedCourses = [
    "Bachelor of Science in Computer Science",
    "Bachelor of Science in Information Technology",
    "Bachelor of Science in Information Communications and Technology Management",
    "Bachelor of Science in Computer Technology",
    "Bachelor of Science in Information Systems",
    "Bachelor of Science in Diploma in IT",
    "Diploma in Information Technology",
    "Bachelor of Science in ICT Management",
    "Diploma in Information and Communication Technology (DICT)"
];

// Sanitize and validate inputs
$year = $_POST['year'] ?? '';
$course = $_POST['course'] ?? '';
$title = trim($_POST['title'] ?? '');
$code = strtoupper(trim($_POST['code'] ?? ''));
$images = $_POST['images'] ?? [];

try {
    // Validate inputs
    if (!preg_match('/^[A-Za-z0-9]{6}$/', $code)) {
        throw new Exception("Code must be exactly 6 alphanumeric characters.");
    }
    if (!in_array($course, $allowedCourses)) {
        throw new Exception("Invalid course selected.");
    }
    if (!in_array($year, ['1', '2', '3', '4'])) {
        throw new Exception("Invalid year.");
    }
    if (empty($images)) {
        throw new Exception("Please take at least one photo.");
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Check for duplicate code
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM past_papers WHERE UPPER(code) = ?");
    $stmt->execute([strtoupper($code)]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("A past paper with the code '$code' already exists.");
    }

    // Check for duplicate title
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM past_papers WHERE UPPER(title) = ?");
    $stmt->execute([strtoupper($title)]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("A past paper with the title '$title' already exists.");
    }

    // Create upload folder
    $folderName = date("Y_m_d_His") . "_" . $code;
    $uploadDir = "Uploads/$folderName";
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception("Failed to create upload directory.");
        }
    }

    // Set permissions (optional, ensure server can write)
    chmod($uploadDir, 0777);

    // Insert into past_papers
    $stmt = $pdo->prepare("INSERT INTO past_papers (year, course, title, code, folder_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$year, $course, $title, $code, $uploadDir]);
    $paperId = $pdo->lastInsertId();

    // Insert each image into paper_images
    $imgStmt = $pdo->prepare("INSERT INTO paper_images (paper_id, filename) VALUES (?, ?)");

    foreach ($images as $index => $dataUrl) {
        // Handle both JPEG and PNG formats
        if (preg_match('/^data:image\/(jpeg|png);base64,/', $dataUrl, $matches)) {
            $imageType = $matches[1]; // jpeg or png
            $data = str_replace("data:image/$imageType;base64,", '', $dataUrl);
            $data = str_replace(' ', '+', $data); // Fix padding issues
            $decodedData = base64_decode($data, true);

            if ($decodedData === false) {
                throw new Exception("Invalid base64 data for image " . ($index + 1));
            }

            $filename = "paper_" . ($index + 1) . "." . $imageType;
            $filePath = "$uploadDir/$filename";

            if (!file_put_contents($filePath, $decodedData)) {
                throw new Exception("Failed to save image $filename to disk.");
            }

            // Insert into paper_images
            $imgStmt->execute([$paperId, $filename]);
        } else {
            throw new Exception("Invalid image format for image " . ($index + 1) . ". Expected JPEG or PNG.");
        }
    }

    $pdo->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Upload successful!'
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    // Log the error for debugging (optional, ensure log file is writable)
    error_log("Upload error: " . $e->getMessage(), 3, "upload_errors.log");
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Upload failed: ' . $e->getMessage()
    ]);
    exit;
}
?>