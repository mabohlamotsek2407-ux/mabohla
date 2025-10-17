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
    $centre = $_POST['centre'];
    $hasInternet = $_POST['hasInternet'];
    $source = isset($_POST['source']) ? $_POST['source'] : null;
    $reliableNetwork = isset($_POST['reliableNetwork']) ? $_POST['reliableNetwork'] : null;
    $challenges = isset($_POST['challenges']) ? $_POST['challenges'] : null;
    $mitigations = isset($_POST['mitigations']) ? $_POST['mitigations'] : null;
    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO internet_infrastructure (school_id, centre, has_internet, source, reliable_network, challenges, mitigations) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $school_id, $centre, $hasInternet, $source, $reliableNetwork, $challenges, $mitigations);
    // Execute the statement
    if ($stmt->execute()) {
        echo "New record created successfully";
        // Data inserted successfully, redirect to update_status.php
        header("Location: redirectpage.php");

    } else {
        echo "Error: " . $stmt->error;
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
<body class="bg-gradient-to-br from-indigo-50 to-blue-50 min-h-screen">
    <div class="container mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-6">
                <div class="bg-white rounded-full p-4 shadow-lg">
                    <i class="fas fa-wifi text-4xl text-indigo-600"></i>
                </div>
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">School Internet Infrastructure Assessment</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Provide details about your school's internet connectivity to help improve digital education access. For schools without internet connection.</p>
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
                <form id="infrastructureForm" class="space-y-8" method="POST" action="without-Internet.php">
                    <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>">

                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-info-circle text-indigo-600 mr-3"></i>
                            Basic School Information
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label for="school_name" class="block text-sm font-medium text-gray-700 mb-2">School Name</label>
                                <input type="text" id="school_name" name="school_name" value="<?php echo htmlspecialchars($school_name); ?>" readonly
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 cursor-not-allowed focus:ring-0 focus:border-gray-300">
                            </div>

                            <div class="form-group">
                                <label for="centre" class="block text-sm font-medium text-gray-700 mb-2">Centre/Cluster</label>
                                <input type="text" id="centre" name="centre" value="<?php echo htmlspecialchars($centre); ?>" readonly
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 cursor-not-allowed focus:ring-0 focus:border-gray-300">
                            </div>
                        </div>
                    </div>

                    <!-- Internet Availability Section -->
                    <div class="form-section border-t pt-8">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-network-wired text-purple-600 mr-3"></i>
                            Internet Connectivity
                        </h2>
                        
                        <div class="space-y-6">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label class="block text-sm font-medium text-gray-700 mb-4">Does the school have access to internet?</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="radio-option p-4 border border-gray-300 rounded-lg cursor-pointer">
                                        <input id="internet-yes" name="hasInternet" type="radio" value="yes" 
                                               class="sr-only peer" onclick="toggleInternetFields(true)">
                                        <label for="internet-yes" class="flex items-center cursor-pointer peer-checked:text-indigo-600">
                                            <i class="fas fa-check-circle text-2xl mr-3 peer-checked:text-indigo-600"></i>
                                            <span class="font-medium">Yes</span>
                                        </label>
                                    </div>
                                    <div class="radio-option p-4 border border-gray-300 rounded-lg cursor-pointer">
                                        <input id="internet-no" name="hasInternet" type="radio" value="no" 
                                               class="sr-only peer" onclick="toggleInternetFields(false)" checked>
                                        <label for="internet-no" class="flex items-center cursor-pointer peer-checked:text-red-600">
                                            <i class="fas fa-times-circle text-2xl mr-3 peer-checked:text-red-600"></i>
                                            <span class="font-medium">No</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="internetSourceField" class="conditional-field space-y-4">
                                <div>
                                    <label for="source" class="block text-sm font-medium text-gray-700">Source of Internet</label>
                                    <input type="text" id="source" name="source" 
                                           placeholder="e.g., Fiber optic, Satellite, Mobile data provider..."
                                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-2 focus:ring-offset-2 py-3 px-4 sm:text-sm">
                                </div>
                            </div>
                            
                            <div id="reliableNetworkField" class="conditional-field space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-4">Which network is reliable and stable?</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="radio-option p-4 border border-gray-300 rounded-lg cursor-pointer">
                                            <input id="network-eec" name="reliableNetwork" type="radio" value="EEC" 
                                                   class="sr-only peer">
                                            <label for="network-eec" class="flex items-center cursor-pointer peer-checked:text-indigo-600">
                                                <i class="fas fa-signal text-2xl mr-3 peer-checked:text-indigo-600"></i>
                                                <span class="font-medium">EEC</span>
                                            </label>
                                        </div>
                                        <div class="radio-option p-4 border border-gray-300 rounded-lg cursor-pointer">
                                            <input id="network-vcl" name="reliableNetwork" type="radio" value="VCL" 
                                                   class="sr-only peer">
                                            <label for="network-vcl" class="flex items-center cursor-pointer peer-checked:text-indigo-600">
                                                <i class="fas fa-signal text-2xl mr-3 peer-checked:text-indigo-600"></i>
                                                <span class="font-medium">VCL</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="challenges" class="block text-sm font-medium text-gray-700">Challenges with Internet Access</label>
                                    <textarea id="challenges" name="challenges" rows="4" 
                                              placeholder="Describe connectivity issues, speed problems, costs, or other challenges..."
                                              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-2 focus:ring-offset-2 p-3"></textarea>
                                </div>
                                
                                <div>
                                    <label for="mitigations" class="block text-sm font-medium text-gray-700">Mitigation Measures in Place</label>
                                    <textarea id="mitigations" name="mitigations" rows="4" 
                                              placeholder="Describe any solutions, alternative access methods, or improvements implemented..."
                                              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-2 focus:ring-offset-2 p-3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-8 border-t flex flex-col sm:flex-row gap-4 justify-end">
                        <button type="reset" class="btn-secondary w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-undo mr-2"></i>
                            Clear Form
                        </button>
                        <button type="submit" class="btn-primary w-full sm:w-auto flex justify-center items-center px-6 py-3 text-sm font-medium text-white bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Submit Data
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
        function toggleInternetFields(hasInternet) {
            const internetSourceField = document.getElementById('internetSourceField');
            const reliableNetworkField = document.getElementById('reliableNetworkField');
            
            if (hasInternet) {
                internetSourceField.style.display = 'block';
                reliableNetworkField.style.display = 'none';
                // Clear reliable network selection
                document.querySelectorAll('input[name="reliableNetwork"]').forEach(radio => radio.checked = false);
                internetSourceField.classList.add('animate-fade-in');
            } else {
                internetSourceField.style.display = 'none';
                reliableNetworkField.style.display = 'block';
                // Clear source input
                document.getElementById('source').value = '';
                reliableNetworkField.classList.add('animate-fade-in');
            }
        }
    </script>
</body>
</html>