<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$products = [];

if (file_exists('product.xml')) {
    $xml = simplexml_load_file('product.xml');
    foreach ($xml->product as $prod) {
        $products[] = [
            'name' => (string)$prod->name,
            'quantity' => (int)$prod->quantity
        ];
    }
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedProduct = $_POST['product_name'] ?? '';
    $addQty = (int)($_POST['add_qty'] ?? 0);

    if ($addQty <= 0) {
        $message = "Please enter a valid quantity.";
    } else {
        $xml = simplexml_load_file('product.xml');
        foreach ($xml->product as $prod) {
            if ((string)$prod->name === $selectedProduct) {
                $currentQty = (int)$prod->quantity;
                if ($currentQty >= 10) {
                    $message = "The quantity is full.";
                } elseif ($currentQty + $addQty > 10) {
                    $prod->quantity = 10;
                    $message = "Quantity set to maximum (10).";
                    $xml->asXML('product.xml');
                } else {
                    $prod->quantity = $currentQty + $addQty;
                    $message = "Quantity successfully updated.";
                    $xml->asXML('product.xml');
                }
                break;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Quantity</title>
    <style>
           body {
            background-color: #15202b;
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .form-container {
            background:rgb(247, 247, 249);
            padding: 30px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        form label, form select, form input[type="number"] {
            display: block;
            margin-bottom: 15px;
            width: 100%;
        }

        .button-group {
            display: flex;
            gap: 10px;
        }

        input[type="submit"], .back-button {
            flex: 1;
            text-align: center;
            background: #2ecc71;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            font-size: 15px;
            transition: background 0.3s ease;
        }

        .back-button {
            background: #3498db;
        }

        input[type="submit"]:hover {
            background: #27ae60;
        }

        .back-button:hover {
            background: #2980b9;
        }

        .message {
            margin-top: 15px;
            color: #1ed760;
            text-align: center;
        }
    </style>
</head>
<body>


<form method="POST">
    <h2>Add Quantity</h2>
    <label for="product_name">Select Product:</label>
    <select name="product_name" required>
        <option value="">-- Choose --</option>
        <?php foreach ($products as $prod): ?>
            <option value="<?= htmlspecialchars($prod['name']) ?>">
                <?= htmlspecialchars($prod['name']) ?> (Current: <?= $prod['quantity'] ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <label for="add_qty">Quantity to Add:</label>
    <input type="number" name="add_qty" min="1" max="10" required>

      <div class="button-group">
            <input type="submit" value="Update">
            <a href="crud.php" class="back-button">Back</a>
        </div>
</form>

<?php if ($message): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>



</body>
</html>
