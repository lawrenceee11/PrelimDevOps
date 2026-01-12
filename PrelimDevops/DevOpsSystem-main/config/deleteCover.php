<?php
session_start();
$imagesDir = realpath(__DIR__ . '/../image');
$coversFile = __DIR__ . '/covers.json';
$logFile = __DIR__ . '/logo-update.log';
function logCover($msg) { global $logFile; @file_put_contents($logFile, date('Y-m-d H:i:s') . " - COVER: " . $msg . PHP_EOL, FILE_APPEND); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../content_management.php');
    exit;
}

$path = $_POST['path'] ?? '';
$path = trim($path);
if ($path === '') {
    $_SESSION['status'] = 'Invalid cover.';
    header('Location: ../content_management.php');
    exit;
}

// Remove from covers.json
$covers = [];
if (file_exists($coversFile)) {
    $raw = @file_get_contents($coversFile);
    $covers = json_decode($raw, true) ?? [];
}
$new = [];
foreach ($covers as $c) {
    // support both legacy string entries and object entries
    if (is_string($c) && $c === $path) { continue; }
    if (is_array($c) && (($c['path'] ?? '') === $path)) { continue; }
    $new[] = $c;
}
file_put_contents($coversFile, json_encode($new, JSON_PRETTY_PRINT));

// Delete file if exists under image/
$abs = realpath(__DIR__ . '/../' . $path);
if ($abs && strpos($abs, realpath(__DIR__ . '/../image')) === 0) {
    @unlink($abs);
    logCover('Deleted cover file: ' . $abs);
}

$_SESSION['status'] = 'Cover removed.';
header('Location: ../content_management.php');
exit;
?>