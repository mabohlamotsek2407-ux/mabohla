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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $cluster = $_POST['cluster'];
    $hasInternet = $_POST['hasInternet'];
    $source = isset($_POST['source']) ? $_POST['source'] : null;
    $reliableNetwork = isset($_POST['reliableNetwork']) ? $_POST['reliableNetwork'] : null;
    $challenges = isset($_POST['challenges']) ? $_POST['challenges'] : null;
    $mitigations = isset($_POST['mitigations']) ? $_POST['mitigations'] : null;
    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO internet_infrastructure (school_id, cluster, has_internet, source, reliable_network, challenges, mitigations) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $school_id, $cluster, $hasInternet, $source, $reliableNetwork, $challenges, $mitigations);
    // Execute the statement
    if ($stmt->execute()) {
        echo "New record created successfully";
        // Data inserted successfully, redirect to update_status.php
        header("Location: redirectpage.php");

    } else {
        echo "Error: " . $stmt->error;
    }
    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Infrastructure Data Collection</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .conditional-field {
            display: none;
        }
        .conditional-field.show {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 py-4 px-6">
                <h1 class="text-2xl font-bold text-white">School Internet Infrastructure Assessment</h1>
                <p class="text-blue-100">Data Collection Form for Offline Use</p>
            </div>

            <form id="infrastructureForm" class="p-6 space-y-6" method="POST" action="without-Internet.php">
                <div class="grid grid-cols-1 gap-6">

                    <div class="form-group">
                        <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>" readonly
                               class="w-auto px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <label for="school">School Name</label>
                        <input type="text" id="school_name" name="school_name" value="<?php echo htmlspecialchars($school_name); ?>" readonly
                               class="w-auto px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="cluster" class="block text-sm font-medium text-gray-700">CLUSTER</label>
                        <input type="text" id="cluster" name="cluster" value="<?php echo htmlspecialchars($cluster); ?>" readonly
                               class="w-auto px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <fieldset>
                            <legend class="text-sm font-medium text-gray-700">DOES THE SCHOOL HAVE INTERNET?</legend>
                            <div class="mt-2 space-y-2">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="hasInternet" value="yes" class="h-4 w-4 text-blue-600 focus:ring-blue-500" onclick="toggleInternetFields(true)">
                                    <span class="ml-2">YES</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="hasInternet" value="no" class="h-4 w-4 text-blue-600 focus:ring-blue-500" onclick="toggleInternetFields(false)">
                                    <span class="ml-2">NO</span>
                                </label>
                            </div>
                        </fieldset>
                    </div>

                    <div id="internetSourceField" class="conditional-field">
                        <label for="source" class="block text-sm font-medium text-gray-700">IF YES, STATE SOURCE?</label>
                        <input type="text" id="source" name="source" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                    </div>

                    <div id="reliableNetworkField" class="conditional-field">
                        <fieldset>
                            <legend class="text-sm font-medium text-gray-700">IF NO, WHICH NETWORK IS RELIABLE AND STABLE?</legend>
                            <div class="mt-2 space-y-2">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="reliableNetwork" value="EEC" class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2">EEC</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="reliableNetwork" value="VCL" class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2">VCL</span>
                                </label>
                            </div>
                        </fieldset>
                    </div>

                    <div>
                        <label for="challenges" class="block text-sm font-medium text-gray-700">ANY CHALLENGES WITH THE SOURCE</label>
                        <textarea id="challenges" name="challenges" rows="3" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border"></textarea>
                    </div>

                    <div>
                        <label for="mitigations" class="block text-sm font-medium text-gray-700">MITIGATIONS</label>
                        <textarea id="mitigations" name="mitigations" rows="3" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border"></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="window.location.href='back.php'" 
        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
    Back
</button>
                    <button type="reset" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Clear Form
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function toggleInternetFields(hasInternet) {
        const internetSourceField = document.getElementById('internetSourceField');
        const reliableNetworkField = document.getElementById('reliableNetworkField');

        if (hasInternet) {
            internetSourceField.classList.add('show');
            reliableNetworkField.classList.remove('show');
        } else {
            internetSourceField.classList.remove('show');
            reliableNetworkField.classList.add('show');
        }
    }
    </script>

</body>
</html>
