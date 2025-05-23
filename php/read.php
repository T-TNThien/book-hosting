<?php
include 'db.php';
session_start();

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
              <a
                class="text-current nav-link active"
                aria-current="page"
                href="#">Home</a>
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
                <a class="nav-link" href="profile.php?username=<?= $_SESSION['username'] ?>">Profile</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
              </li>
            <?php else : ?>
              <a class="nav-link" href="login.php">Login</a>
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
      <h4>© 2025 Your Company. All Rights Reserved.</h4>
    </div>
  </footer>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>