<?php
require_once 'dbconfig.php';

$paper_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($paper_id <= 0) {
    die("Invalid paper ID.");
}

try {
    // Fetch paper details
    $stmt = $pdo->prepare("
        SELECT title, year, course, code, folder_path
        FROM past_papers
        WHERE id = ?
    ");
    $stmt->execute([$paper_id]);
    $paper = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paper) {
        die("Paper not found.");
    }

    // Fetch images for the paper
    $img_stmt = $pdo->prepare("
        SELECT filename
        FROM paper_images
        WHERE paper_id = ?
    ");
    $img_stmt->execute([$paper_id]);
    $images = $img_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Error fetching paper: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Past Paper - <?php echo htmlspecialchars($paper['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to bottom, #f3f4f6 0%, #e5e7eb 100%);
            transition: background-color 0.3s ease;
        }
        .page-container {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e5e7eb;
        }
        .page-container:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .back-button {
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        .back-button:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
        }
        .skeleton {
            animation: pulse 1.5s infinite ease-in-out;
        }
        @keyframes pulse {
            0% { background-color: #e5e7eb; }
            50% { background-color: #d1d5db; }
            100% { background-color: #e5e7eb; }
        }
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(to bottom, #1f2937 0%, #111827 100%);
            }
            .page-container {
                background: #1f2937;
                border-color: #374151;
            }
            .details-card {
                background: #1f2937;
            }
            .no-images {
                background: #1f2937;
            }
            .header {
                background: linear-gradient(to right, #1e40af, #3b82f6);
            }
            .text-gray-700 {
                color: #d1d5db;
            }
            .text-gray-500 {
                color: #9ca3af;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-5xl flex-1">
        <!-- Header Section -->
        <header class="header bg-gradient-to-r from-blue-700 to-indigo-600 text-white rounded-2xl shadow-lg p-8 mb-10 text-center">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight">
                <?php echo htmlspecialchars($paper['title']); ?>
            </h1>
            <p class="mt-2 text-sm sm:text-base text-blue-100">Past Paper Details</p>
        </header>

        <!-- Paper Details Section -->
        <div class="details-card bg-white dark:bg-gray-800 rounded-2xl shadow-md p-6 sm:p-8 mb-10">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <p class="text-gray-700 dark:text-gray-300 text-sm sm:text-base">
                    <strong>Year:</strong> <?php echo htmlspecialchars($paper['year']); ?>
                </p>
                <p class="text-gray-700 dark:text-gray-300 text-sm sm:text-base">
                    <strong>Code:</strong> <?php echo htmlspecialchars($paper['code']); ?>
                </p>
                <p class="text-gray-700 dark:text-gray-300 text-sm sm:text-base col-span-1 sm:col-span-2">
                    <strong>Course:</strong> <?php echo htmlspecialchars($paper['course']); ?>
                </p>
            </div>
            <a href="list_papers.php" 
               class="back-button inline-block mt-6 bg-blue-600 text-white font-medium py-2 px-6 rounded-lg shadow-md"
               aria-label="Return to past papers list">
               Back to List
            </a>
        </div>

        <!-- Images Section -->
        <?php if (!empty($images)): ?>
            <div class="space-y-8" id="images-container">
                <?php foreach ($images as $index => $image): ?>
                    <div class="page-container bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 sm:p-6 mx-auto" 
                         style="aspect-ratio: 1/1.414; max-width: 800px; width: 100%;">
                        <img src="<?php echo htmlspecialchars($paper['folder_path'] . '/' . $image); ?>" 
                             alt="<?php echo htmlspecialchars($paper['title'] . ' Page ' . ($index + 1)); ?>"
                             class="w-full h-full object-contain rounded-md"
                             loading="lazy">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-images bg-white dark:bg-gray-800 rounded-2xl shadow-md p-6 text-center">
                <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base italic">No images available for this paper.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-100 dark:bg-gray-900 py-4 text-center text-gray-600 dark:text-gray-400 text-sm">
        <p>© <?php echo date('Y'); ?> Past Papers Archive. All rights reserved.</p>
    </footer>

    <!-- Skeleton Loader Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.querySelector('#images-container');
            if (container) {
                // Simulate loading state
                container.innerHTML = `
                    <div class="page-container bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 sm:p-6 mx-auto skeleton" style="aspect-ratio: 1/1.414; max-width: 800px; width: 100%; height: 1131px;"></div>
                    <div class="page-container bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 sm:p-6 mx-auto skeleton" style="aspect-ratio: 1/1.414; max-width: 800px; width: 100%; height: 1131px;"></div>
                `;
                setTimeout(() => {
                    container.innerHTML = <?php echo json_encode($images); ?>.map((image, index) => `
                        <div class="page-container bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 sm:p-6 mx-auto" style="aspect-ratio: 1/1.414; max-width: 800px; width: 100%;">
                            <img src="<?php echo htmlspecialchars($paper['folder_path']); ?>/${image}" 
                                 alt="<?php echo htmlspecialchars($paper['title']); ?> Page ${index + 1}"
                                 class="w-full h-full object-contain rounded-md"
                                 loading="lazy">
                        </div>
                    `).join('');
                }, 1000); // Simulate 1s loading
            }
        });
    </script>
</body>
</html>