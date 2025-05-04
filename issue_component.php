<?php
session_start();

include 'db_connection.php';

if (isset($_SESSION['Login']) && $_SESSION['Login'] === true) {
    $user_name = $_SESSION['user_id'];
} else {
    header("Location: index.php");
    exit;
} 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $con->begin_transaction(); // Start the transaction

    try {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'status_') === 0) {
                $id = str_replace('status_', '', $key);
                $status = $value;

                if ($status == "Approved") {
                    // Fetch the request details
                    $sqlRequest = "SELECT emp_id, stock_id, quantity FROM request_issue WHERE r_id=?";
                    $stmtRequest = statement($con, $sqlRequest, $id);
                    $stmtRequest->execute();
                    $requestResult = $stmtRequest->get_result();
                    $requestData = $requestResult->fetch_assoc();

                    // Debug: Check if the data is fetched correctly
                    if (!$requestData) {
                        throw new Exception("No data found for request ID: " . $id);
                    }

                    $emp_id = $requestData['emp_id'];
                    $stock_id = $requestData['stock_id'];
                    $quantityRequested = $requestData['quantity'];

                    // Fetch the available stock
                    $sqlStock = "SELECT available FROM stock WHERE stock_id=?";
                    $stmtStock = statement($con, $sqlStock, $stock_id);
                    $stmtStock->execute();
                    $stockResult = $stmtStock->get_result();
                    $stockData = $stockResult->fetch_assoc();

                    $availableStock = $stockData['available'];

                    if ($availableStock >= $quantityRequested) {
                        // Insert into issue_component
                        $sqlInsertIssue = "INSERT INTO issue_component (emp_id, stock_id, quantity) 
                                           VALUES (?, ?, ?)";
                        $stmtInsertIssue = statement($con, $sqlInsertIssue, $emp_id, $stock_id, $quantityRequested);
                        $stmtInsertIssue->execute();

                        // Update available stock
                        $newAvailableStock = $availableStock - $quantityRequested;
                        $sqlUpdateStock = "UPDATE stock SET available=? WHERE stock_id=?";
                        $stmtUpdateStock = statement($con, $sqlUpdateStock, $newAvailableStock, $stock_id);
                        $stmtUpdateStock->execute();

                        // Update request status to "Approved"
                        $sqlUpdateRequest = "UPDATE request_issue SET status='Approved' WHERE r_id=?";
                        $stmtUpdateRequest = statement($con, $sqlUpdateRequest, $id);
                        $stmtUpdateRequest->execute();
                    } else {
                        throw new Exception("Not enough stock available.");
                    }
                } else {
                    // Update request status to "Rejected"
                    $sqlUpdateRequest = "UPDATE request_issue SET status='Rejected' WHERE r_id=?";
                    $stmtUpdateRequest = statement($con, $sqlUpdateRequest, $id);
                    $stmtUpdateRequest->execute();
                }
            }
        }

        $con->commit(); // Commit the transaction if everything succeeds
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();

    } catch (Exception $e) {
        $con->rollback(); // Rollback the transaction in case of failure
        echo "Transaction failed: " . $e->getMessage();
    }
}

// Query to select data
$sql = "SELECT employee.name, request_issue.request_date, stock.stock_name, request_issue.status, request_issue.quantity, request_issue.r_id 
        FROM request_issue
        JOIN employee ON request_issue.emp_id = employee.emp_id
        JOIN stock ON request_issue.stock_id = stock.stock_id
        WHERE request_issue.status = 'Pending'";

$result = $con->query($sql);

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Pending Requests</title>
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
    <h2>Pending Requests</h2>
    <div class="table_container">
        <div class="table_wrapper">
            <form method="post" action="">
                <table border="1">
                    <thead>
                        <tr>
                            <th>Request Date</th>
                            <th>Employee Name</th>
                            <th>Stock Name</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Issue</th>
                            <th>Reject</th>
                        </tr>
                    </thead>
                    <tbody>
                       <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['request_date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['stock_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                echo "<td><input type='radio' name='status_" . htmlspecialchars($row['r_id']) . "' value='Approved' onchange='submitForm(this)'> Issue</td>";
                                echo "<td><input type='radio' name='status_" . htmlspecialchars($row['r_id']) . "' value='Rejected' onchange='submitForm(this)'> Reject</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No pending requests</td></tr>";
                        }
                       ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</body>
</html>



