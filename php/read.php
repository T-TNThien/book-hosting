<?php
include 'db.php';
session_start();

// Get book and chapter from URL
$book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
$chapter_id = isset($_GET['chapter_id']) ? (int)$_GET['chapter_id'] : 0;

// Fetch chapter
$stmt = $pdo->prepare("
    SELECT 
        books.title AS book_title, 
        chapters.title AS chapter_title, 
        chapters.content, 
        chapters.chapter_number
    FROM chapters
    JOIN books ON chapters.book_id = books.id
    WHERE chapters.book_id = ? AND chapters.id = ?
");
$stmt->execute([$book_id, $chapter_id]);
$chapter = $stmt->fetch();

if (!$chapter) {
  echo "Chapter not found.";
  exit();
}

$chapter_number = $chapter['chapter_number'];

// Get previous chapter
$prev_stmt = $pdo->prepare("
    SELECT id 
    FROM chapters 
    WHERE book_id = ? AND chapter_number < ? 
    ORDER BY chapter_number DESC LIMIT 1
");
$prev_stmt->execute([$book_id, $chapter_number]);
$prev = $prev_stmt->fetch();

// Get next chapter
$next_stmt = $pdo->prepare("
    SELECT id 
    FROM chapters 
    WHERE book_id = ? AND chapter_number > ? 
    ORDER BY chapter_number ASC LIMIT 1
");
$next_stmt->execute([$book_id, $chapter_number]);
$next = $next_stmt->fetch();

// // Book View Count
// $view_stmt = $pdo->prepare("
//     UPDATE books 
//     SET view_count = view_count + 1
//     WHERE id = ?
// ");
// $view_stmt->execute([$book_id]);

// // Get book and chapter details
// $book_stmt = $pdo->prepare("
//     SELECT b.id, b.title, b.cover, b.description, 
//            a.name AS author_name, i.name AS illustrator_name
//     FROM books b
//     LEFT JOIN authors a ON b.author_id = a.id
//     LEFT JOIN illustrators i ON b.illustrator_id = i.id
//     WHERE b.id = ?
// ");
// $book_stmt->execute([$book_id]);
// $book = $book_stmt->fetch(PDO::FETCH_ASSOC);

// $chapter_stmt = $pdo->prepare("
//     SELECT id, chapter_number, title, content 
//     FROM chapters 
//     WHERE id = ? AND book_id = ?
// ");
// $chapter_stmt->execute([$chapter_id, $book_id]);
// $chapter = $chapter_stmt->fetch(PDO::FETCH_ASSOC);

// if (!$book) {
//   header("Location: index.php");
//   exit;
// } elseif (!$chapter) {
//   header("Location: details.php?id=" . $book_id);
//   exit;
// }

// // Get previous and next chapter
// $prev_chapter = $pdo->prepare("
//     SELECT id, chapter_number, title 
//     FROM chapters 
//     WHERE book_id = ? AND chapter_number < ? 
//     ORDER BY chapter_number DESC 
//     LIMIT 1
// ");
// $prev_chapter->execute([$book_id, $chapter['chapter_number']]);
// $prev = $prev_chapter->fetch(PDO::FETCH_ASSOC);

// $next_chapter = $pdo->prepare("
//     SELECT id, chapter_number, title 
//     FROM chapters 
//     WHERE book_id = ? AND chapter_number > ? 
//     ORDER BY chapter_number ASC 
//     LIMIT 1
// ");
// $next_chapter->execute([$book_id, $chapter['chapter_number']]);
// $next = $next_chapter->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($chapter['book_title']) ?> - Chapter <?= htmlspecialchars($chapter_number) ?>: <?= htmlspecialchars($chapter['chapter_title']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../css/style.css" />
  <style>
    body {
      background-color: #222;
      color: #eee;
    }

    .highlight {
      background-color: #444;
    }

    /* Optional: make breadcrumb links white */
    .breadcrumb a {
      color: #ddd;
      text-decoration: none;
    }

    .breadcrumb a:hover {
      text-decoration: underline;
    }
  </style>
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
    <div class="mt-4 sticky-top d-flex justify-content-between" style="top: 12px">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="details.php?id=<?= $book_id ?>">
              <?= htmlspecialchars($chapter['book_title']) ?>
            </a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            Chapter <?= htmlspecialchars($chapter_number) ?>: <?= htmlspecialchars($chapter['chapter_title']) ?>
          </li>
        </ol>
      </nav>

      <div class="text-white">
        <!-- TTS button -->
        <button class="btn btn-secondary me-4" type="button" onclick="readContent()">
          <img src="../img/icon-speaker.svg" alt="" style="height: 20px; width: 20px" />
        </button>

        <!-- Prev chapter -->
        <?php if ($prev): ?>
          <a class="btn btn-secondary me-2" href="read.php?book_id=<?= $book_id ?>&chapter_id=<?= $prev['id'] ?>">
            <img src="../img/icon-up.svg" style="height: 20px; width: 20px; rotate: -90deg" />
          </a>
        <?php endif; ?>

        <!-- Next chapter -->
        <?php if ($next): ?>
          <a class="btn btn-secondary" href="read.php?book_id=<?= $book_id ?>&chapter_id=<?= $next['id'] ?>">
            <img src="../img/icon-up.svg" style="height: 20px; width: 20px; rotate: 90deg" />
          </a>
        <?php endif; ?>
      </div>
    </div>

    <div class="my-4">
      <h1 class="text-center text-light mt-5">
        Chapter <?= htmlspecialchars($chapter_number) ?>: <?= htmlspecialchars($chapter['chapter_title']) ?>
      </h1>
      <p class="text-center text-light mt-3" id="content"></p>
    </div>

    <!-- Scroll to Top -->
    <div id="scrollToTop" class="position-fixed bottom-0 end-0 me-3 mb-5" style="width: 52px; height: 52px">
      <button
        class="btn btn-secondary rounded-circle"
        type="button"
        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
        style="width: 100%; height: 100%; padding: 0">
        <img src="/img/icon-up.svg" alt="" style="height: 20px; width: 20px" />
      </button>
    </div>

    <audio id="audio" hidden preload="auto"></audio>
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
      <h4>© 2025 Your Company. All Rights Reserved.</h4>
    </div>
  </footer>

  <?php $escapedContent = json_encode(htmlspecialchars(strip_tags($chapter['content']))); ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const raw = <?= $escapedContent ?>;
    const lines = raw.split("\n").filter(l => l.trim() !== "");
    const matrixContent = [];
    for (let i = 0; i < lines.length; i += 3) { // group every 3 lines into a paragraph
      matrixContent.push(lines.slice(i, i + 3));
    }

    const contentElement = document.getElementById("content");

    function renderContent() {
      contentElement.innerHTML = "";
      matrixContent.forEach((lines, pIndex) => {
        const p = document.createElement("p");
        p.id = `paragraph-${pIndex + 1}`;
        lines.forEach((line, lIndex) => {
          const span = document.createElement("span");
          span.id = `line-${pIndex + 1}-${lIndex + 1}`;
          span.textContent = line;
          p.appendChild(span);
          p.appendChild(document.createElement("br"));
        });
        contentElement.appendChild(p);
      });
    }
    renderContent();

    let curLines = [1, 1],
      playing = false;
    const audio = document.getElementById("audio");
    audio.volume = 0.5;

    function readContent() {
      if (playing) {
        stopReading();
        return;
      }

      playing = true;

      const curText = matrixContent[curLines[0] - 1][curLines[1] - 1];
      const ttsUrl = new URL("https://translate.google.com/translate_tts");
      ttsUrl.searchParams.append("ie", "UTF-8");
      ttsUrl.searchParams.append("q", curText);
      ttsUrl.searchParams.append("tl", "en");
      ttsUrl.searchParams.append("client", "tw-ob");

      const proxyUrl = new URL("http://103.67.199.137:3010");
      proxyUrl.searchParams.append("url", ttsUrl.toString());
      audio.src = proxyUrl.toString();

      highlightLine();
      audio.play();
    }

    function stopReading() {
      audio.pause();
      audio.currentTime = 0;
      playing = false;
      document.querySelectorAll("span").forEach(el => el.classList.remove("highlight"));
    }

    function highlightLine() {
      const [p, l] = curLines;
      const lineEl = document.querySelector(`#paragraph-${p} #line-${p}-${l}`);
      if (lineEl) {
        lineEl.classList.add("highlight");
        lineEl.scrollIntoView({
          behavior: "smooth",
          block: "center"
        });
      }
    }

    function removeHighlight() {
      const [p, l] = curLines;
      const lineEl = document.querySelector(`#paragraph-${p} #line-${p}-${l}`);
      if (lineEl) lineEl.classList.remove("highlight");
    }

    function resetLines() {
      curLines = [1, 1];
    }

    audio.addEventListener("ended", () => {
      removeHighlight();
      playing = false;

      const [p, l] = curLines;
      if (l < matrixContent[p - 1].length) curLines[1]++;
      else if (p < matrixContent.length) {
        curLines[0]++;
        curLines[1] = 1;
      } else {
        resetLines();
        stopReading();
        return;
      }

      readContent();
    });
  </script>

  <style>
    .highlight {
      background-color: yellow;
      color: black;
    }
  </style>
</body>

</html>