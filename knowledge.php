<?php
session_start();
require_once 'includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Aviation Knowledge</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Shared interaction styles for consistent UI */
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
        
        .knowledge-date { font-size: 0.75rem; color: #94a3b8; font-weight: 600; }
    </style>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h1 class="page-title">Aviation Knowledge Base</h1>
        
        <div class="grid-cards">
            <?php
            // Get current user ID for checking active likes
            $uid = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

            // Fetch knowledge articles + interaction counts + user's current choice
            $sql = "SELECT a.*, 
                    (SELECT COUNT(*) FROM article_interactions WHERE article_id = a.id AND type = 'like') as likes,
                    (SELECT COUNT(*) FROM article_interactions WHERE article_id = a.id AND type = 'dislike') as dislikes,
                    (SELECT type FROM article_interactions WHERE article_id = a.id AND user_id = $uid) as user_choice
                    FROM articles a 
                    WHERE a.category='knowledge' 
                    ORDER BY a.created_at DESC";
            
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0):
                while($row = $result->fetch_assoc()):
            ?>
                <div class="info-card" id="article-<?php echo $row['id']; ?>">
                    <a href="read_article.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="card-img-wrapper">
                            <img src="<?php echo !empty($row['image_url']) ? $row['image_url'] : 'assets/images/default-knowledge.jpg'; ?>" alt="Article">
                        </div>
                        <div class="card-body">
                            <div style="display:flex; justify-content:space-between; margin-bottom: 8px;">
                                <span style="color:var(--accent); font-size: 0.7rem; font-weight:bold; text-transform:uppercase;">Module</span>
                                <span class="knowledge-date"><?php echo date("M Y", strtotime($row['created_at'])); ?></span>
                            </div>
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
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
                <div style="grid-column: 1 / -1; padding: 40px; background: white; text-align: center; border-radius: 8px;">
                    <h3 style="color: #666;">No knowledge articles yet.</h3>
                    <p>Go to your Admin Dashboard to post one.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // AJAX Handler for Like/Dislike (Works with your existing like_article.php)
        function handleLike(articleId, type) {
            fetch(`like_article.php?id=${articleId}&type=${type}`)
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    const card = document.getElementById(`article-${articleId}`);
                    const likeBtn = card.querySelector('.like-btn');
                    const dislikeBtn = card.querySelector('.dislike-btn');

                    // Update numbers dynamically
                    likeBtn.querySelector('.l-count').innerText = data.likes;
                    dislikeBtn.querySelector('.d-count').innerText = data.dislikes;

                    // Toggle colors without page jump
                    if(type === 'like') {
                        likeBtn.classList.toggle('active-like');
                        dislikeBtn.classList.remove('active-dislike');
                    } else {
                        dislikeBtn.classList.toggle('active-dislike');
                        likeBtn.classList.remove('active-like');
                    }
                } else {
                    alert("Please login to interact with knowledge modules.");
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Initialize Lucide Icons
        lucide.createIcons();
    </script>
</body>
</html>