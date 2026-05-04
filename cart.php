<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=Please login to checkout");
    exit;
}

$msg = "";

// --- HANDLE ORDER PLACEMENT ---
if (isset($_POST['place_order'])) {
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $p_id => $qty) {
            $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity, status) VALUES (?, ?, ?, 'pending')");
            $stmt->bind_param("iii", $_SESSION['user_id'], $p_id, $qty);
            $stmt->execute();
        }
        unset($_SESSION['cart']); // Clear cart
        $msg = "order_success";
    }
}

// Remove item from cart
if (isset($_GET['remove'])) {
    $id = $_GET['remove'];
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Cart</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container" style="max-width: 800px;">
        <h1 class="page-title">Shopping Cart</h1>

        <?php if($msg == "order_success"): ?>
            <div style="background: #dcfce7; color: #166534; padding: 20px; border-radius: 8px; text-align: center;">
                <h2>Order Placed Successfully! ✅</h2>
                <p>The Administrator will contact you via WhatsApp soon to arrange payment and delivery.</p>
                <a href="store.php" class="btn-nav btn-profile" style="margin-top:10px; display:inline-block;">Back to Shop</a>
            </div>
        <?php else: ?>

        <div style="background:white; padding:20px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05);">
            <?php if(!empty($_SESSION['cart'])): ?>
                <table style="width:100%; border-collapse: collapse;">
                    <tr style="border-bottom: 2px solid #eee; text-align: left;">
                        <th style="padding:10px;">Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                    <?php 
                    $grand_total = 0;
                    foreach($_SESSION['cart'] as $id => $qty): 
                        $res = $conn->query("SELECT * FROM products WHERE id = $id");
                        $p = $res->fetch_assoc();
                        $total = $p['price'] * $qty;
                        $grand_total += $total;
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding:15px 10px;"><?php echo $p['name']; ?></td>
                        <td>LKR <?php echo number_format($p['price'], 2); ?></td>
                        <td><?php echo $qty; ?></td>
                        <td><strong>LKR <?php echo number_format($total, 2); ?></strong></td>
                        <td><a href="?remove=<?php echo $id; ?>" style="color:#ef4444; font-size:0.8rem;">Remove</a></td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <div style="text-align: right; margin-top: 20px;">
                    <h3>Grand Total: LKR <?php echo number_format($grand_total, 2); ?></h3>
                    <form method="POST">
                        <button type="submit" name="place_order" class="btn-nav btn-login" style="padding: 12px 40px; border:none; cursor:pointer; font-size:1rem;">
                            Place Order Now
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <p style="text-align:center; color:#999;">Your cart is empty.</p>
                <center><a href="store.php" class="btn-nav btn-profile" style="text-decoration:none;">Go Shopping</a></center>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>