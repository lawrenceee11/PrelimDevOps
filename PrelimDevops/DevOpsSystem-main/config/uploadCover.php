<?php
session_start();
$siteDir = realpath(__DIR__ . '/..');
$imagesDir = realpath(__DIR__ . '/../image');
$logFile = __DIR__ . '/logo-update.log';
$coversFile = __DIR__ . '/covers.json';
function logCover($msg) { global $logFile; @file_put_contents($logFile, date('Y-m-d H:i:s') . " - COVER: " . $msg . PHP_EOL, FILE_APPEND); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../content_management.php');
    exit;
}

if ($imagesDir === false) {
    $_SESSION['status'] = 'Images directory not found.';
    header('Location: ../content_management.php');
    exit;
}

if (!isset($_FILES['cover']) || $_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['status'] = 'No file uploaded or upload error.';
    header('Location: ../content_management.php');
    exit;
}

$file = $_FILES['cover'];
$allowed = ['png','jpg','jpeg','gif','webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    $_SESSION['status'] = 'Invalid file type. Allowed: png,jpg,jpeg,gif,webp.';
    header('Location: ../content_management.php');
    exit;
}
if ($file['size'] > $maxSize) {
    $_SESSION['status'] = 'File too large (max 5MB).';
    header('Location: ../content_management.php');
    exit;
}

// Validate MIME
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if (strpos($mime, 'image/') !== 0) {
    $_SESSION['status'] = 'Uploaded file is not an image.';
    header('Location: ../content_management.php');
    exit;
}

// Move file
$newName = 'cover-' . time() . '.' . $ext;
$destPath = $imagesDir . DIRECTORY_SEPARATOR . $newName;
if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    logCover('move_uploaded_file failed for ' . $file['tmp_name']);
    $_SESSION['status'] = 'Failed to move uploaded file.';
    header('Location: ../content_management.php');
    exit;
}
@chmod($destPath, 0644);

// Collect title/caption from POST
$title = trim($_POST['title'] ?? '');
$caption = trim($_POST['caption'] ?? '');

// Add to covers.json (store objects to support title/caption)
$covers = [];
if (file_exists($coversFile)) {
    $raw = @file_get_contents($coversFile);
    $covers = json_decode($raw, true) ?? [];
}
$entry = ['path' => 'image/' . $newName, 'title' => $title, 'caption' => $caption];
$covers[] = $entry;
file_put_contents($coversFile, json_encode($covers, JSON_PRETTY_PRINT));

logCover('Uploaded cover ' . $newName . ' title=' . $title);
$_SESSION['status'] = 'Cover uploaded successfully.';
header('Location: ../content_management.php');
exit;
?>