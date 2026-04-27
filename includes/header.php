<?php
// ============================================================
// Aviation Family Sri Lanka — includes/header.php
// Global header included on every page
// ============================================================

// Bootstrap core files if not already loaded
if (!defined('DB_NAME')) {
    require_once __DIR__ . '/../config/db.php';
}
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// ── Language ──
$lang = $_SESSION['lang'] ?? $_COOKIE['af_lang'] ?? 'en';
if (!in_array($lang, ['en', 'si', 'ta'])) $lang = 'en';
if (!isset($_SESSION['lang'])) $_SESSION['lang'] = $lang;

// ── Dark mode ──
$darkMode = $_COOKIE['af_dark'] ?? '1'; // default dark

// ── Page meta (set before including header) ──
$pageTitle       = $pageTitle       ?? 'Aviation Family Sri Lanka';
$pageDescription = $pageDescription ?? 'Your community in the skies — aviation news, spotting gallery, jobs, events and more.';
$pageKeywords    = $pageKeywords    ?? 'aviation, Sri Lanka, plane spotting, aviation jobs, airshow';
$pageCss         = $pageCss         ?? '';   // extra <link> tags
$bodyClass       = $bodyClass       ?? '';

// ── Current user ──
$currentUser = currentUser();
$isLoggedIn  = isLoggedIn();
$unreadCount = 0;
if ($isLoggedIn && isset($pdo)) {
    try { $unreadCount = getUnreadCount($pdo, currentUserId()); } catch(Exception $e) {}
}

// ── Current page for active nav ──
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// ── Site settings ──
$siteName    = 'Aviation Family';
$siteTagline = 'Your Community in the Skies';
if (isset($pdo)) {
    try {
        $siteName    = getSetting($pdo, 'site_name',    $siteName);
        $siteTagline = getSetting($pdo, 'site_tagline', $siteTagline);
    } catch(Exception $e) {}
}

// Helper: active class
function navActive(string $page): string {
    global $currentPage;
    $map = [
        'articles' => ['articles','article'],
        'gallery'  => ['gallery'],
        'jobs'     => ['jobs','job'],
        'shop'     => ['shop','checkout','cart'],
        'events'   => ['events','event'],
        'quiz'     => ['quiz'],
        'forum'    => ['forum','thread'],
        'contact'  => ['contact'],
    ];
    $pages = $map[$page] ?? [$page];
    return in_array($currentPage, $pages) ? ' nav__link--active' : '';
}

// Language label
$langLabel = ['en' => 'EN', 'si' => 'සිං', 'ta' => 'தமி'];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" class="<?= $darkMode === '0' ? '' : 'dark' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($pageDescription) ?>">
  <meta name="keywords"    content="<?= e($pageKeywords) ?>">
  <meta name="author"      content="Aviation Family Sri Lanka">
  <meta name="theme-color" content="#0A1628">

  <!-- Open Graph -->
  <meta property="og:title"       content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDescription) ?>">
  <meta property="og:type"        content="website">
  <meta property="og:site_name"   content="Aviation Family Sri Lanka">

  <!-- Twitter Card -->
  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="<?= e($pageTitle) ?>">
  <meta name="twitter:description" content="<?= e($pageDescription) ?>">

  <title><?= e($pageTitle) ?> | Aviation Family Sri Lanka</title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/images/favicon.png">
  <link rel="apple-touch-icon"      href="<?= SITE_URL ?>/assets/images/apple-touch-icon.png">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Core CSS -->
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/main.css">

  <!-- Page-specific CSS -->
  <?= $pageCss ?>

  <!-- Inline: apply dark/light before paint to avoid flash -->
  <script>
    (function(){
      var d = document.documentElement;
      var dark = document.cookie.match(/af_dark=([^;]+)/);
      var val  = dark ? dark[1] : '1';
      if(val === '0') document.body && document.body.classList.add('light-mode');
    })();
  </script>
</head>
<body class="<?= $darkMode === '0' ? 'light-mode' : '' ?> <?= e($bodyClass) ?>">

<!-- ============================================================
     PAGE LOADER
     ============================================================ -->
<div class="page-loader" id="pageLoader">
  <div style="text-align:center;">
    <div style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;color:var(--white);letter-spacing:0.08em;margin-bottom:1rem;">
      ✈ <span style="color:var(--teal-500);">AVIATION</span> FAMILY
    </div>
    <div class="spinner spinner--lg"></div>
  </div>
</div>

<!-- ============================================================
     NAVBAR
     ============================================================ -->
<header class="navbar" id="navbar" role="banner">
  <div class="container">
    <div class="navbar__inner">

      <!-- Logo -->
      <a href="<?= SITE_URL ?>/index.php" class="navbar__logo" aria-label="Aviation Family Home">
        <img src="<?= SITE_URL ?>/assets/images/logo.png"
             alt="Aviation Family Sri Lanka Logo"
             class="navbar__logo-img"
             onerror="this.style.display='none'">
        <div class="navbar__logo-text">
          <span class="navbar__logo-name"><?= e($siteName) ?></span>
          <span class="navbar__logo-tagline">Sri Lanka</span>
        </div>
      </a>

      <!-- Desktop Navigation -->
      <nav class="navbar__nav" id="navMenu" aria-label="Main navigation">
        <a href="<?= SITE_URL ?>/index.php"
           class="nav__link<?= navActive('index') ?>"
           data-translate="nav_home">
          <i class="fa-solid fa-house"></i>
          <span><?= t('nav_home', $lang) ?></span>
        </a>
        <a href="<?= SITE_URL ?>/articles.php"
           class="nav__link<?= navActive('articles') ?>"
           data-translate="nav_news">
          <i class="fa-solid fa-newspaper"></i>
          <span><?= t('nav_news', $lang) ?></span>
        </a>
        <a href="<?= SITE_URL ?>/gallery.php"
           class="nav__link<?= navActive('gallery') ?>"
           data-translate="nav_gallery">
          <i class="fa-solid fa-images"></i>
          <span><?= t('nav_gallery', $lang) ?></span>
        </a>
        <a href="<?= SITE_URL ?>/jobs.php"
           class="nav__link<?= navActive('jobs') ?>"
           data-translate="nav_jobs">
          <i class="fa-solid fa-briefcase"></i>
          <span><?= t('nav_jobs', $lang) ?></span>
        </a>
        <a href="<?= SITE_URL ?>/shop.php"
           class="nav__link<?= navActive('shop') ?>"
           data-translate="nav_shop">
          <i class="fa-solid fa-bag-shopping"></i>
          <span><?= t('nav_shop', $lang) ?></span>
        </a>
        <a href="<?= SITE_URL ?>/events.php"
           class="nav__link<?= navActive('events') ?>"
           data-translate="nav_events">
          <i class="fa-solid fa-calendar-days"></i>
          <span><?= t('nav_events', $lang) ?></span>
        </a>
        <a href="<?= SITE_URL ?>/quiz.php"
           class="nav__link<?= navActive('quiz') ?>"
           data-translate="nav_quiz">
          <i class="fa-solid fa-circle-question"></i>
          <span><?= t('nav_quiz', $lang) ?></span>
        </a>
        <a href="<?= SITE_URL ?>/forum.php"
           class="nav__link<?= navActive('forum') ?>"
           data-translate="nav_forum">
          <i class="fa-solid fa-comments"></i>
          <span><?= t('nav_forum', $lang) ?></span>
        </a>
        <a href="<?= SITE_URL ?>/contact.php"
           class="nav__link<?= navActive('contact') ?>"
           data-translate="nav_contact">
          <i class="fa-solid fa-envelope"></i>
          <span><?= t('nav_contact', $lang) ?></span>
        </a>

        <!-- Mobile only extras -->
        <div class="mobile-nav-extras">
          <!-- Mobile lang switcher -->
          <div class="lang-switcher">
            <?php foreach(['en'=>'EN','si'=>'සිං','ta'=>'தமி'] as $code => $label): ?>
            <button class="lang-btn <?= $lang === $code ? 'active' : '' ?>"
                    onclick="setLang('<?= $code ?>')"
                    aria-label="Switch to <?= $code ?>">
              <?= $label ?>
            </button>
            <?php endforeach; ?>
          </div>
          <!-- Mobile dark toggle -->
          <button class="darkmode-toggle" onclick="toggleDark()" aria-label="Toggle dark mode">
            <i class="fa-solid <?= $darkMode === '0' ? 'fa-moon' : 'fa-sun' ?>" id="darkIconMobile"></i>
          </button>
        </div>
      </nav>

      <!-- Right Controls -->
      <div class="navbar__right">

        <!-- Language Switcher (desktop) -->
        <div class="lang-switcher hide-mobile" role="group" aria-label="Language switcher">
          <?php foreach(['en'=>'EN','si'=>'සිං','ta'=>'தமி'] as $code => $label): ?>
          <button class="lang-btn <?= $lang === $code ? 'active' : '' ?>"
                  onclick="setLang('<?= $code ?>')"
                  aria-label="Switch to <?= $code ?>">
            <?= $label ?>
          </button>
          <?php endforeach; ?>
        </div>

        <!-- Dark Mode Toggle (desktop) -->
        <button class="darkmode-toggle hide-mobile"
                onclick="toggleDark()"
                aria-label="Toggle dark mode"
                id="darkToggleBtn">
          <i class="fa-solid <?= $darkMode === '0' ? 'fa-moon' : 'fa-sun' ?>" id="darkIcon"></i>
        </button>

        <?php if($isLoggedIn): ?>

        <!-- Notifications Bell -->
        <div class="notif-wrapper" id="notifWrapper">
          <button class="notif-bell-btn"
                  onclick="toggleNotif()"
                  aria-label="Notifications"
                  aria-expanded="false"
                  id="notifBtn">
            <i class="fa-solid fa-bell"></i>
            <?php if($unreadCount > 0): ?>
            <span class="notif-count" aria-label="<?= $unreadCount ?> unread notifications">
              <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
            </span>
            <?php endif; ?>
          </button>

          <!-- Notification Dropdown -->
          <div class="notif-dropdown" id="notifDropdown" role="dialog" aria-label="Notifications">
            <div class="notif-dropdown__header">
              <span class="notif-dropdown__title">
                <i class="fa-solid fa-bell" style="color:var(--teal-500);margin-right:6px;"></i>
                Notifications
              </span>
              <?php if($unreadCount > 0): ?>
              <a href="<?= SITE_URL ?>/dashboard.php?tab=notifications"
                 class="text-xs text-accent fw-600">
                Mark all read
              </a>
              <?php endif; ?>
            </div>

            <!-- Notification items (AJAX loaded) -->
            <div id="notifList">
              <div style="padding:2rem;text-align:center;">
                <div class="spinner spinner--sm mx-auto"></div>
              </div>
            </div>

            <div class="notif-dropdown__footer">
              <a href="<?= SITE_URL ?>/dashboard.php?tab=notifications"
                 class="text-xs text-accent fw-600">
                View all notifications
              </a>
            </div>
          </div>
        </div>

        <!-- User Menu -->
        <div class="user-menu-wrapper" id="userMenuWrapper">
          <button class="user-menu-btn"
                  onclick="toggleUserMenu()"
                  aria-label="User menu"
                  aria-expanded="false"
                  id="userMenuBtn">
            <?php
              $avatar = $currentUser['avatar'] ?? null;
              $name   = $currentUser['full_name'] ?? $currentUser['username'] ?? 'User';
              $initials = implode('', array_map(fn($w) => strtoupper($w[0]),
                          array_slice(explode(' ', trim($name)), 0, 2)));
            ?>
            <?php if($avatar && file_exists(__DIR__ . '/../uploads/avatars/' . $avatar)): ?>
              <img src="<?= SITE_URL ?>/uploads/avatars/<?= e($avatar) ?>"
                   alt="<?= e($name) ?>"
                   class="avatar avatar--sm">
            <?php else: ?>
              <div class="avatar avatar--sm">
                <?= e($initials) ?>
              </div>
            <?php endif; ?>
            <span class="user-name"><?= e(explode(' ', $name)[0]) ?></span>
            <i class="fa-solid fa-chevron-down text-xs" style="opacity:0.5;"></i>
          </button>

          <!-- User Dropdown -->
          <div class="dropdown-menu" id="userDropdown" role="menu">
            <!-- User info header -->
            <div style="padding:var(--sp-4);border-bottom:1px solid var(--border-subtle);margin-bottom:var(--sp-2);">
              <div class="fw-600 text-sm"><?= e($name) ?></div>
              <div class="text-xs text-muted mt-2">
                <?php
                  $rank = $currentUser['rank_title'] ?? 'Ground Crew';
                  $rankColor = getRankColor($rank);
                ?>
                <span style="color:<?= $rankColor ?>;">
                  <i class="fa-solid fa-chevron-up" style="font-size:0.5rem;"></i>
                  <?= e($rank) ?>
                </span>
                &nbsp;·&nbsp;
                <?= number_format($currentUser['rank_points'] ?? 0) ?> pts
              </div>
              <?php if(($currentUser['role'] ?? '') === 'admin'): ?>
              <span class="badge badge-gold mt-4" style="margin-top:var(--sp-2);">
                <i class="fa-solid fa-shield-halved"></i> Admin
              </span>
              <?php elseif(($currentUser['membership_verified'] ?? 0)): ?>
              <span class="badge badge-teal mt-4" style="margin-top:var(--sp-2);">
                <i class="fa-solid fa-circle-check"></i> Verified
              </span>
              <?php endif; ?>
            </div>

            <a href="<?= SITE_URL ?>/dashboard.php" class="dropdown-item" role="menuitem">
              <i class="fa-solid fa-gauge"></i>
              Dashboard
            </a>
            <a href="<?= SITE_URL ?>/dashboard.php?tab=profile" class="dropdown-item" role="menuitem">
              <i class="fa-solid fa-user"></i>
              My Profile
            </a>
            <a href="<?= SITE_URL ?>/dashboard.php?tab=photos" class="dropdown-item" role="menuitem">
              <i class="fa-solid fa-images"></i>
              My Photos
            </a>
            <a href="<?= SITE_URL ?>/dashboard.php?tab=orders" class="dropdown-item" role="menuitem">
              <i class="fa-solid fa-bag-shopping"></i>
              My Orders
            </a>
            <a href="<?= SITE_URL ?>/dashboard.php?tab=bookmarks" class="dropdown-item" role="menuitem">
              <i class="fa-solid fa-bookmark"></i>
              Bookmarks
            </a>

            <?php if(($currentUser['membership_verified'] ?? 0) || in_array($currentUser['role'] ?? '', ['admin','editor'])): ?>
            <div class="dropdown-divider"></div>
            <a href="<?= SITE_URL ?>/membership-card.php" class="dropdown-item" role="menuitem"
               style="color:var(--gold-500);">
              <i class="fa-solid fa-id-card"></i>
              Membership Card
            </a>
            <?php endif; ?>

            <?php if(isAdmin()): ?>
            <div class="dropdown-divider"></div>
            <a href="<?= SITE_URL ?>/admin/index.php" class="dropdown-item" role="menuitem"
               style="color:var(--teal-500);">
              <i class="fa-solid fa-screwdriver-wrench"></i>
              Admin Panel
            </a>
            <?php endif; ?>

            <div class="dropdown-divider"></div>
            <a href="<?= SITE_URL ?>/logout.php" class="dropdown-item dropdown-item--danger" role="menuitem">
              <i class="fa-solid fa-right-from-bracket"></i>
              <?= t('nav_logout', $lang) ?>
            </a>
          </div>
        </div>

        <?php else: ?>

        <!-- Auth Buttons (logged out) -->
        <div class="navbar__auth">
          <a href="<?= SITE_URL ?>/login.php"
             class="btn btn-ghost btn-sm hide-mobile"
             data-translate="nav_login">
            <?= t('nav_login', $lang) ?>
          </a>
          <a href="<?= SITE_URL ?>/register.php"
             class="btn btn-primary btn-sm"
             data-translate="nav_register">
            <i class="fa-solid fa-plane-departure"></i>
            <?= t('nav_register', $lang) ?>
          </a>
        </div>

        <?php endif; ?>

        <!-- Mobile Hamburger -->
        <button class="mobile-toggle"
                id="mobileToggle"
                onclick="toggleMobileMenu()"
                aria-label="Toggle mobile menu"
                aria-expanded="false"
                aria-controls="navMenu">
          <span></span>
          <span></span>
          <span></span>
        </button>

      </div><!-- /.navbar__right -->
    </div><!-- /.navbar__inner -->
  </div><!-- /.container -->
</header>

<!-- Main content wrapper opens here — closed in footer.php -->
<main class="main-content" id="mainContent">

<?php
// Render flash message if any
$flash = getFlash();
if ($flash):
?>
<div class="container" style="padding-top:var(--sp-4);">
  <div class="flash flash--<?= e($flash['type']) ?>">
    <span class="flash__icon">
      <?= match($flash['type']) {
        'success' => '<i class="fa-solid fa-check"></i>',
        'error'   => '<i class="fa-solid fa-xmark"></i>',
        'warning' => '<i class="fa-solid fa-triangle-exclamation"></i>',
        default   => '<i class="fa-solid fa-circle-info"></i>',
      } ?>
    </span>
    <span><?= e($flash['message']) ?></span>
    <button onclick="this.parentElement.remove()"
            style="margin-left:auto;background:none;border:none;cursor:pointer;color:inherit;opacity:0.6;font-size:0.9rem;">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>
</div>
<?php endif; ?>

<!-- ============================================================
     GLOBAL JAVASCRIPT
     ============================================================ -->
<script>
// ── Dark Mode ──────────────────────────────────────────────
function toggleDark() {
  var body  = document.body;
  var light = body.classList.toggle('light-mode');
  var val   = light ? '0' : '1';
  document.cookie = 'af_dark=' + val + ';path=/;max-age=31536000';

  var icons = document.querySelectorAll('#darkIcon, #darkIconMobile');
  icons.forEach(function(el){
    el.className = 'fa-solid ' + (light ? 'fa-moon' : 'fa-sun');
  });
}

// ── Language Switcher ──────────────────────────────────────
function setLang(code) {
  document.cookie = 'af_lang=' + code + ';path=/;max-age=31536000';
  // POST to lang handler then reload
  fetch('<?= SITE_URL ?>/lang/switch.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({lang: code})
  }).then(function(){ location.reload(); })
    .catch(function(){ location.reload(); });
}

// ── Navbar scroll effect ───────────────────────────────────
(function(){
  var nav = document.getElementById('navbar');
  if (!nav) return;
  function onScroll() {
    nav.classList.toggle('scrolled', window.scrollY > 20);
  }
  window.addEventListener('scroll', onScroll, {passive: true});
  onScroll();
})();

// ── Mobile Menu ────────────────────────────────────────────
function toggleMobileMenu() {
  var menu   = document.getElementById('navMenu');
  var toggle = document.getElementById('mobileToggle');
  var open   = menu.classList.toggle('open');
  toggle.classList.toggle('open', open);
  toggle.setAttribute('aria-expanded', open);
  document.body.style.overflow = open ? 'hidden' : '';
}

// Close mobile menu on outside click
document.addEventListener('click', function(e){
  var menu   = document.getElementById('navMenu');
  var toggle = document.getElementById('mobileToggle');
  if (menu && menu.classList.contains('open')) {
    if (!menu.contains(e.target) && !toggle.contains(e.target)) {
      menu.classList.remove('open');
      toggle.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
    }
  }
});

// ── User Dropdown ──────────────────────────────────────────
function toggleUserMenu() {
  var dd  = document.getElementById('userDropdown');
  var btn = document.getElementById('userMenuBtn');
  var open = dd.classList.toggle('open');
  btn.setAttribute('aria-expanded', open);
  // Close notif if open
  var nd = document.getElementById('notifDropdown');
  if (nd) nd.classList.remove('open');
}

// ── Notification Dropdown ──────────────────────────────────
function toggleNotif() {
  var dd  = document.getElementById('notifDropdown');
  var btn = document.getElementById('notifBtn');
  if (!dd) return;
  var open = dd.classList.toggle('open');
  btn.setAttribute('aria-expanded', open);
  // Close user menu if open
  var ud = document.getElementById('userDropdown');
  if (ud) ud.classList.remove('open');

  // Load notifications via AJAX on first open
  if (open && !dd.dataset.loaded) {
    loadNotifications();
    dd.dataset.loaded = '1';
  }
}

function loadNotifications() {
  var list = document.getElementById('notifList');
  if (!list) return;
  fetch('<?= SITE_URL ?>/api/notifications.php')
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (!data.length) {
        list.innerHTML = '<div style="padding:2rem;text-align:center;color:var(--text-muted);font-size:0.85rem;">' +
          '<i class="fa-solid fa-bell-slash" style="font-size:1.8rem;display:block;margin-bottom:0.75rem;opacity:0.4;"></i>' +
          'No notifications yet</div>';
        return;
      }
      list.innerHTML = data.slice(0,6).map(function(n){
        return '<a href="' + (n.link || '#') + '" class="notif-item' + (n.is_read ? '' : ' notif-item--unread') + '">' +
          (n.is_read ? '' : '<div class="notif-item__dot"></div>') +
          '<div style="flex:1;">' +
            '<div class="notif-item__title">' + escHtml(n.title) + '</div>' +
            '<div class="notif-item__time">' + escHtml(n.created_at) + '</div>' +
          '</div></a>';
      }).join('');
    })
    .catch(function(){
      list.innerHTML = '<div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:0.85rem;">Could not load notifications.</div>';
    });
}

// ── Close dropdowns on outside click ──────────────────────
document.addEventListener('click', function(e){
  // User dropdown
  var uw = document.getElementById('userMenuWrapper');
  if (uw && !uw.contains(e.target)) {
    var ud = document.getElementById('userDropdown');
    if (ud) ud.classList.remove('open');
  }
  // Notif dropdown
  var nw = document.getElementById('notifWrapper');
  if (nw && !nw.contains(e.target)) {
    var nd = document.getElementById('notifDropdown');
    if (nd) nd.classList.remove('open');
  }
});

// ── Escape key closes dropdowns ────────────────────────────
document.addEventListener('keydown', function(e){
  if (e.key === 'Escape') {
    var ud = document.getElementById('userDropdown');
    var nd = document.getElementById('notifDropdown');
    var nm = document.getElementById('navMenu');
    if (ud) ud.classList.remove('open');
    if (nd) nd.classList.remove('open');
    if (nm && nm.classList.contains('open')) toggleMobileMenu();
  }
});

// ── Page Loader ────────────────────────────────────────────
window.addEventListener('load', function(){
  var loader = document.getElementById('pageLoader');
  if (loader) {
    loader.style.opacity = '0';
    setTimeout(function(){ loader.style.display = 'none'; }, 400);
  }
});

// ── Back to Top ────────────────────────────────────────────
(function(){
  var btn = document.getElementById('backToTop');
  if (!btn) return;
  window.addEventListener('scroll', function(){
    btn.classList.toggle('visible', window.scrollY > 400);
  }, {passive:true});
  btn.addEventListener('click', function(){
    window.scrollTo({top:0, behavior:'smooth'});
  });
})();

// ── Utility: HTML escape ───────────────────────────────────
function escHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;');
}

// ── Cart count badge (shop) ────────────────────────────────
(function(){
  var cart = JSON.parse(sessionStorage.getItem('af_cart') || '[]');
  var count = cart.reduce(function(s,i){ return s + (i.qty||1); }, 0);
  if (count > 0) {
    var shopLinks = document.querySelectorAll('a[href*="shop.php"]');
    shopLinks.forEach(function(el){
      if (!el.querySelector('.cart-dot')) {
        var dot = document.createElement('span');
        dot.className = 'cart-dot';
        dot.textContent = count;
        dot.style.cssText = 'background:var(--teal-500);color:var(--navy-900);font-size:0.55rem;font-weight:700;padding:1px 5px;border-radius:99px;margin-left:3px;vertical-align:middle;';
        el.appendChild(dot);
      }
    });
  }
})();
</script>