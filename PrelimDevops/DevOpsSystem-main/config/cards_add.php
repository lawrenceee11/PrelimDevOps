<?php
session_start();
require_once __DIR__ . '/dbcon.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../content_management.php'); exit; }
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
$status = isset($_POST['status']) ? 1 : 1;
if ($title === '' || $description === '') { $_SESSION['status'] = 'Title and description required.'; header('Location: ../content_management.php'); exit; }

// handle image upload
$imagePath = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $f = $_FILES['image'];
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    if (!in_array(mime_content_type($f['tmp_name']), $allowed)) { $_SESSION['status'] = 'Invalid image file type.'; header('Location: ../content_management.php'); exit; }
    if ($f['size'] > 5 * 1024 * 1024) { $_SESSION['status'] = 'Image too large (max 5MB).'; header('Location: ../content_management.php'); exit; }
    $dir = __DIR__ . '/../image/home_cards';
    if (!is_dir($dir)) { mkdir($dir, 0755, true); }
    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
    $fn = uniqid('card_', true) . '.' . $ext;
    $dest = $dir . '/' . $fn;
    if (move_uploaded_file($f['tmp_name'], $dest)) { $imagePath = 'image/home_cards/' . $fn; } else { $_SESSION['status'] = 'Image upload failed.'; header('Location: ../content_management.php'); exit; }
}

$stmt = $conn->prepare('INSERT INTO home_cards (title, description, image_path, status, sort_order) VALUES (?, ?, ?, ?, ?)');
$stmt->bind_param('sssii', $title, $description, $imagePath, $status, $sort_order);
if ($stmt->execute()) { $_SESSION['status'] = 'Card added.'; } else { $_SESSION['status'] = 'Add failed: ' . $conn->error; }
$stmt->close(); header('Location: ../content_management.php'); exit; 