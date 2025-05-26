<?php
session_start();
include('connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['email'])) {
        die("Email not found in session.");
    }

    $email = $_SESSION['email'];
    $entered_otp = trim($_POST['otp']); // Use trim() to avoid extra spaces

    // Query to check OTP from database
    $sql = "SELECT otp_code FROM otp WHERE email='$email' LIMIT 1";
    $result = $conn->query($sql);

    if (!$result) {
        die("Error executing query: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Debug: Display the OTP stored in the database and the entered OTP
        echo "Stored OTP: " . htmlspecialchars($row['otp_code']) . "<br>"; // For testing purposes
        echo "Entered OTP: " . htmlspecialchars($entered_otp) . "<br>"; // For testing purposes

        // Validate OTP by comparing stored OTP and entered OTP
        if (trim($row['otp_code']) == $entered_otp) {
            header("Location: reset_password.php"); // Redirect to password reset page
            exit();
        } else {
            echo "Invalid OTP.";
        }
    } else {
        echo "No OTP record found for this email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <style>
        /* Reset some default styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Set up the body with a clean white background */
body {
  font-family: 'Inter', sans-serif;
  background-color: #f7f7f7;
  color: #333;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
}

/* Container for OTP verification form */
.otp-verification-container {
  background-color: #fff;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
  text-align: center;
}

h2 {
  margin-bottom: 20px;
  font-size: 24px;
  color: #333;
}

form input[type="text"] {
  width: 100%;
  padding: 12px;
  margin-bottom: 20px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
  outline: none;
}

form input[type="text"]:focus {
  border-color: #007bff;
}

form button {
  width: 100%;
  padding: 12px;
  border: none;
  background-color: #007bff;
  color: #fff;
  font-size: 16px;
  border-radius: 4px;
  cursor: pointer;
}

form button:hover {
  background-color: #0056b3;
}

#timer {
  font-size: 14px;
  color: #888;
}

#resend-link {
  font-size: 14px;
  margin-top: 10px;
}

#resend-link a {
  color: #007bff;
  text-decoration: none;
}

#resend-link a:hover {
  text-decoration: underline;
}

    </style>
    
    <script>
        let timeLeft = 60;
        const timer = setInterval(function() {
            if (timeLeft <= 0) {
                clearInterval(timer);
                document.getElementById("resend-link").style.display = "block";
            } else {
                document.getElementById("timer").innerHTML = timeLeft + " seconds remaining";
            }
            timeLeft -= 1;
        }, 1000);
    </script>
</head>
<body>
    <div class="otp-verification-container">
        <h2>Enter OTP</h2>
        <form method="post">
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <button type="submit">Submit</button>
        </form>
        <p id="timer">60 seconds remaining</p>
        <p id="resend-link" style="display:none;"><a href="process_forgot_password.php">Resend OTP</a></p>
    </div>
</body>
</html>
