<?php
session_start();
require_once 'includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Careers</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .job-card {
            background: white; border-radius: 12px; padding: 25px;
            margin-bottom: 20px; border-left: 5px solid var(--accent);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex; justify-content: space-between; align-items: center;
            transition: transform 0.2s;
        }
        .job-card:hover { transform: translateY(-3px); }
        .job-info h2 { font-size: 1.4rem; color: var(--primary); margin-bottom: 5px; }
        .job-info h4 { color: #666; font-weight: 500; margin-bottom: 10px; }
        @media (max-width: 768px) { .job-card { flex-direction: column; align-items: flex-start; gap: 15px; } }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h1 class="page-title">Career Opportunities</h1>
        <p style="margin-bottom: 30px; color: #64748b;">Explore opportunities in Administration, Graphic Design, HR, and more.</p>

        <?php
        $check = $conn->query("SHOW TABLES LIKE 'vacancies'");
        if($check->num_rows > 0) {
            $jobs = $conn->query("SELECT * FROM vacancies ORDER BY id DESC");
            if($jobs->num_rows > 0):
                while($job = $jobs->fetch_assoc()):
        ?>
            <div class="job-card">
                <div class="job-info">
                    <h2><?php echo htmlspecialchars($job['job_title']); ?></h2>
                    <h4><?php echo htmlspecialchars($job['company']); ?></h4>
                    <p style="font-size: 0.9rem; color: #555;"><?php echo htmlspecialchars(substr($job['description'], 0, 150)); ?>...</p>
                </div>
                <a href="apply.php?id=<?php echo $job['id']; ?>" class="btn-nav btn-profile" style="text-decoration:none; padding: 10px 25px;">Apply Now</a>
            </div>
        <?php endwhile; else: echo "<p>No open positions right now. Check back later!</p>"; endif; 
        } else { echo "<p>System update in progress.</p>"; } ?>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>