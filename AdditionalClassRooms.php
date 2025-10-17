<?php
session_start();

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

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get school information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT school_id, school_name, cluster FROM schools WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($school_id, $school_name, $cluster);
$stmt->fetch();
$stmt->close();

// Set defaults if not found
$school_name = $school_name ?? '';
$cluster = $cluster ?? '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $region = filter_input(INPUT_POST, 'region', FILTER_SANITIZE_STRING);
    $current_enrolment = filter_input(INPUT_POST, 'enrolment', FILTER_VALIDATE_INT);
    $require_classrooms = filter_input(INPUT_POST, 'require_classrooms', FILTER_SANITIZE_STRING);
    
    // Validate required fields
    if (empty($region) || $current_enrolment === false || empty($require_classrooms)) {
        die("Error: Required fields are missing or invalid");
    }

    // Initialize variables for insertion
    $infrastructure_summary = '0';
    $requests_made = '0';
    $grades_list = '0';
    $classroom_counts_list = '0';

    if (strtolower($require_classrooms) === 'yes') {
        // Retrieve and sanitize inputs for infrastructure_summary and requests_made
        $infrastructure_summary = filter_input(INPUT_POST, 'infrastructure_summary', FILTER_SANITIZE_STRING);
        $requests_made = filter_input(INPUT_POST, 'requests_made', FILTER_SANITIZE_STRING);

        // Retrieve grades and classroom counts
        $grades = $_POST['grade'] ?? [];
        $classroom_counts = $_POST['classroom_count'] ?? [];

        // Filter out empty grades and counts
        $filtered_grades = array_filter($grades, fn($g) => trim($g) !== '');
        $filtered_counts = array_filter($classroom_counts, fn($c) => is_numeric($c) && $c > 0);

        $grades_list = !empty($filtered_grades) ? implode(", ", $filtered_grades) : '0';
        $classroom_counts_list = !empty($filtered_counts) ? implode(", ", $filtered_counts) : '0';

        // If infrastructure_summary or requests_made are empty, set to '0'
        if (empty(trim($infrastructure_summary))) {
            $infrastructure_summary = '0';
        }
        if (empty(trim($requests_made))) {
            $requests_made = '0';
        }
    }

    // Insert into additionalclassrooms table
    $insert_stmt = $conn->prepare("INSERT INTO additionalclassrooms (
        school_id, 
        region, 
        school_name, 
        cluster, 
        current_enrolment, 
        require_classrooms, 
        infrastructure_summary, 
        requests_made,
        grades,
        classroom_counts
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$insert_stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $insert_stmt->bind_param(
        "isssiissss",
        $school_id,
        $region,
        $school_name,
        $cluster,
        $current_enrolment,
        $require_classrooms,
        $infrastructure_summary,
        $requests_made,
        $grades_list,
        $classroom_counts_list
    );

    if ($insert_stmt->execute()) {
        $_SESSION['success_message'] = "New record created successfully";
        echo '<script>
            alert("Data submitted successfully!");
            window.location.href = "redirectpage.php";
        </script>';
        exit();
    } else {
        echo '<script>alert("Error: ' . $insert_stmt->error . '");</script>';
    }

    $insert_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>School Infrastructure Requirements Form</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .form-container {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        .radio-group {
            display: flex;
            gap: 15px;
            margin-top: 5px;
        }
        .radio-option {
            display: flex;
            align-items: center;
        }
        .radio-option input {
            margin-right: 8px;
        }
        .conditional-section {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #3498db;
            border-radius: 0 4px 4px 0;
        }
        .grade-inputs {
            margin-top: 10px;
        }
        .grade-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        .grade-row input {
            flex: 1;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #2980b9;
        }
        .add-grade-btn {
            background-color: #27ae60;
            margin-top: 10px;
        }
        .add-grade-btn:hover {
            background-color: #219955;
        }
        .remove-grade-btn {
            background-color: #e74c3c;
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            color: white;
        }
        .remove-grade-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>School Infrastructure Requirements Form</h1>
        <form id="infrastructureForm" action="AdditionalClassRooms.php" method="POST" class="space-y-8">
            <div class="form-group">
                <label for="region">Region</label>
                <input type="text" id="region" name="region" required>
            </div>

            <div class="form-group">
                <label for="school_name">School Name</label>
                <input type="text" id="school_name" name="school_name" value="<?php echo htmlspecialchars($school_name); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="cluster">Cluster</label>
                <input type="text" id="cluster" name="cluster" value="<?php echo htmlspecialchars($cluster); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="enrolment">Current Total Enrolment</label>
                <input type="number" id="enrolment" name="enrolment" min="0" required>
            </div>

            <div class="form-group">
                <label>Does the school require additional classrooms?</label>
                <div class="radio-group">
                    <div class="radio-option">
                        <input type="radio" id="require-yes" name="require_classrooms" value="yes" required>
                        <label for="require-yes">Yes</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="require-no" name="require_classrooms" value="no">
                        <label for="require-no">No</label>
                    </div>
                </div>
            </div>

            <div id="additionalClassroomsSection" class="conditional-section">
                <div class="form-group">
                    <label>For which grades and how many classrooms?</label>
                    <div id="gradeInputs" class="grade-inputs">
                        <div class="grade-row" id="gradeRow1">
                            <input type="text" name="grade[]" placeholder="Grade (e.g., 4, 5-6)">
                            <input type="number" name="classroom_count[]" placeholder="Number of classrooms" min="1">
                            <button type="button" class="remove-grade-btn" onclick="removeGradeRow('gradeRow1')">Remove</button>
                        </div>
                    </div>
                    <button type="button" class="add-grade-btn" onclick="addGradeRow()">Add Another Grade</button>
                </div>

                <div class="form-group">
                    <label for="infrastructure_summary">Summary on the current infrastructure</label>
                    <textarea id="infrastructure_summary" name="infrastructure_summary"></textarea>
                </div>

                <div class="form-group">
                    <label for="requests_made">Where have any requests been made? (E.g. Japan, American Embassy, World Vision)</label>
                    <input type="text" id="requests_made" name="requests_made" placeholder="List organizations separated by commas">
                </div>
            </div>

            <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>">

            <button type="submit">Submit Information</button> 
            <button class="px-6 py-2 border border-gray-300 rounded-lg text-white bg-red-600 hover:bg-red-300 transition duration-200">
                        <i class="fas fa-times mr-2"></i> <a href="back.php">Back</a>
                    </button>
        </form>
    </div>

    <script>
        // Show/hide additional classrooms section based on radio selection
        document.querySelectorAll('input[name="require_classrooms"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const section = document.getElementById('additionalClassroomsSection');
                section.style.display = this.value === 'yes' ? 'block' : 'none';
            });
        });

        // Counter for dynamic grade rows
        let gradeRowCounter = 1;

        // Add new grade row
        function addGradeRow() {
            gradeRowCounter++;
            const html = `
                <div class="grade-row" id="gradeRow${gradeRowCounter}">
                    <input type="text" name="grade[]" placeholder="Grade (e.g., 4, 5-6)">
                    <input type="number" name="classroom_count[]" placeholder="Number of classrooms" min="1">
                    <button type="button" class="remove-grade-btn" onclick="removeGradeRow('gradeRow${gradeRowCounter}')">Remove</button>
                </div>
            `;
            document.getElementById('gradeInputs').insertAdjacentHTML('beforeend', html);
        }

        // Remove grade row
        function removeGradeRow(id) {
            const row = document.getElementById(id);
            if (row && document.querySelectorAll('.grade-row').length > 1) {
                row.remove();
            }
        }

        // Initialize form - hide additional classrooms section by default
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('additionalClassroomsSection').style.display = 'none';
        });
    </script>
</body>
</html>
