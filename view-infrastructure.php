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

$schoolType = $_POST['school_type'] ?? 'primary'; // Default to primary
$titleSuffix = ucfirst($schoolType) . ' Schools';
$filenameSuffix = str_replace(' ', '_', $titleSuffix);

// Build SQL query based on school type
$whereClause = "";
if ($schoolType === 'primary') {
    $whereClause = "AND s.centre IS NOT NULL AND (s.cluster IS NULL OR s.cluster = '')";
} elseif ($schoolType === 'high') {
    $whereClause = "AND s.cluster IS NOT NULL AND s.centre IS NULL";
}

// Retrieve infrastructure data, joining with schools table and filtering for recent entries
$sql = "
    SELECT i.*, s.registration_number, s.school_name, s.centre, s.cluster
    FROM infrastructure i
    JOIN schools s ON i.school_id = s.school_id
    WHERE i.created_at IN (
        SELECT MAX(created_at)
        FROM infrastructure
        GROUP BY school_id
    )
    $whereClause
    ORDER BY s.school_name
";

$result = $conn->query($sql);

// Check if there are results
if ($result->num_rows > 0) {
    $infrastructureData = [];
    while ($row = $result->fetch_assoc()) {
        $infrastructureData[] = $row;
    }
} else {
    $infrastructureData = [];
}

// Close the connection
$conn->close();

// Function to download data as Excel
function downloadExcel($data, $filenameSuffix) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"infrastructure_data_$filenameSuffix.xls\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Output the column headings
    echo "Registration Number\tSchool Name\tCentre/Cluster\tClassrooms\tToilets\tKitchen\tStore\tStaffroom\tOffice\tLibrary\tLaboratory\tHall\tPlaygrounds\n";

    // Output the data
    foreach ($data as $row) {
        $location = !empty($row['centre']) ? $row['centre'] : $row['cluster'];
        echo implode("\t", [
            $row['registration_number'],
            $row['school_name'],
            $location,
            $row['classrooms'],
            $row['toilets'],
            $row['kitchen'],
            $row['store'],
            $row['staffroom'],
            $row['office'],
            $row['library'],
            $row['laboratory'],
            $row['hall'],
            $row['playgrounds']
        ]) . "\n";
    }
    exit();
}

// Check if download is requested
if (isset($_POST['download'])) {
    downloadExcel($infrastructureData, $filenameSuffix);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infrastructure Management System - <?php echo $titleSuffix; ?></title>
    <!-- Google Fonts for Professional Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Link to external CSS file (create infrastructure.css with the CSS below) -->
    <link rel="stylesheet" href="infrastructure.css">
    <style>
    /* Import Inter font from Google Fonts (optional if already in HTML) */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

/* CSS Variables for Light Mode */
:root {
    /* Colors */
    --primary-color: #1e3a8a; /* Deep navy for professionalism */
    --secondary-color: #3b82f6; /* Professional blue accent */
    --accent-color: #10b981; /* Success green */
    --bg-light: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); /* Subtle professional gradient */
    --bg-white: #ffffff; /* Clean white */
    --bg-controls: #f8fafc; /* Controls and action bar background */
    --text-primary: #1f2937; /* Dark gray for text */
    --text-secondary: #6b7280; /* Medium gray */
    --border-color: #d1d5db; /* Light borders */
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --table-even: #f9fafb; /* Even row background */
    --table-hover: #eff6ff; /* Hover background */
    --table-hover-shadow: rgba(59, 130, 246, 0.1); /* Hover shadow */
    --no-data-color: #6b7280; /* No data text */
    --no-data-icon: rgba(107, 114, 128, 0.5); /* No data icon opacity */
    --footer-bg: #1e293b; /* Footer background */
    --footer-text: #cbd5e1; /* Footer text */
    --footer-link: #60a5fa; /* Footer link */
    --footer-link-hover: #3b82f6; /* Footer link hover */

    /* Layout & Spacing */
    --border-radius: 0.75rem; /* Subtle rounding */
    --btn-radius: 0.5rem; /* Button radius */
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); /* Smooth transitions */
    --header-padding: 3rem 2rem; /* Header padding */
    --controls-padding: 1.5rem 2rem; /* Controls padding */
    --table-padding: 0; /* Table container padding (overflow handles it) */
    --font-size-table: 0.95rem; /* Table font size */
    --font-size-th: 0.8rem; /* Header font size */
    --max-width: 1400px; /* Container max width */
    --table-min-width: 1200px; /* Table min width for scroll */
    --table-max-height: 70vh; /* Table max height */
}

/* Dark Mode Overrides */
body.dark {
    --bg-light: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); /* Dark gradient background */
    --bg-white: #1e293b; /* Dark container */
    --bg-controls: #334155; /* Dark controls */
    --text-primary: #f1f5f9; /* Light text */
    --text-secondary: #94a3b8; /* Light gray */
    --border-color: #475569; /* Dark borders */
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.3);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.3), 0 2px 4px -2px rgb(0 0 0 / 0.3);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.3), 0 4px 6px -4px rgb(0 0 0 / 0.3);
    --table-even: #334155; /* Dark even row */
    --table-hover: #475569; /* Dark hover */
    --table-hover-shadow: rgba(59, 130, 246, 0.2); /* Darker hover shadow */
    --no-data-color: #94a3b8; /* Light no data text */
    --no-data-icon: rgba(148, 163, 184, 0.5); /* Dark no data icon */
    --footer-bg: #0f172a; /* Darker footer */
    --footer-text: #94a3b8; /* Lighter footer text */
    --footer-link: #93c5fd; /* Light blue link */
    --footer-link-hover: #60a5fa; /* Hover link */
}

* {
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: var(--bg-light);
    min-height: 100vh;
    margin: 0;
    padding: 2rem 0;
    color: var(--text-primary);
    line-height: 1.6;
    transition: background 0.3s ease, color 0.3s ease;
}

.dashboard-container {
    max-width: var(--max-width);
    margin: 0 auto;
    background: var(--bg-white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    transition: background 0.3s ease, box-shadow 0.3s ease;
}

/* Header Section - Stunning Gradient with Professional Typography */
.header-section {
    background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
    color: white;
    padding: var(--header-padding);
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: background 0.3s ease;
}

.header-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.05;
    z-index: 0;
}

.header-section > * {
    position: relative;
    z-index: 1;
}

.header-section h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    letter-spacing: -0.025em;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin: 0 auto 0.5rem;
    transition: color 0.3s ease;
}

.header-section h1 i {
    font-size: 2.5rem;
    opacity: 0.9;
    transition: opacity 0.3s ease;
}

.header-section p {
    font-size: 1.1rem;
    opacity: 0.95;
    max-width: 800px;
    margin: 0 auto;
    font-weight: 400;
    transition: opacity 0.3s ease;
}

/* Selection Form - Professional Dropdown */
.selection-form {
    background: var(--bg-controls);
    padding: var(--controls-padding);
    border-bottom: 1px solid var(--border-color);
    text-align: center;
    transition: background 0.3s ease, border-color 0.3s ease;
}

.selection-form label {
    font-weight: 600;
    margin-right: 1rem;
    color: var(--text-primary);
    font-size: 1rem;
    transition: color 0.3s ease;
}

.selection-form select {
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    font-size: 1rem;
    background: var(--bg-white);
    color: var(--text-primary);
    cursor: pointer;
    transition: var(--transition);
    min-width: 200px;
}

.selection-form select:focus {
    outline: none;
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Action Bar - Clean and Professional */
.action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--controls-padding);
    background: var(--bg-controls);
    border-bottom: 1px solid var(--border-color);
    transition: background 0.3s ease, border-color 0.3s ease;
}

.download-form {
    display: inline-block;
}

/* Buttons */
.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--btn-radius);
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    box-shadow: var(--shadow-sm);
    font-family: inherit;
}

.btn-primary {
    background: linear-gradient(135deg, var(--secondary-color) 0%, #2563eb 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    color: white;
}

.btn-secondary {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    color: white;
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    color: white;
}

.back-link {
    padding: 0.75rem 1.5rem;
    border: 1px solid var(--border-color);
    border-radius: var(--btn-radius);
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    background: var(--bg-white);
    color: var(--text-primary);
}

.back-link:hover {
    background: var(--bg-controls);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
    color: var(--text-primary);
}

/* Table Container - Professional and Responsive */
.table-container {
    overflow-x: auto;
    max-height: var(--table-max-height);
    overflow-y: auto;
    padding: var(--table-padding);
    transition: padding 0.3s ease;
}

/* Custom Scrollbar for Professional Feel */
.table-container::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}

.table-container::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb {
    background: var(--secondary-color);
    border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb:hover {
    background: #2563eb;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: var(--font-size-table);
    min-width: var(--table-min-width); /* Ensure horizontal scroll on small screens */
    transition: none; /* Tables don't transition */
}

thead {
    background: var(--primary-color);
    position: sticky;
    top: 0;
    z-index: 10;
    transition: background 0.3s ease;
}

th {
    color: white;
    font-weight: 600;
    padding: 1rem 1.25rem;
    text-align: left;
    border: none;
    white-space: nowrap;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: var(--font-size-th);
    transition: color 0.3s ease;
}

th i {
    margin-right: 0.5rem;
    opacity: 0.9;
    transition: opacity 0.3s ease;
}

tbody tr {
    transition: var(--transition);
    border-bottom: 1px solid var(--border-color);
}

tbody tr:nth-child(even) {
    background-color: var(--table-even);
}

tbody tr:hover {
    background-color: var(--table-hover);
    transform: scale(1.005);
    box-shadow: 0 2px 8px var(--table-hover-shadow);
}

td {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
    color: var(--text-primary);
    word-wrap: break-word;
    text-align: center;
    transition: background 0.3s ease, color 0.3s ease;
}

td:first-child, td:nth-child(2), td:nth-child(3), td:nth-child(4) {
    text-align: left;
}

.no-data {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--no-data-color);
    font-size: 1.1rem;
    transition: color 0.3s ease;
}

.no-data i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: var(--no-data-icon);
    transition: opacity 0.3s ease;
}

.no-data-subtitle {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-top: 0.5rem;
    transition: color 0.3s ease;
}

/* Footer - Simple and Professional */
.footer {
    background: var(--footer-bg);
    color: var(--footer-text);
    padding: 1.5rem 2rem;
    text-align: center;
    font-size: 0.875rem;
    margin-top: 2rem;
    transition: background 0.3s ease, color 0.3s ease;
}

.footer-content {
    max-width: 1280px; /* Matches Tailwind max-w-7xl approx */
    margin: 0 auto;
}

.footer-contact {
    margin-top: 0.25rem;
}

.footer a {
    color: var(--footer-link);
    text-decoration: none;
    transition: color 0.2s ease;
}

.footer a:hover {
    color: var(--footer-link-hover);
}

/* Responsive Design */
@media (max-width: 768px) {
    body {
        padding: 1rem 0;
    }

    .dashboard-container {
        margin: 0 1rem;
        border-radius: 0.5rem;
    }

    .header-section {
        padding: 2rem 1rem;
    }

    .header-section h1 {
        font-size: 2rem;
        flex-direction: column;
        gap: 0.5rem;
    }

    .header-section h1 i {
        font-size: 2rem;
    }

    .header-section p {
        font-size: 1rem;
    }

    .selection-form {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        align-items: center;
    }

    .action-bar {
        flex-direction: column;
        gap: 1rem;
        padding: 1rem;
    }

    .btn, .back-link {
        width: 100%;
        justify-content: center;
    }

    .table-container {
        font-size: 0.85rem;
    }

    th, td {
        padding: 0.75rem 0.5rem;
    }

    .no-data {
        padding: 2rem 1rem;
    }
}

/* Accessibility */
.btn:focus, .back-link:focus, select:focus {
    outline: 2px solid var(--secondary-color);
    outline-offset: 2px;
}

table:focus {
    outline: none;
}

/* Print Styles */
@media print {
    body {
        background: white !important;
        color: black !important;
        padding: 0 !important;
    }

    .dashboard-container {
        box-shadow: none !important;
        border-radius: 0 !important;
        margin: 0 !important;
    }

    .header-section, .selection-form, .action-bar, .footer {
        display: none !important;
    }

    .table-container {
        overflow: visible !important;
        max-height: none !important;
        padding: 0 !important;
    }

    table {
        min-width: auto !important;
        font-size: 10px !important;
    }

    th, td {
        border: 1px solid #000 !important;
        padding: 0.5rem !important;
        color: black !important;
    }

    tbody tr:hover {
        background: transparent !important;
        transform: none !important;
    }

    .no-data {
        display: table-cell !important;
        background: transparent !important;
    }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}

/* High Contrast */
@media (prefers-contrast: high) {
    :root {
        --border-color: #000;
        --text-secondary: #000;
    }

    .selection-form, .action-bar, .table-container, .no-data {
        border-color: #000;
    }
}
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header Section -->
        <div class="header-section">
            <h1>
                <i class="fas fa-building-columns"></i>
                Infrastructure Management System
            </h1>
            <p>View and manage the most recent infrastructure data for <?php echo strtolower($titleSuffix); ?> (<?php echo $schoolType === 'primary' ? 'Centre populated, Cluster null/empty' : 'Cluster populated, Centre null'; ?>).</p>
        </div>

        <!-- School Type Selection Form -->
        <form method="post" class="selection-form">
            <label for="school_type">Select School Type:</label>
            <select name="school_type" id="school_type" onchange="this.form.submit()">
                <option value="primary" <?php echo $schoolType === 'primary' ? 'selected' : ''; ?>>Primary Schools</option>
                <option value="high" <?php echo $schoolType === 'high' ? 'selected' : ''; ?>>High Schools</option>
            </select>
        </form>

        <!-- Action Bar -->
        <div class="action-bar">
            <form method="post" class="download-form">
                <input type="hidden" name="school_type" value="<?php echo htmlspecialchars($schoolType); ?>">
                <button type="submit" name="download" class="btn btn-primary">
                    <i class="fas fa-file-excel"></i>
                    Export to Excel
                </button>
            </form>
            <a href="Admin.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <table role="table" aria-label="Infrastructure Data">
                <thead>
                    <tr>
                        <th><i class="fas fa-id-badge"></i> Reg Number</th>
                        <th><i class="fas fa-school"></i> School Name</th>
                        <th><i class="fas fa-map-marker-alt"></i> Centre/Cluster</th>
                        <th><i class="fas fa-chalkboard"></i> Classrooms</th>
                        <th><i class="fas fa-restroom"></i> Toilets</th>
                        <th><i class="fas fa-utensils"></i> Kitchen</th>
                        <th><i class="fas fa-store"></i> Store</th>
                        <th><i class="fas fa-user-tie"></i> Staffroom</th>
                        <th><i class="fas fa-briefcase"></i> Office</th>
                        <th><i class="fas fa-book"></i> Library</th>
                        <th><i class="fas fa-flask"></i> Laboratory</th>
                        <th><i class="fas fa-theater-masks"></i> Hall</th>
                        <th><i class="fas fa-futbol"></i> Playgrounds</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($infrastructureData)): ?>
                        <?php foreach ($infrastructureData as $infrastructure): ?>
                            <tr role="row">
                                <td><?php echo htmlspecialchars($infrastructure['registration_number'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($infrastructure['school_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars(!empty($infrastructure['centre']) ? $infrastructure['centre'] : $infrastructure['cluster'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($infrastructure['classrooms'] ?? '0'); ?></td>
                                <td><?php echo htmlspecialchars($infrastructure['toilets'] ?? '0'); ?></td>
                                <td><?php echo htmlspecialchars($infrastructure['kitchen'] ?? '0'); ?></td>
                                <td><?php echo htmlspecialchars($infrastructure['store'] ?? '0'); ?></td>
                                <td><?php echo htmlspecialchars($infrastructure['staffroom'] ?? '0'); ?></td>
                                <td><?php echo htmlspecialchars($infrastructure['office'] ?? '0'); ?></td>
                                <td><?php echo htmlspecialchars($infrastructure['library'] ?? '0'); ?></td>
                                <td><?php echo htmlspecialchars($infrastructure['laboratory'] ?? '0'); ?></td>
                                <td><?php echo htmlspecialchars($infrastructure['hall'] ?? '0'); ?></td>
                                <td><?php echo htmlspecialchars($infrastructure['playgrounds'] ?? '0'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="13" class="no-data">
                                <i class="fas fa-inbox"></i>
                                <div>No infrastructure records found matching the criteria.</div>
                                <div class="no-data-subtitle">Please check back later or contact support.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2025 Ministry of Education and Training. All rights reserved.</p>
            <p class="footer-contact">Contact: <a href="mailto:info@education.gov">info@education.gov</a> | Secure Data Management System</p>
        </div>
    </footer>

<script>
    // Load theme from localStorage (shared with other pages) - No toggle visible
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
</script>

</body>
</html>