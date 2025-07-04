<?php
include 'db.php';
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

$book_id = $_GET['id'];

// Book View Count
$view_stmt = $pdo->prepare("
    UPDATE books 
    SET view_count = view_count + 1
    WHERE id = ?
");
$view_stmt->execute([$book_id]);

// Book Details + Author + Illustrator + Uploader
$details_stmt = $pdo->prepare("
    SELECT 
        b.id AS book_id, b.title AS book_title, b.cover, b.description, 
        a.name AS author_name,
        i.name AS illustrator_name,
        u.username AS uploader_name
    FROM books b
    LEFT JOIN authors a ON b.author_id = a.id
    LEFT JOIN illustrators i ON b.illustrator_id = i.id
    LEFT JOIN users u ON b.uploader_id = u.id
    WHERE b.id = ?
");
$details_stmt->execute([$book_id]);
$details = $details_stmt->fetch(PDO::FETCH_ASSOC);

// Genres
$genre_stmt = $pdo->prepare("
    SELECT g.name 
    FROM books_genres bg 
    JOIN genres g ON bg.genre_id = g.id 
    WHERE bg.book_id = ?
");
$genre_stmt->execute([$book_id]);
$genres = $genre_stmt->fetchAll(PDO::FETCH_COLUMN);

// Chapters
$chapter_stmt = $pdo->prepare("
    SELECT id, chapter_number, title, day_uploaded
    FROM chapters 
    WHERE book_id = ? 
    ORDER BY chapter_number DESC
");
$chapter_stmt->execute([$book_id]);
$chapters = $chapter_stmt->fetchAll(PDO::FETCH_ASSOC);

function time_elapsed_string($datetime, $full = false)
{
    $now = new DateTime;
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    $map = [
        'y' => 'year',
        'm' => 'month',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];

    foreach ($map as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($map[$k]);
        }
    }

    if (!$full) $map = array_slice($map, 0, 1);
    return $map ? implode(', ', $map) . ' ago' : 'just now';
}

$is_saved = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $check_stmt = $pdo->prepare("SELECT 1 FROM saved_books WHERE user_id = ? AND book_id = ?");
    $check_stmt->execute([$user_id, $details['book_id']]);
    $is_saved = $check_stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $details['book_title'] ?>'s Information</title>
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
    <main class="container min-vh-100">
        <div class="my-4">
            <div class="row g-4 align-items-start">
                <div class="col-md-3 mt-5">
                    <img
                        src="<?= htmlspecialchars($details['cover']) ?>"
                        alt="<?= htmlspecialchars($details['book_title']) ?>"
                        class="img-fluid rounded shadow" />
                </div>

                <div class="col-md-9 text-white mt-5">
                    <h2 class="mb-3"><?= htmlspecialchars($details['book_title']) ?></h2>
                    <p class="truncate-2-lines d-max-md-none"><?= htmlspecialchars($details['description']) ?></p>

                    <!-- Show genres -->
                    <?php if (!empty($genres)): ?>
                        <div class="mb-3">
                            <strong>Genres:</strong>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                <?php foreach ($genres as $genre): ?>
                                    <span class="badge bg-3 text-1 fw-semibold"><?= htmlspecialchars($genre) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php
                    $start_read_stmt = $pdo->prepare("
                        SELECT id, chapter_number, title, day_uploaded
                        FROM chapters 
                        WHERE book_id = ? 
                        ORDER BY chapter_number ASC
                        LIMIT 1
                    ");
                    $start_read_stmt->execute([$book_id]);
                    $start_read = $start_read_stmt->fetch(PDO::FETCH_ASSOC);
                    ?>


                    <!-- Show author, illustrator, uploader -->
                    <ul class="list-unstyled mb-0">
                        <li><strong>Author:</strong> <?= htmlspecialchars($details['author_name'] ?? '') ?></li>
                        <li><strong>Illustrator:</strong> <?= htmlspecialchars($details['illustrator_name'] ?? '') ?></li>
                        <li><strong>Uploader:</strong> <?= htmlspecialchars($details['uploader_name'] ?? 'anonymous') ?></li>
                    </ul>
                    <!-- Start reading -->
                    <a href="read.php?book_id=<?= $book_id ?>&chapter_id=<?= htmlspecialchars($start_read['id']) ?>" class="btn btn-primary mt-4"> 📖 Start Reading </a>
                    <!-- Save -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button
                            id="save-btn"
                            data-book-id="<?= htmlspecialchars($details['book_id']) ?>"
                            class="btn <?= $is_saved ? 'btn-danger' : 'btn-outline-light' ?> mt-4">
                            <?= $is_saved ? '🗑️ Unsave Book' : '💾 Save Book' ?>
                        </button>
                    <?php else: ?>
                        <a href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-outline-light mt-4">💾 Save Book</a>
                    <?php endif; ?>

                </div>
            </div>

            <!-- list of chapter, index. chapter names, days ago -->
            <div class="mt-5">
                <h3 class="text-white">Chapters</h3>
                <ol class="list-group">
                    <?php foreach ($chapters as $chapter): ?>
                        <li class="list-group-item bg-transparent text-white d-flex justify-content-between align-items-start px-0 border-bottom py-3">
                            <a class="ms-3 text-4 me-auto fw-bold" href="read.php?book_id=<?= $book_id ?>&chapter_id=<?= htmlspecialchars($chapter['id']) ?>">
                                <span>Ch.<?= htmlspecialchars($chapter['chapter_number']) ?> -</span> <?= htmlspecialchars($chapter['title']) ?>
                            </a>
                            <span class="badge text-white bg-3 rounded-pill">
                                <?= time_elapsed_string($chapter['day_uploaded']) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </main>
    <footer class="text-bg-secondary bg-opacity-50 text-center bottom-0 py-3">
        <div class="container">
            <div class="text-start mb-3">
                <a class="btn text-white" href="search.php?sort=desc-views">Top</a>
                <a class="btn text-white" href="search.php?sort=desc-updated">Latest</a>
                <a class="btn text-white" href="search.php?sort=desc-created">New</a>
                <?php if (isset($_SESSION['user_id'])) : ?>
                    <a class="btn text-white" href="books.php">Manage upload</a>
                <?php endif; ?>
            </div>
            <h4>© 2025 Book Hosting Website</h4>
        </div>
    </footer>

    <script>
        document.getElementById("save-btn")?.addEventListener("click", function(e) {
            e.preventDefault();

            const btn = e.target;
            const bookId = btn.dataset.bookId;

            fetch("toggle_save.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "book_id=" + encodeURIComponent(bookId) + "&ajax=1"
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "saved") {
                        btn.textContent = "🗑️ Unsave Book";
                        btn.classList.remove("btn-outline-light");
                        btn.classList.add("btn-danger");
                    } else if (data.status === "unsaved") {
                        btn.textContent = "💾 Save Book";
                        btn.classList.remove("btn-danger");
                        btn.classList.add("btn-outline-light");
                    } else if (data.status === "unauthenticated") {
                        window.location.href = "login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>";
                    }
                })
                .catch(error => console.error("AJAX error:", error));
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>