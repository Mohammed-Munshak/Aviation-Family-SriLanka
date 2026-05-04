<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<style>
    /* --- NAVIGATION RESET & ALIGNMENT --- */
    .main-header {
        height: 70px;
        background-color: #002147; /* Royal Navy */
        width: 100%;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }

    .nav-container {
        width: 95%;
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .brand-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
    }

    .logo-img {
        height: 45px;
        width: auto;
        background: white;
        border-radius: 50%;
        padding: 2px;
    }

    .brand-text {
        color: white;
        font-size: 1.2rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* MENU WRAPPER */
    .nav-menu {
        display: flex;
        align-items: center;
        gap: 25px;
    }

    /* LINKS & BUTTONS: FORCED UNIFORMITY */
    .nav-link, .btn-nav {
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        height: 38px;
        transition: 0.3s ease;
    }

    .nav-link {
        color: rgba(255, 255, 255, 0.8);
        padding: 0 5px;
    }

    .nav-link:hover {
        color: #FFC107;
    }

    /* BUTTONS */
    .btn-nav {
        padding: 0 18px;
        border-radius: 4px;
        text-transform: uppercase;
    }

    .btn-login, .btn-profile {
        background-color: #FFC107; /* Gold */
        color: #002147 !important;
    }

    .btn-dashboard {
        background-color: #ef4444; /* Admin Red */
        color: white !important;
    }

    .btn-logout {
        border: 1px solid #ef4444;
        color: #ef4444 !important;
        margin-left: 5px;
    }

    .btn-nav:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        opacity: 0.9;
    }

    /* MOBILE TOGGLE */
    .mobile-toggle {
        display: none;
        flex-direction: column;
        gap: 5px;
        cursor: pointer;
    }

    .mobile-toggle span {
        width: 25px;
        height: 3px;
        background: white;
        border-radius: 2px;
    }

    /* RESPONSIVE BREAKPOINT */
    @media (max-width: 1024px) {
        .mobile-toggle { display: flex; }
        
        .nav-menu {
            position: absolute;
            top: 70px;
            left: 0;
            width: 100%;
            background: #002147;
            flex-direction: column;
            gap: 0;
            padding: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .nav-menu.active {
            max-height: 500px;
            padding-bottom: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .nav-link, .btn-nav {
            width: 90%;
            justify-content: center;
            margin: 10px 0;
        }
    }
</style>

<header class="main-header">
    <div class="nav-container">
        <a href="index.php" class="brand-logo">
            <img src="assets/images/logo.png" alt="Logo" class="logo-img">
            <span class="brand-text">Aviation Family SL</span>
        </a>

        <div class="mobile-toggle" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <nav class="nav-menu" id="navMenu">
            <a href="index.php" class="nav-link">HOME</a>
            <a href="news.php" class="nav-link">NEWS</a>
            <a href="store.php" class="nav-link">STORE</a>
            <a href="vacancies.php" class="nav-link">CAREERS</a>
            <a href="spotting.php" class="nav-link">PHOTOGRAPHY</a>
            <a href="contact.php" class="nav-link">CONTACT</a>

            <?php if (isset($_SESSION['user_id'])): ?>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <!-- Admin: show Dashboard only (no Profile) -->
                    <a href="admin/dashboard.php" class="btn-nav btn-dashboard">DASHBOARD</a>
                <?php else: ?>
                    <!-- Regular user: show Profile only (no Dashboard) -->
                    <a href="profile.php" class="btn-nav btn-profile">PROFILE</a>
                <?php endif; ?>

                <a href="logout.php" class="btn-nav btn-logout">LOGOUT</a>

            <?php else: ?>
                <!-- Guest: show Login only -->
                <a href="login.php" class="btn-nav btn-login">LOGIN</a>
            <?php endif; ?>

        </nav>
    </div>
</header>

<script>
    document.getElementById('hamburger').onclick = function() {
        document.getElementById('navMenu').classList.toggle('active');
    };
</script>