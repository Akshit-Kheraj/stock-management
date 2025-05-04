<?php
session_start();
include 'db_connection.php';

if (isset($_SESSION['Login']) && $_SESSION['Login'] === true) {
    $user_name = $_SESSION['user_id'];
    $sqljob = "SELECT job_role FROM employee WHERE user_id = ?";
    $stmt = $con->prepare($sqljob);
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $stmt->bind_result($job);
    $stmt->fetch();
    $stmt->close();

    if ($job === 'Admin') {
        // Fetch employee data
        $employee = "SELECT emp_id, name FROM employee";
        $result = $con->query($employee);
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }

        // Fetch location data
        $location = "SELECT location_id, location_name FROM location";
        $resultLocation = $con->query($location);
        $locations = [];
        while ($row = $resultLocation->fetch_assoc()) {
            $locations[] = $row;
        }

        // Fetch task data
        $task = "SELECT task_id, task_name FROM task"; // Assuming you have a 'tasks' table
        $resultTask = $con->query($task);
        $tasks = [];
        while ($row = $resultTask->fetch_assoc()) {
            $tasks[] = $row;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $emp_id = $_POST['emp_id'];
            $task_id = $_POST['task_name'];
            $assign_date = date('Y-m-d H:i:s');
            $deadline = $_POST['deadline'];

            // Insert into assign_task table
            $stmt = $con->prepare("INSERT INTO assign_task (emp_id, task_id, assign_date, deadline, location_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $emp_id, $task_id, $assign_date, $deadline, $_POST['location']);
            $stmt->execute();
            $stmt->close();

            echo "Task assigned successfully!";
        }
    } else {
        header("Location: index.php");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Assign Task</title>
    <link rel="stylesheet" href="assign_task.css?v=<?php echo time(); ?>">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const employees = <?php echo json_encode($employees); ?>;
            const locations = <?php echo json_encode($locations); ?>;
            const tasks = <?php echo json_encode($tasks); ?>;

            const empIdInput = document.getElementById('emp_id');
            const empNameInput = document.getElementById('emp_name');
            const empNameSelect = document.getElementById('emp_name_select');
            const locationSelect = document.getElementById('location');
            const taskSelect = document.getElementById('task_name');
            const searchInput = document.getElementById('search_employee');

            // Function to update Employee Name based on Employee ID
            function updateEmployeeName() {
                const empId = empIdInput.value.trim();
                const employee = employees.find(emp => emp.emp_id == empId);
                empNameInput.value = employee ? employee.name : '';
            }

            // Function to update Employee ID based on selected Employee Name
            function updateEmployeeIdFromSelect() {
                const selectedName = empNameSelect.value;
                const employee = employees.find(emp => emp.name === selectedName);
                empIdInput.value = employee ? employee.emp_id : '';
            }

            // Populate the dropdown with employee names
            function populateEmployeeDropdown() {
                empNameSelect.innerHTML = '<option value="">Select Employee</option>'; // Reset dropdown
                employees.forEach(emp => {
                    const option = document.createElement('option');
                    option.value = emp.name;
                    option.textContent = emp.name;
                    empNameSelect.appendChild(option);
                });
            }

            // Populate the location dropdown
            locations.forEach(loc => {
                const option = document.createElement('option');
                option.value = loc.location_id;
                option.textContent = loc.location_name;
                locationSelect.appendChild(option);
            });

            // Populate the task dropdown
            tasks.forEach(task => {
                const option = document.createElement('option');
                option.value = task.task_id; // Assuming you have task_id
                option.textContent = task.task_name;
                taskSelect.appendChild(option);
            });

            // Search functionality for employee names
            searchInput.addEventListener('input', function() {
                const searchValue = this.value.toLowerCase();
                empNameSelect.innerHTML = '<option value="">Select Employee</option>'; // Reset dropdown
                employees.forEach(emp => {
                    if (emp.name.toLowerCase().includes(searchValue)) {
                        const option = document.createElement('option');
                        option.value = emp.name;
                        option.textContent = emp.name;
                        empNameSelect.appendChild(option);
                    }
                });
            });

            // Event listeners
            empIdInput.addEventListener('input', updateEmployeeName);
            empNameSelect.addEventListener('change', updateEmployeeIdFromSelect);
            populateEmployeeDropdown(); // Populate on load
        });
    </script>
</head>
<body>
    <h2><b>Assign Task</b></h2>
    <form action="" method="post">
        <label for="emp_id">Employee ID</label>
        <input type="text" name="emp_id" id="emp_id" placeholder="Enter Employee ID" required>

        <label for="search_employee">Search Employee Name</label>
        <input type="text" id="search_employee" placeholder="Search Employee Name">

        <label for="emp_name_select">Employee Name</label>
        <select name="emp_name_select" id="emp_name_select">
            <option value="">Select Employee</option>
            <!-- Options will be populated by JavaScript -->
        </select>

        <label for="task_name">Task Name</label>
        <select name="task_name" id="task_name" required>
            <option value="">Select Task</option>
            <!-- Options will be populated by JavaScript -->
        </select>

        <label for="deadline">Deadline</label>
        <input type="date" name="deadline" id="deadline" required>

        <label for="location">Location</label>
        <select name="location" id="location" required>
            <option value="">Select Location</option>
            <!-- Options will be populated by JavaScript -->
        </select>

        <div class="button-group">
            <button type="button" onclick="window.location.href='index.html';">Cancel</button>
            <button type="submit">Save</button>
        </div>
    </form>
</body>
</html>
