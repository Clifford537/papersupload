<?php
require 'dbconfig.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Past Papers</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <style>
    .page-title {
      font-size: 1.5rem;
      margin-bottom: 1rem;
      text-align: center;
    }

    .paper-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 1rem;
    }

    .paper-card {
      background: #fff;
      padding: 1rem;
      border-radius: 8px;
      box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .paper-card h3 {
      font-size: 1.1rem;
      margin-bottom: 0.5rem;
      color: #333;
    }

    .paper-card p {
      margin: 0.3rem 0;
      font-size: 0.95rem;
      color: #555;
    }

    .paper-card a {
      margin-top: 0.5rem;
      align-self: flex-start;
      padding: 0.4rem 0.8rem;
      background-color: #007BFF;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      font-size: 0.9rem;
      transition: background-color 0.2s ease-in-out;
    }

    .paper-card a:hover {
      background-color: #0056b3;
    }

    @media screen and (max-width: 480px) {
      .paper-card {
        padding: 0.75rem;
      }

      .paper-card h3 {
        font-size: 1rem;
      }

      .paper-card p {
        font-size: 0.85rem;
      }

      .paper-card a {
        font-size: 0.85rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2 class="page-title">📚 All Past Papers</h2>

    <div class="paper-list">
      <?php
      $stmt = $pdo->query("SELECT * FROM past_papers ORDER BY created_at DESC");
      while ($paper = $stmt->fetch(PDO::FETCH_ASSOC)) {
          echo "<div class='paper-card'>
                  <h3>" . htmlspecialchars($paper['title']) . "</h3>
                  <p><strong>Year:</strong> " . htmlspecialchars($paper['year']) . "</p>
                  <p><strong>Course:</strong> " . htmlspecialchars($paper['course']) . "</p>
                  <p><strong>Code:</strong> " . htmlspecialchars($paper['code']) . "</p>
                  <a href='view_images.php?id={$paper['id']}'>View Images</a>
                </div>";
      }
      ?>
    </div>
  </div>
</body>
</html>
