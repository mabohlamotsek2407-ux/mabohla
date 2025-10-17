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
$school_id = $school_id ?? null;

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $waterSource = $_POST['waterSource'];
    $waterType = $_POST['waterType'] ?? null;
    $distance = $_POST['distance'] ?? null;
    $challenges = $_POST['challenges'] ?? null;
    $mitigations = $_POST['mitigations'] ?? null;

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO water_infrastructure (school_id, cluster, water_source, water_type, distance, challenges, mitigations) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssiss", $school_id, $cluster, $waterSource, $waterType, $distance, $challenges, $mitigations);

    // Execute the statement
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Infrastructure Data Collection</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f9ff;
            color: #1e3a8a;
            font-size: 1.125rem; /* 18px */
            line-height: 1.5;
        }
        label {
            font-weight: 500;
            color: #01071d;
            font-size: 1.125rem; /* 18px */
        }
        .form-container {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .conditional-field {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .print-button {
            transition: all 0.2s ease;
        }
        .print-button:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-blue-900 mb-2">School Infrastructure Data Collection</h1>
            <p class="text-blue-700">For schools without internet connection</p>
            <img src="https://placehold.co/800x300" alt="Rural school building with children playing outside" class="mt-6 mx-auto rounded-lg shadow-md">
        </div>
        
        <div class="bg-white rounded-lg p-6 sm:p-8 form-container">
            <form id="infrastructureForm" method="POST" action="without-Water.php">
                <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>" readonly>
                <div class="form-group mb-4">
                    <label for="school_name">School Name</label>
                    <input type="text" id="school_name" name="school_name" value="<?php echo htmlspecialchars($school_name); ?>" readonly
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="form-group mb-4">
                    <label for="cluster" class="block text-xl font-medium text-gray-700">Cluster</label>
                    <input type="text" id="cluster" name="cluster" value="<?php echo htmlspecialchars($cluster); ?>" readonly
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="mb-6">
                    <label class="block text-xl font-medium text-gray-700 mb-2">Does the school have a water source?</label>
                    <div class="flex gap-4">
                        <div class="flex items-center">
                            <input id="water-yes" name="waterSource" type="radio" value="yes" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" onclick="toggleWaterFields(true)">
                            <label for="water-yes" class="ml-2 block text-sm text-gray-700">Yes</label>
                        </div>
                        <div class="flex items-center">
                            <input id="water-no" name="waterSource" type="radio" value="no" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" onclick="toggleWaterFields(false)">
                            <label for="water-no" class="ml-2 block text-sm text-gray-700">No</label>
                        </div>
                    </div>
                </div>
                
                <div id="waterTypeSection" class="conditional-field mb-6">
                    <label for="waterType" class="block text-xl font-medium text-gray-700 mb-1">If yes, which type?</label>
                    <select id="waterType" name="waterType" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select water source type</option>
                        <option value="Borehole">Borehole</option>
                        <option value="Well">Well</option>
                        <option value="Piped">Piped water</option>
                        <option value="Rainwater">Rainwater harvesting</option>
                        <option value="Spring">Spring</option>
                        <option value="River">River/Stream</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div id="distanceSection" class="conditional-field mb-6">
                    <label for="distance" class="block text-xl font-medium text-gray-700 mb-1">Distance from the school (in meters)</label>
                    <input type="number" id="distance" name="distance" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div id="challengesSection" class="conditional-field mb-6">
                    <label for="challenges" class="block text-xl font-medium text-gray-700 mb-1">Any challenges with the source?</label>
                    <textarea id="challenges" name="challenges" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                
                <div id="mitigationsSection" class="conditional-field mb-8">
                    <label for="mitigations" class="block text-xl font-medium text-gray-700 mb-1">Mitigations</label>
                    <textarea id="mitigations" name="mitigations" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                
                <div class="flex flex-col sm:flex-row justify-end gap-3">
                    
                    <button type="button" 
                            onclick="window.location.href='back.php'" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Back
                    </button>
                    <button type="reset" class="px-4 py-2 border border-gray-300 rounded-md text-xl font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Reset Form
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-xl font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Save Data
                    </button>
                    <button type="button" id="printBtn" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-xl font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 print-button">
                        Print Form
                    </button>
                </div>
            </form>
        </div>
        
        <div id="dataTableContainer" class="mt-12 hidden">
            <h2 class="text-xl font-bold text-blue-900 mb-4">Collected Data</h2>
            <div class="bg-white shadow overflow-hidden rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cluster</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Water Source</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Distance (m)</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Challenges</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mitigations</th>
                        </tr>
                    </thead>
                    <tbody id="dataTableBody" class="bg-white divide-y divide-gray-200">
                        <!-- Data will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show/hide conditional fields based on water source selection
            const waterSourceRadio = document.querySelectorAll('input[name="waterSource"]');
            const waterTypeSection = document.getElementById('waterTypeSection');
            const distanceSection = document.getElementById('distanceSection');
            const challengesSection = document.getElementById('challengesSection');
            const mitigationsSection = document.getElementById('mitigationsSection');
            
            waterSourceRadio.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'yes') {
                        waterTypeSection.style.display = 'block';
                        distanceSection.style.display = 'block';
                        challengesSection.style.display = 'block';
                        mitigationsSection.style.display = 'block';
                    } else {
                        waterTypeSection.style.display = 'none';
                        distanceSection.style.display = 'none';
                        challengesSection.style.display = 'none';
                        mitigationsSection.style.display = 'none';
                    }
                });
            });
            
            // Print functionality
            document.getElementById('printBtn').addEventListener('click', function() {
                window.print();
            });
        });

        function toggleWaterFields(hasWater) {
            const waterTypeSection = document.getElementById('waterTypeSection');
            const distanceSection = document.getElementById('distanceSection');
            const challengesSection = document.getElementById('challengesSection');
            const mitigationsSection = document.getElementById('mitigationsSection');

            if (hasWater) {
                waterTypeSection.style.display = 'block';
                distanceSection.style.display = 'block';
                challengesSection.style.display = 'block';
                mitigationsSection.style.display = 'block';
            } else {
                waterTypeSection.style.display = 'none';
                distanceSection.style.display = 'none';
                challengesSection.style.display = 'none';
                mitigationsSection.style.display = 'none';
            }
        }
    </script>
</body>
</html>
