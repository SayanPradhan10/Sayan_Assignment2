<?php
include 'db.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Handle quantity updates
if (isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $_SESSION['user_id']]);
    }

    $_SESSION['cart_message'] = "Cart updated successfully!";
    redirect('cart.php');
}

// Handle item removal
if (isset($_POST['remove_item'])) {
    $cart_id = $_POST['cart_id'];
    
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
    
    $_SESSION['cart_message'] = "Item removed from cart!";
    redirect('cart.php');
}

// Get cart items with product details
$stmt = $pdo->prepare("
    SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.description, p.price, p.image 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Get cart count
$cart_count = 0;
$stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$result = $stmt->fetch();
$cart_count = $result['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Product System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #4a90e2;
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
        }
        .nav-links a:hover {
            opacity: 0.8;
        }
        .cart-count {
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            font-size: 12px;
            margin-left: 5px;
        }
        .message {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
        }
        .cart-title {
            margin-bottom: 20px;
            font-size: 28px;
            color: #333;
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }
        .cart-table th {
            background-color: #4a90e2;
            color: white;
            padding: 15px;
            text-align: left;
        }
        .cart-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .cart-table tr:last-child td {
            border-bottom: none;
        }
        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .product-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        .product-price {
            color: #666;
        }
        .quantity-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
        }
        .update-btn {
            background-color: #4a90e2;
            color: white;
        }
        .update-btn:hover {
            background-color: #3a7bc8;
        }
        .remove-btn {
            background-color: #e74c3c;
            color: white;
            margin-left: 10px;
        }
        .remove-btn:hover {
            background-color: #c0392b;
        }
        .cart-summary {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .summary-box {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            width: 300px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .total-row {
            font-weight: bold;
            font-size: 18px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
        .checkout-btn {
            background-color: #2ecc71;
            color: white;
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            font-weight: 500;
        }
        .checkout-btn:hover {
            background-color: #27ae60;
        }
        .empty-cart {
            text-align: center;
            padding: 50px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .empty-cart p {
            margin-bottom: 20px;
            color: #666;
        }
        .continue-shopping {
            background-color: #4a90e2;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
        }
        .continue-shopping:hover {
            background-color: #3a7bc8;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">Product System</div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="cart.php">Cart <span class="cart-count"><?= $cart_count ?></span></a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <?php if (isset($_SESSION['cart_message'])): ?>
            <div class="message success-message"><?= $_SESSION['cart_message'] ?></div>
            <?php unset($_SESSION['cart_message']); ?>
        <?php endif; ?>
        
        <h1 class="cart-title">Your Shopping Cart</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="index.php" class="continue-shopping">Continue Shopping</a>
            </div>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <img src="images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-image">
                                    <div>
                                        <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                                        <div class="product-price">₹<?= number_format($item['price'], 2) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>₹<?= number_format($item['price'], 2) ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="quantity-input">
                                    <button type="submit" name="update_quantity" class="btn update-btn">Update</button>
                                </form>
                            </td>
                            <td>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                    <button type="submit" name="remove_item" class="btn remove-btn">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="cart-summary">
                <div class="summary-box">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>₹<?= number_format($total, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>Free</span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total:</span>
                        <span>₹<?= number_format($total, 2) ?></span>
                    </div>
                    <button class="btn checkout-btn">Proceed to Checkout</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>