<?php
require 'dbconfig.php';

$allowedCourses = [
    "Bachelor of Science in Computer Science",
    "Bachelor of Science in Information Technology",
    "Bachelor of Science in Information Communications and Technology Management",
    "Bachelor of Science in Computer Technology",
    "Bachelor of Science in Information Systems",
    "Bachelor of Science in Diploma in IT",
    "Diploma in Information Technology",
    "Diploma in Information and Communication Technology (DICT)"
];

// Sanitize and validate inputs
$year   = $_POST['year'] ?? '';
$course = $_POST['course'] ?? '';
$title  = trim($_POST['title'] ?? '');
$code   = strtoupper(trim($_POST['code'] ?? ''));
$images = $_POST['images'] ?? [];

if (!preg_match('/^[A-Za-z0-9]{6}$/', $code)) {
    die("❌ Error: Code must be exactly 6 alphanumeric characters.");
}
if (!in_array($course, $allowedCourses)) {
    die("❌ Error: Invalid course selected.");
}
if (!in_array($year, ['1', '2', '3', '4'])) {
    die("❌ Error: Invalid year.");
}
if (empty($images)) {
    die("❌ Error: Please take at least one photo.");
}

// Create upload folder
$folderName = date("Y_m_d_His") . "_" . $code;
$uploadDir = "uploads/$folderName";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Insert into past_papers
    $stmt = $pdo->prepare("INSERT INTO past_papers (year, course, title, code, folder_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$year, $course, $title, $code, $uploadDir]);
    $paperId = $pdo->lastInsertId();

    // Insert each image into paper_images
    $imgStmt = $pdo->prepare("INSERT INTO paper_images (paper_id, filename) VALUES (?, ?)");

    foreach ($images as $index => $dataUrl) {
        if (preg_match('/^data:image\/png;base64,/', $dataUrl)) {
            $data = base64_decode(str_replace('data:image/png;base64,', '', $dataUrl));
            $filename = "paper_" . ($index + 1) . ".png";
            $filePath = "$uploadDir/$filename";
            if (file_put_contents($filePath, $data)) {
                $imgStmt->execute([$paperId, $filename]);
            }
        }
    }

    $pdo->commit();

    echo "<script>alert('✅ Upload successful!'); window.location.href='view_papers.php';</script>";
} catch (PDOException $e) {
    $pdo->rollBack();
    die("❌ Upload failed: " . $e->getMessage());
}
?>
