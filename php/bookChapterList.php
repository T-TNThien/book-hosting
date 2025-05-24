<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

if (!isset($_GET['book_id'])) {
    header("Location: books.php");
    exit();
}

$book_id = $_GET['book_id'];
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['chapterTitle'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title && $content) {
        // Get next chapter number for this book
        $stmt = $pdo->prepare("SELECT MAX(chapter_number) AS max_num FROM chapters WHERE book_id = ?");
        $stmt->execute([$book_id]);
        $max = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_chapter_number = ($max['max_num'] ?? 0) + 1;

        // Insert new chapter
        $stmt = $pdo->prepare("INSERT INTO chapters (book_id, chapter_number, title, content) VALUES (?, ?, ?, ?)");
        $stmt->execute([$book_id, $next_chapter_number, $title, $content]);
    }

    header("Location: bookChapterList.php?book_id=$book_id");
    exit();
}

// Get book info
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? AND uploader_id = ?");
$stmt->execute([$book_id, $_SESSION['user_id']]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header("Location: books.php");
    exit();
}

// Get chapters
$stmt = $pdo->prepare("SELECT * FROM chapters WHERE book_id = ? ORDER BY id DESC");
$stmt->execute([$book_id]);
$chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Delete chapter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['chapterTitle']);
    $content = trim($_POST['content']);
    $book_id = $_POST['book_id'];
    $uploader_id = $_SESSION['user_id'];

    if (empty($title) || empty($content) || empty($book_id)) {
        header("Location: bookChapterList.php?book_id=$book_id");
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO chapters (book_id, title, content) VALUES (?, ?, ?)");
    $stmt->execute([$book_id, $title, $content]);

    header("Location: bookChapterList.php?book_id=$book_id");
    exit();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $book['title'] ?> Chapters</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    />
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/45.1.0/ckeditor5.css" />
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="../css/ckeditor.css" />
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
            <a class="btn btn-outline-secondary mb-3" href="books.php">Back to book list</a>
            <div class="d-flex justify-content-between align-items-center">
                <h1><?= htmlspecialchars($book['title']) ?></h1>
                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#addChapterModal">
                    <i class="fa-solid fa-plus"></i> Add chapter
                </button>
            </div>
            <!-- List of chapters, click title to view, or click trash icon to delete -->

            <div class="list-group mt-3">
                <?php foreach ($chapters as $chapter): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent text-white">
                        <a href="read.php?chapter_id=<?= $chapter['id'] ?>" class="list-group-item-action link-info bg-transparent">
                            <?= htmlspecialchars($chapter['title']) ?>
                        </a>
                        <button class="ms-3 bg-transparent" onclick="deleteChapter('<?= $chapter['id'] ?>')">
                            <i class="fa-solid fa-trash" style="color: #ff0000"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>


            <!-- Delete confirm modal, has a hidden input with name/id of chapterId -->
            <div
                class="modal fade"
                id="deleteModal"
                tabindex="-1"
                aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content bg-black">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Delete Chapter</h5>
                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete this chapter?
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <form action="bookChapterList.html" method="GET">
                                <input type="hidden" name="chapterId" id="chapterId" />
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add chapter, has title and content -->
            <div
                class="modal fade"
                id="addChapterModal"
                tabindex="-1"
                aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content bg-black">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Add Chapter</h5>
                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <form action="bookChapterList.php?book_id=<?= $book['id'] ?>" method="POST">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="chapterTitle" class="form-label">Chapter Title</label>
                                    <input
                                        type="text"
                                        class="form-control bg-dark text-white"
                                        id="chapterTitle"
                                        name="chapterTitle"
                                        required
                                        placeholder="Enter chapter title" />
                                </div>
                                <div class="mb-3">
                                    <label for="content" class="form-label">Chapter Content</label>
                                    <textarea name="content" id="content"> </textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    data-bs-dismiss="modal">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    Add Chapter
                                </button>
                            </div>
                        </form>
                    </div>
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

    <script>
        function deleteChapter(chapterId) {
            // Update hidden chapterId input value then trigger delete modal
            const deleteModal = new bootstrap.Modal(document.getElementById("deleteModal"));
            const chapterIdInput = document.getElementById("chapterId");
            chapterIdInput.value = chapterId;
            deleteModal.show();
        }

        window.addEventListener("DOMContentLoaded", () => {

            ClassicEditor
                .create(document.querySelector("#content"), {
                    
                    toolbar: [
                        "undo",
                        "redo",
                        "|",
                        "bold",
                        "italic",
                        "|",
                        "bulletedList",
                        "numberedList",
                        "|",
                        "blockQuote",
                    ]
                })
                .catch(error => {
                    console.error("CKEditor error:", error);
                });
        });

    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>