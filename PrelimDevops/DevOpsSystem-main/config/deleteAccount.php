<?php
session_start();
require_once 'dbcon.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../user_management.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    $_SESSION['status'] = 'Invalid user id.';
    header('Location: ../user_management.php');
    exit;
}

$stmt = $conn->prepare('DELETE FROM accounts WHERE id = ?');
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    $_SESSION['status'] = 'User deleted.';
} else {
    $_SESSION['status'] = 'Database error: ' . $conn->error;
}
$stmt->close();

header('Location: ../user_management.php');
exit;
?>