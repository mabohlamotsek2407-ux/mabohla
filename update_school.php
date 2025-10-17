<?php
session_start();

$servername = "sql104.infinityfree.com";
$username = "if0_40021406";
$password = "Op70TI711cS2lB6";
$dbname = "if0_40021406_moet1";
$charset = 'utf8mb4';

try {
    // Create PDO connection once, reuse it
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User not logged in");
    }

    $user_id = $_SESSION['user_id'];

    // Handle POST update
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get the data from the form and sanitize/validate as needed
        $school_id = $_POST['school_id'] ?? null;
        $principal_name = $_POST['principal_name'] ?? '';
        $principal_surname = $_POST['principal_surname'] ?? '';
        $phone_number = $_POST['phone_number'] ?? '';
        $email_address = $_POST['email_address'] ?? '';
        $female_teachers = (int)($_POST['female_teachers'] ?? 0);
        $male_teachers = (int)($_POST['male_teachers'] ?? 0);
        $gender = $_POST['gender'] ?? '';

        if (!$school_id) {
            throw new Exception("School ID is required");
        }

        $total_teachers = $female_teachers + $male_teachers;

        $sql = "UPDATE schools SET 
                    principal_name = :principal_name,
                    principal_surname = :principal_surname,
                    phone_number = :phone_number,
                    email_address = :email_address,
                    female_teachers = :female_teachers,
                    male_teachers = :male_teachers,
                    total_teachers = :total_teachers,
                    gender = :gender
                WHERE school_id = :school_id AND user_id = :user_id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':principal_name', $principal_name);
        $stmt->bindParam(':principal_surname', $principal_surname);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':email_address', $email_address);
        $stmt->bindParam(':female_teachers', $female_teachers, PDO::PARAM_INT);
        $stmt->bindParam(':male_teachers', $male_teachers, PDO::PARAM_INT);
        $stmt->bindParam(':total_teachers', $total_teachers, PDO::PARAM_INT);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':school_id', $school_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirect after successful update
        header("Location: redirectpage.php");
        exit();
    }

    // Retrieve school data for the logged-in user
    $sql = "SELECT * FROM schools WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo '<div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-4 rounded-md" role="alert">';
    echo '<p class="font-medium">Database Error:</p>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
    exit;
} catch (Exception $e) {
    echo '<div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-4 rounded-md" role="alert">';
    echo '<p class="font-medium">Error:</p>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>School Data Management - MOET</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    .gradient-header {
      background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
    }
    .table-row:hover {
      transform: scale(1.01);
      transition: transform 0.2s ease-in-out;
    }
  </style>
</head>
<body class="min-h-screen bg-gray-100">
  <!-- Header with Logo -->
  <header class="gradient-header text-white shadow-lg">
    <div class="container mx-auto px-4 py-4 flex flex-col sm:flex-row justify-between items-center">
      <div class="flex items-center space-x-3 mb-4 sm:mb-0">
        <img src="moet-logo.png" alt="MOET Logo" class="h-12 w-auto" />
        <h1 class="text-2xl sm:text-3xl font-bold">School Data Management</h1>
      </div>
      <div class="flex items-center space-x-4">
        <button 
          onclick="window.location.href='back.php';" 
          class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors duration-200 flex items-center"
        >
          <i class="fas fa-arrow-left mr-2"></i>Back
        </button>
        <div class="flex items-center space-x-2 text-white">
          <i class="fas fa-user-circle text-xl"></i>
          <span class="hidden sm:inline"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></span>
        </div>
      </div>
    </div>
  </header>

  <div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
      <?php
      if (!empty($schools)) {
          foreach ($schools as $index => $school) {
              echo '<div class="mb-8 p-6 border border-blue-200 rounded-lg bg-blue-50">';
              echo '<div class="school-header flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">';
              echo '<h2 class="text-xl font-bold text-blue-800 mb-2 sm:mb-0">';
              echo '<i class="fas fa-school mr-2 text-blue-600"></i>' . htmlspecialchars($school['school_name']);
              echo '</h2>';
              echo '<span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">' . htmlspecialchars($school['registration_number']) . '</span>';
              echo '</div>';

              echo '<div class="overflow-x-auto">';
              echo '<table class="w-full min-w-max">';
              echo '<thead class="bg-blue-800 text-white">';
              echo '<tr>';
              echo '<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Principal Name</th>';
              echo '<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Surname</th>';
              echo '<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Gender</th>';
              echo '<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Phone</th>';
              echo '<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Email</th>';
              echo '<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Total Teachers</th>';
              echo '<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Female</th>';
              echo '<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Male</th>';
              echo '<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Actions</th>';
              echo '</tr>';
              echo '</thead>';
              echo '<tbody class="bg-white divide-y divide-gray-200">';
              echo '<tr class="table-row hover:bg-blue-50">';
              echo '<td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' . htmlspecialchars($school['principal_name']) . '</td>';
              echo '<td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">' . htmlspecialchars($school['principal_surname']) . '</td>';
              echo '<td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">' . htmlspecialchars($school['gender']) . '</td>';
              echo '<td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">' . htmlspecialchars($school['phone_number']) . '</td>';
              echo '<td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">' . htmlspecialchars($school['email_address']) . '</td>';
              echo '<td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-blue-600">' . htmlspecialchars($school['total_teachers']) . '</td>';
              echo '<td class="px-4 py-4 whitespace-nowrap text-sm text-green-600">' . htmlspecialchars($school['female_teachers']) . '</td>';
              echo '<td class="px-4 py-4 whitespace-nowrap text-sm text-blue-600">' . htmlspecialchars($school['male_teachers']) . '</td>';
              echo '<td class="px-4 py-4 whitespace-nowrap text-sm font-medium">';
              echo '<button class="text-blue-600 hover:text-blue-900 mr-3" onclick=\'showEditModal(' . json_encode($school) . ')\' title="Edit">';
              echo '<i class="fas fa-edit"></i>';
              echo '</button>';
              echo '</td>';
              echo '</tr>';
              echo '</tbody>';
              echo '</table>';
              echo '</div>';
              echo '</div>';
          }
      } else {
          echo '<div class="text-center py-12">';
          echo '<i class="fas fa-schools text-6xl text-blue-300 mb-6"></i>';
          echo '<h3 class="text-2xl font-bold text-gray-700 mb-2">No Schools Found</h3>';
          echo '<p class="text-gray-500">No school data available for your account. Add a school to get started.</p>';
          echo '</div>';
      }
      ?>
    </div>
  </div>

  <!-- Edit Modal -->
  <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden transition-opacity duration-300">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg mx-4 transform transition-transform duration-300 scale-100">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-2xl font-bold text-blue-800">
          <i class="fas fa-edit mr-2 text-blue-600"></i>Edit School Details
        </h3>
        <button onclick="hideEditModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <form id="editForm" method="POST" action="" class="space-y-4">
        <input type="hidden" id="edit_school_id" name="school_id" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Principal Name *</label>
            <input type="text" id="edit_principal_name" name="principal_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Principal Surname *</label>
            <input type="text" id="edit_principal_surname" name="principal_surname" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Gender *</label>
            <select id="edit_gender" name="gender" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
              <option value="">Select Gender</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number *</label>
            <input type="tel" id="edit_phone_number" name="phone_number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
            <input type="email" id="edit_email_address" name="email_address" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Female Teachers *</label>
            <input type="number" id="edit_female_teachers" name="female_teachers" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" min="0" required />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Male Teachers *</label>
            <input type="number" id="edit_male_teachers" name="male_teachers" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" min="0" required />
          </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4 border-t border-gray-200">
          <button type="button" onclick="hideEditModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">Cancel</button>
          <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function showEditModal(data) {
      document.getElementById('edit_school_id').value = data.school_id;
      document.getElementById('edit_principal_name').value = data.principal_name || '';
      document.getElementById('edit_principal_surname').value = data.principal_surname || '';
      document.getElementById('edit_gender').value = data.gender || '';
      document.getElementById('edit_phone_number').value = data.phone_number || '';
      document.getElementById('edit_email_address').value = data.email_address || '';
      document.getElementById('edit_female_teachers').value = data.female_teachers || 0;
      document.getElementById('edit_male_teachers').value = data.male_teachers || 0;

      const modal = document.getElementById('editModal');
      modal.classList.remove('hidden');
      modal.classList.add('opacity-100', 'scale-100');
    }

    function hideEditModal() {
      const modal = document.getElementById('editModal');
      modal.classList.add('hidden', 'opacity-0', 'scale-95');
    }

    // Close modal on outside click
    document.getElementById('editModal').addEventListener('click', function(e) {
      if (e.target === this) hideEditModal();
    });
  </script>
</body>
</html>