<?php
session_start();

// Database connection
include 'db_connection.php';

// Check login status
if (!isset($_SESSION['Login']) || $_SESSION['Login'] !== true) {
    header("Location: index.php");
    exit;
}

// Fetch user details and tasks
if (isset($_SESSION['Login']) && $_SESSION['Login'] === true) {
    $user_name = $_SESSION['user_id'];
    
    // Fetch employee details
    $sql = "SELECT name, job_role, emp_id FROM employee WHERE user_id = ?";
    $stmt = statement($con, $sql, $user_name);
    $stmt->execute();
    $stmt->bind_result($name, $job, $emp_id);
    $stmt->fetch();
    $stmt->close();

    //fetching the manager location or gallery
    $sqlGallery = "
    SELECT manager_gallery.location_id, location.location_name
    FROM manager_gallery
    INNER JOIN location ON manager_gallery.location_id = location.location_id
    WHERE manager_gallery.emp_id = ?
    ";

    $stmt = statement($con,$sqlGallery,$emp_id);
    $stmt->execute();
    $stmt->bind_result($location_id,$location_name);
    $stmt->fetch();
    $stmt->close();
    
    
    // Fetch tasks assigned to employee
    $sqlTask = "
        SELECT 
            task.task_name,
            task.task_type,
            assign_task.assign_id,
            Location.location_name,
            assign_task.deadline,
            assign_task.status
        FROM 
            assign_task
        INNER JOIN 
            task ON assign_task.task_id = task.task_id
        INNER JOIN 
            Location ON assign_task.location_id = Location.location_id
        WHERE 
            assign_task.emp_id = ?
    ";
    $stmt = statement($con, $sqlTask, $emp_id);
    $stmt->execute();
    $Task = $stmt->get_result();  // Storing task results
    $stmt->close();
}

// Fetch upcoming events from the events table
$sqlEvents = "SELECT event_name, event_date, event_location, event_description FROM events ORDER BY event_date DESC LIMIT 10";
$events = $con->query($sqlEvents);

// Handle the form submission for status update
if (isset($_POST['update_status'])) {
    $statuses = $_POST['status'];
    $assign_ids = $_POST['assign_id'];

    // Update status for each task
    for ($i = 0; $i < count($statuses); $i++) {
        $status = $statuses[$i];
        $assign_id = $assign_ids[$i];

        $sqlUpdate = "UPDATE assign_task SET status = ? WHERE assign_id = ?";
        $stmt = statement($con, $sqlUpdate, $status, $assign_id);
        $stmt->execute();
        $stmt->close();
    }

    // Refresh the page to show updated status
    header("Location: " . $_SERVER['PHP_SELF']);
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
  <title>Manager Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <link rel="stylesheet" href="emp.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="manager.css?v=<?php echo time(); ?>" />
  
</head>
<body>
<header>
  <div class="logo">
    <img src="sciencCenterLogo.png.jpg" alt="Science Center Logo">
  </div>
  <div class="user-profile">
            <p><span><?php echo "Name: ".htmlspecialchars($name); ?><br><?php echo "Job: ". htmlspecialchars($job); ?></span></p>
        </div>
</header>

<nav class="strip">
    <a href="request_component.php">Request for Component</a>
    <a href="raise_complaint.php">Raise Complaint</a>
    <a href="trackRequest.php">Track Request</a>
    <a href="check_stock_gallery.php">Check Stock</a>
    
    <div class="dropdown">
      <i class="fa-solid fa-bars"></i>
      <div class="dropdown-content">
        <a href="profile.php">Profile</a>
        <a href="taskStatusGallery.php">Gallery Task Status</a>
        <a href="change_password.php">Change Password</a>
        <a href="logout.php">Log out</a>
      </div>
    </div>
</nav>

<hr>

<!-- Task Panel -->
<main class="table-container" align="center">
  <p><b>Task Panel</b></p>
  <form method="POST" action="">
    <table border="1">
      <tr>
        <th>Task Name</th>
        <th>Task Type</th>
        <th>Location</th>
        <th>Deadline</th>
        <th>Status</th>
        <th>Change Status</th>
      </tr>

      <?php
      if ($Task->num_rows > 0) {
          while ($row = $Task->fetch_assoc()) {
              echo "<tr>";
              echo "<td>" . htmlspecialchars($row["task_name"]) . "</td>";
              echo "<td>" . htmlspecialchars($row["task_type"]) . "</td>";
              echo "<td>" . htmlspecialchars($row["location_name"]) . "</td>";
              echo "<td>" . htmlspecialchars($row["deadline"]) . "</td>";
              echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
              echo "<td>
                      <input type='hidden' name='assign_id[]' value='" . $row['assign_id'] . "' />
                      <select name='status[]'>
                          <option value='Pending' " . ($row['status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                          <option value='In Progress' " . ($row['status'] == 'In Progress' ? 'selected' : '') . ">In Progress</option>
                          <option value='Completed' " . ($row['status'] == 'Completed' ? 'selected' : '') . ">Completed</option>
                      </select>
                    </td>";
              echo "</tr>";
          }
      } else {
          echo "<tr><td colspan='6'>No results found</td></tr>";
      }
      ?>
    </table>
    <br>
    <button type="submit" name="update_status">Update Status</button>
  </form>
</main>

<h4><p><b>Upcoming Events</b></p></h4>
<div class="event-container">
  <?php if ($events->num_rows > 0): ?>
    <?php while ($event = $events->fetch_assoc()): ?>
      <div class="event">
        <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
        <p>Date: <?php echo htmlspecialchars($event['event_date']); ?></p>
        <p>Location: <?php echo htmlspecialchars($event['event_location']); ?></p>
        <p><?php echo htmlspecialchars($event['event_description']); ?></p>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No upcoming events</p>
  <?php endif; ?>
</div>

<footer>
    <div class="footer-container">
        <!-- Website Information and Contact Info -->
        <div class="footer-section">
            <p>
                © 2024–Present Regional Science Center, Rajkot. This platform is designed for managing stock, employee tasks, and gallery updates. 
                For any inquiries, please contact us at 
                <a href="mailto:info@rscrajkot.gov.in">info@rscrajkot.gov.in</a> or call us at 
                <a href="tel:+919876543210">+91 9876543210</a>.
            </p>
        </div>

     

        <!-- Social Media Icons -->
        <div class="footer-section social-icons">
            <h4>Follow Us</h4>
            <a href="https://facebook.com" target="_blank"><i class="fab fa-facebook"></i></a>
            <a href="https://twitter.com" target="_blank"><i class="fab fa-twitter"></i></a>
            <a href="https://instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="https://linkedin.com" target="_blank"><i class="fab fa-linkedin"></i></a>
        </div>

        <!-- Back to Top Button -->
        <div class="back-to-top">
            <a href="#top"><i class="fas fa-arrow-up"></i> Back to Top</a>
        </div>
    </div>

    <!-- Decorative Divider -->
    <div class="divider">
        <p>Powered by the Regional Science Center IT Team | <i class="fas fa-laptop-code"></i> <i class="fas fa-wrench"></i></p>
    </div>
</footer>
</body>
</html>
