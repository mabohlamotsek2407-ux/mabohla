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

// Retrieve preschool enrollment data, joining with schools table and filtering for recent entries
$sql = "
    SELECT pe.*, s.registration_number, s.school_name 
    FROM preschool_enrollment pe
    JOIN schools s ON pe.school_id = s.school_id
    WHERE pe.entry_date IN (
        SELECT MAX(entry_date)
        FROM preschool_enrollment
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
    header("Content-Disposition: attachment; filename=\"preschool_enrollment_data.xls\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Output the column headings
    echo "ID\tRegistration Number\tSchool Name\tFemale Reception\tMale Reception\tAge 3 Girls\tAge 3 Boys\tAge 3 Total\tAge 4 Girls\tAge 4 Boys\tAge 4 Total\tAge 5 Girls\tAge 5 Boys\tAge 5 Total\tOverall Total\tEntry Date\n";

    // Output the data
    foreach ($data as $row) {
        echo implode("\t", [
            $row['id'],
            $row['registration_number'],
            $row['school_name'],
            $row['female_reception'],
            $row['male_reception'],
            $row['age3_girls'],
            $row['age3_boys'],
            $row['age3_total'],
            $row['age4_girls'],
            $row['age4_boys'],
            $row['age4_total'],
            $row['age5_girls'],
            $row['age5_boys'],
            $row['age5_total'],
            $row['overall_total'],
            $row['entry_date']
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
    <title>Preschool Enrollment Data</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Link to external CSS file (create preschool-enrollment.css with the CSS below) -->
    <link rel="stylesheet" href="preschool-enrollment.css">
    <style>
    /* Import Poppins font from Google Fonts */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

/* CSS Variables for Light Mode (Adapted from Original Purple-Blue Theme) */
:root {
    /* Colors (Preserving Original Aesthetic) */
    --primary-color: #2c3e50; /* Dark blue-gray for headers */
    --secondary-color: #3498db; /* Blue accent */
    --bg-light: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Original purple-blue gradient */
    --bg-white: #ffffff; /* Pure white container */
    --bg-controls: #f8f9fa; /* Controls background */
    --text-primary: #2c3e50; /* Dark text */
    --text-secondary: #6c757d; /* Medium gray */
    --border-color: #e9ecef; /* Light borders */
    --border-right-light: rgba(255, 255, 255, 0.1); /* Header borders */
    --shadow-container: 0 20px 40px rgba(0, 0, 0, 0.1); /* Container shadow */
    --shadow-btn: 0 4px 8px rgba(0, 0, 0, 0.1); /* Button shadow */
    --shadow-btn-hover: 0 6px 12px rgba(52, 152, 219, 0.3); /* Button hover shadow */
    --table-even: #f8f9fa; /* Even row background */
    --table-hover: #e3f2fd; /* Hover background */
    --table-hover-shadow: rgba(52, 152, 219, 0.1); /* Hover shadow */
    --no-data-bg: #f8f9fa; /* No data background */
    --no-data-color: #cbd5e1; /* No data icon color */
    --footer-bg: #2c3e50; /* Footer background */
    --footer-text: #bdc3c7; /* Footer text */
    --footer-link: #3498db; /* Footer link */
    --footer-link-hover: #2980b9; /* Footer link hover */
    --total-students-color: #3498db; /* Total students highlight */

    /* Layout & Spacing */
    --border-radius: 15px; /* Container radius */
    --btn-radius: 8px; /* Button radius */
    --transition: all 0.3s ease; /* Smooth transitions */
    --header-padding: 30px; /* Header padding */
    --controls-padding: 20px 30px; /* Controls padding */
    --table-padding: 20px; /* Table container padding */
    --font-size-table: 14px; /* Table font size */
    --font-size-th: 13px; /* Header font size */
    --max-width: 1400px; /* Container max width */
    --table-min-width: 1400px; /* Table min width for scroll (16 columns) */
    --table-max-height: 70vh; /* Table max height */
}

/* Dark Mode Overrides (Darker Purple-Blue Theme) */
body.dark {
    --bg-light: linear-gradient(135deg, #4a5568 0%, #2d3748 100%); /* Darker gradient */
    --bg-white: #2c3e50; /* Dark container */
    --bg-controls: #34495e; /* Dark controls */
    --text-primary: #ecf0f1; /* Light text */
    --text-secondary: #bdc3c7; /* Light gray */
    --border-color: #5a6c7d; /* Dark borders */
    --border-right-light: rgba(0, 0, 0, 0.2); /* Dark header borders */
    --shadow-container: 0 20px 40px rgba(0, 0, 0, 0.3); /* Darker shadow */
    --shadow-btn: 0 4px 8px rgba(0, 0, 0, 0.3); /* Darker button shadow */
    --shadow-btn-hover: 0 6px 12px rgba(52, 152, 219, 0.4); /* Darker hover shadow */
    --table-even: #34495e; /* Dark even row */
    --table-hover: #5a6c7d; /* Dark hover */
    --table-hover-shadow: rgba(52, 152, 219, 0.2); /* Darker hover shadow */
    --no-data-bg: #34495e; /* Dark no data */
    --no-data-color: #5a6c7d; /* Dark no data icon */
    --footer-bg: #1a252f; /* Darker footer */
    --footer-text: #bdc3c7; /* Lighter footer text */
    --footer-link: #5dade2; /* Light blue link */
    --footer-link-hover: #3498db; /* Hover link */
    --total-students-color: #5dade2; /* Lighter blue for dark mode */
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
    padding: 20px;
    color: var(--text-primary);
    line-height: 1.6;
    transition: background 0.3s ease, color 0.3s ease;
}

.container {
    max-width: var(--max-width);
    margin: 0 auto;
    background: var(--bg-white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-container);
    overflow: hidden;
    transition: background 0.3s ease, box-shadow 0.3s ease;
}

/* Header - Gradient with Icon */
.header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    padding: var(--header-padding);
    text-align: center;
    transition: background 0.3s ease;
}

.header h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    transition: color 0.3s ease;
}

.header h1 i {
    font-size: 2.5rem;
    opacity: 0.9;
    transition: opacity 0.3s ease;
}

.header p {
    font-size: 1.1rem;
    opacity: 0.9;
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
    gap: 15px;
    transition: background 0.3s ease, border-color 0.3s ease;
}

.download-form {
    display: inline-flex;
    align-items: center;
}

/* Buttons */
.btn {
    padding: 12px 24px;
    border: none;
    border-radius: var(--btn-radius);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: var(--shadow-btn);
    font-family: inherit;
}

.btn-primary {
    background: var(--secondary-color);
    color: white;
}

.btn-primary:hover,
.btn-primary:focus {
    background: #2980b9;
    transform: translateY(-2px);
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
    background: linear-gradient(135deg, #34495e 0%, var(--primary-color) 100%);
    color: white;
    position: sticky;
    top: 0;
    z-index: 10;
    transition: background 0.3s ease;
}

th {
    padding: 15px 12px;
    text-align: left;
    font-weight: 600;
    font-size: var(--font-size-th);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-right: 1px solid var(--border-right-light);
    white-space: nowrap;
    transition: color 0.3s ease;
}

tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid var(--border-color);
}

tbody tr:nth-child(even) {
    background: var(--table-even);
}

tbody tr:hover {
    background: var(--table-hover);
    transform: scale(1.01);
    box-shadow: 0 4px 8px var(--table-hover-shadow);
}

td {
    padding: 12px;
    border-bottom: 1px solid var(--border-color);
    border-right: 1px solid var(--border-color);
    vertical-align: middle;
    transition: background 0.3s ease, color 0.3s ease;
}

.total-students {
    color: var(--total-students-color);
    font-weight: 600;
}

strong {
    font-weight: 600;
    color: var(--text-primary);
}

.no-data {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-secondary);
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
    body {
        padding: 10px;
    }

    .container {
        margin: 0;
        border-radius: 10px;
    }

    .header {
        padding: 20px;
    }

    .header h1 {
        font-size: 2rem;
    }

    .header p {
        font-size: 1rem;
    }

    .controls {
        flex-direction: column;
        text-align: center;
        padding: 15px;
    }

    .btn {
        width: 100%;
        justify-content: center;
        margin-bottom: 10px;
    }

    .table-container {
        padding: 10px;
    }

    th, td {
        padding: 10px 8px;
        font-size: 12px;
    }

    .no-data {
        padding: 40px 10px;
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
        padding: 0 !important;
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

    .controls, .table-container, .no-data {
        border-color: #000;
    }
}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-graduation-cap"></i>Preschool Enrollment Data</h1>
            <p>Information on Preschool Enrollment in Schools</p>
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
            <table role="table" aria-label="Preschool Enrollment Data">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Registration Number</th>
                        <th>School Name</th>
                        <th>Female Reception</th>
                        <th>Male Reception</th>
                        <th>Age 3 Girls</th>
                        <th>Age 3 Boys</th>
                        <th>Age 3 Total</th>
                        <th>Age 4 Girls</th>
                        <th>Age 4 Boys</th>
                        <th>Age 4 Total</th>
                        <th>Age 5 Girls</th>
                        <th>Age 5 Boys</th>
                        <th>Age 5 Total</th>
                        <th>Overall Total</th>
                        <th>Entry Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($enrollmentData)): ?>
                        <?php foreach ($enrollmentData as $enrollment): ?>
                            <tr role="row">
                                <td><?php echo htmlspecialchars($enrollment['id']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['registration_number']); ?></td>
                                <td><strong><?php echo htmlspecialchars($enrollment['school_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollment['female_reception']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['male_reception']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['age3_girls']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['age3_boys']); ?></td>
                                <td><strong><?php echo htmlspecialchars($enrollment['age3_total']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollment['age4_girls']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['age4_boys']); ?></td>
                                <td><strong><?php echo htmlspecialchars($enrollment['age4_total']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollment['age5_girls']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['age5_boys']); ?></td>
                                <td><strong><?php echo htmlspecialchars($enrollment['age5_total']); ?></strong></td>
                                <td><strong class="total-students"><?php echo htmlspecialchars($enrollment['overall_total']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollment['entry_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="16" class="no-data">
                                <i class="fas fa-inbox"></i>
                                <div>No data available</div>
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