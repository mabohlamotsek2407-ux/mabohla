<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User  not logged in");
}

// Database connection configuration
$servername = "sql104.infinityfree.com"; // Removed trailing space
$username = "if0_40021406"; 
$password = "Op70TI711cS2lB6"; // Add your MySQL password here
$dbname = "if0_40021406_moet1";
$charset = 'utf8mb4';

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare the SQL update statement
    $sql = "
        UPDATE schools 
        SET principal_name = :principal_name, 
            principal_surname = :principal_surname, 
            phone_number = :phone_number, 
            email_address = :email_address, 
            number_of_teachers = :number_of_teachers, 
            female_teachers = :female_teachers, 
            male_teachers = :male_teachers 
        WHERE school_id = :school_id 
        AND user_id = :user_id
    ";

    // Prepare the statement
    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':principal_name', $_POST['principal_name'], PDO::PARAM_STR);
    $stmt->bindParam(':principal_surname', $_POST['principal_surname'], PDO::PARAM_STR);
    $stmt->bindParam(':phone_number', $_POST['phone_number'], PDO::PARAM_INT);
    $stmt->bindParam(':email_address', $_POST['email_address'], PDO::PARAM_STR);
    $stmt->bindParam(':number_of_teachers', $_POST['number_of_teachers'], PDO::PARAM_INT);
    $stmt->bindParam(':female_teachers', $_POST['female_teachers'], PDO::PARAM_INT);
    $stmt->bindParam(':male_teachers', $_POST['male_teachers'], PDO::PARAM_INT);
    $stmt->bindParam(':school_id', $_POST['school_id'], PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect to the school data management page after successful update
        header("Location: update_school.php");
        exit(); // Ensure no further code is executed after the redirect
    } else {
        echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">';
        echo '<p>Error updating record.</p>';
        echo '</div>';
    }
} catch (PDOException $e) {
    echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">';
    echo '<p>Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
}
?>
