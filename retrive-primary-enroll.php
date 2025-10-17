<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view this page.");
}

$user_id = $_SESSION['user_id'];

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

// Fetch the school_id for the logged-in user from the schools table via user_id
$stmt = $conn->prepare("SELECT school_id FROM schools WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($school_id);
if (!$stmt->fetch()) {
    die("User 's school not found.");
}
$stmt->close();

// Handle delete request
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Delete only if the record belongs to the user's school
    $stmt = $conn->prepare("DELETE FROM primary_enrollment WHERE P_id = ? AND school_id = ?");
    $stmt->bind_param("ii", $id, $school_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle edit request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['P_id'])) {
    $id = intval($_POST['P_id']);
    // Sanitize and default to 0 if empty
    $female_reception = !empty($_POST['female_reception']) ? intval($_POST['female_reception']) : 0;
    $male_reception = !empty($_POST['male_reception']) ? intval($_POST['male_reception']) : 0;
    $grade1_girls = !empty($_POST['grade1_girls']) ? intval($_POST['grade1_girls']) : 0;
    $grade1_boys = !empty($_POST['grade1_boys']) ? intval($_POST['grade1_boys']) : 0;
    $grade2_girls = !empty($_POST['grade2_girls']) ? intval($_POST['grade2_girls']) : 0;
    $grade2_boys = !empty($_POST['grade2_boys']) ? intval($_POST['grade2_boys']) : 0;
    $grade3_girls = !empty($_POST['grade3_girls']) ? intval($_POST['grade3_girls']) : 0;
    $grade3_boys = !empty($_POST['grade3_boys']) ? intval($_POST['grade3_boys']) : 0;
    $grade4_girls = !empty($_POST['grade4_girls']) ? intval($_POST['grade4_girls']) : 0;
    $grade4_boys = !empty($_POST['grade4_boys']) ? intval($_POST['grade4_boys']) : 0;
    $grade5_girls = !empty($_POST['grade5_girls']) ? intval($_POST['grade5_girls']) : 0;
    $grade5_boys = !empty($_POST['grade5_boys']) ? intval($_POST['grade5_boys']) : 0;
    $grade6_girls = !empty($_POST['grade6_girls']) ? intval($_POST['grade6_girls']) : 0;
    $grade6_boys = !empty($_POST['grade6_boys']) ? intval($_POST['grade6_boys']) : 0;
    $grade7_girls = !empty($_POST['grade7_girls']) ? intval($_POST['grade7_girls']) : 0;
    $grade7_boys = !empty($_POST['grade7_boys']) ? intval($_POST['grade7_boys']) : 0;
    $repeaters_girls = !empty($_POST['repeaters_girls']) ? intval($_POST['repeaters_girls']) : 0;
    $repeaters_boys = !empty($_POST['repeaters_boys']) ? intval($_POST['repeaters_boys']) : 0;

    // Update only if the record belongs to the user's school
    $stmt = $conn->prepare("UPDATE primary_enrollment SET 
        female_reception = ?,
        male_reception = ?,
        grade1_girls = ?,
        grade1_boys = ?,
        grade2_girls = ?,
        grade2_boys = ?,
        grade3_girls = ?,
        grade3_boys = ?,
        grade4_girls = ?,
        grade4_boys = ?,
        grade5_girls = ?,
        grade5_boys = ?,
        grade6_girls = ?,
        grade6_boys = ?,
        grade7_girls = ?,
        grade7_boys = ?,
        repeaters_girls = ?,
        repeaters_boys = ?
        WHERE P_id = ? AND school_id = ?");
    $stmt->bind_param("iiiiiiiiiiiiiiiiiiii", 
        $female_reception,
        $male_reception,
        $grade1_girls,
        $grade1_boys,
        $grade2_girls,
        $grade2_boys,
        $grade3_girls,
        $grade3_boys,
        $grade4_girls,
        $grade4_boys,
        $grade5_girls,
        $grade5_boys,
        $grade6_girls,
        $grade6_boys,
        $grade7_girls,
        $grade7_boys,
        $repeaters_girls,
        $repeaters_boys,
        $id,
        $school_id
    );
    $stmt->execute();
    $stmt->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all data for the user's school only
$stmt = $conn->prepare("SELECT * FROM primary_enrollment WHERE school_id = ?");
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Primary Enrollment Management</title>
    <!-- Google Fonts for Professional Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .table-container {
            overflow-x: auto;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            animation: fadeIn 0.3s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 90%;
            max-width: 900px;
            transform: translateY(-20px);
            animation: slideUp 0.3s ease-out forwards;
        }
        @keyframes slideUp {
            to {
                transform: translateY(0);
            }
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            padding: 2rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #334155;
        }
        .form-group input {
            width: 100%;
           
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-actions {
            text-align: right;
            margin-top: 20px;
        }
        .btn-primary {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3);
        }
        .btn-danger {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.3);
        }
        .table-row:hover {
            background-color: #f0f9ff;
            transform: scale(1.01);
            transition: all 0.2s ease;
        }
        .header-gradient {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
        .empty-state {
            @apply text-center py-12 px-6;
        }
        .empty-state svg {
            @apply w-24 h-24 text-gray-300 mx-auto mb-4;
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
    display: inline-block;
}

.button:hover {
    background-color: #2563eb;
}

.button.delete {
    background-color: #ef4444;
}

.button.delete:hover {
    background-color: #dc2626;
}
        .button {
        padding: 10px 18px;
        font-size: 14px;
        margin: 5px 5px;
    }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="header-gradient text-white rounded-xl p-6 mb-8 shadow-xl flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-4 flex-1">
            <img src="moet.png" alt="Ministry of Education" class="w-16 h-16 rounded-full shadow-lg" />
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold">Primary Enrollment Management</h1>
                <p class="text-blue-100 text-sm sm:text-base">Ministry of Education & Training</p>
            </div>
        </div>
        <button type="button" 
                onclick="window.location.href='back.php'" 
                class="inline-flex items-center justify-center px-6 py-3 bg-white text-gray-900 font-semibold rounded-xl shadow-md hover:shadow-2xl focus:outline-none focus:ring-4 focus:ring-blue-300 focus:ring-offset-2 transition-all duration-300 ease-out transform hover:scale-105 group relative overflow-hidden">
            <!-- Ripple Animation Layer -->
            <span class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-transparent transform -translate-x-full group-hover:translate-x-full transition-transform duration-700 ease-out"></span>
            <i class="fas fa-arrow-left mr-2 text-sm group-hover:mr-1 transition-all duration-300 group-hover:translate-x-[-2px] group-hover:rotate-[-5deg]"></i>
            <span class="relative z-10">Back to Dashboard</span>
        </button>
    </div>

    <div class="max-w-7xl mx-auto">
        <!-- Page Title -->
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-8">Primary Enrollment Data</h2>

        <!-- Add New Button -->
        <div class="text-center mb-8">
            <a href="primaryenrolment.php" class="btn-primary inline-flex items-center px-8 py-3 bg-blue-600 text-white font-semibold rounded-xl shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <i class="fas fa-plus mr-2"></i>
                Add New Enrollment
            </a>
        </div>

        <!-- Data Table -->
        <div class="table-container bg-white rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Female Reception</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Male Reception</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Grade 1 (F/M)</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Grade 2 (F/M)</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Grade 3 (F/M)</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Grade 4 (F/M)</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Grade 5 (F/M)</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Grade 6 (F/M)</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Grade 7 (F/M)</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Repeaters (F/M)</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="table-row hover:bg-blue-50 transition-all duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($row['female_reception']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($row['male_reception']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($row['grade1_girls']) ?> / <?= htmlspecialchars($row['grade1_boys']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($row['grade2_girls']) ?> / <?= htmlspecialchars($row['grade2_boys']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($row['grade3_girls']) ?> / <?= htmlspecialchars($row['grade3_boys']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($row['grade4_girls']) ?> / <?= htmlspecialchars($row['grade4_boys']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($row['grade5_girls']) ?> / <?= htmlspecialchars($row['grade5_boys']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($row['grade6_girls']) ?> / <?= htmlspecialchars($row['grade6_boys']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($row['grade7_girls']) ?> / <?= htmlspecialchars($row['grade7_boys']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($row['repeaters_girls']) ?> / <?= htmlspecialchars($row['repeaters_boys']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="btn-primary inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg mr-2 hover:bg-green-700" onclick='openEditModal(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </button>
                                    <a href="?delete=<?= urlencode($row['P_id']) ?>" class="btn-danger inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700" onclick="return confirm('Are you sure you want to delete this record?');">
                                        <i class="fas fa-trash-alt mr-1"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="empty-state">
                                    <i class="fas fa-users text-6xl mb-4 text-gray-300"></i>
                                    <p class="text-xl text-gray-500 mb-4">No enrollment data found.</p>
                                    <a href="primaryenrolment.php" class="btn-primary inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl shadow-lg hover:bg-blue-700">
                                        <i class="fas fa-plus mr-2"></i>
                                        Add Your First Record
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Enrollment Record</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="editForm" method="POST">
                <input type="hidden" id="edit_P_id" name="P_id" />
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_female_reception">Female Reception</label>
                        <input type="number" id="edit_female_reception" name="female_reception" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_male_reception">Male Reception</label>
                        <input type="number" id="edit_male_reception" name="male_reception" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_grade1_girls">Grade 1 Girls</label>
                        <input type="number" id="edit_grade1_girls" name="grade1_girls" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_grade1_boys">Grade 1 Boys</label>
                        <input type="number" id="edit_grade1_boys" name="grade1_boys" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_grade2_girls">Grade 2 Girls</label>
                        <input type="number" id="edit_grade2_girls" name="grade2_girls" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_grade2_boys">Grade 2 Boys</label>
                        <input type="number" id="edit_grade2_boys" name="grade2_boys" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_grade3_girls">Grade 3 Girls</label>
                        <input type="number" id="edit_grade3_girls" name="grade3_girls" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_grade3_boys">Grade 3 Boys</label>
                        <input type="number" id="edit_grade3_boys" name="grade3_boys" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_grade4_girls">Grade 4 Girls</label>
                        <input type="number" id="edit_grade4_girls" name="grade4_girls" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_grade4_boys">Grade 4 Boys</label>
                        <input type="number" id="edit_grade4_boys" name="grade4_boys" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_grade5_girls">Grade 5 Girls</label>
                        <input type="number" id="edit_grade5_girls" name="grade5_girls" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_grade5_boys">Grade 5 Boys</label>
                        <input type="number" id="edit_grade5_boys" name="grade5_boys" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_grade6_girls">Grade 6 Girls</label>
                        <input type="number" id="edit_grade6_girls" name="grade6_girls" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_grade6_boys">Grade 6 Boys</label>
                        <input type="number" id="edit_grade6_boys" name="grade6_boys" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_grade7_girls">Grade 7 Girls</label>
                        <input type="number" id="edit_grade7_girls" name="grade7_girls" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_grade7_boys">Grade 7 Boys</label>
                        <input type="number" id="edit_grade7_boys" name="grade7_boys" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_repeaters_girls">Repeaters Girls</label>
                        <input type="number" id="edit_repeaters_girls" name="repeaters_girls" min="0" required />
                    </div>
                    <div class="form-group">
                        <label for="edit_repeaters_boys">Repeaters Boys</label>
                        <input type="number" id="edit_repeaters_boys" name="repeaters_boys" min="0" required />
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
            document.getElementById('edit_P_id').value = data.P_id;
            document.getElementById('edit_female_reception').value = data.female_reception;
            document.getElementById('edit_male_reception').value = data.male_reception;
            document.getElementById('edit_grade1_girls').value = data.grade1_girls;
            document.getElementById('edit_grade1_boys').value = data.grade1_boys;
            document.getElementById('edit_grade2_girls').value = data.grade2_girls;
            document.getElementById('edit_grade2_boys').value = data.grade2_boys;
            document.getElementById('edit_grade3_girls').value = data.grade3_girls;
            document.getElementById('edit_grade3_boys').value = data.grade3_boys;
            document.getElementById('edit_grade4_girls').value = data.grade4_girls;
            document.getElementById('edit_grade4_boys').value = data.grade4_boys;
            document.getElementById('edit_grade5_girls').value = data.grade5_girls;
            document.getElementById('edit_grade5_boys').value = data.grade5_boys;
            document.getElementById('edit_grade6_girls').value = data.grade6_girls;
            document.getElementById('edit_grade6_boys').value = data.grade6_boys;
            document.getElementById('edit_grade7_girls').value = data.grade7_girls;
            document.getElementById('edit_grade7_boys').value = data.grade7_boys;
            document.getElementById('edit_repeaters_girls').value = data.repeaters_girls;
            document.getElementById('edit_repeaters_boys').value = data.repeaters_boys;

            document.getElementById('editModal').style.display = "block";
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = "none";
        }

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
