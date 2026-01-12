<?php
session_start();
$coversFile = __DIR__ . '/covers.json';
$logFile = __DIR__ . '/logo-update.log';
function logCover($msg) { global $logFile; @file_put_contents($logFile, date('Y-m-d H:i:s') . " - COVER: " . $msg . PHP_EOL, FILE_APPEND); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../content_management.php');
    exit;
}

$path = trim($_POST['path'] ?? '');
$title = trim($_POST['title'] ?? '');
$caption = trim($_POST['caption'] ?? '');

if ($path === '') {
    $_SESSION['status'] = 'Invalid cover.';
    header('Location: ../content_management.php');
    exit;
}

$covers = [];
if (file_exists($coversFile)) {
    $covers = json_decode(file_get_contents($coversFile), true) ?? [];
}
$changed = false;
foreach ($covers as &$c) {
    if (is_string($c) && $c === $path) {
        // convert string to object
        $c = ['path' => $path, 'title' => $title, 'caption' => $caption];
        $changed = true;
        break;
    } elseif (is_array($c) && ($c['path'] ?? '') === $path) {
        $c['title'] = $title;
        $c['caption'] = $caption;
        $changed = true;
        break;
    }
}
if ($changed) {
    file_put_contents($coversFile, json_encode($covers, JSON_PRETTY_PRINT));
    logCover('Updated cover ' . $path . ' title=' . $title);
    $_SESSION['status'] = 'Cover updated.';
} else {
    $_SESSION['status'] = 'Cover not found.';
}
header('Location: ../content_management.php');
exit;
?>