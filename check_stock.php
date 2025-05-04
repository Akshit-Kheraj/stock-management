<?php
session_start();

include 'db_connection.php';

$sql = "SELECT * FROM stock";
$result = $con->query($sql);

if (isset($_SESSION['Login']) && $_SESSION['Login'] === true) {
    $user_name = $_SESSION['user_id'];
} else {
    header("Location: index.php");
    exit(); // Always exit after redirect
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['download'])) {
    try {
        $sql = "SELECT * FROM stock";
        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            // Set headers to force download as a CSV file
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename=report.csv');
            
            // Open output stream
            $output = fopen('php://output', 'w');
            
            // Write column headers (optional)
            fputcsv($output, ['Stock ID', 'Category', 'Stock Name', 'Quantity', 'Available']);
            
            // Write each row of the result
            while ($rows = $result->fetch_assoc()) {
                fputcsv($output, [
                    $rows['stock_id'],
                    $rows['category'],
                    $rows['stock_name'],
                    $rows['quantity'],
                    $rows['available']
                ]);
            }
            fclose($output);
            exit(); // Stop further script execution after outputting the CSV
        } else {
            echo "No data available to download.";
        }
    } catch (Exception $e) {
        echo "Unable to download at this time: " . $e->getMessage();
    }
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Stock</title>
    <link rel="stylesheet" href="forms.css"/>
    <style>
        .button-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .download-btn {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .download-btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <h2>Available Stock of all category</h2>
    <div class="table_container">
        <div class="table_wrapper">
            <table border="1">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Stock ID</th>
                        <th>Stock Name</th>
                        <th>Quantity</th>
                        <th>Available</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>".htmlspecialchars($row['category'])."</td>";
                            echo "<td>" . htmlspecialchars($row['stock_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['stock_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['available']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>There are no records.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="button-container">
        <form method="post" action="">
            <input type="submit" name="download" value="Download CSV" class="download-btn">
        </form>
    </div>
</body>
</html>
