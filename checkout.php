<?php
session_start();

$selectedItems = [];
$checkoutSuccess = false;

if (empty($_SESSION['cart']) || 
   (!isset($_POST['selected_items']) && !isset($_POST['checkout_submit']))) {
    header("Location: cart.php");
    exit;
}

if (isset($_POST['selected_items'])) {
    $selectedIndexes = $_POST['selected_items'];
    foreach ($selectedIndexes as $index) {
        if (isset($_SESSION['cart'][$index])) {
            $selectedItems[] = $_SESSION['cart'][$index];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_submit'])) {
    $selectedIndexes = $_POST['selected_items'] ?? [];

    // Step 1: Collect selected items and calculate total
    $selectedItems = [];
    $totalAmount = 0;

    foreach ($selectedIndexes as $index) {
        if (isset($_SESSION['cart'][$index])) {
            $item = $_SESSION['cart'][$index];
            $item['price'] = floatval(str_replace([',', '₱'], '', $item['price']));
            $item['quantity'] = intval($item['quantity']); // Ensure it's an integer
            $selectedItems[] = $item;
            $totalAmount += $item['price'] * $item['quantity'];
        }
    }

// Step 2: Save transaction
    $transactionXmlFile = 'transaction.xml';

    if (!file_exists($transactionXmlFile) || filesize($transactionXmlFile) === 0) {
        $xml = new SimpleXMLElement('<transactions></transactions>');
    } else {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($transactionXmlFile);
        if ($xml === false) {
            $xml = new SimpleXMLElement('<transactions></transactions>');
        }
    }

    // Step 3: Collect form input
    $transaction = [
        'name' => $_POST['name'],
        'address' => $_POST['address'],
        'contact' => $_POST['contact'],
        'payment' => $_POST['payment'],
        'gcashNumber' => $_POST['gcashNumber'] ?? '',
        'total' => $totalAmount,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    // Step 4: Add transaction to XML
    $transactionElement = $xml->addChild('transaction');
    $transactionElement->addChild('name', htmlspecialchars($transaction['name']));
    $transactionElement->addChild('address', htmlspecialchars($transaction['address']));
    $transactionElement->addChild('contact', htmlspecialchars($transaction['contact']));
    $transactionElement->addChild('payment', htmlspecialchars($transaction['payment']));
    $transactionElement->addChild('gcashNumber', htmlspecialchars($transaction['gcashNumber']));
    $transactionElement->addChild('total', $transaction['total']);
    $transactionElement->addChild('timestamp', $transaction['timestamp']);

    $itemsElement = $transactionElement->addChild('items');
    foreach ($selectedItems as $item) {
        $itemElement = $itemsElement->addChild('item');
        $itemElement->addChild('name', htmlspecialchars($item['name']));
        $itemElement->addChild('quantity', $item['quantity']);
        $itemElement->addChild('price', $item['price']);
    }

    $xml->asXML($transactionXmlFile);

    // Load product.xml
$productXmlFile = 'product.xml';
if (file_exists($productXmlFile)) {
    $productXml = simplexml_load_file($productXmlFile);

    // Loop through each item in the cart and update the stock
    foreach ($selectedItems as $cartItem) {
        foreach ($productXml->product as $product) {
            if ((string)$product->name === (string)$cartItem['name']) {
                $currentStock = (int)$product->quantity;
                $purchasedQuantity = (int)$cartItem['quantity'];
                $newStock = max(0, $currentStock - $purchasedQuantity);
                $product->quantity = $newStock;
                break;
            }
        }
    }

    // Save the updated XML back to product.xml
    $productXml->asXML($productXmlFile);
}




    // Step 4: Remove from session cart
    foreach ($selectedIndexes as $index) {
        if (isset($_SESSION['cart'][$index])) {
            unset($_SESSION['cart'][$index]);
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    $checkoutSuccess = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Checkout</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
    #checkoutModal { max-width: 400px; margin: auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
    h2 { text-align: center; }
    input, select {
      width: 100%;
      margin: 10px 0;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 3px;
      box-sizing: border-box;
    }
    button {
      width: 100%;
      padding: 10px;
      background: #3498db;
      color: #fff;
      border: none;
      cursor: pointer;
      border-radius: 3px;
      font-size: 16px;
    }
    button:hover { background: #2980b9; }
    .back-btn {
      background: #95a5a6;
      margin-top: 10px;
      text-align: center;
      display: inline-block;
      text-decoration: none;
      color: white;
      padding: 10px;
      border-radius: 3px;
      width: 100%;
      cursor: pointer;
    }
    .product-summary {
      background: #ecf0f1;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 5px;
      font-size: 14px;
    }
    #gcashFields {
      margin-bottom: 15px;
    }
     .back-button {
        display: inline-block;
        margin-bottom: 15px;
        padding: 8px 16px;
        background: #bdc3c7;
        color: #2c3e50;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
    }

    .back-button:hover {
        background: #a5b1b9;
    }
    .back-button {
    display: block;
    width: 100%;
    text-align: center;
    padding: 10px 0;
    margin-top: 12px;
    background-color: #e0e0e0;
    color: #2c3e50;
    border-radius: 6px;
    text-decoration: none;
    font-size: 15px;
    font-weight: 600;
    transition: background-color 0.3s ease;
}

.back-button:hover {
    background-color: #d0d0d0;
}

  </style>
</head>
<body>

<div id="checkoutModal">
  <h2>Checkout Form</h2>

  <?php if ($checkoutSuccess): ?>
    <div class="product-summary" style="text-align:center;">
      <h3>✅ Thank you for your purchase!</h3>
      <p>Your items have been purchased and stock has been updated.</p>
      <a href="smartphone.php" class="back-btn">Continue Shopping</a>
      <a href="cart.php" class="back-btn" style="background:#95a5a6;">Back to Cart</a>
    </div>

  <?php elseif (empty($selectedItems)): ?>
    <p>No items selected. <a href="cart.php">Go back to cart</a></p>

  <?php else: ?>
    <?php foreach ($selectedItems as $item): ?>
      <div class="product-summary">
        <strong><?= htmlspecialchars($item['name']) ?></strong><br>
        Quantity: <?= intval($item['quantity']) ?><br>
    
      </div>
    <?php endforeach; ?>

    <form method="post" id="checkoutForm" onsubmit="return validateCheckoutForm()">
      <input type="hidden" name="checkout_submit" value="1">
      <?php foreach ($_POST['selected_items'] as $idx): ?>
        <input type="hidden" name="selected_items[]" value="<?= intval($idx) ?>">
      <?php endforeach; ?>

      <input type="text" name="name" placeholder="Full Name" required>
      <input type="text" name="address" placeholder="Address" required>
      <input type="text" name="contact" placeholder="Contact Number" required>

      <select name="payment" id="paymentMethod" onchange="handlePaymentChange(this.value)" required>
        <option value="">Select Payment Method</option>
        <option value="GCash">GCash</option>
        <option value="COD">Cash on Delivery</option>
      </select>

      <div id="gcashFields" style="display:none;">
        <input type="text" name="gcashNumber" placeholder="GCash Number">
      </div>

      <button type="submit">Submit</button>
       <a href="cart.php" class="back-button">Back</a>
    </form>
  <?php endif; ?>
</div>

<script>
function handlePaymentChange(value) {
  document.getElementById('gcashFields').style.display = value === 'GCash' ? 'block' : 'none';
}

function validateCheckoutForm() {
  const payment = document.getElementById('paymentMethod').value;
  if (payment === 'GCash') {
    const gcashNumber = document.querySelector('input[name="gcashNumber"]').value.trim();
    if (!gcashNumber) {
      alert("Please enter your GCash Number.");
      return false;
    }
  }
  return true;
}
</script>

</body>
</html>
