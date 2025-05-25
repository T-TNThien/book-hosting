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
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        // --- DELETE book ---
        $book_id = intval($_POST['book_id']);
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = ? AND uploader_id = ?");
        $stmt->execute([$book_id, $_SESSION['user_id']]);
        header("Location: books.php?success=deleted");
        exit;
    }

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $cover_url = trim($_POST['cover_url']);
    $cover_file_path = '';
    $cover_final = '';
    $author = trim($_POST['author']);
    $illustrator = trim($_POST['illustrator']);
    $uploader_id = $_SESSION['user_id'];

    if (empty($title) || (empty($_FILES['cover_file']['name']) && empty($cover_url)) || empty($author)) {
        header("Location: books.php?error=missing_fields");
        exit;
    }

    // Handle uploaded file if present
    if (isset($_FILES['cover_file']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['cover_file']['tmp_name'];
        $fileName = $_FILES['cover_file']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $newFileName = 'cover_' . htmlspecialchars($_SESSION['user_id']) . '.' . $fileExtension;

        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($fileExtension, $allowedExts)) {
            $uploadFileDir = 'uploads/covers/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $cover_file_path = $dest_path;
            } else {
                header("Location: books.php?error=upload_failed");
                exit;
            }
        } else {
            header("Location: books.php?error=invalid_filetype");
            exit;
        }
    }

    // Decide which cover to use
    if (!empty($cover_file_path)) {
        $cover_final = $cover_file_path;
    } elseif (!empty($cover_url)) {
        $cover_final = $cover_url;
    } else {
        header("Location: books.php?error=missing_cover");
        exit;
    }

    // Insert author to database
    $stmt = $pdo->prepare("INSERT INTO authors (name) VALUES (?)");
    $result = $stmt->execute([$author]);
    $author_id = $pdo->lastInsertId();

    // Insert illustrator to database
    $stmt = $pdo->prepare("INSERT INTO illustrators (name) VALUES (?)");
    $result = $stmt->execute([$illustrator]);
    $illustrator_id = $pdo->lastInsertId();

    // Insert book to database
    $stmt = $pdo->prepare("INSERT INTO books (title, description, cover, author_id, illustrator_id, uploader_id) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$title, $description, $cover_final, $author_id, $illustrator_id, $uploader_id]);

    if ($result) {
        header("Location: books.php");
    } else {
        header("Location: books.php?error=insert_failed");
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Uploaded Books</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
        rel="stylesheet" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
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
                                <a class="text-current nav-link active" href="books.php">Manage upload</a>
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
            <a class="btn btn-outline-secondary mb-3" href="profile.php">Back to profile</a>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Uploaded titles</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    <i class="fa-solid fa-plus"></i> Add new title
                </button>
            </div>

            <!-- Error message -->
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger mt-3">
                    <?php
                    switch ($_GET['error']) {
                        case 'upload_failed':
                            echo "Image upload failed. Please try again.";
                            break;
                        case 'invalid_filetype':
                            echo "Unsupported image file type.";
                            break;
                        case 'missing_cover':
                            echo "You must provide either an image URL or upload a file.";
                            break;
                        default:
                            echo "An error occurred.";
                    }
                    ?>
                </div>
            <?php endif; ?>
            <!-- Success message -->
            <?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
                <div class="alert alert-success">Book deleted successfully.</div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php
                    switch ($_GET['error']) {
                        case 'missing_fields':
                            echo 'Please fill in all required fields.';
                            break;
                        case 'insert_failed':
                            echo 'Failed to save the book.';
                            break;
                        default:
                            echo 'An unexpected error occurred.';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <?php foreach ($books as $book): ?>
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <div class="card bg-secondary bg-opacity-50 text-white mb-3 d-flex flex-column h-100">
                            <img src="<?= htmlspecialchars($book['cover']) ?>" class="card-img-top" alt="Cover" />
                            <div class="card-body d-flex flex-column h-100">
                                <h5 class="card-title d-flex justify-content-between mb-auto"><?= htmlspecialchars($book['title']) ?></h5>
                                <p class="card-text truncate-2-lines"><?= nl2br(htmlspecialchars($book['description'])) ?></p>
                                <div class="d-flex justify-content-between mt-auto">
                                    <a href="bookChapterList.php?book_id=<?= $book['id'] ?>" class="btn btn-primary">Manage</a>
                                    <!-- Delete form -->
                                    <form method="post" action="books.php" onsubmit="return confirm('Are you sure you want to delete this book?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Add Book Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form id="addBookForm" action="books.php" method="post" enctype="multipart/form-data" class="modal-content bg-black text-white">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Add title</h5>
                            <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="title" class="col-form-label">Title name:</label>
                                <input type="text" class="form-control bg-black text-white" id="title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="col-form-label">Description:</label>
                                <textarea class="form-control bg-black text-white" id="description" name="description"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="cover_url" class="col-form-label">Cover image (URL):</label>
                                <input type="url" class="form-control bg-black text-white" id="cover_url" name="cover_url">
                            </div>

                            <div class="text-center text-muted mb-2">— or —</div>

                            <div class="mb-3">
                                <label for="cover_file" class="col-form-label">Cover image (Upload):</label>
                                <input type="file" class="form-control bg-black text-white" id="cover_file" name="cover_file" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label for="author" class="col-form-label">Author:</label>
                                <input type="text" class="form-control bg-black text-white" id="author" name="author" required>
                            </div>
                            <div class="mb-3">
                                <label for="illustrator" class="col-form-label">Illustrator:</label>
                                <input type="text" class="form-control bg-black text-white" id="illustrator" name="illustrator">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add</button>
                        </div>
                    </form>
                </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>