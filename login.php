<?php
session_start();

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

$error = '';
$success = '';

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $regNo = $_POST['RegNo'];
    $password = $_POST['password'];

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT user_id, password, role, level, status FROM users WHERE RegNo = ?");
    $stmt->bind_param("s", $regNo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashedPassword, $role, $level, $status);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            $_SESSION['loggedin'] = true;
            $_SESSION['RegNo'] = $regNo; 
            $_SESSION['role'] = $role;
            $_SESSION['level'] = $level;
            $_SESSION['user_id'] = $user_id;

            $success = true;
            $redirectUrl = '';

            if ($status == 0) {
                switch ($level) {
                    case 'High':
                        $redirectUrl = "highschool.php";
                        break;
                    case 'Primary':
                        $redirectUrl = "primaryschool.php";
                        break;
                    case 'Pre':
                        $redirectUrl = "pre-school.php";
                        break;
                    case 'Admin':
                        $redirectUrl = "Admin.php";
                        break;
                    default:
                        $error = "Invalid level.";
                        break;
                }
            } else if ($status == 1) {
                switch ($level) {
                    case 'High':
                        $redirectUrl = "Highschool-Dash.php";
                        break;
                    case 'Primary':
                        $redirectUrl = "Primary-Dash.php";
                        break;
                    case 'Pre':
                        $redirectUrl = "kinder-Dash.php";
                        break;
                    default:
                        $error = "Invalid level.";
                        break;
                }
            }
        } else {
            $error = "Invalid credentials. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "Invalid credentials. Please try again.";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Premium Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        /* Root variables for light mode */
        :root {
            --bg-color: #f0f9ff;
            --bg-container: rgba(255, 255, 255, 0.9);
            --text-color: #0369a1;
            --text-color-light: #1e293b;
            --input-bg: #e0f2fe;
            --input-border: #7dd3fc;
            --input-placeholder: #60a5fa;
            --primary: #0369a1;
            --primary-dark: #024e6a;
            --link-color: #0369a1;
            --link-hover: #024e6a;
            --box-shadow: rgba(0, 0, 0, 0.1);
        }

        /* Dark mode variables */
        body.dark {
            --bg-color: #0f172a;
            --bg-container: rgba(15, 23, 42, 0.8);
            --text-color: #60a5fa;
            --text-color-light: #cbd5e1;
            --input-bg: #1e293b;
            --input-border: #3b82f6;
            --input-placeholder: #93c5fd;
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --link-color: #60a5fa;
            --link-hover: #2563eb;
            --box-shadow: rgba(0, 0, 0, 0.5);
        }

        html {
            scroll-behavior: smooth;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: var(--bg-color);
            color: var(--text-color-light);
            padding: 1rem;
            box-sizing: border-box;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .login-container {
            position: relative;
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            padding: 1.5rem;
            background: var(--bg-container);
            border-radius: 1.5rem;
            box-sizing: border-box;
            box-shadow: 0 10px 30px var(--box-shadow);
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        .form-container {
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255 255 255 / 0.2);
            box-shadow: 0 30px 60px -20px rgba(0 0 0 / 0.15);
            background: transparent !important;
            border-radius: 1.5rem;
            padding: 2rem;
            color: var(--text-color-light);
            transition: color 0.3s ease;
        }

        .input-field {
            transition: all 0.3s ease;
            background: var(--input-bg);
            border: 2px solid var(--input-border);
            color: var(--text-color-light);
            border-radius: 0.5rem;
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .input-field::placeholder {
            color: var(--input-placeholder);
        }

        .input-field:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
            cursor: text;
        }

        .input-field:focus {
            outline: none;
            border-color: var(--primary-dark);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.4);
            background: var(--primary);
            color: white;
        }

        label {
            color: var(--text-color);
            font-weight: 600;
            display: block;
            margin-bottom: 0.25rem;
            transition: color 0.3s ease;
        }

        .submit-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            font-weight: 600;
            padding: 1rem;
            border-radius: 1rem;
            cursor: pointer;
            width: 100%;
            font-size: 1.125rem;
        }

        .submit-btn:hover {
            background: var(--primary);
            border-color: var(--primary-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.6);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .submit-btn::after {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .submit-btn:hover::after {
            left: 100%;
        }

        .relative button {
            color: var(--text-color-light);
            background: transparent;
            border: none;
            cursor: pointer;
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            padding: 0;
        }
        .relative button:hover {
            color: var(--primary);
        }

        a {
            color: var(--link-color);
            font-weight: 500;
            transition: color 0.3s ease;
        }
        a:hover {
            color: var(--link-hover);
            text-decoration: underline;
        }

        input[type="checkbox"] {
            accent-color: var(--primary);
        }

        .error-message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            text-align: center;
            background-color: rgba(239, 68, 68, 0.2);
            color: #dc2626;
            border: 1px solid #ef4444;
            transition: all 0.3s ease;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Professional Loading Overlay Styles */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(3, 105, 161, 0.95) 0%, rgba(15, 23, 42, 0.95) 100%);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(10px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .loading-overlay.show {
            display: flex;
            opacity: 1;
        }

        .loading-content {
            text-align: center;
            color: white;
            font-family: 'Poppins', sans-serif;
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
            border-top-color: #60a5fa;
            animation-delay: 0s;
        }

        .spinner-ring:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 10px;
            left: 10px;
            border-top-color: #3b82f6;
            animation-delay: -0.5s;
        }

        .spinner-ring:nth-child(3) {
            width: 40px;
            height: 40px;
            top: 20px;
            left: 20px;
            border-top-color: #1d4ed8;
            animation-delay: -1s;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
            background: linear-gradient(90deg, #60a5fa, #3b82f6, #1d4ed8);
            border-radius: 2px;
            width: 0%;
            animation: progressBar 2s ease-in-out infinite;
        }

        @keyframes progressBar {
            0% { width: 0%; transform: translateX(-100%); }
            50% { width: 100%; transform: translateX(0%); }
            100% { width: 100%; transform: translateX(100%); }
        }

        /* Success animation */
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

        /* Dark mode toggle */
        .dark-mode-toggle {
            position: fixed;
            top: 1rem;
            right: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--text-color-light);
            user-select: none;
            z-index: 1000;
        }

        .dark-mode-toggle input[type="checkbox"] {
            width: 40px;
            height: 20px;
            appearance: none;
            background: #cbd5e1;
            border-radius: 9999px;
            position: relative;
            cursor: pointer;
            outline: none;
            transition: background-color 0.3s ease;
        }

        .dark-mode-toggle input[type="checkbox"]:checked {
            background: var(--primary);
        }

        .dark-mode-toggle input[type="checkbox"]::before {
            content: "";
            position: absolute;
            top: 2px;
            left: 2px;
            width: 16px;
            height: 16px;
            background: white;
            border-radius: 9999px;
            transition: transform 0.3s ease;
            transform: translateX(0);
        }

        .dark-mode-toggle input[type="checkbox"]:checked::before {
            transform: translateX(20px);
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            body {
                padding: 0.5rem;
            }

            .login-container {
    max-width: 100%;
    padding: 1rem;
    border-radius: 1rem;
    position: relative; /* Required for pseudo-element positioning */
    background: transparent; /* Ensure background doesn't interfere */
    overflow: hidden; /* Clip any overflow from the animated border */
    border: 2px solid transparent; /* Base border to define the shape */
    
    /* CSS Custom Property for animating the gradient starting angle */
    --snake-angle: 0deg;
    
    /* Animate the custom property to move the snake around */
    animation: moveSnakeAround 4s linear infinite;
}

/* Animated Snake-Like Moving Border (::before) */
.login-container::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    /* Conic-gradient with variable starting angle for movement */
    background: conic-gradient(
        from var(--snake-angle),
        transparent 0deg,           /* Start transparent (before snake) */
        #ff0000 5deg,               /* Snake "head" - bright red */
        #ff7f00 10deg,              /* Orange body segment 1 */
        #ffff00 15deg,              /* Yellow body segment 2 */
        #00ff00 20deg,              /* Green tail */
        transparent 25deg,          /* Fade to transparent (end of snake) */
        transparent 360deg          /* Remain transparent for the rest of the circle */
    );
    border-radius: 1.125rem; /* Slightly larger than container's 1rem to account for border thickness */
    z-index: -1;
    padding: 2px; /* Inner padding for the border thickness */
    /* Mask to create hollow border effect (only outline visible) */
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask-composite: exclude;
    -webkit-mask-composite: xor; /* For Webkit compatibility (Safari/Chrome) */
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
}

/* Keyframe animation to move the snake by shifting the gradient angle */
@keyframes moveSnakeAround {
    0% {
        --snake-angle: 0deg;
    }
    100% {
        --snake-angle: 360deg;
    }
}

/* Optional: Subtle wiggle for more snake-like organic movement */
.login-container::after {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    border-radius: 1.125rem;
    z-index: -2;
    background: transparent;
    padding: 2px;
    /* Mask for hollow border */
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask-composite: exclude;
    -webkit-mask-composite: xor;
    animation: subtleWiggle 1s ease-in-out infinite alternate; /* Gentle wiggle synced to movement */
}

@keyframes subtleWiggle {
    0% {
        transform: scale(1);
    }
    100% {
        transform: scale(1.01); /* Very subtle scale for breathing effect */
    }
}

/* Optional: Hover effect to enhance interaction */
.login-container:hover::before {
    /* Shorten the segment on hover for "alert" feel */
    background: conic-gradient(
        from var(--snake-angle),
        transparent 0deg,
        #ff0000 3deg,
        #ff7f00 6deg,
        #ffff00 9deg,
        #00ff00 12deg,
        transparent 15deg,
        transparent 360deg
    );
    animation-duration: 2s; /* Speed up the movement on hover */
    filter: brightness(1.3); /* Brighten the snake */
}

.login-container:hover {
    box-shadow: 0 0 20px rgba(255, 0, 0, 0.6); /* Add outer glow on hover */
}

            .form-container {
                padding: 1.5rem;
                border-radius: 1rem;
            }

            h1 {
                font-size: 1.75rem;
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
        
        
        
        .back-button {
    position: relative;
    display: inline-block;
    padding: 10px 20px;
    background: transparent;
    border: 2px solid transparent;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    cursor: pointer;
    overflow: hidden;
    transition: all 0.3s ease;
}

.back-button::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: conic-gradient(
        from 0deg,
        #ff0000,
        #ff7f00,
        #ffff00,
        #00ff00,
        #0000ff,
        #4b0082,
        #9400d3,
        #ff0000
    );
    border-radius: 10px;
    z-index: -1;
    animation: rotateBorder 3s linear infinite;
    padding: 2px;
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask-composite: exclude;
    -webkit-mask-composite: xor;
}

@keyframes rotateBorder {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.back-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 0, 0, 0.3);
    color: #ff0000; /* Optional: Change text color on hover */
}

.back-button:hover::before {
    animation-duration: 1.5s; /* Speed up on hover */
}

    </style>
</head>
<body>
    <!-- Dark mode toggle -->
    <label class="dark-mode-toggle" for="darkModeToggle">
        Dark Mode
        <input type="checkbox" id="darkModeToggle" aria-label="Toggle dark mode" />
    </label>

    <!-- Professional Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div class="loading-spinner">
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
            </div>
            <div class="success-checkmark" id="successCheckmark"></div>
            <div class="loading-text" id="loadingText">Authenticating...</div>
            <div class="loading-subtext" id="loadingSubtext">Please wait while we verify your credentials</div>
            <div class="loading-progress">
                <div class="loading-progress-bar"></div>
            </div>
        </div>
    </div>

    <form id="loginForm" method="POST" class="space-y-6 w-full max-w-md" novalidate>
        <div class="login-container">
            <div class="form-container">
                <div class="flex justify-center mb-8 bg-transparent p-4 rounded-full">
                    <img 
                        src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/add54c64-54a2-4109-8995-e376197b117e.png" 
                        alt="Default user profile silhouette" 
                        class="w-20 h-20 rounded-full border-4 border-white shadow-lg"
                    />
                </div>

                <h1 class="text-4xl font-extrabold text-center mb-2 tracking-tight" style="color: var(--primary);">Welcome</h1>
                <p class="text-center mb-10 text-lg" style="color: var(--primary);">Sign in to access your exclusive content</p>

                <?php if (!empty($error)): ?>
                    <div class="error-message" id="errorMessage"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="space-y-4">
                    <div>
                        <label for="RegNo">School Reg-No/Username</label>
                        <input 
                            type="text" 
                            id="RegNo" 
                            name="RegNo" 
                            required 
                            placeholder="Reg-Number" 
                            class="input-field"
                            autocomplete="username"
                            value="<?php echo isset($_POST['RegNo']) ? htmlspecialchars($_POST['RegNo']) : ''; ?>"
                        />
                    </div>

                    <div>
                        <label for="password">Password</label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                placeholder="Enter your password" 
                                class="input-field pr-10"
                                autocomplete="current-password"
                            />
                            <button 
                                type="button" 
                                onclick="togglePasswordVisibility()"
                                aria-label="Toggle password visibility"
                                title="Show/Hide password"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" id="submitBtn" class="submit-btn">
                        Sign In
                    </button>
                    <a href="index.html" class="back-button text-center block mt-4 text-sm font-medium transition-all duration-300" style="color: var(--link-color);">
    <b>Back</b>
</a>
                </div>
            </div>
        </div>
    </form>

    <script>
        // Toggle password visibility
        function togglePasswordVisibility() {
            const pwInput = document.getElementById('password');
            if (pwInput.type === 'password') {
                pwInput.type = 'text';
            } else {
                pwInput.type = 'password';
            }
        }

        // Dark mode toggle logic
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('darkModeToggle');
            const body = document.body;

            // Load saved preference
            if (localStorage.getItem('darkMode') === 'enabled') {
                body.classList.add('dark');
                toggle.checked = true;
            }

            toggle.addEventListener('change', () => {
                if (toggle.checked) {
                    body.classList.add('dark');
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    body.classList.remove('dark');
                    localStorage.setItem('darkMode', 'disabled');
                }
            });
        });

        // Professional loading system
        function showLoading(message = 'Authenticating...', subtext = 'Please wait while we verify your credentials') {
            const overlay = document.getElementById('loadingOverlay');
            const loadingText = document.getElementById('loadingText');
            const loadingSubtext = document.getElementById('loadingSubtext');
            const successCheckmark = document.getElementById('successCheckmark');
            
            loadingText.textContent = message;
            loadingSubtext.textContent = subtext;
            successCheckmark.classList.remove('show');
            overlay.classList.add('show');
        }

        function showSuccess(message = 'Login Successful!', subtext = 'Redirecting to your dashboard...') {
            const loadingText = document.getElementById('loadingText');
            const loadingSubtext = document.getElementById('loadingSubtext');
            const successCheckmark = document.getElementById('successCheckmark');
            
            successCheckmark.classList.add('show');
            loadingText.textContent = message;
            loadingSubtext.textContent = subtext;
        }

        function hideLoading() {
            const overlay = document.getElementById('loadingOverlay');
            overlay.classList.remove('show');
        }

        // Handle form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const regNo = document.getElementById('RegNo').value.trim();
            const password = document.getElementById('password').value.trim();
            const submitBtn = document.getElementById('submitBtn');

            if (!regNo || !password) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return;
            }

            // Show loading
            showLoading();
            submitBtn.disabled = true;
            
            // Simulate processing time (remove this in production)
            setTimeout(() => {
                // Form will submit normally after this delay
            }, 500);
        });

        // Handle PHP success/error states
        <?php if (!empty($success) && !empty($redirectUrl)): ?>
            showLoading('Login Successful!', 'Redirecting to your dashboard...');
            setTimeout(() => {
                showSuccess();
                setTimeout(() => {
                    window.location.href = '<?php echo $redirectUrl; ?>';
                }, 1500);
            }, 1000);
        <?php elseif (!empty($error)): ?>
            // Error handling is already done in PHP above
        <?php endif; ?>
    </script>
</body>
</html>