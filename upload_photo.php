<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); exit;
}

$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photo'])) {
    $target_dir = "assets/uploads/spotting/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    
    $filename = time() . "_" . basename($_FILES["photo"]["name"]);
    $target_file = $target_dir . $filename;
    
    $check = getimagesize($_FILES["photo"]["tmp_name"]);
    if($check !== false) {
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            
            // LOGIC: IF ADMIN -> APPROVED, ELSE -> PENDING
            $status = 'pending'; // Default
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                $status = 'approved';
            }

            $stmt = $conn->prepare("INSERT INTO spotting_photos (user_id, image_path, airline, aircraft_model, reg_number, location, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            // Added one more 's' to types string for status
            $stmt->bind_param("issssss", $_SESSION['user_id'], $target_file, $_POST['airline'], $_POST['model'], $_POST['reg'], $_POST['location'], $status);
            $stmt->execute();
            
            // Redirect based on status
            if ($status === 'approved') {
                header("Location: profile.php?msg=Photo+uploaded+successfully!");
            } else {
                header("Location: profile.php?msg=Photo+uploaded!+Waiting+for+approval.");
            }
            exit;
        } else {
            $msg = "Error moving file.";
        }
    } else {
        $msg = "File is not an image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Upload Photo</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container" style="max-width: 600px;">
        <h1 class="page-title">Upload Spotting Photo</h1>
        
        <?php if($msg): ?>
            <div style="background:#fee2e2; color:#991b1b; padding:10px; border-radius:6px; margin-bottom:15px;"><?php echo $msg; ?></div>
        <?php endif; ?>

        <div style="background:white; padding:30px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05);">
            <form method="POST" enctype="multipart/form-data">
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Photo File</label>
                <input type="file" name="photo" required style="margin-bottom:15px; width:100%; border:1px solid #ddd; padding:10px; border-radius:6px;">
                
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Airline</label>
                <input type="text" name="airline" placeholder="e.g. SriLankan Airlines" required style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:6px;">
                
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Aircraft Model</label>
                <input type="text" name="model" placeholder="e.g. Airbus A330-300" required style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:6px;">
                
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Registration Number</label>
                <input type="text" name="reg" placeholder="e.g. 4R-ALO" required style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:6px;">
                
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Location</label>
                <input type="text" name="location" placeholder="e.g. BIA (VCBI)" required style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:6px;">
                
                <button type="submit" class="btn-filled" style="width:100%;">Upload Photo</button>
            </form>
            <a href="profile.php" style="display:block; text-align:center; margin-top:15px; color:#666; text-decoration:none;">Cancel</a>
        </div>
    </div>
</body>
</html>