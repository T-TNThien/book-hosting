<?php
    $host = 'localhost';
    $db   = 'bookhosting';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
    try {
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $pdo = new PDO($dsn, $user, $pass);
        
        // Set error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Database connection failed: " . $e->getMessage();
    }
?>