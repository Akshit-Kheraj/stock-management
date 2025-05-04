<?php
session_start();
include 'db_connection.php';

// Check if the user is logged in
if (isset($_SESSION['Login']) && $_SESSION['Login'] === true) {
    $user_id  = $_SESSION['user_id'];

    // Fetch the employee data
    $sqlEmp = "SELECT emp_id, job_role FROM employee WHERE user_id = ?";
    $stmt = statement($con, $sqlEmp, $user_id);
    $stmt->execute();
    $stmt->bind_result($emp_id, $job_role);
    $stmt->fetch();
    $stmt->close();

    if ($job_role === 'Admin') {
        // Query to fetch managers
        $sqlManager = "SELECT emp_id, name FROM employee WHERE job_role = 'Manager'";
        $resultManager = $con->query($sqlManager);

        // Debug the manager query
        if ($resultManager->num_rows > 0) {
            echo "Managers found: " . $resultManager->num_rows . "<br>";
        } else {
            echo "No managers found!<br>";
        }

        // Query to fetch locations
        $sqlLocation = "SELECT location_id, location_name FROM location";
        $resultLocation = $con->query($sqlLocation);

        // Debug the location query
        if ($resultLocation->num_rows > 0) {
            echo "Locations found: " . $resultLocation->num_rows . "<br>";
        } else {
            echo "No locations found!<br>";
        }

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $location_id = $_POST['location_id'];
            $emp_idM = $_POST['emp_id'];

            // Insert manager and location into the manager_gallery table
            $sqlManagerGallery = "INSERT INTO manager_gallery (emp_id, location_id) VALUES (?, ?)";
            $stmt = statement($con, $sqlManagerGallery, $emp_idM, $location_id);
            $stmt->execute();
            $stmt->close();

            echo "<p>Location assigned successfully to manager!</p>";
        }
    } else {
        echo "You do not have permission to assign locations.";
    }
} else {
    header("Location: index.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Manager Location</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 50%;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            font-size: 1.2em;
            color: #333;
            margin-bottom: 10px;
            display: block;
        }

        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 1em;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Assign Location to Manager</h2>
        <form action="" method="POST">
            <!-- Manager Dropdown -->
            <label for="manager">Select Manager:</label>
            <select name="emp_id" required>
                <option value="" disabled selected>Select Manager</option>
                <?php
                if ($resultManager->num_rows > 0) {
                    while ($row = $resultManager->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['emp_id']) . '">' . htmlspecialchars($row['name']) . '</option>';
                    }
                } else {
                    echo '<option value="" disabled>No managers available</option>';
                }
                ?>
            </select>

            <!-- Location Dropdown -->
            <label for="location">Select Location:</label>
            <select name="location_id" required>
                <option value="" disabled selected>Select Location</option>
                <?php
                if ($resultLocation->num_rows > 0) {
                    while ($row = $resultLocation->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['location_id']) . '">' . htmlspecialchars($row['location_name']) . '</option>';
                    }
                } else {
                    echo '<option value="" disabled>No locations available</option>';
                }
                ?>
            </select>

            <!-- Submit Button -->
            <input type="submit" value="Assign Location">
        </form>
    </div>
</body>
</html>
