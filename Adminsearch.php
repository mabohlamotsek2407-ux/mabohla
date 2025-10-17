<?php
session_start();

// Database connection configuration
$servername = "sql104.infinityfree.com";
$username = "if0_40021406"; 
$password = "Op70TI711cS2lB6";
$dbname = "if0_40021406_moet1";

// Define the missing variables for PDO
$host = $servername;
$user = $username;
$pass = $password;
$db = $dbname;
$charset = 'utf8mb4';

try {
    // Create a new PDO instance with corrected DSN
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Initialize search results
    $searchResults = [];
    $searchQuery = '';

    // Check if a search has been made
    if (isset($_POST['search'])) {
        $searchQuery = trim($_POST['search_query']); // Sanitize input
        if (!empty($searchQuery)) {
            $sqlSearch = "
                SELECT * FROM schools 
                WHERE school_name LIKE :search OR 
                      principal_name LIKE :search OR 
                      principal_surname LIKE :search OR
                      registration_number LIKE :search
                LIMIT 100  -- Prevent overload; adjust as needed
            ";
            $stmtSearch = $pdo->prepare($sqlSearch);
            $stmtSearch->execute(['search' => '%' . $searchQuery . '%']);
            $searchResults = $stmtSearch->fetchAll(PDO::FETCH_ASSOC);
        }
    }

} catch (PDOException $e) {
    $errorMessage = 'Database error: ' . htmlspecialchars($e->getMessage());
} catch (Exception $e) {
    $errorMessage = 'Error: ' . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Search Schools | Ministry of Education</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #1e40af;
            --secondary-blue: #3b82f6;
            --accent-gold: #fbbf24;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: var(--text-dark);
            line-height: 1.6;
            margin: 0;
            min-height: 100vh;
        }

        /* Header Styles - Classic and Professional */
        .header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #1e3a8a 100%);
            color: white;
            padding: 1.5rem 0;
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo img {
            width: 60px;
            height: auto;
            filter: brightness(0) invert(1); /* White version for dark header */
        }

        .header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.025em;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Button Styles - Stunning and Modern */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary {
            background: var(--accent-gold);
            color: var(--primary-blue);
        }

        .btn-primary:hover {
            background: #f59e0b;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
        }

        /* Search Container - Clean and Centered */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .search-section {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }

        .search-form {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 300px;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            background: var(--bg-light);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-icon {
            color: var(--text-light);
            margin-left: -2.5rem;
            pointer-events: none;
        }

        /* Table Styles - Professional and Responsive */
        .table-section {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
        }

        .table-container {
            overflow-x: auto;
            max-height: 70vh;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        thead {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #1e3a8a 100%);
            color: white;
        }

        th {
            padding: 1rem 0.75rem;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.75rem;
            border-bottom: none;
        }

        tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--border-color);
        }

        tbody tr:hover {
            background: rgba(59, 130, 246, 0.05);
            transform: scale(1.002);
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            background: white;
        }

        /* No Data State */
        .no-data {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
            font-style: italic;
        }

        .no-data i {
            font-size: 3rem;
            color: var(--text-light);
            margin-bottom: 1rem;
        }

        /* Error Alert - Subtle */
        .error-alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container,
            .main-container {
                padding: 0 1rem;
            }

            .search-form {
                flex-direction: column;
            }

            .search-input {
                min-width: auto;
                width: 100%;
            }

            .logo-section {
                flex-direction: column;
                text-align: center;
            }

            th, td {
                padding: 0.75rem 0.5rem;
                font-size: 0.8rem;
            }
        }

        /* Logo Integration Note: Ensure 'moet.png' matches the provided MOET logo description (blue pencil, grad cap, "MOET" text, "Quality Education our Commitment" tagline). If needed, replace src with an ASCII or base64 version, but assuming it's hosted. */
    </style>
</head>
<body>
    <!-- Header: Classic Professional Design -->
    <header class="header">
        <div class="header-container">
            <div class="logo-section">
                <div class="logo">
                    <img src="moet.png" alt="MOET Logo - Ministry of Education">
                </div>
                <h1>Admin Dashboard</h1>
            </div>
            <div class="header-actions">
                <a href="Admin.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Error Display (if any) -->
        <?php if (isset($errorMessage)): ?>
            <div class="error-alert">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <!-- Search Section: Stunning Clean Form -->
        <section class="search-section">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Search Schools</h2>
            <form method="POST" class="search-form">
                <div class="relative flex-1">
                    <i class="fas fa-search search-icon"></i>
                    <input 
                        type="text" 
                        name="search_query" 
                        placeholder="Search by school name, registration number, or principal..." 
                        class="search-input pl-10" 
                        value="<?php echo htmlspecialchars($searchQuery); ?>"
                        required
                    >
                </div>
                <button type="submit" name="search" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Search
                </button>
            </form>
        </section>

        <!-- Results Table: Professional Data Display -->
        <section class="table-section">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Reg No</th>
                            <th>Principal Name</th>
                            <th>Principal Surname</th>
                            <th>School Name</th>
                            <th>Cluster</th>
                            <th>Phone Number</th>
                            <th>Email Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($searchResults)): ?>
                            <?php foreach ($searchResults as $school): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($school['registration_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($school['principal_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($school['principal_surname'] ?? 'N/A'); ?></td>
                                    <td><strong><?php echo htmlspecialchars($school['school_name'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($school['cluster'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($school['phone_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($school['email_address'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data">
                                    <i class="fas fa-inbox"></i>
                                    <p>No schools found matching your search. Try adjusting your query.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Footer: Optional Subtle Addition for Professionalism -->
    <footer style="text-align: center; padding: 1rem; background: var(--primary-blue); color: white; margin-top: 2rem;">
        <p>&copy; 2023 Ministry of Education. Quality Education Our Commitment.</p>
    </footer>
</body>
</html>