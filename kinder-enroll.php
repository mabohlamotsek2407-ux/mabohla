<?php
session_start(); // Start the session

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

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Retrieve the registration number and school ID from the schools table
$user_id = $_SESSION['user_id'];

// Prepare and execute the query for registration number and school name
$regNoQuery = "SELECT registration_number, school_id, school_name FROM schools WHERE user_id = ?";
$stmt = $conn->prepare($regNoQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($registration_number, $school_id, $school_name);
$stmt->fetch();
$stmt->close();

// Check if school_name is set, if not set it to an empty string
if (!isset($school_name)) {
    $school_name = ''; // Default to an empty string if not found
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $reception_female = $_POST['recF']; 
    $reception_male = $_POST['recM']; 
    $age3_girls = $_POST['age3_girls'];
    $age3_boys = $_POST['age3_boys'];
    $age4_girls = $_POST['age4_girls'];
    $age4_boys = $_POST['age4_boys'];
    $age5_girls = $_POST['age5_girls'];
    $age5_boys = $_POST['age5_boys'];
    $school_id = $_POST['school_id']; // Get the school ID from the form

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO preschool_enrollment ( female_reception, male_reception, age3_girls,age3_boys,age4_girls, age4_boys, age5_girls, age5_boys, school_id) 
    VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?)" );
    
    // Bind parameters
    $stmt->bind_param("iiiiiiiii", $reception_female, $reception_male, $age3_girls, $age3_boys, $age4_girls, $age4_boys, $age5_girls, $age5_boys, $school_id);

    // Execute the statement
    if ($stmt->execute()) {
        echo "New record created successfully";
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
    <title>Kindergarten Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3B82F6;  /* Blue-500 */
            --secondary: #1D4ED8; /* Blue-700 */
            --accent: #93C5FD;   /* Light blue */
            --dark: #2563EB;     /* Blue-600 */
            --light: #EFF6FF;    /* Lightest blue */
            --rainbow-1: #FF9FF3; /* Pink */
            --rainbow-2: #FECA57; /* Yellow */
            --rainbow-3: #FF6B6B; /* Red */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Comic Neue', cursive;
            background-color: var(--light);
            color: #333;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .header-image {
            width: 70px; /* Reduced size from 110px to 70px */
            height: auto;
            border-radius: 10px;
            float: left;
            margin-right: 20px;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            animation: bounceIn 1s ease-out, floatHeader 8s ease-in-out infinite;
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            border: 2px solid var(--accent);
        }

        @keyframes floatHeader {
            0%, 100% { transform: translateY(0) rotate(-1deg); }
            50% { transform: translateY(-10px) rotate(1deg); }
        }

        h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--accent);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.5s ease;
        }

        h1:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        .section {
            margin-top: 20px;
            padding: 20px;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 15px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        th {
            background-color: var(--dark);
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 1rem;
            position: relative;
        }

        th:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--accent);
        }

        td {
            background-color: rgba(255, 255, 255, 0.9);
            border-bottom: 2px solid #f0f0f0;
        }

        tr:hover td {
            background-color: rgba(219, 234, 254, 0.7);
            transform: scale(1.02);
        }

        tr:last-child td {
            border-bottom: none;
        }

        input[type="text"],
        input[type="number"] {
            padding: 10px 15px;
            border: 2px solid var(--accent);
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.8);
            font-family: 'Comic Neue', cursive;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        }

        input[type="text"]:focus,
        input[type="number"]:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(29, 209, 161, 0.3);
            outline: none;
            transform: scale(1.02);
            background-color: white;
        }

        button {
            background-color: var(--primary);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.3s ease;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
        }

        .bg {
            background-color: red;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.3s ease;
            cursor: pointer;
            width: 10%;
            position: relative;
        }

        footer {
            background-color: #1f2937; /* Tailwind gray-800 */
            color: white;
            padding: 20px 0;
        }

        /* Animation for the header */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .section {
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <header class="pt-24 pb-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <img src="moet.png" alt="Ministry of Education" class="header-image">
            <h1 class="text-4xl font-bold text-blue-500 text-center mt-6">Pre-School Enrollment Data</h1>
            <h2 class="text-xl text-blue-500 text-center mt-2">Ministry of Education & Training Leribe</h2>
            <button class="bg" onclick="window.location.href='kinder-Dash.html'">Back</button>
        </div>
    </header>

    <form action="kinder-enroll.php" method="POST" class="space-y-8">
    <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>">
    <input type="hidden" name="registration_number" value="<?php echo htmlspecialchars($registration_number); ?>">

    <!-- Data Table Section -->
    <section class="section py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <table>
                <thead>
                    <tr>
                        <th>SCHOOL</th>
                        <th>REG NO</th>
                        <th>RECEPTION</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <input type="text" id="school_name" name="school_name" value="<?php echo htmlspecialchars($school_name); ?>" readonly
                                class="w-auto px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </td>
                        <td>
                            <input type="text" id="regN" name="regN" value="<?php echo htmlspecialchars($registration_number); ?>" readonly
                                class="w-auto px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </td>
                        <td>
                            <input type="number" id="recF" name="recF" required placeholder="Female Reception" class="w-full px-4 py-3">
                            <input type="number" id="recM" name="recM" required placeholder="Male Reception" class="w-full px-4 py-3">
                        </td>
                    </tr>
                </tbody>
                <thead>
                    <tr>
                        <th>AGE 3</th>
                        <th>AGE 4</th>
                        <th>AGE 5</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <input type="number" id="age3g" name="age3_girls" required placeholder="Girls" class="w-full px-4 py-3">
                            <input type="number" id="age3b" name="age3_boys" required placeholder="Boys" class="w-full px-4 py-3">
                        </td>
                        <td>
                            <input type="number" id="age4g" name="age4_girls" required placeholder="Girls" class="w-full px-4 py-3">
                            <input type="number" id="age4b" name="age4_boys" required placeholder="Boys" class="w-full px-4 py-3">
                        </td>
                        <td>
                            <input type="number" id="age5g" name="age5_girls" required placeholder="Girls" class="w-full px-4 py-3">
                            <input type="number" id="age5b" name="age5_boys" required placeholder="Boys" class="w-full px-4 py-3">
                        </td>
                    </tr>
                </tbody>
            </table>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition w-full">Submit Information</button>
        </div>
    </section>
</form>

    <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; 2025 Ministry of Education and Training. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
