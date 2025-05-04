<?php
session_start();

include 'db_connection.php';

if (isset($_GET['download'])) {
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    $reportType = isset($_GET['report_type']) ? $_GET['report_type'] : '';

    // Prepare base SQL queries
    $sqlComponent = "SELECT employee.name AS employee_name, stock.category, stock.stock_name, 
                            issue_component.quantity, issue_component.issue_date, 
                            issue_component.return_date, employee.job_role
                     FROM issue_component 
                     JOIN employee ON issue_component.emp_id = employee.emp_id
                     JOIN stock ON issue_component.stock_id = stock.stock_id 
                     WHERE 1 = 1";

    $sqlRejected = "SELECT request_issue.r_id, employee.name AS employee_name, 
                           stock.category, stock.stock_name, request_issue.quantity, request_issue.status
                    FROM request_issue
                    JOIN employee ON request_issue.emp_id = employee.emp_id
                    JOIN stock ON request_issue.stock_id = stock.stock_id
                    WHERE request_issue.status = 'Rejected'";

    $sqlRejectedReg = "SELECT register.register_id AS register_id, register.name, register.gender, 
                              register.contact_number, register.status 
                       FROM register
                       WHERE register.status = 'Rejected'";

    // Filter the report based on the report type
    if ($reportType == 'issued') {
        $sqlComponent .= " AND issue_component.return_date IS NULL"; // Issued items
    } elseif ($reportType == 'returned') {
        $sqlComponent .= " AND issue_component.return_date IS NOT NULL"; // Returned items
    }

    // Apply date range filter for issue_component
    if ($startDate && $endDate) {
        $sqlComponent .= " AND issue_component.issue_date BETWEEN ? AND ?";
    }

    // Prepare statements based on the report type
    if ($reportType === 'rejected') {
        $stmt = $con->prepare($sqlRejected);
    } elseif ($reportType === 'rejected_register') {
        $stmt = $con->prepare($sqlRejectedReg);
    } else {
        $stmt = $con->prepare($sqlComponent);
        if ($startDate && $endDate) {
            $stmt->bind_param("ss", $startDate, $endDate);
        }
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // CSV generation
    if ($result->num_rows > 0) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=report.csv');
        $output = fopen('php://output', 'w');

        // Dynamically set the header based on the report type
        if ($reportType === 'issued' || $reportType === 'returned') {
            fputcsv($output, ['Employee Name', 'Category', 'Stock Name', 'Quantity', 'Issue Date', 'Return Date', 'Job Role']);
        } elseif ($reportType === 'rejected') {
            fputcsv($output, ['Employee Name', 'Category', 'Stock Name', 'Quantity', 'Status']);
        } elseif ($reportType === 'rejected_register') {
            fputcsv($output, ['Register ID', 'Name', 'Gender', 'Contact Number', 'Status']); // Fix: Correct column header
        }

        // Output the data
        while ($row = $result->fetch_assoc()) {
            if ($reportType === 'issued' || $reportType === 'returned') {
                fputcsv($output, [
                    $row['employee_name'],
                    $row['category'],
                    $row['stock_name'],
                    $row['quantity'],
                    $row['issue_date'],
                    $row['return_date'] ?? 'N/A',
                    $row['job_role']
                ]);
            } elseif ($reportType === 'rejected') {
                fputcsv($output, [
                    $row['employee_name'],
                    $row['category'],
                    $row['stock_name'],
                    $row['quantity'],
                    $row['status']
                ]);
            } elseif ($reportType === 'rejected_register') {
                fputcsv($output, [
                    $row['register_id'],  // Fix: Correct column name
                    $row['name'],
                    $row['gender'],
                    $row['contact_number'],
                    $row['status']
                ]);
            }
        }

        fclose($output);
        exit();
    } else {
        echo "No data available to download.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Generate Report</title>
    <link rel="stylesheet" href="forms.css"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        h2 {
            text-align: center;
            margin-top: 20px;
        }
        .form-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 80vh;
        }
        .form-box {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="date"], select {
            padding: 10px;
            margin-bottom: 20px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .download-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #3498db;
            color: white;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .download-btn:hover {
            background-color: #2980b9;
        }
    </style>
    <script>
        function validateForm() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const reportType = document.getElementById('report_type').value;

            if (!startDate || !endDate || !reportType) {
                alert("Please select start date, end date, and report type.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <h2>Generate Issue, Return & Rejected Report</h2>
    <div class="form-container">
        <div class="form-box">
            <form method="get" action="" onsubmit="return validateForm();">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" required>

                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" required>

                <label for="report_type">Report Type:</label>
                <select id="report_type" name="report_type" required>
                    <option value="">Select Report Type</option>
                    <option value="issued">Issued Components</option>
                    <option value="returned">Returned Components</option>
                    <option value="rejected">Rejected Requests</option>
                    <option value="rejected_register">Rejected Registrations</option> <!-- New Option -->
                </select>

                <button type="submit" name="download" value="1" class="download-btn">Download CSV Report</button>
            </form>
        </div>
    </div>
</body>
</html>
