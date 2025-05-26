<?php
$xmlFile = 'product.xml';
if (!file_exists($xmlFile)) {
    die("product.xml file not found.");
}

$xml = simplexml_load_file($xmlFile);
$id = isset($_GET['id']) ? (int)$_GET['id'] : -1;

if ($id < 0 || $id >= count($xml->product)) {
    die("Product not found.");
}

// Get product by index
$productToEdit = $xml->product[$id];
$message = '';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = $_POST['name'] ?? '';
    $newPrice = $_POST['price'] ?? '';
    $newImage = $_POST['image'] ?? '';
    $newDescription = $_POST['description'] ?? '';

    // Update product info
    $productToEdit->name = $newName;
    $productToEdit->price = $newPrice;
    $productToEdit->image = $newImage;
    $productToEdit->description = $newDescription;

    // Save XML file
    $xml->asXML($xmlFile);

    $message = "Product updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Update Product</title>
  <style>
    * {
  box-sizing: border-box;
}

body {
  margin: 0;
  font-family: 'Segoe UI', sans-serif;
  background-color:rgb(21, 18, 18);
  color: #f0f0f0;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  padding: 20px;
}

.container {
  max-width: 300px;
  width: 100%;
  background:rgb(247, 236, 236);
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
}

h2 {
  text-align: center;
  color: #ffffff;
  margin-bottom: 15px;
  font-size: 18px;
}

label {
  display: block;
  margin-top: 10px;
  font-weight: 500;
  color: #cccccc;
  font-size: 13px;
}

input[type="text"],
textarea {
  width: 100%;
  padding: 6px;
  margin-top: 4px;
  border: 1px solid #444;
  border-radius: 5px;
  background-color:rgb(81, 73, 73);
  color: #f0f0f0;
  font-size: 13px;
}

textarea {
  resize: vertical;
}

.button-group {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;
}

button {
  flex: 1;
  padding: 8px;
  border: none;
  border-radius: 6px;
  background-color: #ff7f00;
  color: white;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.3s ease;
  margin: 0 5px;
}

button:hover {
  background-color: #e86f00;
}

.message {
  background: #2e7d32;
  color: #d0f0d0;
  padding: 10px;
  margin-bottom: 20px;
  border-radius: 6px;
  text-align: center;
}

@media (max-width: 480px) {
  .button-group {
    flex-direction: column;
  }

  button {
    margin: 5px 0;
  }
}

  </style>
</head>
<body>



<?php if ($message): ?>
  <p style="color:green;"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="post" action="">
  <h2>Update Product</h2>
  <label>Name:</label><br>
  <input type="text" name="name" value="<?= htmlspecialchars($productToEdit->name) ?>" required><br><br>

  <label>Price:</label><br>
  <input type="text" name="price" value="<?= htmlspecialchars($productToEdit->price) ?>" required><br><br>

  <label>Image URL:</label><br>
  <input type="text" name="image" value="<?= htmlspecialchars($productToEdit->image) ?>" required><br><br>

  <label>Description:</label><br>
  <textarea name="description" rows="4" required><?= htmlspecialchars($productToEdit->description) ?></textarea><br><br>

  <button type="submit">Update Product</button>
  <button type="button" onclick="window.location.href='crud.php'">Cancel</button>
</form>

</body>
</html>
