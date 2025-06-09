<?php include 'db.php'; 

if (!isLoggedIn()) {
    redirect('login.php');
}

if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['user_id'];
    

    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
       
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
        $stmt->execute([$existing['id']]);
    } else {
        
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $product_id]);
    }
    
    $_SESSION['cart_message'] = "Product added to cart!";
    redirect('index.php');
}


$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


$cart_count = 0;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    $cart_count = $result['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List - Product System</title>
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
        .welcome-message {
            margin-bottom: 20px;
            font-size: 18px;
            color: #666;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        .product-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .product-image {
            height: 200px;
            overflow: hidden;
        }
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        .product-info {
            padding: 20px;
        }
        .product-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        .product-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-price {
            font-size: 20px;
            font-weight: bold;
            color: #4a90e2;
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            background-color: #4a90e2;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }
        .btn:hover {
            background-color: #3a7bc8;
        }
        .view-details {
            background-color: #f5f5f5;
            color: #333;
            margin-top: 10px;
        }
        .view-details:hover {
            background-color: #e0e0e0;
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
        .product-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        .modal-product-image {
            width: 100%;
            height: 300px;
            object-fit: contain;
            margin-bottom: 20px;
            background-color: #f5f5f5;
            border-radius: 4px;
        }
        .modal-product-name {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }
        .modal-product-price {
            font-size: 22px;
            color: #4a90e2;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .modal-product-description {
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
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
        
        <h1>Our Products</h1>
        <p class="welcome-message">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
        
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="product-description"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                        <div class="product-price">₹<?= number_format($product['price'], 2) ?></div>

                        <form method="POST" action="">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
                        </form>
                        <button class="btn view-details" onclick="showProductDetails(<?= htmlspecialchars(json_encode($product)) ?>)">View Details</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="product-modal" id="productModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <img src="" alt="" class="modal-product-image" id="modalProductImage">
            <h2 class="modal-product-name" id="modalProductName"></h2>
            <div class="modal-product-price" id="modalProductPrice"></div>
            <p class="modal-product-description" id="modalProductDescription"></p>
            <form method="POST" action="">
                <input type="hidden" name="product_id" id="modalProductId">
                <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
            </form>
        </div>
    </div>
    
    <script>
        function showProductDetails(product) {
            document.getElementById('modalProductImage').src = product.image;
            document.getElementById('modalProductName').textContent = product.name;
            document.getElementById('modalProductPrice').textContent = '$' + parseFloat(product.price).toFixed(2);
            document.getElementById('modalProductDescription').textContent = product.description;
            document.getElementById('modalProductId').value = product.id;
            document.getElementById('productModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>