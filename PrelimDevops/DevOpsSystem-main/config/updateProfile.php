<?php
session_start();
require_once 'dbcon.php'; // not used but keep consistent includes
$siteFile = __DIR__ . '/site.php';
$logFile = __DIR__ . '/logo-update.log';
function logProfile($msg) {
    global $logFile;
    @file_put_contents($logFile, date('Y-m-d H:i:s') . " - PROFILE: " . $msg . PHP_EOL, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../content_management.php');
    exit;
}

// Retrieve and sanitize inputs
$inst = trim($_POST['inst'] ?? '');
$location = trim($_POST['location'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$tty = trim($_POST['tty'] ?? '');

if ($inst === '' || $email === '') {
    $_SESSION['status'] = 'Institute name and email are required.';
    header('Location: ../content_management.php');
    exit;
}

// Basic sanitize
$inst = str_replace("'", "\'", $inst);
$location = str_replace("'", "\'", $location);
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$phone = preg_replace('/[^0-9\+\-\s]/', '', $phone);
$tty = preg_replace('/[^0-9\+\-\s]/', '', $tty);

$content = @file_get_contents($siteFile);
if ($content === false) {
    logProfile('Failed to open site.php');
    $_SESSION['status'] = 'Unable to open site config.';
    header('Location: ../content_management.php');
    exit;
}

// Remove any existing profile blocks or assignments for these keys
$lines = preg_split('/\R/', $content);
$keep = [];
foreach ($lines as $line) {
    // Skip lines that are the profile header comment or any assignment for the profile keys
    if (preg_match('/^\s*\/\/\s*site profile updated by admin/i', $line)) continue;
    if (preg_match('/\$SITE_INST\s*=|\$SITE_LOCATION\s*=|\$SITE_EMAIL\s*=|\$SITE_PHONE\s*=|\$SITE_TTY\s*=/', $line)) continue;
    $keep[] = $line;
}

// Build the new profile block
$insert = "// site profile updated by admin (auto)\n";
$insert .= "\$SITE_INST = '". $inst ."';\n";
$insert .= "\$SITE_LOCATION = '". $location ."';\n";
$insert .= "\$SITE_EMAIL = '". $email ."';\n";
$insert .= "\$SITE_PHONE = '". $phone ."';\n";
$insert .= "\$SITE_TTY = '". $tty ."';\n";

// Insert after opening <?php if present
if (isset($keep[0]) && preg_match('/^<\?php\b/', $keep[0])) {
    array_splice($keep, 1, 0, $insert);
} else {
    array_unshift($keep, '<?php', $insert);
}

$newContent = implode("\n", $keep) . "\n";
$bytes = @file_put_contents($siteFile, $newContent);
if ($bytes === false) {
    logProfile('Failed to write site.php');
    $_SESSION['status'] = 'Failed to update site config. Check file permissions.';
    header('Location: ../content_management.php');
    exit;
}
logProfile('Wrote profile to site.php bytes=' . $bytes);logProfile('Profile updated: inst='. $inst . ' email=' . $email);
$_SESSION['status'] = 'Profile updated successfully.';
// Mark that profile was just saved so we can remove the Preview button on next load
$_SESSION['profile_saved'] = true;
header('Location: ../content_management.php');
exit;
?>