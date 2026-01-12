<?php
session_start();
require_once 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../user_management.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($id <= 0 || $username === '') {
    $_SESSION['status'] = 'Invalid input.';
    header('Location: ../user_management.php');
    exit;
}

// ensure suffix
if (strtolower(substr($username, -6)) !== '@admin') {
    $username .= '@admin';
}

// update
if ($password !== '') {
    $stmt = $conn->prepare('UPDATE accounts SET username = ?, password = ? WHERE id = ?');
    $stmt->bind_param('ssi', $username, $password, $id);
} else {
    $stmt = $conn->prepare('UPDATE accounts SET username = ? WHERE id = ?');
    $stmt->bind_param('si', $username, $id);
}

if ($stmt->execute()) {
    $_SESSION['status'] = 'User updated successfully.';
} else {
    $_SESSION['status'] = 'Database error: ' . $conn->error;
}
$stmt->close();

header('Location: ../user_management.php');
exit;
?>