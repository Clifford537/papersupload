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

// Get and sanitize inputs
$year   = $_POST['year'] ?? '';
$course = $_POST['course'] ?? '';
$title  = $_POST['title'] ?? '';
$code   = strtoupper(trim($_POST['code'] ?? ''));
$images = $_POST['images'] ?? [];

// Server-side validation
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

// Create folder
$uploadDir = "uploads/" . date("Y_m_d_His") . "_" . $code;
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Save images
$savedPaths = [];
foreach ($images as $index => $dataUrl) {
    if (preg_match('/^data:image\/png;base64,/', $dataUrl)) {
        $data = base64_decode(str_replace('data:image/png;base64,', '', $dataUrl));
        $filePath = "$uploadDir/paper_" . ($index + 1) . ".png";
        file_put_contents($filePath, $data);
        $savedPaths[] = $filePath;
    }
}

// Insert record into DB
$stmt = $conn->prepare("INSERT INTO pastpapers (year, course, title, code, image_paths, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())");
$imagePathsJson = json_encode($savedPaths);
$stmt->bind_param("sssss", $year, $course, $title, $code, $imagePathsJson);

if ($stmt->execute()) {
    echo "<script>alert('✅ Upload successful!'); window.location.href='view_papers.php';</script>";
} else {
    echo "❌ Error saving to database: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
