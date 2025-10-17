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

// Get school information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT school_id, school_name, cluster FROM schools WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($school_id, $school_name, $cluster);
$stmt->fetch();
$stmt->close();

// Set defaults if not found
$school_name = $school_name ?? '';
$cluster = $cluster ?? '';

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Infrastructure Management</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #3498db;
        }
        
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .search-filter {
            display: flex;
            gap: 15px;
        }
        
        input[type="text"], select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        button {
            padding: 8px 16px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #2980b9;
        }
        
        .btn-add {
            background-color: #27ae60;
        }
        
        .btn-add:hover {
            background-color: #219653;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background-color: #f1f5f9;
            font-weight: 600;
            color: #2c3e50;
            position: sticky;
            top: 0;
        }
        
        tr:hover {
            background-color: #f8fafc;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-yes {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-no {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-delete {
            background-color: #dc3545;
        }
        
        .pagination {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            gap: 8px;
        }
        
        .pagination button {
            padding: 6px 12px;
        }
        
        @media (max-width: 768px) {
            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-filter {
                width: 100%;
                flex-direction: column;
            }
            
            th, td {
                padding: 8px 10px;
            }
            
            table {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>School Infrastructure Management</h1>
        
        <div class="header-section">
            <div class="search-filter">
                <input type="text" placeholder="Search infrastructure...">
                <select>
                    <option>All Schools</option>
                    <option>Filter by School</option>
                </select>
                <button>Filter</button>
            </div>
            <button type="submit" class="btn-add" form="infrastructureForm">+ Add New Infrastructure</button>
        </div>
        
        <form id="infrastructureForm" method="post" action="infrastructure.php">
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Infrastructure ID</th>
                        <th>School ID</th>
                        <th>Classrooms</th>
                        <th>Toilets</th>
                        <th>Kitchen</th>
                        <th>Store</th>
                        <th>Staffroom</th>
                        <th>Office</th>
                        <th>Library</th>
                        <th>Laboratory</th>
                        <th>Hall</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="number" name="infrastructure_id" required></td>
                        <td><input type="number" name="school_id" required></td>
                        <td><input type="number" name="classrooms" required></td>
                        <td><input type="number" name="toilets" required></td>
                        <td>
                            <select name="kitchen" required>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </td>
                        <td>
                            <select name="store" required>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </td>
                        <td>
                            <select name="staffroom" required>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </td>
                        <td>
                            <select name="office" required>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </td>
                        <td>
                            <select name="library" required>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </td>
                        <td>
                            <select name="laboratory" required>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </td>
                        <td>
                            <select name="hall" required>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </td>
                        <td><input type="date" name="created_at" required></td>
                        <td class="action-buttons">
                            <button type="submit" class="btn-add">Save</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>102</td>
                        <td>8</td>
                        <td>5</td>
                        <td><span class="badge badge-no">No</span></td>
                        <td><span class="badge badge-yes">Yes</span></td>
                        <td><span class="badge badge-yes">Yes</span></td>
                        <td><span class="badge badge-yes">Yes</span></td>
                        <td><span class="badge badge-no">No</span></td>
                        <td><span class="badge badge-no">No</span></td>
                        <td><span class="badge badge-no">No</span></td>
                        <td>2023-09-22</td>
                        <td class="action-buttons">
                            <button class="btn-edit">Edit</button>
                            <button class="btn-delete">Delete</button>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>103</td>
                        <td>15</td>
                        <td>10</td>
                        <td><span class="badge badge-yes">Yes</span></td>
                        <td><span class="badge badge-yes">Yes</span></td>
                        <td><span class="badge badge-yes">Yes</span></td>
                        <td><span class="badge badge-yes">Yes</span></td>
                        <td><span class="badge badge-yes">Yes</span></td>
                        <td><span class="badge badge-yes">Yes</span></td>
                        <td><span class="badge badge-yes">Yes</span></td>
                        <td>2023-11-05</td>
                        <td class="action-buttons">
                            <button class="btn-edit">Edit</button>
                            <button class="btn-delete">Delete</button>
                        </td>
                    </tr>
                    <!-- Additional rows can be added here -->
                </tbody>
            </table>
        </div>
        
        <div class="pagination">
            <button>Previous</button>
            <button>1</button>
            <button>2</button>
            <button>3</button>
            <button>Next</button>
        </div>
    </div>
</body>
</html>
