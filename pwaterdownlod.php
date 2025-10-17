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

// Retrieve water infrastructure data with the specified conditions for primary schools
$sql = "
    SELECT wi.id, s.school_id, s.school_name, s.registration_number, wi.centre, 
           wi.water_source, wi.water_type, wi.distance, wi.challenges, wi.mitigations 
    FROM water_infrastructure wi
    JOIN schools s ON wi.school_id = s.school_id
    WHERE wi.centre IS NOT NULL 
      AND (wi.cluster IS NULL OR wi.cluster = '')  -- Ensure that the school does not have a cluster
      AND wi.id IN (
          SELECT MAX(id) 
          FROM water_infrastructure 
          WHERE centre IS NOT NULL 
          AND (cluster IS NULL OR cluster = '') 
          GROUP BY school_id
      )
    ORDER BY s.school_name
";

$result = $conn->query($sql);

// Check if there are results
if ($result->num_rows > 0) {
    $infrastructureData = [];
    while ($row = $result->fetch_assoc()) {
        // Check if water_type is null or empty and set to "No source"
        $row['water_type'] = empty($row['water_type']) ? 'No source' : $row['water_type'];
        $infrastructureData[] = $row;
    }
} else {
    $infrastructureData = [];
}

// Close the connection
$conn->close();

// Function to download data as Excel
function downloadExcel($data) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"water_infrastructure_data.xls\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Output the column headings
    echo "ID\tRegistration Number\tSchool Name\tCentre\tWater Source\tWater Type\tDistance (km)\tChallenges\tMitigations\n";

    // Output the data
    foreach ($data as $row) {
        echo implode("\t", [
            $row['id'],
            $row['registration_number'],
            $row['school_name'],
            $row['centre'],
            $row['water_source'],
            empty($row['water_type']) ? 'No source' : $row['water_type'], // Handle null water_type
            empty($row['distance']) ? '0' : $row['distance'],
            empty($row['challenges']) ? 'Maintaining hygiene and sanitation' : $row['challenges'],
            $row['mitigations']
        ]) . "\n";
    }
    exit();
}

// Check if download is requested
if (isset($_POST['download'])) {
    downloadExcel($infrastructureData);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Infrastructure Management System - Primary Schools</title>
    <!-- Google Fonts for Professional Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Professional and Stunning CSS Design */
        :root {
            --primary-color: #1e3a8a; /* Deep navy for professionalism */
            --secondary-color: #3b82f6; /* Professional blue accent */
            --accent-color: #10b981; /* Success green */
            --bg-light: #f8fafc; /* Subtle light background */
            --bg-white: #ffffff; /* Clean white */
            --text-primary: #1f2937; /* Dark gray for text */
            --text-secondary: #6b7280; /* Medium gray */
            --border-color: #d1d5db; /* Light borders */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --border-radius: 0.75rem; /* Subtle rounding */
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); /* Subtle professional gradient */
            min-height: 100vh;
            margin: 0;
            padding: 2rem 0;
            color: var(--text-primary);
            line-height: 1.6;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        /* Header Section - Stunning Gradient with Professional Typography */
        .header-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
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
        }

        .header-section h1 i {
            font-size: 2.5rem;
            opacity: 0.9;
        }

        .header-section p {
            font-size: 1.1rem;
            opacity: 0.95;
            max-width: 800px;
            margin: 0 auto;
            font-weight: 400;
        }

        /* Action Bar - Clean and Professional */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color);
        }

        .action-bar form {
            display: inline-block;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            box-shadow: var(--shadow-sm);
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

        /* Table Container - Professional and Responsive */
        .table-container {
            overflow-x: auto;
            max-height: 70vh;
            overflow-y: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
            min-width: 900px; /* Ensure horizontal scroll on small screens */
        }

        thead {
            background: var(--primary-color);
            position: sticky;
            top: 0;
            z-index: 10;
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
            font-size: 0.8rem;
        }

        th i {
            margin-right: 0.5rem;
            opacity: 0.9;
        }

        tbody tr {
            transition: var(--transition);
            border-bottom: 1px solid var(--border-color);
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tbody tr:hover {
            background-color: #eff6ff;
            transform: scale(1.005);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
        }

        td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            color: var(--text-primary);
            word-wrap: break-word;
        }

        /* Status Badges for Professional Look */
        .status-no-source {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* No Data Message */
        .no-data {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-secondary);
            font-size: 1.1rem;
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 1rem 0;
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

            .action-bar {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .table-container {
                font-size: 0.85rem;
            }

            th, td {
                padding: 0.75rem 0.5rem;
            }
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

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .action-bar, .header-section {
                display: none;
            }

            table {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header Section -->
        <div class="header-section">
            <h1>
                <i class="fas fa-tint"></i>
                Water Infrastructure Management System
            </h1>
            <p>View and manage the most recent water infrastructure data for primary schools.</p>
        </div>

        <!-- Action Bar -->
        <div class="action-bar">
            <form method="post" style="display: inline-block;">
                <button type="submit" name="download" class="btn btn-primary">
                    <i class="fas fa-file-excel"></i>
                    Export to Excel
                </button>
            </form>
            <a href="Adminlinks.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back 
            </a>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-id-badge"></i> Registration Number</th>
                        <th><i class="fas fa-school"></i> School Name</th>
                        <th><i class="fas fa-building"></i> Centre</th>
                        <th><i class="fas fa-water"></i> Water Source</th>
                        <th><i class="fas fa-droplet"></i> Water Type</th>
                        <th><i class="fas fa-ruler"></i> Distance (km)</th>
                        <th><i class="fas fa-exclamation-triangle"></i> Challenges</th>
                        <th><i class="fas fa-check-circle"></i> Mitigations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($infrastructureData)): ?>
                        <?php foreach ($infrastructureData as $infrastructure): ?>
                        <tr>
                            <td><?= htmlspecialchars($infrastructure['registration_number'] ?? '') ?></td>
                            <td><?= htmlspecialchars($infrastructure['school_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($infrastructure['centre'] ?? '') ?></td>
                            <td><?= htmlspecialchars($infrastructure['water_source'] ?? '') ?></td>
                            <td>
                                <?php if ($infrastructure['water_type'] === 'No source'): ?>
                                    <span class="status-no-source">No Source</span>
                                <?php else: ?>
                                    <?= htmlspecialchars($infrastructure['water_type']) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($infrastructure['distance'] ?? '0') ?> km</td>
                            <td><?= htmlspecialchars($infrastructure['challenges'] ?? 'Maintaining hygiene and sanitation') ?></td>
                            <td><?= htmlspecialchars($infrastructure['mitigations'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-data">
                                <i class="fas fa-inbox"></i>
                                <div>No water infrastructure records found matching the criteria.</div>
                                <small>Please check the filters or data availability.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>