<?php
session_start();
$logFile = __DIR__ . '/logo-update.log';
function logCard($m) { global $logFile; @file_put_contents($logFile, date('Y-m-d H:i:s') . " - CARD: " . $m . PHP_EOL, FILE_APPEND); }

$cardsFile = __DIR__ . '/cards.json';
$imagesDir = realpath(__DIR__ . '/../image');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../content_management.php'); exit; }
$index = isset($_POST['index']) ? intval($_POST['index']) : -1;
if ($index < 0) { $_SESSION['status'] = 'Invalid card index.'; header('Location: ../content_management.php'); exit; }
$title = trim($_POST['title'] ?? '');
$text = trim($_POST['text'] ?? '');
$removeImage = isset($_POST['remove_image']) && $_POST['remove_image'] == '1';

$cards = [];
if (file_exists($cardsFile)) { $cards = json_decode(@file_get_contents($cardsFile), true) ?? []; }
// ensure at least index exists
while (count($cards) <= $index) { $cards[] = ['title'=>'','text'=>'','image'=>'']; }
$old = $cards[$index]['image'] ?? '';

// handle upload
if (isset($_FILES['bg']) && $_FILES['bg']['error'] === UPLOAD_ERR_OK) {
    if ($imagesDir === false) { $_SESSION['status'] = 'Images directory missing.'; header('Location: ../content_management.php'); exit; }
    $file = $_FILES['bg'];
    $allowed = ['png','jpg','jpeg','gif','webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $max = 5 * 1024 * 1024;
    if (!in_array($ext, $allowed)) { $_SESSION['status'] = 'Invalid image type.'; header('Location: ../content_management.php'); exit; }
    if ($file['size'] > $max) { $_SESSION['status'] = 'Image too large (max 5MB).'; header('Location: ../content_management.php'); exit; }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (strpos($mime, 'image/') !== 0) { $_SESSION['status'] = 'Uploaded file is not an image.'; header('Location: ../content_management.php'); exit; }

    $newName = 'card-' . $index . '-' . time() . '.' . $ext;
    $dest = $imagesDir . DIRECTORY_SEPARATOR . $newName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) { logCard('move failed ' . $file['tmp_name']); $_SESSION['status'] = 'Failed to move uploaded image.'; header('Location: ../content_management.php'); exit; }
    @chmod($dest, 0644);
    $cards[$index]['image'] = 'image/' . $newName;
    // delete old if under image dir
    if ($old) {
        $abs = realpath(__DIR__ . '/../' . $old);
        if ($abs && strpos($abs, realpath(__DIR__ . '/../image')) === 0) { @unlink($abs); logCard('deleted old ' . $abs); }
    }
}

if ($removeImage && $old) {
    $abs = realpath(__DIR__ . '/../' . $old);
    if ($abs && strpos($abs, realpath(__DIR__ . '/../image')) === 0) { @unlink($abs); logCard('removed ' . $abs); }
    $cards[$index]['image'] = '';
}

$cards[$index]['title'] = $title;
$cards[$index]['text'] = $text;
file_put_contents($cardsFile, json_encode($cards, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
logCard('Updated card ' . $index . ' title=' . $title);
$_SESSION['status'] = 'Card updated.';
header('Location: ../content_management.php');
exit;
?>