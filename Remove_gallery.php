<?php
session_start();

include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['gallery'])) {
        $gallery_id = $_POST['gallery'];

        // Prepare and execute the deletion query
        $sql = "DELETE FROM location WHERE location_id = ?";
        $stmt = $con->prepare($sql);
        if (!$stmt) {
            die("Unable to prepare the statement: " . $con->error);
        }

        $stmt->bind_param("i", $gallery_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "Gallery removed successfully.";
        } else {
            echo "No gallery found with the provided ID.";
        }

        $stmt->close();
    } else {
        echo "No gallery selected.";
    }
} else {
    echo "Invalid request method.";
}

$con->close();
?>
