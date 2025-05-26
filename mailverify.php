<?php
include('connect.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token exists in the database and the user is unverified
    $sql = "SELECT * FROM users WHERE verification_token = '$token' AND status = 'unverified'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Update the user's status to 'verified'
        $update_sql = "UPDATE users SET status = 'verified' WHERE verification_token = '$token'";

        if ($conn->query($update_sql) === TRUE) {
            echo "Your account has been verified successfully!";
        } else {
            echo "Error updating status: " . $conn->error;
        }
    } else {
        echo "Invalid or expired token.";
    }
} else {
    echo "No token provided!";
}

$conn->close();
?>