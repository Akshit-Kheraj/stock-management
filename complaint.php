<?php
session_start();

include "db_connection.php";

if (isset($_SESSION['Login']) && $_SESSION['Login'] === true) {
    $user_name = $_SESSION['user_id'];

}
else{
    header("Location: index.php");
}

$sql = "SELECT employee.name, complaint.created_at, complaint.complaint_id, complaint.location, complaint.title, complaint.description, complaint.status 
        FROM complaint 
        INNER JOIN employee ON complaint.emp_id = employee.emp_id 
        WHERE complaint.status = 'Pending'";
$stmt = statement($con, $sql);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'status_') === 0) {
            $id = str_replace('status_', '', $key);
            $status = $value;

            if ($status === "Solved") {
                $sql = "UPDATE complaint SET status = 'Solved' WHERE complaint_id = ?";
                $stmt = statement($con, $sql, $id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
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
    <title>Reported Problem</title>
    <link rel="stylesheet" href="forms.css"/>
    <script>
        function submitForm(radio) {
            const form = radio.closest('form');
            if (form) {
                form.submit();
            }
        }
    </script>
</head>
<body>
    <h1>Reported Problem</h1>
    <hr>
    <div class="table_container">
        <div class="table_wrapper">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <table border="1">
                    <thead>
                        <tr>
                            <th>Reported Time</th>
                            <th>Reported By</th>
                            <th>Location</th>
                            <th>Problem</th>
                            <th>Problem Description</th>
                            <th>Status</th>
                            <th>Solved</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($rows = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($rows['created_at']) . "</td>";
                                echo "<td>" . htmlspecialchars($rows['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($rows['location']) . "</td>";
                                echo "<td>" . htmlspecialchars($rows['title']) . "</td>";
                                echo "<td>" . htmlspecialchars($rows['description']) . "</td>";
                                echo "<td>" . htmlspecialchars($rows['status']) . "</td>";
                                echo "<td><input type='radio' name='status_" . htmlspecialchars($rows['complaint_id']) . "' value='Solved' onchange='submitForm(this)'> Solved</td>";
                                echo "</tr>";
                            }
                        } else {
                            
                            echo "<tr> <td colspan='7'> Nothing is reported </td></tr> ";
                            
                        }
                        ?>
                    </tbody>
                </table>
            </form>
          
        </div>
    </div>
</body>
</html>
