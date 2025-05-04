<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <style>
        /* CSS Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h2 {
            color: #333;
            text-align: center;
        }

        form {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="password"] {
            width: 95%; /* Full width */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            width: 40%; /* Full width */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #2d91fd;
        }

        .button-container {
            display: flex;
            justify-content: center; /* Center horizontally */
            margin-top: 20px; /* Optional: Add some space above the buttons */
        }

        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .success {
            color: green;
            font-size: 14px;
            margin-bottom: 15px;
        }
    </style>

    <script>
        // JavaScript for client-side validation
        function validateForm() {
            let currentPassword = document.getElementById('current_password').value;
            let newPassword = document.getElementById('new_password').value;
            let confirmPassword = document.getElementById('confirm_password').value;
            let errorMessage = '';

            if (newPassword.length < 6) {
                errorMessage = 'New password must be at least 6 characters long.';
            } else if (newPassword !== confirmPassword) {
                errorMessage = 'New passwords do not match!';
            }

            if (errorMessage) {
                document.getElementById('error_message').innerText = errorMessage;
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
    <h2>Change Password</h2>

    <form action="change_password.php" method="POST" onsubmit="return validateForm();">
        <div id="error_message" class="error"></div>

        <label for="current_password">Current Password:</label>
        <input type="password" id="current_password" name="current_password" required>

        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required>

        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <div class="button-container">
            <input type="submit" value="Change Password">
        </div>
    </form>

    <?php
session_start();

include 'db_connection.php';
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
