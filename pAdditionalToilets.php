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
$stmt = $conn->prepare("SELECT school_id, school_name, centre FROM schools WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($school_id, $school_name, $centre);
$stmt->fetch();
$stmt->close();

// Set defaults if not found
$school_name = $school_name ?? '';
$centre = $centre ?? '';

// Fetch toilets from infrastructure table
$toilets = 0; // Default value
$stmt = $conn->prepare("SELECT toilets FROM infrastructure WHERE school_id = ?");
$stmt->bind_param("i", $school_id);
$stmt->execute();
$stmt->bind_result($toilets);
$stmt->fetch();
$stmt->close();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $school_id = $_POST['school_id'];
    $school_name = $_POST['school_name'];
    $centre = $_POST['centre'];
    $current_enrolment = $_POST['currentenrolment'];
    $additional_latrines_needed = isset($_POST['additionalLatrines']) && $_POST['additionalLatrines'] === 'yes' ? 1 : 0;
    $latrine_groups = isset($_POST['latrineGroups']) ? implode(", ", $_POST['latrineGroups']) : '';
    $number_of_latrines = $_POST['numberOfLatrines'];
    $requests_made = isset($_POST['requestsMade']) ? implode(", ", (array)$_POST['requestsMade']) : '';
    $summary = $_POST['summary'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO additionaltoilets (school_id, school_name, centre, current_enrolment, additional_latrines_needed, latrine_groups, number_of_latrines, requests_made, summary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issiisiss", $school_id, $school_name, $centre, $current_enrolment, $additional_latrines_needed, $latrine_groups, $number_of_latrines, $requests_made, $summary);

    // Execute the insert statement
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "New record created successfully";
        echo '<script>
            alert("Data submitted successfully!");
            window.location.href = "redirectpage.php";
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
    <title>School Infrastructure Data Collection</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .conditional-field {
            display: none;
        }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .checkbox-custom {
            width: 20px;
            height: 20px;
            border: 2px solid #4a5568;
            border-radius: 4px;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        input[type="checkbox"]:checked + .checkbox-custom::after {
            content: "✓";
            color: #2d3748;
            font-weight: bold;
        }
        /* Back button styling */
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 10px 18px;
            border-radius: 5px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #388e3c;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-6 max-w-3xl">
        <a href="Primary-Dash.php" class="back-button">&larr; Back to Dashboard</a>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 p-6 text-white">
                <h1 class="text-xl font-bold">School Infrastructure Data Collection</h1>
                <p class="text-blue-200 mt-1">Complete this form to report on your Additional Toilets request</p>
            </div>

            <form id="infrastructureForm" class="p-6 space-y-6" method="POST" action="padditionalToilets.php" novalidate>
                <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>" />

                <div>
                    <label for="school_name" class="block text-sm font-medium text-gray-700 mb-1">School Name</label>
                    <input type="text" id="school_name" name="school_name" value="<?php echo htmlspecialchars($school_name); ?>" readonly class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div>
                    <label for="centre" class="block text-sm font-medium text-gray-700 mb-1">Center</label>
                    <input type="text" id="centre" name="centre" value="<?php echo htmlspecialchars($centre); ?>" readonly class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div>
                    <label for="currentenrolment" class="block text-sm font-medium text-gray-700 mb-1">Current Total Enrolment</label>
                    <input type="number" id="currentenrolment" name="currentenrolment" min="0" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div>
                    <p class="block text-sm font-medium text-gray-700 mb-2">Does the school require additional latrines?</p>
                    <div class="flex space-x-6">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="additionalLatrines" value="yes" class="form-radio" onchange="toggleLatrineFields()" required />
                            <span class="ml-2">Yes</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="additionalLatrines" value="no" class="form-radio" onchange="toggleLatrineFields()" checked />
                            <span class="ml-2">No</span>
                        </label>
                    </div>
                </div>

                <div id="latrineFields" class="conditional-field bg-gray-50 p-4 rounded-md space-y-4">
                    <div>
                        <p class="block text-sm font-medium text-gray-700 mb-2">For which groups? (Select all that apply)</p>
                        <div class="space-y-2">
                            <label class="checkbox-label">
                                <input type="checkbox" name="latrineGroups[]" value="Teachers" class="hidden" />
                                <div class="checkbox-custom"></div>
                                Teachers
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="latrineGroups[]" value="Boys" class="hidden" />
                                <div class="checkbox-custom"></div>
                                Boys
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="latrineGroups[]" value="Girls" class="hidden" />
                                <div class="checkbox-custom"></div>
                                Girls
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="numberOfLatrines" class="block text-sm font-medium text-gray-700 mb-1">How many additional latrines are needed?</label>
                        <input type="number" id="numberOfLatrines" name="numberOfLatrines" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Enter number of latrines" />
                    </div>

                    <div>
                        <label for="requestsMade" class="block text-sm font-medium text-gray-700 mb-1">Where have any requests been made?</label>
                        <select id="requestsMade" name="requestsMade[]" multiple class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 h-auto">
                            <option value="Japan">Japan</option>
                            <option value="American Embassy">American Embassy</option>
                            <option value="World Vision">World Vision</option>
                            <option value="UNICEF">UNICEF</option>
                            <option value="Local Government">Local Government</option>
                            <option value="Other">Other</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Hold down Ctrl/Command to select multiple options</p>
                    </div>
                </div>

                <div>
                    <label for="summary" class="block text-sm font-medium text-gray-700 mb-1">Summary on the current infrastructure</label>
                    <textarea id="summary" name="summary" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Describe the current state of school infrastructure including buildings, facilities, and any urgent needs"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Submit Data
                    </button>
                    <button type="reset" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Reset Form
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleLatrineFields() {
            const latrineFields = document.getElementById('latrineFields');
            const requiredLatrines = document.querySelector('input[name="additionalLatrines"]:checked').value;

            if (requiredLatrines === 'yes') {
                latrineFields.style.display = 'block';
                document.getElementById('numberOfLatrines').setAttribute('required', '');
            } else {
                latrineFields.style.display = 'none';
                document.getElementById('numberOfLatrines').removeAttribute('required');
                // Clear conditional fields when hidden
                document.querySelectorAll('#latrineFields input[type="checkbox"]').forEach(el => el.checked = false);
                document.getElementById('numberOfLatrines').value = '';
                document.getElementById('requestsMade').selectedIndex = -1;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            toggleLatrineFields();

            // Checkbox visual toggle
            document.querySelectorAll('.checkbox-label').forEach(label => {
                label.addEventListener('click', () => {
                    const checkbox = label.querySelector('input[type="checkbox"]');
                    checkbox.checked = !checkbox.checked;
                });
            });
        });
    </script>
</body>
</html>
