<?php
session_start();
require_once __DIR__ . '/dbcon.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../studentpage.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
if (empty($username) || !isset($_SESSION['username']) || $_SESSION['username'] !== $username) {
    $_SESSION['status'] = 'Unauthorized request.';
    $_SESSION['status_type'] = 'danger';
    header('Location: ../studentpage.php');
    exit;
}

$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? '');
$phonenumber = trim($_POST['phonenumber'] ?? '');
// Normalize phone: keep digits only and require exactly 10 digits for local numbers
$phonenumber = preg_replace('/\D+/', '', $phonenumber);
if ($phonenumber !== '') {
    $phonenumber = substr($phonenumber, 0, 10); // limit to 10 digits
    if (strlen($phonenumber) !== 10) {
        $_SESSION['status'] = 'Phone number must be exactly 10 digits.';
        $_SESSION['status_type'] = 'danger';
        header('Location: ../studentpage.php');
        exit;
    }
} else {
    $phonenumber = '';
} 
$section = trim($_POST['section'] ?? '');

if ($firstname === '' || $lastname === '' || $email === '') {
    $_SESSION['status'] = 'Please provide first name, last name and email.';
    $_SESSION['status_type'] = 'danger';
    header('Location: ../studentpage.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['status'] = 'Invalid email address.';
    $_SESSION['status_type'] = 'danger';
    header('Location: ../studentpage.php');
    exit;
}

// Prepare to handle profile picture if uploaded
$profilePicSaved = false;
$profilePicPath = null;
$column_exists = false;
// check if profile_pic column exists
$checkCol = $conn->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'enroll' AND COLUMN_NAME = 'profile_pic' LIMIT 1");
if ($checkCol) {
    $checkCol->execute();
    $checkCol->store_result();
    $column_exists = $checkCol->num_rows > 0;
    $checkCol->close();
}

if (!empty($_FILES['profile_pic']) && isset($_FILES['profile_pic']['tmp_name']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $f = $_FILES['profile_pic'];
    if ($f['size'] > 2 * 1024 * 1024) {
        $_SESSION['status'] = 'Profile picture must be 2MB or smaller.';
        $_SESSION['status_type'] = 'danger';
        header('Location: ../studentpage.php');
        exit;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $f['tmp_name']);
    finfo_close($finfo);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
    if (!isset($allowed[$mime])) {
        $_SESSION['status'] = 'Invalid image format. Use JPG, PNG or GIF.';
        $_SESSION['status_type'] = 'danger';
        header('Location: ../studentpage.php');
        exit;
    }
    $ext = $allowed[$mime];
    $dir = __DIR__ . '/../image/profiles/';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $basename = preg_replace('/[^a-z0-9_\-]/i', '_', $username);
    $filename = $basename . '_' . time() . '.' . $ext;
    $dest = $dir . $filename;
    if (move_uploaded_file($f['tmp_name'], $dest)) {
        $profilePicSaved = true;
        $profilePicPath = 'image/profiles/' . $filename;
        // if column exists, remove old file after we successfully update DB later
    } else {
        $_SESSION['status'] = 'Failed to save uploaded profile picture.';
        $_SESSION['status_type'] = 'danger';
        header('Location: ../studentpage.php');
        exit;
    }
}

// Update profile fields
$stmt = $conn->prepare("UPDATE enroll SET firstname = ?, lastname = ?, email = ?, phonenumber = ?, section = ? WHERE username = ? LIMIT 1");
if (!$stmt) {
    $_SESSION['status'] = 'Database error: ' . $conn->error;
    $_SESSION['status_type'] = 'danger';
    header('Location: ../studentpage.php');
    exit;
}
$stmt->bind_param('ssssss', $firstname, $lastname, $email, $phonenumber, $section, $username);
$execOk = $stmt->execute();

if ($execOk) {
    // update profile_pic column if we saved a file and column exists
    if ($profilePicSaved) {
        if ($column_exists) {
            // get old path to delete
            $oldStmt = $conn->prepare("SELECT profile_pic FROM enroll WHERE username = ? LIMIT 1");
            if ($oldStmt) {
                $oldStmt->bind_param('s', $username);
                $oldStmt->execute();
                $old = $oldStmt->get_result()->fetch_assoc();
                $oldPath = $old['profile_pic'] ?? null;
                $oldStmt->close();
            }

            $up = $conn->prepare("UPDATE enroll SET profile_pic = ? WHERE username = ? LIMIT 1");
            if ($up) {
                $up->bind_param('ss', $profilePicPath, $username);
                $up->execute();
                $up->close();
                // delete old file if exists and is not the default svg
                if (!empty($oldPath) && file_exists(__DIR__ . '/../' . $oldPath) && basename($oldPath) !== 'default_avatar.svg') {
                    @unlink(__DIR__ . '/../' . $oldPath);
                }
            }
        } else {
            // Column missing: keep file but notify user to run migration
            $_SESSION['status'] = 'Profile updated. Image uploaded but DB not configured to store it; run the ALTER TABLE migration.';
            $_SESSION['status_type'] = 'warning';
            header('Location: ../studentpage.php');
            exit;
        }
    }

    if (!isset($_SESSION['status'])) {
        $_SESSION['status'] = 'Profile updated successfully.';
        $_SESSION['status_type'] = 'success';
    }
} else {
    $_SESSION['status'] = 'Failed to update profile: ' . $stmt->error;
    $_SESSION['status_type'] = 'danger';
}
$stmt->close();
header('Location: ../studentpage.php');
exit;