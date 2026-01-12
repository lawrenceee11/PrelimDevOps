<?php
SESSION_START();
require_once 'dbcon.php';

$name = $_POST['name'];
$email = $_POST['email'];
$message = $_POST['message'];

$sql = "INSERT INTO contact (name, email, message, date_submitted)
VALUES ('$name', '$email', '$message', NOW())";

if ($conn->query($sql) === TRUE) {
  $_SESSION['success'] = "Your message has been sent successfully!";
  header("Location: ../contact.php");
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>