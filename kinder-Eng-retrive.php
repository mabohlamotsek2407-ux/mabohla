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
  </style>
</head>
<body class="min-h-screen bg-gray-50">
  <div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-blue-600">
          <i class="fas fa-child mr-2"></i>Preschool Enrollment Data
        </h1>
        <div class="flex items-center space-x-2 text-blue-600">
          <i class="fas fa-user-circle"></i>
          <span><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'User'; ?></span>
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
      
      $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
      $options = [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES => false,
      ];
      
      try {
          $pdo = new PDO($dsn, $user, $pass, $options);
          
          if (!isset($_SESSION['user_id'])) {
              throw new Exception("User not logged in");
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
              echo '<th class="px-6 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">Total</th>';
              echo '</tr>';
              echo '</thead>';
              echo '<tbody class="divide-y divide-gray-200">';
              
              foreach ($enrollments as $enrollment) {
                  echo '<tr class="highlight-row hover:bg-blue-50">';
                  echo '<td class="px-6 py-4 whitespace-nowrap">' . htmlspecialchars($enrollment['school_name']) . '</td>';
                  echo '<td class="px-6 py-4 whitespace-nowrap">' . date('M d, Y', strtotime($enrollment['entry_date'])) . '</td>';
                  echo '<td class="px-6 py-4 whitespace-nowrap"><span class="text-blue-500 font-medium">' . $enrollment['age3_girls'] . '</span> / <span class="text-blue-500 font-medium">' . $enrollment['age3_boys'] . '</span></td>';
                  echo '<td class="px-6 py-4 whitespace-nowrap"><span class="text-blue-500 font-medium">' . $enrollment['age4_girls'] . '</span> / <span class="text-blue-500 font-medium">' . $enrollment['age4_boys'] . '</span></td>';
                  echo '<td class="px-6 py-4 whitespace-nowrap"><span class="text-blue-500 font-medium">' . $enrollment['age5_girls'] . '</span> / <span class="text-blue-500 font-medium">' . $enrollment['age5_boys'] . '</span></td>';
                  echo '<td class="px-6 py-4 whitespace-nowrap font-semibold text-blue-600">' . $enrollment['overall_total'] . '</td>';
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

  <script>
    // Add interactive features
    document.addEventListener('DOMContentLoaded', function() {
      const rows = document.querySelectorAll('.highlight-row');
      
      rows.forEach(row => {
        row.addEventListener('click', function() {
          rows.forEach(r => r.classList.remove('bg-blue-100'));
          this.classList.add('bg-blue-100');
        });
      });
      
      // Add animation to table rows
      let delay = 0;
      const animateRows = () => {
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
      };
      
      animateRows();
    });
  </script>
</body>
</html>
