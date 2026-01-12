<?php
// Run this script once to add a `section` column to the `enroll` table.
// Usage: point your browser at /config/addSectionColumn.php or run via PHP CLI.
require_once __DIR__ . '/dbcon.php';

$sql = "ALTER TABLE enroll ADD COLUMN section VARCHAR(10) DEFAULT NULL";
if ($conn->query($sql) === TRUE) {
  echo "Column 'section' added successfully.";
} else {
  echo "Error adding column (it might already exist): " . $conn->error;
}

$conn->close();
?>