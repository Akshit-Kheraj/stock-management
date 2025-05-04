<?php
session_start();

include 'db_connection.php';

if (isset($_SESSION['Login']) && $_SESSION['Login'] === true) {
    $user_name = $_SESSION['user_id'];

}
else{
    header("Location: index.php");
}

$sqlTask = "
        SELECT
            employee.emp_id,
            employee.name, 
            task.task_name,
            task.task_type,
            assign_task.assign_id,
            assign_task.assign_date,
            Location.location_name,
            assign_task.deadline,
            assign_task.status
        FROM 
            assign_task
        INNER JOIN 
            task ON assign_task.task_id = task.task_id
        INNER JOIN
            employee on assign_task.emp_id = employee.emp_id
        INNER JOIN 
            Location ON assign_task.location_id = Location.location_id
    ";

$stmt = $con->prepare($sqlTask);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();




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
                            <th>Task LOcation</th>
                            <th>Task</th>
                            <th>Task Type</th>
                            <th>Assign Time</th>
                            <th>Deadline</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row["emp_id"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["location_name"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["task_name"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["task_type"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["location_name"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["deadline"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            
                            echo "<tr> <td colspan='7'> Nothing is available </td></tr> ";
                            
                        }
                        ?>
                    </tbody>
                </table>
            
          
        </div>
    </div>
</body>
</html>
