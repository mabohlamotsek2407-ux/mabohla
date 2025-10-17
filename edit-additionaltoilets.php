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

// Fetch the most recent additional toilets data for the user's school
$sql = "SELECT * FROM additionaltoilets WHERE school_id = (SELECT school_id FROM schools WHERE user_id = ?) ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $toilet_data = $result->fetch_assoc(); // Fetch the most recent additional toilets data
}
else {
    echo "<script>
        alert('No additional toilets data found for this user.');
        window.location.href = 'back.php';
    </script>";
    exit();
}

// Handle form submission for editing data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_enrolment = $_POST['current_enrolment'];
    $additional_latrines_needed = $_POST['additional_latrines_needed'];
    $latrine_groups = $_POST['latrine_groups'];
    $number_of_latrines = $_POST['number_of_latrines'];
    $requests_made = $_POST['requests_made'];
    $summary = $_POST['summary'];

    // Update the database with the new data
    $update_sql = "UPDATE additionaltoilets SET current_enrolment = ?, additional_latrines_needed = ?, latrine_groups = ?, number_of_latrines = ?, requests_made = ?, summary = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("iisissi", $current_enrolment, $additional_latrines_needed, $latrine_groups, $number_of_latrines, $requests_made, $summary, $toilet_data['id']);
    
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
    <title>View and Edit Additional Toilets Data</title>
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
        <a href="redirectpage.php" class="back-button" aria-label="Go back to previous page">&larr; Back</a>

        <div class="form-section p-6">
            <div class="form-header">
                <h2 class="text-2xl font-bold">Additional Toilets Data</h2>
            </div>
            <div class="mb-4">
                <h3 class="text-xl font-semibold">Current Additional Toilets Data</h3>
                <p><strong>Current Enrolment:</strong> <?php echo htmlspecialchars($toilet_data['current_enrolment']); ?></p>
                <p><strong>Additional Latrines Needed:</strong> <?php echo htmlspecialchars($toilet_data['additional_latrines_needed']); ?></p>
                <p><strong>Latrine Groups:</strong> <?php echo htmlspecialchars($toilet_data['latrine_groups']); ?></p>
                <p><strong>Number of Latrines:</strong> <?php echo htmlspecialchars($toilet_data['number_of_latrines']); ?></p>
                <p><strong>Requests Made:</strong> <?php echo htmlspecialchars($toilet_data['requests_made']); ?></p>
                <p><strong>Summary:</strong> <?php echo htmlspecialchars($toilet_data['summary']); ?></p>
            </div>

            <button id="editButton" class="bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700">EDIT</button>

            <div id="editForm" class="hidden mt-4">
                <h3 class="text-xl font-semibold mb-2">Edit Recent Additional Toilets Data</h3>
                <form action="" method="POST">
                    <div class="mb-4">
                        <label for="current_enrolment" class="block text-sm font-medium text-gray-700">Current Enrolment</label>
                        <input type="number" id="current_enrolment" name="current_enrolment" value="<?php echo htmlspecialchars($toilet_data['current_enrolment']); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                    </div>
                    <div class="mb-4">
                        <label for="additional_latrines_needed" class="block text-sm font-medium text-gray-700">Additional Latrines Needed</label>
                        <input type="number" id="additional_latrines_needed" name="additional_latrines_needed" value="<?php echo htmlspecialchars($toilet_data['additional_latrines_needed']); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                    </div>
                    <div class="mb-4">
                        <label for="latrine_groups" class="block text-sm font-medium text-gray-700">Latrine Groups</label>
                        <input type="text" id="latrine_groups" name="latrine_groups" value="<?php echo htmlspecialchars($toilet_data['latrine_groups']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                    </div>
                    <div class="mb-4">
                        <label for="number_of_latrines" class="block text-sm font-medium text-gray-700">Number of Latrines</label>
                        <input type="number" id="number_of_latrines" name="number_of_latrines" value="<?php echo htmlspecialchars($toilet_data['number_of_latrines']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                    </div>
                    <div class="mb-4">
                        <label for="requests_made" class="block text-sm font-medium text-gray-700">Requests Made</label>
                        <input type="text" id="requests_made" name="requests_made" value="<?php echo htmlspecialchars($toilet_data['requests_made']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                    </div>
                    <div class="mb-4">
                        <label for="summary" class="block text-sm font-medium text-gray-700">Summary</label>
                        <textarea id="summary" name="summary" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md p-2"><?php echo htmlspecialchars($toilet_data['summary']); ?></textarea>
                    </div>
                    <div class="flex justify-center">
                        <button type="submit" class="submit-button">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle edit form visibility on EDIT button click
        document.getElementById('editButton').addEventListener('click', function() {
            const editForm = document.getElementById('editForm');
            editForm.classList.toggle('hidden');
        });
    </script>
</body>
</html>
