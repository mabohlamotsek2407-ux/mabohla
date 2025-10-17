<?php
session_start();

// Enable error reporting
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $regNo = trim($_POST['RegNo']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $level = $_POST['level'];

    // Validate inputs (basic professional validation)
    if (empty($regNo) || empty($password) || empty($role) || empty($level)) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if RegNo already exists
        $checkStmt = $conn->prepare("SELECT RegNo FROM users WHERE RegNo = ?");
        $checkStmt->bind_param("s", $regNo);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $error = "Registration number already exists.";
        } else {
            // Hash the password for security
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO users (RegNo, password, role, level) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $regNo, $hashedPassword, $role, $level);

            // Execute the statement
            if ($stmt->execute()) {
                $success = "New user registered successfully. You can now log in.";
            } else {
                $error = "Registration failed. Please try again.";
            }

            // Close the statement
            $stmt->close();
        }
        $checkStmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration - Ministry of Education</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Link to external CSS file for styles (create styles.css with the CSS from previous response) -->
    <link rel="stylesheet" href="styles.css">
    <style>
    /* Root variables for light mode (professional and consistent) */
:root {
    --bg-overlay: rgba(255,255,255,0.1);
    --form-bg: rgba(255, 255, 255, 0.95);
    --form-border: rgba(0, 0, 0, 0.1);
    --input-bg: rgba(255,255,255,0.9);
    --input-border: #cbd5e1;
    --text-primary: #1e3a8a;
    --text-secondary: #334155;
    --text-placeholder: #6b7280;
    --focus-color: #3b82f6;
    --error-bg: rgba(239, 68, 68, 0.1);
    --error-border: rgba(239, 68, 68, 0.2);
    --error-text: #dc2626;
    --success-bg: rgba(34, 197, 94, 0.1);
    --success-border: rgba(34, 197, 94, 0.2);
    --success-text: #059669;
    --btn-primary-bg: linear-gradient(135deg, #3b82f6, #1d4ed8);
    --btn-primary-hover: linear-gradient(135deg, #2563eb, #1e40af);
    --btn-secondary-bg: rgba(71, 85, 105, 0.8);
    --btn-secondary-hover: rgba(51, 65, 85, 0.9);
    --btn-logout-bg: linear-gradient(135deg, #dc2626, #ef4444);
    --btn-logout-hover: linear-gradient(135deg, #b91c1c, #dc2626);
    --box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    --loading-bg: linear-gradient(135deg, rgba(59, 130, 246, 0.95) 0%, rgba(30, 58, 138, 0.95) 100%);
    --loading-spinner-primary: #60a5fa;
    --loading-spinner-secondary: #3b82f6;
    --loading-spinner-tertiary: #1d4ed8;
}

/* Dark mode variables (professional dark theme) */
body.dark {
    --bg-overlay: rgba(0,0,0,0.7);
    --form-bg: rgba(30, 41, 59, 0.9);
    --form-border: rgba(255, 255, 255, 0.1);
    --input-bg: rgba(51, 65, 85, 0.8);
    --input-border: #475569;
    --text-primary: #f8fafc;
    --text-secondary: #cbd5e1;
    --text-placeholder: #94a3b8;
    --focus-color: #60a5fa;
    --error-bg: rgba(239, 68, 68, 0.2);
    --error-border: rgba(239, 68, 68, 0.3);
    --error-text: #fca5a5;
    --success-bg: rgba(34, 197, 94, 0.2);
    --success-border: rgba(34, 197, 94, 0.3);
    --success-text: #86efac;
    --btn-primary-bg: linear-gradient(135deg, #1e40af, #3b82f6);
    --btn-primary-hover: linear-gradient(135deg, #1d4ed8, #2563eb);
    --btn-secondary-bg: rgba(71, 85, 105, 0.8);
    --btn-secondary-hover: rgba(51, 65, 85, 0.9);
    --btn-logout-bg: linear-gradient(135deg, #dc2626, #ef4444);
    --btn-logout-hover: linear-gradient(135deg, #b91c1c, #dc2626);
    --box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    --loading-bg: linear-gradient(135deg, rgba(30, 64, 175, 0.95) 0%, rgba(15, 23, 42, 0.95) 100%);
    --loading-spinner-primary: #93c5fd;
    --loading-spinner-secondary: #60a5fa;
    --loading-spinner-tertiary: #3b82f6;
}

/* Shared Base Styles - Professional and Clean */
body {
    font-family: 'Poppins', sans-serif;
    min-height: 100vh;
    margin: 0;
    padding: 20px;
    background: 
        linear-gradient(var(--bg-overlay), var(--bg-overlay)),
        url('https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed;
    background-size: cover;
    display: flex;
    justify-content: center;
    align-items: center;
    color: var(--text-primary);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-container {
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border: 1px solid var(--form-border);
    padding: 2.5rem 3rem;
    border-radius: 1rem;
    box-shadow: var(--box-shadow), inset 0 1px 0 rgba(255, 255, 255, 0.1);
    width: 100%;
    max-width: 480px;
    animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    opacity: 0;
    transform: translateY(20px);
    position: relative;
    background: var(--form-bg);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-container h1 {
    font-weight: 700;
    font-size: 2rem;
    text-align: center;
    margin-bottom: 2rem;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: color 0.3s ease;
    margin-top: 0;
}

.mb-4 {
    margin-bottom: 1rem;
}

.mb-6 {
    margin-bottom: 1.5rem;
}

label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-secondary);
    font-size: 0.95rem;
    transition: color 0.3s ease;
}

input[type="text"],
input[type="password"],
select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--input-border);
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background-color: var(--input-bg);
    color: var(--text-primary);
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    font-family: inherit;
}

input[type="text"]:focus,
input[type="password"]:focus,
select:focus {
    border-color: var(--focus-color);
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1), inset 0 1px 3px rgba(0,0,0,0.1);
    outline: none;
    background-color: var(--input-bg);
    transform: translateY(-1px);
}

input::placeholder {
    color: var(--text-placeholder);
    transition: color 0.3s ease;
}

button[type="submit"] {
    width: 100%;
    background: var(--btn-primary-bg);
    color: white;
    padding: 14px 0;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1.1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    box-shadow: 0 6px 15px rgba(30, 64, 175, 0.4);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
    position: relative;
    overflow: hidden;
    font-family: inherit;
    letter-spacing: 0.025em;
}

button[type="submit"]:hover:not(:disabled) {
    background: var(--btn-primary-hover);
    box-shadow: 0 8px 20px rgba(30, 64, 175, 0.5);
    transform: translateY(-2px);
}

button[type="submit"]:active:not(:disabled) {
    transform: translateY(0);
}

/* Professional Button Loading State */
button[type="submit"].loading {
    background: linear-gradient(135deg, #475569, #64748b);
    cursor: not-allowed;
    transform: none;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

button[type="submit"].loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.button-container {
    display: flex;
    justify-content: space-between;
    margin-top: 1.75rem;
    gap: 1rem;
}

.button-container .btn {
    flex: 1;
    padding: 12px 0;
    border-radius: 10px;
    font-weight: 500;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    user-select: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    border: none;
    text-decoration: none;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    color: white;
    font-family: inherit;
    letter-spacing: 0.025em;
}

.button-container .btn-back {
    background: var(--btn-secondary-bg);
    color: #e2e8f0;
}

.button-container .btn-back:hover {
    background: var(--btn-secondary-hover);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
    transform: translateY(-2px);
}

.button-container .btn-logout {
    background: var(--btn-logout-bg);
    color: white;
}

.button-container .btn-logout:hover {
    background: var(--btn-logout-hover);
    box-shadow: 0 6px 15px rgba(220, 38, 38, 0.4);
    transform: translateY(-2px);
}

button i, .button-container .btn i {
    font-size: 1.2rem;
    transition: transform 0.2s ease;
}

.button-container .btn:hover i {
    transform: scale(1.1);
}

.message {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    animation: slideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.error {
    background: var(--error-bg);
    color: var(--error-text);
    border: 1px solid var(--error-border);
}

.success {
    background: var(--success-bg);
    color: var(--success-text);
    border: 1px solid var(--success-border);
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(-10px); }
    to { opacity: 1; transform: translateX(0); }
}

/* Professional Loading Overlay - Enhanced for Registration */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--loading-bg);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(10px);
    opacity: 0;
    transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.loading-overlay.show {
    display: flex;
    opacity: 1;
}

.loading-content {
    text-align: center;
    color: white;
    font-family: 'Poppins', sans-serif;
    max-width: 400px;
    padding: 2rem;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.loading-spinner {
    position: relative;
    width: 80px;
    height: 80px;
    margin: 0 auto 2rem;
}

.spinner-ring {
    position: absolute;
    border: 4px solid rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    animation: spin 1.5s linear infinite;
}

.spinner-ring:nth-child(1) {
    width: 80px;
    height: 80px;
    border-top-color: var(--loading-spinner-primary);
    animation-delay: 0s;
}

.spinner-ring:nth-child(2) {
    width: 60px;
    height: 60px;
    top: 10px;
    left: 10px;
    border-top-color: var(--loading-spinner-secondary);
    animation-delay: -0.5s;
}

.spinner-ring:nth-child(3) {
    width: 40px;
    height: 40px;
    top: 20px;
    left: 20px;
    border-top-color: var(--loading-spinner-tertiary);
    animation-delay: -1s;
}

.loading-text {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    animation: pulse 2s infinite;
}

.loading-subtext {
    font-size: 1rem;
    opacity: 0.8;
    font-weight: 400;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.loading-progress {
    width: 200px;
    height: 4px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 2px;
    margin: 1.5rem auto 0;
    overflow: hidden;
}

.loading-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--loading-spinner-primary), var(--loading-spinner-secondary), var(--loading-spinner-tertiary));
    border-radius: 2px;
    width: 0%;
    animation: progressBar 2s ease-in-out infinite;
}

@keyframes progressBar {
    0% { width: 0%; transform: translateX(-100%); }
    50% { width: 100%; transform: translateX(0%); }
    100% { width: 100%; transform: translateX(100%); }
}

/* Success Animation for Registration */
.success-checkmark {
    display: none;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #10b981;
    margin: 0 auto 1rem;
    position: relative;
}

.success-checkmark.show {
    display: block;
    animation: successPop 0.5s ease-out;
}

.success-checkmark::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 2.5rem;
    font-weight: bold;
}

@keyframes successPop {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}

/* Responsive */
@media (max-width: 480px) {
    body { padding: 10px; }
    .form-container {
        padding: 2rem 1.5rem;
        border-radius: 0.75rem;
        width: 95%;
    }
    .button-container {
        flex-direction: column;
    }
    .button-container .btn {
        width: 100%;
    }
    button[type="submit"] {
        font-size: 1rem;
        padding: 12px 0;
    }
    .loading-spinner {
        width: 60px;
        height: 60px;
    }
    .spinner-ring:nth-child(1) {
        width: 60px;
        height: 60px;
    }
    .spinner-ring:nth-child(2) {
        width: 45px;
        height: 45px;
        top: 7.5px;
        left: 7.5px;
    }
    .spinner-ring:nth-child(3) {
        width: 30px;
        height: 30px;
        top: 15px;
        left: 15px;
    }
    .loading-progress {
        width: 150px;
    }
}

/* Accessibility */
input:focus, select:focus, button:focus {
    outline: 2px solid var(--focus-color);
    outline-offset: 2px;
}
    </style>
</head>
<body> <!-- No default class; JS will apply based on localStorage -->
    <div class="form-container">
        <h1><i class="fas fa-user-plus"></i>User Registration</h1>
        
        <?php if (isset($error)): ?>
            <div class="message error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form id="registrationForm" action="register.php" method="POST">
            <div class="mb-4">
                <label for="RegNo">Registration Number</label>
                <input type="text" id="RegNo" name="RegNo" value="<?php echo isset($_POST['RegNo']) ? htmlspecialchars($_POST['RegNo']) : ''; ?>" required placeholder="Enter unique registration number">
            </div>
            <div class="mb-4">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter secure password (min 6 chars)">
            </div>
            <div class="mb-4">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="principal" <?php echo (isset($_POST['role']) && $_POST['role'] === 'principal') ? 'selected' : ''; ?>>Principal</option>
                    <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="user" <?php echo (isset($_POST['role']) && $_POST['role'] === 'user') ? 'selected' : ''; ?>>User </option>
                </select>
            </div>
            <div class="mb-6">
                <label for="level">Level</label>
                <select id="level" name="level" required>
                    <option value="">Select Level</option>
                    <option value="High" <?php echo (isset($_POST['level']) && $_POST['level'] === 'High') ? 'selected' : ''; ?>>High School</option>
                    <option value="Primary" <?php echo (isset($_POST['level']) && $_POST['level'] === 'Primary') ? 'selected' : ''; ?>>Primary School</option>
                    <option value="Pre" <?php echo (isset($_POST['level']) && $_POST['level'] === 'Pre') ? 'selected' : ''; ?>>Pre-School</option>
                    <option value="Admin" <?php echo (isset($_POST['level']) && $_POST['level'] === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <button type="submit" id="submitBtn">
                <i class="fas fa-user-plus"></i>
                Register User
            </button>
        </form>
        
        <div class="button-container">
            <a href="Admin.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
            <a href="logout.php" class="btn btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </div>

    <script>
        // Load theme from localStorage (shared with login page) - No toggle visible
        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;

            // Apply saved preference from localStorage (chosen on login)
            if (localStorage.getItem('darkMode') === 'enabled') {
                body.classList.add('dark');
            } else {
                // Default to light if no preference saved
                body.classList.remove('dark');
            }
        });

        // Classic Loading Animation on Form Submit
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner"></i> Registering...';
            
            // Optional: Show full-page loading overlay for better UX (similar to login)
            const overlay = document.createElement('div');
            overlay.id = 'tempOverlay';
            overlay.style.cssText = `
                position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                background: rgba(0,0,0,0.8); display: flex; justify-content: center; align-items: center; 
                z-index: 9999; color: white; font-size: 1.2rem;
            `;
            overlay.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing Registration...';
            document.body.appendChild(overlay);
            
            // Note: Since this is a POST to self, the page will reload naturally.
            // The overlay will be removed on reload.
        });

        // Preserve form data on error (already handled via PHP)
        // Add password confirmation or more validation if needed
    </script>
</body>
</html>