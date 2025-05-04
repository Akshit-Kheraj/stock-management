<?php
session_start();

include 'db_connection.php';

// Check if the user is logged in
if (isset($_SESSION['Login']) && $_SESSION['Login'] === true) {
    $user_id = $_SESSION['user_id'];
} else {
    header("Location: index.php");
    exit;
}

// Fetch employee ID using user_id from session
$sqlEmp_id = "SELECT emp_id FROM employee WHERE user_id = ?";
$stmt = statement($con, $sqlEmp_id, $user_id);
$stmt->execute();
$emp_id_result = $stmt->get_result();
$emp_id = $emp_id_result->fetch_assoc()['emp_id'];
$stmt->close();

// Fetch requests
$sqlRequest = "
    SELECT
        request_issue.r_id,
        stock.stock_name,
        request_issue.request_date,
        request_issue.status,
        request_issue.quantity
    FROM 
        request_issue
    INNER JOIN 
        stock ON request_issue.stock_id = stock.stock_id
    WHERE request_issue.emp_id = ?;
";
$stmt = statement($con, $sqlRequest, $emp_id);
$stmt->execute();
$resultRequest = $stmt->get_result();
$stmt->close();

// Fetch complaints
$sqlComplaint = "
    SELECT
        complaint_id,
        title,
        description,
        created_at,
        location,
        status
    FROM 
        complaint
    WHERE emp_id = ?;
";
$stmt = statement($con, $sqlComplaint, $emp_id);
$stmt->execute();
$resultComplaint = $stmt->get_result();
$stmt->close();

// Close the database connection
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
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        button {
            padding: 10px 20px;
            margin: 10px;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .table_container {
            display: none;
        }
        .active {
            display: block;
        }
        .table_wrapper {
            width: 100%;
            margin: 20px auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            cursor: pointer;
        }
        th:hover {
            background-color: #ddd;
        }
        select {
            padding: 8px;
            margin-left: 20px;
        }
        label {
            margin-left: 20px;
        }
    </style>
</head>
<body>
    <h1>Track Request/complaint</h1>
    
    <!-- Buttons to toggle between Requests and Complaints -->
    <div style="text-align: center;">
        <button onclick="showTable('requests')">Show Requests</button>
        <button onclick="showTable('complaints')">Show Complaints</button>
    </div>

    <!-- Filter for Sorting -->
    <div style="text-align: center;">
        <label for="statusFilter">Sort by Status:</label>
        <select id="statusFilter" onchange="sortTable()">
            <option value="">--Select--</option>
            <option value="Pending">Pending</option>
            <option value="Approved">Approved</option>
            <option value="Rejected">Rejected</option>
            <option value="Solved">Solved</option>
        </select>
    </div>

    <!-- Requests Table -->
    <div class="table_container" id="requestsTable">
        <h2 style="text-align: center;">Requests</h2>
        <div class="table_wrapper">
            <table>
                <thead>
                    <tr>
                        <th onclick="sortColumn('requestBody', 0)">Request ID</th>
                        <th onclick="sortColumn('requestBody', 1)">Stock Name</th>
                        <th onclick="sortColumn('requestBody', 2)">Request Date</th>
                        <th onclick="sortColumn('requestBody', 3)">Quantity</th>
                        <th onclick="sortColumn('requestBody', 4)">Status</th>
                    </tr>
                </thead>
                <tbody id="requestBody">
                    <?php
                    if ($resultRequest->num_rows > 0) {
                        while ($row = $resultRequest->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["r_id"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["stock_name"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["request_date"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["quantity"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr> <td colspan='5'> No requests found </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Complaints Table -->
    <div class="table_container" id="complaintsTable">
        <h2 style="text-align: center;">Complaints</h2>
        <div class="table_wrapper">
            <table>
                <thead>
                    <tr>
                        <th onclick="sortColumn('complaintBody', 0)">Complaint ID</th>
                        <th onclick="sortColumn('complaintBody', 1)">Title</th>
                        <th onclick="sortColumn('complaintBody', 2)">Description</th>
                        <th onclick="sortColumn('complaintBody', 3)">Created At</th>
                        <th onclick="sortColumn('complaintBody', 4)">Location</th>
                        <th onclick="sortColumn('complaintBody', 5)">Status</th>
                    </tr>
                </thead>
                <tbody id="complaintBody">
                    <?php
                    if ($resultComplaint->num_rows > 0) {
                        while ($row = $resultComplaint->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["complaint_id"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["description"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["created_at"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["location"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr> <td colspan='6'> No complaints found </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Function to show the selected table (requests or complaints)
        function showTable(tableId) {
            const requestsTable = document.getElementById("requestsTable");
            const complaintsTable = document.getElementById("complaintsTable");

            // Hide both tables first
            requestsTable.classList.remove("active");
            complaintsTable.classList.remove("active");

            // Show the selected table
            if (tableId === 'requests') {
                requestsTable.classList.add("active");
            } else if (tableId === 'complaints') {
                complaintsTable.classList.add("active");
            }
        }

        // Function to sort the visible table by the selected status
        function sortTable() {
            const filterValue = document.getElementById("statusFilter").value.toLowerCase();
            let rows, tableBody;

            // Check which table is currently visible
            if (document.getElementById("requestsTable").classList.contains("active")) {
                tableBody = document.getElementById("requestBody");
            } else if (document.getElementById("complaintsTable").classList.contains("active")) {
                tableBody = document.getElementById("complaintBody");
            }

            rows = tableBody.getElementsByTagName("tr");

            for (let i = 0; i < rows.length; i++) {
                let td = rows[i].getElementsByTagName("td")[4]; // Status is the 5th column (index 4)
                if (td) {
                    let status = td.textContent || td.innerText;
                    if (filterValue === "" || status.toLowerCase().includes(filterValue)) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        }

        // Function to sort by column
        function sortColumn(tableBodyId, colIndex) {
            let tableBody = document.getElementById(tableBodyId);
            let rows = Array.from(tableBody.getElementsByTagName("tr"));
            let sortedRows = rows.sort((a, b) => {
                let aText = a.getElementsByTagName("td")[colIndex].textContent.trim().toLowerCase();
                let bText = b.getElementsByTagName("td")[colIndex].textContent.trim().toLowerCase();
                return aText.localeCompare(bText);
            });

            // Reorder the rows in the table
            for (let row of sortedRows) {
                tableBody.appendChild(row);
            }
        }
    </script>
</body>
</html>
