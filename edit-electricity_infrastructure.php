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

// Fetch the most recent electricity infrastructure data for the user's school
$sql = "SELECT * FROM electricity_infrastructure WHERE school_id = (SELECT school_id FROM schools WHERE user_id = ?) ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $electricity_data = $result->fetch_assoc(); // Fetch the most recent electricity infrastructure data
} 
else {
    echo "<script>
        alert('No electricity infrastructure data found for this user.');
        window.location.href = 'back.php';
    </script>";
    exit();
}

// Handle form submission for editing data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $has_electricity = $_POST['has_electricity'];
    $source = $_POST['source'];
    $challenges = $_POST['challenges'];
    $mitigations = $_POST['mitigations'];
    $additional_info = $_POST['additional_info'];

    // Update the database with the new data
    $update_sql = "UPDATE electricity_infrastructure SET has_electricity = ?, source = ?, challenges = ?, mitigations = ?, additional_info = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssssi", $has_electricity, $source, $challenges, $mitigations, $additional_info, $electricity_data['id']);
    
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
    <title>View and Edit Electricity Infrastructure Data</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f9fafb; /* Light gray background */
        }
        .container {
            max-width: 800px; /* Set a max width for the container */
        }
        .form-section {
            background-color: #ffffff; /* White background for form */
            border-radius: 0.5rem; /* Rounded corners */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }
        .form-header {
            background-color: #3b82f6; /* Blue background */
            color: white; /* White text */
            padding: 1rem; /* Padding */
            border-top-left-radius: 0.5rem; /* Rounded corners */
            border-top-right-radius: 0.5rem; /* Rounded corners */
        }
        .form-label {
            font-weight: 600; /* Bold labels */
        }
        .form-input, .form-select, .form-textarea {
            border: 1px solid #d1d5db; /* Light gray border */
            border-radius: 0.375rem; /* Rounded corners */
            padding: 0.5rem; /* Padding */
            width: 100%; /* Full width */
            transition: border-color 0.2s; /* Transition for border color */
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: #3b82f6; /* Blue border on focus */
            outline: none; /* Remove outline */
        }
        .submit-button {
            background-color: #3b82f6; /* Blue background */
            color: white; /* White text */
            padding: 0.5rem 1rem; /* Padding */
            border-radius: 0.375rem; /* Rounded corners */
            transition: background-color 0.2s; /* Transition for background color */
        }
        .submit-button:hover {
            background-color: #2563eb; /* Darker blue on hover */
        }
        .hidden {
            display: none; /* Hide elements */
        }
        /* Back button styling */
        .back-button {
            display: inline-block;
            margin-bottom: 1.5rem;
            background-color: #6b7280; /* Gray-500 */
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 8px rgba(107, 114, 128, 0.4);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .back-button:hover,
        .back-button:focus {
            background-color: #4b5563; /* Gray-700 */
            box-shadow: 0 6px 12px rgba(75, 85, 99, 0.6);
            outline: none;
        }
    </style>
</head>
<body>
    <div class="container mx-auto mt-5">
        <!-- Back Button -->
        <a href="redirectpage.php" class="back-button" aria-label="Go back to previous page">&larr; Back</a>

        <div class="form-section p-6">
            <div class="form-header">
                <h2 class="text-2xl font-bold">Electricity Infrastructure Data</h2>
            </div>
            <div class="mb-4">
                <h3 class="text-xl font-semibold">Current Electricity Infrastructure Data</h3>
                <p><strong>Has Electricity:</strong> <?php echo htmlspecialchars($electricity_data['has_electricity']); ?></p>
                <p><strong>Source:</strong> <?php echo htmlspecialchars($electricity_data['source']); ?></p>
                <p><strong>Challenges:</strong> <?php echo htmlspecialchars($electricity_data['challenges']); ?></p>
                <p><strong>Mitigations:</strong> <?php echo htmlspecialchars($electricity_data['mitigations']); ?></p>
                <p><strong>Additional Info:</strong> <?php echo htmlspecialchars($electricity_data['additional_info']); ?></p>
            </div>

            <button id="editButton" class="bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700">EDIT</button>

            <div id="editForm" class="hidden mt-4">
                <h3 class="text-xl font-semibold mb-2">Edit Recent Electricity Infrastructure Data</h3>
                <form action="" method="POST">
                    <div class="mb-4">
                        <label for="has_electricity" class="block text-sm font-medium text-gray-700">Has Electricity</label>
                        <select id="has_electricity" name="has_electricity" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                            <option value="yes" <?php echo ($electricity_data['has_electricity'] == 'yes') ? 'selected' : ''; ?>>Yes</option>
                            <option value="no" <?php echo ($electricity_data['has_electricity'] == 'no') ? 'selected' : ''; ?>>No</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="source" class="block text-sm font-medium text-gray-700">Source</label>
                        <input type="text" id="source" name="source" value="<?php echo htmlspecialchars($electricity_data['source']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                    </div>
                    <div class="mb-4">
                        <label for="challenges" class="block text-sm font-medium text-gray-700">Challenges</label>
                        <textarea id="challenges" name="challenges" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md p-2"><?php echo htmlspecialchars($electricity_data['challenges']); ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="mitigations" class="block text-sm font-medium text-gray-700">Mitigations</label>
                        <textarea id="mitigations" name="mitigations" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md p-2"><?php echo htmlspecialchars($electricity_data['mitigations']); ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="additional_info" class="block text-sm font-medium text-gray-700">Additional Info</label>
                        <textarea id="additional_info" name="additional_info" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md p-2"><?php echo htmlspecialchars($electricity_data['additional_info']); ?></textarea>
                    </div>
                    <div class="flex justify-center">
                        <button type="submit" class="submit-button">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Show/hide the edit form when the EDIT button is clicked
        document.getElementById('editButton').addEventListener('click', function() {
            const editForm = document.getElementById('editForm');
            editForm.classList.toggle('hidden');
        });
    </script>
</body>
</html>
