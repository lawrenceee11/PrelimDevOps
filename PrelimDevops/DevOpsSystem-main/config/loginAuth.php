<?php
session_start();
require_once 'dbcon.php';

// Handle logout via GET (e.g., ?logout=1)
if (isset($_GET['logout'])) {
    // destroy the session and redirect to login
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// Collect input
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// Basic validation
if (empty($username) || empty($password)) {
    $_SESSION['error'] = 'Please enter username and password';
    header("Location: ../index.php");
    exit;
}

/* =========================
   1️⃣ Check Admin first (plain text)
========================= */
$adminSql = "SELECT * FROM accounts WHERE username = ? LIMIT 1";
$stmt = $conn->prepare($adminSql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Plain text check for admin
    if ($password === $row['password']) {
        $_SESSION['username'] = $row['username'];
        $_SESSION['success'] = 'Welcome Admin!';
        header("Location: ../content_management.php");
        exit;
    } else {
        $_SESSION['error'] = 'Invalid password for admin';
        header("Location: ../index.php");
        exit;
    }
}
$stmt->close();

/* =========================
   2️⃣ Check Student (hashed passwords in enroll table)
========================= */
$studentSql = "SELECT * FROM enroll WHERE username = ? LIMIT 1";
$stmt = $conn->prepare($studentSql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        $_SESSION['username'] = $row['username'];
        $_SESSION['success'] = 'Welcome Student!';
        header("Location: ../studentpage.php");
        exit;
    } else {
        $_SESSION['error'] = 'Invalid password for student';
        header("Location: ../index.php");
        exit;
    }
}

// If not found in either table
$_SESSION['error'] = 'Invalid username or password';
header("Location: ../index.php");
exit;

$conn->close();
?>
