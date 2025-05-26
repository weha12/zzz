<?php
session_start();
include('connect.php');

// Check admin login
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get admin profile picture
$adminEmail = $conn->real_escape_string($_SESSION['email']);
$adminQuery = "SELECT email, profile_picture FROM admin WHERE email = '$adminEmail'";
$adminResult = $conn->query($adminQuery);
$admin = $adminResult->fetch_assoc();

$categories = [];
if (file_exists('categories.json')) {
    $json = file_get_contents('categories.json');
    $categories = json_decode($json, true);
}

// Load products from product.xml
$xmlProducts = [];
if (file_exists('product.xml')) {
    $xml = simplexml_load_file('product.xml');
    foreach ($xml->product as $prod) {
        $xmlProducts[] = [
            'name' => (string)$prod->name,
            'price' => (string)$prod->price,
            'image' => (string)$prod->image,
            'description' => (string)$prod->description,
            'quantity' => (int)$prod->quantity
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>CRUD Dashboard</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: Arial, sans-serif;
      background-color: #15202b;
      color: #fff;
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
      object-fit: cover;
    }
    .profile-info h2 { font-size: 16px; word-break: break-word; }
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
      color: #fff;
    }
    .main-content {
      margin-left: 250px;
      flex-grow: 1;
      padding: 20px;
      background-color: #15202b;
      overflow-y: auto;
    }
    section { margin-bottom: 30px; }
    h2 {
      margin-bottom: 15px;
      font-weight: 700;
      color: #1ed760;
    }
    .categories-box, .products-box {
      border: 2px solid #444;
      border-radius: 8px;
      padding: 20px;
      background-color: #1f2231;
      max-height: 300px;
      overflow-y: auto;
    }
    .crud-buttons {
      margin-top: 15px;
      text-align: center;
    }
    .crud-buttons a {
      display: inline-block;
      margin: 5px;
      padding: 10px 15px;
      border-radius: 5px;
      text-decoration: none;
      background-color: #2ecc71;
      color: white;
      transition: background-color 0.3s ease;
    }
    .crud-buttons a:hover {
      background-color: #27ae60;
    }
    .product-item {
      border: 1px solid #555;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
      background-color: #2c2f3a;
      display: flex;
      gap: 15px;
      align-items: center;
      justify-content: space-between;
    }
    .product-info { flex-grow: 1; }
    .product-info h3 { margin-bottom: 8px; }
    .product-info p { margin-bottom: 6px; color: #ccc; }
    .product-info .price { font-weight: bold; color: #1ed760; }
    .product-image {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
      flex-shrink: 0;
    }
    .edit-button {
      background-color: #3498db;
      border: none;
      border-radius: 5px;
      color: white;
      padding: 10px 15px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      text-decoration: none;
    }
    .edit-button:hover { background-color: #2980b9; }
  </style>
</head>
<body>

  <div class="sidebar">
    <div class="profile">
      <img src="<?= !empty($admin['profile_picture']) ? htmlspecialchars($admin['profile_picture']) : 'placeholder_admin.png'; ?>" alt="Profile Picture" />
      <div class="profile-info">
        <h2><?= htmlspecialchars($admin['email'] ?? 'Admin'); ?></h2>
        <p><span class="status-dot"></span> Admin</p>
      </div>
    </div>
    <a href="admin.php" class="menu-item">Dashboard</a>
    <a href="admin_account.php" class="menu-item">Account Settings</a>
    <a href="crud.php" class="menu-item">CRUD</a>
    <a href="transaction.php" class="menu-item">Report</a>
  </div>

  <div class="main-content">

    <section class="categories-section">
      <h2>Categories</h2>
      <div class="categories-box">
        <ul>
          <?php foreach ($categories as $cat): ?>
            <li><?= htmlspecialchars($cat) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="crud-buttons">
        <a href="add_category.php">Add Category</a>
      </div>
    </section>

   <section class="products-section">
  <h2>Products</h2>
  <div class="products-box" id="products-box">
    <?php if (!empty($xmlProducts)): ?>
      <table style="width: 100%; border-collapse: collapse; color: #fff;">
        <thead>
          <tr style="background-color: #2c2f3a;">
            <th style="padding: 10px; border: 1px solid #555;">Image</th>
            <th style="padding: 10px; border: 1px solid #555;">Name</th>
            <th style="padding: 10px; border: 1px solid #555;">Description</th>
            <th style="padding: 10px; border: 1px solid #555;">Price</th>
            <th style="padding: 10px; border: 1px solid #555;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($xmlProducts as $index => $prod): ?>
            <tr style="background-color: #1f2231;">
              <td style="padding: 10px; border: 1px solid #555;">
                <img src="<?= htmlspecialchars($prod['image']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;">
              </td>
              <td style="padding: 10px; border: 1px solid #555;"><?= htmlspecialchars($prod['name']) ?></td>
              <td style="padding: 10px; border: 1px solid #555;"><?= htmlspecialchars($prod['description']) ?></td>
              <td style="padding: 10px; border: 1px solid #555;"><?= htmlspecialchars($prod['price']) ?></td>
              <td style="padding: 10px; border: 1px solid #555;">
                <a href="update_product.php?id=<?= $index ?>" class="edit-button">Edit</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No products in XML.</p>
    <?php endif; ?>
  </div>
  <div class="crud-buttons">
    <a href="add_product.php">Add Product</a>
  </div>
</section>

<section class="quantity-section">
  <h2>Product Quantities</h2>
  <div class="categories-box">
    <table style="width: 100%; border-collapse: collapse; color: #fff;">
      <thead>
        <tr style="background-color: #2c2f3a;">
          <th style="padding: 10px; border: 1px solid #555;">Product Name</th>
          <th style="padding: 10px; border: 1px solid #555;">Quantity</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($xmlProducts as $prod): ?>
          <tr style="background-color: #1f2231;">
            <td style="padding: 10px; border: 1px solid #555;"><?= htmlspecialchars($prod['name']) ?></td>
            <td style="padding: 10px; border: 1px solid #555;"><?= htmlspecialchars($prod['quantity']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="crud-buttons">
    <a href="add_quantity.php">Add Quantity</a>
    <a href="delete_quantity.php">Delete Quantity</a>
  </div>
</section>


  </div>
</body>
</html>
