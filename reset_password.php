<?php
session_start();
include('connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_SESSION['new_password'];
    $email = $_SESSION['email'];

    // Update password in register table
    $sql = "UPDATE users SET password='$new_password' WHERE email='$email'";
    if ($conn->query($sql) === TRUE) {
        echo "Password changed successfully.";
        session_unset(); // Clear session variables
        session_destroy();
        header("Location: login.php");
        exit();
    } else {
        echo "Error updating password: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <style>
        /* css1.css */

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f2f7fb;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

.reset-password-container {
    background: white;
    padding: 2.5rem 3rem;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    max-width: 400px;
    width: 100%;
    text-align: center;
}

.reset-password-container h2 {
    margin-bottom: 1.5rem;
    color: #333;
    font-weight: 600;
    font-size: 1.8rem;
}

.reset-password-container form button {
    width: 100%;
    padding: 0.85rem;
    background-color: #007bff;
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 1.1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.reset-password-container form button:hover {
    background-color: #0056b3;
}

    </style>
</head>
<body>
    <div class="reset-password-container">
        <h2>New Password</h2>
        <form method="post">
            <button type="submit">Confirm Password Change</button>
        </form>
    </div>
</body>
</html>
