<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event</title>
    <link rel="stylesheet" href="addEvent.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="form-container">
    <h2>Add New Event</h2>
    <form id="eventForm" action="" method="POST" enctype="multipart/form-data">
        <label for="event_name">Event Name</label>
        <input type="text" id="event_name" name="event_name" required>

        <label for="event_date">Event Date</label>
        <input type="date" id="event_date" name="event_date" required>

        <label for="event_location">Event Location</label>
        <input type="text" id="event_location" name="event_location">

        <label for="event_description">Event Description</label>
        <textarea id="event_description" name="event_description" rows="4"></textarea>

        <label for="event_poster">Event Poster (JPEG, PNG only)</label>
        <input type="file" id="event_poster" name="event_poster" accept="image/jpeg, image/png" required>

        <button type="submit">Submit Event</button>

        <div class="error" id="formErrors"></div>
    </form>
</div>

<script>
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        let formIsValid = true;
        let errors = [];
        let fileInput = document.getElementById('event_poster');
        let file = fileInput.files[0];

        // Check file type
        if (file) {
            let validTypes = ['image/jpeg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                formIsValid = false;
                errors.push('Only JPEG or PNG images are allowed.');
            }

            // Check file size (limit to 2MB)
            if (file.size > 2 * 1024 * 1024) {
                formIsValid = false;
                errors.push('File size must be less than 2MB.');
            }
        }

        if (!formIsValid) {
            e.preventDefault();
            document.getElementById('formErrors').innerHTML = errors.join('<br>');
        }
    });
</script>

</body>
</html>



<?php
// Define allowed MIME types and corresponding file extensions
$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.ms-excel' => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
   
];

// Ensure the 'uploads' directory exists
$uploadsDir = __DIR__ . '/uploads/';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true); // Creates the directory with the appropriate permissions
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_location = $_POST['event_location'];
    $event_description = $_POST['event_description'];

    // Handle file upload
    $file = $_FILES['event_poster'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];

    // Check for upload errors
    if ($fileError === 0) {
        // Check if MIME type is allowed
        if (array_key_exists($fileType, $allowedTypes)) {
            $fileExtension = $allowedTypes[$fileType]; // Get the correct file extension

            // Check file size (limit to 2MB)
            if ($fileSize <= 2 * 1024 * 1024) { // 2MB limit
                // Create a unique file name to prevent overwriting
                $newFileName = uniqid('', true) . '.' . $fileExtension;
                $fileDestination = $uploadsDir . $newFileName;

                // Move the uploaded file to the destination
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    // Insert event data into the database
                    $host = 'localhost:3306';
                    $db   = 'science_center';
                    $user = 'root';
                    $pass = '';
                    $charset = 'utf8mb4';
                    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
                    $options = [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ];

                    try {
                        $pdo = new PDO($dsn, $user, $pass, $options);
                    } catch (\PDOException $e) {
                        throw new \PDOException($e->getMessage(), (int)$e->getCode());
                    }

                    $sql = "INSERT INTO events (event_name, event_date, event_location, event_description, event_poster)
                            VALUES (:event_name, :event_date, :event_location, :event_description, :event_poster)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':event_name' => $event_name,
                        ':event_date' => $event_date,
                        ':event_location' => $event_location,
                        ':event_description' => $event_description,
                        ':event_poster' => $newFileName // Store file name in the database
                    ]);

                    echo "Event successfully added!";
                } else {
                    echo "There was an error moving the uploaded file.";
                }
            } else {
                echo "File size must be less than 2MB.";
            }
        } else {
            echo "Invalid file type. Allowed types are JPEG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX.";
        }
    } else {
        echo "There was an error uploading the file.";
    }
}
?>

