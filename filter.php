<?php
// Database connection configuration
$servername = "sql104.infinityfree.com";
$username   = "if0_40021406"; 
$password   = "Op70TI711cS2lB6";
$dbname     = "if0_40021406_moet1";
$charset = 'utf8mb4';

$dsn = "mysql:host=$servername;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// Initialize filter variables from GET or POST
$request = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;

$constituency = $request['constituency'] ?? '';
$electricity = $request['electricity'] ?? '';
$water = $request['water'] ?? '';
$level = $request['level'] ?? ''; // 'Primary' or 'High'
$columns = $request['columns'] ?? []; // array of selected columns

// Validate level input
$allowed_levels = ['Primary', 'High'];
if (!in_array($level, $allowed_levels)) {
    $level = ''; // reset if invalid or not set
}

// Define running tap water values (case-insensitive)
$runningWaterValues = ['piped', 'tap water', 'running water', 'well', 'rainwater'];

// Define all possible optional columns and their SQL expressions and labels
$allColumns = [
    'electricity' => [
        'sql' => 'ei.has_electricity',
        'label' => 'Electricity',
        'icon' => 'bolt',    // FontAwesome icon name
        'formatter' => fn($v) => ucfirst($v ?? 'no'),
    ],
    'running_tap_water' => [
        'sql' => '', // will be set later
        'label' => 'Running Tap Water',
        'icon' => 'tint',
        'formatter' => fn($v) => ucfirst($v ?? 'no'),
    ],
    'additional_latrines' => [
        'sql' => 'MAX(COALESCE(at.additional_latrines_needed, 0))',
        'label' => 'Require Latrines',
        'icon' => 'restroom',
        'formatter' => fn($v) => ($v == 1) ? 'Yes' : 'No',
    ],
    'additional_classrooms' => [
        'sql' => "MAX(CASE WHEN ac.require_classrooms = 'yes' THEN 1 ELSE 0 END)",
        'label' => 'Require Classrooms',
        'icon' => 'chalkboard-teacher',
        'formatter' => fn($v) => ($v == 1) ? 'Yes' : 'No',
    ],
    'internet' => [
        'sql' => "MAX(CASE WHEN ii.has_internet = 'yes' THEN 1 ELSE 0 END)",
        'label' => 'Has Internet',
        'icon' => 'wifi',
        'formatter' => fn($v) => ($v == 1) ? 'Yes' : 'No',
    ],
];

// Always show these columns
$baseColumns = [
    'school_name' => ['sql' => 's.school_name', 'label' => 'School Name', 'icon' => 'school', 'formatter' => 'htmlspecialchars'],
    'reg_no' => ['sql' => 's.registration_number', 'label' => 'Reg No', 'icon' => 'id-badge', 'formatter' => 'htmlspecialchars'],
    'constituency' => ['sql' => 's.constituency', 'label' => 'Constituency', 'icon' => 'map-marker-alt', 'formatter' => 'htmlspecialchars'],
];

// Validate selected columns: keep only allowed keys
$columns = is_array($columns) ? array_intersect($columns, array_keys($allColumns)) : [];
if (empty($columns)) {
    // Default columns to show if none selected
    $columns = ['electricity', 'running_tap_water', 'additional_latrines', 'additional_classrooms', 'internet'];
}

// Prepare running water placeholders only if needed
$needRunningWaterPlaceholders = in_array('running_tap_water', $columns) || ($water === 'yes' || $water === 'no');

$runningWaterPlaceholders = [];
$runningWaterParams = [];
if ($needRunningWaterPlaceholders) {
    foreach ($runningWaterValues as $i => $val) {
        $ph = ":water_val_$i";
        $runningWaterPlaceholders[] = $ph;
        $runningWaterParams[$ph] = $val;
    }
}

// Set the SQL for running tap water column now with named placeholders if needed
if (in_array('running_tap_water', $columns)) {
    $allColumns['running_tap_water']['sql'] = "CASE WHEN LOWER(wi.water_source) IN (" . implode(',', $runningWaterPlaceholders) . ") THEN 'yes' ELSE 'no' END";
}

// Build SELECT clause
$selectParts = [];
foreach ($baseColumns as $key => $col) {
    $selectParts[] = "{$col['sql']} AS {$key}";
}
foreach ($columns as $colKey) {
    $selectParts[] = $allColumns[$colKey]['sql'] . " AS {$colKey}";
}

$sql = "SELECT " . implode(",\n", $selectParts) . "
    FROM schools s
    LEFT JOIN electricity_infrastructure ei ON s.school_id = ei.school_id
    LEFT JOIN water_infrastructure wi ON s.school_id = wi.school_id
    LEFT JOIN additionaltoilets at ON s.school_id = at.school_id
    LEFT JOIN additionalclassrooms ac ON s.school_id = ac.school_id
    LEFT JOIN internet_infrastructure ii ON s.school_id = ii.school_id
    WHERE 1=1
";

// Filter by level based on cluster/centre presence
if ($level === 'High') {
    $sql .= " AND s.cluster IS NOT NULL AND s.cluster != '' ";
} elseif ($level === 'Primary') {
    $sql .= " AND s.centre IS NOT NULL AND s.centre != '' ";
} else {
    $sql .= " AND 0=1 ";
}

// Start params array
$params = [];

// Add running water params if needed
if ($needRunningWaterPlaceholders) {
    $params = array_merge($params, $runningWaterParams);
}

// Filter by constituency if provided
if ($constituency !== '') {
    $sql .= " AND s.constituency LIKE :constituency ";
    $params[':constituency'] = "%$constituency%";
}

// Filter by electricity if provided
if ($electricity === 'yes' || $electricity === 'no') {
    $sql .= " AND ei.has_electricity = :electricity ";
    $params[':electricity'] = $electricity;
}

// Filter by running tap water if provided
if ($water === 'yes' || $water === 'no') {
    $sql .= " AND (CASE WHEN LOWER(wi.water_source) IN (" . implode(',', $runningWaterPlaceholders) . ") THEN 'yes' ELSE 'no' END) = :water ";
    $params[':water'] = $water;
}

$sql .= " GROUP BY s.school_id ORDER BY s.school_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

// Function to download data as Excel
function downloadExcel(array $results, array $baseColumns, array $allColumns, array $columns) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"schools_data.xls\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Prepare headers (base + selected optional columns)
    $headers = [];
    foreach ($baseColumns as $col) {
        $headers[] = $col['label'];
    }
    foreach ($columns as $colKey) {
        $headers[] = $allColumns[$colKey]['label'];
    }

    echo implode("\t", $headers) . "\n";

    // Output each row
    foreach ($results as $row) {
        $rowData = [];

        // Base columns
        foreach ($baseColumns as $key => $_) {
            $val = $row[$key] ?? '';
            // Use htmlspecialchars but decode HTML entities instead for Excel
            $rowData[] = html_entity_decode($val);
        }
        // Optional columns with formatter applied
        foreach ($columns as $colKey) {
            $val = $row[$colKey] ?? '';
            $formatter = $allColumns[$colKey]['formatter'];
            $valueFormatted = $formatter($val);
            // Decode any HTML entities for Excel
            $rowData[] = html_entity_decode($valueFormatted);
        }

        echo implode("\t", $rowData) . "\n";
    }
    exit();
}

// Handle download request (triggered by POST parameter 'download')
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download'])) {
    // We already have $results from above, use them for download
    downloadExcel($results, $baseColumns, $allColumns, $columns);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>School Filter - MOET</title>
    <!-- Font Awesome CDN for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
:root {
    --primary-color: #1e3a8a;
    --secondary-color: #3b82f6;
    --accent-color: #60a5fa;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --bg-light: #f8fafc;
    --bg-white: #ffffff;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --border-color: #e5e7eb;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
    --border-radius: 0.75rem;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --gradient-primary: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    --gradient-secondary: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
    --back-btn-bg: var(--secondary-color);
    --back-btn-hover: #2563eb;
    --error-bg: #fef2f2;
    --error-text: #dc2626;
    --info-bg: #eff6ff;
    --info-text: #2563eb;
    --table-even: #f9fafb;
    --table-hover: #eff6ff;
    --table-hover-shadow: rgba(59, 130, 246, 0.1);
    --badge-yes-bg: #dbeafe;
    --badge-yes-text: #1e40af;
    --badge-no-bg: #fee2e2;
    --badge-no-text: #991b1b;
    --thead-bg: var(--gradient-primary);
    --thead-text: white;
    --scrollbar-track: #f1f5f9;
    --scrollbar-thumb: var(--secondary-color);
    --scrollbar-thumb-hover: var(--accent-color);
    --focus-ring: rgba(59, 130, 246, 0.2);
    --logo-blue: #3b82f6;
}

/* Dark Mode */
body.dark {
    --bg-light: #111827;
    --bg-white: #1f2937;
    --text-primary: #f9fafb;
    --text-secondary: #d1d5db;
    --border-color: #374151;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -2px rgba(0, 0, 0, 0.3);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -4px rgba(0, 0, 0, 0.3);
    --back-btn-bg: #1d4ed8;
    --back-btn-hover: #1e40af;
    --error-bg: #451a03;
    --error-text: #f87171;
    --info-bg: #0c4a6e;
    --info-text: #93c5fd;
    --table-even: #374151;
    --table-hover: #4b5563;
    --table-hover-shadow: rgba(96, 165, 250, 0.2);
    --badge-yes-bg: #1e40af;
    --badge-yes-text: #dbeafe;
    --badge-no-bg: #7f1d1d;
    --badge-no-text: #fca5a5;
    --thead-bg: linear-gradient(135deg, #1f2937, #374151);
    --thead-text: #e5e7eb;
    --scrollbar-track: #1f2937;
    --scrollbar-thumb: #60a5fa;
    --scrollbar-thumb-hover: #93c5fd;
    --focus-ring: rgba(96, 165, 250, 0.3);
}

* {
    box-sizing: border-box;
}

body {
    font-family: 'Inter', Georgia, serif;
    background: linear-gradient(to bottom, var(--bg-light), #e0e7ff);
    color: var(--text-primary);
    line-height: 1.6;
    margin: 0;
    padding: 20px 0;
    min-height: 100vh;
    transition: var(--transition);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    position: relative;
}

/* Hero Header with MOET Logo - Applied SVG from Image */
.header {
    text-align: center;
    margin-bottom: 40px;
    position: relative;
    background: var(--bg-white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    padding: 30px;
    overflow: hidden;
}

.header::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-primary);
}

.moet-logo {
    display: inline-block;
    width: 120px;
    height: 80px;
    margin-bottom: 15px;
}

.moet-logo svg {
    width: 100%;
    height: 100%;
    fill: var(--logo-blue); /* Applies blue theme from CSS variables */
}

/* Inline SVG for MOET Logo (Approximated from Provided Image) */
.moet-logo svg {
    <svg viewBox="0 0 400 200" xmlns="http://www.w3.org/2000/svg">
        <!-- Pencil Icon -->
        <path d="M50 20 L70 100 L60 110 L40 30 Z" fill="#3b82f6" /> <!-- Pencil body -->
        <!-- Graduation Cap -->
        <path d="M55 10 L65 10 L60 20 Z" fill="#000" /> <!-- Cap top -->
        <path d="M50 20 L70 20 L75 25 L45 25 Z" fill="#3b82f6" /> <!-- Cap base -->
        <!-- MOET Text -->
        <text x="100" y="60" font-family="Arial, sans-serif" font-size="40" font-weight="bold" fill="#3b82f6">MOET</text>
        <!-- Open Book Waves -->
        <path d="M80 100 Q100 90 120 100 Q140 90 160 100 Q180 90 200 100" stroke="#3b82f6" stroke-width="2" fill="none" />
        <!-- Slogan -->
        <text x="200" y="160" font-family="Georgia, serif" font-size="12" fill="#3b82f6" font-style="italic" text-anchor="middle">Quality Education our Commitment</text>
    </svg>
    /* Note: Embed this SVG in your HTML as innerHTML of .moet-logo for rendering; the paths are approximations based on the image. */
}

.moet-title {
    color: var(--primary-color);
    font-weight: 700;
    font-size: 2.5rem;
    letter-spacing: -0.025em;
    margin: 0 0 5px 0;
}

.moet-slogan {
    color: var(--text-secondary);
    font-size: 1rem;
    font-style: italic;
    margin: 0;
    font-weight: 400;
}

h1 {
    text-align: center;
    margin: 20px 0 30px 0;
    color: var(--text-primary);
    font-weight: 600;
    font-size: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

h1 i {
    color: var(--logo-blue);
    font-size: 2.2rem;
}

/* Back Button */
.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--gradient-secondary);
    color: white;
    padding: 12px 24px;
    border-radius: var(--border-radius);
    text-decoration: none;
    font-weight: 500;
    font-size: 1rem;
    transition: var(--transition);
    box-shadow: var(--shadow-md);
    border: none;
    cursor: pointer;
    margin-bottom: 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.back-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.back-btn:hover::before {
    left: 100%;
}

.back-btn:hover {
    background: var(--gradient-primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    color: white;
}

.back-btn i {
    font-size: 1.2rem;
    transition: transform 0.2s ease;
}

.back-btn:hover i {
    transform: scale(1.1) rotate(-5deg);
}

/* Filter Form */
.filters {
    background: var(--bg-white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    padding: 40px;
    margin-bottom: 40px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    border: 1px solid var(--border-color);
    transition: var(--transition);
    position: relative;
}

.filters::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-primary);
    border-radius: var(--border-radius) var(--border-radius) 0 0;
}

.filters label {
    display: flex;
    flex-direction: column;
    font-weight: 600;
    color: var(--text-primary);
    gap: 10px;
    transition: color 0.3s ease;
    position: relative;
}

.filters label i {
    color: var(--secondary-color);
    font-size: 1.2rem;
    align-self: flex-start;
    transition: color 0.3s ease;
    margin-bottom: 5px;
}

.required {
    color: var(--danger-color);
    font-size: 0.875rem;
    font-weight: 600;
}

select, input[type="text"] {
    padding: 14px 18px;
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius);
    background-color: var(--bg-white);
    color: var(--text-primary);
    font-size: 1rem;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
    width: 100%;
}

select:focus, input[type="text"]:focus {
    outline: none;
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 4px var(--focus-ring), var(--shadow-md);
    transform: translateY(-1px);
    background-color: #fff;
}

select[multiple] {
    height: 160px;
    padding: 10px;
}

/* Buttons */
.filter-btn, .download-btn {
    background: var(--gradient-primary);
    color: white;
    border: none;
    padding: 16px 32px;
    font-weight: 600;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
    box-shadow: var(--shadow-md);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 1.1rem;
    position: relative;
    overflow: hidden;
}

.filter-btn {
    justify-self: center;
    grid-column: 1 / -1;
}

.download-btn {
    background: var(--gradient-secondary);
    grid-column: auto;
    width: auto;
    justify-self: start;
    margin-bottom: 0;
}

.filter-btn::before, .download-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.filter-btn:hover::before, .download-btn:hover::before {
    left: 100%;
}

.filter-btn:hover, .download-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    color: white;
}

.filter-btn:hover {
    background: var(--gradient-secondary);
}

.download-btn:hover {
    background: linear-gradient(135deg, #059669, #10b981);
}

/* Messages */
.error-message, .info-message {
    padding: 20px;
    border-radius: var(--border-radius);
    font-weight: 500;
    text-align: center;
    margin: 20px 0;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    position: relative;
}

.error-message {
    background: var(--error-bg);
    color: var(--error-text);
    border-left: 5px solid var(--danger-color);
}

.error-message i {
    color: var(--danger-color);
    font-size: 1.2rem;
}

.info-message {
    background: var(--info-bg);
    color: var(--info-text);
    border-left: 5px solid var(--secondary-color);
}

.info-message i {
    color: var(--secondary-color);
    font-size: 1.2rem;
}

/* Table */
.table-wrapper {
    background: var(--bg-white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    border: 1px solid var(--border-color);
    margin-bottom: 40px;
    position: relative;
    transition: var(--transition);
}

.table-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-primary);
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.95rem;
    table-layout: auto;
}

thead {
    background: var(--thead-bg);
    position: sticky;
    top: 0;
    z-index: 10;
}

thead th {
    color: var(--thead-text);
    font-weight: 600;
    padding: 18px 24px;
    text-align: left;
    border: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 140px;
    transition: var(--transition);
}

thead th i {
    margin-right: 10px;
    opacity: 0.9;
    width: 18px;
    display: inline-block;
    color: var(--thead-text);
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
    transform: translateX(4px);
    box-shadow: 0 4px 12px var(--table-hover-shadow);
}

tbody td {
    padding: 18px 24px;
    border: none;
    vertical-align: middle;
    color: var(--text-primary);
    word-wrap: break-word;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    min-width: 120px;
    transition: var(--transition);
}

/* Badges */
.badge {
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: inline-block;
    transition: var(--transition);
}

.badge-yes {
    background-color: var(--badge-yes-bg);
    color: var(--badge-yes-text);
}

.badge-no {
    background-color: var(--badge-no-bg);
    color: var(--badge-no-text);
}

.badge:hover {
    transform: scale(1.05);
}

/* Responsive Design */
@media (max-width: 768px) {
    body {
        padding: 10px 0;
        font-size: 0.9rem;
        background: var(--bg-light);
    }

    .header {
        padding: 20px;
        margin-bottom: 20px;
    }

    .moet-title {
        font-size: 2rem;
    }

    h1 {
        font-size: 1.8rem;
        flex-direction: column;
        gap: 10px;
    }

    .filters {
        grid-template-columns: 1fr;
        padding: 25px;
        gap: 20px;
    }

    .filter-btn {
        width: 100%;
    }

    .table-wrapper {
        overflow-x: auto;
        border-radius: var(--border-radius);
        margin-bottom: 20px;
    }

    table {
        min-width: 750px;
    }

    thead th, tbody td {
        padding: 14px 16px;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    .table-wrapper::after {
        content: '';
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 30px;
        background: linear-gradient(to left, var(--bg-light), transparent);
        pointer-events: none;
        z-index: 1;
    }

    .download-btn {
        width: 100%;
        margin-top: 10px;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .filters {
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    table {
        min-width: 850px;
    }
}

/* Accessibility & Print */
@media (prefers-reduced-motion: reduce) {
    * {
        transition-duration: 0.01ms !important;
        animation-duration: 0.01ms !important;
    }
}

@media (prefers-contrast: high) {
    :root {
        --border-color: #000;
        --text-secondary: #000;
    }
    .filters, .table-wrapper, .header {
        border-color: #000;
    }
}

@media print {
    body {
        background: white !important;
        color: black !important;
        padding: 0;
    }

    .filters, .back-btn, .filter-btn, .download-btn, .error-message, .info-message {
        display: none !important;
    }

    .header {
        display: block;
        box-shadow: none;
        border: 1px solid #000;
        padding: 10px;
    }

    .moet-logo svg path, .moet-logo svg text {
        fill: #000 !important;
    }

    .moet-title {
        color: #000 !important;
    }

    .table-wrapper {
        overflow: visible !important;
        box-shadow: none;
        border: 1px solid #000;
    }

    table {
        font-size: 11px;
        min-width: auto;
    }

    thead th {
        background: #1e3a8a !important;
        color: white !important;
        border: 1px solid #000;
    }

    tbody td {
        border: 1px solid #000;
    }

    tbody tr:hover {
        background: transparent !important;
    }

    .badge {
        border: 1px solid #000;
    }
}

/* Custom Scrollbar */
.table-wrapper::-webkit-scrollbar {
    height: 10px;
}

.table-wrapper::-webkit-scrollbar-track {
    background: var(--scrollbar-track);
    border-radius: 5px;
}

.table-wrapper::-webkit-scrollbar-thumb {
    background: var(--scrollbar-thumb);
    border-radius: 5px;
}

.table-wrapper::-webkit-scrollbar-thumb:hover {
    background: var(--scrollbar-thumb-hover);
}
    </style>
</head>
<body>

<div class="container">
    <!-- MOET Header -->
    <div class="header">
        <div class="moet-logo">
            <!-- Simplified SVG for MOET Logo (Pen with cap, MOET text, book base, slogan) -->
            <svg viewBox="0 0 200 100" xmlns="http://www.w3.org/2000/svg">
                <!-- Pen with cap -->
                <path d="M10 10 L20 90 L30 90 L25 10 Z" fill="var(--logo-blue)" stroke="#1e3a8a" stroke-width="2"/>
                <!-- Cap -->
                <circle cx="20" cy="5" r="8" fill="#1e3a8a"/>
                <!-- MOET Text (stylized) -->
                <text x="50" y="40" font-family="Arial, sans-serif" font-size="24" font-weight="bold" fill="var(--logo-blue)">MOET</text>
                <!-- Book base -->
                <path d="M0 80 Q100 90 200 80 L200 100 L0 100 Z" fill="var(--logo-blue)" opacity="0.3"/>
                <!-- Slogan -->
                <text x="50" y="95" font-family="Arial, sans-serif" font-size="8" fill="var(--text-secondary)" text-anchor="start">Quality Education our Commitment</text>
            </svg>
        </div>
        <h2 class="moet-title">Ministry of Education and Training</h2>
        <p class="moet-slogan">Quality Education our Commitment</p>
    </div>

    <a href="Admin.php" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i> Back to Admin
    </a>
    <h1><i class="fa-solid fa-graduation-cap"></i> Filter Schools and Select Columns</h1>

    <!-- Filter form -->
    <form method="get" action="" class="filters" aria-label="School filter form">
        <label for="level">
            <i class="fa-solid fa-school"></i> School Level: <span class="required">*</span>
            <select id="level" name="level" required aria-required="true" aria-describedby="levelHelp">
                <option value="" <?= $level === '' ? 'selected' : '' ?>>-- Select Level --</option>
                <option value="Primary" <?= $level === 'Primary' ? 'selected' : '' ?>>Primary Schools</option>
                <option value="High" <?= $level === 'High' ? 'selected' : '' ?>>High Schools</option>
            </select>
        </label>

        <label for="constituency">
            <i class="fa-solid fa-map-marker-alt"></i> Constituency:
            <input type="text" id="constituency" name="constituency" value="<?= htmlspecialchars($constituency) ?>" placeholder="e.g. Leribe No 12" />
        </label>

        <label for="columns">
            <i class="fa-solid fa-table-columns"></i> Select Columns (Ctrl+Click for Multiple):
            <select id="columns" name="columns[]" multiple aria-multiselectable="true" size="6">
                <?php foreach ($allColumns as $key => $col): ?>
                    <option value="<?= htmlspecialchars($key) ?>" <?= in_array($key, $columns) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($col['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <button type="submit" class="filter-btn" aria-label="Apply filters">
            <i class="fa-solid fa-filter"></i> Apply Filters
        </button>
    </form>

    <?php if ($level === ''): ?>
        <p class="error-message"><i class="fa-solid fa-exclamation-triangle"></i> Please select a school level to view results.</p>
    <?php elseif (count($results) === 0): ?>
        <p class="info-message"><i class="fa-solid fa-info-circle"></i> No schools found matching your criteria. Try adjusting the filters.</p>
    <?php else: ?>
        <!-- Download Form -->
        <form method="post" action="" style="margin-bottom: 20px;">
            <input type="hidden" name="level" value="<?= htmlspecialchars($level) ?>">
            <input type="hidden" name="constituency" value="<?= htmlspecialchars($constituency) ?>">
            <input type="hidden" name="electricity" value="<?= htmlspecialchars($electricity) ?>">
            <input type="hidden" name="water" value="<?= htmlspecialchars($water) ?>">
            <?php foreach ($columns as $colKey): ?>
                <input type="hidden" name="columns[]" value="<?= htmlspecialchars($colKey) ?>">
            <?php endforeach; ?>
            <button type="submit" name="download" class="filter-btn download-btn" aria-label="Download as Excel">
                <i class="fa-solid fa-file-excel"></i> Download Excel Report
            </button>
        </form>

        <div class="table-wrapper">
            <table role="table" aria-label="Filtered schools data">
                <thead>
                    <tr>
                        <?php foreach ($baseColumns as $key => $col): ?>
                            <th><i class="fa-solid fa-<?= htmlspecialchars($col['icon']) ?>" title="<?= htmlspecialchars($col['label']) ?>"></i> <?= htmlspecialchars($col['label']) ?></th>
                        <?php endforeach; ?>
                        <?php foreach ($columns as $colKey): 
                            $col = $allColumns[$colKey];
                        ?>
                            <th><i class="fa-solid fa-<?= htmlspecialchars($col['icon']) ?>" title="<?= htmlspecialchars($col['label']) ?>"></i> <?= htmlspecialchars($col['label']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                    <tr>
                        <?php foreach ($baseColumns as $key => $col): 
                            $val = $row[$key] ?? '';
                            $formatter = $col['formatter'];
                            $content = ($formatter === 'htmlspecialchars') ? htmlspecialchars($val) : $formatter($val);
                        ?>
                            <td data-label="<?= htmlspecialchars($col['label']) ?>"><?= $content ?></td>
                        <?php endforeach; ?>
                        <?php foreach ($columns as $colKey): 
                            $val = $row[$colKey] ?? '';
                            $formatter = $allColumns[$colKey]['formatter'];
                            $label = $allColumns[$colKey]['label'];
                            $content = $formatter($val);
                            $badgeClass = (stripos($content, 'yes') !== false) ? 'badge badge-yes' : (stripos($content, 'no') !== false ? 'badge badge-no' : '');
                        ?>
                            <td data-label="<?= htmlspecialchars($label) ?>"><span class="<?= $badgeClass ?>"><?= $content ?></span></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    // Theme Loader
    document.addEventListener('DOMContentLoaded', () => {
        const body = document.body;
        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark');
        }
    });

    // Smooth fade-in for elements
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });

    document.querySelectorAll('.filters, .table-wrapper, .header').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
</script>

</body>
</html>
