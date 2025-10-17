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
    $cluster = $_POST['cluster'];
    $current_enrolment = $_POST['currentenrolment'];
    $additional_latrines_needed = isset($_POST['additionalLatrines']) && $_POST['additionalLatrines'] === 'yes' ? 1 : 0;
  //  $latrine_groups = isset($_POST['latrineGroups']) ? implode(", ", (array)$_POST['latrineGroups']) : '';
    $latrine_groups = isset($_POST['latrineGroups']) ? implode(", ", $_POST['latrineGroups']) : '';

    $number_of_latrines = $_POST['numberOfLatrines'];
    $requests_made = isset($_POST['requestsMade']) ? implode(", ", (array)$_POST['requestsMade']) : '';
    $summary = $_POST['summary'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO additionaltoilets (school_id, school_name, cluster, current_enrolment, additional_latrines_needed, latrine_groups, number_of_latrines, requests_made, summary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issiisiss", $school_id, $school_name, $cluster, $current_enrolment, $additional_latrines_needed, $latrine_groups, $number_of_latrines, $requests_made, $summary);

    // Execute the insert statement
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "New record created successfully";
        echo '<script>
            alert("Data submitted successfully!");
            window.location.href = "redirectpage.php";
        </script>';
    } else {
        echo '<script>alert("Error: ' . $insert_stmt->error . '");</script>';
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-6">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Header with descriptive image -->
            <div class="bg-blue-600 p-6 h-5px text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-1xl font-bold">School Infrastructure Data Collection</h1>
                        <p class="text-blue-100">Complete this form to report on your Additional Toilets request</p>
                    </div>
                 <!---   <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/323839a7-6643-4869-8426-15c1e426d931.png" alt="Illustration of a school building with modern architecture showing classrooms and outdoor spaces" class="rounded-full w-px10 border-2 border-white" /> -->
                </div>
            </div>

            <form id="infrastructureForm" class="p-6 space-y-6" method="POST" action="additionalToilets.php">
                <!-- Infrastructure Table -->
                <div class="form-group">
                    <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>" readonly
                   class="w-auto px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <label for="school">School Name</label>
                <input type="text" id="school_name" name="school_name" value="<?php echo htmlspecialchars($school_name); ?>" readonly
                       class="w-auto px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="form-group">
                <label for="cluster">Cluster</label>
                <input type="text" id="cluster" name="cluster" value="<?php echo htmlspecialchars($cluster); ?>" readonly
                       class="w-auto px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>

                <!-- Current Total Enrolment -->
                <div>
                    <label for="enrolment" class="block text-sm font-medium text-gray-700 mb-1">Current Total Enrolment</label>
                    <input type="text" id="cluster" name="currentenrolment" value="<?php echo htmlspecialchars($toilets); ?>" readonly
                       class="w-auto px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Additional Latrines Needed -->
                <div>
                    <p class="block text-sm font-medium text-gray-700 mb-2">Does the school require additional latrines?</p>
                    <div class="flex space-x-4">
                        <label class="radio-label">
                            <input type="radio" name="additionalLatrines" value="yes" class="mr-2" onchange="toggleLatrineFields()"> Yes
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="additionalLatrines" value="no" class="mr-2" onchange="toggleLatrineFields()" checked> No
                        </label>
                    </div>
                </div>

                <!-- Conditional Fields - Latrine Details -->
                <div id="latrineFields" class="conditional-field bg-gray-50 p-4 rounded-md space-y-4">
                    <!-- Groups -->
                    <div>
    <p class="block text-sm font-medium text-gray-700 mb-2">For which groups? (Select all that apply)</p>
    <div class="space-y-2">
        <label class="checkbox-label">
            <input type="checkbox" name="latrineGroups[]" value="Teachers" class="hidden">
            <div class="checkbox-custom"></div>
            Teachers
        </label>
        <label class="checkbox-label">
            <input type="checkbox" name="latrineGroups[]" value="Boys" class="hidden">
            <div class="checkbox-custom"></div>
            Boys
        </label>
        <label class="checkbox-label">
            <input type="checkbox" name="latrineGroups[]" value="Girls" class="hidden">
            <div class="checkbox-custom"></div>
            Girls
        </label>
    </div>
</div>


                    <!-- Number of Latrines -->
                    <div>
                        <label for="numberOfLatrines" class="block text-sm font-medium text-gray-700 mb-1">How many additional latrines are needed?</label>
                        <input type="number" id="numberOfLatrines" name="numberOfLatrines" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Enter number of latrines">
                    </div>

                    <!-- Requests Made -->
                    <div>
                        <label for="requestsMade" class="block text-sm font-medium text-gray-700 mb-1">Where have any requests been made?</label>
                        <select id="requestsMade" name="requestsMade" multiple class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 h-auto">
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

                <!-- Summary of Current Infrastructure -->
                <div>
                    <label for="summary" class="block text-sm font-medium text-gray-700 mb-1">Summary on the current infrastructure</label>
                    <textarea id="summary" name="summary" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Describe the current state of school infrastructure including buildings, facilities, and any urgent needs"></textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" 
        onclick="window.location.href='back.php'" 
        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
    Back
</button>

                    
                    <button type="submit" onclick="validateForm()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Submit Data
                    </button>
                    <button type="reset" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Reset Form
                    </button>
                </div>
            </form>
        </div>

        <!-- Image placeholder for context -->
      
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
            document.querySelectorAll('#latrineFields input, #latrineFields select').forEach(element => {
                if (element.type === 'checkbox') {
                    element.checked = false;
                } else if (element.type === 'number' || element.tagName === 'SELECT') {
                    element.value = '';
                }
            });
        }
    }

    function validateForm() {
        const form = document.getElementById('infrastructureForm');
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value) {
                field.style.borderColor = 'red';
                isValid = false;
            } else {
                field.style.borderColor = '';
            }
        });

        return isValid;
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleLatrineFields();

        // Add visual feedback for checkboxes
        document.querySelectorAll('.checkbox-label').forEach(label => {
            label.addEventListener('click', function() {
                const checkbox = this.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;
            });
        });

     /*   // Handle submit button click
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.addEventListener('click', function() {
            if (validateForm()) {
                if (confirm('Are you sure you want to submit this infrastructure data?')) {
                    document.getElementById('infrastructureForm').submit();
                }
            } else {
                alert('Please complete all required fields marked in red.');
            }
        }); */
    });
</script>

</body>
</html>

