<?php
session_start();
require_once 'includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Aviation Photography</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 class="page-title" style="margin: 0;">Spotting Gallery</h1>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="upload_photo.php" class="btn-filled">Upload New Photo</a>
            <?php endif; ?>
        </div>

        <div class="grid-cards">
            <?php
            // CRITICAL FIX: Only select APPROVED photos
            $sql = "SELECT p.*, u.username FROM spotting_photos p 
                    JOIN users u ON p.user_id = u.id 
                    WHERE p.status = 'approved' 
                    ORDER BY p.uploaded_at DESC";
            
            $result = $conn->query($sql);

            if ($result->num_rows > 0):
                while($row = $result->fetch_assoc()):
            ?>
                <div class="info-card">
                    <div class="card-img-wrapper" style="height: 250px;">
                        <img src="<?php echo $row['image_path']; ?>" loading="lazy">
                    </div>
                    <div class="card-body">
                        <small style="color: var(--accent); font-weight: bold;"><?php echo $row['reg_number']; ?></small>
                        <h3 style="margin: 5px 0;"><?php echo $row['airline']; ?></h3>
                        <p style="margin-bottom: 10px;"><?php echo $row['aircraft_model']; ?> @ <?php echo $row['location']; ?></p>
                        
                        <div style="margin-top: auto; border-top: 1px solid #eee; padding-top: 10px;">
                            <span style="font-size: 0.9rem; color: #666;">
                                Captured by <a href="profile.php?user_id=<?php echo $row['user_id']; ?>" style="color: var(--primary); font-weight: bold; text-decoration: none;"><?php echo $row['username']; ?></a>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endwhile; else: ?>
                <p>No approved photos yet. Be the first to upload!</p>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>