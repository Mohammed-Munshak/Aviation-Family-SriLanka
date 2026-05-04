<?php
session_start();
require_once 'includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Aviation News</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .interaction-bar {
            display: flex; gap: 15px; margin-top: 15px;
            padding-top: 10px; border-top: 1px solid #eee;
        }
        .btn-interact {
            display: flex; align-items: center; gap: 5px;
            text-decoration: none; font-size: 0.85rem; color: #64748b;
            cursor: pointer; background: none; border: none; padding: 5px;
            transition: 0.2s;
        }
        .btn-interact:hover { color: var(--primary); }
        .btn-interact.active-like { color: #22c55e; font-weight: 800; }
        .btn-interact.active-dislike { color: #ef4444; font-weight: 800; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h1 class="page-title">Latest Aviation News</h1>
        
        <div class="grid-cards">
            <?php
            $uid = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
            $sql = "SELECT a.*, 
                    (SELECT COUNT(*) FROM article_interactions WHERE article_id = a.id AND type = 'like') as likes,
                    (SELECT COUNT(*) FROM article_interactions WHERE article_id = a.id AND type = 'dislike') as dislikes,
                    (SELECT type FROM article_interactions WHERE article_id = a.id AND user_id = $uid) as user_choice
                    FROM articles a WHERE a.category='news' ORDER BY a.created_at DESC";
            
            $result = $conn->query($sql);
            if ($result->num_rows > 0):
                while($row = $result->fetch_assoc()):
            ?>
                <div class="info-card" id="article-<?php echo $row['id']; ?>">
                    <a href="read_article.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="card-img-wrapper" style="height: 200px;">
                            <img src="<?php echo $row['image_url']; ?>" alt="News">
                        </div>
                        <div class="card-body">
                            <small style="color: var(--secondary); font-weight: bold;"><?php echo date("M d, Y", strtotime($row['created_at'])); ?></small>
                            <h3 style="margin-top: 5px;"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p><?php echo substr(htmlspecialchars($row['summary']), 0, 100); ?>...</p>
                        </div>
                    </a>
                    
                    <div class="card-body" style="padding-top: 0;">
                        <div class="interaction-bar">
                            <button onclick="handleLike(<?php echo $row['id']; ?>, 'like')" 
                               class="btn-interact like-btn <?php echo ($row['user_choice'] == 'like') ? 'active-like' : ''; ?>">
                                <i data-lucide="thumbs-up" style="width:16px;"></i> <span class="l-count"><?php echo $row['likes']; ?></span>
                            </button>

                            <button onclick="handleLike(<?php echo $row['id']; ?>, 'dislike')" 
                               class="btn-interact dislike-btn <?php echo ($row['user_choice'] == 'dislike') ? 'active-dislike' : ''; ?>">
                                <i data-lucide="thumbs-down" style="width:16px;"></i> <span class="d-count"><?php echo $row['dislikes']; ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; else: ?>
                <p>No news articles posted yet.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>

    <script>
        // THE AJAX MAGIC: Updates without moving the page
        function handleLike(articleId, type) {
            fetch(`like_article.php?id=${articleId}&type=${type}`)
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    const card = document.getElementById(`article-${articleId}`);
                    const likeBtn = card.querySelector('.like-btn');
                    const dislikeBtn = card.querySelector('.dislike-btn');

                    // Update numbers
                    likeBtn.querySelector('.l-count').innerText = data.likes;
                    dislikeBtn.querySelector('.d-count').innerText = data.dislikes;

                    // Toggle colors
                    if(type === 'like') {
                        likeBtn.classList.toggle('active-like');
                        dislikeBtn.classList.remove('active-dislike');
                    } else {
                        dislikeBtn.classList.toggle('active-dislike');
                        likeBtn.classList.remove('active-like');
                    }
                } else {
                    alert("Please login to like or dislike articles.");
                }
            });
        }
        lucide.createIcons();
    </script>
</body>
</html>