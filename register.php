<?php
session_start(); // Start the session
include('connect.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:\xampp\htdocs\yyy\PHPMailer\PHPMailer\src\Exception.php';
require 'C:\xampp\htdocs\yyy\PHPMailer\PHPMailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\yyy\PHPMailer\PHPMailer\src\SMTP.php';


// Function to send the OTP email
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ohayotalaga@gmail.com'; // Your email
        $mail->Password = 'pmri dzeo iifb vmmy';  // Your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('ohayotalaga@gmail.com', 'View');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Account Verification';
        $mail->Body = "<p>Your OTP is: <b>$otp</b>.</p><p>Please enter this on the verification page to verify your account.</p>";

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

$message = ""; // Variable to hold feedback messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['register'])) {
        // reCAPTCHA validation
        $recaptcha_secret = '6Ldl1hErAAAAAMOsi2ZI2dFSmTAmEPS7O_z5yO6B'; // Correct the secret key
        $recaptcha_response = $_POST['g-recaptcha-response'];

        if (empty($recaptcha_response)) {
            $message = "Please verify that you are not a robot.";
        } else {
            // Verify the CAPTCHA response
            $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
            $response_keys = json_decode($response, true);

            if (intval($response_keys["success"]) !== 1) {
                $message = "reCAPTCHA verification failed. Please try again.";
            } else {
                // Registration logic
                $full_name = $_POST['full_name'];
                $email = $_POST['email'];
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];

                if ($password !== $confirm_password) {
                    $message = "Password and Confirm Password do not match.";
                } else {
                    $otp = rand(100000, 999999); // Generate a 6-digit OTP
                    $status = 'unverified'; // Initial status

                    // Insert user data into the database
                    $sql = "INSERT INTO users (full_name, email, password, otp, status) 
                            VALUES ('$full_name', '$email', '$password', '$otp', '$status')";

                    if ($conn->query($sql) === TRUE) {
                        sendOTP($email, $otp); // Send OTP email
                        $_SESSION['email'] = $email; // Store email in session for OTP verification
                        $_SESSION['otp_sent'] = true; // Indicate OTP sent
                        $message = "Registration successful! Please check your email for the OTP.";
                    } else {
                        $message = "Error: " . $sql . "<br>" . $conn->error;
                    }
                }
            }
        }
    } elseif (isset($_POST['verify'])) {
        // OTP verification logic
        if (isset($_SESSION['email'])) {
            $email = $_SESSION['email'];
            $otp = $_POST['otp'];

            $sql = "SELECT * FROM users WHERE email = '$email' AND otp = '$otp' AND status = 'unverified'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $update_sql = "UPDATE users SET status = 'verified', otp = NULL WHERE email = '$email'";
                if ($conn->query($update_sql) === TRUE) {
                    $message = "Your account has been verified successfully!";
                    unset($_SESSION['email'], $_SESSION['otp_sent']); // Clear session data
                    header("Location: login.php");
                    exit();
                } else {
                    $message = "Error updating status: " . $conn->error;
                }
            } else {
                $message = "Invalid OTP or account already verified.";
            }
        } else {
            $message = "Session expired. Please register again.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration and Verification</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        function enableSubmitBtn(){
            document.getElementById("mySubmitBtn").disabled = false;
        }
    </script>
    <style>
     body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f0f2f5;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

.container {
    width: 100%;
    max-width: 480px;
    background-color: #fff;
    padding: 40px 30px;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
}

h2 {
    text-align: center;
    margin-bottom: 30px;
    font-size: 24px;
    color: #333;
}

.input-group {
    margin-bottom: 20px;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

label {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
    width: 70%;
    text-align: left;
}

input {
    width: 70%;
    padding: 12px 15px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background-color: #f9f9f9;
    color: #333;
    transition: border-color 0.3s ease;
}

input:focus {
    border-color: #007bff;
    outline: none;
    background-color: #fff;
}

.g-recaptcha {
    display: flex;
    justify-content: center;
    margin: 20px 0;
}

.button-group {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 20px;
}

button {
    padding: 12px 25px;
    width: 140px;
    font-size: 16px;
    font-weight: 600;
    border: none;
    border-radius: 6px;
    background-color: #007bff;
    color: #fff;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #0056b3;
}

/* Shared button style */
button, .back-button {
    padding: 5px 10px;
    width: 100px;
    font-size: 16px;
    font-weight: 600;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

/* Main button (e.g. Register) */
button {
    background-color: #007bff;
    color: #fff;
}

button:hover {
    background-color: #0056b3;
}

/* Back button variation */
.back-button {
    background-color: #f3f3f3;
    color: #333;
}

.back-button:hover {
    background-color: #ddd;
}

        

        .notification {
            margin-bottom: 20px;
            padding: 12px;
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 5px;
            font-size: 14px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .input-group {
                margin-bottom: 20px;
            }

            button, .back-button {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2><?php echo isset($_SESSION['otp_sent']) ? "Verify Your Account" : "Create an Account"; ?></h2>

    <!-- Display Notification -->
    <?php if (!empty($message)): ?>
        <div class="notification">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Registration Form -->
    <?php if (!isset($_SESSION['otp_sent'])): ?>
        <form method="post">
            <div class="input-group">
                <label for="full-name">Full Name</label>
                <input type="text" id="full-name" name="full_name" required placeholder="Enter your full name">
            </div>

            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Create a password">
            </div>

            <div class="input-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm_password" required placeholder="Confirm your password">
            </div>

            <!-- reCAPTCHA -->
            <div class="g-recaptcha" data-sitekey="6Ldl1hErAAAAAMd7S7gsWw8UgAdTLMBYQQ5hhpAQ" data-callback="enableSubmitBtn"></div>

            <div class="button-group">
                <button type="submit" name="register" id="mySubmitBtn" disabled="disabled">Register</button>
                <button type="button" onclick="window.location.href='login.php'">Back to Login</button>
            </div>
        </form>
    <?php else: ?>
        <!-- OTP Verification Form -->
        <form method="post">
            <div class="input-group">
                <label for="otp">Enter OTP</label>
                <input type="text" id="otp" name="otp" required placeholder="Enter the OTP sent to your email">
            </div>

            <div class="button-group">
                <button type="submit" name="verify">Verify</button>
            </div>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
