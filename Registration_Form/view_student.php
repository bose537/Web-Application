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

// Fetch student details
$stmt = $conn->prepare("SELECT name, contact, roll_number, address, registered_at FROM students WHERE id = ?");
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
    <title>Student Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 py-4 px-6">
                <h1 class="text-2xl font-bold text-white">Student Details</h1>
                <p class="text-blue-100">Complete information about the student</p>
            </div>
            
            <div class="p-6">
                <div class="flex justify-between mb-6">
                    <a href="records.php" class="text-blue-600 hover:text-blue-800 flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Records
                    </a>
                    <div class="flex space-x-2">
                        <a href="edit_student.php?id=<?php echo $id; ?>" class="text-yellow-600 hover:text-yellow-800 flex items-center">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Personal Information</h3>
                        <dl class="space-y-3">
                            <div class="flex items-start">
                                <dt class="text-sm font-medium text-gray-500 w-32">Name:</dt>
                                <dd class="text-sm text-gray-900"><?php echo htmlspecialchars($student['name']); ?></dd>
                            </div>
                            <div class="flex items-start">
                                <dt class="text-sm font-medium text-gray-500 w-32">Roll Number:</dt>
                                <dd class="text-sm text-gray-900"><?php echo htmlspecialchars($student['roll_number']); ?></dd>
                            </div>
                            <div class="flex items-start">
                                <dt class="text-sm font-medium text-gray-500 w-32">Contact:</dt>
                                <dd class="text-sm text-gray-900"><?php echo htmlspecialchars($student['contact']); ?></dd>
                            </div>
                            <div class="flex items-start">
                                <dt class="text-sm font-medium text-gray-500 w-32">Registered On:</dt>
                                <dd class="text-sm text-gray-900">
                                    <?php echo date('M j, Y g:i A', strtotime($student['registered_at'])); ?>
                                </dd>
                            </div>
                        </dl>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Address</h3>
                        <div class="text-sm text-gray-900 whitespace-pre-line">
                            <?php echo htmlspecialchars($student['address']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>