<?php
// Database configuration
$servername = "localhost";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "bose";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $name = htmlspecialchars(trim($_POST['name']));
    $contact = preg_replace('/[^0-9]/', '', $_POST['contact']);
    $roll_number = htmlspecialchars(trim($_POST['roll_number']));
    $address = htmlspecialchars(trim($_POST['address']));
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (!preg_match('/^[0-9]{10}$/', $contact)) {
        $errors[] = "Invalid contact number (must be 10 digits)";
    }
    
    if (empty($roll_number)) {
        $errors[] = "Roll number is required";
    }
    
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO students (name, contact, roll_number, address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $contact, $roll_number, $address);
        
        if ($stmt->execute()) {
            // Success - redirect to thank you page
            header("<Location:records class="php"></Location:records> ");
            exit();
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
    
    // If errors, show them (you might want to pass these back to the form)
    if (!empty($errors)) {
        session_start();
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        header("Location: index.html");
        exit();
    }
}

$conn->close();
?>