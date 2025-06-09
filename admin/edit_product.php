<?php include '../db.php'; 

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

if (!isset($_GET['id'])) {
    redirect('dashboard.php');
}

$product_id = $_GET['id'];


$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('dashboard.php');
}

$errors = [];
$name = $product['name'];
$description = $product['description'];
$price = $product['price'];
$image = $product['image'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $image_url = trim($_POST['image_url']);
    
  
    if (empty($name)) {
        $errors[] = "Product name is required";
    }
    
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = "Valid price is required";
    }
    
    
    if (!empty($image_url)) {
        if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
            $errors[] = "Invalid image URL format";
        } else {
            $image = $image_url;
        }
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ? WHERE id = ?");
        if ($stmt->execute([$name, $description, $price, $image, $product_id])) {
            $_SESSION['success_message'] = "Product updated successfully!";
            redirect('dashboard.php');
        } else {
            $errors[] = "Failed to update product";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin Panel</title>
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
        .page-title {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #4a90e2;
            outline: none;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-primary {
            background-color: #4a90e2;
            color: white;
        }
        .btn-primary:hover {
            background-color: #3a7bc8;
        }
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        .error {
            color: #e74c3c;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #fadbd8;
            border-radius: 4px;
        }
        .current-image {
            max-width: 100%;
            max-height: 300px;
            margin-top: 10px;
            display: block;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .image-preview-container {
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">Admin Panel</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="add_product.php">Add Product</a>
                <a href="../index.php">View Site</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <h1 class="page-title">Edit Product</h1>
        
        <div class="form-container">
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $error): ?>
                        <p><?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required><?= htmlspecialchars($description) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?= htmlspecialchars($price) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="image_url">Image URL</label>
                    <input type="url" id="image_url" name="image_url" value="<?= htmlspecialchars($image) ?>" placeholder="Enter full image URL">
                    <div class="image-preview-container">
                        <p>Current Image Preview:</p>
                        <img src="<?= htmlspecialchars($image) ?>" alt="Product Image" class="current-image" id="currentImagePreview">
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        
        document.getElementById('image_url').addEventListener('input', function(e) {
            const preview = document.getElementById('currentImagePreview');
            const url = e.target.value.trim();
            
            if (url) {
                preview.src = url;
            }
        });
    </script>
</body>
</html>
