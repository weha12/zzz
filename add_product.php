<?php
// add_product.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');

    if ($name && $image && $description && $price) {
        $xmlFile = 'product.xml';

        if (file_exists($xmlFile)) {
            $xml = simplexml_load_file($xmlFile);

            // Add new product node
            $newProduct = $xml->addChild('product');
            $newProduct->addChild('name', htmlspecialchars($name));
            $newProduct->addChild('image', htmlspecialchars($image));
            $newProduct->addChild('description', htmlspecialchars($description));
            $newProduct->addChild('price', htmlspecialchars($price));

            // Save back to XML file
            $xml->asXML($xmlFile);

            $message = "Product added successfully!";
        } else {
            $message = "Product XML file not found!";
        }
    } else {
        $message = "Please fill in all fields!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Product</title>
     <style>
        body { font-family: Arial, sans-serif; padding: 20px; background:rgb(60, 51, 51); }
        form { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        label { display: block; margin: 15px 0 5px; }
        input[type=text], textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        .btn {
            margin-top: 15px;
            padding: 10px 20px;
            background: #ff7f00;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 4px;
            width: 48%;
            box-sizing: border-box;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            gap: 4%;
        }
        .message { margin: 10px 0; padding: 10px; background: #d4edda; color: #155724; border-radius: 4px; }
    </style>
</head>
<body>



<?php if (!empty($message)): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="post" action="add_product.php">
    <h2>Add New Product</h2>
    <label for="name">Product Name:</label>
    <input type="text" id="name" name="name" required />

    <label for="image">Image URL:</label>
    <input type="text" id="image" name="image" required placeholder="e.g. images/product1.jpg" />

    <label for="description">Description:</label>
    <textarea id="description" name="description" rows="4" required></textarea>

    <label for="price">Price:</label>
    <input type="text" id="price" name="price" required placeholder="e.g. ₱1000" />

    <div class="button-group">
        <button type="submit" class="btn">Add Product</button>
        <button type="button" class="btn" onclick="window.location.href='crud.php'">Back</button>
    </div>
</form>

</body>
</html>
