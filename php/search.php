<?php
include 'db.php';
session_start();

$limit = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query search
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort = $_GET['sort'] ?? 'asc-alphabet';

$orderClause = match ($sort) {
    'asc-alphabet' => 'ORDER BY title ASC',
    'desc-alphabet' => 'ORDER BY title DESC',
    'desc-updated' => 'ORDER BY updated_at DESC',
    'asc-updated' => 'ORDER BY updated_at ASC',
    'desc-created' => 'ORDER BY day_uploaded DESC',
    'asc-created' => 'ORDER BY day_uploaded ASC',
    'desc-views' => 'ORDER BY view_count DESC',
    'asc-views' => 'ORDER BY view_count ASC',
    default => 'ORDER BY title ASC',
};

$countSql = "SELECT COUNT(DISTINCT b.id) FROM books b
             LEFT JOIN chapters c ON b.id = c.book_id
             WHERE b.title LIKE :query OR b.description LIKE :query";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute(['query' => "%$q%"]);
$totalResults = $countStmt->fetchColumn();

// Calculate total pages
$totalPages = ceil($totalResults / $limit);

$sql = "SELECT b.*, COUNT(c.id) AS chapter_count
        FROM books b
        LEFT JOIN chapters c ON b.id = c.book_id
        WHERE b.title LIKE :query OR b.description LIKE :query
        GROUP BY b.id
        $orderClause
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':query', "%$q%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css" />
</head>

<body class="bg-dark">
    <header style="background-color: #608BC1;">
        <nav class="navbar navbar-expand-lg align-items-center">
            <div class="container d-flex">
                <div class="navbar-brand d-flex align-items-center">
                    <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo03" aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <a href="index.php">
                        <img id="logo" src="../img/tachi.png" alt="Tachi Logo" class="rounded" />
                    </a>
                </div>
                <div class="collapse navbar-collapse" id="navbarTogglerDemo03">
                    <ul class="navbar-nav me-auto my-2 fw-bold ps-5">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="search.php?sort=desc-views">Top</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="search.php?sort=desc-updated">Latest</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="search.php?sort=desc-created">New</a>
                        </li>
                        <?php if (isset($_SESSION['user_id'])) : ?>
                            <li class="nav-item">
                                <a class="nav-link" href="books.php">Manage upload</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="profile.php">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="logout.php">Logout</a>
                            </li>
                        <?php else : ?>
                            <a class="nav-link" href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Login</a>
                        <?php endif; ?>
                    </ul>
                    <form class="d-flex" role="search">
                        <input
                            class="form-control me-2"
                            type="search"
                            placeholder="Enter name"
                            aria-label="Search" />
                        <button class="btn btn-light" type="submit">
                            <img src="../img/icon-search.png" alt="" style="height: 20px" />
                        </button>
                    </form>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mb-5 text-white">
        <div class="min-vh-100 py-4">
            <h1>
                Searching
                <span class="text-secondary"><?= $q ? (htmlspecialchars($q)) : '' ?></span>
            </h1>
            <form class="" role="search" method="get">
                <div class="input-group">
                    <input
                        class="form-control border-start-0"
                        type="search"
                        placeholder="Search"
                        aria-label="Search"
                        name="q"
                        id="q"
                        value="<?= htmlspecialchars($q) ?>" />
                </div>
                <div class="d-flex gap-2 justify-content-end mt-2">
                    <select class="form-select w-auto" name="sort" id="sort">
                        <option value="asc-alphabet" <?= $sort == 'asc-alphabet' ? 'selected' : '' ?>>A-Z</option>
                        <option value="desc-alphabet" <?= $sort == 'desc-alphabet' ? 'selected' : '' ?>>Z-A</option>
                        <option value="desc-updated" <?= $sort == 'desc-updated' ? 'selected' : '' ?>>Last Updated (Descending)</option>
                        <option value="asc-updated" <?= $sort == 'asc-updated' ? 'selected' : '' ?>>Last Updated (Ascending)</option>
                        <option value="desc-created" <?= $sort == 'desc-created' ? 'selected' : '' ?>>Upload Date (Descending)</option>
                        <option value="asc-created" <?= $sort == 'asc-created' ? 'selected' : '' ?>>Upload Date (Ascending)</option>
                        <option value="desc-views" <?= $sort == 'desc-views' ? 'selected' : '' ?>>Most Viewed (Descending)</option>
                        <option value="asc-views" <?= $sort == 'asc-views' ? 'selected' : '' ?>>Most Viewed (Ascending)</option>
                    </select>
                    <button class="btn btn-light" type="submit">
                        <img src="../img/icon-search.png" alt="" style="height: 20px" />
                    </button>
                </div>
            </form>
            <div class="mt-4">
                <div class="row">
                    <?php if (count($results) === 0): ?>
                        <p class="text-muted">No results found.</p>
                    <?php else: ?>
                        <?php foreach ($results as $book): ?>
                            <div class="col-6 col-md-4 col-lg-3 mb-4">
                                <div class="card mb-3 d-flex flex-column h-100">
                                    <a href="details.php?id=<?= htmlspecialchars($book['id']) ?>" class="stretched-link"></a>
                                    <img src="<?= htmlspecialchars($book['cover']) ?>" class="card-img-top" alt="<?= htmlspecialchars($book['title']) ?>" />
                                    <div class="card-body bg-secondary d-flex flex-column h-100">
                                        <h5 class="card-title d-flex justify-content-between mb-auto"><?= htmlspecialchars($book['title']) ?></h5>
                                        <p class="card-text truncate-2-lines"><?= htmlspecialchars($book['description']) ?></p>
                                        <div class="d-flex justify-content-between mt-auto" style="font-weight: 500;">
                                            <?= $book['chapter_count'] ?> Chapters
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif ?>
                </div>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Previous button -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?q=<?= urlencode($q) ?>&sort=<?= urlencode($sort) ?>&page=<?= $page - 1 ?>" tabindex="-1">Previous</a>
                        </li>

                        <!-- Page numbers -->
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                                <a class="page-link <?= ($p == $page) ? 'bg-3' : '' ?>" href="?q=<?= urlencode($q) ?>&sort=<?= urlencode($sort) ?>&page=<?= $p ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next button -->
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?q=<?= urlencode($q) ?>&sort=<?= urlencode($sort) ?>&page=<?= $page + 1 ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </main>

    <footer class="text-bg-secondary bg-opacity-50 text-center bottom-0 py-3">
        <div class="text-start mb-3">
            <a class="btn text-white" href="search.php?sort=desc-views">Top</a>
            <a class="btn text-white" href="search.php?sort=desc-updated">Latest</a>
            <a class="btn text-white" href="search.php?sort=desc-created">New</a>
            <?php if (isset($_SESSION['user_id'])) : ?>
                <a class="btn text-white" href="books.php">Manage upload</a>
            <?php endif; ?>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>