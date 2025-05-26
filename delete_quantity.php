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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedProduct = $_POST['product_name'] ?? '';
    $deleteQty = (int)($_POST['delete_qty'] ?? 0);

    if ($deleteQty <= 0) {
        $message = "Please enter a valid quantity to delete.";
    } else {
        $xml = simplexml_load_file('product.xml');
        foreach ($xml->product as $prod) {
            if ((string)$prod->name === $selectedProduct) {
                $currentQty = (int)$prod->quantity;
                if ($currentQty === 0) {
                    $message = "Quantity is already 0.";
                } elseif ($currentQty - $deleteQty <= 0) {
                    $prod->quantity = 0;
                    $message = "Quantity set to 0.";
                    $xml->asXML('product.xml');
                } else {
                    $prod->quantity = $currentQty - $deleteQty;
                    $message = "Quantity successfully decreased.";
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
    <title>Delete Quantity</title>
    <style>
    body {
    background-color: #15202b;
    color: white;
    font-family: Arial;
    padding: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

form {
    background: #1f2231;
    padding: 20px;
    border-radius: 8px;
    width: 300px;
    text-align: center;
}

label, select, input[type="number"] {
    display: block;
    margin-bottom: 10px;
    width: 100%;
}

.button-group {
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.button-group input,
.button-group a {
    background: #e74c3c;
    border: none;
    padding: 10px;
    color: white;
    text-align: center;
    text-decoration: none;
    border-radius: 5px;
    width: 48%;
    cursor: pointer;
}

.message {
    margin-top: 15px;
    color: #1ed760;
}

    </style>
</head>
<body>



<form method="POST">
    <h2>Delete Quantity</h2>
    <label for="product_name">Select Product:</label>
    <select name="product_name" required>
        <option value="">-- Choose --</option>
        <?php foreach ($products as $prod): ?>
            <option value="<?= htmlspecialchars($prod['name']) ?>">
                <?= htmlspecialchars($prod['name']) ?> (Current: <?= $prod['quantity'] ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <label for="delete_qty">Quantity to Delete:</label>
    <input type="number" name="delete_qty" min="1" max="10" required>

 <div class="button-group">
    <input type="submit" value="Delete Quantity">
    <a href="crud.php">Back</a>
</div>

</form>

<?php if ($message): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

</body>
</html>
