<?php
session_start();
require_once __DIR__ . '/dbcon.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../content_management.php'); exit; }
$header = trim($_POST['header'] ?? 'Featured');
$title = trim($_POST['title'] ?? 'Special title treatment');
$body = trim($_POST['body'] ?? '');
$footer = trim($_POST['footer'] ?? '');
$bg = trim($_POST['bg_color'] ?? '#ffffff');
// simple color validation (hex)
if (!preg_match('/^#[0-9a-fA-F]{3,6}$/', $bg)) { $bg = '#ffffff'; }

// ensure table exists
$create = "CREATE TABLE IF NOT EXISTS `feature_card` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header` varchar(255) NOT NULL DEFAULT 'Featured',
  `title` varchar(255) NOT NULL DEFAULT 'Special title treatment',
  `body` text NOT NULL,
  `footer` varchar(255) NOT NULL DEFAULT '',
  `bg_color` varchar(20) NOT NULL DEFAULT '#ffffff',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
$conn->query($create);

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id > 0) {
    // update existing
    $stmt = $conn->prepare('UPDATE feature_card SET header = ?, title = ?, body = ?, footer = ?, bg_color = ? WHERE id = ?');
    $stmt->bind_param('sssssi', $header, $title, $body, $footer, $bg, $id);
    if ($stmt->execute()) { $_SESSION['status'] = 'Feature card updated.'; } else { $_SESSION['status'] = 'Update failed: ' . $conn->error; }
    $stmt->close();
} else {
    // insert new
    $stmt = $conn->prepare('INSERT INTO feature_card (header, title, body, footer, bg_color) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssss', $header, $title, $body, $footer, $bg);
    if ($stmt->execute()) { $_SESSION['status'] = 'Feature card added.'; } else { $_SESSION['status'] = 'Create failed: ' . $conn->error; }
    $stmt->close();
}
header('Location: ../content_management.php'); exit;