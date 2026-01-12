<?php
session_start();
require_once __DIR__ . '/dbcon.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../content_management.php'); exit; }
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$id) { $_SESSION['status'] = 'Invalid ID.'; header('Location: ../content_management.php'); exit; }
// fetch image and delete file
$stmt = $conn->prepare('SELECT image_path FROM home_cards WHERE id = ?');
$stmt->bind_param('i', $id); $stmt->execute(); $res = $stmt->get_result(); if ($r = $res->fetch_assoc()) { $image = $r['image_path']; if ($image && file_exists(__DIR__ . '/../' . $image)) { @unlink(__DIR__ . '/../' . $image); } } $stmt->close();

$stmt = $conn->prepare('DELETE FROM home_cards WHERE id = ?');
$stmt->bind_param('i', $id);
if ($stmt->execute()) { $_SESSION['status'] = 'Card deleted.'; } else { $_SESSION['status'] = 'Delete failed: ' . $conn->error; }
$stmt->close(); header('Location: ../content_management.php'); exit; 