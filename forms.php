<?php
session_start();

include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'status_') === 0) {
            $id = str_replace('status_', '', $key);
            $status = $value;

            if ($status == "Approved") {
                $sqlInsert = "INSERT INTO employee (name, email, contact_number, gender, job_role,user_id,password) 
                              SELECT name, email, contact_number, gender, job_role ,user_id, password
                              FROM register WHERE register_id=?";
                $stmtInsert = statement($con, $sqlInsert, $id);
                $stmtInsert->execute();

                $sqlDelete = "DELETE FROM register WHERE register_id=?";
                $stmtDelete = statement($con, $sqlDelete, $id);
                $stmtDelete->execute();
            } else {
                $sqlUpdate = "UPDATE register SET status='Rejected' WHERE register_id=?";
                $stmtUpdate = statement($con, $sqlUpdate, $id);
                $stmtUpdate->execute();
            }
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$sql = "SELECT * FROM register WHERE status = 'Pending'";
$result = $con->query($sql);

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Received Registration Form</title>
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
    <h2>Received Registration Form</h2>
    <div class="table_container">
        <div class="table_wrapper">
            <form method="post" action="">
                <table border="1">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact Number</th>
                            <th>Gender</th>
                            <th>Job Role</th>
                            <th>Status</th>
                            <th>Approve</th>
                            <th>Reject</th>
                        </tr>
                    </thead>
                    <tbody>
                       <?php
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()){
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "</td>";
                                echo "<td>" . htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') . "</td>";
                                echo "<td>" . htmlspecialchars($row['contact_number'], ENT_QUOTES, 'UTF-8') . "</td>";
                                echo "<td>" . htmlspecialchars($row['gender'], ENT_QUOTES, 'UTF-8') . "</td>";
                                echo "<td>" . htmlspecialchars($row['job_role'], ENT_QUOTES, 'UTF-8') . "</td>";
                                echo "<td>" . htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') . "</td>";
                                echo "<td><input type='radio' name='status_" . htmlspecialchars($row['register_id'], ENT_QUOTES, 'UTF-8') . "' value='Approved' onchange='submitForm(this)'> Approve</td>";
                                echo "<td><input type='radio' name='status_" . htmlspecialchars($row['register_id'], ENT_QUOTES, 'UTF-8') . "' value='Rejected' onchange='submitForm(this)'> Reject</td>";
                                echo "</tr>";
                            }
                        }
                        else{
                            echo "<tr><td colspan='8'>There are no received registration forms</td></tr>";
                        }
                       ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</body>
</html>
