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
$school_id = $school_id ?? null;

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $waterSource = $_POST['waterSource'];
    $waterType = $_POST['waterType'] ?? null;
    $distance = $_POST['distance'] ?? null;
    $challenges = $_POST['challenges'] ?? null;
    $mitigations = $_POST['mitigations'] ?? null;

    // Ensure centre is not empty
    if (empty($centre)) {
        echo '<script>alert("Centre cannot be empty.");</script>';
    } else {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO water_infrastructure (school_id, centre, water_source, water_type, distance, challenges, mitigations) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssiss", $school_id, $centre, $waterSource, $waterType, $distance, $challenges, $mitigations);

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
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Water Infrastructure Survey</title>
    <!-- Google Fonts for Professional Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .conditional-field {
            display: none;
        }
        .form-section {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .form-card {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid #e5e7eb;
        }
        .btn-primary {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3);
        }
        .btn-secondary {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-secondary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.1);
        }
        .radio-option {
            transition: all 0.2s ease;
        }
        .radio-option:hover {
            background-color: #f8fafc;
            border-radius: 0.5rem;
            padding: 0.25rem;
        }
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-cyan-50 min-h-screen">
    <div class="container mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-6">
                <div class="bg-white rounded-full p-4 shadow-lg">
                    <i class="fas fa-tint text-4xl text-blue-600"></i>
                </div>
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">School Water Infrastructure Survey</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Provide details about your school's water access to help improve sanitation and educational facilities. For schools without internet connection.</p>
        </div>

        <div class="max-w-4xl mx-auto">
            <!-- Action Bar with Back Button -->
            <div class="bg-white rounded-lg p-4 sm:p-6 mb-6 form-card">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <a href="back.php" class="btn-secondary inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Dashboard
                    </a>
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Secure form - All data is encrypted and stored confidentially.
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white form-card rounded-xl p-6 sm:p-8">
                <form id="infrastructureForm" class="space-y-8" method="POST" action="">
                    <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>">

                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                            Basic School Information
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label for="school_name" class="block text-sm font-medium text-gray-700 mb-2">School Name</label>
                                <input type="text" id="school_name" name="school_name" value="<?php echo htmlspecialchars($school_name); ?>" readonly
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 cursor-not-allowed focus:ring-0 focus:border-gray-300">
                            </div>

                            <div class="form-group">
                                <label for="centre" class="block text-sm font-medium text-gray-700 mb-2">Centre</label>
                                <input type="text" id="centre" name="centre" value="<?php echo htmlspecialchars($centre); ?>" readonly
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 cursor-not-allowed focus:ring-0 focus:border-gray-300">
                            </div>
                        </div>
                    </div>

                    <!-- Water Availability Section -->
                    <div class="form-section border-t pt-8">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-water text-cyan-600 mr-3"></i>
                            Water Availability
                        </h2>
                        
                        <div class="space-y-6">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label class="block text-sm font-medium text-gray-700 mb-4">Does the school have access to a water source?</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="radio-option p-4 border border-gray-300 rounded-lg cursor-pointer">
                                        <input id="water-yes" name="waterSource" type="radio" value="yes" 
                                               class="sr-only peer" onclick="toggleWaterFields(true)">
                                        <label for="water-yes" class="flex items-center cursor-pointer peer-checked:text-blue-600">
                                            <i class="fas fa-check-circle text-2xl mr-3 peer-checked:text-blue-600"></i>
                                            <span class="font-medium">Yes</span>
                                        </label>
                                    </div>
                                    <div class="radio-option p-4 border border-gray-300 rounded-lg cursor-pointer">
                                        <input id="water-no" name="waterSource" type="radio" value="no" 
                                               class="sr-only peer" onclick="toggleWaterFields(false)" checked>
                                        <label for="water-no" class="flex items-center cursor-pointer peer-checked:text-red-600">
                                            <i class="fas fa-times-circle text-2xl mr-3 peer-checked:text-red-600"></i>
                                            <span class="font-medium">No</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="waterTypeSection" class="conditional-field space-y-4">
                                <div>
                                    <label for="waterType" class="block text-sm font-medium text-gray-700">Type of Water Source</label>
                                    <select id="waterType" name="waterType" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white py-3 px-4 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:text-sm">
                                        <option value="">Select water source type</option>
                                        <option value="Borehole">Borehole</option>
                                        <option value="Well">Well</option>
                                        <option value="Piped">Piped water</option>
                                        <option value="Rainwater">Rainwater harvesting</option>
                                        <option value="Spring">Spring</option>
                                        <option value="River">River/Stream</option>
                                        <option value="Other">Other (Please specify below)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div id="distanceSection" class="conditional-field space-y-4">
                                <div>
                                    <label for="distance" class="block text-sm font-medium text-gray-700">Distance from School (in meters)</label>
                                    <input type="number" id="distance" name="distance" 
                                           placeholder="Enter distance in meters"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 py-3 px-4 sm:text-sm">
                                </div>
                            </div>
                            
                            <div id="challengesSection" class="conditional-field space-y-4">
                                <div>
                                    <label for="challenges" class="block text-sm font-medium text-gray-700">Challenges with Water Supply</label>
                                    <textarea id="challenges" name="challenges" rows="4" 
                                              placeholder="Describe any reliability issues, maintenance problems, contamination concerns, or other challenges..."
                                              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 p-3"></textarea>
                                </div>
                            </div>
                            
                            <div id="mitigationsSection" class="conditional-field space-y-4">
                                <div>
                                    <label for="mitigations" class="block text-sm font-medium text-gray-700">Mitigation Measures in Place</label>
                                    <textarea id="mitigations" name="mitigations" rows="4" 
                                              placeholder="Describe any solutions, backups, treatment methods, or improvements implemented..."
                                              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 p-3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-8 border-t flex flex-col sm:flex-row gap-4 justify-end">
                        <button type="reset" class="btn-secondary w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-undo mr-2"></i>
                            Reset Form
                        </button>
                        <button type="button" id="printBtn" class="btn-secondary w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 text-sm font-medium text-green-700 bg-green-50 border border-green-300 rounded-lg hover:bg-green-100">
                            <i class="fas fa-print mr-2"></i>
                            Print Form
                        </button>
                        <button type="submit" class="btn-primary w-full sm:w-auto flex justify-center items-center px-6 py-3 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>
                            Save Data
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Footer Note -->
            <div class="mt-8 text-center text-sm text-gray-500 bg-gray-50 rounded-lg p-4">
                <i class="fas fa-shield-alt mr-2"></i>
                <p>Your data is secure and will be used solely for educational infrastructure improvement. Thank you for contributing!</p>
            </div>
        </div>
    </div>

    <script>
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
                waterTypeSection.classList.add('animate-fade-in');
                distanceSection.classList.add('animate-fade-in');
                challengesSection.classList.add('animate-fade-in');
                mitigationsSection.classList.add('animate-fade-in');
            } else {
                waterTypeSection.style.display = 'none';
                distanceSection.style.display = 'none';
                challengesSection.style.display = 'none';
                mitigationsSection.style.display = 'none';
                // Clear values
                document.getElementById('waterType').value = '';
                document.getElementById('distance').value = '';
                document.getElementById('challenges').value = '';
                document.getElementById('mitigations').value = '';
            }
        }

        // Print functionality
        document.getElementById('printBtn').addEventListener('click', function() {
            window.print();
        });
    </script>
</body>
</html>