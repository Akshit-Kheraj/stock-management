<?php
session_start();

include 'db_connection.php';

// Ensure user is logged in
if (isset($_SESSION['Login']) && $_SESSION['Login'] === true) {
    $user_name = $_SESSION['user_id'];

    try {
        // Fetch employee details
        $sql = "SELECT name, job_role, emp_id FROM employee WHERE user_id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('s', $user_name);
        $stmt->execute();
        $stmt->bind_result($name, $job_role, $emp_id);
        $stmt->fetch();
        $stmt->close();

        // Fetch the manager's location or gallery
        $sqlGallery = "SELECT location_id FROM manager_gallery WHERE emp_id = ?";
        $stmt = $con->prepare($sqlGallery);
        $stmt->bind_param('i', $emp_id);
        $stmt->execute();
        $stmt->bind_result($location_id);
        $stmt->fetch();
        $stmt->close();

        // Fetch stock data for display and download based on manager's location
        $sql = "
            SELECT stock.stock_name, permanent_issue.quantity, permanent_issue.p_id 
            FROM permanent_issue 
            INNER JOIN stock ON permanent_issue.stock_id = stock.stock_id 
            WHERE permanent_issue.location_id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('i', $location_id);
        $stmt->execute();
        $result = $stmt->get_result(); // Get the result for display

        // Handle CSV download
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['download'])) {
            if ($result->num_rows > 0) {
                // Set headers to force download as a CSV file
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment;filename=report.csv');

                // Open output stream
                $output = fopen('php://output', 'w');

                // Write CSV column headers
                fputcsv($output, ['Permanent Issue ID', 'Stock Name', 'Quantity']);

                // Write each row of the result to CSV
                while ($rows = $result->fetch_assoc()) {
                    fputcsv($output, [
                        $rows['p_id'],
                        $rows['stock_name'],
                        $rows['quantity']
                    ]);
                }
                fclose($output);
                exit();
            } else {
                echo "No data available to download.";
            }
        }
    } catch (Exception $e) {
        echo "Unable to fetch data: " . $e->getMessage();
    }
} else {
    // Redirect to login if session is not active
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
    <title>Stock</title>
    <link rel="stylesheet" href="forms.css?v=<?php echo time(); ?>">
    <style>
        .button-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .download-btn, .back-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }

        .download-btn:hover, .back-btn:hover {
            background-color: #2d91fd;
        }
    </style>
</head>
<body>
    <h2>Available Stock of all categories</h2>
    <div class="table_container">
        <div class="table_wrapper">
            <table border="1">
                <thead>
                    <tr>
                        <th>Count</th>
                        <th>Issue ID</th>
                        <th>Stock Name</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>$i</td>";
                            echo "<td>" . (isset($row['p_id']) ? htmlspecialchars($row['p_id']) : "N/A") . "</td>";
                            echo "<td>" . htmlspecialchars($row['stock_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                            echo "</tr>";
                            ++$i;
                        }
                    } else {
                        echo "<tr><td colspan='4'>There are no records.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="button-container">
        <form method="get" action="manager.php">
            <input type="submit" value="Back to Dashboard" class="back-btn">
        </form>
        <form method="post" action="">
            <input type="submit" name="download" value="Download CSV" class="download-btn">
        </form>
    </div>
</body>
</html>
