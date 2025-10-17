<?php
session_start();

// Show success message if set
if (isset($_SESSION['success_message'])) {
    echo '<script>alert("' . htmlspecialchars($_SESSION['success_message']) . '");</script>';
    unset($_SESSION['success_message']);
}

// Database connection configuration
$servername = "sql104.infinityfree.com"; // Removed trailing space
$username = "if0_40021406"; 
$password = "Op70TI711cS2lB6"; // Add your MySQL password here
$dbname = "if0_40021406_moet1";
$charset = 'utf8mb4';

try {
    // Use correct variable names here
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User  not logged in");
    }

    $user_id = $_SESSION['user_id'];

    // Total teachers sum from schools for this user
    $sqlTeachers = "
        SELECT SUM(total_teachers) AS total_teachers 
        FROM schools 
        WHERE user_id = :user_id
    ";
    $stmtTeachers = $pdo->prepare($sqlTeachers);
    $stmtTeachers->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmtTeachers->execute();
    $totalTeachers = $stmtTeachers->fetchColumn();
    if ($totalTeachers === false) {
        $totalTeachers = 0;
    }

    // Total students from most recent enrollment per school
    $sqlStudents = "
        SELECT SUM(latest.total_students) AS total_students FROM (
            SELECT hse.total_students
            FROM high_school_enrollment hse
            INNER JOIN (
                SELECT school_id, MAX(entry_date) AS max_date
                FROM high_school_enrollment
                WHERE school_id IN (SELECT school_id FROM schools WHERE user_id = :user_id)
                GROUP BY school_id
            ) latest_enrollment ON hse.school_id = latest_enrollment.school_id AND hse.entry_date = latest_enrollment.max_date
        ) AS latest
    ";
    $stmtStudents = $pdo->prepare($sqlStudents);
    $stmtStudents->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmtStudents->execute();
    $totalStudents = $stmtStudents->fetchColumn();
    if ($totalStudents === false) {
        $totalStudents = 0;
    }

    // Principal surname and gender (limit 1) - Updated to include gender
    $sqlPrincipal = "
        SELECT principal_surname, gender 
        FROM schools 
        WHERE user_id = :user_id
        LIMIT 1
    ";
    $stmtPrincipal = $pdo->prepare($sqlPrincipal);
    $stmtPrincipal->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmtPrincipal->execute();
    $principalData = $stmtPrincipal->fetch(PDO::FETCH_ASSOC);
    
    $principalSurname = $principalData['principal_surname'] ?? 'Unknown';
    $gender = strtolower($principalData['gender'] ?? '');
    
    // Add title based on gender
    $principalFullName = $principalSurname;
    if ($gender === 'male') {
        $principalFullName = 'Mr. ' . $principalSurname;
    } elseif ($gender === 'female') {
        $principalFullName = 'Mrs. ' . $principalSurname;
    }
    
    $_SESSION['principal_full_name'] = $principalFullName;
    $_SESSION['principal_surname'] = $principalSurname; // Keep original for backward compatibility if needed

} catch (PDOException $e) {
    echo '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4" role="alert">';
    echo '<p class="text-sm">Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
    $principalFullName = 'Unknown'; // Fallback
} catch (Exception $e) {
    echo '<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4" role="alert">';
    echo '<p class="text-sm">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
    $principalFullName = 'Unknown'; // Fallback
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal's Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Minimal CSS only for classic loading screen (professional spinner with fade-out) -->
    <style>
        /* Classic Loading Screen - Minimal and Professional */
        #loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #ffffff; /* Clean white for professional look */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }

        #loader.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .spinner {
            border: 4px solid #f3f4f6; /* Neutral light gray */
            border-top: 4px solid #1e40af; /* Professional navy blue */
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 1.1rem;
            color: #374151;
            font-weight: 500;
        }

        body.loading {
            overflow: hidden;
        }
    </style>
</head>
<body class="loading bg-gray-50 font-sans antialiased"> <!-- Professional light gray background, system font for classic feel -->
    <!-- Classic Professional Loading Screen -->
    <div id="loader">
        <div class="spinner"></div>
        <div class="loading-text">Loading Principal Dashboard...</div>
    </div>

    <!-- Fixed Professional Header (Classic navy theme) -->
    <header class="fixed top-0 left-0 w-full bg-white shadow-md z-50 px-6 py-4 flex justify-between items-center border-b border-gray-200">
        <div class="text-xl font-semibold text-gray-800">Principal's Dashboard</div>
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gray-600 rounded-full flex items-center justify-center text-white font-medium text-sm" aria-label="User  avatar">
                <?php
                if (isset($_SESSION['username'])) {
                    echo strtoupper(substr($_SESSION['username'], 0, 1));
                } else {
                    echo '?';
                }
                ?>
            </div>
            <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($principalFullName ?? $_SESSION['principal_full_name'] ?? 'Unknown'); ?></span>
        </div>
    </header>

    <!-- Toggle Button for Sidebar (Professional icon) -->
    <button class="fixed top-4 left-4 z-50 bg-white p-2 rounded-lg shadow-md hover:shadow-lg transition-shadow" aria-label="Toggle navigation menu" onclick="toggleSidebar()">
        <i class="fas fa-bars text-gray-600 text-lg"></i>
    </button>

    <!-- Professional Sidebar (Classic dark navy, collapsible) -->
    <nav class="sidebar fixed top-16 left-0 w-64 h-full bg-gray-800 text-white shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out z-40 overflow-y-auto" id="sidebar" aria-label="Main navigation">
        <div class="p-6 border-b border-gray-700">
            <h2 class="text-lg font-semibold text-white">Navigation Menu</h2>
            <h3 class="text-sm font-medium text-gray-300 mt-2">View and Edit Resources</h3>
        </div>
        <div class="p-4 space-y-2">
            <a href="update-ifrastructure.php" class="block py-3 px-4 text-gray-200 hover:bg-gray-700 hover:text-white rounded-lg transition-colors duration-200 font-medium">
                <i class="fas fa-building mr-3"></i>View Infrastructure
            </a>
            <a href="edit-AdditionalClassRooms.php" class="block py-3 px-4 text-gray-200 hover:bg-gray-700 hover:text-white rounded-lg transition-colors duration-200 font-medium">
                <i class="fas fa-chalkboard-teacher mr-3"></i>Additional Classrooms
            </a>
            <a href="edit-additionaltoilets.php" class="block py-3 px-4 text-gray-200 hover:bg-gray-700 hover:text-white rounded-lg transition-colors duration-200 font-medium">
                <i class="fas fa-restroom mr-3"></i>Additional Toilets
            </a>
            <a href="edit-electricity_infrastructure.php" class="block py-3 px-4 text-gray-200 hover:bg-gray-700 hover:text-white rounded-lg transition-colors duration-200 font-medium">
                <i class="fas fa-plug mr-3"></i>Electricity Infrastructure
            </a>
            <a href="edit-internet_infrastructure.php" class="block py-3 px-4 text-gray-200 hover:bg-gray-700 hover:text-white rounded-lg transition-colors duration-200 font-medium">
                <i class="fas fa-wifi mr-3"></i>Internet Infrastructure
            </a>
            <a href="update_school.php" class="block py-3 px-4 text-gray-200 hover:bg-gray-700 hover:text-white rounded-lg transition-colors duration-200 font-medium">
                <i class="fas fa-user-edit mr-3"></i>Edit Profile
            </a>
            <a href="logout.php" class="block py-3 px-4 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200 font-medium flex items-center">
                <i class="fas fa-sign-out-alt mr-3"></i>Logout
            </a>
        </div>
    </nav>

    <!-- Main Content (Professional layout with padding for fixed header) -->
    <main class="content pt-20 px-6 pb-8 min-h-screen" id="content" tabindex="-1">
        <!-- Quick Stats Cards (Classic grid, professional blue accents) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center hover:shadow-md transition-shadow duration-200" tabindex="0" aria-label="Total students">
                <div class="text-3xl font-bold text-blue-600 mb-2" aria-live="polite"><?php echo (int)($totalStudents ?? 0); ?></div>
                <div class="text-gray-600 font-medium">Total Students</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center hover:shadow-md transition-shadow duration-200" tabindex="0" aria-label="Total teachers">
                <div class="text-3xl font-bold text-blue-600 mb-2" aria-live="polite"><?php echo (int)($totalTeachers ?? 0); ?></div>
                <div class="text-gray-600 font-medium">Total Teachers</div>
            </div>
        </div>

        <!-- Dashboard Sections Grid (Professional cards with subtle hovers) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
            <!-- Profile Section -->
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-200" tabindex="0" aria-label="Profile management">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Profile</h2>
                <button class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200 mb-3" onclick="redirectTo('update_school.php')" aria-label="View school profile">View Profile</button>
                <button class="w-full bg-gray-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-gray-700 transition-colors duration-200" onclick="redirectTo('update_school.php')" aria-label="Edit school profile">Edit Profile</button>
            </section>

            <!-- Infrastructure Section -->
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-200" tabindex="0" aria-label="School infrastructure requirements">
                <h2 class="text-lg font-semibold text-gray-800 mb-2 border-b border-gray-200 pb-2">Infrastructure</h2>
                <p class="text-sm text-gray-600 mb-4">Does your school require?</p>
                <button class="w-full bg-green-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-green-700 transition-colors duration-200 mb-2 text-sm" onclick="redirectTo('infrastructure.php')" aria-label="Manage infrastructure">Infrastructure</button>
                <button class="w-full bg-green-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-green-700 transition-colors duration-200 mb-2 text-sm" onclick="redirectTo('AdditionalClassRooms.php')" aria-label="Additional classrooms">Additional Classrooms</button>
                <button class="w-full bg-green-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-green-700 transition-colors duration-200 text-sm" onclick="redirectTo('AdditionalToilets.php')" aria-label="Additional toilets">Additional Toilets</button>
            </section>

            <!-- Utilities Section -->
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-200" tabindex="0" aria-label="School utilities status">
                <h2 class="text-lg font-semibold text-gray-800 mb-2 border-b border-gray-200 pb-2">Utilities</h2>
                <p class="text-sm text-gray-600 mb-4">Does your school have?</p>
                <button class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-indigo-700 transition-colors duration-200 mb-2 text-sm" onclick="redirectTo('withoutElecticity.php')" aria-label="Electricity status">Electricity</button>
                <button class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-indigo-700 transition-colors duration-200 mb-2 text-sm" onclick="redirectTo('without-Water.php')" aria-label="Water status">Water</button>
                <button class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-indigo-700 transition-colors duration-200 text-sm" onclick="redirectTo('without-Internet.php')" aria-label="Internet status">Internet</button>
            </section>

            <!-- Enrollment Section -->
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-200" tabindex="0" aria-label="Student enrollment management">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Enrollment</h2>
                <button class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-purple-700 transition-colors duration-200 mb-3" onclick="redirectTo('Highenrolment.php')" aria-label="Add new enrollment">New Enrollment</button>
                <button class="w-full bg-gray-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-gray-700 transition-colors duration-200" onclick="redirectTo('retrivehigh-school-enrollment.php')" aria-label="View existing enrollments">View Enrollments</button>
            </section>
        </div>
    </main>

    <!-- Professional Footer (Classic dark theme) -->
    <footer class="bg-gray-800 text-white py-8 mt-12 border-t border-gray-700" role="contentinfo">
        <div class="max-w-6xl mx-auto px-6 text-center">
            <p class="text-sm">&copy; 2025 Ministry of Education and Training. All rights reserved.</p>
            <p class="text-sm mt-2">Contact: <a href="mailto:info@education.gov" class="text-blue-400 hover:text-blue-300 transition-colors">info@education.gov</a></p>
        </div>
    </footer>

    <script>
        // Hide loading screen after page fully loads (professional fade-out)
        window.addEventListener('load', function() {
            const loader = document.getElementById('loader');
            const body = document.body;
            setTimeout(() => {
                loader.classList.add('hidden');
                body.classList.remove('loading');
            }, 500);
        });

        // Professional Sidebar Toggle (Smooth and responsive)
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const body = document.body;
            sidebar.classList.toggle('-translate-x-full');
            if (window.innerWidth <= 768) {
                if (!sidebar.classList.contains('-translate-x-full')) {
                    body.classList.add('overflow-hidden');
                    // Add overlay for mobile
                    let overlay = document.createElement('div');
                    overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden';
                    overlay.onclick = toggleSidebar;
                    document.body.appendChild(overlay);
                } else {
                    body.classList.remove('overflow-hidden');
                    let overlay = document.querySelector('.fixed.inset-0.bg-black');
                    if (overlay) overlay.remove();
                }
            }
        }

        // Close sidebar on outside click (mobile only)
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = event.target.closest('button[onclick="toggleSidebar()"]');
            if (window.innerWidth <= 768 && sidebar.classList.contains('-translate-x-full') === false && !sidebar.contains(event.target) && !toggleBtn) {
                toggleSidebar();
            }
        });

        // Simple redirect function
        function redirectTo(page) {
            if (confirm('Are you sure you want to navigate to ' + page + '?')) { // Basic confirmation for professionalism
                window.location.href = page;
            }
        }

        // Ensure required session checks (redirect if not logged in)
        <?php if (!isset($_SESSION['user_id'])): ?>
            window.location.href = 'login.php'; // Redirect to login if no session
        <?php endif; ?>
    </script>
</body>
</html>