<?php
session_start();
include 'db_connection.php';  // Include the database connection file

// Check if the user is logged in
if (!isset($_SESSION['Login']) || $_SESSION['Login'] !== true) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit();
}

$message = "";  // Message to show success or errors

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the gallery name is provided
    if (isset($_POST['gallery_name']) && !empty(trim($_POST['gallery_name']))) {
        $gallery_name = trim($_POST['gallery_name']);

        // Insert the gallery name into the location table
        $sql = "INSERT INTO location (location_name) VALUES (?)";
        if ($stmt = $con->prepare($sql)) {
            $stmt->bind_param('s', $gallery_name);
            if ($stmt->execute()) {
                // Get the last inserted gallery ID
                $gallery_id = $con->insert_id;
                $message = "Gallery added successfully! Gallery ID: " . $gallery_id;
            } else {
                $message = "Error: Could not insert gallery.";
            }
            $stmt->close();
        } else {
            $message = "Error: Could not prepare the SQL statement.";
        }
    } else {
        $message = "Please enter a valid gallery name.";
    }
}
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Gallery</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .message {
            margin-top: 20px;
            font-size: 1.2em;
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Add a New Gallery</h2>
    <form method="post" action="">
        <label for="gallery_name">Gallery Name:</label>
        <input type="text" name="gallery_name" id="gallery_name" required>
        <input type="submit" value="Add Gallery">
    </form>

    <!-- Display message -->
    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'Error') !== false ? 'error' : '' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
