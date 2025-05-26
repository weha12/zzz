<?php
session_start();
include('connect.php');

// Redirect if not admin
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch admin data
$email = $_SESSION['email'];
$sql = "SELECT * FROM admin WHERE email='$email'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
} else {
    echo "Admin not found.";
    exit();
}

// Handle block/unblock/delete actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['user_email'])) {
    $userEmail = $_POST['user_email'];
    $action = $_POST['action'];

    if ($action === 'block') {
        $sql = "UPDATE users SET is_blocked=1 WHERE email='$userEmail'";
        echo $conn->query($sql) ? "User account blocked successfully!" : "Error blocking account: " . $conn->error;
    } elseif ($action === 'unblock') {
        $sql = "UPDATE users SET is_blocked=0, failed_attempts=0 WHERE email='$userEmail'";
        echo $conn->query($sql) ? "User account unblocked successfully!" : "Error unblocking account: " . $conn->error;
    } elseif ($action === 'delete') {
        $adminPassword = $_POST['admin_password'] ?? '';
        if ($admin['password'] === $adminPassword) {
            $deleteSql = "DELETE FROM users WHERE email='$userEmail'";
            echo $conn->query($deleteSql) ? "User deleted successfully!" : "Error deleting user: " . $conn->error;
        } else {
            echo "Incorrect admin password. User not deleted.";
        }
    }
}

// Fetch all users
$usersSql = "SELECT email, is_blocked, failed_attempts FROM users";
$usersResult = $conn->query($usersSql);

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_picture'])) {
    $target_dir = "uploads/admin/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if ($check !== false) {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $update_sql = "UPDATE admin SET profile_picture='$target_file' WHERE email='$email'";
            if ($conn->query($update_sql)) {
                echo "Profile picture updated successfully!";
                $admin['profile_picture'] = $target_file;
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

// Handle email and password updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $new_email = $_POST['email'];
    $password = $_POST['password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($admin['password'] === $password) {
        $update_sql = "UPDATE admin SET email='$new_email' WHERE email='$email'";
        if ($conn->query($update_sql)) {
            echo "Email updated successfully!";
            $_SESSION['email'] = $new_email;
        } else {
            echo "Error updating email: " . $conn->error;
        }

        if (!empty($new_password) && $new_password === $confirm_password) {
            $update_password_sql = "UPDATE admin SET password='$new_password' WHERE email='$new_email'";
            echo $conn->query($update_password_sql) ? "Password updated successfully!" : "Error updating password: " . $conn->error;
        } elseif (!empty($new_password)) {
            echo "New password and confirmation password do not match.";
        }
    } else {
        echo "Incorrect current password.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Account Settings</title>
 <style> 
    /* General reset for padding and margins */
/* Universal Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Basic Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #15202b; /* Twitter dark mode background */
    color: #ffffff;
    display: flex;
    min-height: 100vh;
}

/* Sidebar Styles */
.sidebar {
    width: 250px;
    background-color: #1f2231;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
    position: fixed; /* Sidebar fixed on the left */
    height: 100%;
    top: 0;
    left: 0;
    overflow-y: auto;
}

.profile {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.profile img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #3a3f4b;
}

.profile-info h2 {
    font-size: 16px;
}

.sidebar h1 {
    font-size: 20px;
    color: #ffffff;
    margin-bottom: 15px;
}

/* Main content area */
.main-content {
    margin-left: 500px; /* Give space for sidebar */
    flex-grow: 1; /* Take up remaining space */
    padding: 20px;
    height: 100%;
    background-color: #1c1f26; /* Dark background like Twitter's dark mode */
    color: #e1e8ed; /* Light text color for better contrast */
    overflow-y: auto; /* Enable scrolling if content overflows */
}

/* Form and Table Elements */
form,
table {
    background-color: #2c2f38; /* Dark background for form and table */
    border-radius: 8px; /* Rounded corners for form and table */
    padding: 20px;
    margin-bottom: 20px;
}

form input,
form button,
table {
    color: #ffffff; /* White text for form inputs and table */
}

form input,
form button {
    background-color: #3a3f4b; /* Slightly lighter dark background */
    border: 1px solid #444; /* Subtle border */
    color: #fff; /* White text for inputs and buttons */
}

/* Update Button Styles */
button {
    background-color: #4caf50; /* Green background for the button */
    color: #fff; /* White text color for button */
    border: none; /* Remove default border */
    cursor: pointer; /* Pointer cursor on hover */
    transition: background-color 0.3s ease; /* Smooth transition on hover */
}

button:hover {
    background-color: #45a049; /* Slightly darker green on hover */
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table th,
table td {
    padding: 12px;
    text-align: left;
}

table th {
    background-color: #333; /* Dark header for the table */
    color: #fff;
}

table tr:nth-child(even) {
    background-color: #2a2f38; /* Alternating row colors */
}

table tr:hover {
    background-color: #3a3f4b; /* Hover effect for rows */
}

/* Profile Image and Info */
.profile img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #3a3f4b;
    border: 2px solid #fff;
}

/* Sidebar Style */
.sidebar {
    width: 250px;
    background-color: #1f2231; /* Sidebar dark background */
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
    position: fixed;
    height: 100%;
    top: 0;
    left: 0;
    overflow-y: auto;
}

.sidebar h1 {
    font-size: 20px;
    color: #ffffff;
    margin-bottom: 15px;
}

.menu-item {
    color: #ccc;
    text-decoration: none;
    background-color: #3a3f4b;
    padding: 12px 15px;
    border-radius: 5px;
    transition: background-color 0.3s;
    display: block;
}

.menu-item:hover {
    background-color: #4b515d;
    color: #ffffff;
}


</style></head> 
<body>
<div class="container">
    <div class="sidebar">
        <div class="profile">
            <img src="<?= !empty($admin['profile_picture']) ? $admin['profile_picture'] : 'placeholder_admin.png'; ?>" alt="Profile Picture">
            <div class="profile-info">
                <h2><?= htmlspecialchars($admin['email']); ?></h2>
                <p><span class="status-dot"></span> Admin</p>
            </div>
        </div>
        <a href="admin.php" class="menu-item">Dashboard</a>
        <a href="admin_account.php" class="menu-item">Account Settings</a>
        <a href="crud.php" class="menu-item">CRUD</a>
            <a href="transaction.php" class="menu-item">Report</a>
    </div>

    <div class="main-content">
        <h1>Update Account Settings</h1>

        <!-- Upload Profile Picture -->
        <form action="admin_account.php" method="POST" enctype="multipart/form-data">
            <h3>Change Profile Picture</h3>
            <input type="file" name="profile_picture" accept="image/*" required>
            <button type="submit">Upload</button>
        </form>

        <!-- Change Email / Password -->
        <form action="admin_account.php" method="POST">
            <h3>Update Password</h3>
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($admin['email']); ?>" readonly><br><br>

            <label>Current Password:</label>
            <input type="password" name="password" value="<?= htmlspecialchars($admin['password']); ?>" readonly><br><br>

            <label>New Password:</label>
            <input type="password" name="new_password"><br><br>

            <label>Confirm Password:</label>
            <input type="password" name="confirm_password"><br><br>

            <button type="submit">Update</button>
        </form>

        <!-- Manage Users -->
        <h2>Manage Users</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Failed Attempts</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($usersResult->num_rows > 0): ?>
                    <?php while ($user = $usersResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['email']); ?></td>
                            <td><?= $user['failed_attempts']; ?></td>
                            <td><?= $user['is_blocked'] ? 'Blocked' : 'Active'; ?></td>
                            <td>
                                <!-- Block/Unblock -->
                                <form action="admin_account.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="user_email" value="<?= $user['email']; ?>">
                                    <input type="hidden" name="action" value="<?= $user['is_blocked'] ? 'unblock' : 'block'; ?>">
                                    <button type="submit"><?= $user['is_blocked'] ? 'Unblock' : 'Block'; ?></button>
                                </form>

                                <!-- Delete with JS prompt -->
                                <form method="POST" style="display:inline;" onsubmit="return confirmDelete(this);">
                                    <input type="hidden" name="user_email" value="<?= $user['email']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="admin_password" class="admin-password-field">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- JavaScript for delete confirmation -->
<script>
function confirmDelete(form) {
    const confirmAction = confirm("Are you sure you want to delete this user?");
    if (!confirmAction) return false;

    const password = prompt("Enter admin password to confirm deletion:");
    if (!password) return false;

    form.querySelector(".admin-password-field").value = password;
    return true;
}
</script>
</body>
</html>
