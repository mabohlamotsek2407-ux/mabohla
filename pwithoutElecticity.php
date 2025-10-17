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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $school_id = $_POST['school_id'];
    $school_name = $_POST['school_name'];
    $centre = $_POST['centre'];
    $hasElectricity = $_POST['hasElectricity'];
    $source = isset($_POST['source']) ? $_POST['source'] : null;
    $challenges = isset($_POST['challenges']) ? $_POST['challenges'] : null;
    $mitigations = isset($_POST['mitigations']) ? $_POST['mitigations'] : null;
    $additionalInfo = isset($_POST['additionalInfo']) ? $_POST['additionalInfo'] : null;

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO electricity_infrastructure (school_id, school_name, centre, has_electricity, source, challenges, mitigations, additional_info) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $school_id, $school_name, $centre, $hasElectricity, $source, $challenges, $mitigations, $additionalInfo);

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
    <title>School Electricity Infrastructure Survey</title>
    <!-- Google Fonts for Professional Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .required-field::after {
            content: "*";
            color: #ef4444;
            margin-left: 4px;
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
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <div class="container mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-6">
                <div class="bg-white rounded-full p-4 shadow-lg">
                    <i class="fas fa-bolt text-4xl text-blue-600"></i>
                </div>
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">School Electricity Infrastructure Survey</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Help us understand power availability in schools to improve education infrastructure. Your input is valuable for better resource allocation.</p>
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
                <form id="electricityForm" class="space-y-8" method="POST" action="">
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

                        <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>">
                    </div>

                    <!-- Electricity Availability Section -->
                    <div class="form-section border-t pt-8">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-plug text-green-600 mr-3"></i>
                            Electricity Availability
                        </h2>
                        
                        <div class="space-y-6">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label class="block text-sm font-medium text-gray-700 required-field mb-4">Does the school have access to electricity?</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="radio-option p-4 border border-gray-300 rounded-lg cursor-pointer">
                                        <input id="electricity-yes" name="hasElectricity" type="radio" value="yes" 
                                               class="sr-only peer" onclick="toggleElectricityFields(true)">
                                        <label for="electricity-yes" class="flex items-center cursor-pointer peer-checked:text-blue-600">
                                            <i class="fas fa-check-circle text-2xl mr-3 peer-checked:text-blue-600"></i>
                                            <span class="font-medium">Yes</span>
                                        </label>
                                    </div>
                                    <div class="radio-option p-4 border border-gray-300 rounded-lg cursor-pointer">
                                        <input id="electricity-no" name="hasElectricity" type="radio" value="no" 
                                               class="sr-only peer" onclick="toggleElectricityFields(false)" checked>
                                        <label for="electricity-no" class="flex items-center cursor-pointer peer-checked:text-red-600">
                                            <i class="fas fa-times-circle text-2xl mr-3 peer-checked:text-red-600"></i>
                                            <span class="font-medium">No</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="sourceSection" class="conditional-field space-y-4">
                                <div>
                                    <label for="source" class="block text-sm font-medium text-gray-700 required-field">Source of Electricity</label>
                                    <select id="source" name="source" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white py-3 px-4 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:text-sm">
                                        <option value="">Select electricity source</option>
                                        <option value="grid">National/Regional Grid</option>
                                        <option value="solar">Solar Power</option>
                                        <option value="generator">Generator</option>
                                        <option value="battery">Battery System</option>
                                        <option value="wind">Wind Power</option>
                                        <option value="other">Other (Please specify below)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div id="challengesSection" class="conditional-field space-y-4">
                                <div>
                                    <label for="challenges" class="block text-sm font-medium text-gray-700">Challenges with Electricity Supply</label>
                                    <textarea id="challenges" name="challenges" rows="4" 
                                              placeholder="Describe any reliability issues, maintenance problems, or other challenges..."
                                              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 p-3"></textarea>
                                </div>
                            </div>
                            
                            <div id="mitigationsSection" class="conditional-field space-y-4">
                                <div>
                                    <label for="mitigations" class="block text-sm font-medium text-gray-700">Mitigation Measures in Place</label>
                                    <textarea id="mitigations" name="mitigations" rows="4" 
                                              placeholder="Describe any solutions, backups, or improvements implemented..."
                                              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 p-3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information Section -->
                    <div class="form-section border-t pt-8">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-info-circle text-purple-600 mr-3"></i>
                            Additional Information
                        </h2>
                        <div>
                            <label for="additionalInfo" class="block text-sm font-medium text-gray-700">Any other relevant details</label>
                            <textarea id="additionalInfo" name="additionalInfo" rows="4" 
                                      placeholder="Share any additional information about power usage, future needs, or special circumstances..."
                                      class="mt-1 block w-full rounded-lg border border-gray-300 bg-white shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 p-3"></textarea>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-8 border-t flex flex-col sm:flex-row gap-4 justify-end">
                        <a href="back.php" class="btn-secondary w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Cancel & Go Back
                        </a>
                        <button type="submit" class="btn-primary w-full sm:w-auto flex justify-center items-center px-6 py-3 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Submit Survey
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
        function toggleElectricityFields(show) {
            const sourceSection = document.getElementById('sourceSection');
            const challengesSection = document.getElementById('challengesSection');
            const mitigationsSection = document.getElementById('mitigationsSection');
            const sourceSelect = document.getElementById('source');
            
            if (show) {
                sourceSection.style.display = 'block';
                challengesSection.style.display = 'block';
                mitigationsSection.style.display = 'block';
                sourceSelect.required = true;
                sourceSection.classList.add('animate-fade-in');
                challengesSection.classList.add('animate-fade-in');
                mitigationsSection.classList.add('animate-fade-in');
            } else {
                sourceSection.style.display = 'none';
                challengesSection.style.display = 'none';
                mitigationsSection.style.display = 'none';
                sourceSelect.required = false;
                sourceSelect.value = '';
            }
        }

        // Add fade-in animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fade-in {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-fade-in {
                animation: fade-in 0.3s ease-out;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>