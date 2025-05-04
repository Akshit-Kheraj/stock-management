<?php
session_start();

include 'db_connection.php';
// Verify the session to restrict unauthorized access
if (!isset($_SESSION['Login']) || $_SESSION['Login'] !== true) {
    header("Location: index.php");
    exit;
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Task Status</title>
    <link rel="stylesheet" href="emp.css?v=<?php echo time(); ?>" />
</head>
<body>
    <header style="background-color: sky blue;">
        <div class="logo">
            <img src="logo.jpg" alt="Science Center Logo">
        </div>
        <div class="user-profile">
            <p><span>Welcome, Manager <?php echo htmlspecialchars($_SESSION['name']); ?></span></p>
        </div>
    </header>

    <main class="table-container" align="center">
        <p><b>Stock Task Status</b></p>
        <table border="1">
            <tr>
                <th>Task</th>
                <th>Assigned To</th>
                <th>Deadline</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>Restock Inventory</td>
                <td>Employee 2</td>
                <td>05-09-2024</td>
                <td>
                    <select>
                        <option>Pending</option>
                        <option>In Progress</option>
                        <option>Completed</option>
                        <option>Rejected</option>
                    </select>
                </td>
            </tr>
            <!-- Add more rows as needed -->
        </table>
    </main>
    <hr>

    <footer>
        Event
        <!-- Footer content here -->
    </footer>
</body>
</html>
