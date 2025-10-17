<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view this page.");
}

// Database connection configuration
$servername = "sql104.infinityfree.com"; // Removed trailing space
$username = "if0_40021406"; 
$password = "Op70TI711cS2lB6"; // Add your MySQL password here
$dbname = "if0_40021406_moet1";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM high_school_enrollment WHERE id = $id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle edit request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $female_reception = $_POST['female_reception'] ?: 0;
    $male_reception = $_POST['male_reception'] ?: 0;
    $grade_8_girls = $_POST['grade_8_girls'] ?: 0;
    $grade_8_boys = $_POST['grade_8_boys'] ?: 0;
    $grade_9_girls = $_POST['grade_9_girls'] ?: 0;
    $grade_9_boys = $_POST['grade_9_boys'] ?: 0;
    $grade_10_girls = $_POST['grade_10_girls'] ?: 0;
    $grade_10_boys = $_POST['grade_10_boys'] ?: 0;
    $grade_11_girls = $_POST['grade_11_girls'] ?: 0;
    $grade_11_boys = $_POST['grade_11_boys'] ?: 0;
    $grants_girls = $_POST['grants_girls'] ?: 0;
    $grants_boys = $_POST['grants_boys'] ?: 0;

    $conn->query("UPDATE high_school_enrollment SET 
        female_reception = $female_reception,
        male_reception = $male_reception,
        grade_8_girls = $grade_8_girls,
        grade_8_boys = $grade_8_boys,
        grade_9_girls = $grade_9_girls,
        grade_9_boys = $grade_9_boys,
        grade_10_girls = $grade_10_girls,
        grade_10_boys = $grade_10_boys,
        grade_11_girls = $grade_11_girls,
        grade_11_boys = $grade_11_boys,
        grants_girls = $grants_girls,
        grants_boys = $grants_boys
        WHERE id = $id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all data
$result = $conn->query("SELECT 
    h.*, 
    s.school_name 
FROM high_school_enrollment h
JOIN schools s ON h.school_id = s.school_id
WHERE s.user_id = {$_SESSION['user_id']}
ORDER BY h.entry_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>High School Enrollment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Base Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 20px;
        }

        .header {
            background-color: #3b82f6;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            position: relative;
        }
        
        .header .logo {
            position: absolute;
            left: 20px;
            top: 15px;
        }
        
        h1 {
            margin: 0;
            font-size: 2em;
        }

        .header a {
            background-color: #2563eb;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            position: absolute;
            right: 20px;
            top: 15px;
            transition: background-color 0.3s;
        }

        .header a:hover {
            background-color: #1d4ed8;
        }

        h2 {
            text-align: center;
            color: #1e3a8a;
            margin: 30px 0;
        }

        .button-container {
            text-align: center;
            gap: 25px;
            margin-bottom: 15px;
            margin-top: 20px;
            display: flex;
            padding: auto;
        }

        .button {
            padding: 12px 24px;
            margin: 0 10px;
            border: none;
            border-radius: 6px;
            background-color: #3b82f6;
            color: white;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #2563eb;
        }

        .table-container {
            overflow-x: auto;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
            background-color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        th {
            background-color: #3b82f6;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f8fafc;
        }

        tr:hover {
            background-color: #dbeafe;
        }

        .button.delete {
            background-color: #ef4444;
        }

        .button.delete:hover {
            background-color: #dc2626;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 500px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .modal-header {
            margin-bottom: 20px;
            text-align: center;
        }

        .modal-header h2 {
            margin: 0;
            color: #3b82f6;
        }

        .modal-header .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .modal-header .close:hover {
            color: black;
            cursor: pointer;
        }

        input[type="number"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-actions {
            text-align: center;
        }

        .form-actions .button {
            margin-top: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        img{
            border-radius: 50%;
            width: 50px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="moet.png" alt="Logo" class="logo"> <!-- Adjust the logo path -->
        <h1>High School Enrollment</h1>
        <a href="back.php"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
    
    <div class="button-container">
        <a href="Highenrolment.php" class="button">
            <i class="fas fa-plus"></i> Add New Enrollment
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>School</th>
                    <th>Date</th>
                    <th>Reception (F/M)</th>
                    <th>Grade 8 (F/M)</th>
                    <th>Grade 9 (F/M)</th>
                    <th>Grade 10 (F/M)</th>
                    <th>Grade 11 (F/M)</th>
                    <th>Grants (F/M)</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr id="row-<?= $row['id'] ?>">
                    <td><?= htmlspecialchars($row['school_name']) ?></td>
                    <td><?= date('M j, Y', strtotime($row['entry_date'])) ?></td>
                    <td><?= $row['female_reception'] ?> / <?= $row['male_reception'] ?></td>
                    <td><?= $row['grade_8_girls'] ?> / <?= $row['grade_8_boys'] ?></td>
                    <td><?= $row['grade_9_girls'] ?> / <?= $row['grade_9_boys'] ?></td>
                    <td><?= $row['grade_10_girls'] ?> / <?= $row['grade_10_boys'] ?></td>
                    <td><?= $row['grade_11_girls'] ?> / <?= $row['grade_11_boys'] ?></td>
                    <td><?= $row['grants_girls'] ?> / <?= $row['grants_boys'] ?></td>
                    <td><?= $row['total_students'] ?></td>
                    <td>
                        <?php
                        $currentDate = new DateTime();
                        $entryDate = new DateTime($row['entry_date']);
                        $interval = $currentDate->diff($entryDate);
                        $daysDifference = $interval->days;

                        if ($daysDifference < 7): ?>
                            <button class="button" onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        <?php else: ?>
                            <button class="button locked" disabled>
                                <i class="fas fa-lock"></i> Locked
                            </button>
                        <?php endif; ?>
                        <button class="button delete" onclick="confirmDelete(<?= $row['id'] ?>)">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Enrollment Record</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="editForm" method="POST">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="form-grid">
                    <div>
                        <label for="edit_female_reception">Female Reception</label>
                        <input type="number" id="edit_female_reception" name="female_reception" min="0">
                    </div>
                    <div>
                        <label for="edit_male_reception">Male Reception</label>
                        <input type="number" id="edit_male_reception" name="male_reception" min="0">
                    </div>
                    <div>
                        <label for="edit_grade_8_girls">Grade 8 Girls</label>
                        <input type="number" id="edit_grade_8_girls" name="grade_8_girls" min="0">
                    </div>
                    <div>
                        <label for="edit_grade_8_boys">Grade 8 Boys</label>
                        <input type="number" id="edit_grade_8_boys" name="grade_8_boys" min="0">
                    </div>
                    <div>
                        <label for="edit_grade_9_girls">Grade 9 Girls</label>
                        <input type="number" id="edit_grade_9_girls" name="grade_9_girls" min="0">
                    </div>
                    <div>
                        <label for="edit_grade_9_boys">Grade 9 Boys</label>
                        <input type="number" id="edit_grade_9_boys" name="grade_9_boys" min="0">
                    </div>
                    <div>
                        <label for="edit_grade_10_girls">Grade 10 Girls</label>
                        <input type="number" id="edit_grade_10_girls" name="grade_10_girls" min="0">
                    </div>
                    <div>
                        <label for="edit_grade_10_boys">Grade 10 Boys</label>
                        <input type="number" id="edit_grade_10_boys" name="grade_10_boys" min="0">
                    </div>
                    <div>
                        <label for="edit_grade_11_girls">Grade 11 Girls</label>
                        <input type="number" id="edit_grade_11_girls" name="grade_11_girls" min="0">
                    </div>
                    <div>
                        <label for="edit_grade_11_boys">Grade 11 Boys</label>
                        <input type="number" id="edit_grade_11_boys" name="grade_11_boys" min="0">
                    </div>
                    <div>
                        <label for="edit_grants_girls">Grants Girls</label>
                        <input type="number" id="edit_grants_girls" name="grants_girls" min="0">
                    </div>
                    <div>
                        <label for="edit_grants_boys">Grants Boys</label>
                        <input type="number" id="edit_grants_boys" name="grants_boys" min="0">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="button delete" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="button">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_female_reception').value = data.female_reception;
            document.getElementById('edit_male_reception').value = data.male_reception;
            document.getElementById('edit_grade_8_girls').value = data.grade_8_girls;
            document.getElementById('edit_grade_8_boys').value = data.grade_8_boys;
            document.getElementById('edit_grade_9_girls').value = data.grade_9_girls;
            document.getElementById('edit_grade_9_boys').value = data.grade_9_boys;
            document.getElementById('edit_grade_10_girls').value = data.grade_10_girls;
            document.getElementById('edit_grade_10_boys').value = data.grade_10_boys;
            document.getElementById('edit_grade_11_girls').value = data.grade_11_girls;
            document.getElementById('edit_grade_11_boys').value = data.grade_11_boys;
            document.getElementById('edit_grants_girls').value = data.grants_girls;
            document.getElementById('edit_grants_boys').value = data.grants_boys;

            document.getElementById('editModal').style.display = "block";
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = "none";
        }

        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this record?')) {
                window.location.href = "?delete=" + id;
            }
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
