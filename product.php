<?php
session_start();
include "db.php";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];
$result = $mysqli->query("SELECT * FROM products WHERE id = $id");
$product = $result->fetch_assoc();

if (!$product) {
    echo "Product not found.";
    exit;
}

// Optional: handle add to cart form POST here

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($product['name']) ?></title>
  <style>
    /* simple styling here */
  </style>
</head>
<body>
  <h1><?= htmlspecialchars($product['name']) ?></h1>
  <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" />
  <p><?= htmlspecialchars($product['description']) ?></p>
  <p>Price: $<?= number_format($product['price'], 2) ?></p>
  <form method="post" action="index.php">
    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
    <button type="submit">Add to Cart</button>
  </form>
  <a href="index.php">Back to Products</a>
</body>
</html>
