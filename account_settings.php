<?php
session_start();
include('connect.php');

// Redirect to login if the user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Fetch the user info based on the session email
$email = $_SESSION['email'];
$sql = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // User data is found, fetch it
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if the file is a valid image
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if ($check !== false) {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            // Update profile picture path in the database
            $update_sql = "UPDATE users SET profile_picture='$target_file' WHERE email='$email'";
            if ($conn->query($update_sql)) {
                echo "Profile picture updated successfully!";
                $user['profile_picture'] = $target_file;
            } else {
                echo "Error updating profile picture: " . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        echo "File is not a valid image.";
    }
}

// Handle profile information and password updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['full_name'])) {
    $full_name = $_POST['full_name'];
    $new_email = $_POST['email'];
    $password = $_POST['password']; // This will be the current password
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate the entered password (current password)
    if ($user['password'] === $password) {  // Use $user here, not $register
        // Update profile information (name, email)
        $update_sql = "UPDATE users SET full_name='$full_name', email='$new_email' WHERE email='$email'";  // Corrected to 'users'
        if ($conn->query($update_sql)) {
            echo "Profile updated successfully!";
            $_SESSION['email'] = $new_email;
        } else {
            echo "Error updating profile: " . $conn->error;
        }

        // Update password if new password is provided and confirmed
        if (!empty($new_password) && $new_password === $confirm_password) {
            // Update password directly (no current password validation again)
            $update_password_sql = "UPDATE users SET password='$new_password' WHERE email='$new_email'";  // Corrected to 'users'
            if ($conn->query($update_password_sql)) {
                echo "Password updated successfully!";
            } else {
                echo "Error updating password: " . $conn->error;
            }
        } elseif (!empty($new_password)) {
            echo "New password and confirmation password do not match.";
        }
    } else {
        echo "Incorrect password.";
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Settings</title>
   <style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background: white;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.container {
    background: white;
    color: #333;
    max-width: 500px;
    width: 50%;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    text-align: center;
}

h4 {
    font-size: 28px;
    margin-bottom: 20px;
    color: #222;
}

.image-container {
    text-align: center;
    margin-bottom: 20px;
}

.image-container img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto;
    display: block;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Center label + input */
.form-group {
    margin-bottom: 15px;
    display: flex;
    flex-direction: column;
    align-items: center;  /* centers horizontally */
    text-align: center;
}

label {
    font-size: 16px;
    margin-bottom: 5px;
    color: #444;
    width: 50%;   /* match input width */
}

input[type="text"],
input[type="email"],
input[type="password"],
input[type="file"] {
    width: 50%;
    padding: 10px;
    border-radius: 4px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    font-size: 16px;
    margin-bottom: 10px;
}

/* Buttons */
button.peanut-btn,
button[type="submit"],
.button-group button {
    width: 50%;
    padding: 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    border: none;
    background-color: #ff7f00;
    color: white;
    transition: background-color 0.3s ease;
    display: block;
    margin: 10px auto 0 auto;  /* center horizontally */
}

button.peanut-btn:hover,
button[type="submit"]:hover,
.button-group button:hover {
    background-color: #e67300;
}

.button-group {
    display: flex;
    justify-content: center;  /* center group */
    gap: 10px;
    margin-top: 20px;
}

.button-group button {
    width: 50%;
    flex-grow: 0;
}

input::placeholder {
    color: #aaa;
}





   </style>

   </style>
</head>
<body>
<div class="container">
    <h4>Account Settings</h4>
    <form action="" method="post" enctype="multipart/form-data">
        <!-- Profile Picture -->
        <div class="form-group">
            <label>Profile Picture</label><br>
            <!-- Use $user instead of $register to access the profile picture -->
            <img src="<?= !empty($user['profile_picture']) ? $user['profile_picture'] : 'placeholder.png'; ?>" alt="Profile Picture" style="width: 100px; height: 100px; object-fit: cover;">
            <input type="file" name="profile_picture">
            <button type="submit">Upload</button>
        </div>

        <!-- Profile Information -->
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?= $user['full_name']; ?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= $user['email']; ?>" required>
        </div>

        <!-- Password (Current Password) -->
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" value="<?= $user['password']; ?>" required> <!-- This is the current password -->
        </div>

        <!-- New Password Fields -->
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password">
        </div>

        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password">
        </div>

        <button type="submit">Save Changes</button>
        <div class="button-group">
            <button type="button" onclick="window.location.href='home1.php'">Back to Login</button>
        </div>
    </form>
</div>
</body>
</html>