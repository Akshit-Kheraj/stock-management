<?php
session_start();

include 'db_connection.php';
if (isset($_SESSION['Login']) && $_SESSION['Login'] === true) {
    $user_name = $_SESSION['user_id'];
} else {
    header("Location: index.php");
}

$sql = "SELECT name, emp_id, job_role, gender, contact_number, email FROM employee";
$stmt = $con->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$employeeDetails = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['view_emp'])) {
    $emp_id = $_POST['view_emp'];
    // Fetch employee details based on emp_id
    $detailsSql = "SELECT * FROM employee WHERE emp_id = ?";
    $detailsStmt = $con->prepare($detailsSql);
    $detailsStmt->bind_param('i', $emp_id);
    $detailsStmt->execute();
    $employeeDetails = $detailsStmt->get_result()->fetch_assoc();
    $detailsStmt->close();
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
    <style>
        .details-box {
            margin-top: 20px;
            border: 1px solid #007BFF;
            padding: 20px;
            border-radius: 10px;
            background-color: #e9f4ff;
            color: #333;
            max-width: 500px; /* Fixed width for the details box */
            margin-left: auto;
            margin-right: auto; /* Center align the box */
        }
        .details-box h2 {
            margin-bottom: 15px;
            font-size: 1.5rem;
            color: #0056b3;
        }
        .details-content {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .details-content p {
            margin: 0;
            font-size: 1.1rem;
            line-height: 1.5;
        }
        .details-content p strong {
            display: inline-block;
            width: 150px; /* Fixed width for labels */
            font-weight: bold;
        }
        .view-btn {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 8px 15px;
            font-size: 1rem;
            border-radius: 5px;
            cursor: pointer;
        }
        .view-btn:hover {
            background-color: #0056b3;
        }
        .back-btn {
            margin-top: 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 5px;
            cursor: pointer;
            display: inline-block;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
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
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Job Profile</th>
                            <th>Email</th>
                            <th>Contact Number</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($rows = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($rows['emp_id']) . "</td>";
                                echo "<td>" . htmlspecialchars($rows['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($rows['gender']) . "</td>";
                                echo "<td>" . htmlspecialchars($rows['job_role']) . "</td>";
                                echo "<td>" . htmlspecialchars($rows['email']) . "</td>";
                                echo "<td>" . htmlspecialchars($rows['contact_number']) . "</td>";
                                echo "<td>
                                        <button type='submit' name='view_emp' value='" . htmlspecialchars($rows['emp_id']) . "' class='view-btn'>View</button>
                                      </td>";
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

    <?php if ($employeeDetails): ?>
    <div class="details-box">
        <h2>Employee Details</h2>
        <div class="details-content">
            <p><strong>Employee ID:</strong> <?php echo htmlspecialchars($employeeDetails['emp_id']); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($employeeDetails['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($employeeDetails['email']); ?></p>
            <p><strong>Gender:</strong> <?php echo htmlspecialchars($employeeDetails['gender']); ?></p>
            <p><strong>Job Role:</strong> <?php echo htmlspecialchars($employeeDetails['job_role']); ?></p>
            <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($employeeDetails['contact_number']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($employeeDetails['address']); ?></p>
            <p><strong>Joining Date:</strong> <?php echo htmlspecialchars($employeeDetails['joining_date']); ?></p>
        </div>
        <button class="back-btn" onclick="window.location.href='admin.php';">Back</button>
    </div>
    <?php endif; ?>
</body>
</html>
