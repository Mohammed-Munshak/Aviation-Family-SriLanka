<!DOCTYPE html>
<html lang="en">
<head>
    <title>Social - Aviation Family</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .social-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px; }
        .social-card { background: white; padding: 30px; border-radius: 12px; text-align: center; text-decoration: none; color: #333; transition: 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .social-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .social-icon { width: 50px; height: 50px; margin-bottom: 15px; color: var(--primary-dark); }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="text-align: center;">Join our Community</h1>
        <p style="text-align: center; color: #666;">Follow us for daily aviation updates.</p>

        <div class="social-grid">
            <a href="#" class="social-card">
                <i data-lucide="facebook" class="social-icon"></i>
                <h3>Facebook</h3>
                <p>Join the discussion group</p>
            </a>
            <a href="#" class="social-card">
                <i data-lucide="instagram" class="social-icon"></i>
                <h3>Instagram</h3>
                <p>Daily plane spotting highlights</p>
            </a>
            <a href="#" class="social-card">
                <i data-lucide="youtube" class="social-icon"></i>
                <h3>YouTube</h3>
                <p>Watch our event vlogs</p>
            </a>
            <a href="#" class="social-card">
                <i data-lucide="twitter" class="social-icon"></i>
                <h3>Twitter / X</h3>
                <p>Live aviation news</p>
            </a>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="index.php" class="btn-primary">Back to Home</a>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>