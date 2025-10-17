<?php
session_start(); // Start the session

// Database connection configuration
$servername = "sql104.infinityfree.com"; // Removed trailing space
$username = "if0_40021406"; 
$password = "Op70TI711cS2lB6"; // Add your MySQL password here
$dbname = "if0_40021406_moet1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view this page.");
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Fetch the most recent additional classrooms data for the user's school
$sql = "SELECT * FROM additionalclassrooms WHERE school_id = (SELECT school_id FROM schools WHERE user_id = ?) ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $classroom_data = $result->fetch_assoc(); // Fetch the most recent additional classrooms data
} 
else {
    echo "<script>
        alert('No additional classrooms data found for this user.');
        window.location.href = 'back.php';
    </script>";
    exit();
}

// Handle form submission for editing data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_enrolment = $_POST['current_enrolment'];
    $require_classrooms = $_POST['require_classrooms'];
    $infrastructure_summary = $_POST['infrastructure_summary'];
    $requests_made = $_POST['requests_made'];
    $grades = $_POST['grades'];
    $classroom_counts = $_POST['classroom_counts'];

    // Update the database with the new data
    $update_sql = "UPDATE additionalclassrooms SET current_enrolment = ?, require_classrooms = ?, infrastructure_summary = ?, requests_made = ?, grades = ?, classroom_counts = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("issssii", $current_enrolment, $require_classrooms, $infrastructure_summary, $requests_made, $grades, $classroom_counts, $classroom_data['id']);
    
    if ($update_stmt->execute()) {
        echo "<script>alert('Data updated successfully.');</script>";
        // Redirect to the same page to see updated data
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error updating data: " . $conn->error;
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>View and Edit Additional Classrooms Data</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <script>
        function toggleEditForm() {
            const form = document.getElementById('editForm');
            form.classList.toggle('hidden');
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 p-5 bg-white rounded-lg shadow-lg max-w-3xl">
        <!-- Back Button -->
        <a href="redirectpage.php" 
           class="inline-block mb-6 bg-gray-600 hover:bg-gray-800 text-white font-semibold py-2 px-4 rounded shadow transition-colors duration-300"
           aria-label="Go back to previous page">
            &larr; Back
        </a>

        <h2 class="text-2xl font-bold mb-4 text-center">Additional Classrooms Data</h2>
        <div class="mb-4">
            <h3 class="text-xl font-semibold">Current Additional Classrooms Data</h3>
            <div class="bg-gray-50 p-4 rounded-md shadow">
                <p><strong>Current Enrolment:</strong> <?php echo htmlspecialchars($classroom_data['current_enrolment']); ?></p>
                <p><strong>Require Classrooms:</strong> <?php echo htmlspecialchars($classroom_data['require_classrooms']); ?></p>
                <p><strong>Infrastructure Summary:</strong> <?php echo htmlspecialchars($classroom_data['infrastructure_summary']); ?></p>
                <p><strong>Requests Made:</strong> <?php echo htmlspecialchars($classroom_data['requests_made']); ?></p>
                <p><strong>Grades:</strong> <?php echo htmlspecialchars($classroom_data['grades']); ?></p>
                <p><strong>Classroom Counts:</strong> <?php echo htmlspecialchars($classroom_data['classroom_counts']); ?></p>
            </div>
        </div>

        <button onclick="toggleEditForm()" class="bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 mb-4 w-full sm:w-auto">EDIT</button>

        <div id="editForm" class="hidden">
            <h3 class="text-xl font-semibold mb-2">Edit Recent Additional Classrooms Data</h3>
            <form action="" method="POST" class="space-y-4">
                <div>
                    <label for="current_enrolment" class="block text-sm font-medium text-gray-700">Current Enrolment</label>
                    <input type="number" id="current_enrolment" name="current_enrolment" value="<?php echo htmlspecialchars($classroom_data['current_enrolment']); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                </div>
                <div>
                    <label for="require_classrooms" class="block text-sm font-medium text-gray-700">Require Classrooms</label>
                    <select id="require_classrooms" name="require_classrooms" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                        <option value="yes" <?php echo ($classroom_data['require_classrooms'] == 'yes') ? 'selected' : ''; ?>>Yes</option>
                        <option value="no" <?php echo ($classroom_data['require_classrooms'] == 'no') ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                <div>
                    <label for="infrastructure_summary" class="block text-sm font-medium text-gray-700">Infrastructure Summary</label>
                    <textarea id="infrastructure_summary" name="infrastructure_summary" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md p-2"><?php echo htmlspecialchars($classroom_data['infrastructure_summary']); ?></textarea>
                </div>
                <div>
                    <label for="requests_made" class="block text-sm font-medium text-gray-700">Requests Made</label>
                    <input type="text" id="requests_made" name="requests_made" value="<?php echo htmlspecialchars($classroom_data['requests_made']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                </div>
                <div>
                    <label for="grades" class="block text-sm font-medium text-gray-700">Grades</label>
                    <input type="text" id="grades" name="grades" value="<?php echo htmlspecialchars($classroom_data['grades']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                </div>
                <div>
                    <label for="classroom_counts" class="block text-sm font-medium text-gray-700">Classroom Counts</label>
                    <input type="number" id="classroom_counts" name="classroom_counts" value="<?php echo htmlspecialchars($classroom_data['classroom_counts']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
