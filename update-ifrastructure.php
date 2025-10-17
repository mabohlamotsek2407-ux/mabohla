<?php
session_start(); // Start the session

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
    die("You must be logged in to view this page.");
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Fetch the most recent infrastructure data for the user's school
$sql = "SELECT * FROM infrastructure WHERE school_id = (SELECT school_id FROM schools WHERE user_id = ?) ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $infrastructure_data = $result->fetch_assoc(); // Fetch the most recent infrastructure data
} else {
    echo "<script>
        alert('No infrastructure data found for this user.');
        window.location.href = 'back.php';
    </script>";
    exit();
}

// Handle form submission for editing data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $classrooms = $_POST['classrooms'];
    $toilets = $_POST['toilets'];
    $kitchen = $_POST['kitchen'];
    $store = $_POST['store'];
    $staffroom = $_POST['staffroom'];
    $office = $_POST['office'];
    $library = $_POST['library'];
    $laboratory = $_POST['laboratory'];
    $hall = $_POST['hall'];
    $playgrounds = $_POST['playgrounds'];

    // Update the database with the new data
    $update_sql = "UPDATE infrastructure SET classrooms = ?, toilets = ?, kitchen = ?, store = ?, staffroom = ?, office = ?, library = ?, laboratory = ?, hall = ?, playgrounds = ? WHERE infrastructure_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("iissssssssi", $classrooms, $toilets, $kitchen, $store, $staffroom, $office, $library, $laboratory, $hall, $playgrounds, $infrastructure_data['infrastructure_id']);
    
    if ($update_stmt->execute()) {
        // Redirect to the same page to see updated data and hide the form again
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error updating data: " . $conn->error;
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>View and Edit Infrastructure Data</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet" />
  <style>
    /* Reset some default styles */
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f3f4f6; /* light gray */
      color: #1f2937; /* dark slate */
      margin: 0;
      padding: 0;
      line-height: 1.6;
    }

    .container {
      max-width: 900px;
      margin: 40px auto;
      background-color: #ffffff;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    h2, h3 {
      color: #111827; /* darker text */
      margin-bottom: 20px;
    }

    h2 {
      font-size: 2rem;
      font-weight: 700;
      border-bottom: 3px solid #2563eb; /* blue underline */
      padding-bottom: 8px;
    }

    h3 {
      font-size: 1.5rem;
      font-weight: 600;
      margin-top: 30px;
      margin-bottom: 15px;
      border-bottom: 2px solid #3b82f6; /* lighter blue underline */
      padding-bottom: 6px;
    }

    p {
      font-size: 1rem;
      margin: 8px 0;
    }

    strong {
      color: #2563eb; /* blue accent */
    }

    form {
      margin-top: 20px;
    }

    label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
      color: #374151; /* medium dark */
    }

    input[type="number"],
    input[type="text"],
    select {
      width: 100%;
      padding: 10px 14px;
      border: 1.5px solid #d1d5db; /* light gray border */
      border-radius: 8px;
      font-size: 1rem;
      color: #111827;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    input[type="number"]:focus,
    input[type="text"]:focus,
    select:focus {
      border-color: #2563eb;
      outline: none;
      box-shadow: 0 0 8px rgba(37, 99, 235, 0.4);
    }

    .mb-4 {
      margin-bottom: 1.5rem;
    }

    button {
      background-color: #2563eb;
      color: #ffffff;
      font-weight: 700;
      padding: 12px 28px;
      border: none;
      border-radius: 8px;
      font-size: 1.1rem;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }

    button:hover,
    button:focus {
      background-color: #1e40af;
      box-shadow: 0 6px 16px rgba(30, 64, 175, 0.6);
      outline: none;
    }

    .flex {
      display: flex;
    }

    .justify-center {
      justify-content: center;
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
      .container {
        padding: 20px 25px;
        margin: 20px auto;
      }

      h2 {
        font-size: 1.5rem;
      }

      h3 {
        font-size: 1.25rem;
      }

      input[type="number"],
      input[type="text"],
      select {
        font-size: 0.9rem;
        padding: 8px 12px;
      }

      button {
        width: 100%;
        font-size: 1rem;
        padding: 12px 0;
      }
    }

    /* Hide edit form initially */
    #editForm {
      display: none;
    }

    /* Back button styling */
    .back-button {
      display: inline-block;
      margin-bottom: 20px;
      background-color: #6b7280; /* gray-500 */
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      box-shadow: 0 4px 8px rgba(107, 114, 128, 0.4);
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }

    .back-button:hover,
    .back-button:focus {
      background-color: #4b5563; /* gray-700 */
      box-shadow: 0 6px 12px rgba(75, 85, 99, 0.6);
      outline: none;
    }
  </style>
  <script>
    function toggleEditForm() {
      const form = document.getElementById('editForm');
      const btn = document.getElementById('editBtn');
      if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        btn.textContent = 'Cancel Edit';
      } else {
        form.style.display = 'none';
        btn.textContent = 'Edit';
      }
    }
  </script>
</head>
<body>
  <div class="container mx-auto mt-5">
    <!-- Back Button -->
    <a href="redirectpage.php" class="back-button" aria-label="Go back to previous page">&larr; Back</a>

    <h2>Infrastructure Data</h2>
    <div class="mb-4">
      <h3>Current Infrastructure Data</h3>
      <p><strong>Classrooms:</strong> <?php echo htmlspecialchars($infrastructure_data['classrooms']); ?></p>
      <p><strong>Toilets:</strong> <?php echo htmlspecialchars($infrastructure_data['toilets']); ?></p>
      <p><strong>Kitchen:</strong> <?php echo htmlspecialchars($infrastructure_data['kitchen']); ?></p>
      <p><strong>Store:</strong> <?php echo htmlspecialchars($infrastructure_data['store']); ?></p>
      <p><strong>Staffroom:</strong> <?php echo htmlspecialchars($infrastructure_data['staffroom']); ?></p>
      <p><strong>Office:</strong> <?php echo htmlspecialchars($infrastructure_data['office']); ?></p>
      <p><strong>Library:</strong> <?php echo htmlspecialchars($infrastructure_data['library']); ?></p>
      <p><strong>Laboratory:</strong> <?php echo htmlspecialchars($infrastructure_data['laboratory']); ?></p>
      <p><strong>Hall:</strong> <?php echo htmlspecialchars($infrastructure_data['hall']); ?></p>
      <p><strong>Playgrounds:</strong> <?php echo htmlspecialchars($infrastructure_data['playgrounds']); ?></p>
    </div>

    <div class="flex justify-center mb-6">
      <button id="editBtn" onclick="toggleEditForm()" class="bg-blue-600 text-white font-bold py-2 px-6 rounded hover:bg-blue-700">
        Edit
      </button>
    </div>

    <form id="editForm" action="" method="POST" novalidate>
      <h3>Edit Recent Infrastructure Data</h3>
      <div class="mb-4">
        <label for="classrooms">Classrooms</label>
        <input type="number" id="classrooms" name="classrooms" value="<?php echo htmlspecialchars($infrastructure_data['classrooms']); ?>" required />
      </div>
      <div class="mb-4">
        <label for="toilets">Toilets</label>
        <input type="number" id="toilets" name="toilets" value="<?php echo htmlspecialchars($infrastructure_data['toilets']); ?>" required />
      </div>
      <div class="mb-4">
        <label for="kitchen">Kitchen</label>
        <select id="kitchen" name="kitchen" required>
          <option value="Yes" <?php echo ($infrastructure_data['kitchen'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
          <option value="No" <?php echo ($infrastructure_data['kitchen'] == 'No') ? 'selected' : ''; ?>>No</option>
        </select>
      </div>
      <div class="mb-4">
        <label for="store">Store</label>
        <select id="store" name="store" required>
          <option value="Yes" <?php echo ($infrastructure_data['store'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
          <option value="No" <?php echo ($infrastructure_data['store'] == 'No') ? 'selected' : ''; ?>>No</option>
        </select>
      </div>
      <div class="mb-4">
        <label for="staffroom">Staffroom</label>
        <select id="staffroom" name="staffroom" required>
          <option value="Yes" <?php echo ($infrastructure_data['staffroom'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
          <option value="No" <?php echo ($infrastructure_data['staffroom'] == 'No') ? 'selected' : ''; ?>>No</option>
        </select>
      </div>
      <div class="mb-4">
        <label for="office">Office</label>
        <input type="number" id="office" name="office" value="<?php echo htmlspecialchars($infrastructure_data['office']); ?>" required />
      </div>
      <div class="mb-4">
        <label for="library">Library</label>
        <input type="number" id="library" name="library" value="<?php echo htmlspecialchars($infrastructure_data['library']); ?>" required />
      </div>
      <div class="mb-4">
        <label for="laboratory">Laboratory</label>
        <input type="number" id="laboratory" name="laboratory" value="<?php echo htmlspecialchars($infrastructure_data['laboratory']); ?>" required />
      </div>
      <div class="mb-4">
        <label for="hall">Hall</label>
        <input type="number" id="hall" name="hall" value="<?php echo htmlspecialchars($infrastructure_data['hall']); ?>" required />
      </div>
      <div class="mb-4">
        <label for="playgrounds">Playgrounds</label>
        <input type="text" id="playgrounds" name="playgrounds" value="<?php echo htmlspecialchars($infrastructure_data['playgrounds']); ?>" />
      </div>
      <div class="flex justify-center">
        <button type="submit">Update</button>
      </div>
    </form>
  </div>
</body>
</html>
