<?php
require_once 'dbconfig.php';

// Fetch distinct years and courses for filter dropdowns
try {
    $yearStmt = $pdo->query("SELECT DISTINCT year FROM past_papers ORDER BY year");
    $years = $yearStmt->fetchAll(PDO::FETCH_COLUMN);

    $courseStmt = $pdo->query("SELECT DISTINCT course FROM past_papers ORDER BY course");
    $courses = $courseStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Error fetching filters: " . $e->getMessage());
}

// Handle filtering and search
$yearFilter = isset($_GET['year']) ? $_GET['year'] : '';
$courseFilter = isset($_GET['course']) ? $_GET['course'] : '';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

$conditions = [];
$params = [];

if ($yearFilter !== '') {
    $conditions[] = "year = ?";
    $params[] = $yearFilter;
}
if ($courseFilter !== '') {
    $conditions[] = "course = ?";
    $params[] = $courseFilter;
}
if ($searchQuery !== '') {
    $conditions[] = "(title LIKE ? OR code LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

try {
    // Fetch papers with filters, sorted by most recent (created_at or id)
    $query = "SELECT id, year, course, title, code, folder_path 
              FROM past_papers 
              $whereClause 
              ORDER BY created_at DESC, id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $papers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching papers: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Past Papers List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to bottom, #f3f4f6 0%, #e5e7eb 100%);
        }
        .filter-container {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
        }
        .paper-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .paper-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        .search-input {
            transition: border-color 0.2s;
        }
        .search-input:focus {
            border-color: #2563eb;
            outline: none;
        }
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(to bottom, #1f2937 0%, #111827 100%);
            }
            .filter-container {
                background: #1f2937;
                border-color: #374151;
            }
            .paper-card {
                background: #1f2937;
            }
            .search-input {
                background: #374151;
                color: #d1d5db;
                border-color: #4b5563;
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
        <!-- Header -->
        <header class="bg-gradient-to-r from-blue-700 to-indigo-600 text-white rounded-2xl shadow-lg p-8 mb-10 text-center">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight">Past Papers Archive</h1>
            <p class="mt-2 text-sm sm:text-base text-blue-100">Browse and filter past papers</p>
        </header>

        <!-- Filter and Search -->
        <div class="filter-container mb-8">
            <form method="GET" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year</label>
                    <select name="year" id="year" class="mt-1 block w-full p-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                        <option value="">All Years</option>
                        <?php foreach ($years as $y): ?>
                            <option value="<?php echo htmlspecialchars($y); ?>" <?php echo $yearFilter === $y ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($y); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-1">
                    <label for="course" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course</label>
                    <select name="course" id="course" class="mt-1 block w-full p-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?php echo htmlspecialchars($c); ?>" <?php echo $courseFilter === $c ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                    <div class="relative">
                        <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($searchQuery); ?>" 
                               placeholder="Search by title or code" 
                               class="search-input mt-1 block w-full p-2 pl-10 border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300" />
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 text-white p-2 rounded-md hover:bg-blue-700">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Papers List -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (!empty($papers)): ?>
                <?php foreach ($papers as $paper): ?>
                    <a href="view_paper.php?id=<?php echo $paper['id']; ?>" class="paper-card bg-white dark:bg-gray-800 rounded-2xl shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <?php echo htmlspecialchars($paper['title']); ?>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">
                            <strong>Code:</strong> <?php echo htmlspecialchars($paper['code']); ?>
                        </p>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                            <strong>Year:</strong> <?php echo htmlspecialchars($paper['year']); ?>
                        </p>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                            <strong>Course:</strong> <?php echo htmlspecialchars($paper['course']); ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center text-gray-500 dark:text-gray-400">
                    <p>No papers found matching your criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-100 dark:bg-gray-900 py-4 text-center text-gray-600 dark:text-gray-400 text-sm">
        <p>© <?php echo date('Y'); ?> Past Papers Archive. All rights reserved.</p>
    </footer>
</body>
</html>