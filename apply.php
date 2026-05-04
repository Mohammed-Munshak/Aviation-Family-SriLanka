<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=Please login to apply for jobs."); exit;
}

$job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$res = $conn->query("SELECT * FROM vacancies WHERE id = $job_id");
$job = $res->fetch_assoc();

if (!$job) { die("Job not found."); }

$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['resume'])) {
    $target_dir = "assets/uploads/resumes/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    
    $file_ext = strtolower(pathinfo($_FILES["resume"]["name"], PATHINFO_EXTENSION));
    $allowed = array("pdf", "doc", "docx");
    
    if (in_array($file_ext, $allowed)) {
        $filename = "CV_" . $_SESSION['user_id'] . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO job_applications (vacancy_id, user_id, full_name, email, phone, experience_years, why_aviation, resume_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissssss", $job_id, $_SESSION['user_id'], $_POST['name'], $_POST['email'], $_POST['phone'], $_POST['exp'], $_POST['why'], $target_file);
            
            if ($stmt->execute()) {
                $msg = "success";
            } else { $msg = "Database error."; }
        } else { $msg = "Error uploading file."; }
    } else { $msg = "Only PDF, DOC, and DOCX files are allowed."; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Apply - <?php echo $job['job_title']; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .apply-container { max-width: 700px; margin: 40px auto; background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--primary); }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; }
        .job-banner { background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 30px; border-left: 4px solid var(--accent); }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="apply-container">
            <?php if($msg == "success"): ?>
                <div style="text-align: center; padding: 40px;">
                    <h2 style="color: #166534;">Application Sent! 🚀</h2>
                    <p>Thank you for applying for the <strong><?php echo $job['job_title']; ?></strong> position. Our team will review your profile and contact you soon.</p>
                    <a href="vacancies.php" class="btn-nav btn-profile" style="display: inline-block; margin-top: 20px; text-decoration:none;">Back to Careers</a>
                </div>
            <?php else: ?>
                <h1 style="margin-bottom: 10px;">Application Form</h1>
                <div class="job-banner">
                    <strong>Position:</strong> <?php echo $job['job_title']; ?><br>
                    <strong>Company:</strong> <?php echo $job['company']; ?>
                </div>

                <?php if($msg): ?><p style="color: #ef4444; margin-bottom: 15px;"><?php echo $msg; ?></p><?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" required placeholder="Enter your full name">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" required placeholder="email@example.com">
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" required placeholder="+94 ...">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Years of Professional Experience</label>
                        <select name="exp" required>
                            <option value="Entry Level (0-1)">Entry Level (0-1 years)</option>
                            <option value="Junior (1-3)">Junior (1-3 years)</option>
                            <option value="Senior (3-5)">Senior (3-5 years)</option>
                            <option value="Expert (5+)">Expert (5+ years)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Why do you want to join the Aviation Industry?</label>
                        <textarea name="why" rows="4" required placeholder="Tell us about your passion for aviation and this role..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Upload Resume (PDF, DOC, DOCX only)</label>
                        <input type="file" name="resume" required style="border: none; padding: 0;">
                        <small style="color: #64748b;">A professional resume is mandatory for your application.</small>
                    </div>

                    <button type="submit" class="btn-nav btn-login" style="width: 100%; border: none; height: 50px; font-size: 1rem; cursor: pointer;">Submit Application</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>