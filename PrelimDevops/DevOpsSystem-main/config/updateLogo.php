<?php
session_start();
// Updates the $SITE_LOGO value in config/site.php by handling an uploaded image
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../content_management.php');
    exit;
}

$siteFile = __DIR__ . '/site.php';
$imagesDir = realpath(__DIR__ . '/../image');
$logFile = __DIR__ . '/logo-update.log';
function logDebug($msg) {
    global $logFile;
    @file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $msg . PHP_EOL, FILE_APPEND);
}
if ($imagesDir === false) {
    logDebug('Images directory not found: ' . __DIR__ . '/../image');
    $_SESSION['status'] = 'Images directory not found.';
    header('Location: ../content_management.php');
    exit;
} else {
    logDebug('Images directory: ' . $imagesDir);
}

// Reset to default
if (isset($_POST['reset'])) {
    // set to default logo name
    $default = 'image/Logo.png';
    $content = file_get_contents($siteFile);
    if ($content === false) {
        $_SESSION['status'] = 'Unable to open site config.';
        header('Location: ../content_management.php');
        exit;
    }
    // If a previous custom logo exists, remove it (safety: only from image/ and not the default)
    if (preg_match("/\$SITE_LOGO\s*=\s*'([^']*)';/", $content, $m)) {
        $old = $m[1];
        if (strpos($old, 'image/') === 0 && basename($old) !== basename($default)) {
            $oldPath = realpath(__DIR__ . '/../' . $old);
            if ($oldPath && strpos($oldPath, realpath(__DIR__ . '/../image')) === 0) {
                @unlink($oldPath);
            }
        }
    }

    // Remove any existing $SITE_LOGO assignments and insert a single assignment after <?php
    $content = preg_replace("/\$SITE_LOGO\s*=\s*'[^']*';\s*/", '', $content);
    $newContent = preg_replace("/(<\?php\s*)/", "\\1\n// site logo updated by admin (auto)\n\$SITE_LOGO = '{$default}';\n", $content, 1);
    if ($newContent === null) $newContent = $content;
    file_put_contents($siteFile, $newContent);
    $_SESSION['status'] = 'Logo reset to default.';
    header('Location: ../content_management.php');
    exit;
}

if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['status'] = 'No file uploaded or upload error.';
    header('Location: ../content_management.php');
    exit;
}

$allowed = ['png','jpg','jpeg','gif','webp'];
$maxSize = 2 * 1024 * 1024; // 2MB
$file = $_FILES['logo'];
if ($file['size'] > $maxSize) {
    $_SESSION['status'] = 'File too large (max 2MB).';
    header('Location: ../content_management.php');
    exit;
}

// Basic extension check
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    $_SESSION['status'] = 'Invalid file type. Allowed: png,jpg,jpeg,gif,webp.';
    header('Location: ../content_management.php');
    exit;
}

// Validate MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if (strpos($mime, 'image/') !== 0) {
    $_SESSION['status'] = 'Uploaded file is not an image.';
    header('Location: ../content_management.php');
    exit;
}

// Move file into image directory with safe name
$newName = 'logo-' . time() . '.' . $ext;
$destPath = $imagesDir . DIRECTORY_SEPARATOR . $newName;
logDebug('Uploading file ' . $file['name'] . ' tmp=' . $file['tmp_name'] . ' -> dest=' . $destPath);
if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    logDebug('move_uploaded_file failed for ' . $file['tmp_name']);
    $_SESSION['status'] = 'Failed to move uploaded file.';
    header('Location: ../content_management.php');
    exit;
}

// Set 0644 permissions if possible
if (!@chmod($destPath, 0644)) {
    logDebug('chmod failed for ' . $destPath);
}

// Update config/site.php to point to new logo (relative path)
$relativePath = 'image/' . $newName;
$content = file_get_contents($siteFile);
if ($content === false) {
    logDebug('Failed to read site config: ' . $siteFile);
    $_SESSION['status'] = 'Unable to open site config.';
    header('Location: ../content_management.php');
    exit;
}
logDebug('Prepared to set SITE_LOGO to ' . $relativePath);

// Remove any existing $SITE_LOGO assignments robustly
// If there is a previous custom logo file (non-default) try to delete it
if (preg_match("/\$SITE_LOGO\s*=\s*'([^']*)';/", $content, $m)) {
    $old = $m[1];
    if (strpos($old, 'image/') === 0 && basename($old) !== 'Logo.png') {
        $oldPath = realpath(__DIR__ . '/../' . $old);
        if ($oldPath && strpos($oldPath, realpath(__DIR__ . '/../image')) === 0) {
            @unlink($oldPath);
            logDebug('Deleted old logo file: ' . $oldPath);
        }
    }
}

// Build a cleaned version of the site config: remove all lines that assign $SITE_LOGO
$lines = preg_split('/\R/', $content);
$keep = [];
foreach ($lines as $line) {
    // skip lines that contain $SITE_LOGO assignment
    if (preg_match('/\$SITE_LOGO\s*=\s*[^;]+;/', $line)) {
        continue;
    }
    $keep[] = $line;
}

// Recompose the file with a single $SITE_LOGO assignment near the top after <?php
$insert = "// site logo updated by admin (auto)\n\$SITE_LOGO = '{$relativePath}';";
// Find the position after the opening <?php line
if (isset($keep[0]) && preg_match('/^<\?php\b/', $keep[0])) {
    array_splice($keep, 1, 0, $insert);
} else {
    array_unshift($keep, '<?php', $insert);
}

$newContent = implode("\n", $keep) . "\n";

$bytesWritten = @file_put_contents($siteFile, $newContent);
if ($bytesWritten === false) {
    logDebug('file_put_contents FAILED for ' . $siteFile . ' (destPath=' . $destPath . ')');
    $_SESSION['status'] = 'Failed to write site config. Check file permissions.';
    // rollback: remove the uploaded file we added
    @unlink($destPath);
    header('Location: ../content_management.php');
    exit;
}
logDebug('Wrote cleaned site config file, bytes=' . $bytesWritten . ' newLogo=' . $relativePath);

// Success — record last updated time (optional) and redirect
$_SESSION['status'] = 'Logo updated successfully.';
header('Location: ../content_management.php');
exit;
?>