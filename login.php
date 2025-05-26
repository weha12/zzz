<?php
include('connect.php');
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check Admin Table
    $adminSql = "SELECT * FROM admin WHERE email = '$email' AND password = '$password' AND status = 'verified'";
    $adminResult = $conn->query($adminSql);

    if ($adminResult->num_rows > 0) {
        $admin = $adminResult->fetch_assoc();

        // Admin login success
        $_SESSION['email'] = $admin['email'];
        $_SESSION['role'] = 'admin';
        header("Location: admin.php");
        exit();
    }
}

/*
if (isset($_COOKIE['user_email']) && isset($_COOKIE['user_password'])) {
    $email = $_COOKIE['user_email'];
    $password = $_COOKIE['user_password'];

    $userSql = "SELECT * FROM users WHERE email = '$email' AND password = '$password' AND status = 'verified'";
    $userResult = $conn->query($userSql);

    if ($userResult->num_rows > 0) {
        $user = $userResult->fetch_assoc();
        if ($user['is_blocked'] == 1) {
            $message = "Your account is blocked. Please contact the admin.";
        } else {
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = 'user';
            header("Location: home1.php");
            exit();
        }
    } else {
        $message = "Invalid credentials or account not verified.";
    }
}
*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    $userSql = "SELECT * FROM users WHERE email = '$email' AND status = 'verified'";
    $userResult = $conn->query($userSql);

    if ($userResult->num_rows > 0) {
        $user = $userResult->fetch_assoc();
        if ($user['is_blocked'] == 1) {
            $message = "Your account is blocked. Please contact the admin.";
        } else {
            if ($user['password'] === $password) {
                $resetAttemptsSql = "UPDATE users SET failed_attempts = 0 WHERE email = '$email'";
                $conn->query($resetAttemptsSql);

                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = 'user';

                if ($remember) {
                    setcookie("user_email", $email, time() + 60, "/");
                    setcookie("user_password", $password, time() + 60, "/");
                }

                header("Location: home1.php");
                exit();
            } else {
                $failedAttempts = $user['failed_attempts'] + 1;

                if ($failedAttempts >= 3) {
                    $blockSql = "UPDATE users SET is_blocked = 1 WHERE email = '$email'";
                    $conn->query($blockSql);
                    $message = "Your account has been blocked due to multiple failed login attempts.";
                } else {
                    $updateAttemptsSql = "UPDATE users SET failed_attempts = $failedAttempts WHERE email = '$email'";
                    $conn->query($updateAttemptsSql);
                    $remainingAttempts = 3 - $failedAttempts;
                    $message = "Invalid credentials. You have $remainingAttempts attempts remaining.";
                }
            }
        }
    } else {
        $message = "Invalid login credentials or account not verified.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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

    .login-box {
        background: #ffffff;
        padding: 40px 30px;
        width: 100%;
        max-width: 400px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }

    h1 {
        text-align: center;
        margin-bottom: 30px;
        font-size: 28px;
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

    .checkbox-container {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        font-size: 14px;
        color: #555;
    }

    .checkbox-container input {
        margin-right: 8px;
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

    .notification {
        background-color: #ffe0e0;
        color: #d9534f;
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #f5c2c7;
        border-radius: 8px;
        font-size: 14px;
        text-align: center;
    }

    .register-link {
        text-align: center;
        margin-top: 20px;
        font-size: 14px;
        color: #555;
    }

    .register-link a {
        color: #3a86ff;
        text-decoration: none;
    }

    .register-link a:hover {
        text-decoration: underline;
    }
</style>

</head>
<body>
    <div class="login-box">
        <h1>Login</h1>

        <?php if (!empty($message)): ?>
            <div class="notification"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <div class="checkbox-container">
    <input type="checkbox" name="remember" id="remember">
    <label for="remember">Remember Me</label>
</div>

            <button type="submit">LOGIN</button>
        </form>

        <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
        <p class="register-link"><a href="forget_password.php">Forgot Password?</a></p>
    </div>
</body>
</html>
