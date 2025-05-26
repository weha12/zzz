<?php
session_start();
include('connect.php');
include('mail.php'); // Include the mail function for sending OTP

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password != $confirm_password) {
        echo "Passwords do not match!";
        exit();
    }

    // Generate a random 6-digit OTP
    $otp = mt_rand(100000, 999999);

    // Save the OTP in the database
    $sql = "INSERT INTO otp (email, otp_code) VALUES ('$email', '$otp')";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['email'] = $email; // Store email in session for verification
        $_SESSION['new_password'] = $new_password; // Temporarily store the new password
        sendOtpEmail($email, $otp);  // Call function in mail.php to send OTP

        echo "OTP has been sent to your email: $otp"; // Display OTP for testing purposes
        header("Location: verify_otp1.php");
        exit();
    } else {
        echo "Error saving OTP: " . $conn->error;
    }
}
?>
