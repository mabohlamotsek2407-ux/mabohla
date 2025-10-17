<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $principal_name = $_POST['principal_name'] ?? '';
    $principal_surname = $_POST['principal_surname'] ?? '';
    $principal_phone = $_POST['principal_phone'] ?? '';
    $principal_email = $_POST['principal_email'] ?? '';
    $constituency = $_POST['constituency'] ?? '';
    $cluster = $_POST['cluster'] ?? '';
    $typeofschool = $_POST['typeofschool'] ?? '';

    // School data
    $school_name = $_POST['school_name'] ?? '';
    $registration_number = $_POST['registration'] ?? '';
//    $number_of_teachers = (int) ($_POST['teachers'] ?? 0);
    $male_teachers = (int) ($_POST['male_teachers'] ?? 0);
    $female_teachers = (int) ($_POST['female_teachers'] ?? 0);
    $council = $_POST['council'] ?? '';
    $village = $_POST['village'] ?? '';

    // Get user_id from session
    $user_id = $_SESSION['user_id'] ?? null;
    if ($user_id === null) {
        echo "User  ID is not set in the session.";
        exit();
    }

    // Fetch RegNo from users table
    $regNoQuery = "SELECT RegNo FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($regNoQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($regNo);
    $stmt->fetch();
    $stmt->close();

    // Check if the registration number matches
    if ($registration_number !== $regNo) {
        echo "Registration number does not match the user's RegNo.";
        exit();
    }

    // Check if the registration number already exists in the schools table
    $checkQuery = "SELECT COUNT(*) FROM schools WHERE registration_number = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $registration_number);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        // Duplicate registration number found
        echo "<script>
                alert('You have already registered this school.'); 
                window.location.href = 'HighDash.html';
              </script>";
        exit();
    }

    // Prepare SQL insert statement
    $stmt = $conn->prepare("INSERT INTO schools (
        principal_name, 
        principal_surname, 
        phone_number, 
        email_address, 
        constituency,
        cluster,
        typeofschool,
        school_name, 
        registration_number, 
        male_teachers, 
        female_teachers,
        council,
        village,
        user_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param(
        "ssissssssiissi", 
        $principal_name,
        $principal_surname,
        $principal_phone,
        $principal_email,
        $constituency,
        $cluster,
        $typeofschool,
        $school_name,
        $registration_number,
    //    $number_of_teachers,
        $male_teachers,
        $female_teachers,
        $council,
        $village,
        $user_id
    );

    // Execute the statement and check for success
    if ($stmt->execute()) {
        // Data inserted successfully, redirect to update_status.php
        header("Location: highschoolstatusupdate.php");
        exit();
    } else {
        // Insertion failed, display error message
        echo "Error inserting school: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Information Form - Ministry of Education</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body class="font-inter bg-gray-50 text-gray-800 min-h-screen">
    <!-- Professional Loading Screen -->
    <div id="loader" class="fixed inset-0 bg-gray-50 flex flex-col justify-center items-center z-50 transition-opacity duration-500">
        <img src="moet.png" alt="Ministry Logo" class="w-20 h-auto mb-6 rounded-lg border-2 border-blue-800">
        <div class="w-12 h-12 border-4 border-gray-200 border-t-blue-600 rounded-full animate-spin mb-4"></div>
        <div class="text-xl font-semibold text-blue-800 mb-2">Loading School Registration Form</div>
        <div class="text-sm text-gray-600 mb-8">Ministry of Education & Training</div>
        <div class="w-48 h-1 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-blue-600 to-blue-800 w-0 animate-pulse" style="animation-duration: 2s;"></div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="fixed w-full bg-white shadow-md z-40">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold text-blue-800">Ministry of Education & Training Leribe</span>
                </div>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="#form" class="text-gray-600 hover:text-blue-800 transition font-medium">Form</a>
                    <a href="#contact" class="text-gray-600 hover:text-blue-800 transition font-medium" onclick="toggleContactInfo()">Contact</a>
                    <a href="profile.html" class="text-gray-600 hover:text-blue-800 transition">
                        <i class="fas fa-user-circle text-2xl"></i>
                    </a>
                    <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="pt-20 pb-16 bg-gradient-to-r from-blue-800 to-indigo-900 text-white text-center">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <img src="moet.png" alt="Ministry of Education" class="mx-auto w-20 h-auto mb-6 rounded-lg border-2 border-white/20">
            <h1 class="text-4xl font-bold mb-4">School Information Form</h1>
            <h2 class="text-xl font-medium opacity-90">Ministry of Education & Training Leribe</h2>
        </div>
    </header>

    <!-- Contact Section -->
    <section id="contact" class="contact-info hidden py-10 bg-blue-50 border-b border-blue-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Contact Information</h3>
            <p class="text-blue-800 text-lg mb-2">Email: moshabesham@yahoo.com</p>
            <p class="text-blue-800 text-lg">Phone: +266 53836808 || 63121506</p>
        </div>
    </section>

    <!-- Form Section -->
    <section id="form" class="py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <form action="highschool.php" method="POST" class="space-y-8">
                <!-- Principal Information Section -->
                <div class="bg-gradient-to-r from-cyan-50 to-blue-50 p-8 rounded-lg shadow-md border border-blue-200">
                    <h3 class="text-2xl font-semibold text-blue-800 mb-6 border-b-2 border-blue-800 pb-3">Principal Information</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="principal_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <input type="text" id="principal_name" name="principal_name" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Enter first name">
                        </div>
                        <div>
                            <label for="principal_surname" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <input type="text" id="principal_surname" name="principal_surname" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Enter last name">
                        </div>
                        <div>
                            <label for="principal_phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" id="principal_phone" name="principal_phone" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="+266 12345678">
                        </div>
                        <div>
                            <label for="principal_email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" id="principal_email" name="principal_email" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="example@email.com">
                        </div>
                    </div>
                </div>

                <!-- General Information Section -->
                <div class="bg-gradient-to-r from-cyan-50 to-blue-50 p-8 rounded-lg shadow-md border border-blue-200">
                    <h3 class="text-2xl font-semibold text-blue-800 mb-6 border-b-2 border-blue-800 pb-3">General Information</h3>
                    <div class="space-y-6">
                        <div>
                            <label for="school_name" class="block text-sm font-medium text-gray-700 mb-2">School Name</label>
                            <input type="text" id="school_name" name="school_name" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label for="registration" class="block text-sm font-medium text-gray-700 mb-2">Registration Number</label>
                            <input type="text" id="registration" name="registration" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label for="typeofschool" class="block text-sm font-medium text-gray-700 mb-2">Type of School</label>
                            <select id="typeofschool" name="typeofschool" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option value="">Select type</option>
                                <option value="Government">Government</option>
                                <option value="Community">Community</option>
                                <option value="RCC">RCC</option>
                                <option value="LECSA">LECSA</option>
                                <option value="Independent">Independent</option>
                                <option value="ACL">ACL</option>
                            </select>
                        </div>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label for="male_teachers" class="block text-sm font-medium text-gray-700 mb-2">Male Teachers</label>
                                <input type="number" id="male_teachers" name="male_teachers" min="0" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            </div>
                            <div>
                                <label for="female_teachers" class="block text-sm font-medium text-gray-700 mb-2">Female Teachers</label>
                                <input type="number" id="female_teachers" name="female_teachers" min="0" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            </div>
                        </div>
                        <div>
                            <label for="constituency" class="block text-sm font-medium text-gray-700 mb-2">Constituency</label>
                            <input type="text" id="constituency" name="constituency" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label for="cluster" class="block text-sm font-medium text-gray-700 mb-2">Cluster</label>
                            <select id="cluster" name="cluster" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option value="">Select cluster</option>
                                <option value="ST SAVIOUS">ST SAVIOUS</option>
                                <option value="ST ROSE">ST ROSE</option>
                                <option value="HOLY TRINITY">HOLY TRINITY</option>
                                <option value="ST LUKE">ST LUKE</option>  
                                <option value="MALIBA-MATSO">MALIBA-MATSO</option>
                                <option value="ST PHILIPS">ST PHILIPS</option>
                                <option value="OTHER">OTHER</option>
                            </select>
                        </div>
                        <div>
                            <label for="council" class="block text-sm font-medium text-gray-700 mb-2">Council</label>
                            <input type="text" id="council" name="council" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label for="village" class="block text-sm font-medium text-gray-700 mb-2">Village</label>
                            <input type="text" id="village" name="village" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 px-6 rounded-lg font-semibold text-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    Submit Information
                </button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-auto">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; 2025 Ministry of Education and Training. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Hide loading screen after page loads
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loader').classList.add('opacity-0', 'pointer-events-none');
            }, 1500); // 1.5 second delay for effect
        });

        function toggleContactInfo() {
            const contactSection = document.getElementById('contact');
            contactSection.classList.toggle('hidden');
        }
    </script>
</body>
</html>