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
$school_name = strtoupper($school_name ?? ''); // Convert school name to uppercase
$cluster = $cluster ?? '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $school_id = filter_input(INPUT_POST, 'school_id', FILTER_VALIDATE_INT);
    $classrooms = filter_input(INPUT_POST, 'classrooms', FILTER_VALIDATE_INT);
    $toilets = filter_input(INPUT_POST, 'toilets', FILTER_VALIDATE_INT);
    $kitchen = filter_input(INPUT_POST, 'kitchen', FILTER_SANITIZE_NUMBER_INT);
    $store = filter_input(INPUT_POST, 'store', FILTER_SANITIZE_NUMBER_INT);
    $staffroom = filter_input(INPUT_POST, 'staffroom', FILTER_SANITIZE_NUMBER_INT);
    $office = filter_input(INPUT_POST, 'office', FILTER_SANITIZE_NUMBER_INT);
    $library = filter_input(INPUT_POST, 'library', FILTER_SANITIZE_NUMBER_INT);
    $laboratory = filter_input(INPUT_POST, 'laboratory', FILTER_SANITIZE_NUMBER_INT);
    $hall = filter_input(INPUT_POST, 'hall', FILTER_SANITIZE_NUMBER_INT);
    $playgrounds = filter_input(INPUT_POST, 'playgrounds', FILTER_SANITIZE_STRING); // New playgrounds field
    $created_at = date('Y-m-d H:i:s'); // Current timestamp

    // Validate required fields
    if ($school_id === false || $classrooms === false || $toilets === false) {
        die("Error: Required fields are missing or invalid");
    }

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO infrastructure (school_id, classrooms, toilets, kitchen, store, staffroom, office, library, laboratory, hall, playgrounds, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiissssssss", $school_id, $classrooms, $toilets, $kitchen, $store, $staffroom, $office, $library, $laboratory, $hall, $playgrounds, $created_at);

    // Execute the statement
    if ($stmt->execute()) {
        echo "New infrastructure record created successfully.";
        header("Location: redirectpage.php");
    } else {
        echo "Error: " . $stmt->error; // Display error message
    }

    // Close the statement
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Infrastructure Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .required:after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto py-8 px-4 max-w-4xl">
        <div class="bg-white rounded-xl shadow-md overflow-hidden animate-fade-in">
            <!-- Header Section -->
            <div class="bg-blue-600 py-4 px-6 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-white">
                    <i class="fas fa-school mr-2"></i> School Infrastructure Form
                </h1>
                <div class="text-blue-100 text-sm">
                    <i class="fas fa-info-circle mr-1"></i> All fields required
                </div>
            </div>
            
            <!-- Form Section -->
            <form class="p-6" id="infrastructureForm" method="POST" action=""> 
                
                <!-- School Information -->
                <div class="mb-8 p-4 border border-gray-200 rounded-lg">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-building mr-2 text-blue-500"></i>
                        School Details
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>" readonly
                         class="w-auto px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">

                        <div>
                            <label for="school_name" class="block text-sm font-medium text-gray-700 mb-1">School Name</label>
                            <input type="text" id="school_name" name="school_name" value="<?php echo htmlspecialchars($school_name); ?>" readonly
                            class="w-auto px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
                
                <!-- Infrastructure Facilities -->
                <div class="mb-8 p-4 border border-gray-200 rounded-lg">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-wrench mr-2 text-blue-500"></i>
                        Facilities Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Column 1 -->
                        <div>
                            <div class="mb-4">
                                <label for="classrooms" class="block text-sm font-medium text-gray-700 mb-1 required">Number of Classrooms</label>
                                <input type="number" id="classrooms" name="classrooms" min="0"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="toilets" class="block text-sm font-medium text-gray-700 mb-1 required">Number of Toilets</label>
                                <div class="flex">
                                    <input type="number" id="toilets" name="toilets" min="0"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="kitchen" class="block text-sm font-medium text-gray-700 mb-1">Kitchen Available?</label>
                                <select id="kitchen" name="kitchen"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="store" class="block text-sm font-medium text-gray-700 mb-1">Store Room Available?</label>
                                <select id="store" name="store"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Column 2 -->
                        <div>
                            <div class="mb-4">
                                <label for="staffroom" class="block text-sm font-medium text-gray-700 mb-1">Staff Room Available?</label>
                                <select id="staffroom" name="staffroom"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="office" class="block text-sm font-medium text-gray-700 mb-1"> Office?</label>
                                <select id="office" name="office"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="library" class="block text-sm font-medium text-gray-700 mb-1">Library Available?</label>
                                <select id="library" name="library"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="laboratory" class="block text-sm font-medium text-gray-700 mb-1">Laboratory Available?</label>
                                <select id="laboratory" name="laboratory"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="hall" class="block text-sm font-medium text-gray-700 mb-1">Hall Available?</label>
                                <select id="hall" name="hall"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Playgrounds Section -->
                <div class="mb-8 p-4 border border-gray-200 rounded-lg">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-playground mr-2 text-blue-500"></i>
                        Playground Information
                    </h2>
                    <div class="mb-4">
                        <label for="playgrounds" class="block text-sm font-medium text-gray-700 mb-1">Describe Playground Facilities</label>
                        <textarea id="playgrounds" name="playgrounds" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Describe the type of playgrounds available..."></textarea>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" 
                            class="px-6 py-2 border border-gray-300 rounded-lg text-white bg-red-600 hover:bg-red-300 transition duration-200">
                        <i class="fas fa-times mr-2"></i> <a href="back.php">Back</a>
                    </button>
                    <button type="button" 
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-save mr-2"></i> Save Information
                    </button>
                </div>
            </form>
            
            <!-- Created At Info -->
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 text-right">
                <span class="text-xs text-gray-500">Record created at: <span id="created_at">Not submitted yet</span></span>
            </div>
        </div>
    </div>

    <script>
        // Add animation to form elements
        document.querySelectorAll('input, select, textarea').forEach((el, index) => {
            el.style.animationDelay = `${index * 50}ms`;
            el.classList.add('animate-fade-in');
        });
    </script>
</body>
</html>
