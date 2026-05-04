<?php
session_start();
require_once 'includes/db_connect.php';

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            
            // Redirect based on role
            if($row['role'] === 'admin'){
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $msg = "Incorrect password.";
        }
    } else {
        $msg = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - Aviation Family</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* INLINE STYLES TO FORCE DESIGN IF CSS FAILS */
        .auth-body {
            display: flex; align-items: center; justify-content: center;
            height: 100vh; background: #e2e8f0; margin: 0;
        }
        .auth-card {
            background: white; padding: 40px; border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); width: 100%; max-width: 400px;
        }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; color: #475569; font-weight: 600; }
        .input-group input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; }
    </style>
</head>
<body class="auth-body">

    <div class="auth-card">
        <h2 style="text-align: center; color: #002147; margin-bottom: 20px;">Welcome Back</h2>
        
        <?php if($msg): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Username or Email</label>
                <input type="text" name="username" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-filled" style="width: 100%; border: none; cursor: pointer;">Login</button>
        </form>

        <p style="text-align: center; margin-top: 20px;">
            New here? <a href="signup.php" style="color: #FFC107; font-weight: bold;">Create Account</a>
        </p>
        <div style="text-align: center; margin-top: 10px;">
            <a href="index.php" style="color: #666; text-decoration: none;">&larr; Back to Home</a>
        </div>
    </div>

</body>
</html>