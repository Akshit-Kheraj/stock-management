<?php
session_start(); // Start session

include 'db_connection.php';

// Verify if the user is logged in
if (!isset($_SESSION['Login']) || $_SESSION['Login'] !== true) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit;
}



$message = ""; // Message variable initialized
$user_id = $_SESSION['user_id'];

// Fetch employee id
$sql = "SELECT emp_id FROM employee WHERE user_id=?";
$stmt = statement($con, $sql, $user_id);
$stmt->execute();
$emp_id_result = $stmt->get_result()->fetch_assoc();
$emp_id = $emp_id_result['emp_id'];
$stmt->close();

// Fetch stock components
$sqlStock = "SELECT stock_id, stock_name FROM stock";
$result = $con->query($sqlStock);

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $category = $_POST['category'];
  $stock_id = $_POST['name']; // Selected stock component ID
  $quantity = $_POST['quantity'];

  // Insert the request into the database
  $sqlInsert = "INSERT INTO request_issue (emp_id, stock_id,  quantity) VALUES ( ?, ?, ?)";
  $stmt = statement($con, $sqlInsert, $emp_id, $stock_id,  $quantity);

  if ($stmt->execute()) {
      $message = "Request submitted successfully.";
  } else {
      // Print more detailed error message
      $message = "Error: " . $stmt->error . " SQL: " . $sqlInsert;
  }

  $stmt->close();
}


$con->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Request</title>
    <link rel="stylesheet" href="request_component.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <h1><b>Request</b></h1>
        <hr />

        <!-- Display the success or error message -->
        <?php if ($message): ?>
            <p id="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <!-- Form for component request -->
        <form action="" method="post">
           

            <label for="name">Component Name</label>
            <br />
            <select name="name" id="name">
                <?php
                if ($result->num_rows > 0) {
                    while ($rows = $result->fetch_assoc()) {
                        echo "<option value='".htmlspecialchars($rows['stock_id'])."'>".htmlspecialchars($rows['stock_name'])."</option>";
                    }
                }
                ?>
            </select>
            <br />

            <label for="quantity">Quantity</label>
            <br />
            <input type="number" id="quantity" name="quantity" placeholder="Quantity" required />
            <br />

            <!-- Submit button -->
            <button type="submit">Submit</button>
        </form>
        <br />
    </div>
</body>
</html>
