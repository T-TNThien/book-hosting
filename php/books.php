<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Fetch books uploaded by this user
$stmt = $pdo->prepare("SELECT * FROM books WHERE uploader_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add a new book
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not logged in.']);
        exit;
    }

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $cover = trim($_POST['cover']);
    $author = trim($_POST['author']);
    $illustrator = trim($_POST['illustrator']);
    $uploader_id = $_SESSION['user_id'];

    if (empty($title) || empty($description) || empty($cover)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO books (title, description, cover, author, illustrator, uploader_id) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$title, $description, $cover, $author, $illustrator, $uploader_id]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save book.']);
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Books</title>
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
            rel="stylesheet"
        />
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        />
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
                            <?php if (isset($_SESSION['user_id'])) : ?>
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
        <main class="container mb-5 text-white">
            <div class="min-vh-100 py-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1>Danh sách truyện</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        <i class="fa-solid fa-plus"></i> Thêm truyện
                    </button>
                </div>

                <div class="row">
                    <?php foreach ($books as $book): ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="card mb-3">
                                <img src="<?= htmlspecialchars($book['cover']) ?>" class="card-img-top" alt="Cover" />
                                <div class="card-body bg-secondary">
                                    <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                                    <p class="card-text truncate-2-lines"><?= nl2br(htmlspecialchars($book['description'])) ?></p>
                                    <a href="bookChapterList.php?book_id=<?= $book['id'] ?>" class="btn btn-primary">Quản lý</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Add Book Modal -->
                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <form id="addBookForm" action="books.php" method="post" class="modal-content bg-black text-white">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Thêm truyện</h5>
                                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="title" class="col-form-label">Tên truyện:</label>
                                    <input type="text" class="form-control bg-black text-white" id="title" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="col-form-label">Mô tả:</label>
                                    <textarea class="form-control bg-black text-white" id="description" name="description"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="cover" class="col-form-label">Hình ảnh bìa (URL):</label>
                                    <input type="url" class="form-control bg-black text-white" id="cover" name="cover" required>
                                </div>
                                <div class="mb-3">
                                    <label for="author" class="col-form-label">Tác giả:</label>
                                    <input type="text" class="form-control bg-black text-white" id="author" name="author">
                                </div>
                                <div class="mb-3">
                                    <label for="illustrator" class="col-form-label">Minh họa:</label>
                                    <input type="text" class="form-control bg-black text-white" id="illustrator" name="illustrator">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                <button type="submit" class="btn btn-primary">Thêm</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        <footer class="text-bg-secondary bg-opacity-50 text-center bottom-0 py-3">
            <div class="container">
                <div class="text-start mb-3">
                    <a class="btn text-white" href="#">Top</a>
                    <a class="btn text-white" href="#">Latest</a>
                    <a class="btn text-white" href="#">New</a>
                    <?php if (isset($_SESSION['user_id'])) : ?>
                        <a class="btn text-white" href="#">Saved</a>
                    <?php endif; ?>
                </div>
                <h4>© 2025 Book Hosting Website</h4>
            </div>
        </footer>

        <script>
        document.getElementById('addBookForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('books.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Book added successfully!");
                    window.location.reload();
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert("An unexpected error occurred.");
            });
        });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>