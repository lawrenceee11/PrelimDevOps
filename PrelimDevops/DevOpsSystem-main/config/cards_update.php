<?php
session_start();
require_once __DIR__ . '/dbcon.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../content_management.php'); exit; }
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
$status = isset($_POST['status']) ? 1 : 0;
if (!$id || $title === '' || $description === '') { $_SESSION['status'] = 'Invalid input.'; header('Location: ../content_management.php'); exit; }

// get old image
$old = '';
$stmt = $conn->prepare('SELECT image_path FROM home_cards WHERE id = ?');
$stmt->bind_param('i', $id); $stmt->execute(); $res = $stmt->get_result(); if ($r = $res->fetch_assoc()) { $old = $r['image_path']; } $stmt->close();

// handle image removal
$imagePath = $old;
if (isset($_POST['remove_image']) && $_POST['remove_image']) {
    if ($old && file_exists(__DIR__ . '/../' . $old)) { @unlink(__DIR__ . '/../' . $old); }
    $imagePath = '';
}

// handle new upload
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
    if (move_uploaded_file($f['tmp_name'], $dest)) {
        if ($old && file_exists(__DIR__ . '/../' . $old)) { @unlink(__DIR__ . '/../' . $old); }
        $imagePath = 'image/home_cards/' . $fn;
    } else { $_SESSION['status'] = 'Image upload failed.'; header('Location: ../content_management.php'); exit; }
}

$stmt = $conn->prepare('UPDATE home_cards SET title=?, description=?, image_path=?, status=?, sort_order=? WHERE id=?');
$stmt->bind_param('sssiii', $title, $description, $imagePath, $status, $sort_order, $id);
if ($stmt->execute()) { $_SESSION['status'] = 'Card updated.'; } else { $_SESSION['status'] = 'Update failed: ' . $conn->error; }
$stmt->close(); header('Location: ../content_management.php'); exit; 