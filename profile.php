<?php
session_start();

include 'db_connection.php';

// Verify if the user is logged in
if (!isset($_SESSION['Login']) || $_SESSION['Login'] !== true) {
    header("Location: index.php");
    exit;
}

// Get the emp_id from the session
$user_id = $_SESSION['user_id'];
$sqlEmp_id = "SELECT emp_id FROM employee WHERE user_id = ?"; // Changed to user_id for session
$stmt = statement($con, $sqlEmp_id, $user_id);
$stmt->execute();
$stmt->bind_result($emp_id);
$stmt->fetch(); // Fetch the emp_id value
$stmt->close(); // Close the statement

// If form is submitted to save changes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve input values
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $job_role = $_POST['job_role'];
    $joining_date = $_POST['joining_date'];
    $dob = $_POST['dob']; // New DOB field
    $user_id = $_POST['user_id']; // New User ID field

    // Update query to save the changes to the database
    $update_sql = "UPDATE employee SET 
                        name = ?, 
                        gender = ?, 
                        address = ?, 
                        contact_number = ?, 
                        email = ?, 
                        job_role = ?, 
                        joining_date = ?, 
                        DOB = ?, 
                        user_id = ? 
                   WHERE emp_id = ?";
    
    $stmt = statement($con,$update_sql,$name, $gender, $address, $phone, $email, $job_role, $joining_date, $dob, $user_id, $emp_id);
    $stmt->execute();
    $stmt->close();
    
}

// Fetch employee details from the database
$sql = "SELECT * FROM employee WHERE user_id = ?";
$stmt = statement($con, $sql, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    die("No profile found.");
}

$stmt->close();
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Profile</title>
  <link rel="stylesheet" href="profile.css?v=<?php echo time(); ?>" />
  <script>
    // Enable editing of the form fields
    function enableEditing() {
        var inputs = document.querySelectorAll('.profile-input');
        inputs.forEach(input => input.removeAttribute('readonly'));
        document.getElementById('edit-btn').style.display = 'none'; // Hide edit button
        document.getElementById('save-btn').style.display = 'inline'; // Show save button
    }

    // Function to go back to the dashboard
    function goBackToDashboard() {
        window.location.href = 'manager.php'; // Redirect to the dashboard
    }

    // Optional: Reset form after saving (if needed)
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST') { ?>
        document.addEventListener('DOMContentLoaded', function() {
            var inputs = document.querySelectorAll('.profile-input');
            inputs.forEach(input => input.setAttribute('readonly', true));
            document.getElementById('save-btn').style.display = 'none';
            document.getElementById('edit-btn').style.display = 'inline';
        });
    <?php } ?>
  </script>
</head>
<body>
    <header style="background-color: #3B8FF3;">
        <h1>Profile</h1>
        <div class="circle-container">
            <div class="circle" style="background-image: url('sciencCenterLogo.png.jpg');"></div>
        </div>
    </header>

    <main class="profile-container">
        <form method="POST">
            <div class="profile-section">
                <div class="left-column">
                    <table>
                        <tr>
                            <th>Employee ID</th>
                            <td><input type="text" value="<?php echo htmlspecialchars($employee['emp_id']); ?>" readonly></td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td><input type="text" name="name" class="profile-input" value="<?php echo htmlspecialchars($employee['name']); ?>" readonly></td>
                        </tr>
                        <tr>
                            <th>Gender</th>
                            <td><input type="text" name="gender" class="profile-input" value="<?php echo htmlspecialchars($employee['gender']); ?>" readonly></td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td><input type="text" name="address" class="profile-input" value="<?php echo htmlspecialchars($employee['address']); ?>" readonly></td>
                        </tr>
                        <tr>
                            <th>Date of Birth</th>
                            <td><input type="date" name="dob" class="profile-input" value="<?php echo htmlspecialchars($employee['DOB']); ?>" readonly></td>
                        </tr>
                    </table>
                </div>
                <div class="right-column">
                    <table>
                        <tr>
                            <th>Phone</th>
                            <td><input type="text" name="phone" class="profile-input" value="<?php echo htmlspecialchars($employee['contact_number']); ?>" readonly></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><input type="text" name="email" class="profile-input" value="<?php echo htmlspecialchars($employee['email']); ?>" readonly></td>
                        </tr>
                        <tr>
                            <th>Job Role</th>
                            <td><input type="text" name="job_role" class="profile-input" value="<?php echo htmlspecialchars($employee['job_role']); ?>" readonly></td>
                        </tr>
                        <tr>
                            <th>Joining Date</th>
                            <td><input type="date" name="joining_date" class="profile-input" value="<?php echo htmlspecialchars($employee['joining_date']); ?>" readonly></td>
                        </tr>
                        <tr>
                            <th>User ID</th>
                            <td><input type="text" name="user_id" class="profile-input" value="<?php echo htmlspecialchars($employee['user_id']); ?>" readonly></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="profile-buttons">
                <button type="button" id="edit-btn" onclick="enableEditing()">Edit</button>
                <button type="submit" id="save-btn" style="display: none;">Save</button>
                <button type="button" id="back-btn" onclick="goBackToDashboard()">Back</button>
            </div>
        </form>
    </main>
</body>
</html>
