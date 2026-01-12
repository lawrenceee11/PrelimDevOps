<?php
session_start();
require_once 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $_SESSION['status'] = 'Username and password are required.';
        header('Location: ../user_management.php');
        exit;
    }

    // Normalize username to ensure suffix
    if (strtolower(substr($username, -6)) !== '@admin') {
        $username .= '@admin';
    }

    // Basic length checks
    if (strlen($username) > 255 || strlen($password) > 255) {
        $_SESSION['status'] = 'Username or password too long.';
        header('Location: ../dashboard.php');
        exit;
    }

    // Check existence using prepared statements
    $stmt = $conn->prepare('SELECT id FROM accounts WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['status'] = 'Username already exists.';
        $stmt->close();
        header('Location: ../dashboard.php');
        exit;
    }
    $stmt->close();

    // Insert (note: passwords are stored plaintext to match existing login behavior)
    $stmt = $conn->prepare('INSERT INTO accounts (username, password) VALUES (?, ?)');
    $stmt->bind_param('ss', $username, $password);

    if ($stmt->execute()) {
        $_SESSION['status'] = 'User added successfully.';
    } else {
        $_SESSION['status'] = 'Database error: ' . $conn->error;
    }
    $stmt->close();
}

header('Location: ../user_management.php');
exit;
?>