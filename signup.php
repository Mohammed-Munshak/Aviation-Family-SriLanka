<?php
session_start();
require_once 'includes/db_connect.php';

$msg = "";
$msg_type = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Clean and Prepare Inputs
    $username = strtolower(trim($_POST['username'])); 
    $email = strtolower(trim($_POST['email']));
    $whatsapp = trim($_POST['whatsapp']);
    $dob = $_POST['dob'];
    $password = $_POST['password'];

    // 2. Validation
    $errors = [];
    if (empty($username)) $errors[] = "Username is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";

    if (!empty($errors)) {
        $msg = implode(' ', $errors);
        $msg_type = "error";
    } else {
        // 3. CHECK IF USERNAME OR EMAIL EXISTS FIRST
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $msg = "Username or Email is already taken.";
            $msg_type = "error";
            $check->close();
        } else {
            $check->close();
            // 4. PERFORM THE INSERT (only columns that exist in DB)
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (username, email, password_hash, whatsapp_number, dob) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param("sssss", $username, $email, $hashed, $whatsapp, $dob);
            
            if ($insert->execute()) {
                $insert->close();
                // 5. REDIRECT IMMEDIATELY
                header("Location: login.php?msg=Registration+Successful!+Please+Login.");
                exit();
            } else {
                $msg = "Database Error: " . $conn->error;
                $msg_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign Up</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-body { display: flex; align-items: center; justify-content: center; height: 100vh; background: #e2e8f0; margin: 0; }
        .auth-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; color: #475569; font-weight: 600; }
        .input-group input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; }
        
        /* --- STRENGTH METER COLORS --- */
        .strength-meter { height: 5px; width: 100%; background: #e2e8f0; border-radius: 3px; margin-top: 8px; overflow: hidden; }
        .strength-bar { height: 100%; width: 0%; transition: width 0.3s, background-color 0.3s; }
        .weak { background-color: #ef4444 !important; } 
        .medium { background-color: #eab308 !important; }
        .strong { background-color: #22c55e !important; }
        .strength-text { font-size: 12px; margin-top: 5px; font-weight: 600; text-align: right; }
    </style>
</head>
<body class="auth-body">

    <div class="auth-card">
        <h2 style="text-align: center; color: #002147; margin-bottom: 20px;">Join the Family</h2>

        <?php if($msg): ?>
            <div style="background: <?php echo ($msg_type == 'error') ? '#fee2e2' : '#dcfce7'; ?>; 
                        color: <?php echo ($msg_type == 'error') ? '#991b1b' : '#166534'; ?>; 
                        padding: 10px; border-radius: 6px; margin-bottom: 15px;">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="input-group">
                    <label>WhatsApp</label>
                    <input type="text" name="whatsapp" required>
                </div>
            </div>

            <div class="input-group">
                <label>Date of Birth</label>
                <input type="date" name="dob" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" id="passwordInput" required>
                <div class="strength-meter"><div id="strengthBar" class="strength-bar"></div></div>
                <div id="strengthText" class="strength-text" style="color: #94a3b8;">Start typing...</div>
            </div>

            <button type="submit" class="btn-filled" style="width: 100%; border: none; cursor: pointer;">Create Account</button>
        </form>

        <p style="text-align: center; margin-top: 20px;">
            Already have an account? <a href="login.php" style="color: #FFC107; font-weight: bold;">Login here</a>
        </p>
    </div>

    <script>
        const passwordInput = document.getElementById('passwordInput');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const val = passwordInput.value;
            let strength = 0;
            if (val.length >= 6) strength++;
            if (val.match(/[0-9]/)) strength++;
            if (val.match(/[^A-Za-z0-9]/)) strength++;

            strengthBar.classList.remove('weak', 'medium', 'strong');

            if (val.length === 0) {
                strengthBar.style.width = '0%';
                strengthText.textContent = 'Start typing...';
                strengthText.style.color = '#94a3b8';
            } else if (val.length < 6) {
                strengthBar.classList.add('weak');
                strengthBar.style.width = '33%';
                strengthText.textContent = 'Too Short';
                strengthText.style.color = '#ef4444';
            } else if (strength < 3) {
                strengthBar.classList.add('medium');
                strengthBar.style.width = '66%';
                strengthText.textContent = 'Medium';
                strengthText.style.color = '#eab308';
            } else {
                strengthBar.classList.add('strong');
                strengthBar.style.width = '100%';
                strengthText.textContent = 'Strong Password';
                strengthText.style.color = '#22c55e';
            }
        });
    </script>
</body>
</html>