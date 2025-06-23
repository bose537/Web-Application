<?php
// Start session
session_start();

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

// Initialize variables
$request = [];
$error = '';
$success = '';

// Get the request ID from URL parameter
$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the specific service request
$sql = "SELECT * FROM service_requests WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Service request not found.");
}

$request = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $customer_name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $service_type = trim($_POST['service_type']);
    $issue_description = trim($_POST['description']);
    $status = trim($_POST['status']);
    
    // Additional optional fields
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $device_model = isset($_POST['device_model']) ? trim($_POST['device_model']) : '';
    $serial_number = isset($_POST['serial_number']) ? trim($_POST['serial_number']) : '';
    $technician_notes = isset($_POST['technician_notes']) ? trim($_POST['technician_notes']) : '';
    
        // Update the request in database
        $sql = "UPDATE service_requests SET 
                customer_name = ?, 
                email = ?, 
                phone = ?, 
                device_type = ?, 
                issue_description = ?, 
                status = ?";
        
        // Add optional fields to query if they exist in the table
        $params = [$customer_name, $email, $phone, $device_type, $issue_description, $status];
        $types = "ssssss";
        
        if (isset($request['address'])) {
            $sql .= ", address = ?";
            $params[] = $address;
            $types .= "s";
        }
        if (isset($request['device_model'])) {
            $sql .= ", device_model = ?";
            $params[] = $device_model;
            $types .= "s";
        }
        if (isset($request['serial_number'])) {
            $sql .= ", serial_number = ?";
            $params[] = $serial_number;
            $types .= "s";
        }
        if (isset($request['technician_notes'])) {
            $sql .= ", technician_notes = ?";
            $params[] = $technician_notes;
            $types .= "s";
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $request_id;
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $success = "Service request updated successfully!";
            // Refresh the request data
            $stmt = $conn->prepare("SELECT * FROM service_requests WHERE id = ?");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $request = $result->fetch_assoc();
        } else {
            $error = "Error updating request: " . $conn->error;
        }
    }
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service Request - <?php echo htmlspecialchars($request['id']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8 fade-in">
            <!-- Header -->
            <div class="bg-blue-600 py-4 px-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-white">Edit Service Request</h1>
                    <p class="text-blue-100">Request ID: <?php echo htmlspecialchars($request['id']); ?></p>
                </div>
                <div>
                    <?php 
                    $statusClass = '';
                    $statusIcon = '';
                    switch($request['status']) {
                        case 'Completed':
                            $statusClass = 'bg-green-100 text-green-800';
                            $statusIcon = 'fa-check-circle';
                            break;
                        case 'In Progress':
                            $statusClass = 'bg-yellow-100 text-yellow-800';
                            $statusIcon = 'fa-spinner';
                            break;
                        case 'Pending':
                            $statusClass = 'bg-blue-100 text-blue-800';
                            $statusIcon = 'fa-clock';
                            break;
                        case 'Cancelled':
                            $statusClass = 'bg-red-100 text-red-800';
                            $statusIcon = 'fa-times-circle';
                            break;
                        default:
                            $statusClass = 'bg-gray-100 text-gray-800';
                            $statusIcon = 'fa-info-circle';
                    }
                    ?>
                    <span class="status-badge <?php echo $statusClass; ?>">
                        <i class="fas <?php echo $statusIcon; ?>"></i>
                        <?php echo htmlspecialchars($request['status']); ?>
                    </span>
                </div>
            </div>
            
            <!-- Back button -->
            <div class="p-4 border-b border-gray-200">
                <a href="view_request.php?id=<?php echo $request['id']; ?>" class="text-blue-600 hover:text-blue-800 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Request Details
                </a>
            </div>
            
            <!-- Error/Success messages -->
            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Edit Form -->
            <form method="POST" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Customer Information -->
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-gray-800 border-b pb-2">
                            <i class="fas fa-user mr-2 text-blue-600"></i>Customer Information
                        </h2>
                        
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                            <input type="text" id="name" name="name" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                value="<?php echo htmlspecialchars($request['name']); ?>">
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                            <input type="email" id="email" name="email" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                value="<?php echo htmlspecialchars($request['email']); ?>">
                        </div>
                        
                        <div>
                            <label for="contact" class="block text-sm font-medium text-gray-700">contact *</label>
                            <input type="tel" id="contact" name="contact" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                value="<?php echo htmlspecialchars($request['contact']); ?>">
                        </div>
                        
                        <?php if (isset($request['address'])): ?>
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea id="address" name="address" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($request['address']); ?></textarea>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Service Information -->
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-gray-800 border-b pb-2">
                            <i class="fas fa-tools mr-2 text-blue-600"></i>Service Information
                        </h2>
                        
                        <div>
                            <label for="service_type" class="block text-sm font-medium text-gray-700">service_type*</label>
                            <input type="text" id="service_type" name="service_type" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                value="<?php echo htmlspecialchars($request['service_type']); ?>">
                        </div>
                        
                        <?php if (isset($request['description'])): ?>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">description</label>
                            <input type="text" id="description" name="description"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                value="<?php echo htmlspecialchars($request['description']); ?>">
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($request['serial_number'])): ?>
                        <div>
                            <label for="serial_number" class="block text-sm font-medium text-gray-700">Serial Number</label>
                            <input type="text" id="serial_number" name="serial_number"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                value="<?php echo htmlspecialchars($request['serial_number']); ?>">
                        </div>
                        <?php endif; ?>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                            <select id="status" name="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="Pending" <?php echo $request['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="In Progress" <?php echo $request['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Completed" <?php echo $request['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="Cancelled" <?php echo $request['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Issue Description -->
                    <div class="md:col-span-2 space-y-4">
                        <h2 class="text-lg font-semibold text-gray-800 border-b pb-2">
                            <i class="fas fa-exclamation-circle mr-2 text-blue-600"></i>Issue Description *
                        </h2>
                        <div>
                            <textarea id="description" name="description" rows="4" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($request['issue_description']); ?></textarea>
                        </div>
                    </div>
                    
                    <?php if (isset($request['technician_notes'])): ?>
                    <!-- Technician Notes -->
                    <div class="md:col-span-2 space-y-4">
                        <h2 class="text-lg font-semibold text-gray-800 border-b pb-2">
                            <i class="fas fa-clipboard mr-2 text-blue-600"></i>Technician Notes
                        </h2>
                        <div>
                            <textarea id="technician_notes" name="technician_notes" rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($request['technician_notes']); ?></textarea>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Form Actions -->
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="view_request.php?id=<?php echo $request['id']; ?>" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="view_request" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>