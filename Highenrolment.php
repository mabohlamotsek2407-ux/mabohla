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

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Retrieve the registration number and school ID from the schools table
$user_id = $_SESSION['user_id'];

// Prepare and execute the query for registration number and school name
$regNoQuery = "SELECT registration_number, school_id, school_name FROM schools WHERE user_id = ?";
$stmt = $conn->prepare($regNoQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($registration_number, $school_id, $school_name);
$stmt->fetch();
$stmt->close();

// Check if school_name is set, if not set it to an empty string
if (!isset($school_name)) {
    $school_name = ''; // Default to an empty string if not found
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $reception_female = $_POST['recF']; 
    $reception_male = $_POST['recM']; 
    $grade_8_girls = $_POST['g8g'];
    $grade_8_boys = $_POST['g8b'];
    $grade_9_girls = $_POST['grade9g'];
    $grade_9_boys = $_POST['grade9b'];
    $grade_10_girls = $_POST['grade10g'];
    $grade_10_boys = $_POST['grade10b'];
    $grade_11_girls = $_POST['grade11g'];
    $grade_11_boys = $_POST['grade11b'];
    $grants_girls = $_POST['grants_g'];
    $grants_boys = $_POST['grants_b'];
    $school_id = $_POST['school_id']; // Get the school ID from the form

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO high_school_enrollment (female_reception, male_reception, grade_8_girls, grade_8_boys, grade_9_girls, grade_9_boys, grade_10_girls, grade_10_boys, grade_11_girls, grade_11_boys, grants_girls, grants_boys, school_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Bind parameters
    $stmt->bind_param("iiiiiiiiiiiii", $reception_female, $reception_male, $grade_8_girls, $grade_8_boys, $grade_9_girls, $grade_9_boys, $grade_10_girls, $grade_10_boys, $grade_11_girls, $grade_11_boys, $grants_girls, $grants_boys, $school_id);

    // Execute the statement
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "New record created successfully";
        echo '<script>
            alert("Data submitted successfully!");
            window.location.href = "retrivehigh-school-enrollment.php";
        </script>';
        exit();
    } else {
        echo '<script>alert("Error: ' . $stmt->error . '");</script>';
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>School Enrollment Data</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: #374151;
        }

        .header-container {
            background-color: #1e40af;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 20px;
        }

        .header-title {
            font-size: 2rem;
            font-weight: 600;
        }

        .header-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .back-button {
            background-color: #3b82f6;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #2563eb;
        }

        .section {
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .school-info {
            display: flex;
            gap: 2rem;
            margin-bottom: 30px;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e40af;
        }

        .school-info div {
            flex: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th,
        td {
            padding: 16px;
            border: 1px solid #d1d5db;
            text-align: left;
            font-size: 18px;
        }

        th {
            background-color: #3b82f6;
            color: #ffffff;
            font-size: 20px;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .submit-button {
            background-color: #3b82f6;
            color: white;
            padding: 14px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            width: 100%;
        }

        .submit-button:hover {
            background-color: #2563eb;
        }

        footer {
            background-color: #1e3a8a;
            color: white;
            padding: 20px 0;
            text-align: center;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <form action="Highenrolment.php" method="POST">
        <!-- Header Section -->
        <header class="header-container">
            <div class="header-content">
                <div class="logo-container">
                    <img src="moet.png" alt="Ministry of Education" class="logo-image" />
                    <div>
                        <h1 class="header-title">School Enrollment Data</h1>
                        <p class="header-subtitle">Ministry of Education & Training</p>
                    </div>
                </div>
                <a href="Highschool-Dash.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </header>

        <!-- Main Form Section -->
        <section class="section">
            <!-- School Name and Registration Number outside the table -->
            <div class="school-info">
                <span><strong>School Name:</strong> <?php echo htmlspecialchars($school_name); ?></span>
                 <span><strong>Reg-No:</strong> <?php echo htmlspecialchars($registration_number); ?></span>
            </div>

            <!-- Enrollment Tables -->
            <table>
                <thead>
                    <tr>
                        <th>Reception</th>
                        <th>Grade 8</th>
                        <th>Grade 9</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <input type="number" name="recF" required placeholder="Female" class="form-input mb-2" />
                            <input type="number" name="recM" required placeholder="Male" class="form-input" />
                        </td>
                        <td>
                            <input type="number" name="g8g" required placeholder="Girls" class="form-input mb-2" />
                            <input type="number" name="g8b" required placeholder="Boys" class="form-input" />
                        </td>
                        <td>
                            <input type="number" name="grade9g" required placeholder="Girls" class="form-input mb-2" />
                            <input type="number" name="grade9b" required placeholder="Boys" class="form-input" />
                        </td>
                    </tr>
                </tbody>
            </table>

            <table>
                <thead>
                    <tr>
                        <th>Grade 10</th>
                        <th>Grade 11</th>
                        <th>Grants</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <input type="number" name="grade10g" required placeholder="Girls" class="form-input mb-2" />
                            <input type="number" name="grade10b" required placeholder="Boys" class="form-input" />
                        </td>
                        <td>
                            <input type="number" name="grade11g" required placeholder="Girls" class="form-input mb-2" />
                            <input type="number" name="grade11b" required placeholder="Boys" class="form-input" />
                        </td>
                        <td>
                            <input type="number" name="grants_g" required placeholder="Girls" class="form-input mb-2" />
                            <input type="number" name="grants_b" required placeholder="Boys" class="form-input" />
                        </td>
                    </tr>
                </tbody>
            </table>

            <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>" />

            <button type="submit" class="submit-button">
                <i class="fas fa-save mr-2"></i> Submit Information
            </button>
        </section>
    </form>

    <footer>
        <div class="footer-content">
            <p>&copy; 2025 Ministry of Education and Training. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>
