<?php
session_start();

// Show success message if set
if (isset($_SESSION['success_message'])) {
    echo '<script>alert("' . htmlspecialchars($_SESSION['success_message']) . '");</script>';
    unset($_SESSION['success_message']);
}

// Database connection configuration
$servername = "sql104.infinityfree.com";
$username = "if0_40021406"; 
$password = "Op70TI711cS2lB6";
$dbname = "if0_40021406_moet1";
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User     not logged in");
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

    // Retrieve total students from the most recent primary_enrollment per school
    $sqlStudents = "
        SELECT SUM(pe.overall_total) AS total_students FROM primary_enrollment pe
        INNER JOIN (
            SELECT school_id, MAX(created_at) AS max_created_at
            FROM primary_enrollment
            WHERE school_id IN (SELECT school_id FROM schools WHERE user_id = :user_id)
            GROUP BY school_id
        ) latest ON pe.school_id = latest.school_id AND pe.created_at = latest.max_created_at
    ";
    $stmtStudents = $pdo->prepare($sqlStudents);
    $stmtStudents->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmtStudents->execute();
    $totalStudents = $stmtStudents->fetchColumn();

    // Retrieve principal surname (limit 1)
    $sqlSurname = "
        SELECT principal_surname 
        FROM schools 
        WHERE user_id = :user_id
        LIMIT 1
    ";
    $stmtSurname = $pdo->prepare($sqlSurname);
    $stmtSurname->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmtSurname->execute();
    $_SESSION['principal_surname'] = $stmtSurname->fetchColumn();

} catch (PDOException $e) {
    echo '<div><p>Database error: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
} catch (Exception $e) {
    echo '<div><p>Error: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal's Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            z-index: 50;
            border-bottom: 1px solid #e5e7eb;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .toggle-btn {
            display: none;
            margin-right: 1rem;
            padding: 0.5rem;
            background: #f3f4f6;
            color: #6b7280;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 1rem;
        }

        .header-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .avatar {
            width: 2.5rem;
            height: 2.5rem;
            background: #2563eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .principal-name {
            white-space: nowrap;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 16rem;
            height: 100vh;
            background: #1f2937;
            color: white;
            overflow-y: auto;
            z-index: 40;
            padding-top: 4rem;
            display: none;
        }

        .sidebar-content {
            padding: 1.5rem;
        }

        .sidebar-title {
            text-align: center;
            margin-bottom: 2rem;
            font-weight: bold;
            font-size: 1.125rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #4b5563;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav li a {
            display: block;
            padding: 0.75rem;
            color: #d1d5db;
            text-decoration: none;
            border-left: 3px solid transparent;
        }

        .sidebar-nav li a:hover {
            background: #374151;
        }

        .sidebar-nav li a i {
            margin-right: 0.75rem;
        }

        .logout-link {
            background: #374151 !important;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 30;
            display: none;
        }

        .main {
            margin-top: 4rem;
            margin-left: 0;
            padding: 1rem;
            min-height: 100vh;
        }

        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            flex: 1;
            min-width: 200px;
            background: white;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-label i {
            margin-right: 0.5rem;
        }

        .sections-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .section {
            flex: 1;
            min-width: 280px;
            background: white;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: bold;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-title i {
            color: #2563eb;
            margin-right: 0.5rem;
        }

        .section-subtitle {
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn {
            width: 100%;
            background: #2563eb;
            color: white;
            font-weight: 500;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .footer {
            background: #1f2937;
            color: #9ca3af;
            padding: 2rem 0;
            text-align: center;
            margin-top: 4rem;
            border-top: 1px solid #374151;
        }

        .footer-content {
            max-width: 72rem;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .footer a {
            color: #60a5fa;
            text-decoration: none;
        }

        .footer a:hover {
            color: #3b82f6;
        }

        /* Mobile Styles */
        @media (max-width: 1023px) {
            .toggle-btn {
                display: block;
            }

            .sidebar {
                display: none;
            }

            .overlay {
                display: none;
            }

            body.sidebar-open {
                overflow: hidden;
            }

            body.sidebar-open .overlay {
                display: block;
            }

            body.sidebar-open .sidebar {
                display: block;
            }
        }

        /* Desktop Styles */
        @media (min-width: 1024px) {
            .toggle-btn {
                display: none !important;
            }

            .sidebar {
                display: block;
            }

            .main {
                margin-left: 16rem;
            }
        }

        /* Error styles */
        div p {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 0.375rem;
        }
    </style>
</head>
<body>
    <script>
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-open');
        }

        document.addEventListener('click', function (event) {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.querySelector('.toggle-btn');
            const overlay = document.querySelector('.overlay');

            if (window.innerWidth < 1024 && 
                document.body.classList.contains('sidebar-open') &&
                !sidebar.contains(event.target) && 
                !toggleBtn.contains(event.target)) {
                document.body.classList.remove('sidebar-open');
            }
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                document.body.classList.remove('sidebar-open');
            }
        });

        function redirectTo(page) {
            window.location.href = page;
        }

        // Initialize
        window.addEventListener('load', function() {
            if (window.innerWidth < 1024) {
                document.body.classList.remove('sidebar-open');
            }
        });
    </script>

    <header class="header">
        <div class="header-left">
            <button class="toggle-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="header-title">Principal's Dashboard</h1>
        </div>
        <div class="header-right">
            <div class="avatar">
                <?php if (isset($_SESSION['username'])) { echo strtoupper(substr($_SESSION['username'], 0, 1)); } else { echo '?'; } ?>
            </div>
            <span class="principal-name"><?php echo isset($_SESSION['principal_surname']) ? htmlspecialchars($_SESSION['principal_surname']) : 'Principal'; ?></span>
        </div>
    </header>

    <nav class="sidebar">
        <div class="sidebar-content">
            <h2 class="sidebar-title">Navigation</h2>
            <ul class="sidebar-nav">
                <li><a href="update-ifrastructure.php"><i class="fas fa-building"></i>View Infrastructure</a></li>
                <li><a href="edit-AdditionalClassRooms.php"><i class="fas fa-chalkboard"></i>View Additional Classrooms</a></li>
                <li><a href="edit-AdditionalToilets.php"><i class="fas fa-restroom"></i>View Additional Toilets</a></li>
                <li><a href="edit-electricity_infrastructure.php"><i class="fas fa-plug"></i>View Electricity Infrastructure</a></li>
                <li><a href="edit-internet_infrastructure.php"><i class="fas fa-wifi"></i>Internet Infrastructure</a></li>
                <li><a href="update_school.php"><i class="fas fa-user-edit"></i>Edit Profile</a></li>
                <li><a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="overlay" onclick="toggleSidebar()"></div>

    <main class="main">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo isset($totalStudents) ? number_format($totalStudents) : 0; ?></div>
                <div class="stat-label"><i class="fas fa-users"></i>Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo isset($totalTeachers) ? number_format($totalTeachers) : 0; ?></div>
                <div class="stat-label"><i class="fas fa-chalkboard-teacher"></i>Teachers</div>
            </div>
        </div>

        <div class="sections-grid">
            <section class="section">
                <h2 class="section-title"><i class="fas fa-user"></i>Profile</h2>
                <button class="btn" onclick="redirectTo('update_school.php')"><i class="fas fa-eye"></i>View Profile</button>
                <button class="btn" onclick="redirectTo('update_school.php')"><i class="fas fa-edit"></i>Edit Profile</button>
            </section>

            <section class="section">
                <h2 class="section-title"><i class="fas fa-tools"></i>Infrastructure</h2>
                <h3 class="section-subtitle">Does your school require?</h3>
                <button class="btn" onclick="redirectTo('infrastructure.php')"><i class="fas fa-tools"></i>Infrastructure</button>
                <button class="btn" onclick="redirectTo('pAdditionalClassRooms.php')"><i class="fas fa-chalkboard"></i>Additional Classrooms</button>
                <button class="btn" onclick="redirectTo('pAdditionalToilets.php')"><i class="fas fa-restroom"></i>Additional Toilets</button>
            </section>

            <section class="section">
                <h2 class="section-title"><i class="fas fa-bolt"></i>Utilities</h2>
                <h3 class="section-subtitle">Does your school have?</h3>
                <button class="btn" onclick="redirectTo('pwithoutElecticity.php')"><i class="fas fa-bolt"></i>Electricity</button>
                <button class="btn" onclick="redirectTo('pwithout-Water.php')"><i class="fas fa-tint"></i>Water</button>
                <button class="btn" onclick="redirectTo('pwithout-Internet.php')"><i class="fas fa-wifi"></i>Internet</button>
            </section>

            <section class="section">
                <h2 class="section-title"><i class="fas fa-graduation-cap"></i>Enrollment</h2>
                <button class="btn" onclick="redirectTo('primaryenrolment.php')"><i class="fas fa-plus"></i>New Enrollment</button>
                <button class="btn" onclick="redirectTo('retrive-primary-enroll.php')"><i class="fas fa-list"></i>View Enrollments</button>
            </section>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2025 Ministry of Education and Training. All rights reserved.</p>
            <p>Contact: <a href="mailto:info@education.gov">info@education.gov</a></p>
        </div>
    </footer>
</body>
</html>