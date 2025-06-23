<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_registration";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get student ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Delete student record
$stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Student record deleted successfully";
} else {
    $_SESSION['error'] = "Error deleting record: " . $stmt->error;
}

$stmt->close();
$conn->close();

header("Location: records.php");
exit();
?>