<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
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
    $service_type = htmlspecialchars(trim($_POST['service_type']));
    $name = htmlspecialchars(trim($_POST['name']));
    $contact = preg_replace('/[^0-9]/', '', $_POST['contact']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $address = htmlspecialchars(trim($_POST['address']));
    $description = htmlspecialchars(trim($_POST['description']));
    
    // Get specific service based on type
    if ($service_type == 'electrical') {
        $specific_service = htmlspecialchars(trim($_POST['electrical_service']));
    } else {
        $specific_service = htmlspecialchars(trim($_POST['plumbing_service']));
    }
    
    // Validate required fields
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (!preg_match('/^[0-9]{10}$/', $contact)) {
        $errors[] = "Invalid contact number (must be 10 digits)";
    }
    
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    
    if (empty($service_type)) {
        $errors[] = "Service type is required";
    }
    
    if (empty($specific_service)) {
        $errors[] = "Specific service is required";
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO service_requests 
                               (service_type, specific_service, name, contact, email, address, description, request_date) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssss", $service_type, $specific_service, $name, $contact, $email, $address, $description);
        
        if ($stmt->execute()) {
            // Success - redirect to thank you page
            header("Location: success.php");
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