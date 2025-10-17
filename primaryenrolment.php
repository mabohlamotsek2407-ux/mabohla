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
    $grade1_girls = $_POST['g_1g'];
    $grade1_boys = $_POST['g_1b'];
    $grade2_girls = $_POST['g_2g'];
    $grade2_boys = $_POST['g_2b'];
    $grade3_girls = $_POST['g_3g'];
    $grade3_boys = $_POST['g_3b'];
    $grade4_girls = $_POST['g_4g'];
    $grade4_boys = $_POST['g_4b'];
    $grade5_girls = $_POST['g_5g'];
    $grade5_boys = $_POST['g_5b'];
    $grade6_girls = $_POST['g_6g'];
    $grade6_boys = $_POST['g_6b'];
    $grade7_girls = $_POST['G_7g'];
    $grade7_boys = $_POST['G_7b'];
    $repeaters_girls = $_POST['G_7girlsrepeaters'];
    $repeaters_boys = $_POST['G_7boysrepeaters'];
    $school_id = $_POST['school_id'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO primary_enrollment (female_reception, male_reception, grade1_girls, grade1_boys, grade2_girls, grade2_boys, grade3_girls, grade3_boys, grade4_girls, grade4_boys, grade5_girls, grade5_boys, grade6_girls, grade6_boys, grade7_girls, grade7_boys, repeaters_girls, repeaters_boys, school_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Bind parameters
    $stmt->bind_param("iiiiiiiiiiiiiiiiiii", $reception_female, $reception_male, $grade1_girls, $grade1_boys, $grade2_girls, $grade2_boys, $grade3_girls, $grade3_boys, $grade4_girls, $grade4_boys, $grade5_girls, $grade5_boys, $grade6_girls, $grade6_boys, $grade7_girls, $grade7_boys, $repeaters_girls, $repeaters_boys, $school_id);

    // Execute the insert statement
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "New record created successfully";
        echo '<script>
            alert("Data submitted successfully!");
            window.location.href = "redirectpage.php";
        </script>';
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
            position: relative;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 20px;
            object-fit: cover;
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .header-subtitle {
            font-size: 1rem;
            opacity: 0.9;
        }

        .navigation-buttons {
            display: flex;
            gap: 15px;
        }

        .nav-button {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .nav-button:hover {
            transform: translateY(-1px);
        }

        .back-button {
            background-color: #3b82f6;
        }

        .section {
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th,
        td {
            padding: 18px;
            border: 1px solid #e5e7eb;
            text-align: left;
            font-size: 16px;
        }

        th {
            background-color: #3b82f6;
            color: white;
            font-weight: 600;
            font-size: 17px;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
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

        .form-input-group {
            display: flex;
            gap: 10px;
        }

        .form-input-pair {
            flex: 1;
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

        /* Responsive adjustments for smaller devices */
        @media (max-width: 640px) {
            .form-input {
                font-size: 14px;
                padding: 8px;
            }

            th,
            td {
                padding: 12px;
                font-size: 14px;
            }

            .header-title {
                font-size: 1.25rem;
            }

            .header-subtitle {
                font-size: 0.875rem;
            }

            .form-input-group {
                flex-direction: column;
            }
        }

        /* Display school info above form */
        .school-info {
            max-width: 1200px;
            margin: 20px auto 0 auto;
            padding: 0 30px;
            font-size: 1.125rem;
            color: #1e40af;
            font-weight: 600;
        }

        .school-info span {
            display: inline-block;
            margin-right: 20px;
        }
    </style>
</head>

<body>
    <!-- Header with Navigation -->
    <header class="header-container">
        <div class="header-content">
            <div class="logo-container">
                <img src="moet.png" alt="Ministry of Education" class="logo-image" />
                <div>
                    <h1 class="header-title">School Enrollment Data</h1>
                    <p class="header-subtitle">Ministry of Education & Training</p>
                </div>
            </div>
            <div class="navigation-buttons">
                <a href="Primary-Dash.php" class="nav-button back-button">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Display school name and registration number outside the table -->
    <div class="school-info">
        <span><strong>School:</strong> <?php echo htmlspecialchars($school_name); ?></span>
        <span><strong>Registration No:</strong> <?php echo htmlspecialchars($registration_number); ?></span>
    </div>

    <form action="primaryenrolment.php" method="POST">
        <!-- Main Form Section -->
        <section class="section">
            <!-- Reception Table -->
            <table>
                <thead>
                    <tr>
                        <th>RECEPTION</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="form-input-group">
                                <div class="form-input-pair">
                                    <input type="number" name="recF" required placeholder="Girls" class="form-input" />
                                </div>
                                <div class="form-input-pair">
                                    <input type="number" name="recM" required placeholder="Boys" class="form-input" />
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Grades Section -->
            <table>
                <thead>
                    <tr>
                        <th>GRADE 1</th>
                        <th>GRADE 2</th>
                        <th>GRADE 3</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="form-input-group">
                                <div class="form-input-pair">
                                    <input type="number" name="g_1g" required placeholder="Girls" class="form-input" />
                                </div>
                                <div class="form-input-pair">
                                    <input type="number" name="g_1b" required placeholder="Boys" class="form-input" />
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="form-input-group">
                                <div class="form-input-pair">
                                    <input type="number" name="g_2g" required placeholder="Girls" class="form-input" />
                                </div>
                                <div class="form-input-pair">
                                    <input type="number" name="g_2b" required placeholder="Boys" class="form-input" />
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="form-input-group">
                                <div class="form-input-pair">
                                    <input type="number" name="g_3g" required placeholder="Girls" class="form-input" />
                                </div>
                                <div class="form-input-pair">
                                    <input type="number" name="g_3b" required placeholder="Boys" class="form-input" />
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table>
                <thead>
                    <tr>
                        <th>GRADE 4</th>
                        <th>GRADE 5</th>
                        <th>GRADE 6</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="form-input-group">
                                <div class="form-input-pair">
                                    <input type="number" name="g_4g" required placeholder="Girls" class="form-input" />
                                </div>
                                <div class="form-input-pair">
                                    <input type="number" name="g_4b" required placeholder="Boys" class="form-input" />
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="form-input-group">
                                <div class="form-input-pair">
                                    <input type="number" name="g_5g" required placeholder="Girls" class="form-input" />
                                </div>
                                <div class="form-input-pair">
                                    <input type="number" name="g_5b" required placeholder="Boys" class="form-input" />
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="form-input-group">
                                <div class="form-input-pair">
                                    <input type="number" name="g_6g" required placeholder="Girls" class="form-input" />
                                </div>
                                <div class="form-input-pair">
                                    <input type="number" name="g_6b" required placeholder="Boys" class="form-input" />
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table>
                <thead>
                    <tr>
                        <th>GRADE 7</th>
                        <th>REPEATERS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="form-input-group">
                                <div class="form-input-pair">
                                    <input type="number" name="G_7g" required placeholder="Girls" class="form-input" />
                                </div>
                                <div class="form-input-pair">
                                    <input type="number" name="G_7b" required placeholder="Boys" class="form-input" />
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="form-input-group">
                                <div class="form-input-pair">
                                    <input type="number" name="G_7girlsrepeaters" required placeholder="Girls" class="form-input" />
                                </div>
                                <div class="form-input-pair">
                                    <input type="number" name="G_7boysrepeaters" required placeholder="Boys" class="form-input" />
                                </div>
                            </div>
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
