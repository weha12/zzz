<?php
// add_category.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newCategory = trim($_POST['category_name'] ?? '');

    if ($newCategory === '') {
        $error = "Category name cannot be empty.";
    } else {
        $file = 'categories.json';

        // Load existing categories
        if (file_exists($file)) {
            $categories = json_decode(file_get_contents($file), true);
            if (!is_array($categories)) {
                $categories = [];
            }
        } else {
            $categories = [];
        }

        // Check for duplicates
        if (in_array($newCategory, $categories)) {
            $error = "Category already exists.";
        } else {
            $categories[] = $newCategory;
            // Save back to file
            file_put_contents($file, json_encode($categories, JSON_PRETTY_PRINT));
            $success = "Category added successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Category</title>
  <style>
  body {
    background-color: #121212;
    color: #E4E4E4;
    font-family: Arial, sans-serif;
    padding: 20px;
   
  }

  form {
    background-color: #1e1e1e;
    padding: 40px;
    border-radius: 8px;
    max-width: 400px;
    margin: auto;
    text-align: center;
  }

  label {
    color: #B0B0B0;
    font-size: 16px;
    display: block;
   text-align: center;
    margin-bottom: 5px;
  }

  input[type="text"] {
    background-color: #333;
    color: #E4E4E4;
    border: 1px solid #444;
    padding: 10px;
    width: 100%;
    margin-bottom: 15px;
    border-radius: 5px;
  }

  .button-group {
    display: flex;
    justify-content: space-between;
    gap: 10px;
  }

  .button-group button {
    background-color: #1DA1F2;
    color: white;
    border: none;
    padding: 10px 0;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    width: 48%;
  }

  .button-group button:hover {
    opacity: 0.9;
  }

  .message {
    margin-bottom: 15px;
  }

  .error {
    color: #FF6666;
  }

  .success {
    color: #66FF66;
  }
</style>

</head>
<body>

<form method="POST" id="category-form">
  <h2>Add New Category</h2>

  <?php if (!empty($error)): ?>
    <div class="message error"><?= htmlspecialchars($error) ?></div>
  <?php elseif (!empty($success)): ?>
    <div class="message success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <label for="category_name">Category Name:</label>
  <input type="text" id="category_name" name="category_name" required>

<div class="button-group">
    <button type="submit">Add Category</button>
    <button type="button" onclick="window.location.href='crud.php'">Back</button>
  </div>
</form>

</body>
</html>
