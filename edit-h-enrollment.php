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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Retrieve the school ID for the logged-in user
$schoolSql = "SELECT school_id FROM schools WHERE user_id = ?";
$schoolStmt = $conn->prepare($schoolSql);
$schoolStmt->bind_param("i", $user_id);
$schoolStmt->execute();
$schoolResult = $schoolStmt->get_result();

if ($schoolResult->num_rows > 0) {
    $school = $schoolResult->fetch_assoc();
    $school_id = $school['school_id'];

    // Retrieve recent enrollment data for the user's school
    $enrollmentSql = "SELECT * FROM high_school_enrollment WHERE school_id = ? ORDER BY entry_date DESC LIMIT 10";
    $enrollmentStmt = $conn->prepare($enrollmentSql);
    $enrollmentStmt->bind_param("i", $school_id);
    $enrollmentStmt->execute();
    $enrollmentResult = $enrollmentStmt->get_result();
    $enrollmentData = $enrollmentResult->fetch_all(MYSQLI_ASSOC);
} else {
    $enrollmentData = []; // No school found for the user
}

$schoolStmt->close();
$enrollmentStmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>High School Enrollment Data</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .controls {
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background: #229954;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 12px;
        }

        .table-container {
            overflow-x: auto;
            max-height: 70vh;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            min-width: 1200px;
        }

        thead {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            color: white;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        th {
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        tbody tr {
            transition: all 0.3s ease;
        }

        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        tbody tr:hover {
            background: #e3f2fd;
            transform: scale(1.01);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            border-right: 1px solid #e9ecef;
            vertical-align: middle;
            position: relative;
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .edit-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
            display: none;
        }

        .display-value {
            display: block;
        }

        .editing-mode .edit-input {
            display: block;
        }

        .editing-mode .display-value {
            display: none;
        }

        .actions-cell {
            white-space: nowrap;
        }

        .form-control {
            margin-bottom: 10px;
        }

        .form-control label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        .form-control input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-control input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .status-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            background: #27ae60;
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
        }

        .status-message.show {
            opacity: 1;
            transform: translateY(0);
        }

        .status-message.error {
            background: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-graduation-cap"></i> High School Enrollment Data</h1>
            <p>Manage and edit recent high school enrollment information</p>
        </div>

        <div class="controls">
            <a href="Highschool-Dash.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <div class="flex gap-2">
                <button class="btn btn-success" onclick="enableAllEdit()">
                    <i class="fas fa-edit"></i> Edit All
                </button>
                <button class="btn btn-primary" onclick="saveAll()">
                    <i class="fas fa-save"></i> Save All
                </button>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Female Reception</th>
                        <th>Male Reception</th>
                        <th>Reception Total</th>
                        <th>Grade 8 Girls</th>
                        <th>Grade 8 Boys</th>
                        <th>Grade 8 Total</th>
                        <th>Grade 9 Girls</th>
                        <th>Grade 9 Boys</th>
                        <th>Grade 9 Total</th>
                        <th>Grade 10 Girls</th>
                        <th>Grade 10 Boys</th>
                        <th>Grade 10 Total</th>
                        <th>Grade 11 Girls</th>
                        <th>Grade 11 Boys</th>
                        <th>Grade 11 Total</th>
                        <th>Grants Girls</th>
                        <th>Grants Boys</th>
                        <th>Grants Total</th>
                        <th>Total Students</th>
                        <th>Entry Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($enrollmentData)): ?>
                        <?php foreach ($enrollmentData as $enrollment): ?>
                            <tr id="row-<?php echo $enrollment['id']; ?>" class="data-row">
                                <!-- ID (Non-editable) -->
                                <td>
                                    <span class="display-value"><?php echo htmlspecialchars($enrollment['id']); ?></span>
                                </td>
                                
                                <!-- Female Reception -->
                                <td>
                                    <span class="display-value" data-field="female_reception"><?php echo htmlspecialchars($enrollment['female_reception']); ?></span>
                                    <input type="number" class="edit-input" data-field="female_reception" 
                                           value="<?php echo htmlspecialchars($enrollment['female_reception']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Male Reception -->
                                <td>
                                    <span class="display-value" data-field="male_reception"><?php echo htmlspecialchars($enrollment['male_reception']); ?></span>
                                    <input type="number" class="edit-input" data-field="male_reception" 
                                           value="<?php echo htmlspecialchars($enrollment['male_reception']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Reception Total (Calculated) -->
                                <td>
                                    <span class="display-value" data-field="reception_total"><?php echo htmlspecialchars($enrollment['reception_total']); ?></span>
                                    <input type="number" class="edit-input" data-field="reception_total" 
                                           value="<?php echo htmlspecialchars($enrollment['reception_total']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Repeat the same pattern for all other columns -->
                                <!-- Grade 8 Girls -->
                                <td>
                                    <span class="display-value" data-field="grade_8_girls"><?php echo htmlspecialchars($enrollment['grade_8_girls']); ?></span>
                                    <input type="number" class="edit-input" data-field="grade_8_girls" 
                                           value="<?php echo htmlspecialchars($enrollment['grade_8_girls']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Grade 8 Boys -->
                                <td>
                                    <span class="display-value" data-field="grade_8_boys"><?php echo htmlspecialchars($enrollment['grade_8_boys']); ?></span>
                                    <input type="number" class="edit-input" data-field="grade_8_boys" 
                                           value="<?php echo htmlspecialchars($enrollment['grade_8_boys']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Grade 8 Total -->
                                <td>
                                    <span class="display-value" data-field="grade_8_total"><?php echo htmlspecialchars($enrollment['grade_8_total']); ?></span>
                                    <input type="number" class="edit-input" data-field="grade_8_total" 
                                           value="<?php echo htmlspecialchars($enrollment['grade_8_total']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Continue the pattern for other grades... -->
                                <!-- Grade 9 Girls -->
                                <td>
                                    <span class="display-value" data-field="grade_9_girls"><?php echo htmlspecialchars($enrollment['grade_9_girls']); ?></span>
                                    <input type="number" class="edit-input" data-field="grade_9_girls" 
                                           value="<?php echo htmlspecialchars($enrollment['grade_9_girls']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Grade 9 Boys -->
                                <td>
                                    <span class="display-value" data-field="grade_9_boys"><?php echo htmlspecialchars($enrollment['grade_9_boys']); ?></span>
                                    <input type="number" class="edit-input" data-field="grade_9_boys" 
                                           value="<?php echo htmlspecialchars($enrollment['grade_9_boys']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Grade 9 Total -->
                                <td>
                                    <span class="display-value" data-field="grade_9_total"><?php echo htmlspecialchars($enrollment['grade_9_total']); ?></span>
                                    <input type="number" class="edit-input" data-field="grade_9_total" 
                                           value="<?php echo htmlspecialchars($enrollment['grade_9_total']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Grade 10 Girls -->
                                <td>
                                    <span class="display-value" data-field="grade_10_girls"><?php echo htmlspecialchars($enrollment['grade_10_girls']); ?></span>
                                    <input type="number" class="edit-input" data-field="grade_10_girls" 
                                           value="<?php echo htmlspecialchars($enrollment['grade_10_girls']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Grade 10 Boys -->
                                <td>
                                    <span class="display-value" data-field="grade_10_boys"><?php echo htmlspecialchars($enrollment['grade_10_boys']); ?></span>
                                    <input type="number" class="edit-input" data-field="grade_10_boys" 
                                           value="<?php echo htmlspecialchars($enrollment['grade_10_boys']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Grade 10 Total -->
                                <td>
                                    <span class="display-value" data-field="grade_10_total"><?php echo htmlspecialchars($enrollment['grade_10_total']); ?></span>
                                    <input type="number" class="edit-input" data-field="grade_10_total" 
                                           value="<?php echo htmlspecialchars($enrollment['grade_10_total']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Grade 11 Girls -->
                                <td>
                                    <span class="display-value" data-field="grade_11_girls"><?php echo htmlspecialchars($enrollment['grade_11_girls']); ?></span>
                                    <input type="number" class="edit-input" data-field="grade_11_girls" 
                                           value="<?php echo htmlspecialchars($enrollment['grade_11_girls']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Grade 11 Boys -->
                                <td>
                                    <span class="display-value" data-field="grade_11_boys"><?php echo htmlspecialchars($enrollment['grade_11_boys']); ?></span>
                                    <input type="number" class="edit-input" data-field="grade_11_boys" 
                                           value="<?php echo htmlspecialchars($enrollment['grade_11_boys']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Grade 11 Total -->
                                <td>
                                    <span class="display-value" data-field="grade_11_total"><?php echo htmlspecialchars($enrollment['grade_11_total']); ?></span>
                                    <input type="number" class="edit-input" data-field="grade_11_total" 
                                           value="<?php echo htmlspecialchars($enrollment['grade_11_total']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Grants Girls -->
                                <td>
                                    <span class="display-value" data-field="grants_girls"><?php echo htmlspecialchars($enrollment['grants_girls']); ?></span>
                                    <input type="number" class="edit-input" data-field="grants_girls" 
                                           value="<?php echo htmlspecialchars($enrollment['grants_girls']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Grants Boys -->
                                <td>
                                    <span class="display-value" data-field="grants_boys"><?php echo htmlspecialchars($enrollment['grants_boys']); ?></span>
                                    <input type="number" class="edit-input" data-field="grants_boys" 
                                           value="<?php echo htmlspecialchars($enrollment['grants_boys']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Grants Total -->
                                <td>
                                    <span class="display-value" data-field="grants_total"><?php echo htmlspecialchars($enrollment['grants_total']); ?></span>
                                    <input type="number" class="edit-input" data-field="grants_total" 
                                           value="<?php echo htmlspecialchars($enrollment['grants_total']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Total Students -->
                                <td>
                                    <span class="display-value" data-field="total_students"><?php echo htmlspecialchars($enrollment['total_students']); ?></span>
                                    <input type="number" class="edit-input" data-field="total_students" 
                                           value="<?php echo htmlspecialchars($enrollment['total_students']); ?>" 
                                           min="0" step="1">
                                </td>
                                
                                <!-- Entry Date -->
                                <td>
                                    <span class="display-value"><?php echo htmlspecialchars($enrollment['entry_date']); ?></span>
                                </td>
                                
                                <!-- Actions -->
                                <td class="actions-cell">
                                    <button class="btn btn-primary btn-sm edit-btn" onclick="editRow(<?php echo $enrollment['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-success btn-sm save-btn" style="display:none;" onclick="saveRow(<?php echo $enrollment['id']; ?>)">
                                        <i class="fas fa-save"></i> Save
                                    </button>
                                    <button class="btn btn-danger btn-sm cancel-btn" style="display:none;" onclick="cancelEdit(<?php echo $enrollment['id']; ?>)">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="23" class="no-data">
                                <i class="fas fa-database" style="font-size: 3rem; margin-bottom: 20px;"></i>
                                <h3>No enrollment data available</h3>
                                <p>Start by adding some high school enrollment records</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="statusMessage" class="status-message"></div>

    <script>
        let originalData = {};

        function editRow(id) {
            const row = document.getElementById('row-' + id);
            row.classList.add('editing-mode');
            row.querySelector('.edit-btn').style.display = 'none';
            row.querySelector('.save-btn').style.display = 'inline-block';
            row.querySelector('.cancel-btn').style.display = 'inline-block';
            
            // Store original values
            originalData[id] = {};
            const inputs = row.querySelectorAll('.edit-input');
            inputs.forEach(input => {
                originalData[id][input.dataset.field] = input.value;
            });
        }

        function cancelEdit(id) {
            const row = document.getElementById('row-' + id);
            row.classList.remove('editing-mode');
            row.querySelector('.edit-btn').style.display = 'inline-block';
            row.querySelector('.save-btn').style.display = 'none';
            row.querySelector('.cancel-btn').style.display = 'none';
            
            // Restore original values
            const inputs = row.querySelectorAll('.edit-input');
            inputs.forEach(input => {
                input.value = originalData[id][input.dataset.field];
            });
            
            delete originalData[id];
        }

        function saveRow(id) {
            const row = document.getElementById('row-' + id);
            const inputs = row.querySelectorAll('.edit-input');
            const data = { id: id };

            inputs.forEach(input => {
                data[input.dataset.field] = input.value;
            });

            // Send the data to the server using AJAX
            fetch('edit-h-enrollment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Update display values
                    const displays = row.querySelectorAll('.display-value');
                    displays.forEach(display => {
                        const field = display.dataset.field;
                        if (field && result.data && result.data[field]) {
                            display.textContent = result.data[field];
                        }
                    });
                    
                    row.classList.remove('editing-mode');
                    row.querySelector('.edit-btn').style.display = 'inline-block';
                    row.querySelector('.save-btn').style.display = 'none';
                    row.querySelector('.cancel-btn').style.display = 'none';
                    
                    showStatus('Data saved successfully!', false);
                } else {
                    showStatus('Error: ' + result.message, true);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showStatus('Network error occurred. Please try again.', true);
            });
        }

        function enableAllEdit() {
            const rows = document.querySelectorAll('.data-row');
            rows.forEach(row => {
                const id = row.id.replace('row-', '');
                editRow(id);
            });
        }

        function saveAll() {
            const rows = document.querySelectorAll('.data-row.editing-mode');
            if (rows.length === 0) {
                showStatus('No rows in edit mode to save.', true);
                return;
            }
            
            rows.forEach(row => {
                const id = row.id.replace('row-', '');
                saveRow(id);
            });
        }

        function showStatus(message, isError) {
            const statusEl = document.getElementById('statusMessage');
            statusEl.textContent = message;
            statusEl.className = 'status-message ' + (isError ? 'error show' : 'show');
            
            setTimeout(() => {
                statusEl.className = 'status-message';
            }, 3000);
        }

        // Add real-time calculation for totals
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('edit-input')) {
                const field = e.target.dataset.field;
                const row = e.target.closest('tr');
                
                if (field.includes('female_reception') || field.includes('male_reception')) {
                    const female = parseInt(row.querySelector('[data-field="female_reception"]').value) || 0;
                    const male = parseInt(row.querySelector('[data-field="male_reception"]').value) || 0;
                    row.querySelector('[data-field="reception_total"]').value = female + male;
                }
                
                // Add similar logic for other grade totals if needed
            }
        });
    </script>
</body>
</html>

