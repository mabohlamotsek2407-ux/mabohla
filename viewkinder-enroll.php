<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Preschool Enrollment Data</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f3f4f6;
    }
    .table-container {
      max-height: 70vh;
      overflow-y: auto;
    }
    .table-container::-webkit-scrollbar {
      width: 8px;
    }
    .table-container::-webkit-scrollbar-thumb {
      background-color: #3b82f6;
      border-radius: 4px;
    }
    .highlight-row {
      transition: all 0.2s ease;
    }
    .highlight-row:hover {
      background-color: #e0f2fe;
    }
    .action-buttons {
      transition: opacity 0.3s ease;
    }
  </style>
</head>
<body class="min-h-screen bg-gray-50">
  <div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-blue-600">
          <i class="fas fa-child mr-2"></i>Preschool Enrollment Data
        </h1>
        <div class="flex items-center space-x-4">
          <button 
            onclick="window.location.href='kinder-Dash.php';" 
            class="bg-red-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors duration-200"
          >
            <i class="fas fa-arrow-left mr-2"></i>BACK
          </button>
          <button 
            onclick="window.location.href='kinder-enroll.php';" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors duration-200"
          >
            <i class="fas fa-plus mr-2"></i>Add New
          </button>
          <div class="flex items-center space-x-2 text-blue-600">
            <i class="fas fa-user-circle"></i>
            <span><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'User '; ?></span>
          </div>
        </div>
      </div>

      <?php
      session_start();
      
      // Database connection configuration
$servername = "sql104.infinityfree.com"; // Removed trailing space
$username = "if0_40021406"; 
$password = "Op70TI711cS2lB6"; // Add your MySQL password here
$dbname = "if0_40021406_moet1";
      $charset = 'utf8mb4';

      // Handle delete action
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
          try {
              $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
              $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              
              // Check if the record is recent (within 7 days) and belongs to the user's school
              $checkSql = "
                  SELECT pe.id 
                  FROM preschool_enrollment pe
                  JOIN schools s ON pe.school_id = s.school_id
                  WHERE pe.id = :id 
                  AND s.user_id = :user_id
                  AND pe.entry_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
              ";
              
              $checkStmt = $pdo->prepare($checkSql);
              $checkStmt->bindParam(':id', $_POST['delete_id'], PDO::PARAM_INT);
              $checkStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
              $checkStmt->execute();
              
              if ($checkStmt->rowCount() > 0) {
                  $deleteSql = "DELETE FROM preschool_enrollment WHERE id = :id";
                  $deleteStmt = $pdo->prepare($deleteSql);
                  $deleteStmt->bindParam(':id', $_POST['delete_id'], PDO::PARAM_INT);
                  $deleteStmt->execute();
                  
                  echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">';
                  echo '<p>Record deleted successfully.</p>';
                  echo '</div>';
              } else {
                  echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">';
                  echo '<p>Error: You can only delete recent records (within 7 days).</p>';
                  echo '</div>';
              }
          } catch (PDOException $e) {
              echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">';
              echo '<p>Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
              echo '</div>';
          }
      }
      
      try {
          $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
          $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          
          if (!isset($_SESSION['user_id'])) {
              throw new Exception("User  not logged in");
          }
          
          $user_id = $_SESSION['user_id'];
          
          $sql = "
              SELECT pe.*, s.school_name 
              FROM preschool_enrollment pe
              JOIN schools s ON pe.school_id = s.school_id
              WHERE s.user_id = :user_id
              ORDER BY pe.entry_date DESC
          ";
          
          $stmt = $pdo->prepare($sql);
          $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
          $stmt->execute();
          
          $enrollments = $stmt->fetchAll();
          
          if (count($enrollments) > 0) {
              echo '<div class="table-container border rounded-lg overflow-hidden">';
              echo '<table class="w-full">';
              echo '<thead class="bg-blue-50">';
              echo '<tr>';
              echo '<th class="px-6 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">School</th>';
              echo '<th class="px-6 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">Entry Date</th>';
              echo '<th class="px-6 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">Age 3 (Girls/Boys)</th>';
              echo '<th class="px-6 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">Age 4 (Girls/Boys)</th>';
              echo '<th class="px-6 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">Age 5 (Girls/Boys)</th>';
              echo '<th class="px-6 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">Reception</th>';
              echo '<th class="px-6 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">Total</th>';
              echo '<th class="px-6 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">Actions</th>';
              echo '</tr>';
              echo '</thead>';
              echo '<tbody class="divide-y divide-gray-200">';
              
              foreach ($enrollments as $enrollment) {
                  $isRecent = strtotime($enrollment['entry_date']) >= strtotime('-7 days');
                  
                  echo '<tr class="highlight-row hover:bg-blue-50 group">';
                  echo '<td class="px-6 py-4 whitespace-nowrap">' . htmlspecialchars($enrollment['school_name']) . '</td>';
                  echo '<td class="px-6 py-4 whitespace-nowrap">' . date('M d, Y', strtotime($enrollment['entry_date'])) . '</td>';
                  echo '<td class="px-6 py-4 whitespace-nowrap"><span class="text-blue-500 font-medium">' . $enrollment['age3_girls'] . '</span> / <span class="text-blue-500 font-medium">' . $enrollment['age3_boys'] . '</span></td>';
                  echo '<td class="px-6 py-4 whitespace-nowrap"><span class="text-blue-500 font-medium">' . $enrollment['age4_girls'] . '</span> / <span class="text-blue-500 font-medium">' . $enrollment['age4_boys'] . '</span></td>';
                  echo '<td class="px-6 py-4 whitespace-nowrap"><span class="text-blue-500 font-medium">' . $enrollment['age5_girls'] . '</span> / <span class="text-blue-500 font-medium">' . $enrollment['age5_boys'] . '</span></td>';
                  echo '<td class="px-6 py-4 whitespace-nowrap"><span class="text-blue-500 font-medium">' . $enrollment['female_reception'] . '</span> / <span class="text-blue-500 font-medium">' . $enrollment['male_reception'] . '</span></td>';
                  echo '<td class="px-6 py-4 whitespace-nowrap font-semibold text-blue-600">' . $enrollment['overall_total'] . '</td>';
                  
                  // Action buttons (only show for recent entries)
                  echo '<td class="px-6 py-4 whitespace-nowrap">';
                  if ($isRecent) {
                      echo '<div class="flex space-x-2 action-buttons opacity-0 group-hover:opacity-100">';
                      echo '<button onclick="showEditModal(' . htmlspecialchars(json_encode($enrollment)) . ')" class="text-blue-600 hover:text-blue-800">';
                      echo '<i class="fas fa-edit"></i>';
                      echo '</button>';
                      
                      echo '<form method="POST" onsubmit="return confirmDelete()" class="inline">';
                      echo '<input type="hidden" name="delete_id" value="' . $enrollment['id'] . '">';
                      echo '<button type="submit" class="text-red-600 hover:text-red-800">';
                      echo '<i class="fas fa-trash-alt"></i>';
                      echo '</button>';
                      echo '</form>';
                      echo '</div>';
                  } else {
                      echo '<span class="text-gray-400 text-sm">Read-only</span>';
                  }
                  echo '</td>';
                  
                  echo '</tr>';
              }
              
              echo '</tbody>';
              echo '</table>';
              echo '</div>';
          } else {
              echo '<div class="text-center py-8">';
              echo '<i class="fas fa-info-circle text-4xl text-blue-300 mb-4"></i>';
              echo '<p class="text-gray-600">No enrollment data found for your school.</p>';
              echo '</div>';
          }
      } catch (PDOException $e) {
          echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">';
          echo '<p>Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
          echo '</div>';
      } catch (Exception $e) {
          echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">';
          echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
          echo '</div>';
      }
      ?>
    </div>
  </div>

  <!-- Edit Modal -->
  <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold text-blue-600">Edit Enrollment</h3>
        <button onclick="hideEditModal()" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <form id="editForm" method="POST" action="update_enrollment.php">
        <input type="hidden" id="edit_id" name="id">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Age 3 Girls</label>
            <input type="number" id="edit_age3_girls" name="age3_girls" class="w-full px-3 py-2 border rounded-md">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Age 3 Boys</label>
            <input type="number" id="edit_age3_boys" name="age3_boys" class="w-full px-3 py-2 border rounded-md">
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Age 4 Girls</label>
            <input type="number" id="edit_age4_girls" name="age4_girls" class="w-full px-3 py-2 border rounded-md">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Age 4 Boys</label>
            <input type="number" id="edit_age4_boys" name="age4_boys" class="w-full px-3 py-2 border rounded-md">
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Age 5 Girls</label>
            <input type="number" id="edit_age5_girls" name="age5_girls" class="w-full px-3 py-2 border rounded-md">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Age 5 Boys</label>
            <input type="number" id="edit_age5_boys" name="age5_boys" class="w-full px-3 py-2 border rounded-md">
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Female Reception</label>
            <input type="number" id="edit_female_reception" name="female_reception" class="w-full px-3 py-2 border rounded-md">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Male Reception</label>
            <input type="number" id="edit_male_reception" name="male_reception" class="w-full px-3 py-2 border rounded-md">
          </div>
        </div>
        
        <div class="flex justify-end space-x-3 mt-6">
          <button type="button" onclick="hideEditModal()" class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-100">
            Cancel
          </button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold text-red-600">Confirm Deletion</h3>
        <button onclick="hideDeleteModal()" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <p class="mb-6">Are you sure you want to delete this enrollment record? This action cannot be undone.</p>
      <div class="flex justify-end space-x-3">
        <button onclick="hideDeleteModal()" class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-100">
          Cancel
        </button>
        <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700" onclick="confirmDelete()">
          Delete
        </button>
      </div>
    </div>
  </div>

  <script>
    // Modal functions
    function showEditModal(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_age3_girls').value = data.age3_girls;
        document.getElementById('edit_age3_boys').value = data.age3_boys;
        document.getElementById('edit_age4_girls').value = data.age4_girls;
        document.getElementById('edit_age4_boys').value = data.age4_boys;
        document.getElementById('edit_age5_girls').value = data.age5_girls;
        document.getElementById('edit_age5_boys').value = data.age5_boys;
        document.getElementById('edit_female_reception').value = data.female_reception;
        document.getElementById('edit_male_reception').value = data.male_reception;

        document.getElementById('editModal').classList.remove('hidden');
    }

    function hideEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function confirmDelete() {
        // Redirect to delete-kinderenrol.php with the delete ID
        const deleteId = document.querySelector('input[name="delete_id"]').value; // Get the delete ID
        window.location.href = `delete-kinderenrol.php?delete_id=${deleteId}`; // Redirect to the
    }

    function hideDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Attach click handler for delete confirmation
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        confirmDelete();
    });

    // Add interactive features
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.highlight-row');

        // Add animation to table rows
        let delay = 0;
        rows.forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateY(20px)';
            row.style.transition = `all 0.3s ease ${delay}s`;
            delay += 0.05;

            setTimeout(() => {
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, 100);
        });
    });
</script>
</body>
</html>
