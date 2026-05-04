<?php
session_start();
require_once 'includes/db_connect.php';

// ── Fetch latest 3 news articles
$news_stmt = $conn->prepare("SELECT id, title, summary, image_url, created_at FROM articles WHERE category='news' ORDER BY created_at DESC LIMIT 3");
$news_stmt->execute();
$latest_news = $news_stmt->get_result();

// ── Fetch latest 3 approved spotting photos
$photos_stmt = $conn->prepare("SELECT image_path, aircraft_model, airline, reg_number FROM spotting_photos WHERE status='approved' ORDER BY uploaded_at DESC LIMIT 3");
$photos_stmt->execute();
$latest_photos = $photos_stmt->get_result();

// ── Fetch DB counts
$member_count  = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE role='user'")->fetch_assoc()['cnt'];
$photo_count   = $conn->query("SELECT COUNT(*) as cnt FROM spotting_photos WHERE status='approved'")->fetch_assoc()['cnt'];
$article_count = $conn->query("SELECT COUNT(*) as cnt FROM articles")->fetch_assoc()['cnt'];

// ── Fetch social media counts from settings table
$settings = [];
$res = $conn->query("SELECT `key`, `value` FROM settings");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $settings[$row['key']] = $row['value'];
    }
}
$fb_followers  = isset($settings['fb_followers'])  ? number_format((int)$settings['fb_followers'])  : '0';
$ig_followers  = isset($settings['ig_followers'])  ? number_format((int)$settings['ig_followers'])  : '0';
$yt_subs       = isset($settings['yt_subscribers'])? number_format((int)$settings['yt_subscribers']): '0';
$wa_members    = isset($settings['wa_members'])    ? number_format((int)$settings['wa_members'])    : '0';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aviation Family Sri Lanka</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;500;600;700&family=Barlow+Condensed:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
    /* ════════════════════════════════════════
       ROOT
    ════════════════════════════════════════ */
    :root {
        --navy:  #002147;
        --navy2: #001533;
        --gold:  #FFC107;
        --gold2: #e6a800;
        --white: #ffffff;
        --light: #f0f4f8;
        --text:  #1a2438;
        --muted: #64748b;
        --font-display: 'Bebas Neue', sans-serif;
        --font-ui:      'Barlow', sans-serif;
        --font-cond:    'Barlow Condensed', sans-serif;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: var(--font-ui);
        background: var(--light);
        color: var(--text);
        overflow-x: hidden;
    }

    /* ════════════════════════════════════════
       HERO SLIDESHOW
    ════════════════════════════════════════ */
    .hero {
        position: relative;
        height: 100vh;
        min-height: 600px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-slides { position: absolute; inset: 0; z-index: 0; }

    .hero-slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        transition: opacity 1.4s cubic-bezier(0.4,0,0.2,1);
        background-size: cover;
        background-position: center;
    }

    /* ── REPLACE with your own images in assets/images/ ── */
    .hero-slide:nth-child(1) {
        background-image:
            linear-gradient(160deg, rgba(0,21,51,0.72) 0%, rgba(0,33,71,0.55) 60%, rgba(0,0,0,0.3) 100%),
            url('assets/images/hero1.png');
    }
    .hero-slide:nth-child(2) {
        background-image:
            linear-gradient(160deg, rgba(0,21,51,0.72) 0%, rgba(0,33,71,0.55) 60%, rgba(0,0,0,0.3) 100%),
            url('assets/images/hero2.png');
    }
    .hero-slide:nth-child(3) {
        background-image:
            linear-gradient(160deg, rgba(0,21,51,0.72) 0%, rgba(0,33,71,0.55) 60%, rgba(0,0,0,0.3) 100%),
            url('assets/images/hero3.png');
    }

    .hero-slide.active { opacity: 1; }

    .hero::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 220px;
        background: linear-gradient(to top, var(--light), transparent);
        z-index: 2;
        pointer-events: none;
    }

    /* ── HERO CONTENT ── */
    .hero-content {
        position: relative;
        z-index: 3;
        text-align: center;
        padding: 0 24px;
        max-width: 900px;
    }

    .hero-eyebrow {
        font-family: var(--font-cond);
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: var(--gold);
        margin-bottom: 18px;
        opacity: 0;
        transform: translateY(20px);
        animation: fadeUp 0.8s 0.3s forwards;
    }

    .hero-title {
        font-family: var(--font-display);
        font-size: clamp(4rem, 10vw, 8.5rem);
        line-height: 0.92;
        color: var(--white);
        letter-spacing: 2px;
        opacity: 0;
        transform: translateY(30px);
        animation: fadeUp 0.9s 0.5s forwards;
    }

    .hero-title span { color: var(--gold); display: block; }

    .hero-sub {
        font-family: var(--font-ui);
        font-size: clamp(1rem, 2vw, 1.2rem);
        font-weight: 300;
        color: rgba(255,255,255,0.78);
        margin-top: 22px;
        line-height: 1.6;
        opacity: 0;
        transform: translateY(20px);
        animation: fadeUp 0.9s 0.75s forwards;
    }

    .hero-actions {
        margin-top: 38px;
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
        opacity: 0;
        transform: translateY(20px);
        animation: fadeUp 0.9s 0.95s forwards;
    }

    .btn-primary {
        background: var(--gold);
        color: var(--navy);
        padding: 14px 36px;
        border-radius: 4px;
        font-family: var(--font-cond);
        font-size: 15px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        text-decoration: none;
        transition: background 0.25s, transform 0.2s;
        display: inline-block;
    }
    .btn-primary:hover { background: #ffe066; transform: translateY(-2px); }

    .btn-ghost {
        border: 1.5px solid rgba(255,255,255,0.5);
        color: white;
        padding: 14px 36px;
        border-radius: 4px;
        font-family: var(--font-cond);
        font-size: 15px;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        text-decoration: none;
        transition: border-color 0.25s, background 0.25s, transform 0.2s;
        display: inline-block;
    }
    .btn-ghost:hover { border-color: white; background: rgba(255,255,255,0.08); transform: translateY(-2px); }

    /* ── DOTS ── */
    .hero-dots {
        position: absolute;
        bottom: 50px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 4;
        display: flex;
        gap: 10px;
    }

    .hero-dot {
        width: 28px; height: 3px;
        background: rgba(255,255,255,0.3);
        border-radius: 2px;
        cursor: pointer;
        transition: background 0.3s, width 0.3s;
        border: none;
    }
    .hero-dot.active { background: var(--gold); width: 48px; }

    /* ── ARROWS ── */
    .hero-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 4;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
        width: 48px; height: 48px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s, border-color 0.2s;
        backdrop-filter: blur(4px);
    }
    .hero-arrow:hover { background: rgba(255,193,7,0.25); border-color: var(--gold); }
    .hero-arrow.prev { left: 28px; }
    .hero-arrow.next { right: 28px; }

    /* ════════════════════════════════════════
       STATS BAR — two rows
       Row 1: Community stats (from DB)
       Row 2: Social media counts (from settings)
    ════════════════════════════════════════ */
    .stats-bar {
        background: var(--navy);
        position: relative;
        z-index: 5;
    }

    .stats-row {
        display: flex;
        justify-content: center;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .stats-row:last-child { border-bottom: none; }

    .stat-item {
        flex: 1;
        max-width: 220px;
        text-align: center;
        padding: 26px 20px;
        border-right: 1px solid rgba(255,255,255,0.07);
        position: relative;
    }
    .stat-item:last-child { border-right: none; }

    .stat-num {
        font-family: var(--font-display);
        font-size: 2.6rem;
        color: var(--gold);
        line-height: 1;
    }

    .stat-lbl {
        font-family: var(--font-cond);
        font-size: 10px;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: rgba(255,255,255,0.4);
        margin-top: 6px;
    }

    /* Social row — slightly smaller */
    .stats-row.social-row .stat-num {
        font-size: 2rem;
    }

    .stats-row.social-row .stat-item {
        padding: 20px 16px;
    }

    /* Social platform colour accents */
    .stat-fb   .stat-num { color: #1877F2; }
    .stat-ig   .stat-num { color: #e1306c; }
    .stat-yt   .stat-num { color: #FF0000; }
    .stat-wa   .stat-num { color: #25D366; }

    .stats-divider-label {
        text-align: center;
        font-family: var(--font-cond);
        font-size: 9px;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: rgba(255,255,255,0.2);
        padding: 8px 0 0;
        background: var(--navy);
    }

    /* ════════════════════════════════════════
       SECTION HELPERS
    ════════════════════════════════════════ */
    .section-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 24px;
    }

    .section-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 36px;
        padding-bottom: 16px;
        border-bottom: 1px solid rgba(0,33,71,0.1);
    }

    .section-label {
        font-family: var(--font-cond);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: var(--gold2);
        margin-bottom: 6px;
    }

    .section-title {
        font-family: var(--font-display);
        font-size: clamp(2rem, 4vw, 3rem);
        color: var(--navy);
        line-height: 1;
        letter-spacing: 1px;
    }

    .section-link {
        font-family: var(--font-cond);
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--navy);
        text-decoration: none;
        border-bottom: 2px solid var(--gold);
        padding-bottom: 2px;
        white-space: nowrap;
        transition: color 0.2s;
    }
    .section-link:hover { color: var(--gold2); }

    /* ════════════════════════════════════════
       EXPLORE CARDS
    ════════════════════════════════════════ */
    .explore-section { padding: 80px 0 60px; }

    .explore-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2px;
        border-radius: 12px;
        overflow: hidden;
    }

    .explore-card {
        position: relative;
        height: 420px;
        overflow: hidden;
        text-decoration: none;
        display: block;
        background: var(--navy);
    }

    .explore-card img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.4,0,0.2,1);
        filter: brightness(0.6);
    }
    .explore-card:hover img { transform: scale(1.06); filter: brightness(0.45); }

    .explore-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,21,51,0.95) 0%, rgba(0,21,51,0.3) 50%, transparent 100%);
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 32px 28px;
        transition: background 0.3s;
    }
    .explore-card:hover .explore-overlay {
        background: linear-gradient(to top, rgba(0,21,51,0.98) 0%, rgba(0,21,51,0.5) 60%, rgba(0,21,51,0.15) 100%);
    }

    .explore-tag {
        font-family: var(--font-cond);
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: var(--gold);
        margin-bottom: 10px;
    }

    .explore-name {
        font-family: var(--font-display);
        font-size: 2.2rem;
        color: white;
        line-height: 1;
        letter-spacing: 1px;
        margin-bottom: 10px;
    }

    .explore-desc {
        font-size: 0.88rem;
        font-weight: 300;
        color: rgba(255,255,255,0.7);
        line-height: 1.5;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease, opacity 0.4s;
        opacity: 0;
    }
    .explore-card:hover .explore-desc { max-height: 80px; opacity: 1; }

    .explore-arrow {
        margin-top: 14px;
        font-family: var(--font-cond);
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: var(--gold);
        display: flex;
        align-items: center;
        gap: 8px;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: max-height 0.4s ease, opacity 0.4s;
    }
    .explore-card:hover .explore-arrow { max-height: 40px; opacity: 1; }

    /* ════════════════════════════════════════
       LATEST NEWS
    ════════════════════════════════════════ */
    .news-section { padding: 70px 0; background: white; }

    .news-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 28px;
    }

    .news-card {
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid rgba(0,33,71,0.08);
        transition: box-shadow 0.25s, transform 0.25s;
        background: white;
    }
    .news-card:hover { box-shadow: 0 16px 40px rgba(0,33,71,0.12); transform: translateY(-4px); }

    .news-card-img { height: 190px; overflow: hidden; background: var(--navy); }
    .news-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
    .news-card:hover .news-card-img img { transform: scale(1.05); }

    .news-card-body { padding: 22px 22px 26px; flex: 1; display: flex; flex-direction: column; }

    .news-date {
        font-family: var(--font-cond);
        font-size: 10px;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 10px;
    }

    .news-card-title {
        font-family: var(--font-cond);
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--navy);
        line-height: 1.3;
        margin-bottom: 10px;
    }

    .news-card-summary { font-size: 0.88rem; color: var(--muted); line-height: 1.6; flex: 1; }

    .news-card-cta {
        margin-top: 16px;
        font-family: var(--font-cond);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--gold2);
    }

    .no-content {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        color: var(--muted);
        font-size: 0.95rem;
    }

    /* ════════════════════════════════════════
       GALLERY STRIP
    ════════════════════════════════════════ */
    .gallery-section { padding: 70px 0; background: var(--light); }

    .gallery-strip { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }

    .gallery-tile {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        aspect-ratio: 4/3;
        text-decoration: none;
    }

    .gallery-tile img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
        filter: brightness(0.85);
    }
    .gallery-tile:hover img { transform: scale(1.06); filter: brightness(0.65); }

    .gallery-tile-info {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        padding: 20px 16px 14px;
        background: linear-gradient(to top, rgba(0,21,51,0.9), transparent);
        transform: translateY(8px);
        opacity: 0;
        transition: opacity 0.3s, transform 0.3s;
    }
    .gallery-tile:hover .gallery-tile-info { opacity: 1; transform: translateY(0); }

    .gallery-airline { font-family: var(--font-cond); font-size: 14px; font-weight: 700; color: white; letter-spacing: 1px; }
    .gallery-reg     { font-family: var(--font-cond); font-size: 11px; color: var(--gold); letter-spacing: 2px; }

    /* ════════════════════════════════════════
       CTA BANNER
    ════════════════════════════════════════ */
    .cta-section {
        background: var(--navy);
        padding: 90px 24px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .cta-section::before {
        content: 'AFSL';
        position: absolute;
        font-family: var(--font-display);
        font-size: 22rem;
        color: rgba(255,255,255,0.02);
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
        white-space: nowrap;
        letter-spacing: 10px;
    }

    .cta-label {
        font-family: var(--font-cond);
        font-size: 11px;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: var(--gold);
        margin-bottom: 18px;
    }

    .cta-title {
        font-family: var(--font-display);
        font-size: clamp(2.5rem, 6vw, 5rem);
        color: white;
        line-height: 1;
        letter-spacing: 2px;
        margin-bottom: 20px;
    }

    .cta-sub {
        font-size: 1rem;
        font-weight: 300;
        color: rgba(255,255,255,0.6);
        max-width: 500px;
        margin: 0 auto 36px;
        line-height: 1.7;
    }

    /* ════════════════════════════════════════
       ANIMATIONS
    ════════════════════════════════════════ */
    @keyframes fadeUp {
        to { opacity: 1; transform: translateY(0); }
    }

    .reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.7s ease, transform 0.7s ease;
    }
    .reveal.visible { opacity: 1; transform: translateY(0); }

    /* ════════════════════════════════════════
       RESPONSIVE
    ════════════════════════════════════════ */
    @media (max-width: 900px) {
        .explore-grid      { grid-template-columns: 1fr; }
        .explore-card      { height: 280px; }
        .news-grid         { grid-template-columns: 1fr; }
        .gallery-strip     { grid-template-columns: 1fr 1fr; }
        .stats-row         { flex-wrap: wrap; }
        .stat-item         { border-right: none; border-bottom: 1px solid rgba(255,255,255,0.07); flex: 0 0 50%; max-width: none; }
    }

    @media (max-width: 600px) {
        .gallery-strip     { grid-template-columns: 1fr; }
        .hero-arrow        { display: none; }
        .stat-item         { flex: 0 0 100%; }
    }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<!-- ════════════════════════════════════════
     HERO SLIDESHOW
════════════════════════════════════════ -->
<section class="hero">
    <div class="hero-slides">
        <div class="hero-slide active"></div>
        <div class="hero-slide"></div>
        <div class="hero-slide"></div>
    </div>

    <div class="hero-content">
        <p class="hero-eyebrow">Aviation Family &nbsp;·&nbsp; Sri Lanka</p>
        <h1 class="hero-title">
            We Live<br>
            <span>To Fly</span>
        </h1>
        <p class="hero-sub">
            Connecting Sri Lanka's aviation enthusiasts, professionals,<br>
            and the next generation of aviators.
        </p>
        <div class="hero-actions">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="signup.php" class="btn-primary">Join the Family</a>
                <a href="about.php"  class="btn-ghost">About Us</a>
            <?php else: ?>
                <a href="spotting.php" class="btn-primary">View Gallery</a>
                <a href="news.php"     class="btn-ghost">Latest News</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="hero-dots">
        <button class="hero-dot active" onclick="goToSlide(0)"></button>
        <button class="hero-dot"        onclick="goToSlide(1)"></button>
        <button class="hero-dot"        onclick="goToSlide(2)"></button>
    </div>

    <button class="hero-arrow prev" onclick="prevSlide()">&#8592;</button>
    <button class="hero-arrow next" onclick="nextSlide()">&#8594;</button>
</section>

<!-- ════════════════════════════════════════
     STATS BAR
     Row 1 — Community (from DB)
     Row 2 — Social Media (from settings table)
════════════════════════════════════════ -->
<div class="stats-bar">

    <!-- Row 1: Community stats -->
    <div class="stats-row">
        <div class="stat-item">
            <div class="stat-num"><?php echo $member_count; ?>+</div>
            <div class="stat-lbl">Members</div>
        </div>
        <div class="stat-item">
            <div class="stat-num"><?php echo $photo_count; ?>+</div>
            <div class="stat-lbl">Photos Logged</div>
        </div>
        <div class="stat-item">
            <div class="stat-num"><?php echo $article_count; ?>+</div>
            <div class="stat-lbl">Articles</div>
        </div>
    </div>

    <!-- Divider label -->
    <div class="stats-divider-label">Follow Us</div>

    <!-- Row 2: Social media followers -->
    <div class="stats-row social-row">
        <div class="stat-item stat-fb">
            <div class="stat-num"><?php echo $fb_followers; ?></div>
            <div class="stat-lbl">Facebook</div>
        </div>
        <div class="stat-item stat-ig">
            <div class="stat-num"><?php echo $ig_followers; ?></div>
            <div class="stat-lbl">Instagram</div>
        </div>
        <div class="stat-item stat-yt">
            <div class="stat-num"><?php echo $yt_subs; ?></div>
            <div class="stat-lbl">YouTube</div>
        </div>
        <div class="stat-item stat-wa">
            <div class="stat-num"><?php echo $wa_members; ?></div>
            <div class="stat-lbl">WhatsApp</div>
        </div>
    </div>

</div>

<!-- ════════════════════════════════════════
     EXPLORE SECTION
════════════════════════════════════════ -->
<section class="explore-section">
    <div class="section-wrap">
        <div class="section-header reveal">
            <div>
                <p class="section-label">Discover</p>
                <h2 class="section-title">Explore the Community</h2>
            </div>
            <a href="about.php" class="section-link">About Us &rarr;</a>
        </div>

        <div class="explore-grid reveal">

            <a href="news.php" class="explore-card">
                <img src="assets/images/explore-news.png" alt="Aviation News"
                     onerror="this.style.display='none'">
                <div class="explore-overlay">
                    <span class="explore-tag">Stay Informed</span>
                    <h3 class="explore-name">Aviation News</h3>
                    <p class="explore-desc">Latest updates from Sri Lankan and global aviation.</p>
                    <span class="explore-arrow">Read Articles &nbsp;&#8594;</span>
                </div>
            </a>

            <a href="spotting.php" class="explore-card">
                <img src="assets/images/explore-spotting.png" alt="Spotting Gallery"
                     onerror="this.style.display='none'">
                <div class="explore-overlay">
                    <span class="explore-tag">Captured Moments</span>
                    <h3 class="explore-name">Photo Gallery</h3>
                    <p class="explore-desc">Best shots of your Plane Spotting — submit yours and get featured.</p>
                    <span class="explore-arrow">View Gallery &nbsp;&#8594;</span>
                </div>
            </a>

            <a href="vacancies.php" class="explore-card">
                <img src="assets/images/explore-careers.png" alt="Careers"
                     onerror="this.style.display='none'">
                <div class="explore-overlay">
                    <span class="explore-tag">Opportunities</span>
                    <h3 class="explore-name">Careers</h3>
                    <p class="explore-desc">Browse aviation job listings and take the next step in your career in the skies.</p>
                    <span class="explore-arrow">View Vacancies &nbsp;&#8594;</span>
                </div>
            </a>

        </div>
    </div>
</section>

<!-- ════════════════════════════════════════
     LATEST NEWS
════════════════════════════════════════ -->
<section class="news-section">
    <div class="section-wrap">
        <div class="section-header reveal">
            <div>
                <p class="section-label">What's Happening</p>
                <h2 class="section-title">Latest News</h2>
            </div>
            <a href="news.php" class="section-link">All Articles &rarr;</a>
        </div>

        <div class="news-grid">
            <?php if ($latest_news->num_rows > 0):
                while ($article = $latest_news->fetch_assoc()): ?>
                <a href="article.php?id=<?php echo $article['id']; ?>" class="news-card reveal">
                    <div class="news-card-img">
                        <?php if (!empty($article['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($article['image_url']); ?>"
                                 alt="<?php echo htmlspecialchars($article['title']); ?>">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,#002147,#004e89);"></div>
                        <?php endif; ?>
                    </div>
                    <div class="news-card-body">
                        <span class="news-date"><?php echo date('d M Y', strtotime($article['created_at'])); ?></span>
                        <h3 class="news-card-title"><?php echo htmlspecialchars($article['title']); ?></h3>
                        <p class="news-card-summary"><?php echo htmlspecialchars($article['summary'] ?? ''); ?></p>
                        <span class="news-card-cta">Read More &rarr;</span>
                    </div>
                </a>
            <?php endwhile;
            else: ?>
                <div class="no-content">No articles published yet. Check back soon.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════
     SPOTTING GALLERY STRIP
════════════════════════════════════════ -->
<?php if ($latest_photos->num_rows > 0): ?>
<section class="gallery-section">
    <div class="section-wrap">
        <div class="section-header reveal">
            <div>
                <p class="section-label">From Our Spotters</p>
                <h2 class="section-title">Recent Shots</h2>
            </div>
            <a href="spotting.php" class="section-link">Full Gallery &rarr;</a>
        </div>

        <div class="gallery-strip">
            <?php while ($photo = $latest_photos->fetch_assoc()): ?>
            <a href="spotting.php" class="gallery-tile reveal">
                <img src="<?php echo htmlspecialchars($photo['image_path']); ?>"
                     alt="<?php echo htmlspecialchars($photo['airline']); ?>">
                <div class="gallery-tile-info">
                    <div class="gallery-airline"><?php echo htmlspecialchars($photo['airline']); ?></div>
                    <div class="gallery-reg">
                        <?php echo htmlspecialchars($photo['reg_number']); ?>
                        &nbsp;·&nbsp;
                        <?php echo htmlspecialchars($photo['aircraft_model']); ?>
                    </div>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ════════════════════════════════════════
     CTA BANNER
════════════════════════════════════════ -->
<section class="cta-section">
    <div style="position:relative;z-index:2;">
        <p class="cta-label">Ready to Join?</p>
        <h2 class="cta-title">Together, We Fly Higher</h2>
        <p class="cta-sub">Become part of Sri Lanka's most passionate aviation community. No experience required — just a love for flight.</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="signup.php" class="btn-primary">Create Free Account</a>
        <?php else: ?>
            <a href="store.php" class="btn-primary">Visit the Store</a>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
/* ── HERO SLIDESHOW ── */
let current  = 0;
const slides = document.querySelectorAll('.hero-slide');
const dots   = document.querySelectorAll('.hero-dot');
let timer    = null;

function goToSlide(n) {
    slides[current].classList.remove('active');
    dots[current].classList.remove('active');
    current = (n + slides.length) % slides.length;
    slides[current].classList.add('active');
    dots[current].classList.add('active');
    resetTimer();
}

function nextSlide() { goToSlide(current + 1); }
function prevSlide() { goToSlide(current - 1); }

function resetTimer() {
    clearInterval(timer);
    timer = setInterval(nextSlide, 6000);
}

resetTimer();

/* ── SCROLL REVEAL ── */
const revealEls = document.querySelectorAll('.reveal');
const io = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
        if (e.isIntersecting) {
            setTimeout(() => e.target.classList.add('visible'), i * 80);
            io.unobserve(e.target);
        }
    });
}, { threshold: 0.12 });

revealEls.forEach(el => io.observe(el));
</script>

</body>
</html>