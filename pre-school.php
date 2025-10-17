<?php
session_start(); // Start the session

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    $typeofschool = $_POST['typeofschool'] ?? '';
    $school_name = $_POST['school_name'] ?? '';
    $registration_number = $_POST['registration'] ?? '';
    $number_of_teachers = (int) ($_POST['teachers'] ?? 0);
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
                window.location.href = 'kinder-dash.html';
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
        typeofschool,
        school_name, 
        registration_number, 
        number_of_teachers, 
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
        "ssisssssiiissi", 
        $principal_name,
        $principal_surname,
        $principal_phone,
        $principal_email,
        $constituency,
        $typeofschool,
        $school_name,
        $registration_number,
        $number_of_teachers,
        $male_teachers,
        $female_teachers,
        $council,
        $village,
        $user_id
    );

    // Execute the statement and check for success
    if ($stmt->execute()) {
        // Data inserted successfully, redirect to update_status.php
        header("Location: kinder_statusupdate.php");
        exit();
    } else {
        // Insertion failed, display error message
        echo "Error registering your school: " . $stmt->error;
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
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: #374151;
        }

        .header-image {
            width: 110px; /* Decreased size */
            height: auto;
            border-radius: 10px;
            float: left; /* Align to the left */
            margin-right: 20px; /* Space between image and text */
        }

        

        .section {
            margin-top: 20px;
            padding: 20px;
            border-radius: 10px;
        }

        button {
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        .student-image {
            width: 100%;
            height: auto;
            border-radius: 10px;
            margin-top: 20px;
        }

        .contact-info {
            display: none; /* Initially hide contact info */
        }

        .general-info,
        .infrastructure,
        .utilities,
        .playgrounds,
        .principal-info {
            background-color: #e0f7fa; /* Consistent light cyan color */
        }

        .principal-info h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e3a8a; /* Deep blue */
            margin-bottom: 16px;
        }

        header{
                height:30px;
        }
        /* Targeting all td elements to display inline-block */
td {
    display: inline-block;
    vertical-align: top;
    margin-right: 10px;
    margin-bottom: 10px;
    width: 12px;
}

/* Style the inputs inside td elements */
td input {
    width: 150px; /* Fixed width or use min-width as needed */
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    display: inline-block;
}

/* For the toilet section specifically */
.toilets-section td {
    display: inline-block;
    width: 45px;
    margin: 0 10px 0 0;
}

/* Adjust the placeholder alignment */
td input::placeholder {
    color: #999;
    font-style: italic;
}

/* Clear floats between sections */
.section:after {
    content: "";
    display: table;
    clear: both;
}

    </style>
</head>

<body>
    <form action="pre-school.php" method="POST" class="space-y-8">
    <!-- Navigation -->
    <nav class="fixed w-full bg-white shadow-md z-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold text-blue-600">Ministry of Education & Training Leribe</span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#form" class="text-gray-700 hover:text-blue-600 transition">Form</a>
                    <a href="#contact" class="text-gray-700 hover:text-blue-600 transition" onclick="toggleContactInfo()">
                        <i class="fas fa-address-book"></i> Contact
                    </a>
                    <a href="profile.html" class="text-gray-700 hover:text-blue-600 transition">
                        <i class="fas fa-user-graduate"></i> <img src="icon.png" width="20" height="20" alt="Profile Icon">
                    </a>
                    <button id="logoutButton" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-500 focus:outline-none"><a href="homepage.html">Logout</a></button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="pt-24 pb-16 bg-gradient-to-r from-blue-50 to-indigo-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8" >
            <img src="moet.png" alt="Ministry of Education" class="header-image">
            <h1 class="text-4xl font-bold text-blue-600 text-center mt-6">School Information Form</h1>
            <h2 class="text-xl text-blue-600 text-center mt-2">Ministry of Education & Training Leribe</h2>
        </div>
    </header>

    <!-- Contact Information Section -->
    <section id="contact" class="contact-info py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Contact Information</h3>
            <p style="color: #1e3a8a;">Email: moshabesham@yahoo.com</p>
            <p style="color: #1e3a8a;">Phone: +266 53836808||63121506</p>
        </div>
    </section>

    <!-- Form Section -->
    <section id="form" class="section py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <form class="space-y-8">
                <!-- Principal Information Section -->
                <div class="section principal-info">
                    <h3 class="text-2xl font-semibold text-gray-800 mb-4">Principal Information</h3>
                    <div class="space-y-6">
                        <div>
                            <label for="principal" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" id="principal" name="principal_name" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Name">
                        </div>
                        <div>
                            <label for="principal" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" id="principal" name="principal_surname" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Surname">
                        </div>
                        <div>
                            <label for="principal_phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="text" id="principal_phone" name="principal_phone"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="+266 12345678">
                        </div>
                        <div>
                            <label for="principal_email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" id="principal_email" name="principal_email"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="e.g email@gmail.com">
                        </div>
                    </div>
                </div>

                <!-- General Information Section -->
                <div class="section general-info">
                    <h3 class="text-2xl font-semibold text-gray-800 mb-4">General Information</h3>
                    <div class="space-y-6">
                        <div class="le">
                            <label for="shelter" class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                            <select id="shelter" name="level_of_school" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                          
                                <option value="Primary">Primary School</option>
                                <option value="Pre">Pre-School</option>
                            </select>
                        </div>
                        <div class="ce">
                            <label for="shelter" class="block text-sm font-medium text-gray-700 mb-1">Center</label>
                            <select id="shelter" name="center" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="Holy-Trinity">Holy-Trinity</option>
                                <option value="Kolonyama">Kolonyama </option>
                                <option value="Laghetto">Laghetto</option>
                                <option value="Makokoane">Makokoane</option>
                                <option value="Maliba-Matso">Maliba-Matso</option>  
                                <option value="Pitseng">Pitseng</option>
                                <option value="ST Luke">ST Luke</option>
                                <option value="ST Phillips">ST Phillips</option>
                                <option value="ST Rose">ST Rose</option>
                                <option value="ST Saviours B">ST Saviours B</option>
                                <option value="ST Saviours A">ST Saviours A</option>
                            </select>
                        </div>
                        <div>
                            <label for="school_name" class="block text-sm font-medium text-gray-700 mb-1">School Name</label>
                            <input type="text" id="school_name" name="school_name" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="registration" class="block text-sm font-medium text-gray-700 mb-1">Registration Number</label>
                            <input type="text" id="registration" name="registration" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" >
                        </div>
                        <div>
                            <label for="shelter" class="block text-sm font-medium text-gray-700 mb-1">Type of school</label>
                            <select id="shelter" name="typeofschool" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="Government">Government School</option>
                                <option value="Private">Private school</option>
                                <option value="Special">Special & Inclusive school</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="teachers" class="block text-sm font-medium text-gray-700 mb-1">Number of Teachers</label>
                            <input type="number" id="teachers" name="teachers" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" >
                        </div>
                        <div>
                            <label for="male_students" class="block text-sm font-medium text-gray-700 mb-1">Male Teachers</label>
                            <input type="number" id="male_students" name="male_teachers" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" >
                        </div>
                        <div>
                            <label for="female_students" class="block text-sm font-medium text-gray-700 mb-1">Female Teachers</label>
                            <input type="number" id="female_students" name="female_teachers" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="constituency" class="block text-sm font-medium text-gray-700 mb-1">constituency</label>
                            <input type="textarea" id="constituency" name="constituency" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="Council" class="block text-sm font-medium text-gray-700 mb-1">Council</label>
                            <input type="textarea" id="Council" name="council" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="Village" class="block text-sm font-medium text-gray-700 mb-1">Village</label>
                            <input type="textarea" id="Village" name="village" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition w-full">Submit
                    Information</button>
            </form>
        </div>
    </section>

    <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; 2025 Ministry of Education and Training. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function toggleContactInfo() {
            const contactSection = document.querySelector('.contact-info');
            contactSection.style.display = contactSection.style.display === 'none' || contactSection.style.display === '' ? 'block' : 'none';
        }

        


document.addEventListener('DOMContentLoaded', function() {
    const levelSelect = document.getElementById('level'); // Level dropdown
    const centerSelect = document.getElementById('center'); // Center dropdown

    function toggleCenterVisibility() {
        const selectedLevel = levelSelect.value;
        if (selectedLevel === 'High' || selectedLevel === 'Pre') {
            centerSelect.style.display = 'none'; // Hide center dropdown
        } else if (selectedLevel === 'Primary') {
            centerSelect.style.display = 'block'; // Show center dropdown
        }
    }

    levelSelect.addEventListener('change', toggleCenterVisibility);
    toggleCenterVisibility(); // Initial check on page load
});

    </script>
    </form>
</body>

</html>
