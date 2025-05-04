<?php
session_start();

include 'db_connection.php';
if (isset($_SESSION['Login']) && $_SESSION['Login'] === true) {
    $user_name = $_SESSION['user_id'];
    $sql = "SELECT job_role, emp_id FROM employee WHERE user_id = ?";
    $stmt = statement($con, $sql, $user_name);
    $stmt->execute();
    $stmt->bind_result($job, $emp_id);
    $stmt->fetch();
    $stmt->close();

    // Fetching the manager location or gallery
    $sqlGallery = "
    SELECT manager_gallery.location_id
    FROM manager_gallery 
    JOIN employee ON manager_gallery.emp_id = employee.emp_id
    WHERE manager_gallery.emp_id = ?";  // Removed the unnecessary JOIN with location

    $stmt = statement($con, $sqlGallery, $emp_id);
    $stmt->execute();
    $stmt->bind_result($location_id);
    $stmt->fetch();
    $stmt->close();

    // Check if the location_id was successfully fetched
    if ($location_id !== null) {
        $sqlTask = "
        SELECT
            employee.emp_id,
            employee.name, 
            task.task_name,
            task.task_type,
            assign_task.assign_id,
            assign_task.assign_date,
            location.location_name,
            assign_task.deadline,
            assign_task.status
        FROM 
            assign_task
        INNER JOIN 
            task ON assign_task.task_id = task.task_id
        INNER JOIN
            employee ON assign_task.emp_id = employee.emp_id
        INNER JOIN 
            location ON assign_task.location_id = location.location_id
        WHERE assign_task.location_id = ?;";

        $stmt = statement($con, $sqlTask, $location_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        // Handle the case where location_id is not found
        echo "No location found for this employee.";
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Task Status</title>
    <link rel="stylesheet" href="forms.css"/>
   
</head>
<body>
    <h1>Task Status</h1>
    <hr>
    <div class="table_container">
        <div class="table_wrapper">
            <table border="1">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Task Location</th>
                        <th>Task</th>
                        <th>Task Type</th>
                        <th>Assign Time</th>
                        <th>Deadline</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (isset($result) && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["emp_id"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["location_name"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["task_name"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["task_type"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["assign_date"]) . "</td>"; // Correct column for assign time
                            echo "<td>" . htmlspecialchars($row["deadline"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr> <td colspan='8'> Nothing is available </td></tr>"; // Correct colspan value
                    }
                    ?>
                </tbody>
            </table>
           
        </div>
    </div>
</body>
</html>
