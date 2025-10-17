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
        UPDATE preschool_enrollment 
        SET age3_girls = :age3_girls, 
            age3_boys = :age3_boys, 
            age4_girls = :age4_girls, 
            age4_boys = :age4_boys, 
            age5_girls = :age5_girls, 
            age5_boys = :age5_boys, 
            female_reception = :female_reception, 
            male_reception = :male_reception 
        WHERE id = :id 
        AND school_id IN (SELECT school_id FROM schools WHERE user_id = :user_id)
    ";

    // Prepare the statement
    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':age3_girls', $_POST['age3_girls'], PDO::PARAM_INT);
    $stmt->bindParam(':age3_boys', $_POST['age3_boys'], PDO::PARAM_INT);
    $stmt->bindParam(':age4_girls', $_POST['age4_girls'], PDO::PARAM_INT);
    $stmt->bindParam(':age4_boys', $_POST['age4_boys'], PDO::PARAM_INT);
    $stmt->bindParam(':age5_girls', $_POST['age5_girls'], PDO::PARAM_INT);
    $stmt->bindParam(':age5_boys', $_POST['age5_boys'], PDO::PARAM_INT);
    $stmt->bindParam(':female_reception', $_POST['female_reception'], PDO::PARAM_INT);
    $stmt->bindParam(':male_reception', $_POST['male_reception'], PDO::PARAM_INT);
    $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect to viewkinder-enroll.php after successful update
        header("Location: viewkinder-enroll.php");
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
