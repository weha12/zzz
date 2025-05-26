<?php
session_start();
require 'connect.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$userRole = $_SESSION['role'];
$userName = $_SESSION['email'];

// Admin profile picture query
$adminEmail = mysqli_real_escape_string($conn, $_SESSION['email']);
$adminQuery = "SELECT email, profile_picture FROM admin WHERE email = '$adminEmail'";
$adminResult = mysqli_query($conn, $adminQuery);
$admin = mysqli_fetch_assoc($adminResult);

// Prepare sales data from transaction.xml
$salesData = [];

if (file_exists('transaction.xml')) {
    $xml = simplexml_load_file('transaction.xml');
    foreach ($xml->transaction as $txn) {
        $date = date('Y-m-d', strtotime((string)$txn->timestamp));
        $amount = floatval(str_replace(['₱', ','], '', (string)$txn->total));

        if (!isset($salesData[$date])) {
            $salesData[$date] = 0;
        }
        $salesData[$date] += $amount;
    }
    ksort($salesData); // sort by date
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background-color: #15202b;
            color: #ffffff;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #1f2231;
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

        .logout-btn {
            background-color: #e74c3c;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            position: fixed;
            top: 20px;
            right: 20px;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        .main-content {
            margin-left: 270px;
            margin-top: 50px;
            flex-grow: 1;
            background-color: #141d26;
            color: #ffffff;
            padding: 30px;
            box-sizing: border-box;
        }

        .main-content h2 {
            font-size: 28px;
            margin-bottom: 20px;
        }

        .stats-box {
            display: inline-block;
            width: 200px;
            height: 150px;
            margin: 15px;
            background-color: #1b2a36;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            text-align: center;
            padding-top: 30px;
            transition: background-color 0.3s ease;
        }

        .stats-box:hover {
            background-color: #3498db;
        }

        .stats-box h3 {
            font-size: 24px;
            margin: 0;
            color: #ffffff;
        }

        .stats-box p {
            font-size: 18px;
            color: #ccc;
        }

        #salesChart {
            background-color: #1b2a36;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<a href="logout.php" class="logout-btn">Log Out</a>

<div class="sidebar">
    <div class="profile">
        <img src="<?= !empty($admin['profile_picture']) ? htmlspecialchars($admin['profile_picture']) : 'placeholder_admin.png'; ?>" alt="Profile Picture">
        <div class="profile-info">
            <h2><?= htmlspecialchars($admin['email'] ?? 'Admin'); ?></h2>
            <p><span class="status-dot"></span> Admin</p>
        </div>
    </div>
    <a href="admin.php" class="menu-item">Dashboard</a>
    <a href="admin_account.php" class="menu-item">Account Settings</a>
    <a href="crud.php" class="menu-item">CRUD</a>
    <a href="transaction.php" class="menu-item">Reports</a>
</div>

<div class="main-content">
   

    <!-- Sales Graph -->
    <h2>Sales Overview</h2>
    <canvas id="salesChart" width="800" height="400"></canvas>
</div>



<!-- Chart.js and Graph Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const salesDates = <?= json_encode(array_keys($salesData)) ?>;
    const salesTotals = <?= json_encode(array_values($salesData)) ?>;

    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: salesDates,
            datasets: [{
                label: 'Total Sales (₱)',
                data: salesTotals,
                backgroundColor: 'rgba(255, 159, 64, 0.3)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: { color: '#fff' }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                },
                y: {
                    ticks: {
                        color: '#fff',
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    },
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.1)' }
                }
            }
        }
    });
</script>




</body>
</html>
