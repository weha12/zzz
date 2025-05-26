<?php
session_start();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Count total quantity of items in cart
$totalCartItems = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $totalCartItems += $item['quantity'];
    }
}

$categoriesFile = 'categories.json';
$categories = [];
if (file_exists($categoriesFile)) {
    $categories = json_decode(file_get_contents($categoriesFile), true);
    if (!is_array($categories)) {
        $categories = [];
    }
}


$message = '';

// Load products from XML
$xmlFile = 'product.xml';
if (!file_exists($xmlFile)) {
    die("product.xml file not found.");
}
$xml = simplexml_load_file($xmlFile);
$products = [];
foreach ($xml->product as $product) {
    $products[] = [
        'name' => (string) $product->name,
        'price' => (string) $product->price,
        'image' => (string) $product->image,
        'description' => (string) $product->description,
        'quantity' => (int) $product->quantity
    ];
}

// Handle Add to Cart POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $image = $_POST['image'] ?? '';
    $description = $_POST['description'] ?? '';
    $available = 0;

    foreach ($products as $product) {
        if ($product['name'] === $name) {
            $available = $product['quantity'];
            break;
        }
    }

    if ($available > 0) {
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['name'] === $name) {
                $item['quantity']++;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = [
                'name' => $name,
                'price' => $price,
                'image' => $image,
                'description' => $description,
                'quantity' => 1
            ];
        }
        $message = "<div class='success-message'>Successfully added <strong>" . htmlspecialchars($name) . "</strong> to your cart!</div>";
    } else {
        $message = "<div class='error-message'><strong>" . htmlspecialchars($name) . "</strong> is out of stock.</div>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Smartphone Shop</title>
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
    .logo {
      font-size: 24px;
      font-weight: bold;
    }
    nav a {
      margin: 0 15px;
      text-decoration: none;
      color: #333;
      font-weight: 500;
    }
    nav a.active {
      color: orange;
      font-weight: bold;
    }
    .category-bar {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      padding: 20px 50px;
    }
    .category {
      background-color: #ff7f00;
      color: white;
      padding: 10px 20px;
      border-radius: 20px;
      cursor: pointer;
      font-weight: bold;
    }
    .filter-container {
      display: flex;
      justify-content: space-between;
      padding: 20px 50px;
      flex-wrap: wrap;
    }
    .filter-wrapper {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  padding: 20px 50px;
  gap: 20px;
}

.category-bar-inline {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

    .search-bar {
      flex: 1;
      margin-right: 20px;
      max-width: 400px;
      padding: 10px;
      font-size: 16px;
      background-color: #f3f3f3;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .sort-select {
      width: 200px;
      padding: 10px;
      font-size: 16px;
      background-color: #f3f3f3;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .product-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      padding: 40px 50px;
    }
    .product {
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      padding: 20px;
      text-align: center;
    }
    .product img {
      width: 100%;
      max-height: 200px;
      object-fit: contain;
      border-radius: 8px;
    }
    .pagination {
      text-align: center;
      padding: 20px;
    }
    .pagination button {
      padding: 10px 15px;
      margin: 0 5px;
      background-color: #ff7f00;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .cart-icon {
      position: relative;
      font-size: 20px;
      text-decoration: none;
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
    .product-buttons {
      margin-top: 10px;
    }
    .btn {
      padding: 8px 12px;
      margin: 5px;
      cursor: pointer;
      background-color: #ff7f00;
      color: white;
      border: none;
      border-radius: 5px;
    }
    .add-to-cart span {
      background: white;
      color: #ff7f00;
      margin-left: 5px;
      padding: 2px 6px;
      border-radius: 50%;
      font-size: 12px;
    }

    .stock {
  font-weight: bold;
  color: green;
}

.btn:disabled {
  background-color: #ccc;
  cursor: not-allowed;
  color: #666;
}

.stock.out-of-stock {
  color: red;
}

.message {
  background-color: #d4edda;
  color: #155724;
  padding: 10px 15px;
  margin: 20px 50px;
  border-left: 5px solid #28a745;
  border-radius: 5px;
  font-size: 16px;
}


  </style>
</head>
<body>

<header>
  <div class="logo">HiTech</div>
  <nav>
    <a href="home1.php">Home</a>
    <a href="smartphone.php" class="active">Shop</a>
    <a href="#">About Us</a>
    <a href="#">Contact Us</a>
  </nav>
   <div style="display: flex; align-items: center; gap: 10px;">
    <input type="text" placeholder="Search" style="padding: 5px 10px;">
    <a href="account_settings.php" style="text-decoration: none; font-size: 20px;">👤</a>
<a href="cart.php" class="cart-icon">🛒<span id="cart-count"><?= $totalCartItems ?></span></a>

  </div>
</header>

<div class="filter-container">
  <input type="text" id="search-bar" class="search-bar" placeholder="Search smartphones..." oninput="filterProducts()">
  <select id="sort-by" class="sort-select" onchange="sortProducts()">
    <option value="best-match">All</option>
    <option value="highest-price">Highest Price</option>
    <option value="lowest-price">Lowest Price</option>
  </select>
</div>

<div class="category-bar">
  <?php foreach ($categories as $category): ?>
    <div class="category" onclick="filterByCategory('<?= htmlspecialchars($category) ?>')">
      <?= htmlspecialchars($category) ?>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($message): ?>
  <div class="message" id="message"><?= $message ?></div>
<?php endif; ?>

<div class="product-container" id="product-container">
<?php foreach ($products as $product): 
    $isOutOfStock = $product['quantity'] == 0;
?>
  <div class="product"
       data-name="<?= strtolower($product['name']); ?>"
       data-price="<?= preg_replace('/[^\d]/', '', $product['price']); ?>">
    <img src="<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
    <p><strong><?= htmlspecialchars($product['name']); ?></strong></p>
    <p class="price"><?= htmlspecialchars($product['price']); ?></p>
    <p class="description"><?= htmlspecialchars($product['description']); ?></p>
    <p class="stock <?= $isOutOfStock ? 'out-of-stock' : '' ?>">
      <?= $isOutOfStock ? 'Out of Stock' : 'Available: ' . $product['quantity'] . ' pcs'; ?>
    </p>
    <div class="product-buttons">
      <button class="btn buy-now-btn"
        data-name="<?= htmlspecialchars($product['name']); ?>"
        data-price="<?= htmlspecialchars($product['price']); ?>"
        data-image="<?= htmlspecialchars($product['image']); ?>"
        data-description="<?= htmlspecialchars($product['description']); ?>"
        data-quantity="<?= htmlspecialchars($product['quantity']); ?>"
        <?= $isOutOfStock ? 'disabled' : '' ?>>
        Buy Now
      </button>

      <form method="post" action="smartphone.php" class="add-to-cart-form">
        <input type="hidden" name="name" value="<?= htmlspecialchars($product['name']); ?>">
        <input type="hidden" name="price" value="<?= htmlspecialchars($product['price']); ?>">
        <input type="hidden" name="image" value="<?= htmlspecialchars($product['image']); ?>">
        <input type="hidden" name="description" value="<?= htmlspecialchars($product['description']); ?>">
        <button type="submit" name="add_to_cart" class="btn" <?= $isOutOfStock ? 'disabled' : '' ?>>🛒 Add to Cart</button>
      </form>
    </div>
  </div>
<?php endforeach; ?>

</div>

<div id="buyModal" style="display:none; position:fixed; top:20%; left:30%; width:40%; background:white; padding:20px; border:1px solid #ccc; border-radius:10px; z-index:1000;">
  <h3>Enter Quantity</h3>
  <p id="selectedProductName"></p>
  <div>
    <button onclick="changeQty(-1)">-</button>
    <input type="number" id="qtyInput" value="1" min="1" style="width: 50px;" readonly>
    <button onclick="changeQty(1)">+</button>
  </div>
  <button id="proceedToCheckout" class="btn" style="margin-top:10px;">Proceed to Checkout</button>
  <button onclick="document.getElementById('buyModal').style.display='none'" class="btn" style="background:#ccc;">Cancel</button>
</div>





<script>

  let selectedProduct = {};

document.querySelectorAll('.buy-now-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    selectedProduct = {
      name: btn.dataset.name,
      price: btn.dataset.price,
      image: btn.dataset.image,
      description: btn.dataset.description,
      quantityAvailable: parseInt(btn.dataset.quantity),
    };
    document.getElementById('selectedProductName').textContent = selectedProduct.name + " (Available: " + selectedProduct.quantityAvailable + ")";
    document.getElementById('qtyInput').value = 1;
    document.getElementById('buyModal').style.display = 'block';
  });
});

function changeQty(change) {
  const input = document.getElementById('qtyInput');
  let qty = parseInt(input.value);
  qty += change;
  if (qty < 1) qty = 1;
  if (qty > selectedProduct.quantityAvailable) qty = selectedProduct.quantityAvailable;
  input.value = qty;
}

document.getElementById('proceedToCheckout').addEventListener('click', () => {
  const qty = document.getElementById('qtyInput').value;
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'buy.php';

  ['name', 'price', 'image', 'description'].forEach(key => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = key;
    input.value = selectedProduct[key];
    form.appendChild(input);
  });

  const qtyInput = document.createElement('input');
  qtyInput.type = 'hidden';
  qtyInput.name = 'quantity';
  qtyInput.value = qty;
  form.appendChild(qtyInput);

  document.body.appendChild(form);
  form.submit();
});

  // Hide success message after 3 seconds
  setTimeout(() => {
    const msg = document.getElementById('message');
    if (msg) {
      msg.style.display = 'none';
    }
  }, 3000);
</script>

</body>
</html>


<div class="pagination" id="pagination"></div>

<script>
  let allProducts = [];
  let currentPage = 1;
  const itemsPerPage = 6;

  // Collect PHP-rendered products into JS
  document.querySelectorAll('.product').forEach(el => {
    allProducts.push({
      element: el,
      name: el.dataset.name,
      price: parseFloat(el.dataset.price)
    });
  });

  function displayProducts(products) {
    document.querySelectorAll('.product').forEach(p => p.style.display = 'none');

    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;

    products.slice(start, end).forEach(p => {
      p.element.style.display = 'block';
    });
  }

  function setupPagination(products) {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';
    const totalPages = Math.ceil(products.length / itemsPerPage);

    for (let i = 1; i <= totalPages; i++) {
      const btn = document.createElement('button');
      btn.textContent = i;
      btn.onclick = () => {
        currentPage = i;
        displayProducts(products);
      };
      pagination.appendChild(btn);
    }
  }

  function filterProducts() {
    const search = document.getElementById('search-bar').value.toLowerCase();
    const filtered = allProducts.filter(p => p.name.includes(search));
    currentPage = 1;
    displayProducts(filtered);
    setupPagination(filtered);
  }

  function filterByCategory(category) {
  const products = allProducts.filter(p => p.element.querySelector('p strong').textContent.toLowerCase().includes(category.toLowerCase()));
  currentPage = 1;
  displayProducts(products);
  setupPagination(products);
}


  function sortProducts() {
    const sortBy = document.getElementById('sort-by').value;
    let sorted = [...allProducts];

    if (sortBy === 'highest-price') {
      sorted.sort((a, b) => b.price - a.price);
    } else if (sortBy === 'lowest-price') {
      sorted.sort((a, b) => a.price - b.price);
    }

    currentPage = 1;
    displayProducts(sorted);
    setupPagination(sorted);
  }

  window.onload = () => {
    displayProducts(allProducts);
    setupPagination(allProducts);
  };
</script>

</body>
</html>
