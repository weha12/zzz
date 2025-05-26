<?php
include('connect.php');

if (isset($_POST['otp']) && isset($_POST['email'])) {
    $otp = $_POST['otp'];
    $email = $_POST['email'];

    // Check if OTP matches
    $sql = "SELECT * FROM users WHERE email = '$email' AND otp = '$otp' AND status = 'unverified'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Update status to 'verified'
        $update_sql = "UPDATE users SET status = 'verified' WHERE email = '$email' AND otp = '$otp'";
        if ($conn->query($update_sql) === TRUE) {
            echo "Your account has been verified successfully! You can now log in.";
        } else {
            echo "Error updating status.";
        }
    } else {
        echo "Invalid OTP or account already verified.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    
</head>
<body>
    <h1>Enter OTP Sent to Your Email</h1>
    <form method="POST" action="verify_otp.php">
        <input type="email" name="email" placeholder="Your Email" value="<?php echo isset($_GET['email']) ? $_GET['email'] : ''; ?>" required />
        <input type="text" name="otp" placeholder="Enter OTP" required />
        <button type="submit">Verify OTP</button>
    </form>
</body>
</html>
