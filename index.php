<?php
session_start();

include 'db_connection.php';

// Establish database connection
$con = new mysqli($server, $user, $password, $dbs);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare statement to fetch the password and job role
    $stmt = $con->prepare("SELECT Password, job_role FROM employee WHERE user_id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $con->error);
    }

    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($Password, $job_role);
    $stmt->fetch();
    $stmt->close();

    // Verify the password
    if ($password === $Password) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['Login'] = true;

        if ($job_role === 'Admin') {
            header("Location: admin.php");
            exit;
        } elseif ($job_role === 'Manager') {
            header("Location: manager.php");
            exit;
        } elseif ($job_role === 'Employee') {
            header("Location: employee_dashboard.php");
            exit;
        } else {
            $_SESSION['error'] = "Invalid job role.";
            header("Location: index.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "Invalid username or password.";
        header("Location: index.php");
        exit;
    }
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login/Register</title>
    <link rel="stylesheet" href="index.css?v=<?php echo time(); ?>" />
    <script>
        function validateForm() {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            if (username === '' || password === '') {
                alert('Both username and password are required.');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="box">
            <h1><b>Login</b></h1>
            <?php
            if (isset($_SESSION['error'])) {
                echo "<p>" . htmlspecialchars($_SESSION['error']) . "</p>";
                unset($_SESSION['error']);
            }
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="return validateForm()">       
                <label for="username">Username</label><br>
                <input type="text" id="username" name="username" placeholder="Username" required>
                <br>
                <label for="password">Password</label><br>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <br>
                <input type="submit" value="Login" id="button">
            </form>
            <br>
            <label for="register">Don't have an account?</label>
            <br>
            <a href="Register.php" style="color:black;">Register</a>
            <br>
            <a href="forgot_password.php" style="color:black;">Forgot Password?</a> <!-- Forgot Password link -->
        </div>
    </div>
</body>
</html>
