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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $school_id = $_POST['school_id'];
    $school_name = $_POST['school_name'];
    $cluster = $_POST['cluster'];
    $hasElectricity = $_POST['hasElectricity'];
    $source = isset($_POST['source']) ? $_POST['source'] : null;
    $challenges = isset($_POST['challenges']) ? $_POST['challenges'] : null;
    $mitigations = isset($_POST['mitigations']) ? $_POST['mitigations'] : null;
    $additionalInfo = isset($_POST['additionalInfo']) ? $_POST['additionalInfo'] : null;

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO electricity_infrastructure (school_id, school_name, cluster, has_electricity, source, challenges, mitigations, additional_info) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $school_id, $school_name, $cluster, $hasElectricity, $source, $challenges, $mitigations, $additionalInfo);

    // Execute the statement
/*   <<<<<<< SEARCH
 if ($stmt->execute()) {
        echo "New record created successfully";
        // Data inserted successfully, redirect to update_status.php
        header("Location: redirectpage.php");

    } else {
        echo "Error: " . $stmt->error;
    }
======= */
if ($stmt->execute()) {
    $_SESSION['success_message'] = "New record created successfully";
    echo '<script>
        alert("Data submitted successfully!");
        window.location.href = "redirectpage.php";
    </script>';
} else {
    echo '<script>alert("Error: ' . $stmt->error . '");</script>';
}
//>>>>>>> REPLACE
//

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
    <title>School Electricity Infrastructure Survey</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .required-field::after {
            content: "*";
            color: red;
            margin-left: 4px;
        }
        .conditional-field {
            display: none;
        }
        .form-section {
            transition: all 0.3s ease;
        }
        .form-card {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <img src="https://placehold.co/800x200" alt="School building with solar panels on roof and children in school uniforms" class="mx-auto h-48 w-auto rounded-lg object-cover">
                <h1 class="mt-6 text-3xl font-bold text-gray-900">School Electricity Infrastructure Survey</h1>
                <p class="mt-4 text-lg text-gray-600">Help us understand power availability in schools to improve education infrastructure.</p>
            </div>

            <div class="bg-white form-card rounded-lg p-8">
                <form id="electricityForm" class="space-y-8" method="POST" action="">
                    <div class="form-section">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Basic Information</h2>
                        
                        <div class="form-group">
                            
                            <button type="button" 
        onclick="window.location.href='back.php'" 
        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
    Back
</button> <br>
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

                        <div class="form-section border-t pt-8">
                            <h2 class="text-xl font-semibold text-gray-800 mb-6">Electricity Availability</h2>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 required-field">Does the school have electricity?</label>
                                    <div class="mt-2 space-y-2">
                                        <div class="flex items-center">
                                            <input id="electricity-yes" name="hasElectricity" type="radio" value="yes" 
                                                   class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500" onclick="toggleElectricityFields(true)">
                                            <label for="electricity-yes" class="ml-3 block text-sm text-gray-700">Yes</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input id="electricity-no" name="hasElectricity" type="radio" value="no" 
                                                   class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500" onclick="toggleElectricityFields(false)" checked>
                                            <label for="electricity-no" class="ml-3 block text-sm text-gray-700">No</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="sourceSection" class="conditional-field">
                                    <label for="source" class="block text-sm font-medium text-gray-700 required-field">If yes, state source</label>
                                    <select id="source" name="source" class="mt-1 block w-full rounded-md border-gray-300 border bg-white py-2 px-3 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm">
                                        <option value="">Select electricity source</option>
                                        <option value="grid">National/Regional Grid</option>
                                        <option value="solar">Solar Power</option>
                                        <option value="generator">Generator</option>
                                        <option value="battery">Battery System</option>
                                        <option value="wind">Wind Power</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                
                                <div id="challengesSection" class="conditional-field">
                                    <label for="challenges" class="block text-sm font-medium text-gray-700">Any challenges with the source?</label>
                                    <textarea id="challenges" name="challenges" rows="3" 
                                              class="mt-1 block w-full rounded-md border-gray-300 border shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"></textarea>
                                    <p class="mt-2 text-sm text-gray-500">Please describe any reliability, maintenance, or other issues with the power source.</p>
                                </div>
                                
                                <div id="mitigationsSection" class="conditional-field">
                                    <label for="mitigations" class="block text-sm font-medium text-gray-700">Mitigations in place</label>
                                    <textarea id="mitigations" name="mitigations" rows="3" 
                                              class="mt-1 block w-full rounded-md border-gray-300 border shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"></textarea>
                                    <p class="mt-2 text-sm text-gray-500">Describe any measures taken to address power challenges.</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-section border-t pt-8">
                            <h2 class="text-xl font-semibold text-gray-800 mb-4">Additional Information</h2>
                            <label for="additionalInfo" class="block text-sm font-medium text-gray-700">Other relevant information</label>
                            <textarea id="additionalInfo" name="additionalInfo" rows="3" 
                                      class="mt-1 block w-full rounded-md border-gray-300 border shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"></textarea>
                        </div>

                        <div class="pt-6">
                            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Submit Data
                            </button>
                            

                        </div>
                    </div>
                </form>
            </div>
            
            <div class="mt-8 text-center text-sm text-gray-500">
                <p>Form submissions will be saved to our infrastructure database for analysis.</p>
            </div>
        </div>
    </div>

    <script>
        function toggleElectricityFields(show) {
            document.getElementById('sourceSection').style.display = show ? 'block' : 'none';
            document.getElementById('challengesSection').style.display = show ? 'block' : 'none';
            document.getElementById('mitigationsSection').style.display = show ? 'block' : 'none';
            
            if (show) {
                document.getElementById('source').required = true;
            } else {
                document.getElementById('source').required = false;
            }
        }
    </script>
</body>
</html>
