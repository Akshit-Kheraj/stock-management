

<?php
session_start();

include 'db_connection.php';



if (isset($_SESSION['Login']) && $_SESSION['Login'] === true) {
    $user_name = $_SESSION['user_id'];

    // Retrieve employee information
    $sql = "SELECT name, job_role FROM employee WHERE user_id = ?";
    $stmt = statement($con, $sql, $user_name);

    $stmt->execute();
    $stmt->bind_result($name, $job);
    $stmt->fetch();
    $stmt->close();
} else {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['menu'])) {
        $selected_menu = $_POST['menu'];

        switch ($selected_menu) {
            case "managerLocation":
                header("Location: addManagerLocation.php");
                break;
            case "profile":
                header("Location: profile.php");
                break;
            case "change_password":
                header("Location: change_password.php");
                break;
            case "issue_component":
                header("Location: issue_component.php");
                break;
            case "add_gallery":
                header("Location: add_gallery.php");
                break;
            case "remove_gallery":
                header("Location: remove_gallery.php");
                break;
            case "add_employee":
                header("Location: add_employee.php");
                break;
            case "logout":
                session_destroy();
                header("Location: index.php");
                exit();
                
            case "View Employee":
                header("Location: employee_details.php");
                exit();
            default:
                echo "Please select a valid option.";
                break;
        }
        exit();
    }

    if (isset($_POST['inputData'])) {
        $inputdata = $_POST['inputData'];

        switch ($inputdata) {
            case 'add_gallery':
                header("Location: add_gallery.php");
                break;
            case 'add_stock':
                header("Location: add_stock.php");
                break;
            case 'update_gallery':
                header("Location: permanentIssue.php");
                break;
            default:
                echo "The page you had selected is not available on the server.";
                break;
        }
        exit();
    }
}

//retrieve location for delete 
$sql = "SELECT * FROM location";
$result = $con->query($sql);

$sqlTask = "SELECT 
    employee.name,
    employee.emp_id,
    assign_task.deadline,
    task.task_name,
    location.location_name,
    assign_task.status,
    assign_task.assign_date,
    assign_task.assign_id
FROM 
    assign_task
JOIN employee ON assign_task.emp_id = employee.emp_id
JOIN task ON assign_task.task_id = task.task_id
JOIN location ON assign_task.location_id = location.location_id;
";

$stmt = statement($con,$sqlTask);
$stmt->execute();
$resultTaskTable =$stmt->get_result();
$stmt->close();


$con->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="emp.css?v=<?php echo time(); ?>">
    <style>
        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Styling for the buttons */
        .btn {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0056b3;
        }
       
    </style>
</head>

<body>
    <header>
        <div class="logo">
            <img src="sciencCenterLogo.png.jpg" alt="Science center logo">
        </div>
        
        <div class="user-profile">
            <p><span><?php echo "Name: ".htmlspecialchars($name); ?><br><?php echo "Job: ". htmlspecialchars($job); ?></span></p>
        </div>
    </header>

    <div class="strip">
        <i class="fa-solid fa-bell"></i>
        <a href="issue_component.php">View Request</a>
        <a href="complaint.php">View Complaint</a>
        <a href="assign_task.php">Assign Task</a>
        <a href="check_stock.php">Check Stock Availability</a>
        <a href="return_component.php">Return Component</a>
        <form action="" method="POST">
            <i class="fa-solid fa-bars">
                <select id="sc" name="menu" onchange="this.form.submit()">
                    <option value="profile">Profile</option>
                    <option value="managerLocation">Manager Location</option>
                    <option value="change_password">Change Password</option>
                    <option value="issue_component">Issue Component</option>
                    <option value="add_gallery">Add Gallery</option>
                    <option value="View Employee">View Employee</option>
                    <option value="add_employee">Add New Employee</option>
                    <option value="logout">Log Out</option>
                </select>
            </i>
        </form>
    </div>

    <hr>

    <div class="table-container" align="center">
        <p><b>Task Checking Panel</b></p>
        <table border="1">
            <tr>
                <th><b>Employee Id</b></th>
                <th><b>Employee Name</b></th>
                <th><b>Task</b></th>
                <th><b>Task Location</b></th>
                <th><b>Asign Date</b></th>
                <th><b>Deadline</b></th>
                <th>Status</th>
            </tr>

            <?php
      if ($resultTaskTable->num_rows > 0) {
          while ($row = $resultTaskTable->fetch_assoc()) {
              echo "<tr>";
              echo "<td>" . htmlspecialchars($row["emp_id"]) . "</td>";
              echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
              echo "<td>" . htmlspecialchars($row["task_name"]) . "</td>";
              echo "<td>" . htmlspecialchars($row["location_name"]) . "</td>";
              echo "<td>" . htmlspecialchars($row["assign_date"]) . "</td>";
              echo "<td>" . htmlspecialchars($row["deadline"]) . "</td>";
              echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
              echo "</tr>";
          }
        }
            ?>

           
        </table>
    </div>

    <hr>

    <div class="bar">
        <a style="color:black;" href="addEvent.php">Add Event</a>
        <a style="color:black;" href="forms.php">Registration Form</a>
        <a style="color:black;" href="generate_report.php">Generate Report</a>
        <a style="color:black;" href="#" id="updateDatabaseButton">Update Database</a>
        <a style="color:black;" href="#" id="removeGalleryButton">Remove Gallery</a>
        <a style="color:black;" href="taskStatus.php">Check Task Status</a>
    </div>

    <hr>

    <!-- Update Database Modal -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Update Database</h2>
            <form id="updateForm" action="" method="post">
                <label for="inputData">Enter Data:</label>
                <select name="inputData">
                    <option value="add_gallery">Add Gallery</option>
                    <option value="update_gallery">Update Existing Gallery Stock Data</option>
                    <option value="add_stock">Add New Stock</option>
                </select>
                <button type="submit" class="btn">Submit</button>
            </form>
        </div>
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

        <!-- Quick Links Section -->
        <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="about.php">About Us</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="support.php">Support</a></li>
            </ul>
        </div>

        <!-- Newsletter Subscription Form -->
        <div class="footer-section">
            <h4>Subscribe to our Newsletter</h4>
            <form id="newsletter-form" action="subscribe.php" method="POST">
                <input type="email" name="email" placeholder="Enter your email" required>
                <button type="submit" class="btn">Subscribe</button>
            </form>
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


    <!-- Remove Gallery Modal -->
    <div id="removeGalleryModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Remove Gallery</h2>
            <form id="removeGalleryForm" action="remove_gallery.php" method="post">
                <label for="gallery">Select Gallery to Remove:</label>
                <?php 
                if($result->num_rows > 0) {
                    echo "<select name='gallery' required>";
                    while($rows = $result->fetch_assoc()) {
                       echo "<option value='" . htmlspecialchars($rows['location_id']) . "'>" . htmlspecialchars($rows['location_name']) . "</option>";
                    }
                    echo "</select>";
                } else {
                    echo "<p>Unable to find the location</p>";
                }
                ?>
                <button type="submit" class="btn">Submit</button>
            </form>
        </div>
    </div>

    <script>
        // Get the modals
        var updateModal = document.getElementById("updateModal");
        var removeGalleryModal = document.getElementById("removeGalleryModal");

        // Get the buttons that open the modals
        var updateDatabaseButton = document.getElementById("updateDatabaseButton");
        var removeGalleryButton = document.getElementById("removeGalleryButton");

        // Get the <span> elements that close the modals
        var closeUpdateModal = updateModal.getElementsByClassName("close")[0];
        var closeRemoveGalleryModal = removeGalleryModal.getElementsByClassName("close")[0];

        // When the user clicks the buttons, open the modals
        updateDatabaseButton.onclick = function() {
            updateModal.style.display = "block";
        }

        removeGalleryButton.onclick = function() {
            removeGalleryModal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modals
        closeUpdateModal.onclick = function() {
            updateModal.style.display = "none";
        }

        closeRemoveGalleryModal.onclick = function() {
            removeGalleryModal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == updateModal) {
                updateModal.style.display = "none";
            }
            if (event.target == removeGalleryModal) {
                removeGalleryModal.style.display = "none";
            }
        }
    </script>
   
</body>
</html>
