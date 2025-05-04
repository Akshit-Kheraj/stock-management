<?php
session_start();

// Database connection
$server = "localhost:3306";
$user = "root";
$pass = "";
$dbs = "science_center";

$conn = new mysqli($server, $user, $pass, $dbs);
if ($conn->connect_error) {
    die("Can't connect to the server: " . $conn->connect_error);
}

// Check if the user is logged in
if (isset($_SESSION['Login']) && $_SESSION['Login'] === true) {
    $user_id = $_SESSION['user_id']; // User ID from session

    // Fetch employee ID based on the logged-in user
    $sqlEmp_id = "SELECT emp_id FROM employee WHERE user_id = ?";
    $stmt = $conn->prepare($sqlEmp_id);
    $stmt->bind_param('s', $user_id);
    $stmt->execute();
    $stmt->bind_result($employee_id);
    $stmt->fetch();
    $stmt->close();

    // Check if employee ID was fetched
    if (!$employee_id) {
        echo "Employee ID not found!";
        exit();
    }

    // Handle POST request for password change
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate new passwords match
        if ($new_password !== $confirm_password) {
            echo "New passwords do not match!";
            exit();
        }

        // Fetch the current password from the database
        $query = "SELECT password FROM employee WHERE emp_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        $stmt->bind_result($db_password);
        $stmt->fetch();
        $stmt->close();

        // Verify the current password matches the one in the database
        if ($current_password !== $db_password) {
            echo "Current password is incorrect!";
            exit();
        }

        // Update the password in the database without hashing
        $update_query = "UPDATE employee SET password = ? WHERE emp_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $new_password, $employee_id);

        if ($update_stmt->execute()) {
            echo "Password changed successfully!";
        } else {
            echo "Error updating password.";
        }

        $update_stmt->close();
        $conn->close();
    }
} else {
    // Redirect to login page if not logged in
    header("Location: index.php");
    exit();
}
?>
