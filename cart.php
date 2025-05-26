<?php
session_start();
include('connect.php');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart (keep your existing add to cart logic)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $image = $_POST['image'] ?? '';
    $description = $_POST['description'] ?? '';
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['name'] === $name) {
            $item['quantity'] += $quantity;
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
            'quantity' => $quantity,
        ];
    }

    header("Location: cart.php");
    exit;
}

// Handle remove item from cart
if (isset($_GET['remove'])) {
    $removeName = $_GET['remove'];
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['name'] === $removeName) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
    // Reindex the cart array after removal
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header("Location: cart.php");
    exit;
}

// Handle quantity update (when user submits the update button)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $index = intval($_POST['item_index']);
    $newQty = max(1, intval($_POST['new_quantity']));
    if (isset($_SESSION['cart'][$index])) {
        $_SESSION['cart'][$index]['quantity'] = $newQty;
    }
    header("Location: cart.php");
    exit;
}

$totalPrice = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Cart</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      padding: 15px;
      font-size: 14px;
      color: #333;
    }

    h1 {
      font-size: 20px;
      margin-bottom: 15px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      margin-bottom: 15px;
      font-size: 13px;
    }

    th, td {
      padding: 8px;
      border: 1px solid #eee;
      text-align: center;
      vertical-align: middle;
    }

    th {
      background-color: #f2f2f2;
      font-weight: bold;
    }

    td img {
      width: 50px;
      height: 50px;
      object-fit: contain;
      border: 1px solid #ccc;
      border-radius: 3px;
    }

    .actions a {
      color: #e74c3c;
      font-weight: bold;
      font-size: 12px;
      text-decoration: none;
    }

    .actions a:hover {
      text-decoration: underline;
    }

    .total {
      font-size: 15px;
      font-weight: bold;
      text-align: right;
      margin-top: 10px;
    }

    .back-btn {
      margin-top: 15px;
      padding: 8px 15px;
      background-color: #3498db;
      color: white;
      border: none;
      border-radius: 3px;
      font-size: 13px;
      cursor: pointer;
      text-decoration: none;
    }

    .back-btn:hover {
      background-color: #2980b9;
    }

    @media (max-width: 600px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }

      tr {
        margin-bottom: 10px;
        border-bottom: 1px solid #ccc;
      }

      td {
        padding-left: 45%;
        position: relative;
        text-align: right;
      }

      td::before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        width: 40%;
        text-align: left;
        font-weight: bold;
      }

      th {
        display: none;
      }
    }

    /* Quantity controls */
    .qty-controls {
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .qty-controls button {
      background: #3498db;
      border: none;
      color: white;
      width: 25px;
      height: 25px;
      font-weight: bold;
      cursor: pointer;
      border-radius: 3px;
      user-select: none;
    }
    .qty-controls input[type="number"] {
      width: 40px;
      text-align: center;
      margin: 0 5px;
      border: 1px solid #ccc;
      border-radius: 3px;
      font-size: 14px;
      -moz-appearance: textfield;
    }
    .qty-controls input[type=number]::-webkit-inner-spin-button,
    .qty-controls input[type=number]::-webkit-outer-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
    .qty-controls button:disabled {
      background: #aaa;
      cursor: not-allowed;
    }
  </style>
</head>
<body>

<h1>My Cart</h1>

<?php if (empty($_SESSION['cart'])): ?>
    <p>Your cart is empty. <a href="smartphone.php">Continue shopping</a></p>
<?php else: ?>
  <form method="post" action="checkout.php">
    <table>
      <thead>
        <tr>
          <th>Select</th>
          <th>Image</th>
          <th>Product</th>
          <th>Description</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Subtotal</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($_SESSION['cart'] as $index => $item):
          $priceNum = floatval(preg_replace('/[^\d\.]/', '', $item['price']));
          $subtotal = $priceNum * $item['quantity'];
          $totalPrice += $subtotal;
        ?>
        <tr>
          <td data-label="Select"><input type="checkbox" name="selected_items[]" value="<?= $index ?>"></td>
          <td data-label="Image"><img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"></td>
          <td data-label="Product"><?= htmlspecialchars($item['name']) ?></td>
          <td data-label="Description"><?= htmlspecialchars($item['description']) ?></td>
          <td data-label="Price"><?= htmlspecialchars($item['price']) ?></td>
          <td data-label="Quantity">
            <!-- Quantity update form -->
            <form method="post" style="display:inline-block; margin:0;">
              <div class="qty-controls">
                <input type="hidden" name="item_index" value="<?= $index ?>">
                <button type="button" onclick="changeQty(<?= $index ?>, -1)">-</button>
                <input type="number" id="qty_<?= $index ?>" name="new_quantity" value="<?= $item['quantity'] ?>" min="1" readonly>
                <button type="button" onclick="changeQty(<?= $index ?>, 1)">+</button>
                <button type="submit" name="update_quantity" title="Update Quantity" style="margin-left:10px; padding: 5px 8px;">Update</button>
              </div>
            </form>
          </td>
          <td data-label="Subtotal">₱<?= number_format($subtotal, 2) ?></td>
          <td data-label="Action">
            <a href="cart.php?remove=<?= urlencode($item['name']) ?>" onclick="return confirm('Remove this item?')">Remove</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <p class="total"><strong>Total Price: ₱<?= number_format($totalPrice, 2) ?></strong></p>

    <button type="submit" class="back-btn">Checkout Selected</button>
    <a href="smartphone.php" class="back-btn" style="background:#95a5a6;">Continue Shopping</a>
  </form>

<script>
function changeQty(index, delta) {
  const qtyInput = document.getElementById('qty_' + index);
  let currentQty = parseInt(qtyInput.value);
  if (isNaN(currentQty)) currentQty = 1;
  let newQty = currentQty + delta;
  if (newQty < 1) newQty = 1;
  qtyInput.value = newQty;

  // Update subtotal for this item
  // Get the price text in the same row's price column
  const priceCell = document.querySelectorAll('tbody tr')[index].querySelector('td[data-label="Price"]');
  const priceText = priceCell.innerText;
  const priceNum = parseFloat(priceText.replace(/[^0-9\.]+/g,"")) || 0;

  const newSubtotal = priceNum * newQty;

  // Update subtotal cell
  const subtotalCell = document.querySelectorAll('tbody tr')[index].querySelector('td[data-label="Subtotal"]');
  subtotalCell.innerText = '₱' + newSubtotal.toFixed(2);

  // Recalculate total price for all items
  let newTotal = 0;
  document.querySelectorAll('tbody tr').forEach(row => {
    const subCell = row.querySelector('td[data-label="Subtotal"]');
    if (subCell) {
      const val = parseFloat(subCell.innerText.replace(/[^0-9\.]+/g,"")) || 0;
      newTotal += val;
    }
  });
  document.querySelector('.total strong').innerText = 'Total Price: ₱' + newTotal.toFixed(2);
}
</script>

<?php endif; ?>

</body>
</html>
