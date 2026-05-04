<?php
session_start();
require_once 'includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aviation News — Aviation Family SL</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;500;600;700&family=Barlow+Condensed:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --navy:  #002147;
        --navy2: #001533;
        --gold:  #FFC107;
        --gold2: #e6a800;
        --light: #f0f4f8;
        --white: #ffffff;
        --text:  #1a2438;
        --muted: #64748b;
        --border:#e2e8f0;
        --font-display: 'Bebas Neue', sans-serif;
        --font-ui:      'Barlow', sans-serif;
        --font-cond:    'Barlow Condensed', sans-serif;
    }

    body { font-family: var(--font-ui); background: var(--light); color: var(--text); }

    /* ── PAGE HEADER ── */
    .news-hero {
        background: var(--navy);
        padding: 60px 24px 50px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .news-hero::before {
        content: 'NEWS';
        position: absolute;
        font-family: var(--font-display);
        font-size: 18rem;
        color: rgba(255,255,255,0.03);
        top: 50%; left: 50%;
        transform: translate(-50%,-50%);
        pointer-events: none;
        white-space: nowrap;
        letter-spacing: 8px;
    }
    .news-hero-label {
        font-family: var(--font-cond);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: var(--gold);
        margin-bottom: 12px;
    }
    .news-hero-title {
        font-family: var(--font-display);
        font-size: clamp(3rem, 8vw, 6rem);
        color: white;
        line-height: 1;
        letter-spacing: 2px;
    }

    /* ── LAYOUT ── */
    .news-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 50px 24px;
    }

    /* ── FEATURED (first article big) ── */
    .featured-card {
        display: grid;
        grid-template-columns: 1fr 420px;
        border-radius: 16px;
        overflow: hidden;
        background: var(--white);
        box-shadow: 0 4px 24px rgba(0,33,71,0.10);
        margin-bottom: 40px;
        text-decoration: none;
        color: inherit;
        transition: box-shadow 0.25s, transform 0.25s;
        position: relative;
    }
    .featured-card:hover { box-shadow: 0 12px 40px rgba(0,33,71,0.16); transform: translateY(-3px); }

    .featured-img {
        position: relative;
        overflow: hidden;
        min-height: 420px;
    }
    .featured-img img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }
    .featured-card:hover .featured-img img { transform: scale(1.04); }

    .featured-badge {
        position: absolute;
        top: 20px; left: 20px;
        background: var(--gold);
        color: var(--navy);
        font-family: var(--font-cond);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 3px;
        text-transform: uppercase;
        padding: 5px 12px;
        border-radius: 3px;
    }

    .featured-body {
        padding: 36px 32px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .featured-date {
        font-family: var(--font-cond);
        font-size: 11px;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 14px;
    }

    .featured-title {
        font-family: var(--font-display);
        font-size: 2.2rem;
        color: var(--navy);
        line-height: 1.1;
        letter-spacing: 1px;
        margin-bottom: 16px;
    }

    .featured-summary {
        font-size: 0.95rem;
        color: var(--muted);
        line-height: 1.7;
        flex: 1;
    }

    .featured-cta {
        margin-top: 24px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: var(--font-cond);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--navy);
        border-bottom: 2px solid var(--gold);
        padding-bottom: 2px;
        width: fit-content;
    }

    /* ── GRID SECTION ── */
    .grid-label {
        font-family: var(--font-cond);
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border);
    }

    .news-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }

    /* ── NEWS CARD ── */
    .news-card {
        background: var(--white);
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        transition: box-shadow 0.22s, transform 0.22s;
        position: relative;
    }
    .news-card:hover { box-shadow: 0 10px 32px rgba(0,33,71,0.12); transform: translateY(-4px); }

    .news-card-img {
        position: relative;
        height: 200px;
        overflow: hidden;
        background: var(--navy);
    }
    .news-card-img img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }
    .news-card:hover .news-card-img img { transform: scale(1.06); }

    .news-card-body {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .nc-date {
        font-family: var(--font-cond);
        font-size: 10px;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 8px;
    }

    .nc-title {
        font-family: var(--font-cond);
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--navy);
        line-height: 1.3;
        margin-bottom: 10px;
        text-decoration: none;
    }

    .nc-summary {
        font-size: 0.85rem;
        color: var(--muted);
        line-height: 1.6;
        flex: 1;
    }

    .nc-read {
        margin-top: 14px;
        font-family: var(--font-cond);
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--gold2);
        text-decoration: none;
        display: inline-block;
    }

    /* ── REACTION BAR (Facebook style) ── */
    .reaction-bar {
        padding: 10px 20px 14px;
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .react-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        border: 1.5px solid var(--border);
        background: var(--white);
        cursor: pointer;
        font-family: var(--font-ui);
        font-size: 13px;
        font-weight: 600;
        color: var(--muted);
        transition: all 0.18s ease;
        user-select: none;
    }

    .react-btn:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }

    /* Like active */
    .react-btn.liked {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #1d4ed8;
    }
    .react-btn.liked .react-emoji { filter: none; }

    /* Dislike active */
    .react-btn.disliked {
        background: #fef2f2;
        border-color: #ef4444;
        color: #dc2626;
    }

    .react-emoji { font-size: 16px; line-height: 1; }
    .react-count { font-size: 13px; font-weight: 700; min-width: 14px; text-align: center; }

    /* Pop animation */
    @keyframes pop {
        0%   { transform: scale(1); }
        40%  { transform: scale(1.28); }
        100% { transform: scale(1); }
    }
    .pop { animation: pop 0.3s ease forwards; }

    /* Featured reaction bar */
    .featured-reactions {
        margin-top: 20px;
        display: flex;
        gap: 8px;
    }

    /* ── EMPTY STATE ── */
    .no-news {
        grid-column: 1/-1;
        text-align: center;
        padding: 80px 20px;
        color: var(--muted);
    }
    .no-news p { font-size: 1rem; margin-top: 12px; }

    /* ── REVEAL ── */
    .reveal { opacity: 0; transform: translateY(24px); transition: opacity 0.6s ease, transform 0.6s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }

    /* ── RESPONSIVE ── */
    @media (max-width: 900px) {
        .featured-card { grid-template-columns: 1fr; }
        .featured-img  { min-height: 260px; }
        .news-grid     { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 600px) {
        .news-grid { grid-template-columns: 1fr; }
    }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<!-- PAGE HERO -->
<div class="news-hero">
    <div style="position:relative;z-index:2;">
        <p class="news-hero-label">Aviation Family SL</p>
        <h1 class="news-hero-title">Aviation News</h1>
    </div>
</div>

<div class="news-wrap">
    <?php
    $uid = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    $sql = "SELECT a.*,
            (SELECT COUNT(*) FROM article_interactions WHERE article_id=a.id AND type='like') as likes,
            (SELECT COUNT(*) FROM article_interactions WHERE article_id=a.id AND type='dislike') as dislikes,
            (SELECT type FROM article_interactions WHERE article_id=a.id AND user_id=$uid) as user_choice
            FROM articles a WHERE a.category='news' ORDER BY a.created_at DESC";
    $result = $conn->query($sql);
    $articles = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) $articles[] = $row;
    }
    ?>

    <?php if (empty($articles)): ?>
        <div class="no-news reveal">
            <p>No news articles published yet. Check back soon.</p>
        </div>
    <?php else: ?>

        <?php
        $featured = $articles[0];
        $rest     = array_slice($articles, 1);
        ?>

        <!-- FEATURED ARTICLE -->
        <div class="reveal">
            <div class="featured-card" id="article-<?php echo $featured['id']; ?>">
                <div class="featured-img">
                    <?php if (!empty($featured['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($featured['image_url']); ?>" alt="<?php echo htmlspecialchars($featured['title']); ?>">
                    <?php else: ?>
                        <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--navy),#004e89);"></div>
                    <?php endif; ?>
                    <span class="featured-badge">Featured</span>
                </div>
                <div class="featured-body">
                    <div>
                        <p class="featured-date"><?php echo date('d M Y', strtotime($featured['created_at'])); ?></p>
                        <h2 class="featured-title"><?php echo htmlspecialchars($featured['title']); ?></h2>
                        <p class="featured-summary"><?php echo htmlspecialchars($featured['summary'] ?? ''); ?></p>
                        <a href="read_article.php?id=<?php echo $featured['id']; ?>" class="featured-cta">Read Full Story &rarr;</a>
                    </div>
                    <div class="featured-reactions">
                        <button onclick="handleReact(<?php echo $featured['id']; ?>,'like')"
                            class="react-btn <?php echo $featured['user_choice']==='like'?'liked':''; ?>"
                            id="like-<?php echo $featured['id']; ?>">
                            <span class="react-emoji">👍</span>
                            <span class="react-count l-count"><?php echo $featured['likes']; ?></span>
                            <span style="font-size:12px;">Like</span>
                        </button>
                        <button onclick="handleReact(<?php echo $featured['id']; ?>,'dislike')"
                            class="react-btn <?php echo $featured['user_choice']==='dislike'?'disliked':''; ?>"
                            id="dislike-<?php echo $featured['id']; ?>">
                            <span class="react-emoji">👎</span>
                            <span class="react-count d-count"><?php echo $featured['dislikes']; ?></span>
                            <span style="font-size:12px;">Dislike</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($rest)): ?>
        <!-- MORE ARTICLES GRID -->
        <p class="grid-label reveal">More Stories</p>
        <div class="news-grid">
            <?php foreach ($rest as $i => $row): ?>
            <div class="news-card reveal" id="article-<?php echo $row['id']; ?>" style="transition-delay:<?php echo ($i % 3) * 80; ?>ms;">
                <div class="news-card-img">
                    <?php if (!empty($row['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                    <?php else: ?>
                        <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--navy),#004e89);"></div>
                    <?php endif; ?>
                </div>
                <div class="news-card-body">
                    <p class="nc-date"><?php echo date('d M Y', strtotime($row['created_at'])); ?></p>
                    <h3 class="nc-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p class="nc-summary"><?php echo htmlspecialchars(substr($row['summary'] ?? '', 0, 110)); ?>...</p>
                    <a href="read_article.php?id=<?php echo $row['id']; ?>" class="nc-read">Read More &rarr;</a>
                </div>
                <div class="reaction-bar">
                    <button onclick="handleReact(<?php echo $row['id']; ?>,'like')"
                        class="react-btn <?php echo $row['user_choice']==='like'?'liked':''; ?>"
                        id="like-<?php echo $row['id']; ?>">
                        <span class="react-emoji">👍</span>
                        <span class="react-count l-count"><?php echo $row['likes']; ?></span>
                        <span style="font-size:11px;">Like</span>
                    </button>
                    <button onclick="handleReact(<?php echo $row['id']; ?>,'dislike')"
                        class="react-btn <?php echo $row['user_choice']==='dislike'?'disliked':''; ?>"
                        id="dislike-<?php echo $row['id']; ?>">
                        <span class="react-emoji">👎</span>
                        <span class="react-count d-count"><?php echo $row['dislikes']; ?></span>
                        <span style="font-size:11px;">Dislike</span>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
lucide.createIcons();

function handleReact(articleId, type) {
    fetch(`like_article.php?id=${articleId}&type=${type}`)
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            const likeBtn    = document.getElementById(`like-${articleId}`);
            const dislikeBtn = document.getElementById(`dislike-${articleId}`);

            likeBtn.querySelector('.l-count').innerText    = data.likes;
            dislikeBtn.querySelector('.d-count').innerText = data.dislikes;

            if (type === 'like') {
                const wasLiked = likeBtn.classList.contains('liked');
                likeBtn.classList.toggle('liked');
                dislikeBtn.classList.remove('disliked');
                if (!wasLiked) popAnimate(likeBtn);
            } else {
                const wasDisliked = dislikeBtn.classList.contains('disliked');
                dislikeBtn.classList.toggle('disliked');
                likeBtn.classList.remove('liked');
                if (!wasDisliked) popAnimate(dislikeBtn);
            }
        } else {
            showLoginPrompt();
        }
    });
}

function popAnimate(el) {
    el.classList.remove('pop');
    void el.offsetWidth;
    el.classList.add('pop');
    setTimeout(() => el.classList.remove('pop'), 350);
}

function showLoginPrompt() {
    const toast = document.createElement('div');
    toast.textContent = 'Please login to react to articles.';
    toast.style.cssText = `
        position:fixed;bottom:28px;left:50%;transform:translateX(-50%);
        background:#002147;color:white;padding:12px 24px;border-radius:8px;
        font-size:13.5px;font-weight:600;z-index:9999;
        box-shadow:0 8px 24px rgba(0,0,0,0.2);
        animation:fadeUp .3s ease;
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

/* Scroll reveal */
const io = new IntersectionObserver(entries => {
    entries.forEach((e, i) => {
        if (e.isIntersecting) { setTimeout(() => e.target.classList.add('visible'), i * 60); io.unobserve(e.target); }
    });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => io.observe(el));
</script>

<style>
@keyframes fadeUp { from { opacity:0; transform:translate(-50%,10px); } to { opacity:1; transform:translate(-50%,0); } }
</style>

</body>
</html>