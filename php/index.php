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
  <main class="container mb-5">
    <!-- Carousel -->
    <div
      id="carouselExampleControls"
      class="carousel slide"
      data-bs-ride="carousel">
      <h2 class="text-light p-3 pt-4">Popular</h2>
      <div class="carousel-inner">

      <?php
        // Fetch top 5 most recently updated books
        $sql = "SELECT * FROM books ORDER BY view_count DESC LIMIT 5";
        $stmt = $pdo->query($sql);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>

      <?php if (!empty($books)) : ?>
        <!-- First carousel item (active) -->
        <div class="carousel-item active">
          <a href="details.php?id=<?= htmlspecialchars($books[0]['id']) ?>" class="stretched-link"></a>
          <div class="row row-cols-1 row-cols-lg-2 text-light align-carousel">
            <div class="col col-lg-4">

              <img src="<?= htmlspecialchars($books[0]['cover']) ?>" alt="<?= htmlspecialchars($books[0]['title']) ?>" class="img-fluid m-auto rounded-5 carousel-img">
            </div>
            <div class="col-lg-8">
              <h4><?= htmlspecialchars($books[0]['title']) ?></h4>

              <p class="lead text-truncate mb-0">
                <?php
                  $author_stmt = $pdo->prepare("SELECT name FROM authors WHERE id = ?");
                  $author_stmt->execute([(int)$books[0]['author_id']]);
                  $author = $author_stmt->fetch(PDO::FETCH_ASSOC);
                  echo $author ? htmlspecialchars($author['name']) : '';
                ?>
              </p>

              <p class="lead text-truncate">
                <?php
                  $illustrator_stmt = $pdo->prepare("SELECT name FROM illustrators WHERE id = ?");
                  $illustrator_stmt->execute([(int)$books[0]['illustrator_id']]);
                  $illustrator = $illustrator_stmt->fetch(PDO::FETCH_ASSOC);
                  echo $illustrator ? htmlspecialchars($illustrator['name']) : '';
                ?>
              </p>

              <p class="truncate-2-lines d-max-md-none"><?= htmlspecialchars($books[0]['description']) ?></p>
            </div>
          </div>
        </div>

        <!-- Remaining carousel items -->
        <?php foreach ($books as $i => $book): ?>
          <?php if ($i === 0) {continue;} ?>
          <div class="carousel-item">
            <a href="details.php?id=<?= htmlspecialchars($book['id']) ?>" class="stretched-link"></a>
            <div class="row row-cols-1 row-cols-lg-2 text-light align-carousel">
              <div class="col col-lg-4">

                <img src="<?= htmlspecialchars($book['cover']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" class="img-fluid m-auto rounded-5 carousel-img">
              </div>
              <div class="col-lg-8">
                <h4><?= htmlspecialchars($book['title']) ?></h4>

                <p class="lead text-truncate mb-0">
                  <?php
                    $author_stmt = $pdo->prepare("SELECT name FROM authors WHERE id = ?");
                    $author_stmt->execute([(int)$book['author_id']]);
                    $author = $author_stmt->fetch(PDO::FETCH_ASSOC);
                    echo $author ? htmlspecialchars($author['name']) : '';
                  ?>
                </p>

                <p class="lead text-truncate">
                  <?php
                    $illustrator_stmt = $pdo->prepare("SELECT name FROM illustrators WHERE id = ?");
                    $illustrator_stmt->execute([(int)$book['illustrator_id']]);
                    $illustrator = $illustrator_stmt->fetch(PDO::FETCH_ASSOC);
                    echo $illustrator ? htmlspecialchars($illustrator['name']) : '';
                  ?>
                </p>

                <p class="truncate-2-lines d-max-md-none"><?= htmlspecialchars($book['description']) ?></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
        <button
          class="carousel-control-prev position-absolute d-none d-md-block"
          type="button"
          data-bs-target="#carouselExampleControls"
          data-bs-slide="prev">
          <span class="bg-secondary rounded carousel-control-prev-icon"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button
          class="carousel-control-next position-absolute d-none d-md-block"
          type="button"
          data-bs-target="#carouselExampleControls"
          data-bs-slide="next">
          <span class="bg-secondary rounded carousel-control-next-icon"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>
    </div>
  
    <!-- Latest updates -->
    <div class="d-flex justify-content-between align-items-center mt-3">
      <h2 class="text-light p-3 pt-4">Latest updates</h2>
      <a href="#" class="btn btn-info fs-5">View more</a>
    </div>
    <div class="row row-cols-md-2 row-cols-lg-3 g-2">
      <?php
        $sql = "
          SELECT 
            b.id AS book_id,
            b.title AS book_title,
            b.cover,
            MAX(c.chapter_number) AS latest_chapter
          FROM books b
          LEFT JOIN chapters c ON b.id = c.book_id
          GROUP BY b.id
          ORDER BY b.updated_at DESC
          LIMIT 18
        ";
        $stmt = $pdo->query($sql);
        $latestBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>

      <?php foreach ($latestBooks as $book): ?>
        <div>
          <a href="details.php?id=<?= $book['book_id'] ?>" class="d-flex text-white bg-secondary bg-opacity-50 rounded-3">
            <img src="<?= htmlspecialchars($book['cover']) ?>" alt="<?= htmlspecialchars($book['book_title']) ?>" class="w-25 rounded-3">
            <div class="w-75 m-auto px-3">
              <h5 class="card-title text-truncate"><?= htmlspecialchars($book['book_title']) ?></h5>
              <p class="card-text">
                <?= $book['latest_chapter'] ? "Chapter " . htmlspecialchars($book['latest_chapter']) : "No chapters yet" ?>
              </p>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  
    <div class="row row-cols-1 row-cols-lg-2 mt-3">

      <!-- New titles -->
      <div class="col col-lg-8">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-light p-3 pt-4">New titles</h2>
            <a href="#" class="btn btn-info fs-5">View more</a>
          </div>
        <div class="row row-cols-md-3 row-cols-lg-4 g-2">
          <?php
            $sql = "
              SELECT 
                b.id AS book_id,
                b.title AS book_title,
                b.cover,
                MAX(c.chapter_number) AS latest_chapter
              FROM books b
              LEFT JOIN chapters c ON b.id = c.book_id
              GROUP BY b.id
              ORDER BY b.day_uploaded DESC
              LIMIT 12
            ";
            $stmt = $pdo->query($sql);
            $latestBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
          ?>

          <?php foreach ($latestBooks as $book): ?>
            <div class="col">
              <a href="details.php?id=<?= $book['book_id'] ?>" class="card text-white bg-secondary bg-opacity-50 rounded-3">
                <img src="<?= htmlspecialchars($book['cover']) ?>" alt="<?= htmlspecialchars($book['book_title']) ?>" class="card-img-top rounded-3">
                <h5 class="card-title text-truncate m-3"><?= htmlspecialchars($book['book_title']) ?></h5>
                <p class="card-text mx-3 mb-3"><?= $book['latest_chapter'] ? "Chapter " . htmlspecialchars($book['latest_chapter']) : "No chapters yet" ?></p>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="col col-lg-4">
        <!-- Saved -->
        <div>
          <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-light p-3 pt-4">Saved</h2>
            <a href="#" class="btn btn-info fs-5">View more</a>
          </div>
          <div class="row row-cols-md-2 row-cols-lg-1 g-2">
            <?php if (isset($_SESSION["user_id"])): ?>
              <?php
                $sql = "
                  SELECT b.id AS book_id, b.title, b.cover, c.chapter_number, c.id AS chapter_id
                  FROM saved_books sb
                  JOIN books b ON sb.book_id = b.id
                  LEFT JOIN (
                      SELECT book_id, MAX(chapter_number) AS chapter_number, MAX(id) AS id
                      FROM chapters
                      GROUP BY book_id
                  ) c ON c.book_id = b.id
                  WHERE sb.user_id = ?
                  ORDER BY sb.saved_at DESC
                  LIMIT 4
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_SESSION["user_id"]]);
                $savedBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
              ?>
              <?php if (!empty($savedBooks)): ?>
                <?php foreach ($savedBooks as $book): ?>
                  <div>
                    <div class="position-relative d-flex text-white bg-secondary bg-opacity-50 rounded-3">
                      <img src="<?= htmlspecialchars($book['cover'] ?? '../img/default-cover.jpg') ?>" alt=""
                          class="w-25 rounded-3">
                      <div class="w-75 m-auto px-3">
                        <h5 class="card-title text-truncate"><?= htmlspecialchars($book['title']) ?></h5>
                        <p class="card-text">Chapter <?= htmlspecialchars($book['chapter_number'] ?? '0') ?></p>
                      </div>
                      <a href="details.php?id=<?= $book['book_id'] ?>" class="stretched-link"></a>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="text-white px-3">No saved books yet.</div>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Random -->
        <div>
          <h2 class="text-light p-3 pt-4">Random</h2>
          <div class="row row-cols-md-2 row-cols-lg-1 g-2">
            <?php
              $sql = "
                SELECT b.id AS book_id, b.title, b.cover, c.chapter_number, c.id AS chapter_id
                FROM books b
                LEFT JOIN (
                    SELECT book_id, MAX(chapter_number) AS chapter_number, MAX(id) AS id
                    FROM chapters
                    GROUP BY book_id
                ) c ON c.book_id = b.id
                ORDER BY RAND()
                LIMIT 4
              ";
              $stmt = $pdo->prepare($sql);
              $stmt = $pdo->query($sql);
              $randomBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <?php foreach ($randomBooks as $book): ?>
              <div>
                <div class="position-relative d-flex text-white bg-secondary bg-opacity-50 rounded-3">
                  <img src="<?= htmlspecialchars($book['cover'] ?? '../img/default-cover.jpg') ?>" alt="" class="w-25 rounded-3">
                  <div class="w-75 m-auto px-3">
                    <h5 class="card-title text-truncate"><?= htmlspecialchars($book['title']) ?></h5>
                    <p class="card-text">Chapter <?= htmlspecialchars($book['chapter_number'] ?? '0') ?></p>
                  </div>
                  <a href="details.php?id=<?= $book['book_id'] ?>" class="stretched-link"></a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
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
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>