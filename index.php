<?php
// ============================================================
// Aviation Family Sri Lanka — index.php (Homepage)
// ============================================================
require_once 'config/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$pageTitle       = 'Home';
$pageDescription = 'Aviation Family Sri Lanka — ගුවන් යානා ලෝලීන් වෙනුවෙන්ම වෙන්වූ සයිබර් අවකාශය. Aviation news, spotting gallery, jobs, events and community.';
$pageCss         = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/pages/home.css">';

// ── Fetch data ──────────────────────────────────────────────
try {
    // Latest articles
    $stmt = $pdo->query("
        SELECT a.*, u.full_name, u.username, u.avatar
        FROM articles a
        JOIN users u ON a.user_id = u.id
        WHERE a.status = 'published'
        ORDER BY a.published_at DESC
        LIMIT 7
    ");
    $articles = $stmt->fetchAll();

    // Featured photos
    $stmt = $pdo->query("
        SELECT sp.*, u.username, u.full_name
        FROM spotting_photos sp
        JOIN users u ON sp.user_id = u.id
        WHERE sp.is_approved = 1 AND sp.is_featured = 1
        ORDER BY sp.likes DESC
        LIMIT 6
    ");
    $photos = $stmt->fetchAll();

    // Upcoming events
    $stmt = $pdo->query("
        SELECT * FROM events
        WHERE is_active = 1 AND start_date >= NOW()
        ORDER BY start_date ASC
        LIMIT 3
    ");
    $events = $stmt->fetchAll();

    // Latest jobs
    $stmt = $pdo->query("
        SELECT * FROM vacancies
        WHERE is_active = 1
        ORDER BY is_featured DESC, created_at DESC
        LIMIT 5
    ");
    $jobs = $stmt->fetchAll();

    // Featured products
    $stmt = $pdo->query("
        SELECT * FROM products
        WHERE is_active = 1 AND is_featured = 1
        ORDER BY created_at DESC
        LIMIT 4
    ");
    $products = $stmt->fetchAll();

    // Forum threads
    $stmt = $pdo->query("
        SELECT ft.*, u.username, u.avatar, u.full_name
        FROM forum_threads ft
        JOIN users u ON ft.user_id = u.id
        ORDER BY ft.is_pinned DESC, ft.last_reply_at DESC
        LIMIT 5
    ");
    $threads = $stmt->fetchAll();

    // Community stats
    $stats = [
        'members' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn(),
        'photos'  => $pdo->query("SELECT COUNT(*) FROM spotting_photos WHERE is_approved=1")->fetchColumn(),
        'articles'=> $pdo->query("SELECT COUNT(*) FROM articles WHERE status='published'")->fetchColumn(),
        'jobs'    => $pdo->query("SELECT COUNT(*) FROM vacancies WHERE is_active=1")->fetchColumn(),
    ];

} catch(PDOException $e) {
    $articles = $photos = $events = $jobs = $products = $threads = [];
    $stats = ['members'=>0,'photos'=>0,'articles'=>0,'jobs'=>0];
}

// Forum category icons
$catIcons = [
    'Spotting'   => '📸',
    'Careers'    => '💼',
    'ATC'        => '🎧',
    'General'    => '✈️',
    'Cabin Crew' => '👩‍✈️',
    'Safety'     => '🛡️',
    'Events'     => '📅',
    'default'    => '💬',
];

// WhatsApp link (update with real link)
$whatsappLink = '#';

$lang = $_SESSION['lang'] ?? 'en';

require_once 'includes/header.php';
?>

<!-- ============================================================
     ANNOUNCEMENT / TOP BAR
     ============================================================ -->
<div class="announce-bar" role="banner" aria-label="Site announcements">
  <div class="container">
    <div class="announce-bar__inner">

      <!-- Left: WhatsApp -->
      <div class="announce-bar__left">
        <a href="<?= e($whatsappLink) ?>"
           class="announce-bar__whatsapp"
           target="_blank"
           rel="noopener noreferrer"
           aria-label="Join our WhatsApp group">
          <i class="fa-brands fa-whatsapp"></i>
          <span>Join WhatsApp Group</span>
        </a>
      </div>

      <!-- Centre: News ticker -->
      <div class="announce-bar__ticker" aria-hidden="true">
        <div class="announce-bar__ticker-track">
          <?php foreach(array_merge($articles, $articles) as $a): ?>
          <span class="ticker-item">
            <i class="fa-solid fa-plane-up"></i>
            <?= e(truncate($a['title'], 8)) ?>
          </span>
          <span class="ticker-sep">✦</span>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Right: Login / Register -->
      <?php if(!isLoggedIn()): ?>
      <div class="announce-bar__right">
        <a href="<?= SITE_URL ?>/login.php"
           class="announce-bar__auth-btn announce-bar__auth-btn--login">
          <i class="fa-solid fa-right-to-bracket"></i>
          <?= t('nav_login', $lang) ?>
        </a>
        <a href="<?= SITE_URL ?>/register.php"
           class="announce-bar__auth-btn announce-bar__auth-btn--register">
          <i class="fa-solid fa-plane-departure"></i>
          <?= t('nav_register', $lang) ?>
        </a>
      </div>
      <?php else: ?>
      <div class="announce-bar__right">
        <span style="font-family:var(--font-cond);font-size:0.68rem;color:var(--teal-500);font-weight:600;">
          <i class="fa-solid fa-circle-check"></i>
          Welcome, <?= e(explode(' ', currentUser()['full_name'])[0]) ?> ✈️
        </span>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section class="hero" aria-label="Hero">
  <div class="hero__bg">
    <div class="hero__bg-gradient"></div>
    <div class="hero__bg-grid"></div>
    <div class="hero__radar"></div>
    <!-- Large faded plane silhouette -->
    <svg class="hero__bg-plane" viewBox="0 0 800 300" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M50 150 L400 80 L750 150 L400 200 Z" fill="white" opacity="0.5"/>
      <path d="M400 80 L420 30 L450 80" fill="white" opacity="0.4"/>
      <path d="M350 190 L360 230 L400 200 L440 230 L450 190" fill="white" opacity="0.4"/>
    </svg>
  </div>

  <!-- Flying plane emoji -->
  <div class="hero__plane-fly" aria-hidden="true">✈</div>

  <div class="hero__content">
    <div class="container">
      <div style="max-width:640px;">

        <div class="hero__eyebrow anim-fade-in">
          <i class="fa-solid fa-compass"></i>
          Aviation Family · Sri Lanka
        </div>

        <h1 class="hero__title anim-fade-in delay-1">
          <span class="block">Your Sky,</span>
          <span class="block">Your <span class="accent">Community</span></span>
        </h1>

        <p class="hero__slogan anim-fade-in delay-2">
          "ගුවන් යානා ලෝලීන් වෙනුවෙන්ම වෙන්වූ සයිබර් අවකාශය"
        </p>

        <div class="hero__actions anim-fade-in delay-3">
          <a href="<?= SITE_URL ?>/gallery.php" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-images"></i>
            View Gallery
          </a>
          <a href="<?= SITE_URL ?>/articles.php" class="btn btn-secondary btn-lg">
            <i class="fa-solid fa-newspaper"></i>
            Latest News
          </a>
          <?php if(!isLoggedIn()): ?>
          <a href="<?= SITE_URL ?>/register.php" class="btn btn-ghost btn-lg">
            <i class="fa-solid fa-user-plus"></i>
            Join Free
          </a>
          <?php endif; ?>
        </div>

        <!-- Stats strip -->
        <div class="hero__stats anim-fade-in delay-4">
          <div class="hero__stat">
            <span class="hero__stat-value" data-count="<?= $stats['members'] ?>">
              <?= number_format($stats['members']) ?>
            </span>
            <span class="hero__stat-label">Members</span>
          </div>
          <div class="hero__stat-sep"></div>
          <div class="hero__stat">
            <span class="hero__stat-value" data-count="<?= $stats['photos'] ?>">
              <?= number_format($stats['photos']) ?>
            </span>
            <span class="hero__stat-label">Photos</span>
          </div>
          <div class="hero__stat-sep"></div>
          <div class="hero__stat">
            <span class="hero__stat-value" data-count="<?= $stats['articles'] ?>">
              <?= number_format($stats['articles']) ?>
            </span>
            <span class="hero__stat-label">Articles</span>
          </div>
          <div class="hero__stat-sep"></div>
          <div class="hero__stat">
            <span class="hero__stat-value" data-count="<?= $stats['jobs'] ?>">
              <?= number_format($stats['jobs']) ?>
            </span>
            <span class="hero__stat-label">Jobs</span>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Scroll cue -->
  <div style="position:absolute;bottom:var(--sp-6);left:50%;transform:translateX(-50%);z-index:2;text-align:center;animation:fadeInUp 1s ease 1.5s both;">
    <div style="font-family:var(--font-cond);font-size:0.62rem;letter-spacing:0.15em;text-transform:uppercase;color:var(--text-muted);margin-bottom:6px;">Scroll</div>
    <div style="width:1px;height:36px;background:linear-gradient(to bottom,var(--teal-500),transparent);margin:0 auto;animation:pulse 2s ease infinite;"></div>
  </div>
</section>

<!-- ============================================================
     LATEST NEWS
     ============================================================ -->
<section class="home-section" aria-labelledby="news-heading">
  <div class="container">
    <div class="section-header section-header--left">
      <div class="eyebrow">
        <i class="fa-solid fa-newspaper"></i>
        Latest News
      </div>
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:var(--sp-4);flex-wrap:wrap;">
        <h2 id="news-heading">Aviation News &amp; Articles</h2>
        <a href="<?= SITE_URL ?>/articles.php" class="btn btn-secondary btn-sm" style="flex-shrink:0;margin-top:4px;">
          View All <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
    </div>

    <?php if(empty($articles)): ?>
    <div class="empty-state">
      <span class="empty-state__icon">📰</span>
      <div class="empty-state__title">No articles yet</div>
    </div>
    <?php else: ?>
    <div class="news-grid">
      <?php foreach($articles as $i => $a): ?>
      <article class="card article-card <?= $i === 0 ? '' : '' ?> anim-fade-in delay-<?= min($i+1,5) ?>">
        <?php if(!empty($a['cover_image'])): ?>
        <img src="<?= SITE_URL ?>/uploads/photos/<?= e($a['cover_image']) ?>"
             alt="<?= e($a['title']) ?>"
             class="card__img card__img--16-9"
             loading="lazy"
             onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.jpg'">
        <?php else: ?>
        <div class="card__img card__img--16-9"
             style="display:flex;align-items:center;justify-content:center;font-size:3rem;background:linear-gradient(135deg,var(--navy-800),var(--navy-700));">
          ✈️
        </div>
        <?php endif; ?>

        <div class="card__body">
          <span class="card__category"><?= e($a['category'] ?? 'General') ?></span>
          <h3 class="card__title">
            <a href="<?= SITE_URL ?>/article.php?slug=<?= e($a['slug']) ?>"
               style="color:inherit;text-decoration:none;">
              <?= e($a['title']) ?>
            </a>
          </h3>
          <?php if(!empty($a['excerpt'])): ?>
          <p class="card__excerpt line-clamp-3"><?= e($a['excerpt']) ?></p>
          <?php endif; ?>
        </div>

        <div class="card__footer">
          <div class="card__meta">
            <span><i class="fa-regular fa-user"></i> <?= e($a['full_name'] ?? $a['username']) ?></span>
            <span><i class="fa-regular fa-clock"></i> <?= timeAgo($a['published_at'] ?? $a['created_at']) ?></span>
            <span><i class="fa-regular fa-eye"></i> <?= number_format($a['views']) ?></span>
          </div>
          <a href="<?= SITE_URL ?>/article.php?slug=<?= e($a['slug']) ?>"
             class="btn btn-ghost btn-xs">
            Read <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </article>
      <?php if($i === 0 && count($articles) > 1): ?>
      <!-- After first featured article, wrap remaining in right col grid -->
      <div style="display:contents;">
      <?php endif; ?>
      <?php endforeach; ?>
      </div><!-- close display:contents -->
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ============================================================
     SPOTTING GALLERY
     ============================================================ -->
<section class="home-section home-section--alt" aria-labelledby="gallery-heading">
  <div class="container">
    <div class="section-header section-header--left">
      <div class="eyebrow"><i class="fa-solid fa-camera"></i> Spotting Gallery</div>
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:var(--sp-4);flex-wrap:wrap;">
        <h2 id="gallery-heading">Featured Spotting Photos</h2>
        <a href="<?= SITE_URL ?>/gallery.php" class="btn btn-secondary btn-sm" style="flex-shrink:0;margin-top:4px;">
          Full Gallery <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
    </div>

    <?php if(empty($photos)): ?>
    <div class="empty-state">
      <span class="empty-state__icon">📸</span>
      <div class="empty-state__title">No photos yet</div>
    </div>
    <?php else: ?>
    <div class="gallery-grid">
      <?php foreach($photos as $i => $p): ?>
      <a href="<?= SITE_URL ?>/gallery.php?id=<?= $p['id'] ?>"
         class="photo-card anim-fade-in delay-<?= min($i+1,5) ?>"
         aria-label="View photo: <?= e($p['title']) ?>">
        <img src="<?= SITE_URL ?>/<?= e($p['file_path']) ?>"
             alt="<?= e($p['title']) ?>"
             loading="lazy"
             onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.jpg'">

        <div class="photo-card__overlay">
          <div class="photo-card__title"><?= e($p['title']) ?></div>
          <div class="photo-card__sub">
            <?= e($p['aircraft_type'] ?? '') ?>
            <?= !empty($p['airline']) ? '· ' . e($p['airline']) : '' ?>
            <?= !empty($p['airport']) ? '· ' . e($p['airport']) : '' ?>
          </div>
        </div>

        <div class="photo-card__likes">
          <i class="fa-solid fa-heart" style="color:var(--red-400);"></i>
          <?= number_format($p['likes']) ?>
        </div>

        <?php if($p['is_featured']): ?>
        <div class="photo-card__featured">
          <span class="badge badge-gold"><i class="fa-solid fa-star"></i> Featured</span>
        </div>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ============================================================
     COMMUNITY STATS
     ============================================================ -->
<section class="stats-section" aria-labelledby="stats-heading">
  <div class="container">
    <div class="section-header" style="margin-bottom:var(--sp-10);">
      <div class="eyebrow"><i class="fa-solid fa-chart-line"></i> Community Stats</div>
      <h2 id="stats-heading">Growing Every Day</h2>
    </div>
    <div class="stats-grid">
      <div class="stat-card anim-fade-in delay-1">
        <span class="stat-card__icon">✈️</span>
        <div class="stat-card__value counter" data-target="<?= $stats['members'] ?>">0</div>
        <div class="stat-card__label">Community Members</div>
      </div>
      <div class="stat-card anim-fade-in delay-2">
        <span class="stat-card__icon">📸</span>
        <div class="stat-card__value counter" data-target="<?= $stats['photos'] ?>">0</div>
        <div class="stat-card__label">Spotting Photos</div>
      </div>
      <div class="stat-card anim-fade-in delay-3">
        <span class="stat-card__icon">📰</span>
        <div class="stat-card__value counter" data-target="<?= $stats['articles'] ?>">0</div>
        <div class="stat-card__label">Articles Published</div>
      </div>
      <div class="stat-card anim-fade-in delay-4">
        <span class="stat-card__icon">💼</span>
        <div class="stat-card__value counter" data-target="<?= $stats['jobs'] ?>">0</div>
        <div class="stat-card__label">Active Job Listings</div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     UPCOMING EVENTS + JOBS (two-col)
     ============================================================ -->
<section class="home-section" aria-label="Events and Jobs">
  <div class="container">
    <div class="grid-2" style="gap:var(--sp-10);">

      <!-- Events -->
      <div>
        <div class="section-header section-header--left" style="margin-bottom:var(--sp-6);">
          <div class="eyebrow"><i class="fa-solid fa-calendar-days"></i> Events</div>
          <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2>Upcoming Events</h2>
            <a href="<?= SITE_URL ?>/events.php" class="btn btn-ghost btn-sm">All <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

        <?php if(empty($events)): ?>
        <div class="empty-state" style="padding:var(--sp-8);">
          <span class="empty-state__icon">📅</span>
          <div class="empty-state__title">No upcoming events</div>
        </div>
        <?php else: ?>
        <div class="events-strip" style="grid-template-columns:1fr;">
          <?php foreach($events as $ev): ?>
          <?php
            $dt    = new DateTime($ev['start_date']);
            $day   = $dt->format('d');
            $month = $dt->format('M');
            $year  = $dt->format('Y');
            $typeLabel = ucfirst($ev['event_type'] ?? 'Event');
          ?>
          <a href="<?= SITE_URL ?>/events.php?slug=<?= e($ev['slug']) ?>"
             class="event-card"
             style="flex-direction:row;align-items:stretch;"
             aria-label="<?= e($ev['title']) ?>">
            <div class="event-card__date-strip" style="flex-direction:column;align-items:center;justify-content:center;min-width:80px;padding:var(--sp-4);">
              <span class="event-card__day"><?= $day ?></span>
              <span class="event-card__month"><?= $month ?></span>
              <span class="event-card__year"><?= $year ?></span>
            </div>
            <div class="event-card__body">
              <div class="event-card__type"><?= e($typeLabel) ?></div>
              <div class="event-card__title"><?= e($ev['title']) ?></div>
              <div class="event-card__venue">
                <i class="fa-solid fa-location-dot" style="color:var(--teal-500);font-size:0.7rem;"></i>
                <?= e($ev['venue'] ?? $ev['country'] ?? 'TBA') ?>
              </div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Jobs -->
      <div>
        <div class="section-header section-header--left" style="margin-bottom:var(--sp-6);">
          <div class="eyebrow"><i class="fa-solid fa-briefcase"></i> Careers</div>
          <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2>Latest Jobs</h2>
            <a href="<?= SITE_URL ?>/jobs.php" class="btn btn-ghost btn-sm">All <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

        <?php if(empty($jobs)): ?>
        <div class="empty-state" style="padding:var(--sp-8);">
          <span class="empty-state__icon">💼</span>
          <div class="empty-state__title">No jobs listed</div>
        </div>
        <?php else: ?>
        <?php foreach($jobs as $j): ?>
        <a href="<?= SITE_URL ?>/job.php?id=<?= $j['id'] ?>" class="job-row">
          <div class="job-row__logo">
            <?php if(!empty($j['company_logo'])): ?>
            <img src="<?= SITE_URL ?>/uploads/photos/<?= e($j['company_logo']) ?>"
                 alt="<?= e($j['company']) ?>">
            <?php else: ?>
            ✈️
            <?php endif; ?>
          </div>
          <div class="job-row__info">
            <div class="job-row__title"><?= e($j['title']) ?></div>
            <div class="job-row__company">
              <i class="fa-solid fa-building" style="font-size:0.65rem;"></i>
              <?= e($j['company']) ?>
              &nbsp;·&nbsp;
              <i class="fa-solid fa-location-dot" style="font-size:0.65rem;"></i>
              <?= e($j['location']) ?>
            </div>
          </div>
          <div class="job-row__right">
            <span class="badge <?= $j['type']==='full-time' ? 'badge-teal' : ($j['type']==='internship' ? 'badge-gold' : 'badge-gray') ?>">
              <?= e(ucfirst(str_replace('-',' ',$j['type']))) ?>
            </span>
            <?php if(!empty($j['deadline'])): ?>
            <span style="font-size:0.65rem;color:var(--text-muted);">
              <i class="fa-regular fa-clock"></i>
              <?= formatDate($j['deadline'], 'd M') ?>
            </span>
            <?php endif; ?>
          </div>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>

<!-- ============================================================
     FORUM PREVIEW
     ============================================================ -->
<section class="home-section home-section--alt" aria-labelledby="forum-heading">
  <div class="container">
    <div class="layout-sidebar">
      <div>
        <div class="section-header section-header--left" style="margin-bottom:var(--sp-6);">
          <div class="eyebrow"><i class="fa-solid fa-comments"></i> Community</div>
          <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 id="forum-heading">Forum Discussions</h2>
            <a href="<?= SITE_URL ?>/forum.php" class="btn btn-ghost btn-sm">All Threads <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

        <?php if(empty($threads)): ?>
        <div class="empty-state">
          <span class="empty-state__icon">💬</span>
          <div class="empty-state__title">No discussions yet</div>
        </div>
        <?php else: ?>
        <div class="card" style="padding:var(--sp-2);">
          <?php foreach($threads as $th): ?>
          <?php $icon = $catIcons[$th['category']] ?? $catIcons['default']; ?>
          <a href="<?= SITE_URL ?>/thread.php?slug=<?= e($th['slug']) ?>"
             class="forum-thread-row"
             aria-label="<?= e($th['title']) ?>">
            <div class="forum-thread-row__cat"><?= $icon ?></div>
            <div style="flex:1;min-width:0;">
              <div class="forum-thread-row__title"><?= e($th['title']) ?></div>
              <div class="forum-thread-row__meta">
                <span><i class="fa-solid fa-user" style="font-size:0.6rem;"></i> <?= e($th['full_name'] ?? $th['username']) ?></span>
                <span><i class="fa-solid fa-tag" style="font-size:0.6rem;"></i> <?= e($th['category']) ?></span>
                <span><?= timeAgo($th['last_reply_at'] ?? $th['created_at']) ?></span>
                <?php if($th['is_pinned']): ?>
                <span class="badge badge-teal" style="padding:1px 6px;font-size:0.55rem;">📌 Pinned</span>
                <?php endif; ?>
              </div>
            </div>
            <div class="forum-thread-row__replies">
              <div class="forum-thread-row__replies-count"><?= $th['reply_count'] ?></div>
              <div class="forum-thread-row__replies-label">Replies</div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Sidebar: Join CTA + Quick Links -->
      <div class="sticky-sidebar">
        <!-- Join CTA -->
        <?php if(!isLoggedIn()): ?>
        <div class="widget">
          <div style="background:linear-gradient(135deg,var(--navy-700),var(--navy-600));padding:var(--sp-6);text-align:center;">
            <div style="font-size:2.5rem;margin-bottom:var(--sp-3);">✈️</div>
            <h3 style="font-size:1rem;margin-bottom:var(--sp-2);color:var(--white);">Join Aviation Family</h3>
            <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:var(--sp-5);">
              Connect with <?= number_format($stats['members']) ?>+ aviation enthusiasts across Sri Lanka.
            </p>
            <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-full">
              <i class="fa-solid fa-plane-departure"></i> Join Free
            </a>
            <a href="<?= SITE_URL ?>/login.php" class="btn btn-ghost btn-full" style="margin-top:var(--sp-2);">
              Already a member? Login
            </a>
          </div>
        </div>
        <?php else: ?>
        <!-- Logged in: Profile quick card -->
        <?php $u = currentUser(); ?>
        <div class="widget">
          <div style="padding:var(--sp-5);text-align:center;">
            <div class="avatar avatar--lg mx-auto" style="margin-bottom:var(--sp-3);">
              <?php
                $initials = implode('', array_map(fn($w)=>strtoupper($w[0]),
                  array_slice(explode(' ', trim($u['full_name']??'U')), 0, 2)));
              ?>
              <?= e($initials) ?>
            </div>
            <div class="fw-600"><?= e($u['full_name']) ?></div>
            <div class="text-xs text-muted mt-2" style="margin-top:var(--sp-1);">
              <span style="color:<?= getRankColor($u['rank_title']??'Ground Crew') ?>;">
                <?= e($u['rank_title']??'Ground Crew') ?>
              </span>
              · <?= number_format($u['rank_points']??0) ?> pts
            </div>
            <a href="<?= SITE_URL ?>/dashboard.php" class="btn btn-secondary btn-sm btn-full" style="margin-top:var(--sp-4);">
              <i class="fa-solid fa-gauge"></i> My Dashboard
            </a>
          </div>
        </div>
        <?php endif; ?>

        <!-- Quick Stats widget -->
        <div class="widget">
          <div class="widget__header">
            <span class="widget__title">Community Highlights</span>
          </div>
          <div class="widget__body widget__body--compact">
            <div style="display:flex;flex-direction:column;gap:var(--sp-3);">
              <?php
                $highlights = [
                  ['icon'=>'fa-users',    'label'=>'Members',  'val'=>$stats['members'],  'color'=>'var(--teal-500)'],
                  ['icon'=>'fa-images',   'label'=>'Photos',   'val'=>$stats['photos'],   'color'=>'var(--gold-500)'],
                  ['icon'=>'fa-newspaper','label'=>'Articles', 'val'=>$stats['articles'], 'color'=>'var(--blue-300)'],
                  ['icon'=>'fa-briefcase','label'=>'Live Jobs','val'=>$stats['jobs'],     'color'=>'var(--green-500)'],
                ];
                foreach($highlights as $h):
              ?>
              <div style="display:flex;align-items:center;justify-content:space-between;">
                <span style="display:flex;align-items:center;gap:var(--sp-2);font-size:var(--text-sm);color:var(--text-secondary);">
                  <i class="fa-solid <?= $h['icon'] ?>" style="color:<?= $h['color'] ?>;width:14px;"></i>
                  <?= $h['label'] ?>
                </span>
                <span style="font-family:var(--font-display);font-size:0.9rem;font-weight:700;color:<?= $h['color'] ?>;">
                  <?= number_format($h['val']) ?>
                </span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ============================================================
     SHOP PREVIEW
     ============================================================ -->
<?php if(!empty($products)): ?>
<section class="home-section home-section--dark" aria-labelledby="shop-heading">
  <div class="container">
    <div class="section-header">
      <div class="eyebrow"><i class="fa-solid fa-bag-shopping"></i> Shop</div>
      <h2 id="shop-heading">Aviation Merchandise</h2>
      <p>Gear up with premium aviation apparel and accessories</p>
    </div>
    <div class="shop-preview-grid">
      <?php foreach($products as $i => $pr): ?>
      <a href="<?= SITE_URL ?>/shop.php?id=<?= $pr['id'] ?>"
         class="product-card anim-fade-in delay-<?= $i+1 ?>"
         aria-label="<?= e($pr['name']) ?>">
        <div class="product-card__img">
          <?php if(!empty($pr['image'])): ?>
          <img src="<?= SITE_URL ?>/uploads/products/<?= e($pr['image']) ?>"
               alt="<?= e($pr['name']) ?>"
               loading="lazy"
               onerror="this.parentElement.innerHTML='🛍️'">
          <?php else: ?>
          🛍️
          <?php endif; ?>
          <?php if($pr['stock'] < 5 && $pr['stock'] > 0): ?>
          <span class="badge badge-red" style="position:absolute;top:var(--sp-2);right:var(--sp-2);">
            Only <?= $pr['stock'] ?> left
          </span>
          <?php elseif($pr['stock'] == 0): ?>
          <span class="badge badge-gray" style="position:absolute;top:var(--sp-2);right:var(--sp-2);">
            Sold Out
          </span>
          <?php endif; ?>
        </div>
        <div class="product-card__body">
          <div class="product-card__name"><?= e($pr['name']) ?></div>
          <div class="product-card__price">
            <span class="currency">LKR</span>
            <?= number_format($pr['price'], 2) ?>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:var(--sp-8);">
      <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary btn-lg">
        <i class="fa-solid fa-bag-shopping"></i> Visit Shop
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============================================================
     NEWSLETTER CTA
     ============================================================ -->
<section class="home-section" aria-labelledby="newsletter-heading">
  <div class="container">
    <div class="newsletter-cta">
      <span class="newsletter-cta__icon">✉️</span>
      <div class="eyebrow" style="justify-content:center;"><?= t('newsletter_sub', $lang) ?></div>
      <h2 id="newsletter-heading">Stay in the Loop</h2>
      <p>Get the latest aviation news, job alerts, event announcements and community highlights — straight to your inbox. No spam, ever.</p>
      <form class="newsletter-cta__form" id="heroCTAForm" novalidate>
        <?= csrfField() ?>
        <input type="email"
               name="email"
               id="heroCTAEmail"
               placeholder="Your email address"
               required
               autocomplete="email">
        <button type="submit" class="btn btn-primary" style="border-radius:var(--r-full);padding:0.75rem 1.5rem;flex-shrink:0;">
          <i class="fa-solid fa-paper-plane"></i> Subscribe
        </button>
      </form>
    </div>
  </div>
</section>

<!-- ============================================================
     PAGE SCRIPTS
     ============================================================ -->
<script>
// ── Counter animation ──────────────────────────────────────
(function(){
  function animateCounter(el) {
    var target = parseInt(el.dataset.target) || 0;
    var duration = 2000;
    var start = performance.now();
    function update(now) {
      var elapsed = Math.min((now - start) / duration, 1);
      var eased   = 1 - Math.pow(1 - elapsed, 3);
      el.textContent = Math.floor(eased * target).toLocaleString();
      if (elapsed < 1) requestAnimationFrame(update);
      else el.textContent = target.toLocaleString();
    }
    requestAnimationFrame(update);
  }

  var counters = document.querySelectorAll('.counter');
  if (!counters.length) return;

  var obs = new IntersectionObserver(function(entries){
    entries.forEach(function(entry){
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  counters.forEach(function(c){ obs.observe(c); });
})();

// ── Hero newsletter CTA ────────────────────────────────────
(function(){
  var form = document.getElementById('heroCTAForm');
  if (!form) return;
  form.addEventListener('submit', function(e){
    e.preventDefault();
    var email = document.getElementById('heroCTAEmail').value.trim();
    var btn   = form.querySelector('button[type="submit"]');
    if (!email) return;
    btn.innerHTML = '<div class="spinner spinner--sm" style="border-top-color:var(--navy-900);"></div>';
    btn.disabled  = true;
    fetch('<?= SITE_URL ?>/api/newsletter.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({email: email, csrf: '<?= csrfToken() ?>'})
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (data.success) {
        form.innerHTML = '<div class="flash flash--success" style="width:100%;justify-content:center;">' +
          '<span class="flash__icon"><i class="fa-solid fa-check"></i></span>' +
          '<span>You\'re subscribed! Welcome aboard ✈️</span></div>';
      } else {
        btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Subscribe';
        btn.disabled  = false;
      }
    })
    .catch(function(){
      btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Subscribe';
      btn.disabled  = false;
    });
  });
})();

// ── Intersection observer for anim-fade-in ────────────────
(function(){
  var els = document.querySelectorAll('.anim-fade-in');
  var obs = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      if (e.isIntersecting) {
        e.target.style.animationPlayState = 'running';
        obs.unobserve(e.target);
      }
    });
  }, { threshold: 0.1 });
  els.forEach(function(el){
    el.style.animationPlayState = 'paused';
    obs.observe(el);
  });
})();
</script>

<?php require_once 'includes/footer.php'; ?>