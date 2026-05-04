<?php
session_start();
require_once 'includes/db_connect.php';

// 1. Check if an ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php"); 
    exit;
}

$id = intval($_GET['id']);
$uid = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// 2. Fetch the specific article + Counts + User choice
$query = "SELECT a.*, 
          (SELECT COUNT(*) FROM article_interactions WHERE article_id = a.id AND type = 'like') as likes,
          (SELECT COUNT(*) FROM article_interactions WHERE article_id = a.id AND type = 'dislike') as dislikes,
          (SELECT type FROM article_interactions WHERE article_id = a.id AND user_id = $uid) as user_choice
          FROM articles a WHERE a.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();

// 3. If article doesn't exist, redirect or show error
if (!$article) {
    die("Article not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($article['title']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .article-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .article-hero {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .meta-data {
            color: var(--secondary);
            font-weight: bold;
            font-size: 0.9rem;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .article-body {
            line-height: 1.8;
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 40px;
        }
        
        /* Interaction Section */
        .article-footer {
            border-top: 2px solid #f1f5f9;
            padding-top: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .btn-interact {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 20px;
            border-radius: 50px;
            cursor: pointer;
            transition: 0.3s;
            color: #64748b;
            font-weight: 600;
        }
        .btn-interact:hover { background: #f1f5f9; }
        .active-like { background: #dcfce7; color: #166534; border-color: #22c55e; }
        .active-dislike { background: #fee2e2; color: #991b1b; border-color: #ef4444; }
    </style>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="container">
        <a href="javascript:history.back()" class="btn-outline" style="color: var(--primary); border-color: var(--primary); display:inline-block; margin: 20px 0;">&larr; Back to Feed</a>

        <article class="article-container" id="article-root">
            <div class="meta-data">
                <?php echo strtoupper($article['category']); ?> • <?php echo date("F d, Y", strtotime($article['created_at'])); ?>
            </div>

            <h1 style="font-size: 2.5rem; color: var(--primary); margin-bottom: 20px; line-height: 1.2;">
                <?php echo htmlspecialchars($article['title']); ?>
            </h1>

            <?php if (!empty($article['image_url'])): ?>
                <img src="<?php echo $article['image_url']; ?>" class="article-hero" alt="Article Image">
            <?php endif; ?>

            <div class="article-body">
                <?php echo nl2br(htmlspecialchars($article['content'])); ?>
            </div>

            <div class="article-footer">
                <div style="display:flex; gap:15px;">
                    <button onclick="handleLike(<?php echo $article['id']; ?>, 'like')" 
                            id="like-btn"
                            class="btn-interact <?php echo ($article['user_choice'] == 'like') ? 'active-like' : ''; ?>">
                        <i data-lucide="thumbs-up" style="width:20px;"></i> 
                        <span id="l-count"><?php echo $article['likes']; ?></span>
                    </button>

                    <button onclick="handleLike(<?php echo $article['id']; ?>, 'dislike')" 
                            id="dislike-btn"
                            class="btn-interact <?php echo ($article['user_choice'] == 'dislike') ? 'active-dislike' : ''; ?>">
                        <i data-lucide="thumbs-down" style="width:20px;"></i> 
                        <span id="d-count"><?php echo $article['dislikes']; ?></span>
                    </button>
                </div>
                
                <div style="color: #94a3b8; font-size: 0.9rem;">
                    Was this helpful?
                </div>
            </div>
        </article>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function handleLike(articleId, type) {
            fetch(`like_article.php?id=${articleId}&type=${type}`)
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    const likeBtn = document.getElementById('like-btn');
                    const dislikeBtn = document.getElementById('dislike-btn');

                    // Update values
                    document.getElementById('l-count').innerText = data.likes;
                    document.getElementById('d-count').innerText = data.dislikes;

                    // Update UI Colors
                    if(type === 'like') {
                        likeBtn.classList.toggle('active-like');
                        dislikeBtn.classList.remove('active-dislike');
                    } else {
                        dislikeBtn.classList.toggle('active-dislike');
                        likeBtn.classList.remove('active-like');
                    }
                } else {
                    alert("Please login to react to this article.");
                }
            })
            .catch(err => console.error(err));
        }
        lucide.createIcons();
    </script>
</body>
</html>