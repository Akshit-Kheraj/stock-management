<?php
session_start(); // Start session

// Database connection setup
include 'db_connection.php';

// Verify if the user is logged in
if (!isset($_SESSION['Login']) || $_SESSION['Login'] !== true) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit;
}

$message = ""; // Message variable initialized

// If form is submitted to save changes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $issue_id = $_POST['issue_id'];
    $emp_id = $_SESSION['emp_id']; // Get emp_id from session
    $stock_id = $_POST['stock_id'];
    $issue_date = $_POST['issue_date'];
    $return_date = $_POST['return_date'];
    $quantity = $_POST['quantity'];

    // Insert query to save changes to the database
    $stmt = $con->prepare("INSERT INTO component (issue_id, emp_id, stock_id, issue_date, return_date, quantity) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissi", $issue_id, $emp_id, $stock_id, $issue_date, $return_date, $quantity);

    if ($stmt->execute()) {
        $message = "Request submitted successfully."; // Success message set
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all data from 'component' table
$sql = "SELECT * FROM component";
$result = $con->query($sql);

$con->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Request</title>
    
    <!-- Corrected path to CSS -->
    <link rel="stylesheet" href="raise_complaints.css?v=1.0" />
</head>

<body>
    <div class="container">
        <h1><b>Request</b></h1>
        <hr />

        <!-- Form for component request -->
        <form action="submit" method="post">
            <label for="category">Category of Component</label>
            <select name="category" id="category">
                <option>Electronic</option>
                <option>Stationary</option>
                <option>Other</option>
            </select>

            <label for="name">Component Name</label>
    <input
        type="text"
        id="name"
        name="name"
        placeholder="Component Name"
        required
    />

    <label for="quantity">Quantity</label>
    <input
        type="number"
        id="quantity"
        name="quantity"
        placeholder="Quantity"
        required
    />

    <!-- Submit button -->
    <button type="submit">Submit</button>
</form>
        <br />
        <div id="message"><?php echo $message; ?></div>
    </div>
</body>
</html>
