<?php
session_start();
include('connect.php');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f9fafb;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .forgot-password-container {
            background: #ffffff;
            padding: 40px 30px;
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 26px;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="email"],
        input[type="password"] {
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fefefe;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #3a86ff;
            outline: none;
        }

        button {
            padding: 12px;
            background-color: #3a86ff;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #2c6ce1;
        }
    </style>
 
</head>
<body>
    <div class="forgot-password-container">
        <h2>Forgot Password</h2>
        <form action="process_forgot_password.php" method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="password" name="new_password" placeholder="Enter new password" required>
            <input type="password" name="confirm_password" placeholder="Confirm new password" required>
            <button type="submit">Send OTP</button>
        </form>
    </div>
</body>
</html>
