<?php
session_start();

// Show success message if set
if (isset($_SESSION['success_message'])) {
    echo '<script>alert("' . htmlspecialchars($_SESSION['success_message']) . '");</script>';
    unset($_SESSION['success_message']);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection configuration
$servername = "sql104.infinityfree.com";
$username   = "if0_40021406"; 
$password   = "Op70TI711cS2lB6";
$dbname     = "if0_40021406_moet1";
$charset    = 'utf8mb4';

try {
    // Corrected variable names in PDO constructor
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch counts and teacher sums grouped by level
    $sql = "
        SELECT u.level,
               COUNT(*) AS total_schools,
               COALESCE(SUM(s.total_teachers), 0) AS total_teachers
        FROM users u
        LEFT JOIN schools s ON u.user_id = s.user_id
        WHERE u.level IN ('High', 'Primary', 'Pre')
        GROUP BY u.level
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize totals
    $totals = [
        'High'    => ['schools' => 0, 'teachers' => 0],
        'Primary' => ['schools' => 0, 'teachers' => 0],
        'Pre'     => ['schools' => 0, 'teachers' => 0],
    ];

    foreach ($data as $row) {
        $level = $row['level'];
        $totals[$level]['schools']  = (int)$row['total_schools'];
        $totals[$level]['teachers'] = (int)$row['total_teachers'];
    }

} catch (PDOException $e) {
    echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">';
    echo '<p>Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
    exit;
} catch (Exception $e) {
    echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">';
    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<style>
  /* CSS Variables for Light/Dark Mode - Professional Institutional Blues & Neutrals */
:root {
    --bg-color: #f8fafc;
    --bg-container: #ffffff;
    --text-color: #1e293b;
    --text-color-light: #64748b;
    --primary: #1e40af; /* Deep institutional blue */
    --primary-dark: #1e3a8a;
    --header-bg: #1e40af;
    --sidebar-bg: #1e293b;
    --card-bg: #ffffff;
    --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    --btn-bg: #dc2626;
    --btn-bg-hover: #b91c1c;
    --btn-primary-bg: #1e40af;
    --btn-primary-bg-hover: #1e3a8a;
    --search-bg: #ffffff;
    --search-border: #cbd5e1;
    --search-btn-bg: #1e40af;
    --search-btn-bg-hover: #1e3a8a;
    --border-color: #e2e8f0;
    --footer-bg: #1e293b;
}

/* Dark Mode Variables */
body.dark {
    --bg-color: #0f172a;
    --bg-container: #1e293b;
    --text-color: #f1f5f9;
    --text-color-light: #cbd5e1;
    --primary: #3b82f6;
    --primary-dark: #2563eb;
    --header-bg: #1e293b;
    --sidebar-bg: #0f172a;
    --card-bg: #1e293b;
    --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    --btn-bg: #dc2626;
    --btn-bg-hover: #b91c1c;
    --btn-primary-bg: #3b82f6;
    --btn-primary-bg-hover: #2563eb;
    --search-bg: #1e293b;
    --search-border: #475569;
    --search-btn-bg: #3b82f6;
    --search-btn-bg-hover: #2563eb;
    --border-color: #334155;
    --footer-bg: #0f172a;
}

/* Global Styles - Clean, Professional with Inter Font */
body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: linear-gradient(135deg, var(--bg-color) 0%, #e2e8f0 100%);
    min-height: 100vh;
    margin: 0;
    color: var(--text-color);
    line-height: 1.6;
    transition: background-color 0.3s ease, color 0.3s ease;
}

#content-wrapper {
    background-color: var(--bg-container);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    transition: background-color 0.3s ease;
    border: 1px solid var(--border-color);
}

/* Header - Streamlined, Fixed, Subtle Shadow */
.header {
    background: var(--header-bg);
    color: white;
    padding: 1rem 2rem;
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    z-index: 60;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--card-shadow);
    box-sizing: border-box;
    transition: background-color 0.3s ease;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header h1 {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    white-space: nowrap;
}

.header img {
    width: 50px;
    height: auto;
    border-radius: 4px;
}

/* Mode Toggle - Subtle, Professional */
.mode-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    user-select: none;
    color: white;
}

.mode-toggle input[type="checkbox"] {
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

.mode-toggle input[type="checkbox"]:checked {
    background: var(--primary);
}

.mode-toggle input[type="checkbox"]::before {
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

.mode-toggle input[type="checkbox"]:checked::before {
    transform: translateX(20px);
}

/* Buttons - Flat, Attractive with Subtle Hovers */
.btn {
    background: var(--btn-bg);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
    white-space: nowrap;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn:hover {
    background: var(--btn-bg-hover);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-primary {
    background: var(--btn-primary-bg);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
    white-space: nowrap;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-primary:hover {
    background: var(--btn-primary-bg-hover);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Sidebar - Compact, Clean Navigation */
.sidebar {
    background-color: var(--sidebar-bg);
    color: white;
    width: 250px;
    min-height: calc(100vh - 64px);
    padding: 1.5rem 0;
    position: fixed;
    top: 64px;
    left: 0;
    overflow-y: auto;
    transition: transform 0.3s ease;
    z-index: 50;
    box-shadow: var(--card-shadow);
    border-right: 1px solid var(--border-color);
}

.sidebar.hidden {
    transform: translateX(-100%);
}

.sidebar h2 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    padding: 0 1rem;
    color: white;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 0.5rem;
}

.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar li {
    margin-bottom: 0.25rem;
}

.sidebar a {
    display: block;
    padding: 0.75rem 1rem;
    font-weight: 500;
    border-radius: 4px;
    transition: all 0.2s ease;
    color: white;
    text-decoration: none;
    border-left: 3px solid transparent;
}

.sidebar a:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-left-color: var(--primary);
    transform: translateX(4px);
}

/* Main Content - Spacious, Responsive Grid */
main {
    margin-left: 250px;
    padding: 100px 2rem 2rem;
    transition: margin-left 0.3s ease;
    flex: 1;
}

main.shifted {
    margin-left: 0;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.card {
    background: var(--card-bg);
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: var(--card-shadow);
    border: 1px solid var(--border-color);
    transition: all 0.2s ease;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-4px);
}

.card h3 {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--primary);
}

.stat-number {
    font-size: 2.25rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.25rem;
}

.card p {
    color: var(--text-color-light);
    font-size: 0.875rem;
}

/* Toggle Button - Icon-Focused, Minimal */
.toggle-btn {
    background: transparent;
    border: none;
    color: white;
    font-size: 1.25rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.toggle-btn:hover,
.toggle-btn:focus {
    background-color: rgba(255, 255, 255, 0.1);
    outline: none;
    transform: scale(1.05);
}

/* Enhanced Search Bar - Classic, Stunning, Professional Design */
.search-container {
    position: fixed;
    top: 64px;
    left: 250px;
    right: 0;
    background: linear-gradient(145deg, var(--search-bg), rgba(255,255,255,0.8));
    backdrop-filter: blur(10px);
    padding: 1rem 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    z-index: 55;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-bottom: 1px solid var(--border-color);
    transition: all 0.3s ease;
    border-radius: 0 0 12px 12px;
}

.search-container.shifted {
    left: 0;
}

.search-container body.dark & {
    background: linear-gradient(145deg, var(--search-bg), rgba(0,0,0,0.2));
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.search-form {
    display: flex;
    align-items: center;
    width: 100%;
    max-width: 800px;
    position: relative;
    background: var(--search-bg);
    border-radius: 50px;
    padding: 0.75rem 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--search-border);
    transition: all 0.3s ease;
    overflow: hidden;
}

.search-form:focus-within {
    box-shadow: 0 8px 25px rgba(30, 64, 175, 0.15);
    border-color: var(--primary);
    transform: translateY(-2px);
}

.search-icon {
    color: var(--text-color-light);
    margin-right: 0.75rem;
    font-size: 1.1rem;
    transition: color 0.2s ease;
}

.search-form:focus-within .search-icon {
    color: var(--primary);
}

.search-input {
    flex-grow: 1;
    border: none;
    background: transparent;
    padding: 0.5rem 0;
    font-size: 1rem;
    color: var(--text-color);
    outline: none;
    min-width: 200px;
}

.search-input::placeholder {
    color: var(--text-color-light);
}

.search-btn {
    background: linear-gradient(135deg, var(--search-btn-bg), var(--primary-dark));
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
}

.search-btn:hover,
.search-btn:focus {
    background: linear-gradient(135deg, var(--search-btn-bg-hover), var(--primary));
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 6px 20px rgba(30, 64, 175, 0.3);
    outline: none;
}

/* Footer - Simple, Themed */
footer {
    background: var(--footer-bg);
    color: white;
    padding: 1.5rem 0;
    margin-top: auto;
    text-align: center;
    font-size: 0.875rem;
    border-top: 1px solid var(--border-color);
}

/* Loading Screen - Full-Screen, Professional Spinner */
#loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: var(--bg-color);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    transition: opacity 0.3s ease;
}

#loader.hidden {
    opacity: 0;
    pointer-events: none;
}

.spinner {
    border: 4px solid var(--border-color);
    border-top: 4px solid var(--primary);
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

.loader-text {
    font-size: 1.1rem;
    font-weight: 500;
    color: var(--text-color);
}

/* Responsive Design - Mobile-First Enhancements */
@media (max-width: 1024px) {
    .sidebar {
        top: 64px;
        height: calc(100vh - 64px);
        position: fixed;
        transform: translateX(-100%);
        width: 220px;
        padding: 1rem;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.2);
    }

    .sidebar.visible {
        transform: translateX(0);
    }

    main {
        margin-left: 0;
        padding: 100px 1rem 1rem;
    }

    main.shifted {
        margin-left: 220px;
    }

    .toggle-btn {
        display: flex;
    }

    .search-container {
        left: 0;
        padding: 1rem;
        box-shadow: none;
        flex-direction: column;
        gap: 1rem;
        border-radius: 0;
    }

    .search-container.shifted {
        left: 220px;
    }

    .search-form {
        max-width: none;
        width: 100%;
    }
}

@media (max-width: 768px) {
    .header {
        padding: 1rem;
    }

    .header h1 {
        font-size: 1.25rem;
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .stat-number {
        font-size: 1.75rem;
    }

    .search-container.shifted {
        left: 0 !important;
    }

    .search-btn {
        width: 100%;
        justify-content: center;
    }
}

/* Overlay when sidebar open on small screens */
body.sidebar-open::before {
    content: "";
    position: fixed;
    top: 64px;
    left: 0;
    width: 100vw;
    height: calc(100vh - 64px);
    background: rgba(0, 0, 0, 0.5);
    z-index: 45;
    cursor: pointer;
}
</style>
</head>
<body>
  <!-- Loading Screen -->
  <div id="loader">
    <div class="spinner"></div>
    <div class="loader-text">Loading Dashboard...</div>
  </div>

  <div id="content-wrapper">
    <header class="header max-w-full px-4 md:px-8">
      <div class="header-left">
        <button id="toggleSidebarBtn" class="toggle-btn" aria-label="Toggle menu" aria-expanded="false" aria-controls="sidebar" aria-haspopup="true">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <img src="moet.png" alt="Ministry of Education" />
        <h1>Admin Dashboard</h1>
      </div>
      <label class="mode-toggle" for="darkModeToggle" title="Toggle dark/light mode">
        <span id="modeLabel">Light Mode</span>
        <input type="checkbox" id="darkModeToggle" aria-label="Toggle dark mode" />
      </label>
    </header>

    <aside class="sidebar hidden md:block" id="sidebar" role="navigation" aria-label="Sidebar menu" tabindex="-1">
      <h2>Admin Dashboard</h2>
      <nav>
        <ul>
          <li><a href="register.php">Register Users</a></li>
          <li><a href="filter.php">Filter data</a></li>
          <li><a href="Adminlinks.php">View and download additional requirements</a></li>
          <li><a href="view-high_school_enrollment.php">View high-school enrollment</a></li>
          <li><a href="view-primary_enrollment.php">View primary enrollment</a></li>
          <li><a href="view-preschool_enrollment.php">View pre-school enrollment</a></li>
          <li><a href="view-infrastructure.php">View infrastructure</a></li>
          <li><a href="logout.php" class="btn w-full text-left mt-4"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a></li>
        </ul>
      </nav>
    </aside>

    <!-- Enhanced Search Bar -->
    <form action="Adminsearch.php" method="POST" class="search-container" role="search" aria-label="Search schools or principals">
      <div class="search-form">
        <i class="fas fa-search search-icon" aria-hidden="true"></i>
        <input type="text" name="search_query" class="search-input" placeholder="Search for school or principal..." required aria-required="true" />
      </div>
      <button type="submit" name="search" class="search-btn" aria-label="Search">
        <i class="fas fa-search" aria-hidden="true"></i>
        Search
      </button>
    </form>

    <main id="mainContent" tabindex="-1">
      <h2 class="text-3xl font-bold mb-6">Dashboard Overview</h2>

      <section class="dashboard-grid" aria-label="High Schools statistics">
        <div class="card" role="region" aria-labelledby="highSchoolsTitle">
          <h3 id="highSchoolsTitle">High Schools</h3>
          <p class="stat-number" aria-live="polite" aria-atomic="true"><?php echo htmlspecialchars($totals['High']['schools']); ?></p>
          <p>Total High Schools</p>
        </div>
        <div class="card" role="region" aria-labelledby="highTeachersTitle">
          <h3 id="highTeachersTitle">Teachers</h3>
          <p class="stat-number" aria-live="polite" aria-atomic="true"><?php echo htmlspecialchars($totals['High']['teachers']); ?></p>
          <p>Total Teachers (High Schools)</p>
        </div>
      </section>

      <section class="dashboard-grid mt-8" aria-label="Primary Schools statistics">
        <div class="card" role="region" aria-labelledby="primarySchoolsTitle">
          <h3 id="primarySchoolsTitle">Primary Schools</h3>
          <p class="stat-number" aria-live="polite" aria-atomic="true"><?php echo htmlspecialchars($totals['Primary']['schools']); ?></p>
          <p>Total Primary Schools</p>
        </div>
        <div class="card" role="region" aria-labelledby="primaryTeachersTitle">
          <h3 id="primaryTeachersTitle">Teachers</h3>
          <p class="stat-number" aria-live="polite" aria-atomic="true"><?php echo htmlspecialchars($totals['Primary']['teachers']); ?></p>
          <p>Total Teachers (Primary Schools)</p>
        </div>
      </section>

      <section class="dashboard-grid mt-8" aria-label="Pre-Schools statistics">
        <div class="card" role="region" aria-labelledby="preSchoolsTitle">
          <h3 id="preSchoolsTitle">Pre-Schools</h3>
          <p class="stat-number" aria-live="polite" aria-atomic="true"><?php echo htmlspecialchars($totals['Pre']['schools']); ?></p>
          <p>Total Pre-Schools</p>
        </div>
        <div class="card" role="region" aria-labelledby="preTeachersTitle">
          <h3 id="preTeachersTitle">Teachers</h3>
          <p class="stat-number" aria-live="polite" aria-atomic="true"><?php echo htmlspecialchars($totals['Pre']['teachers']); ?></p>
          <p>Total Teachers (Pre-Schools)</p>
        </div>
      </section>
    </main>

    <footer class="bg-gray-800 text-white py-4 mt-auto">
      <div class="max-w-7xl mx-auto text-center">
        <p>&copy; 2025 Ministry of Education and Training. All rights reserved.</p>
      </div>
    </footer>
  </div>

<script>
  // Hide loading screen after page loads (with a short delay for effect)
  window.addEventListener('load', () => {
    setTimeout(() => {
      const loader = document.getElementById('loader');
      if (loader) {
        loader.classList.add('hidden');
      }
    }, 1000); // 1 second delay; adjust as needed
  });

  // Sidebar toggle
  const toggleBtn = document.getElementById('toggleSidebarBtn');
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('mainContent');
  const searchContainer = document.querySelector('.search-container');

  toggleBtn.addEventListener('click', () => {
    const isHidden = sidebar.classList.toggle('hidden');
    toggleBtn.setAttribute('aria-expanded', !isHidden);
    if (!isHidden) {
      sidebar.classList.add('visible');
      sidebar.focus();
      document.body.classList.add('sidebar-open');
      if(window.innerWidth <= 1024) {
        searchContainer.classList.add('shifted');
      }
    } else {
      sidebar.classList.remove('visible');
      mainContent.focus();
      document.body.classList.remove('sidebar-open');
      searchContainer.classList.remove('shifted');
    }
  });

  // Close sidebar on outside click (mobile/tablet)
  document.addEventListener('click', (e) => {
    if (window.innerWidth <= 1024) {
      if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target) && !sidebar.classList.contains('hidden')) {
        sidebar.classList.add('hidden');
        sidebar.classList.remove('visible');
        toggleBtn.setAttribute('aria-expanded', false);
        mainContent.focus();
        document.body.classList.remove('sidebar-open');
        searchContainer.classList.remove('shifted');
      }
    }
  });

  // Close sidebar on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !sidebar.classList.contains('hidden')) {
      sidebar.classList.add('hidden');
      sidebar.classList.remove('visible');
      toggleBtn.setAttribute('aria-expanded', false);
      mainContent.focus();
      document.body.classList.remove('sidebar-open');
      searchContainer.classList.remove('shifted');
    }
  });

  // Dark/light mode toggle
  const darkModeToggle = document.getElementById('darkModeToggle');
  const modeLabel = document.getElementById('modeLabel');
  const body = document.body;

  // Load saved preference
  if (localStorage.getItem('darkMode') === 'enabled') {
    body.classList.add('dark');
    darkModeToggle.checked = true;
    modeLabel.textContent = 'Dark Mode';
  } else {
    modeLabel.textContent = 'Light Mode';
  }

  darkModeToggle.addEventListener('change', () => {
    if (darkModeToggle.checked) {
      body.classList.add('dark');
      localStorage.setItem('darkMode', 'enabled');
      modeLabel.textContent = 'Dark Mode';
    } else {
      body.classList.remove('dark');
      localStorage.setItem('darkMode', 'disabled');
      modeLabel.textContent = 'Light Mode';
    }
  });
</script>
</body>
</html>
