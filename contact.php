<?php
session_start();
require_once 'includes/db_connect.php';

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $subject, $message);
    
    if ($stmt->execute()) {
        $msg = "Message sent successfully! We will contact you soon.";
    } else {
        $msg = "Error sending message.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Contact Us</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container" style="max-width: 600px;">
        <h1 class="page-title">Contact Us</h1>
        
        <?php if($msg): ?>
            <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <form method="POST">
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Your Name</label>
                <input type="text" name="name" required style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:6px;">
                
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Email Address</label>
                <input type="email" name="email" required style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:6px;">
                
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Subject</label>
                <input type="text" name="subject" required style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:6px;">
                
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Message</label>
                <textarea name="message" rows="5" required style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:6px;"></textarea>
                
                <button type="submit" class="btn-filled" style="width:100%;">Send Message</button>
            </form>
        </div>
    </div>
</body>
</html>