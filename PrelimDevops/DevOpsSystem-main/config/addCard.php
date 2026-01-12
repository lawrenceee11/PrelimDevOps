<?php
session_start();
require_once __DIR__ . '/dbcon.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ../content_management.php'); exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
$status = 1; // default active

if ($title === '' || $description === '') {
  $_SESSION['status'] = 'Title and description are required.';
  header('Location: ../content_management.php'); exit;
}

$stmt = $conn->prepare('INSERT INTO home_cards (title, description, status, sort_order) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssii', $title, $description, $status, $sort_order);
if ($stmt->execute()) {
  $_SESSION['status'] = 'Card added successfully.';
} else {
  $_SESSION['status'] = 'Failed to add card: ' . $conn->error;
}
$stmt->close();
header('Location: ../content_management.php');
exit;