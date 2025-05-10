<?php
require 'dbconfig.php';

if (!isset($_GET['id'])) {
    die("Missing paper ID.");
}

$paperId = (int) $_GET['id'];

// Fetch paper info
$paper = $pdo->prepare("SELECT * FROM past_papers WHERE id = ?");
$paper->execute([$paperId]);
$paperData = $paper->fetch(PDO::FETCH_ASSOC);

if (!$paperData) {
    die("Paper not found.");
}

// Fetch images
$images = $pdo->prepare("SELECT filename FROM paper_images WHERE paper_id = ?");
$images->execute([$paperId]);
$imageFiles = $images->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Past Paper - <?= htmlspecialchars($paperData['title']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <style>
    .paper-details {
      background: white;
      padding: 1rem;
      border-radius: 8px;
      box-shadow: 0 0 6px rgba(0,0,0,0.1);
      margin-bottom: 1rem;
    }

    .gallery {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      gap: 1rem;
    }

    .gallery img {
      width: 100%;
      border-radius: 6px;
      border: 1px solid #ccc;
      background: #f8f8f8;
      padding: 4px;
      transition: transform 0.2s ease-in-out;
    }

    .gallery img:hover {
      transform: scale(1.03);
    }

    .back-link {
      display: inline-block;
      margin-bottom: 1rem;
      color: #007BFF;
      text-decoration: none;
      font-weight: bold;
    }

    .back-link:hover {
      text-decoration: underline;
    }

    @media screen and (max-width: 600px) {
      h2 {
        font-size: 1.2rem;
      }

      .paper-details p {
        font-size: 0.9rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <a class="back-link" href="view_papers.php">← Back to list</a>

    <div class="paper-details">
      <h2>📄 <?= htmlspecialchars($paperData['title']) ?> (Year <?= htmlspecialchars($paperData['year']) ?>)</h2>
      <p><strong>Course:</strong> <?= htmlspecialchars($paperData['course']) ?></p>
      <p><strong>Code:</strong> <?= htmlspecialchars($paperData['code']) ?></p>
    </div>

    <div class="gallery">
      <?php
        $folder = $paperData['folder_path'];
        foreach ($imageFiles as $img) {
            $path = $folder . '/' . $img;
            echo "<img src='$path' alt='Page'>";
        }
      ?>
    </div>
  </div>
</body>
</html>
