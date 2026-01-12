<?php
session_start();
require_once __DIR__ . '/dbcon.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../studentpage.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
if (empty($username) || !isset($_SESSION['username']) || $_SESSION['username'] !== $username) {
    $_SESSION['status'] = 'Unauthorized request.';
    $_SESSION['status_type'] = 'danger';
    header('Location: ../studentpage.php');
    exit;
}

$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($new === '' || strlen($new) < 6) {
    $_SESSION['status'] = 'New password must be at least 6 characters.';
    $_SESSION['status_type'] = 'danger';
    header('Location: ../studentpage.php');
    exit;
}
if ($new !== $confirm) {
    $_SESSION['status'] = 'New password and confirmation do not match.';
    $_SESSION['status_type'] = 'danger';
    header('Location: ../studentpage.php');
    exit;
}

// fetch current hashed password
$stmt = $conn->prepare("SELECT password FROM enroll WHERE username = ? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row || empty($row['password']) || !password_verify($current, $row['password'])) {
    $_SESSION['status'] = 'Current password is incorrect.';
    $_SESSION['status_type'] = 'danger';
    header('Location: ../studentpage.php');
    exit;
}

$hash = password_hash($new, PASSWORD_DEFAULT);
$u = $conn->prepare("UPDATE enroll SET password = ? WHERE username = ? LIMIT 1");
if (!$u) {
    $_SESSION['status'] = 'Database error: ' . $conn->error;
    $_SESSION['status_type'] = 'danger';
    header('Location: ../studentpage.php');
    exit;
}
$u->bind_param('ss', $hash, $username);
if ($u->execute()) {
    $_SESSION['status'] = 'Password changed successfully.';
    $_SESSION['status_type'] = 'success';
} else {
    $_SESSION['status'] = 'Failed to update password: ' . $u->error;
    $_SESSION['status_type'] = 'danger';
}
$u->close();
header('Location: ../studentpage.php');
exit;