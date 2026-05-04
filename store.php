<?php
session_start();
require_once 'includes/db_connect.php';

// --- ADD TO CART LOGIC ---
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add product or increase quantity
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]++;
    } else {
        $_SESSION['cart'][$product_id] = 1;
    }
    header("Location: store.php?status=added");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pilot Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .price-tag { font-size: 1.2rem; color: var(--primary); font-weight: 800; display: block; margin: 10px 0; }
        .cart-count { background: #ef4444; color: white; padding: 2px 8px; border-radius: 50px; font-size: 0.8rem; vertical-align: middle; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="page-title">The Pilot Shop</h1>
            <a href="cart.php" class="btn-nav btn-profile" style="text-decoration:none;">
                🛒 My Cart 
                <?php if(isset($_SESSION['cart'])): ?>
                    <span class="cart-count"><?php echo array_sum($_SESSION['cart']); ?></span>
                <?php endif; ?>
            </a>
        </div>
        
        <div class="grid-cards">
            <?php
            $products = $conn->query("SELECT * FROM products ORDER BY id DESC");
            if($products && $products->num_rows > 0):
                while($item = $products->fetch_assoc()):
            ?>
                <div class="info-card">
                    <div class="card-img-wrapper" style="background: white; padding: 20px;">
                        <img src="<?php echo $item['image_path']; ?>" style="object-fit: contain; height: 180px;">
                    </div>
                    <div class="card-body">
                        <span style="font-size: 0.7rem; color: #999; text-transform: uppercase;"><?php echo $item['category']; ?></span>
                        <h3 style="margin: 5px 0;"><?php echo $item['name']; ?></h3>
                        <span class="price-tag">LKR <?php echo number_format($item['price'], 2); ?></span>
                        
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" name="add_to_cart" class="btn-nav btn-login" style="width:100%; border:none; cursor:pointer;">
                                Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; else: ?>
                <p>No products available yet.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>