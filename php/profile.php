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

// Avatar update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $avatarUrl = '';
    
    // Handle file upload first if provided
    if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../img/';
        $tmp = $_FILES['avatar_file']['tmp_name'];
        $originalName = basename($_FILES['avatar_file']['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $allowed)) {
            $newFileName = 'avatar_' . htmlspecialchars($_SESSION['user_id']) . '.' . $ext;
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($tmp, $destination)) {
                $avatarUrl = $destination;
            }
        }
    }

    // Fallback to URL input
    if (!$avatarUrl && !empty($_POST['avatar_url'])) {
        $avatarUrl = $_POST['avatar_url'];
    }

    if ($avatarUrl) {
        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$avatarUrl, $_SESSION['user_id']]);
        header("Location: profile.php");
        exit();
    }
}


// Pagination
// Defaults
$limit = 12;
$page_uploaded = isset($_GET['uploaded_page']) ? (int)$_GET['uploaded_page'] : 1;
$page_saved = isset($_GET['saved_page']) ? (int)$_GET['saved_page'] : 1;

// Calculate offsets
$offset_uploaded = ($page_uploaded - 1) * $limit;
$offset_saved = ($page_saved - 1) * $limit;

// Uploaded books
$stmt = $pdo->prepare("SELECT * FROM books WHERE uploader_id = ? LIMIT ? OFFSET ?");
$stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset_uploaded, PDO::PARAM_INT);
$stmt->execute();
$uploaded_books = $stmt->fetchAll();

// Saved books
$stmt = $pdo->prepare("
    SELECT b.* FROM saved_books sb
    JOIN books b ON sb.book_id = b.id
    WHERE sb.user_id = ?
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset_saved, PDO::PARAM_INT);
$stmt->execute();
$saved_books = $stmt->fetchAll();


// Uploaded
$stmt = $pdo->prepare("SELECT COUNT(*) FROM books WHERE uploader_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_uploaded = $stmt->fetchColumn();
$total_uploaded_pages = ceil($total_uploaded / $limit);

// Saved
$stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_books WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_saved = $stmt->fetchColumn();
$total_saved_pages = ceil($total_saved / $limit);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profile</title>
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
                                <a class="text-current nav-link active" href="profile.php">Profile</a>
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
                class="btn bg-3 mb-5"
                data-bs-toggle="modal"
                data-bs-target="#avatarModal">
                Update Image
            </button>
            <a href="books.php" class="btn btn-secondary mb-5">Manage Uploaded Books</a>
            <button
                class="btn btn-warning mb-5"
                data-bs-toggle="modal"
                data-bs-target="#passwordModal">
                Change Password
            </button>
            <!-- Modal for avatar change -->
            <div
                class="modal fade"
                id="avatarModal"
                tabindex="-1"
                aria-labelledby="avatarModalLabel"
                aria-hidden="true">
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
                                aria-label="Close"></button>
                        </div>
                        <form action="profile.php" method="POST" id="avatarForm" enctype="multipart/form-data">
                            <div class="modal-body">
                                <label for="newAvatarUrl" class="form-label">New Avatar URL</label>
                                <input
                                    type="url"
                                    class="form-control bg-secondary text-white border-0 mb-3"
                                    id="newAvatarUrl"
                                    name="avatar_url"
                                    placeholder="https://example.com/avatar.jpg" />

                                <div class="text-center text-muted mb-2">— or —</div>

                                <label for="avatarFile" class="form-label">Upload Image File</label>
                                <input
                                    type="file"
                                    class="form-control bg-secondary text-white border-0"
                                    id="avatarFile"
                                    name="avatar_file"
                                    accept="image/*" />
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Modal for password change -->
            <div
                class="modal fade"
                id="passwordModal"
                tabindex="-1"
                aria-labelledby="passwordModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content bg-dark text-white border-light">
                        <div class="modal-header">
                            <h5 class="modal-title" id="passwordModalLabel">Change Password</h5>
                            <button
                                type="button"
                                class="btn-close btn-close-white"
                                data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <form id="passwordForm">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="currentPassword" class="form-label">Current Password</label>
                                    <input
                                        type="password"
                                        class="form-control bg-secondary text-white border-0"
                                        id="currentPassword"
                                        name="current_password"
                                        required />
                                </div>
                                <div class="mb-3">
                                    <label for="newPassword" class="form-label">New Password</label>
                                    <input
                                        type="password"
                                        class="form-control bg-secondary text-white border-0"
                                        id="newPassword"
                                        name="new_password"
                                        required
                                        maxlength="64"
                                        minlength="6" />
                                </div>
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                    <input
                                        type="password"
                                        class="form-control bg-secondary text-white border-0"
                                        id="confirmPassword"
                                        name="confirm_password"
                                        required
                                        maxlength="64"
                                        minlength="6" />
                                </div>
                                <div class="mb-3">
                                    <div id="passwordMatchMessage" class="form-text text-danger mt-1"></div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button
                                    type="button"
                                    class="btn btn-outline-light"
                                    data-bs-dismiss="modal">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-warning" form="passwordForm" disabled>
                                    Save Changes
                                </button>
                            </div>
                        </form>
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
                                    <div class="card bg-secondary bg-opacity-50 shadow-sm h-100">
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

            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_uploaded_pages; $i++): ?>
                        <li class="page-item <?= $i === $page_uploaded ? 'active' : '' ?>">
                            <a class="page-link" href="?uploaded_page=<?= $i ?>&saved_page=<?= $page_saved ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>

            <!-- Saved Books -->
            <div class="mb-5" id="saved-books">
                <h3 class="mb-3">💾 Saved Books</h3>
                <?php if (count($saved_books)): ?>
                    <div class="row row-cols-2 row-cols-md-4 g-3">
                        <?php foreach ($saved_books as $book): ?>
                            <div class="col">
                                <a href="details.php?id=<?= $book['id'] ?>" class="text-decoration-none text-white">
                                    <div class="card bg-secondary bg-opacity-50 shadow-sm h-100">
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

            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_saved_pages; $i++): ?>
                        <li class="page-item <?= $i === $page_saved ? 'active' : '' ?>">
                            <a class="page-link" href="?uploaded_page=<?= $page_uploaded ?>&saved_page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
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
        // Password validation
        const currentPassword = document.getElementById('currentPassword');
        const newPassword = document.getElementById('newPassword');
        const confirmPassword = document.getElementById('confirmPassword');
        const matchMessage = document.getElementById('passwordMatchMessage');
        const submitBtn = document.querySelector('#passwordForm button[type="submit"]');

        function checkPasswordMatch() {
            if (currentPassword.value === "" || newPassword.value === "" || confirmPassword.value === "") {
                matchMessage.textContent = '';
                submitBtn.disabled = true;
                return;
            }

            if (newPassword.value === confirmPassword.value) {
                matchMessage.textContent = '✔ Passwords match';
                matchMessage.classList.remove('text-danger');
                matchMessage.classList.add('text-success');
                submitBtn.disabled = false;
            } else {
                matchMessage.textContent = '✖ Passwords do not match';
                matchMessage.classList.remove('text-success');
                matchMessage.classList.add('text-danger');
                submitBtn.disabled = true;
            }
            if (currentPassword.value === newPassword.value) {
                matchMessage.textContent = '✖ New password cannot be the same as the current password';
                matchMessage.classList.remove('text-success');
                matchMessage.classList.add('text-danger');
                submitBtn.disabled = true;
            }
        }

        currentPassword.addEventListener('input', checkPasswordMatch);
        newPassword.addEventListener('input', checkPasswordMatch);
        confirmPassword.addEventListener('input', checkPasswordMatch);

        // Password change submit
        document.getElementById('passwordForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);

            const response = await fetch('update_password.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            const msgBox = document.getElementById('passwordMatchMessage');

            if (result.success) {
                msgBox.classList.remove('text-danger');
                msgBox.classList.add('text-success');
                msgBox.textContent = result.message;
            } else {
                msgBox.classList.remove('text-success');
                msgBox.classList.add('text-danger');
                msgBox.textContent = result.message;
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>