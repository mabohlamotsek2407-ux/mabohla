<?php
session_start();

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

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve primary enrollment data, joining with schools table and filtering for recent entries
$sql = "
    SELECT pe.*, s.registration_number, s.school_name 
    FROM primary_enrollment pe
    JOIN schools s ON pe.school_id = s.school_id
    WHERE pe.created_at IN (
        SELECT MAX(created_at)
        FROM primary_enrollment
        GROUP BY school_id
    )
    ORDER BY s.school_name
";

$result = $conn->query($sql);

// Check if there are results
if ($result->num_rows > 0) {
    $enrollmentData = [];
    while ($row = $result->fetch_assoc()) {
        $enrollmentData[] = $row;
    }
} else {
    $enrollmentData = [];
}

// Close the connection
$conn->close();

// Function to download data as Excel
function downloadExcel($data) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"primary_enrollment_data.xls\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Output the column headings
    echo "Registration Number\tSchool Name\tFemale Reception\tMale Reception\tReception Total\tGrade 1 Girls\tGrade 1 Boys\tGrade 1 Total\tGrade 2 Girls\tGrade 2 Boys\tGrade 2 Total\tGrade 3 Girls\tGrade 3 Boys\tGrade 3 Total\tGrade 4 Girls\tGrade 4 Boys\tGrade 4 Total\tGrade 5 Girls\tGrade 5 Boys\tGrade 5 Total\tGrade 6 Girls\tGrade 6 Boys\tGrade 6 Total\tGrade 7 Girls\tGrade 7 Boys\tGrade 7 Total\tRepeaters Girls\tRepeaters Boys\tRepeaters Total\tOverall Total\tCreated At\tUpdated At\n";

    // Output the data
    foreach ($data as $row) {
        echo implode("\t", [
            $row['registration_number'],
            $row['school_name'],
            $row['female_reception'],
            $row['male_reception'],
            $row['reception_total'],
            $row['grade1_girls'],
            $row['grade1_boys'],
            $row['grade1_total'],
            $row['grade2_girls'],
            $row['grade2_boys'],
            $row['grade2_total'],
            $row['grade3_girls'],
            $row['grade3_boys'],
            $row['grade3_total'],
            $row['grade4_girls'],
            $row['grade4_boys'],
            $row['grade4_total'],
            $row['grade5_girls'],
            $row['grade5_boys'],
            $row['grade5_total'],
            $row['grade6_girls'],
            $row['grade6_boys'],
            $row['grade6_total'],
            $row['grade7_girls'],
            $row['grade7_boys'],
            $row['grade7_total'],
            $row['repeaters_girls'],
            $row['repeaters_boys'],
            $row['repeaters_total'],
            $row['overall_total'],
            $row['created_at'],
            $row['updated_at']
        ]) . "\n";
    }
    exit();
}

// Check if download is requested
if (isset($_POST['download'])) {
    downloadExcel($enrollmentData);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Primary Enrollment Data</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Link to external CSS file (create primary-enrollment.css with the CSS below) -->
    <link rel="stylesheet" href="primary-enrollment.css">
    <style>
    /* Import Poppins font from Google Fonts */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

/* CSS Variables for Light Mode */
:root {
    /* Colors */
    --primary-color: #1e293b; /* Dark slate for headers */
    --secondary-color: #3b82f6; /* Blue accent */
    --success-color: #10b981; /* Green for positives */
    --danger-color: #ef4444; /* Red for warnings */
    --bg-light: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); /* Light gradient background */
    --bg-white: #ffffff; /* Pure white */
    --bg-controls: #f8fafc; /* Controls background */
    --text-primary: #334155; /* Dark gray text */
    --text-secondary: #64748b; /* Medium gray */
    --border-color: #e2e8f0; /* Light borders */
    --border-right-light: rgba(255, 255, 255, 0.1); /* Header borders */
    --shadow-container: 0 10px 40px rgba(0, 0, 0, 0.08); /* Container shadow */
    --shadow-btn: 0 2px 8px rgba(0, 0, 0, 0.08); /* Button shadow */
    --shadow-btn-hover: 0 4px 16px rgba(59, 130, 246, 0.3); /* Button hover shadow */
    --table-even: #f8fafc; /* Even row background */
    --table-hover: #eff6ff; /* Hover background */
    --table-hover-shadow: rgba(59, 130, 246, 0.1); /* Hover shadow */
    --no-data-bg: #f8fafc; /* No data background */
    --no-data-color: #cbd5e1; /* No data icon color */
    --footer-bg: #1e293b; /* Footer background */
    --footer-text: #cbd5e1; /* Footer text */
    --footer-link: #60a5fa; /* Footer link */
    --footer-link-hover: #3b82f6; /* Footer link hover */
    --total-students-color: #3b82f6; /* Total students highlight */

    /* Layout & Spacing */
    --border-radius: 16px; /* Container and buttons radius */
    --btn-radius: 8px; /* Button radius */
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); /* Smooth transitions */
    --header-padding: 2.5rem 2rem; /* Header padding */
    --controls-padding: 1.5rem 2rem; /* Controls padding */
    --table-padding: 1.5rem 2rem; /* Table container padding */
    --font-size-table: 0.875rem; /* Table font size */
    --font-size-th: 0.75rem; /* Header font size */
    --max-width: 1400px; /* Container max width */
    --table-min-width: 1600px; /* Table min width for scroll (wider for primary data) */
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
    --border-right-light: rgba(0, 0, 0, 0.2); /* Dark header borders */
    --shadow-container: 0 10px 40px rgba(0, 0, 0, 0.3); /* Darker shadow */
    --shadow-btn: 0 2px 8px rgba(0, 0, 0, 0.3); /* Darker button shadow */
    --shadow-btn-hover: 0 4px 16px rgba(59, 130, 246, 0.4); /* Darker hover shadow */
    --table-even: #334155; /* Dark even row */
    --table-hover: #475569; /* Dark hover */
    --table-hover-shadow: rgba(59, 130, 246, 0.2); /* Darker hover shadow */
    --no-data-bg: #334155; /* Dark no data */
    --no-data-color: #475569; /* Dark no data icon */
    --footer-bg: #0f172a; /* Darker footer */
    --footer-text: #94a3b8; /* Lighter footer text */
    --footer-link: #93c5fd; /* Light blue link */
    --footer-link-hover: #60a5fa; /* Hover link */
    --total-students-color: #60a5fa; /* Lighter blue for dark mode */
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: var(--bg-light);
    min-height: 100vh;
    color: var(--text-primary);
    line-height: 1.6;
    transition: background 0.3s ease, color 0.3s ease;
}

.container {
    max-width: var(--max-width);
    margin: 2rem auto;
    background: var(--bg-white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-container);
    overflow: hidden;
    transition: background 0.3s ease, box-shadow 0.3s ease;
}

/* Header - Elegant and Professional */
.header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    padding: var(--header-padding);
    text-align: center;
    position: relative;
    transition: background 0.3s ease;
}

.header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1470&q=80') center/cover;
    opacity: 0.1;
    z-index: 0;
}

.header-content {
    position: relative;
    z-index: 1;
}

.header h1 {
    font-size: 2.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    letter-spacing: -0.025em;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    transition: color 0.3s ease;
}

.header p {
    font-size: 1.125rem;
    opacity: 0.9;
    font-weight: 400;
    transition: opacity 0.3s ease;
}

/* Controls Section - Clean and Functional */
.controls {
    padding: var(--controls-padding);
    background: var(--bg-controls);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    transition: background 0.3s ease, border-color 0.3s ease;
}

.download-form {
    display: inline;
}

/* Buttons */
.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--btn-radius);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: var(--shadow-btn);
    letter-spacing: 0.025em;
    text-transform: uppercase;
    font-family: inherit;
}

.btn-primary {
    background: var(--secondary-color);
    color: white;
}

.btn-primary:hover,
.btn-primary:focus {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: var(--shadow-btn-hover);
    outline: none;
}

.btn-secondary {
    background: transparent;
    color: var(--secondary-color);
    border: 1px solid var(--secondary-color);
}

.btn-secondary:hover {
    background: var(--secondary-color);
    color: white;
}

/* Table Container - Responsive and Scrollable */
.table-container {
    overflow-x: auto;
    max-height: var(--table-max-height);
    padding: var(--table-padding);
    transition: padding 0.3s ease;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: var(--font-size-table);
    min-width: var(--table-min-width); /* Ensure horizontal scroll on small screens */
    transition: none; /* Tables don't transition */
}

thead {
    background: linear-gradient(135deg, var(--primary-color) 0%, #334155 100%);
    color: white;
    position: sticky;
    top: 0;
    z-index: 10;
    transition: background 0.3s ease;
}

th {
    padding: 1rem 0.75rem;
    text-align: left;
    font-weight: 500;
    font-size: var(--font-size-th);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-right: 1px solid var(--border-right-light);
    white-space: nowrap;
    transition: color 0.3s ease;
}

tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid var(--border-color);
}

tbody tr:nth-child(even) {
    background: var(--table-even);
}

tbody tr:hover {
    background: var(--table-hover);
    box-shadow: 0 2px 8px var(--table-hover-shadow);
}

td {
    padding: 0.875rem 0.75rem;
    border-bottom: 1px solid var(--border-color);
    border-right: 1px solid #f1f5f9; /* Light border, override in dark mode via variables if needed */
    vertical-align: middle;
    white-space: nowrap;
    transition: background 0.3s ease, color 0.3s ease;
}

.total-students {
    color: var(--total-students-color);
    font-weight: 600;
}

.no-data {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-secondary);
    font-size: 1.125rem;
    background: var(--no-data-bg);
    transition: background 0.3s ease, color 0.3s ease;
}

.no-data i {
    font-size: 3rem;
    color: var(--no-data-color);
    margin-bottom: 1rem;
    transition: color 0.3s ease;
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

/* Responsive Adjustments */
@media (max-width: 768px) {
    .container {
        margin: 1rem;
        border-radius: 12px;
    }

    .header {
        padding: 2rem 1rem;
    }

    .header h1 {
        font-size: 1.75rem;
    }

    .header p {
        font-size: 1rem;
    }

    .controls {
        flex-direction: column;
        text-align: center;
        padding: 1rem;
    }

    .btn {
        width: 100%;
        justify-content: center;
        margin-bottom: 0.5rem;
    }

    .table-container {
        padding: 1rem;
    }

    th, td {
        padding: 0.75rem 0.5rem;
        font-size: 0.8rem;
    }

    .no-data {
        padding: 2rem 1rem;
    }
}

/* Accessibility */
.btn:focus {
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
    }

    .container {
        box-shadow: none !important;
        border-radius: 0 !important;
        margin: 0 !important;
    }

    .header, .controls, .footer {
        display: none !important;
    }

    .table-container {
        overflow: visible !important;
        padding: 0 !important;
        max-height: none !important;
    }

    table {
        min-width: auto !important;
        font-size: 10px !important;
    }

    th, td {
        border: 1px solid #000 !important;
        padding: 0.5rem !important;
    }

    tbody tr:hover {
        background: transparent !important;
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

    .controls, .table-container, .no-data {
        border-color: #000;
    }
}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1><i class="fas fa-graduation-cap"></i>Primary Enrollment Data</h1>
                <p>Comprehensive overview of enrollment statistics across primary schools</p>
            </div>
        </div>

        <div class="controls">
            <form method="POST" class="download-form">
                <button type="submit" name="download" class="btn btn-primary">
                    <i class="fas fa-download"></i>Download as Excel
                </button>
            </form>
            <a href="Admin.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>Back to Admin Dashboard
            </a>
        </div>

        <div class="table-container">
            <table role="table" aria-label="Primary Enrollment Data">
                <thead>
                    <tr>
                        <th>Registration Number</th>
                        <th>School Name</th>
                        <th>Female Reception</th>
                        <th>Male Reception</th>
                        <th>Reception Total</th>
                        <th>Grade 1 Girls</th>
                        <th>Grade 1 Boys</th>
                        <th>Grade 1 Total</th>
                        <th>Grade 2 Girls</th>
                        <th>Grade 2 Boys</th>
                        <th>Grade 2 Total</th>
                        <th>Grade 3 Girls</th>
                        <th>Grade 3 Boys</th>
                        <th>Grade 3 Total</th>
                        <th>Grade 4 Girls</th>
                        <th>Grade 4 Boys</th>
                        <th>Grade 4 Total</th>
                        <th>Grade 5 Girls</th>
                        <th>Grade 5 Boys</th>
                        <th>Grade 5 Total</th>
                        <th>Grade 6 Girls</th>
                        <th>Grade 6 Boys</th>
                        <th>Grade 6 Total</th>
                        <th>Grade 7 Girls</th>
                        <th>Grade 7 Boys</th>
                        <th>Grade 7 Total</th>
                        <th>Repeaters Girls</th>
                        <th>Repeaters Boys</th>
                        <th>Repeaters Total</th>
                        <th>Overall Total</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($enrollmentData)): ?>
                        <?php foreach ($enrollmentData as $enrollment): ?>
                            <tr role="row">
                                <td><?php echo htmlspecialchars($enrollment['registration_number']); ?></td>
                                <td><strong><?php echo htmlspecialchars($enrollment['school_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollment['female_reception']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['male_reception']); ?></td>
                                <td><strong><?php echo htmlspecialchars($enrollment['reception_total']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollment['grade1_girls']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['grade1_boys']); ?></td>
                                <td><strong><?php echo htmlspecialchars($enrollment['grade1_total']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollment['grade2_girls']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['grade2_boys']); ?></td>
                                <td><strong><?php echo htmlspecialchars($enrollment['grade2_total']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollment['grade3_girls']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['grade3_boys']); ?></td>
                                <td><strong><?php echo htmlspecialchars($enrollment['grade3_total']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollment['grade4_girls']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['grade4_boys']); ?></td>
                                <td><strong><?php echo htmlspecialchars($enrollment['grade4_total']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollment['grade5_girls']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['grade5_boys']); ?></td>
                                <td><strong><?php echo htmlspecialchars($enrollment['grade5_total']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollment['grade6_girls']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['grade6_boys']); ?></td>
                                <td><strong><?php echo htmlspecialchars($enrollment['grade6_total']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollment['grade7_girls']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['grade7_boys']); ?></td>
                                <td><strong><?php echo htmlspecialchars($enrollment['grade7_total']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollment['repeaters_girls']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['repeaters_boys']); ?></td>
                                <td><strong><?php echo htmlspecialchars($enrollment['repeaters_total']); ?></strong></td>
                                <td><strong class="total-students"><?php echo htmlspecialchars($enrollment['overall_total']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollment['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['updated_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="32" class="no-data">
                                <i class="fas fa-inbox"></i>
                                <div>No enrollment data available at this time.</div>
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