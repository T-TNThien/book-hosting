<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password']) &&
    isset($_SESSION['user_id'])
) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user && password_verify($current, $user['password'])) {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $_SESSION['user_id']]);
        echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
