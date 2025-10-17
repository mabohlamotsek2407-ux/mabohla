<?php
session_start(); // Start the session

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Check if user_id is set in the session
$user_id = $_SESSION['user_id'] ?? null;
if ($user_id === null) {
    echo "User  ID is not set in the session.";
    exit();
}

// Prepare SQL update statement
$updateStatusQuery = "UPDATE users SET status = '1' WHERE user_id = ?";
$updateStmt = $conn->prepare($updateStatusQuery);
$updateStmt->bind_param("i", $user_id);

// Execute the statement and check for success
if ($updateStmt->execute()) {
    // Redirect to a success page or dashboard
    header("Location: kinder-Dash.html"); // Change to your desired page
    exit();
} else {
    echo "Error updating user status: " . $updateStmt->error;
}

$updateStmt->close();
$conn->close();
?>
