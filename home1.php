<?php
session_start();
include('connect.php');

// Optional: get name if logged in
$fullname = "Guest";
$totalCartItems = 0;

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $sql = "SELECT full_name FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $fullname = $row['full_name'];
    }

    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $totalCartItems += $item['quantity'];
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Our Story - HiTech</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background-color: #fff;
      color: #333;
    }
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 50px;
      border-bottom: 1px solid #ddd;
    }
    nav a {
      margin: 0 15px;
      text-decoration: none;
      color: #333;
      font-weight: 500;
    }
    .top-banner {
      text-align: center;
      font-size: 12px;
      background: #f3f3f3;
      padding: 10px;
    }
    .story-section {
      padding: 60px 50px;
    }
    .story-section h1 {
      font-size: 36px;
      margin-bottom: 40px;
      text-align: center;
    }
    .story-content {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 50px;
    }
    .story-content img {
      width: 600px;
      height: auto;
      border-radius: 10px;
    }
    .story-text {
      max-width: 600px;
    }
    .story-text h2 {
      font-size: 24px;
      margin-bottom: 20px;
    }
    .story-text p {
      margin-bottom: 20px;
      line-height: 1.6;
    }
    .btn {
      background: black;
      color: white;
      padding: 10px 20px;
      border-radius: 20px;
      text-decoration: none;
      font-size: 14px;
    }
    .stats {
      display: flex;
      gap: 50px;
      margin-top: 30px;
    }
    .stats div {
      font-size: 18px;
    }
    .highlight-section {
      padding: 60px 50px;
      background: #f9f9f9;
    }
    .highlight-section h2 {
      font-size: 28px;
      margin-bottom: 40px;
    }
    .highlights {
      display: flex;
      gap: 30px;
    }
    .highlight {
      flex: 1;
      background: white;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .highlight img {
      width: 50%;
      border-radius: 10px;
      margin-bottom: 15px;
      max-height: 160px;
      object-fit: cover;
    }
    .highlight h3 {
      font-size: 18px;
      margin-bottom: 10px;
    }
    .highlight p {
      font-size: 14px;
      color: #555;
    }
    footer {
      background: #000;
      color: #fff;
      padding: 60px 50px;
      display: flex;
      flex-wrap: wrap;
      gap: 40px;
      justify-content: space-between;
      font-size: 14px;
    }
    footer h4 {
      margin-bottom: 10px;
    }
    footer p {
      margin: 5px 0;
    }
    footer input {
      padding: 8px;
      width: 100%;
      margin-bottom: 10px;
      border-radius: 5px;
      border: none;
    }
    footer button {
      padding: 10px 20px;
      background: #fff;
      color: #000;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .welcome-logout {
      display: flex;
      align-items: center;
    }
    .welcome {
      font-size: 14px;
      color: #555;
    }
    .logout-link a {
      color: red;
      text-decoration: none;
      font-weight: bold;
      font-size: 14px;
      margin-left: 15px;
    }
    input[type="text"] {
      padding: 5px 10px;
    }
     .cart-icon span {
      position: absolute;
      top: -8px;
      right: -10px;
      background-color: red;
      color: white;
      border-radius: 50%;
      padding: 2px 6px;
      font-size: 12px;
    }
      .cart-icon {
      position: relative;
      font-size: 20px;
      text-decoration: none;
    }

    /* Responsive Styles */
  @media (max-width: 768px) {
    header {
      flex-direction: column;
      align-items: flex-start;
      padding: 15px 20px;
    }

    .story-section {
      padding: 30px 20px;
    }

    .highlight-section {
      padding: 30px 20px;
    }

    footer {
      padding: 30px 20px;
      flex-direction: column;
    }

    nav a {
      margin: 5px 10px;
    }

    .story-section h1,
    .highlight-section h2 {
      font-size: 22px;
    }

    .story-content {
      flex-direction: column;
    }

    .story-text h2 {
      font-size: 18px;
    }

    .stats {
      flex-direction: column;
      gap: 15px;
    }

    .highlights {
      flex-direction: column;
      gap: 20px;
    }

    .highlight {
      width: 100%;
    }
  }

  @media (max-width: 480px) {
    .btn {
      padding: 8px 16px;
      font-size: 13px;
    }

    .story-text p,
    .highlight p {
      font-size: 13px;
    }

    .highlight h3 {
      font-size: 15px;
    }

    .cart-icon {
      font-size: 18px;
    }
  }
  </style>
</head>
<body>

  <header>
    <div class="logo"><strong>HiTech</strong></div>
    <nav>
      <a href="#">Home</a>
      <a href="smartphone.php">Shop</a>
      <a href="#">About us</a>
      <a href="#">Contact us</a>
    <?php if (!isset($_SESSION['email'])): ?>
    <a href="login.php">Login</a> | <a href="register.php">Register</a>
<?php else: ?>
    <!-- Add your logged-in links here -->
<?php endif; ?>

    </nav>
    <div style="display: flex; align-items: center; gap: 10px; padding: 10px;">
     
      
     <?php if (isset($_SESSION['email'])): ?>
    <div class="welcome-logout">
        <div class="welcome">
            Welcome, <strong><?php echo htmlspecialchars($fullname); ?></strong>
        </div>
        <div class="logout-link">
            <a href="logout.php">Logout</a>
        </div>
    </div>
<?php endif; ?>

      
      <a href="account_settings.php" style="text-decoration: none; font-size: 20px;">👤</a>
      <a href="cart.php" class="cart-icon">🛒<span id="cart-count"><?= $totalCartItems ?></span></a>
    </div>
  </header>

  <section class="story-section">
    <h1>Our Story</h1>
    <div class="story-content">
      <img src="33.jpg" alt="Team Image">
      <div class="story-text">
        <h2>SINCE 2006</h2>
        <p>From humble beginnings to where we are today, our commitment to innovation has remained unwavering. We’ve overcome challenges, embraced opportunities, and evolved with the rapidly changing tech landscape.</p>
        <p>Our story is one of passion, innovation, and relentless pursuit of excellence. It all began with a shared vision to revolutionize the way people interact with technology.</p>
        <a href="smartphone.html" class="btn">Explore →</a>
        <div class="stats">
          <div><strong>14+</strong><br>Stores</div>
          <div><strong>20+</strong><br>Brands</div>
        </div>
      </div>
    </div>
  </section>

  <section class="highlight-section">
    <h2>What make us different</h2>
    <div class="highlights">
      <div class="highlight">
        <img src="https://images.unsplash.com/photo-1603791440384-56cd371ee9a7" alt="Customer Service">
        <h3>Customer Service</h3>
        <p>Our dedicated team is here to assist you with any inquiries, providing personalized support and expert guidance.</p>
      </div>
      <div class="highlight">
        <img src="32.jpg" alt="Product Quality">
        <h3>Product Quality</h3>
        <p>Providing you with confidence and peace of mind. Trust in our dedication to quality with every purchase.</p>
      </div>
    </div>
  </section>

  <footer>
    <div>
      <h4>Where abouts</h4>
      <p>L. Mercado Street corner C.L.<br> Hilario Street, Brgy. Poblacion, Bustos, Bulacan</p>
      <h4>Mailbox</h4>
      <p>hellohigh@gmail.com</p>
      <h4>Contact</h4>
      <p>(608) 555-0111</p>
    </div>

    <div>
      <h4>Pages</h4>
      <p>About Us</p>
      <p>Categories</p>
      <p>Shop</p>
      <p>Contact us</p>
    </div>

    <div>
      <h4>Resource</h4>
      <p>Blogs</p>
      <p>FAQ</p>
      <p>Reviews</p>
    </div>

    <div>
      <h4>Utilities</h4>
      <p>Style Guide</p>
      <p>Error 404</p>
      <p>Changelog</p>
      <p>Return Policy</p>
    </div>

    <div>
      <h4>Connected</h4>
      <p>Instagram</p>
      <p>Facebook</p>
      <p>YouTube</p>
      <p>Twitter</p>
    </div>

    <div style="min-width: 200px;">
      <h4>Subscribe</h4>
      <input type="text" placeholder="Enter your name">
      <input type="email" placeholder="Enter email">
      <button>Submit →</button>
    </div>
  </footer>

</body>
</html>
