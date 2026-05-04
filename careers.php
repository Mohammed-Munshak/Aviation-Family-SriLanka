<div class="job-list">
    <?php
    $jobs = $conn->query("SELECT * FROM vacancies WHERE closing_date >= CURDATE() ORDER BY posted_at DESC");
    while($job = $jobs->fetch_assoc()):
    ?>
    <div class="job-row" style="border-left: 4px solid var(--accent); background: white; padding: 20px; margin-bottom: 15px; border-radius: 4px;">
        <div class="job-header" style="display: flex; justify-content: space-between;">
            <h2><?php echo $job['job_title']; ?></h2>
            <span class="company-name" style="font-weight: bold; color: #64748b;"><?php echo $job['company']; ?></span>
        </div>
        <p><?php echo substr($job['description'], 0, 150); ?>...</p>
        <div class="job-footer" style="margin-top: 10px;">
            <small>Closing: <?php echo $job['closing_date']; ?></small>
            <a href="<?php echo $job['apply_link']; ?>" class="btn-apply" style="float: right; text-decoration: none; color: var(--accent); font-weight: bold;">Apply Now →</a>
        </div>
    </div>
    <?php endwhile; ?>
</div>