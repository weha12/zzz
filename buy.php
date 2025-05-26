<?php
session_start();
include('connect.php');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$name = $_POST['name'] ?? '';
$price = $_POST['price'] ?? '';
$image = $_POST['image'] ?? '';
$description = $_POST['description'] ?? '';
$quantity = intval($_POST['quantity'] ?? 1);

$checkoutSuccess = false;
$showForm = true;
$error = '';

// Sanitize price to remove any non-numeric symbols (like ₱)
$cleanPrice = floatval(preg_replace('/[^\d.]/', '', $price));

if (isset($_POST['checkout_submit'])) {
    $customerName = $_POST['customer_name'] ?? '';
    $address = $_POST['address'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $payment = $_POST['payment'] ?? '';
    $gcashNumber = $_POST['gcashNumber'] ?? '';

    if ($payment === 'GCash' && !$gcashNumber) {
        $error = "GCash number required.";
    } else {
        // Load product.xml to update stock
        $xml = simplexml_load_file('product.xml');
        $productFound = false;
        $total = 0;

        foreach ($xml->product as $prod) {
            if ((string)$prod->name === $name) {
                $productFound = true;
                $currentStock = intval($prod->quantity);
                if ($quantity <= $currentStock) {
                    $prod->quantity = $currentStock - $quantity;
                    $xml->asXML('product.xml');
                    $checkoutSuccess = true;
                    $showForm = false;
                    $total = $cleanPrice * $quantity;

                    $transactionFile = 'transaction.xml';

                    if (file_exists($transactionFile) && filesize($transactionFile) > 0) {
                        $xmlTrans = @simplexml_load_file($transactionFile);
                        if ($xmlTrans === false) {
                            $xmlTrans = new SimpleXMLElement('<transactions></transactions>');
                        }
                    } else {
                        $xmlTrans = new SimpleXMLElement('<transactions></transactions>');
                    }

                    $txn = $xmlTrans->addChild('transaction');
                    $txn->addChild('name', htmlspecialchars($customerName));
                    $txn->addChild('address', htmlspecialchars($address));
                    $txn->addChild('contact', htmlspecialchars($contact));
                    $txn->addChild('payment', htmlspecialchars($payment));
                    if ($payment === 'GCash') {
                        $txn->addChild('gcashNumber', htmlspecialchars($gcashNumber));
                    }
                    $txn->addChild('total', number_format($total, 2));
                    $txn->addChild('timestamp', date('Y-m-d H:i:s'));

                    // Add items
                    $items = $txn->addChild('items');
                    if (isset($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $cartItem) {
                            $item = $items->addChild('item');
                            $item->addChild('name', htmlspecialchars($cartItem['name']));
                            $item->addChild('price', number_format($cartItem['price'], 2));
                            $item->addChild('quantity', $cartItem['quantity']);
                        }
                    }

                    $xmlTrans->asXML($transactionFile);

                    unset($_SESSION['cart']); // clear cart
                } else {
                    $error = "Not enough stock.";
                }
                break;
            }
        }

        if (!$productFound && !$error) {
            $error = "Product not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
      <style>
         body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f4f6f9;
        margin: 0;
        padding: 0;
    }

    #checkoutModal {
        max-width: 350px; /* Smaller width */
        margin: 60px auto;
        background: white;
        border-radius: 12px;
        padding: 20px; /* Less padding */
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    h2 {
        text-align: center;
        color: #2c3e50;
        font-size: 22px;
        margin-bottom: 15px;
    }

    h3 {
        color: #27ae60;
        font-size: 18px;
    }

    strong {
        font-weight: 600;
    }

    input[type="text"], select {
        width: 100%;
        padding: 8px;
        margin: 8px 0;
        box-sizing: border-box;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 14px;
    }

    button {
        width: 100%;
        background-color: #2980b9;
        color: white;
        padding: 10px;
        margin-top: 10px;
        border: none;
        border-radius: 5px;
        font-size: 15px;
        cursor: pointer;
    }

    button:hover {
        background-color: #1c5980;
    }

    .summary {
        background: #ecf0f1;
        padding: 12px;
        margin: 12px 0;
        border-radius: 8px;
        font-size: 14px;
    }

    a {
        display: inline-block;
        margin-top: 12px;
        color: #2980b9;
        text-decoration: none;
        font-weight: bold;
        font-size: 14px;
    }

    a:hover {
        text-decoration: underline;
    }

    p.error {
        color: #c0392b;
        font-weight: bold;
        font-size: 14px;
    }

    /* Style for the back button */
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
        <div style="text-align:center;">
            <h3>✅ Thank you for your purchase!</h3>
            <p>The selected items have been successfully checked out.</p>
            <a href="smartphone.php">Continue Shopping</a>
        </div>
    <?php elseif (!$showForm): ?>
        <p style="color:red;">❌ <?= $error ?: 'An unknown error occurred.' ?></p>
    <?php else: ?>
        <div>
            <strong><?= htmlspecialchars($name) ?></strong><br>
            Quantity: <?= $quantity ?><br>
            Price per item: ₱<?= number_format($cleanPrice, 2) ?><br>
            <strong>Total: ₱<?= number_format($cleanPrice * $quantity, 2) ?></strong><br>
        </div>

        <?php if (!empty($error)): ?>
            <p style="color:red;"><?= $error ?></p>
        <?php endif; ?>

        <form method="post" onsubmit="return validateCheckoutForm()">
            <input type="hidden" name="checkout_submit" value="1">
            <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
            <input type="hidden" name="price" value="<?= htmlspecialchars($price) ?>">
            <input type="hidden" name="quantity" value="<?= $quantity ?>">

            <input type="text" name="customer_name" placeholder="Full Name" required><br>
            <input type="text" name="address" placeholder="Address" required><br>
            <input type="text" name="contact" placeholder="Contact Number" required><br>

            <select name="payment" id="paymentMethod" onchange="handlePaymentChange(this.value)" required>
                <option value="">Select Payment Method</option>
                <option value="GCash">GCash</option>
                <option value="COD">Cash on Delivery</option>
            </select><br>

            <div id="gcashFields" style="display:none;">
                <input type="text" name="gcashNumber" placeholder="GCash Number"><br>
            </div>

            <button type="submit">Submit</button>
         <a href="smartphone.php" class="back-button">Back</a>


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
