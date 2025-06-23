<?php
// Start session and check authentication if needed
session_start();

// Database configuration for the bose database
$servername = "localhost";
$username = "root"; // Change if different
$password = ""; // Change if you have a password
$dbname = "bose";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// First check what columns exist in the table
$columns = [];
$checkColumns = $conn->query("SHOW COLUMNS FROM service_requests");
if ($checkColumns) {
    while ($row = $checkColumns->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
} else {
    die("Error checking table columns: " . $conn->error);
}

// Determine the correct date column to use for sorting
$dateColumn = null;
if (in_array('created_at', $columns)) {
    $dateColumn = 'created_at';
} elseif (in_array('date_created', $columns)) {
    $dateColumn = 'date_created';
} elseif (in_array('registration_date', $columns)) {
    $dateColumn = 'registration_date';
} elseif (in_array('timestamp', $columns)) {
    $dateColumn = 'timestamp';
}

// Build the query
if ($dateColumn) {
    $sql = "SELECT * FROM service_requests ORDER BY $dateColumn DESC";
} else {
    $sql = "SELECT * FROM service_requests";
}

// Fetch all service requests
$result = $conn->query($sql);
if (!$result) {
    die("Error fetching service requests: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Requests</title>
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
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8 fade-in">
            <div class="bg-blue-600 py-4 px-6">
                <h1 class="text-2xl font-bold text-white">Service Requests</h1>
                <p class="text-blue-100">All service requests in the system</p>
            </div>
            
            <div class="p-4 flex justify-between items-center">
                <a href="intex.html"class="text-blue-600 hover:text-blue-800 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Home
                </a>
                <div class="text-gray-600">
                    Total Requests: <?php echo $result->num_rows; ?>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <?php
                            // Dynamically create table headers based on available columns
                            $displayColumns = [
                                'id' => 'iD',
                                'name' => 'name',
                                'email' => 'Email',
                                'contact' => 'contact',
                                'service_type' => 'service_type',
                                'description' => 'description',
                                'status' => 'Status'
                            ];
                            
                            // Add date column if available
                            if ($dateColumn) {
                                $displayColumns[$dateColumn] = 'Date';
                            }
                            
                            foreach ($displayColumns as $col => $title) {
                                if (in_array($col, $columns)) {
                                    echo "<th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>$title</th>";
                                }
                            }
                            ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <?php
                                foreach ($displayColumns as $col => $title) {
                                    if (in_array($col, $columns)) {
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm " . 
                                             ($col === 'status' ? '' : 'text-gray-500') . "'>";
                                        
                                        if ($col === 'status') {
                                            echo "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full " .
                                                 ($row[$col] === 'Completed' ? 'bg-green-100 text-green-800' : 
                                                  ($row[$col] === 'In Progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')) . "'>" .
                                                 htmlspecialchars($row[$col]) . "</span>";
                                        } 
                                        elseif ($col === 'issue_description' && strlen($row[$col]) > 50) {
                                            echo htmlspecialchars(substr($row[$col], 0, 50)) . '...';
                                        }
                                        elseif ($col === $dateColumn) {
                                            echo date('M j, Y g:i A', strtotime($row[$col]));
                                        }
                                        else {
                                            echo htmlspecialchars($row[$col]);
                                        }
                                        
                                        echo "</td>";
                                    }
                                }
                                ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="view_request.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="edit_request.php?id=<?php echo $row['id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete_request.php?id=<?php echo $row['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo count($displayColumns) + 1; ?>" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No service requests found in the database.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200">
                <div class="text-sm text-gray-500">
                    Showing <span class="font-medium">1</span> to <span class="font-medium"><?php echo $result->num_rows; ?></span> of <span class="font-medium"><?php echo $result->num_rows; ?></span> results
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple confirmation for delete actions
        document.querySelectorAll('a[href*="delete_request"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this service request?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>