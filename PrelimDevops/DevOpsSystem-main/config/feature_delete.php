<?php
session_start();
require_once __DIR__ . '/dbcon.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../content_management.php'); exit; }
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$id) { $_SESSION['status'] = 'Invalid ID.'; header('Location: ../content_management.php'); exit; }
// delete
$stmt = $conn->prepare('DELETE FROM feature_card WHERE id = ?');
$stmt->bind_param('i', $id);
if ($stmt->execute()) { $_SESSION['status'] = 'Feature deleted.'; } else { $_SESSION['status'] = 'Delete failed: ' . $conn->error; }
$stmt->close(); header('Location: ../content_management.php'); exit;