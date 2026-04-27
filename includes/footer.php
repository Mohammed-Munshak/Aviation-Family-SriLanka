<?php
// ============================================================
// Aviation Family Sri Lanka — includes/footer.php
// Global footer included on every page
// ============================================================
$lang = $_SESSION['lang'] ?? 'en';

// Site settings (use cached if available)
$siteName    = isset($pdo) ? getSetting($pdo, 'site_name',    'Aviation Family') : 'Aviation Family';
$siteTagline = isset($pdo) ? getSetting($pdo, 'site_tagline', 'Your Community in the Skies') : 'Your Community in the Skies';
$siteEmail   = isset($pdo) ? getSetting($pdo, 'site_email',   'admin@aviationfamily.com') : 'admin@aviationfamily.com';
$currentYear = date('Y');

// Handle newsletter subscription (AJAX handled separately — this is fallback)
$newsletterMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_email'], $_POST['csrf_token'])) {
    if (verifyCsrf() && isset($pdo)) {
        $email = filter_var(trim($_POST['newsletter_email']), FILTER_VALIDATE_EMAIL);
        if ($email) {
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO newsletter_subscribers (email, is_confirmed) VALUES (?,1)");
                $stmt->execute([$email]);
                $newsletterMsg = 'success';
            } catch(PDOException $e) {
                $newsletterMsg = 'error';
            }
        } else {
            $newsletterMsg = 'invalid';
        }
    }
}
?>

</main><!-- /.main-content -->

<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer class="footer" role="contentinfo">

  <!-- Top Section -->
  <div class="footer__top">
    <div class="container">
      <div class="footer__grid">

        <!-- Brand Column -->
        <div class="footer__brand">
          <a href="<?= SITE_URL ?>/index.php" class="footer__logo" aria-label="Aviation Family Home">
            <img src="<?= SITE_URL ?>/assets/images/logo.png"
                 alt="Aviation Family Sri Lanka"
                 style="height:38px;width:auto;"
                 onerror="this.style.display='none'">
            <div>
              <div class="footer__logo-name"><?= e($siteName) ?></div>
              <span class="footer__logo-tag">Sri Lanka</span>
            </div>
          </a>

          <p class="footer__desc">
            <?= e($siteTagline) ?> — connecting aviation enthusiasts,
            spotters, pilots and professionals across Sri Lanka and beyond.
          </p>

          <!-- Social Links -->
          <div class="footer__social" aria-label="Social media links">
            <a href="#" class="social-btn social-btn--fb" aria-label="Facebook" target="_blank" rel="noopener">
              <i class="fa-brands fa-facebook-f"></i>
            </a>
            <a href="#" class="social-btn social-btn--tw" aria-label="X / Twitter" target="_blank" rel="noopener">
              <i class="fa-brands fa-x-twitter"></i>
            </a>
            <a href="#" class="social-btn social-btn--ig" aria-label="Instagram" target="_blank" rel="noopener">
              <i class="fa-brands fa-instagram"></i>
            </a>
            <a href="#" class="social-btn social-btn--yt" aria-label="YouTube" target="_blank" rel="noopener">
              <i class="fa-brands fa-youtube"></i>
            </a>
            <a href="#" class="social-btn social-btn--wa" aria-label="WhatsApp" target="_blank" rel="noopener">
              <i class="fa-brands fa-whatsapp"></i>
            </a>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="footer__col">
          <div class="footer__col-title">
            <i class="fa-solid fa-link" style="color:var(--teal-500);margin-right:6px;"></i>
            Quick Links
          </div>
          <nav class="footer__links" aria-label="Quick links">
            <a href="<?= SITE_URL ?>/index.php"    class="footer__link"><i class="fa-solid fa-chevron-right"></i><?= t('nav_home', $lang) ?></a>
            <a href="<?= SITE_URL ?>/articles.php" class="footer__link"><i class="fa-solid fa-chevron-right"></i><?= t('nav_news', $lang) ?></a>
            <a href="<?= SITE_URL ?>/gallery.php"  class="footer__link"><i class="fa-solid fa-chevron-right"></i><?= t('nav_gallery', $lang) ?></a>
            <a href="<?= SITE_URL ?>/jobs.php"     class="footer__link"><i class="fa-solid fa-chevron-right"></i><?= t('nav_jobs', $lang) ?></a>
            <a href="<?= SITE_URL ?>/shop.php"     class="footer__link"><i class="fa-solid fa-chevron-right"></i><?= t('nav_shop', $lang) ?></a>
            <a href="<?= SITE_URL ?>/events.php"   class="footer__link"><i class="fa-solid fa-chevron-right"></i><?= t('nav_events', $lang) ?></a>
            <a href="<?= SITE_URL ?>/quiz.php"     class="footer__link"><i class="fa-solid fa-chevron-right"></i><?= t('nav_quiz', $lang) ?></a>
            <a href="<?= SITE_URL ?>/forum.php"    class="footer__link"><i class="fa-solid fa-chevron-right"></i><?= t('nav_forum', $lang) ?></a>
          </nav>
        </div>

        <!-- Community -->
        <div class="footer__col">
          <div class="footer__col-title">
            <i class="fa-solid fa-users" style="color:var(--teal-500);margin-right:6px;"></i>
            Community
          </div>
          <nav class="footer__links" aria-label="Community links">
            <a href="<?= SITE_URL ?>/register.php"        class="footer__link"><i class="fa-solid fa-chevron-right"></i>Join Us</a>
            <a href="<?= SITE_URL ?>/login.php"           class="footer__link"><i class="fa-solid fa-chevron-right"></i>Login</a>
            <a href="<?= SITE_URL ?>/dashboard.php"       class="footer__link"><i class="fa-solid fa-chevron-right"></i>My Dashboard</a>
            <a href="<?= SITE_URL ?>/membership-card.php" class="footer__link"><i class="fa-solid fa-chevron-right"></i>Membership Card</a>
            <a href="<?= SITE_URL ?>/forum.php"           class="footer__link"><i class="fa-solid fa-chevron-right"></i>Forum</a>
            <a href="<?= SITE_URL ?>/contact.php"         class="footer__link"><i class="fa-solid fa-chevron-right"></i>Contact Us</a>
          </nav>

          <!-- App badges placeholder -->
          <div style="margin-top:var(--sp-5);">
            <div class="footer__col-title" style="margin-bottom:var(--sp-3);">
              <i class="fa-solid fa-mobile-screen" style="color:var(--teal-500);margin-right:6px;"></i>
              Mobile App
            </div>
            <div style="display:flex;flex-direction:column;gap:var(--sp-2);">
              <a href="#" style="display:inline-flex;align-items:center;gap:8px;background:var(--bg-secondary);border:1px solid var(--border-default);border-radius:var(--r-md);padding:8px 14px;font-size:0.75rem;color:var(--text-muted);text-decoration:none;transition:all var(--ease-fast);" onmouseover="this.style.borderColor='var(--border-hover)'" onmouseout="this.style.borderColor='var(--border-default)'">
                <i class="fa-brands fa-google-play" style="color:var(--green-500);font-size:1rem;"></i>
                <span>Google Play<br><strong style="color:var(--text-primary);font-size:0.8rem;">Coming Soon</strong></span>
              </a>
              <a href="#" style="display:inline-flex;align-items:center;gap:8px;background:var(--bg-secondary);border:1px solid var(--border-default);border-radius:var(--r-md);padding:8px 14px;font-size:0.75rem;color:var(--text-muted);text-decoration:none;transition:all var(--ease-fast);" onmouseover="this.style.borderColor='var(--border-hover)'" onmouseout="this.style.borderColor='var(--border-default)'">
                <i class="fa-brands fa-app-store-ios" style="color:var(--blue-300);font-size:1rem;"></i>
                <span>App Store<br><strong style="color:var(--text-primary);font-size:0.8rem;">Coming Soon</strong></span>
              </a>
            </div>
          </div>
        </div>

        <!-- Newsletter -->
        <div class="footer__col">
          <div class="footer__col-title">
            <i class="fa-solid fa-paper-plane" style="color:var(--teal-500);margin-right:6px;"></i>
            <?= t('newsletter_sub', $lang) ?>
          </div>

          <p class="footer__newsletter-desc">
            Get the latest aviation news, job alerts and event updates straight to your inbox.
          </p>

          <?php if($newsletterMsg === 'success'): ?>
          <div class="flash flash--success" style="margin-bottom:var(--sp-4);">
            <span class="flash__icon"><i class="fa-solid fa-check"></i></span>
            <span>Subscribed! Welcome aboard ✈️</span>
          </div>
          <?php elseif($newsletterMsg === 'invalid'): ?>
          <div class="flash flash--error" style="margin-bottom:var(--sp-4);">
            <span class="flash__icon"><i class="fa-solid fa-xmark"></i></span>
            <span>Please enter a valid email.</span>
          </div>
          <?php endif; ?>

          <form class="newsletter-form" id="newsletterForm" novalidate>
            <?= csrfField() ?>
            <input type="email"
                   name="newsletter_email"
                   id="newsletterEmail"
                   placeholder="<?= t('your_email', $lang) ?>"
                   required
                   autocomplete="email"
                   aria-label="Email address for newsletter">
            <button type="submit" aria-label="Subscribe to newsletter">
              <i class="fa-solid fa-paper-plane"></i>
            </button>
          </form>
          <p style="font-size:var(--text-xs);color:var(--text-muted);margin-top:var(--sp-2);">
            No spam. Unsubscribe anytime.
          </p>

          <!-- Contact info -->
          <div style="margin-top:var(--sp-6);display:flex;flex-direction:column;gap:var(--sp-3);">
            <a href="mailto:<?= e($siteEmail) ?>"
               class="footer__link"
               style="font-size:var(--text-xs);">
              <i class="fa-solid fa-envelope" style="color:var(--teal-500);width:14px;"></i>
              <?= e($siteEmail) ?>
            </a>
            <span class="footer__link" style="font-size:var(--text-xs);cursor:default;">
              <i class="fa-solid fa-location-dot" style="color:var(--teal-500);width:14px;"></i>
              Colombo, Sri Lanka 🇱🇰
            </span>
            <span class="footer__link" style="font-size:var(--text-xs);cursor:default;">
              <i class="fa-solid fa-plane" style="color:var(--teal-500);width:14px;"></i>
              ICAO: VCBI — Bandaranaike Int'l
            </span>
          </div>
        </div>

      </div><!-- /.footer__grid -->
    </div><!-- /.container -->
  </div><!-- /.footer__top -->

  <hr class="footer__divider">

  <!-- Bottom Bar -->
  <div class="footer__bottom">
    <div class="container">
      <div class="footer__bottom-inner">

        <div>
          <div class="footer__copyright">
            &copy; <?= $currentYear ?>
            <span><?= e($siteName) ?> Sri Lanka</span>.
            <?= t('footer_rights', $lang) ?>
          </div>
          <div class="footer__copyright" style="margin-top:4px;">
            Made with <span style="color:var(--red-400);">&#9829;</span>
            for the aviation community of Sri Lanka
          </div>
        </div>

        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:var(--sp-3);">
          <!-- Policy links -->
          <div class="footer__bottom-links">
            <a href="<?= SITE_URL ?>/privacy.php" class="footer__bottom-link">
              <?= t('privacy', $lang) ?>
            </a>
            <span style="color:var(--border-default);">·</span>
            <a href="<?= SITE_URL ?>/terms.php" class="footer__bottom-link">
              <?= t('terms', $lang) ?>
            </a>
            <span style="color:var(--border-default);">·</span>
            <a href="<?= SITE_URL ?>/contact.php" class="footer__bottom-link">
              <?= t('nav_contact', $lang) ?>
            </a>
          </div>

          <!-- Language switcher bottom -->
          <div class="footer__lang-row">
            <span class="footer__lang-label">Lang:</span>
            <?php foreach(['en'=>'English','si'=>'සිංහල','ta'=>'தமிழ்'] as $code => $label): ?>
            <button onclick="setLang('<?= $code ?>')"
                    style="background:none;border:none;cursor:pointer;font-size:var(--text-xs);color:<?= $lang === $code ? 'var(--teal-500)' : 'var(--text-muted)' ?>;font-weight:<?= $lang === $code ? '700' : '400' ?>;padding:0;transition:color var(--ease-fast);">
              <?= $label ?>
            </button>
            <?php if($code !== 'ta'): ?>
            <span style="color:var(--border-default);font-size:var(--text-xs);">·</span>
            <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>

      </div>
    </div>
  </div><!-- /.footer__bottom -->

</footer><!-- /.footer -->

<!-- ============================================================
     BACK TO TOP BUTTON
     ============================================================ -->
<button class="back-to-top" id="backToTop" aria-label="Back to top">
  <i class="fa-solid fa-chevron-up"></i>
</button>

<!-- ============================================================
     NEWSLETTER AJAX
     ============================================================ -->
<script>
(function(){
  var form = document.getElementById('newsletterForm');
  if (!form) return;

  form.addEventListener('submit', function(e){
    e.preventDefault();
    var email = document.getElementById('newsletterEmail').value.trim();
    var btn   = form.querySelector('button[type="submit"]');
    if (!email) return;

    btn.innerHTML = '<div class="spinner spinner--sm" style="border-top-color:var(--navy-900);"></div>';
    btn.disabled = true;

    fetch('<?= SITE_URL ?>/api/newsletter.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({
        email: email,
        csrf: '<?= csrfToken() ?>'
      })
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (data.success) {
        form.innerHTML = '<div class="flash flash--success" style="width:100%;">' +
          '<span class="flash__icon"><i class="fa-solid fa-check"></i></span>' +
          '<span>Subscribed! Welcome aboard ✈️</span></div>';
      } else {
        btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
        btn.disabled  = false;
        var err = form.querySelector('.nl-error');
        if (!err) {
          err = document.createElement('p');
          err.className = 'nl-error';
          err.style.cssText = 'color:var(--red-400);font-size:0.75rem;margin-top:0.5rem;';
          form.parentElement.appendChild(err);
        }
        err.textContent = data.message || 'Something went wrong. Please try again.';
      }
    })
    .catch(function(){
      btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
      btn.disabled  = false;
    });
  });
})();
</script>

</body>
</html>