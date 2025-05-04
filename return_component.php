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
    $con->begin_transaction(); 

    try {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'return_') === 0) {
                $id = str_replace('return_', '', $key);
                $status = $value;

                if ($status == "Returned") {
                    // Update the request_issue table to set the status to 'Returned'
                    $sqlRequest = "UPDATE request_issue SET status='Returned' WHERE r_id=?";
                    $stmtRequest = statement($con, $sqlRequest, $id);
                    $stmtRequest->execute();

                    // Update the issue_component table to set the return date (current date)
                    $currentDate = date('Y-m-d H:i:s');  // Get the current date and time
                    $sqlUpdateIssue = "UPDATE issue_component SET return_date=? WHERE r_id=?";
                    $stmtIssue = statement($con, $sqlUpdateIssue, $currentDate, $id);
                    $stmtIssue->execute();

                    // Now update the stock table to adjust the available quantity
                    // First, we need to find the quantity returned and the corresponding stock_id
                    $sqlGetStock = "SELECT stock_id, quantity FROM request_issue WHERE r_id=?";
                    $stmtStock = statement($con, $sqlGetStock, $id);
                    $stmtStock->execute();
                    $stockResult = $stmtStock->get_result()->fetch_assoc();

                    // Update the available quantity in the stock table
                    if ($stockResult) {
                        $stock_id = $stockResult['stock_id'];
                        $returnedQuantity = $stockResult['quantity'];

                        $sqlUpdateStock = "UPDATE stock SET available = available + ? WHERE stock_id = ?";
                        $stmtUpdateStock = statement($con, $sqlUpdateStock, $returnedQuantity, $stock_id);
                        $stmtUpdateStock->execute();
                    }
                }
            }
        }
        $con->commit();
    } catch (Exception $e) {
        $con->rollback();
        die("Error: " . $e->getMessage());
    }
}

// Query to retrieve items that have not been returned yet
$sql = "SELECT request_issue.r_id, request_issue.request_date, employee.name, stock.stock_name, request_issue.quantity, status FROM request_issue
        INNER JOIN stock ON request_issue.stock_id = stock.stock_id 
        INNER JOIN employee ON request_issue.emp_id = employee.emp_id
        WHERE status = 'Approved'";
$result = $con->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Component</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .container {
            margin: 20px auto;
            width: 80%;
        }
        #searchInput {
            width: 40%;
            padding: 10px;
            margin-bottom: 20px;
        }
        thead {
    background-color: rgb(207, 207, 207);
}
    </style>
    <script>
        function searchByRID() {
            // Get the value of the input field
            let input = document.getElementById('searchInput').value.toUpperCase();
            // Get all rows in the table
            let table = document.getElementById('returnTable');
            let tr = table.getElementsByTagName('tr');

            // Loop through all table rows (excluding the header)
            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td')[0]; // r_id is in the first column
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(input) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Return Stock Items</h1>
        <input type="text" id="searchInput" onkeyup="searchByRID()" placeholder="Search by Request ID (r_id)">
        <form method="post" action="">
            <table id="returnTable">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Request Date</th>
                        <th>Employee Name</th>
                        <th>Stock Name</th>
                        <th>Quantity</th>
                        <th>Return Status</th>
                        <th>Mark as Returned</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['r_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['request_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['stock_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "<td><input type='radio' name='return_" . htmlspecialchars($row['r_id']) . "' value='Returned' onchange='this.form.submit()'> Returned</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No items pending return</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </form>
    </div>
</body>
</html>
