<?php
session_start();
require_once 'dbcon.php';

/* =====================
   1️⃣ Collect & sanitize input
===================== */
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$elemName = $_POST['elemName'] ?? '';
$elemYear = $_POST['elemYear'] ?? '';
$juniorName = $_POST['juniorName'] ?? '';
$juniorYear = $_POST['juniorYear'] ?? '';
$seniorName = $_POST['seniorName'] ?? '';
$seniorYear = $_POST['seniorYear'] ?? '';

$lastname = $_POST['lastname'] ?? '';
$firstname = $_POST['firstname'] ?? '';
$middlename = $_POST['middlename'] ?? '';
$sex = $_POST['sex'] ?? '';
$dob = $_POST['dob'] ?? '';

$phonenumber = $_POST['phoneNumber'] ?? '';
$guardianName = $_POST['guardianName'] ?? '';
$guardianPhoneNumber = $_POST['guardianPhoneNumber'] ?? '';

// Normalize phone inputs (keep digits only, cap length to 10)
$phonenumber = preg_replace('/\D+/', '', $phonenumber);
$phonenumber = $phonenumber !== '' ? substr($phonenumber, 0, 10) : '';
$guardianPhoneNumber = preg_replace('/\D+/', '', $guardianPhoneNumber);
$guardianPhoneNumber = $guardianPhoneNumber !== '' ? substr($guardianPhoneNumber, 0, 10) : '';

// Validate phone format (require 10 digits)
if ($phonenumber === '' || !preg_match('/^[0-9]{10}$/', $phonenumber)) {
    $_SESSION['status'] = "Please provide a valid 10-digit phone number for the student.";
    header("Location: ../enroll.php");
    exit;
}
if ($guardianPhoneNumber === '' || !preg_match('/^[0-9]{10}$/', $guardianPhoneNumber)) {
    $_SESSION['status'] = "Please provide a valid 10-digit phone number for the guardian.";
    header("Location: ../enroll.php");
    exit;
}
$guardianAddress = $_POST['guardianAddress'] ?? '';

$course = $_POST['course'] ?? '';
$year = $_POST['year'] ?? '';
$section = $_POST['section'] ?? null;

$appointment_date = $_POST['appointment_date'] ?? '';
$time = $_POST['time'] ?? '';
$appointment_id = isset($_POST['appointmentID']) ? intval($_POST['appointmentID']) : 0;

/* =====================
   2️⃣ Basic validation
===================== */
if (empty($username) || empty($password)) {
    $_SESSION['status'] = "Username and password are required.";
    header("Location: ../enroll.php");
    exit;
}

/* =====================
   3️⃣ CHECK DUPLICATE USERNAME
===================== */
$check = $conn->prepare("SELECT id FROM enroll WHERE username = ? LIMIT 1");
$check->bind_param("s", $username);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->close();
    $_SESSION['error'] = "Username already exists. Please choose another."; // ❌ Use 'error' instead of 'status'
    header("Location: ../enroll.php");
    exit;
}

$check->close();

/* =====================
   4️⃣ Hash password
===================== */
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

/* =====================
   5️⃣ INSERT DATA (SAFE)
===================== */
$stmt = $conn->prepare("
    INSERT INTO enroll (
        username, password, email,
        elemName, elemYear,
        juniorName, juniorYear,
        seniorName, seniorYear,
        lastname, firstname, middlename,
        sex, dob, phonenumber,
        guardianName, guardianPhoneNumber, guardianAddress,
        course, year, section,
        status, appointment_date, time
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

$status = "PENDING";

$stmt->bind_param(
    "ssssssssssssssssssssssss",
    $username, $hashedPassword, $email,
    $elemName, $elemYear,
    $juniorName, $juniorYear,
    $seniorName, $seniorYear,
    $lastname, $firstname, $middlename,
    $sex, $dob, $phonenumber,
    $guardianName, $guardianPhoneNumber, $guardianAddress,
    $course, $year, $section,
    $status, $appointment_date, $time
);

if ($stmt->execute()) {

    /* =====================
       6️⃣ Decrement appointment slots
    ===================== */
    if ($appointment_id > 0) {
        $u = $conn->prepare(
            "UPDATE appointments 
             SET slots = GREATEST(slots - 1, 0) 
             WHERE id = ?"
        );
        $u->bind_param("i", $appointment_id);
        $u->execute();
        $u->close();
    }

    $_SESSION['status'] = "Enrollment Successful!";
    header("Location: ../enroll.php");
    exit;

} else {
    $_SESSION['status'] = "Enrollment failed. Please try again.";
    header("Location: ../enroll.php");
    exit;
}

$stmt->close();
$conn->close();
