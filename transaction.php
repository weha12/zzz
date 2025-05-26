<?php
session_start();
include 'connect.php';

$transactions = [];

if (file_exists('transaction.xml')) {
    $xml = simplexml_load_file('transaction.xml');
    foreach ($xml->transaction as $txn) {
        $transactions[] = [
            'name' => (string) $txn->name,
            'address' => (string) $txn->address,
            'contact' => (string) $txn->contact,
            'payment' => (string) $txn->payment,
            'gcashNumber' => isset($txn->gcashNumber) ? (string) $txn->gcashNumber : '',
            'total' => floatval(str_replace(['₱', ','], '', $txn->total)),
            'timestamp' => (string) $txn->timestamp,
        ];
    }
}

$userRole = $_SESSION['role'];
$userName = $_SESSION['email'];

$adminEmail = mysqli_real_escape_string($conn, $_SESSION['email']);
$adminQuery = "SELECT email, profile_picture FROM admin WHERE email = '$adminEmail'";
$adminResult = mysqli_query($conn, $adminQuery);
$admin = mysqli_fetch_assoc($adminResult);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Transaction History</title>

  <!-- jsPDF CDN -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

  <style>
   * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

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

    .main-content {
        margin-left: 270px;
        padding: 30px;
        flex-grow: 1;
        background-color: #141d26;
    }

    h2 {
        font-size: 28px;
        margin-bottom: 20px;
        text-align: center;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
    }

    th, td {
        padding: 12px 15px;
        border: 1px solid #444;
        text-align: left;
    }

    th {
        background-color: #1f2c3a;
        color: #ffffff;
    }

    td {
        background-color: #1a2735;
        color: #ddd;
    }

    .back-link {
        text-align: center;
        display: block;
        background-color: #3498db;
        color: white;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 5px;
        width: 200px;
        margin: 0 auto;
    }

    button.print-btn {
    background-color: #3498db;
    border: none;
    color: white;
    padding: 8px 14px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: background-color 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
}

button.print-btn:hover {
    background-color: #217dbb;
}

  </style>
</head>
<body>

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
    <h2>Transaction History</h2>

    <?php if (empty($transactions)): ?>
        <p>No transactions found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Contact</th>
                    <th>Payment</th>
                    <th>GCash No.</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $index => $txn): ?>
                    <tr>
                        <td><?= htmlspecialchars($txn['name']) ?></td>
                        <td><?= htmlspecialchars($txn['address']) ?></td>
                        <td><?= htmlspecialchars($txn['contact']) ?></td>
                        <td><?= htmlspecialchars($txn['payment']) ?></td>
                        <td><?= $txn['payment'] === 'GCash' ? htmlspecialchars($txn['gcashNumber']) : 'N/A' ?></td>
                        <td>₱<?= number_format($txn['total'], 2) ?></td>
                        <td><?= htmlspecialchars($txn['timestamp']) ?></td>
                       <td>
    <button class="print-btn" 
        onclick='printReceipt(<?= htmlspecialchars(json_encode($txn)) ?>)'>
        Print
    </button>
</td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function printReceipt(txn) {
    const { jsPDF } = window.jspdf;

    // Widened to 90mm instead of 80mm
    const doc = new jsPDF({
        orientation: "portrait",
        unit: "mm",
        format: [100, 200] // Width x Height
    });

    doc.setFont("courier", "normal");
    doc.setFontSize(10);

    let y = 10;

    // Centered Store Header
    doc.text("HiTech ", 45, y, { align: "center" }); y += 5;
    doc.text("Official Receipt", 45, y, { align: "center" }); y += 5;
    doc.text("========================================", 45, y, { align: "center" }); y += 5;

    // Transaction Info
    doc.text(`Name     : ${txn.name}`, 5, y); y += 5;
    doc.text(`Address  : ${txn.address}`, 5, y); y += 5;
    doc.text(`Contact  : ${txn.contact}`, 5, y); y += 5;
    doc.text(`Payment  : ${txn.payment}`, 5, y); y += 5;
    if (txn.payment === 'GCash') {
        doc.text(`GCash No.: ${txn.gcashNumber}`, 5, y); y += 5;
    }
    doc.text(`Date     : ${txn.timestamp}`, 5, y); y += 5;
    doc.text("========================================", 45, y, { align: "center" }); y += 5;

    // TOTAL (left-aligned to fix ± issue)
    doc.setFontSize(12);
    const formattedTotal = " " + Number(txn.total).toLocaleString(undefined, { minimumFractionDigits: 2 });
    doc.text(`TOTAL: ${formattedTotal}`, 5, y); y += 10;

    // Footer
    doc.setFontSize(9);
    doc.text("Thank you for your purchase!", 45, y, { align: "center" }); y += 4;
    doc.text("This is your official receipt.", 45, y, { align: "center" });

    // Save file
    const safeName = txn.name.replace(/\s+/g, '_').replace(/[^\w]/g, '');
    const safeTime = txn.timestamp.replace(/[: ]/g, '-');
    const fileName = `Receipt_${safeName}_${safeTime}.pdf`;
    doc.save(fileName);
}
</script>

</body>
</html>
