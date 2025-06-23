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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $name = htmlspecialchars(trim($_POST['name']));
    $contact = preg_replace('/[^0-9]/', '', $_POST['contact']);
    $roll_number = htmlspecialchars(trim($_POST['roll_number']));
    $address = htmlspecialchars(trim($_POST['address']));
    
    // Update student record
    $stmt = $conn->prepare("UPDATE students SET name=?, contact=?, roll_number=?, address=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $contact, $roll_number, $address, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Student record updated successfully";
        header("Location: view_student.php?id=".$id);
        exit();
    } else {
        $_SESSION['error'] = "Error updating record: " . $stmt->error;
    }
    
    $stmt->close();
}

// Fetch student details
$stmt = $conn->prepare("SELECT name, contact, roll_number, address FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    header("Location: records.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 py-4 px-6">
                <h1 class="text-2xl font-bold text-white">Edit Student</h1>
                <p class="text-blue-100">Update student information</p>
            </div>
            
            <div class="p-6">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                
                <form action="edit_student.php?id=<?php echo $id; ?>" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" id="name" name="name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   value="<?php echo htmlspecialchars($student['name']); ?>">
                        </div>
                        
                        <div>
                            <label for="contact" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                            <input type="tel" id="contact" name="contact" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   pattern="[0-9]{10}" title="10 digit phone number"
                                   value="<?php echo htmlspecialchars($student['contact']); ?>">
                        </div>
                        
                        <div>
                            <label for="roll_number" class="block text-sm font-medium text-gray-700 mb-1">Roll Number</label>
                            <input type="text" id="roll_number" name="roll_number" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   value="<?php echo htmlspecialchars($student['roll_number']); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea id="address" name="address" rows="3" required 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($student['address']); ?></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <a href="view_student.php?id=<?php echo $id; ?>" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Update Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>