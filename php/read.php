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
    <div class="mt-4 pt-4 sticky-top d-flex justify-content-between bg-dark top-0" style="top: 12px">
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

      <div class="text-white d-flex align-items-center">
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

    <!-- Chapter content -->
    <div class="my-4">
      <h1 class="text-center text-light mt-5">
        Chapter <?= htmlspecialchars($chapter_number) ?>: <?= htmlspecialchars($chapter['chapter_title']) ?>
      </h1>
      <p class="text-center text-light mt-3" id="content"></p>
    </div>

    <!-- Scroll to Top -->
    <div id="scrollToTop" class="position-fixed d-flex flex-column bottom-0 end-0 me-3 mb-5" style="width: 52px; height: 104px; gap: 5px;">
      <a
        class="btn btn-secondary rounded-circle flex-fill d-flex align-items-center justify-content-center"
        href="details.php?id=<?= $book_id ?>"
        style="min-height: 48px; padding: 0;"
        title="Go to details page">
        <img src="../img/icon-return.png" alt="Details" style="height: 20px; width: 20px" />
      </a>

      <!-- scroll to top button -->
      <button
        class="btn btn-secondary rounded-circle flex-fill d-flex align-items-center justify-content-center"
        type="button"
        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
        style="min-height: 48px; padding: 0;"
        title="Scroll to top">
        <img src="../img/icon-up.svg" alt="Scroll Up" style="height: 20px; width: 20px" />
      </button>
    </div>

    <audio id="audio" hidden preload="auto" controls></audio>
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

  <?php $escapedContent = json_encode($chapter['content']); ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const rawHtml = <?= $escapedContent ?>;

    // Create a temporary DOM parser
    const parser = new DOMParser();
    const doc = parser.parseFromString(rawHtml, "text/html");

    // Extract paragraphs as arrays of sentences
    const paragraphs = Array.from(doc.querySelectorAll("p")).map(p => {
      const text = p.textContent.trim();
      // Split paragraph text into sentences by punctuation
      const sentences = text.match(/[^\.!\?]+[\.!\?]+(\s|$)/g)?.map(s => s.trim()) || [text];
      return sentences;
    });

    // Now paragraphs is an array of arrays of sentences
    // You can flatten or group as you want, e.g., keep as is
    const matrixContent = paragraphs;

    const contentElement = document.getElementById("content");

    function renderContent() {
      contentElement.innerHTML = "";
      matrixContent.forEach((sentences, pIndex) => {
        const p = document.createElement("p");
        p.id = `paragraph-${pIndex + 1}`;
        sentences.forEach((sentence, sIndex) => {
          const span = document.createElement("span");
          span.id = `line-${pIndex + 1}-${sIndex + 1}`;
          span.textContent = sentence;
          p.appendChild(span);
          p.appendChild(document.createElement("br"));
        });
        contentElement.appendChild(p);
      });
    }

    renderContent();


    let curLines = [1, 1];
    let playing = false;
    const audio = document.getElementById("audio");
    audio.volume = 0.5;

    // Your ElevenLabs API key here — IMPORTANT: Replace with your actual key!
    const ELEVEN_API_KEY = "sk_1038fad2e5b3992cf5a91330d98eead218f438682e9db9e8";
    const ELEVEN_VOICE_ID = "EXAVITQu4vr4xnSDxMaL"; // default voice

    async function playElevenLabsTTS(text) {
      try {
        const response = await fetch(`https://api.elevenlabs.io/v1/text-to-speech/${ELEVEN_VOICE_ID}`, {
          method: "POST",
          headers: {
            "xi-api-key": ELEVEN_API_KEY,
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            text: text,
            model_id: "eleven_monolingual_v1",
            voice_settings: {
              stability: 0.75,
              similarity_boost: 0.75
            }
          })
        });

        if (!response.ok) {
          throw new Error(`ElevenLabs TTS error: ${response.status} ${response.statusText}`);
        }

        const audioBlob = await response.blob();
        const audioUrl = URL.createObjectURL(audioBlob);
        audio.src = audioUrl;

        // Wait for audio to load metadata to avoid skipping
        await new Promise((resolve, reject) => {
          audio.onloadedmetadata = () => {
            resolve();
          };
          audio.onerror = (e) => reject(e);
        });

        // Play audio
        await audio.play();

        return audioUrl; // Return URL so we can revoke later
      } catch (error) {
        console.error(error);
        return null; // fallback, don't stall
      }
    }



    async function readContent() {
      if (playing) {
        stopReading();
        return;
      }

      playing = true;
      const [pIndex, lIndex] = curLines;
      const curText = matrixContent[pIndex - 1][lIndex - 1];
      highlightLine();

      const audioUrl = await playElevenLabsTTS(curText);

      // Note: Do NOT advance here, advance only on audio ended event

      // Keep track of audio URL to revoke later
      if (audioUrl) {
        audio.dataset.audioUrl = audioUrl;
      }
    }


    function stopReading() {
      audio.pause();
      audio.currentTime = 0;
      playing = false;
      removeHighlight();
    }


    function highlightLine() {
      removeHighlight(); // remove first to avoid multiple highlights
      const [p, l] = curLines;
      const lineEl = document.getElementById(`line-${p}-${l}`);

      if (lineEl) {
        lineEl.classList.add("highlight");
        lineEl.scrollIntoView({
          behavior: "smooth",
          block: "center"
        });
      }
    }


    function removeHighlight() {
      document.querySelectorAll("span.highlight").forEach(el => el.classList.remove("highlight"));
    }


    function resetLines() {
      curLines = [1, 1];
    }

    audio.addEventListener("ended", () => {
      // Revoke previous audio URL to avoid memory leaks
      if (audio.dataset.audioUrl) {
        URL.revokeObjectURL(audio.dataset.audioUrl);
        delete audio.dataset.audioUrl;
      }

      removeHighlight();
      playing = false;

      // Advance to next sentence
      let [p, l] = curLines;

      if (l < matrixContent[p - 1].length) {
        curLines[1]++;
      } else if (p < matrixContent.length) {
        curLines[0]++;
        curLines[1] = 1;
      } else {
        resetLines();
        stopReading();
        return;
      }

      // Play next line
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