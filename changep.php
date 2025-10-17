<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['RegNo'])) {
    header("Location: login.php"); // Redirect to login if not logged in (assume login.php exists)
    exit();
}

$regNo = $_SESSION['RegNo'];

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection configuration
$servername = "sql104.infinityfree.com";
$username = "if0_40021406"; 
$password = "Op70TI711cS2lB6";
$dbname = "if0_40021406_moet1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = trim($_POST['current_password']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "New password and confirmation do not match.";
    } elseif (strlen($newPassword) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        // Fetch current hashed password from database
        $stmt = $conn->prepare("SELECT password FROM users WHERE RegNo = ?");
        $stmt->bind_param("s", $regNo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Verify current password
            if (password_verify($currentPassword, $row['password'])) {
                // Hash new password
                $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Update password in database
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE RegNo = ?");
                $updateStmt->bind_param("ss", $hashedNewPassword, $regNo);
                
                if ($updateStmt->execute()) {
                    // On success, immediately redirect to redirectpage.php
                    $updateStmt->close();
                    $stmt->close();
                    $conn->close();
                    header("Location: redirectpage.php");
                    exit();
                } else {
                    $error = "Failed to update password. Please try again.";
                }
                $updateStmt->close();
            } else {
                $error = "Current password is incorrect.";
            }
        } else {
            $error = "User  not found.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Body with classic background image and overlay */
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            background: 
                linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
                url('https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Form container with glassmorphism effect */
        .form-container {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 2.5rem 3rem;
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 480px;
            animation: fadeInUp 0.8s ease forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        /* Animate form container on load */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Heading */
        .form-container h1 {
            font-weight: 700;
            font-size: 2rem;
            color: #1e3a8a;
            text-align: center;
            margin-bottom: 2rem;
            letter-spacing: 1px;
            text-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        /* Labels */
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #334155;
            text-shadow: 0 1px 1px rgba(255,255,255,0.7);
        }

        /* Inputs and selects */
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #cbd5e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background-color: rgba(255,255,255,0.9);
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        }

        input[type="password"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 8px rgba(59,130,246,0.6);
            outline: none;
        }

        /* Success and Error messages */
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            text-align: center;
        }

        .success {
            background-color: rgba(34, 197, 94, 0.2);
            color: #16a34a;
            border: 1px solid #22c55e;
        }

        .error {
            background-color: rgba(239, 68, 68, 0.2);
            color: #dc2626;
            border: 1px solid #ef4444;
        }

        /* Button styles */
        button[type="submit"] {
            width: 100%;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            padding: 14px 0;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.5);
            transition: background 0.4s ease, box-shadow 0.4s ease, transform 0.2s ease;
            user-select: none;
        }

        button[type="submit"]:hover {
            background: linear-gradient(135deg, #1e40af, #2563eb);
            box-shadow: 0 8px 20px rgba(30, 64, 175, 0.7);
            transform: translateY(-3px);
        }

        button[type="submit"]:active {
            transform: translateY(-1px);
        }

        /* Button container */
        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 1.75rem;
            gap: 1rem;
        }

        /* Back and Logout buttons */
        .button-container .btn {
            flex: 1;
            padding: 12px 0;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            user-select: none;
            transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }

        .button-container .btn.bg-gray-100 {
            background-color: rgba(243, 244, 246, 0.9);
            color: #475569;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .button-container .btn.bg-gray-100:hover {
            background-color: rgba(229, 231, 235, 0.95);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .button-container .btn.bg-red-600 {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: white;
            box-shadow: 0 6px 15px rgba(220, 38, 38, 0.5);
        }

        .button-container .btn.bg-red-600:hover {
            background: linear-gradient(135deg, #b91c1c, #dc2626);
            box-shadow: 0 8px 20px rgba(185, 28, 28, 0.7);
            transform: translateY(-2px);
        }

        /* Icons inside buttons */
        button i, .button-container .btn i {
            font-size: 1.2rem;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .form-container {
                padding: 2rem 1.5rem;
                border-radius: 0.75rem;
                width: 90%;
            }
            .button-container {
                flex-direction: column;
            }
            .button-container .btn {
                width: 100%;
                justify-content: center;
            }
            button[type="submit"] {
                font-size: 1rem;
                padding: 12px 0;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1 class="text-2xl font-bold text-center mb-6 text-gray-800">Change Password</h1>
        
        <?php if ($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <script>
                // Show pop-up alert with error message and redirect on OK
                alert("<?php echo htmlspecialchars($error); ?>");
                window.location.href = "changep.php";
            </script>
        <?php endif; ?>
        
        <form action="change_password.php" method="POST">
            <div class="mb-4">
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                <input type="password" id="current_password" name="current_password" required class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition duration-150" placeholder="Enter Current Password">
            </div>
            <div class="mb-4">
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input type="password" id="new_password" name="new_password" required class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition duration-150" placeholder="Enter New Password">
            </div>
            <div class="mb-6">
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition duration-150" placeholder="Confirm New Password">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-medium flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150">
                <i class="fas fa-key mr-2"></i>
                Update Password
            </button>
        </form>
        <div class="button-container">
            <a href="Admin.php" class="btn bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-4 rounded-lg font-medium flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                <i class="fas fa-arrow-left mr-2"></i>
                Back
            </a>
            <a href="logout.php" class="btn bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-lg font-medium flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Logout
            </a>
        </div>
    </div>
</body>
</html>