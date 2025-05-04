<?php 
session_start();

if (isset($_SESSION['Login']) && $_SESSION['Login'] === true) {
    $user_name = $_SESSION['user_id'];
}
else {
    header("Location: index.php");
}

include 'db_connection.php';



$success = false;

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $contact_number = $_POST['contact_number'];
    $position = $_POST['position'];
    $password = $_POST['password'];

    $sql = "INSERT INTO employee (name, email, job_role, gender, password, contact_number) VALUES (?, ?, ?, ?, ?, ?)";

   
   
    $stmt = statement($con,$sql, $name, $email, $position, $gender, $password, $contact_number);
    if ($stmt->execute()) {
        $success = true;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

$con->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    
    <title>Add Employee</title>
    <link rel="stylesheet" href="add_employee.css?v=<?php echo time(); ?>">
</head>
<body>

<?php if ($success): ?>
<script>
    alert("Employee added successfully!");
</script>
<?php endif; ?>

<h2><b>Enter Employee Details</b></h2>  
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <label for="name">Employee Name</label>
    <br>
    <input type="text" id="name" name="name" placeholder="Enter Employee Name" required>
    <br>
    <label for="gender">Gender</label>
    <br>
    <select name="gender">
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Other">Other</option>
    </select>
    <br>
    <label for="email">Employee Email</label>
    <br>
    <input type="email" id="email" name="email" placeholder="Enter Employee Email" required>
    <br>
    <label for="contact_number">Contact Number</label>
    <br>
    <input type="number" id="contact_number" name="contact_number" placeholder="Enter Employee Contact Number" required>
    <br>
    <label for="position">Position</label>
    <br>
    <select id="position" name="position">
        <option value="Manager">Manager</option>
        <option value="Engineer">Engineer</option>
        <option value="it_head">IT Head</option>
        <option value="Employee">Employee</option>
        <option value="house_keeping">House Keeping</option>
    </select>
    <br>
    <label for="password">Password</label>
    <input type="password" name="password" placeholder="Enter password for employee" required>
    <br>
    <button type="submit">Add Employee</button>
</form>

</body>
</html>
