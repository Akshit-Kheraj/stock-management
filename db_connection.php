<?php

$server = "localhost:3306";
$user = "root";
$password = "";
$dbs = "science_center";

$con = new mysqli($server, $user, $password, $dbs);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Function for preparing statements
function statement($con, $sql, ...$parameter) {
    $stmt = $con->prepare($sql);
    if (!$stmt) {
        die("Unable to proceed with this action: " . $con->error);
    }

    if ($parameter) {
        $types = '';

        foreach ($parameter as $param) {
            if (is_int($param)) {
                $types .= 'i'; // integer
            } elseif (is_double($param)) {
                $types .= 'd'; // double
            } elseif (is_string($param)) {
                $types .= 's'; // string
            } else {
                throw new Exception("Unsupported parameter type");
            }
        }

        if ($types) {
            $stmt->bind_param($types, ...$parameter);
        }
    }

    return $stmt;
}

?>