<?php
session_start();

// Database connection setup
include 'db_connection.php';
// Verify if the user is logged in
if (!isset($_SESSION['Login']) || $_SESSION['Login'] !== true) {
    header("Location: index.php");
    exit;
}

// Ensure emp_id is set in session
if (!isset($_SESSION['user_id'])) {
    die("Error: Employee ID not found in session.");
}

$user_id = $_SESSION['user_id'];
$sqlId = "SELECT emp_id from employee where user_id =?";
$stmt = $con->prepare($sqlId);
$stmt->bind_param('s',$user_id);
$stmt->execute();
$stmt->bind_result($emp_id);
$stmt->fetch();
$stmt->close();



$message = ""; // Message variable initialized

// If form is submitted to save complaint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $location = $_POST['location'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = 'Pending'; // Default status

    // Insert query to save complaint to the database
    $stmt = $con->prepare("INSERT INTO complaint (emp_id, created_at, location, title, description, status) VALUES (?, NOW(), ?, ?, ?, ?)");
    $stmt->bind_param("issss", $emp_id, $location, $title, $description, $status);

    if ($stmt->execute()) {
        $message = "Complaint submitted successfully."; // Success message set
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all data from 'complaint' table (if needed)
$sql = "SELECT * FROM complaint";
$result = $con->query($sql);

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Raise Complaint</title>
  <link rel="stylesheet" href="raise_complaint.css?v=<?php echo time(); ?>">

  <script>
    // JavaScript to hide the message after 10 seconds
    function hideMessage() {
      setTimeout(function() {
        document.getElementById('message').style.display = 'none';
      }, 10000); // 10,000 milliseconds = 10 seconds
    }
  </script>
</head>
<body onload="hideMessage()">
  <div class="container">
    <h2>Raise Complaint</h2>
    <form method="POST" action="raise_complaint.php" class="center-form">
      <div class="form-group">
        <label for="location">Location:</label>
        <input type="text" name="location" id="location" required>

        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required>

        <label for="description">Description:</label>
        <textarea name="description" id="description" style="height: 200px; resize: none;" required></textarea>
      </div>
      
      <input type="submit" value="Submit Complaint">
    </form>

    <!-- Message section to display success or error -->
    <?php if ($message): ?>
      <p id="message" style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>
  </div>
</body>
</html>
