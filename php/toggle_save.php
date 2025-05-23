<?php
include 'db.php';
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "unauthenticated"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["book_id"])) {
    $book_id = (int) $_POST["book_id"];
    $user_id = (int) $_SESSION["user_id"];

    // Check if already saved
    $check_stmt = $pdo->prepare("SELECT 1 FROM saved_books WHERE user_id = ? AND book_id = ?");
    $check_stmt->execute([$user_id, $book_id]);

    if ($check_stmt->fetch()) {
        // Unsave
        $delete_stmt = $pdo->prepare("DELETE FROM saved_books WHERE user_id = ? AND book_id = ?");
        $delete_stmt->execute([$user_id, $book_id]);
        echo json_encode(["status" => "unsaved"]);
    } else {
        // Save
        $insert_stmt = $pdo->prepare("INSERT INTO saved_books (user_id, book_id) VALUES (?, ?)");
        $insert_stmt->execute([$user_id, $book_id]);
        echo json_encode(["status" => "saved"]);
    }
    exit();
}

echo json_encode(["status" => "error"]);
exit();
