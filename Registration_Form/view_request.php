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

// Close statement and connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Service Request - <?php echo htmlspecialchars($request['id']); ?></title>
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
        .status-badge i {
            margin-right: 0.25rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8 fade-in">
            <!-- Header -->
            <div class="bg-blue-600 py-4 px-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-white">Service Request Details</h1>
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
                <a href="records.php" class="text-blue-600 hover:text-blue-800 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to All Requests
                </a>
            </div>
            
            <!-- Request Details -->
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Customer Information -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                        <i class="fas fa-user mr-2 text-blue-600"></i>Customer Information
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">Full Name</p>
                            <p class="font-medium"><?php echo htmlspecialchars($request['name']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium"><?php echo htmlspecialchars($request['email']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">service_type</p>
                            <p class="font-medium"><?php echo htmlspecialchars($request['service_type']); ?></p>
                        </div>
                        <?php if (isset($request['address']) && !empty($request['address'])): ?>
                        <div>
                            <p class="text-sm text-gray-500">Address</p>
                            <p class="font-medium"><?php echo htmlspecialchars($request['address']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Service Information -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                        <i class="fas fa-tools mr-2 text-blue-600"></i>Service Information
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <p class="font-medium"><?php echo htmlspecialchars($request['status']); ?></p>
                        </div>
                        <?php if (isset($request['device_model']) && !empty($request['device_model'])): ?>
                        <div>
                            <p class="text-sm text-gray-500">Device Model</p>
                            <p class="font-medium"><?php echo htmlspecialchars($request['device_model']); ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($request['serial_number']) && !empty($request['serial_number'])): ?>
                        <div>
                            <p class="text-sm text-gray-500">Serial Number</p>
                            <p class="font-medium"><?php echo htmlspecialchars($request['serial_number']); ?></p>
                        </div>
                        <?php endif; ?>
                        <div>
                            <p class="text-sm text-gray-500">Request Date</p>
                            <p class="font-medium">
                                <?php 
                                $dateColumn = isset($request['created_at']) ? 'created_at' : 
                                            (isset($request['date_created']) ? 'date_created' : 
                                            (isset($request['timestamp']) ? 'timestamp' : null));
                                if ($dateColumn) {
                                    echo date('F j, Y \a\t g:i A', strtotime($request[$dateColumn]));
                                } else {
                                    echo 'Date not available';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Issue Description -->
                <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                        <i class="fas fa-exclamation-circle mr-2 text-blue-600"></i>Issue Description
                    </h2>
                    <div class="whitespace-pre-line"><?php echo htmlspecialchars($request['description']); ?></div>
                </div>
                
                <?php if (isset($request['technician_notes']) && !empty($request['technician_notes'])): ?>
                <!-- Technician Notes -->
                <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                        <i class="fas fa-clipboard mr-2 text-blue-600"></i>Technician Notes
                    </h2>
                    <div class="whitespace-pre-line"><?php echo htmlspecialchars($request['technician_notes']); ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Action Buttons -->
            <div class="p-4 border-t border-gray-200 flex justify-end space-x-3">
                <a href="edit_request.php?id=<?php echo $request['id']; ?>" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 flex items-center">
                    <i class="fas fa-edit mr-2"></i> Edit Request
                </a>
                <a href="delete_request.php?id=<?php echo $request['id']; ?>" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 flex items-center" onclick="return confirm('Are you sure you want to delete this request?');">
                    <i class="fas fa-trash mr-2"></i> Delete Request
                </a>
            </div>
        </div>
    </div>
</body>
</html>