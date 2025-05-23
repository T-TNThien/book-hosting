<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch uploaded books
$uploaded_stmt = $pdo->prepare("SELECT id, title, cover FROM books WHERE uploader_id = ?");
$uploaded_stmt->execute([$user['id']]);
$uploaded_books = $uploaded_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch saved books
$stmt = $pdo->prepare("
    SELECT b.id, b.title, b.cover
    FROM saved_books sb 
    JOIN books b ON sb.book_id = b.id
    WHERE sb.user_id = ?
");
$stmt->execute([$user['id']]);
$saved_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home</title>
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
                    <a href="#">
                        <img id="logo" src="../img/tachi.png" alt="Tachi Logo" class="rounded" />
                    </a>
                </div>
                <div class="collapse navbar-collapse" id="navbarTogglerDemo03">
                    <ul class="navbar-nav me-auto my-2 fw-bold ps-5">
                        <li class="nav-item">
                            <a class="text-current nav-link active" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Top</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Latest</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">New</a>
                        </li>
                        <?php if (isset($_SESSION['is']) && $_SESSION['is']) : ?>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Saved</a>
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

    <main>
        <div class="container py-5 text-white">
            <div class="row align-items-center mb-5">
                <div class="col-md-3 text-center">
                    <img src="<?= htmlspecialchars($user['avatar'] ?? '../img/default-avatar.png') ?>"
                        class="img-thumbnail rounded-circle shadow"
                        style="width: 150px; height: 150px; object-fit: cover;"
                        alt="Avatar">
                </div>
                <div class="col-md-9">
                    <h2><?= htmlspecialchars($user['username']) ?></h2>
                </div>
            </div>

            <button
                class="btn btn-primary mb-5"
                data-bs-toggle="modal"
                data-bs-target="#avatarModal"
            >
                Update Image
            </button>
            <a href="books.php" class="btn btn-secondary mb-5">Manage Books</a>
            <!-- Modal -->
            <div
                class="modal fade"
                id="avatarModal"
                tabindex="-1"
                aria-labelledby="avatarModalLabel"
                aria-hidden="true"
            >
                <div class="modal-dialog">
                    <div class="modal-content bg-dark text-white border-light">
                        <div class="modal-header">
                            <h5 class="modal-title" id="avatarModalLabel">
                                Update Profile Picture
                            </h5>
                            <button
                                type="button"
                                class="btn-close btn-close-white"
                                data-bs-dismiss="modal"
                                aria-label="Close"
                            ></button>
                        </div>
                        <div class="modal-body">
                            <form action="profile.html" method="GET" id="avatarForm">
                                <label for="newAvatarUrl" class="form-label">New Avatar URL</label>
                                <input
                                    type="url"
                                    class="form-control bg-secondary text-white border-0"
                                    id="newAvatarUrl"
                                    name="avatar"
                                />
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-outline-light"
                                data-bs-dismiss="modal"
                            >
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" form="avatarForm">
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Uploaded Books -->
            <div class="mb-5">
                <h3 class="mb-3">📚 Uploaded Books</h3>
                <?php if (count($uploaded_books)): ?>
                    <div class="row row-cols-2 row-cols-md-4 g-3">
                        <?php foreach ($uploaded_books as $book): ?>
                            <div class="col">
                                <a href="details.php?id=<?= $book['id'] ?>" class="text-decoration-none text-white">
                                    <div class="card bg-dark shadow-sm h-100">
                                        <img src="<?= htmlspecialchars($book['cover']) ?>"
                                            class="card-img-top"
                                            alt="<?= htmlspecialchars($book['title']) ?>"
                                            style="height: 200px; object-fit: cover;">
                                        <div class="card-body">
                                            <h6 class="card-title text-truncate"><?= htmlspecialchars($book['title']) ?></h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No uploaded books yet.</p>
                <?php endif; ?>
            </div>

            <!-- Saved Books -->
            <div class="mb-5">
                <h3 class="mb-3">💾 Saved Books</h3>
                <?php if (count($saved_books)): ?>
                    <div class="row row-cols-2 row-cols-md-4 g-3">
                        <?php foreach ($saved_books as $book): ?>
                            <div class="col">
                                <a href="details.php?id=<?= $book['id'] ?>" class="text-decoration-none text-white">
                                    <div class="card bg-dark shadow-sm h-100">
                                        <img src="<?= htmlspecialchars($book['cover']) ?>"
                                            class="card-img-top"
                                            alt="<?= htmlspecialchars($book['title']) ?>"
                                            style="height: 200px; object-fit: cover;">
                                        <div class="card-body">
                                            <h6 class="card-title text-truncate"><?= htmlspecialchars($book['title']) ?></h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No saved books yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <footer class="text-bg-secondary bg-opacity-50 text-center bottom-0 py-3">
        <div class="container">
            <div class="text-start mb-3">
                <a class="btn text-white" href="#">Top</a>
                <a class="btn text-white" href="#">Latest</a>
                <a class="btn text-white" href="#">New</a>
                <?php if (isset($_SESSION['is']) && $_SESSION['is']) : ?>
                    <a class="btn text-white" href="#">Saved</a>
                <?php endif; ?>
            </div>
            <h4>© 2025 Book Hosting Website</h4>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>