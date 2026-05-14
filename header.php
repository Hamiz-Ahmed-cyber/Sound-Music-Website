<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sound<?php echo isset($page_title) ? ' — ' . $page_title : ''; ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
    /* ══════════════════════════════════════
       CSS VARIABLES — shared across all pages
    ══════════════════════════════════════ */
    :root {
    --bg-base:        #0a0a0c;
    --bg-card:        #111116;
    --bg-card-hover:  #16161f;
    --bg-nav:         rgba(10,10,12,0.94);
    --accent:         #1db954;
    --accent-dim:     #158a3e;
    --accent-glow:    rgba(29,185,84,0.12);
    --green:          #34c759;
    --blue: #f0f0f0;
    --red:            #e05c5c;
    --purple:         #a78bfa;
    --text-primary:   #f0ede8;
    --text-muted:     #7a7a8a;
    --text-sub:       #4a4a5a;
    --border:         rgba(255,255,255,0.07);
    --radius:         14px;
    --font-display:   'Playfair Display', serif;
    --font-body:      'DM Sans', sans-serif;
    --nav-height:     68px;
}

body {
    padding-bottom: 0px;
    transition: padding-bottom .3s;
}
body.player-active {
    padding-bottom: 80px;
}

    /* ── Reset & Base ── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
        background-color: var(--bg-base);
        color: var(--text-primary);
        font-family: var(--font-body);
        font-weight: 400;
        min-height: 100vh;
        overflow-x: hidden;
    }

    /* ══════════════════════════════════════
       NAVBAR
    ══════════════════════════════════════ */
    .Sound-nav {
        background: var(--bg-nav);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border-bottom: 1px solid var(--border);
        position: sticky; top: 0; z-index: 1000;
        height: var(--nav-height);
        transition: box-shadow .3s, border-color .3s;
    }
    .Sound-nav.scrolled {
        box-shadow: 0 8px 40px rgba(0,0,0,0.5);
        border-bottom-color: rgba(245,166,35,0.1);
    }

    .nav-inner {
        display: flex; align-items: center;
        justify-content: space-between;
        height: var(--nav-height);
        gap: 16px;
    }

    /* ── Logo ── */
    .nav-logo {
        font-family: var(--font-display);
        font-size: 1.65rem; font-weight: 900;
        color: var(--text-primary); text-decoration: none;
        display: flex; align-items: center; gap: 9px;
        flex-shrink: 0; transition: opacity .2s;
    }
    .nav-logo:hover { opacity: .88; color: var(--text-primary); }
    .nav-logo .logo-box {
        width: 36px; height: 36px;
        background: var(--accent); border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; color: #000;
        box-shadow: 0 4px 14px rgba(245,166,35,0.35);
        transition: transform .25s, box-shadow .25s;
    }
    .nav-logo:hover .logo-box {
        transform: rotate(-8deg) scale(1.08);
        box-shadow: 0 6px 20px rgba(245,166,35,0.5);
    }
    .nav-logo .dot { color: var(--accent); }

    /* ── Desktop nav links ── */
    .nav-links {
        display: flex; align-items: center;
        gap: 2px; list-style: none;
        flex: 1; justify-content: center;
    }
    .nav-links a {
        color: var(--text-muted); text-decoration: none;
        font-size: 0.875rem; font-weight: 500;
        padding: 7px 14px; border-radius: 9px;
        transition: all .2s; position: relative;
        display: flex; align-items: center; gap: 6px;
        white-space: nowrap;
    }
    .nav-links a::after {
        content: '';
        position: absolute; bottom: 3px; left: 50%;
        transform: translateX(-50%) scaleX(0);
        width: 16px; height: 2px;
        background: var(--accent); border-radius: 2px;
        transition: transform .25s ease;
    }
    .nav-links a:hover {
        color: var(--text-primary);
        background: rgba(255,255,255,0.05);
    }
    .nav-links a.active {
        color: var(--accent);
        background: rgba(245,166,35,0.08);
    }
    .nav-links a.active::after,
    .nav-links a:hover::after {
        transform: translateX(-50%) scaleX(1);
    }
    .nav-links a i { font-size: 0.82rem; }

    /* ── Search bar ── */
    .nav-search {
        position: relative;
        display: flex; align-items: center;
    }
    .nav-search-icon {
        position: absolute; left: 12px;
        color: var(--text-sub); font-size: 0.82rem;
        pointer-events: none; transition: color .2s;
        z-index: 1;
    }
    .nav-search input {
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--border);
        border-radius: 10px; color: var(--text-primary);
        font-family: var(--font-body); font-size: 0.84rem;
        padding: 8px 14px 8px 36px;
        width: 190px; outline: none;
        transition: all .3s;
    }
    .nav-search input::placeholder { color: var(--text-sub); }
    .nav-search input:focus {
        border-color: var(--accent);
        background: rgba(245,166,35,0.05);
        width: 220px;
        box-shadow: 0 0 0 3px rgba(245,166,35,0.08);
    }
    .nav-search input:focus ~ .nav-search-icon { color: var(--accent); }

    /* ── Right actions ── */
    .nav-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

    .btn-nav {
        font-family: var(--font-body); font-size: 0.84rem;
        font-weight: 500; padding: 7px 17px;
        border-radius: 9px; text-decoration: none;
        transition: all .22s; white-space: nowrap;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-nav-ghost {
        background: transparent;
        border: 1px solid var(--border); color: var(--text-muted);
    }
    .btn-nav-ghost:hover { border-color: var(--accent); color: var(--accent); }
    .btn-nav-fill {
        background: var(--accent); border: 1px solid var(--accent);
        color: #000; font-weight: 700;
        box-shadow: 0 4px 14px rgba(245,166,35,0.25);
    }
    .btn-nav-fill:hover {
        background: #ffb733; border-color: #ffb733; color: #000;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(245,166,35,0.4);
    }

    /* ── User dropdown ── */
    .user-menu-wrap { position: relative; }
    .user-menu-btn {
        display: flex; align-items: center; gap: 8px;
        background: rgba(245,166,35,0.08);
        border: 1px solid rgba(245,166,35,0.2);
        border-radius: 10px; padding: 5px 12px 5px 6px;
        cursor: pointer; transition: all .22s;
    }
    .user-menu-btn:hover { background: rgba(245,166,35,0.14); }
    .user-avatar {
        width: 30px; height: 30px; border-radius: 50%;
        background: var(--accent);
        display: flex; align-items: center; justify-content: center;
        font-family: var(--font-display); font-size: 0.9rem;
        font-weight: 900; color: #000; flex-shrink: 0;
    }
    .user-name-text {
        font-size: 0.84rem; font-weight: 500;
        color: var(--text-primary); max-width: 90px;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .user-chevron {
        color: var(--text-muted); font-size: 0.72rem;
        transition: transform .25s;
    }
    .user-menu-btn.open .user-chevron { transform: rotate(180deg); }

    .user-dropdown {
        display: none; position: absolute;
        top: calc(100% + 10px); right: 0;
        background: #14141c;
        border: 1px solid rgba(255,255,255,0.09);
        border-radius: 14px; padding: 7px;
        min-width: 210px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.55),
                    0 0 0 1px rgba(245,166,35,0.06);
        z-index: 999;
        animation: dropIn .2s ease;
    }
    .user-dropdown.open { display: block; }
    @keyframes dropIn {
        from { opacity:0; transform:translateY(-8px); }
        to   { opacity:1; transform:translateY(0); }
    }

    .dd-header {
        padding: 10px 12px 12px;
        border-bottom: 1px solid var(--border);
        margin-bottom: 5px;
    }
    .dd-header .dd-name {
        font-size: 0.88rem; font-weight: 600;
        color: var(--text-primary); margin-bottom: 2px;
    }
    .dd-header .dd-email {
        font-size: 0.72rem; color: var(--text-sub);
    }
    .dd-item {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 12px; border-radius: 9px;
        color: var(--text-muted); text-decoration: none;
        font-size: 0.84rem; transition: all .15s;
        cursor: pointer; border: none; background: none;
        width: 100%; text-align: left;
    }
    .dd-item:hover {
        background: rgba(255,255,255,0.05);
        color: var(--text-primary);
    }
    .dd-item .dd-icon {
        width: 28px; height: 28px; border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.82rem; flex-shrink: 0;
    }
    .dd-divider {
        height: 1px; background: var(--border);
        margin: 5px 0;
    }
    .dd-item.danger { color: #e05c5c; }
    .dd-item.danger:hover { background: rgba(224,92,92,0.08); color: #e05c5c; }

    /* ── Mobile hamburger ── */
    .nav-toggler {
        display: none; background: none;
        border: 1px solid var(--border); border-radius: 9px;
        color: var(--text-primary); padding: 7px 10px;
        cursor: pointer; font-size: 1.1rem;
        transition: all .2s; flex-shrink: 0;
    }
    .nav-toggler:hover {
        border-color: var(--accent); color: var(--accent);
    }
    .nav-toggler.open { border-color: var(--accent); color: var(--accent); }

    /* ── Mobile drawer ── */
    .mobile-drawer {
        display: none; position: fixed;
        top: var(--nav-height); left: 0; right: 0; bottom: 0;
        background: rgba(10,10,12,0.98);
        backdrop-filter: blur(20px);
        z-index: 999; padding: 28px 24px;
        overflow-y: auto;
        flex-direction: column; gap: 6px;
        border-top: 1px solid var(--border);
        animation: slideDown .25s ease;
    }
    .mobile-drawer.open { display: flex; }
    @keyframes slideDown {
        from { opacity:0; transform:translateY(-12px); }
        to   { opacity:1; transform:translateY(0); }
    }

    .mob-link {
        display: flex; align-items: center; gap: 12px;
        padding: 13px 16px; border-radius: 12px;
        color: var(--text-muted); text-decoration: none;
        font-size: 0.95rem; font-weight: 500;
        border: 1px solid transparent;
        transition: all .2s;
    }
    .mob-link:hover, .mob-link.active {
        background: rgba(245,166,35,0.07);
        border-color: rgba(245,166,35,0.15);
        color: var(--accent);
    }
    .mob-link .mob-icon {
        width: 36px; height: 36px; border-radius: 9px;
        background: rgba(255,255,255,0.05);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.9rem; flex-shrink: 0;
        transition: background .2s;
    }
    .mob-link:hover .mob-icon,
    .mob-link.active .mob-icon {
        background: rgba(245,166,35,0.12);
        color: var(--accent);
    }

    .mob-search {
        position: relative; margin-bottom: 8px;
    }
    .mob-search i {
        position: absolute; left: 14px; top: 50%;
        transform: translateY(-50%);
        color: var(--text-sub); pointer-events: none;
    }
    .mob-search input {
        width: 100%;
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--border); border-radius: 12px;
        color: var(--text-primary); font-family: var(--font-body);
        font-size: 0.9rem; padding: 12px 14px 12px 42px;
        outline: none; transition: border-color .2s;
    }
    .mob-search input:focus { border-color: var(--accent); }
    .mob-search input::placeholder { color: var(--text-sub); }

    .mob-auth {
        display: flex; gap: 10px; margin-top: 16px;
        padding-top: 16px; border-top: 1px solid var(--border);
    }
    .mob-auth a {
        flex: 1; text-align: center; padding: 12px;
        border-radius: 11px; text-decoration: none;
        font-family: var(--font-body); font-size: 0.88rem;
        font-weight: 600; transition: all .2s;
    }
    .mob-auth .mob-login {
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--border); color: var(--text-muted);
    }
    .mob-auth .mob-login:hover { border-color: var(--accent); color: var(--accent); }
    .mob-auth .mob-signup {
        background: var(--accent); color: #000;
    }
    .mob-auth .mob-signup:hover { background: #ffb733; }

    .mob-user-info {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 16px; border-radius: 13px;
        background: rgba(245,166,35,0.07);
        border: 1px solid rgba(245,166,35,0.15);
        margin-bottom: 8px;
    }
    .mob-user-info .mob-avatar {
        width: 42px; height: 42px; border-radius: 50%;
        background: var(--accent);
        display: flex; align-items: center; justify-content: center;
        font-family: var(--font-display); font-size: 1.1rem;
        font-weight: 900; color: #000; flex-shrink: 0;
    }

    /* ═══════════════════════════════════════
       SHARED COMPONENT STYLES
       (used by all pages that include header)
    ═══════════════════════════════════════ */

    /* Section headings */
    .section-label {
        font-family: var(--font-body); font-size: 0.72rem;
        font-weight: 600; letter-spacing: 0.14em;
        text-transform: uppercase; color: var(--accent);
        margin-bottom: 5px; display: block;
    }
    .section-title {
        font-family: var(--font-display);
        font-size: clamp(1.55rem, 3vw, 2.1rem);
        font-weight: 700; color: var(--text-primary); line-height: 1.2;
    }

    /* Media cards */
    .media-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius); overflow: hidden;
        transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
        height: 100%;
    }
    .media-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 60px rgba(0,0,0,0.55),
                    0 0 0 1px rgba(245,166,35,0.18);
        border-color: rgba(245,166,35,0.28);
        background: var(--bg-card-hover);
    }

    .card-thumb {
        position: relative; overflow: hidden;
        aspect-ratio: 16/9; background: #15151c;
    }
    .card-thumb img {
        width: 100%; height: 100%; object-fit: cover;
        transition: transform .55s ease;
    }
    .media-card:hover .card-thumb img { transform: scale(1.07); }

    .thumb-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(to top,
            rgba(10,10,12,0.9) 0%, transparent 55%);
        opacity: 0; transition: opacity .3s;
        display: flex; align-items: center; justify-content: center;
    }
    .media-card:hover .thumb-overlay { opacity: 1; }

    .card-badge {
        position: absolute; top: 10px; left: 10px;
        background: rgba(0,0,0,0.68);
        border: 1px solid var(--border);
        backdrop-filter: blur(8px);
        font-size: 0.68rem; font-weight: 600;
        letter-spacing: 0.08em; text-transform: uppercase;
        padding: 3px 10px; border-radius: 20px;
    }
    .card-badge.music { color: var(--accent); border-color: rgba(245,166,35,0.3); }
    .card-badge.video { color: #f0f0f0;       border-color: rgba(240,240,240,0.2); }

    .btn-play-circle {
        width: 52px; height: 52px;
        background: var(--accent); border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #000; font-size: 1.15rem; cursor: pointer;
        transition: transform .2s, background .2s, box-shadow .2s;
        box-shadow: 0 0 30px rgba(245,166,35,0.5);
        text-decoration: none; border: none;
        animation: pulsePlay 2.5s ease-in-out infinite;
    }
    @keyframes pulsePlay {
    0%,100% { box-shadow: 0 0 20px rgba(29,185,84,.4); }
    50%      { box-shadow: 0 0 40px rgba(29,185,84,.7); }
    }
    .btn-play-circle:hover {
        transform: scale(1.13); background: #ffb733; color: #000;
        animation: none;
        box-shadow: 0 0 40px rgba(245,166,35,.8);
    }

    .card-body-pad { padding: 15px 16px 16px; }
    .card-title-text {
        font-size: 0.95rem; font-weight: 600;
        color: var(--text-primary); line-height: 1.3; margin-bottom: 4px;
    }
    .card-artist-line {
        font-size: 0.79rem; color: var(--text-muted); margin-bottom: 8px;
        display: flex; align-items: center; gap: 5px;
    }
    .card-artist-line span { color: var(--accent); font-weight: 500; }
    .genre-tag {
        display: inline-block; font-size: 0.68rem; font-weight: 600;
        letter-spacing: 0.06em; text-transform: uppercase;
        color: var(--text-sub); background: rgba(255,255,255,0.04);
        border: 1px solid var(--border); padding: 2px 9px;
        border-radius: 20px; margin-bottom: 11px;
    }

    /* Star rating */
    .rating-row {
        display: flex; align-items: center;
        justify-content: space-between; margin-bottom: 11px;
    }
    .rating-info { display: flex; align-items: center; gap: 6px; }
    .stars-rendered { font-size: 0.88rem; letter-spacing: 1px; line-height: 1; }
    .rating-avg { font-size: 0.8rem; font-weight: 600; color: var(--text-primary); }
    .rating-count { font-size: 0.73rem; color: var(--text-sub); }

    .user-stars { display: flex; gap: 2px; }
    .user-stars .s {
        background: none; border: none; cursor: pointer;
        font-size: 1rem; color: var(--text-sub);
        padding: 2px 1px; transition: color .12s, transform .15s;
        line-height: 1;
    }
    .user-stars .s:hover,
    .user-stars .s.lit { color: var(--accent); transform: scale(1.2); }

    /* Card buttons */
    .card-btns { display: flex; gap: 8px; }
    .cbtn {
        flex: 1; font-family: var(--font-body); font-size: 0.79rem;
        font-weight: 500; padding: 8px 10px; border-radius: 9px;
        text-align: center; text-decoration: none;
        display: flex; align-items: center; justify-content: center;
        gap: 5px; transition: all .2s; cursor: pointer; border: 1px solid;
    }
    .cbtn-play {
        background: var(--accent); border-color: var(--accent);
        color: #000; font-weight: 700;
    }
    .cbtn-play:hover { background: #ffb733; border-color: #ffb733; color: #000; }
    .cbtn-details {
        background: rgba(255,255,255,0.04);
        border-color: var(--border); color: var(--text-muted);
    }
    .cbtn-details:hover {
        border-color: var(--accent); color: var(--accent);
        background: var(--accent-glow);
    }

    /* Page fade-up animation */
    .fade-up {
        opacity: 0; transform: translateY(22px);
        animation: fadeUp .5s ease forwards;
    }
    @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
    .fade-up:nth-child(1) { animation-delay:.04s; }
    .fade-up:nth-child(2) { animation-delay:.10s; }
    .fade-up:nth-child(3) { animation-delay:.16s; }
    .fade-up:nth-child(4) { animation-delay:.22s; }
    .fade-up:nth-child(5) { animation-delay:.28s; }

    /* Divider */
    .hr-dark { border-color: var(--border); margin: 0; }

    /* ── Responsive ── */
    @media (max-width: 991px) {
        .nav-links,
        .nav-search,
        .btn-nav,
        .user-menu-wrap { display: none !important; }
        .nav-toggler { display: block; }
    }
    @media (max-width: 576px) {
        .nav-logo span.logo-text { display: none; }
    }
/* ── Dropdown fix ── */
select {
    color-scheme: dark;
    color: var(--text-primary);
}
select option {
    background-color: #111116 !important;
    color: #f0ede8 !important;
}
select option:checked {
    background-color: #1db954 !important;
    color: #000 !important;
}

    </style>
</head>
<body>

<!-- ══════════════════════════════════════
     NAVBAR
══════════════════════════════════════ -->
<nav class="Sound-nav" id="mainNav">
    <div class="container">
        <div class="nav-inner">

            <!-- Logo -->
            <a href="/music_site/index.php" class="nav-logo">
                <div class="logo-box"><i class="bi bi-music-note-beamed"></i></div>
                <span class="logo-text">Sound</span>
            </a>

            <!-- Desktop links -->
            <ul class="nav-links">
                <?php
                $current = basename($_SERVER['PHP_SELF']);
                $navItems = [
                    ['index.php',         'bi-house',              'Home',    '/music_site/index.php'],
                    ['music.php',         'bi-music-note-beamed',  'Music',   '/music_site/user/music.php'],
                    ['videos.php',        'bi-camera-video',       'Videos',  '/music_site/user/videos.php'],
                    ['search.php',        'bi-search',             'Search',  '/music_site/user/search.php'],
                ];
                foreach ($navItems as [$file,$icon,$label,$href]):
                    $active = $current === $file ? 'active' : '';
                ?>
                <li>
                    <a href="<?= $href ?>" class="<?= $active ?>">
                        <i class="bi <?= $icon ?>"></i> <?= $label ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>

            <!-- Search -->
            <div class="nav-search d-none d-lg-flex">
                <i class="bi bi-search nav-search-icon"></i>
                <input type="text" placeholder="Search music, artists…"
                       id="navSearchInput"
                       onkeydown="if(event.key==='Enter'&&this.value.trim()){
                           window.location='/music_site/user/search.php?keyword='+encodeURIComponent(this.value.trim());
                       }">
            </div>

            <!-- Auth actions -->
            <div class="nav-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Logged in — user dropdown -->
                <div class="user-menu-wrap" id="userMenuWrap">
                    <button class="user-menu-btn" id="userMenuBtn" onclick="toggleUserMenu()">
                        <div class="user-avatar">
                            <?= strtoupper(substr($_SESSION['name'], 0, 1)) ?>
                        </div>
                        <span class="user-name-text">
                            <?= htmlspecialchars(explode(' ', $_SESSION['name'])[0]) ?>
                        </span>
                        <i class="bi bi-chevron-down user-chevron"></i>
                    </button>

                    <div class="user-dropdown" id="userDropdown">
                        <!-- Header -->
                        <div class="dd-header">
                            <div class="dd-name"><?= htmlspecialchars($_SESSION['name']) ?></div>
                            <div class="dd-email"><?= htmlspecialchars($_SESSION['email']) ?></div>
                        </div>

                        <?php
                        $dashHref = $_SESSION['role'] === 'admin'
                            ? '/music_site/admin/dashboard.php'
                            : '/music_site/user/dashboard.php';
                        $ddItems = [
                            [$dashHref,                         'bi-speedometer2',    'rgba(245,166,35,0.1)', 'var(--accent)',  'Dashboard',    false],
                            ['/music_site/user/edit_profile.php','bi-pencil-square',  'rgba(240,240,240,0.08)','#f0f0f0',       'Edit Profile', false],
                            ['/music_site/user/music.php',      'bi-music-note-beamed','rgba(52,199,89,0.1)', '#34c759',       'Music Library',false],
                            ['/music_site/user/videos.php',     'bi-camera-video',    'rgba(167,139,250,0.1)','#a78bfa',       'Videos',       false],
                            ['/music_site/user/search.php',     'bi-search',          'rgba(255,255,255,0.06)','var(--text-muted)','Search',  false],
                        ];
                        foreach ($ddItems as [$href,$icon,$bg,$color,$label,$danger]): ?>
                        <a href="<?= $href ?>" class="dd-item <?= $danger ? 'danger' : '' ?>">
                            <div class="dd-icon" style="background:<?= $bg ?>;color:<?= $color ?>;">
                                <i class="bi <?= $icon ?>"></i>
                            </div>
                            <?= $label ?>
                        </a>
                        <?php endforeach; ?>

                        <div class="dd-divider"></div>
                        <a href="/music_site/user/logout.php" class="dd-item danger">
                            <div class="dd-icon" style="background:rgba(224,92,92,0.1);color:#e05c5c;">
                                <i class="bi bi-box-arrow-right"></i>
                            </div>
                            Logout
                        </a>
                    </div>
                </div>

                <?php else: ?>
                <!-- Not logged in -->
                <a href="/music_site/user/login.php"    class="btn-nav btn-nav-ghost d-none d-md-inline-flex">Login</a>
                <a href="/music_site/user/register.php" class="btn-nav btn-nav-fill">Sign Up</a>
                <?php endif; ?>

                <!-- Hamburger -->
                <button class="nav-toggler" id="navToggler" onclick="toggleMobileMenu()">
                    <i class="bi bi-list" id="togglerIcon"></i>
                </button>
            </div>

        </div>
    </div>
</nav>

<!-- ══════════════════════════════════════
     MOBILE DRAWER
══════════════════════════════════════ -->
<div class="mobile-drawer" id="mobileDrawer">

    <!-- Mobile search -->
    <div class="mob-search">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Search music, artists…"
               onkeydown="if(event.key==='Enter'&&this.value.trim()){
                   window.location='/music_site/user/search.php?keyword='+encodeURIComponent(this.value.trim());
               }">
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- User info strip -->
    <div class="mob-user-info">
        <div class="mob-avatar">
            <?= strtoupper(substr($_SESSION['name'], 0, 1)) ?>
        </div>
        <div>
            <div style="font-size:.92rem;font-weight:600;color:var(--text-primary);">
                <?= htmlspecialchars($_SESSION['name']) ?>
            </div>
            <div style="font-size:.74rem;color:var(--text-sub);">
                <?= htmlspecialchars($_SESSION['email']) ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Nav links -->
    <?php
    $mobItems = [
        ['index.php',          'bi-house',             'Home',         '/music_site/index.php'],
        ['music.php',          'bi-music-note-beamed', 'Music Library','/music_site/user/music.php'],
        ['videos.php',         'bi-camera-video',      'Videos',       '/music_site/user/videos.php'],
        ['search.php',         'bi-search',            'Search',       '/music_site/user/search.php'],
    ];
    foreach ($mobItems as [$file,$icon,$label,$href]):
        $active = $current === $file ? 'active' : '';
    ?>
    <a href="<?= $href ?>" class="mob-link <?= $active ?>">
        <div class="mob-icon"><i class="bi <?= $icon ?>"></i></div>
        <?= $label ?>
    </a>
    <?php endforeach; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
    <a href="<?= $dashHref ?>"            class="mob-link"><div class="mob-icon"><i class="bi bi-speedometer2"></i></div>Dashboard</a>
    <a href="/music_site/user/edit_profile.php" class="mob-link"><div class="mob-icon"><i class="bi bi-pencil-square"></i></div>Edit Profile</a>
    <a href="/music_site/user/logout.php" class="mob-link" style="color:#e05c5c;">
        <div class="mob-icon" style="background:rgba(224,92,92,0.1);color:#e05c5c;">
            <i class="bi bi-box-arrow-right"></i>
        </div>Logout
    </a>
    <?php else: ?>
    <div class="mob-auth">
        <a href="/music_site/user/login.php"    class="mob-login">Login</a>
        <a href="/music_site/user/register.php" class="mob-signup">Sign Up</a>
    </div>
    <?php endif; ?>

</div>

<script>
/* ── Navbar scroll effect ── */
window.addEventListener('scroll', function () {
    document.getElementById('mainNav')
        .classList.toggle('scrolled', window.scrollY > 20);
});

/* ── User dropdown ── */
function toggleUserMenu() {
    const btn  = document.getElementById('userMenuBtn');
    const drop = document.getElementById('userDropdown');
    if (!btn || !drop) return;
    const isOpen = drop.classList.toggle('open');
    btn.classList.toggle('open', isOpen);
}
document.addEventListener('click', function (e) {
    const wrap = document.getElementById('userMenuWrap');
    if (wrap && !wrap.contains(e.target)) {
        document.getElementById('userDropdown')?.classList.remove('open');
        document.getElementById('userMenuBtn')?.classList.remove('open');
    }
});

/* ── Mobile menu ── */
function toggleMobileMenu() {
    const drawer = document.getElementById('mobileDrawer');
    const icon   = document.getElementById('togglerIcon');
    const btn    = document.getElementById('navToggler');
    const isOpen = drawer.classList.toggle('open');
    btn.classList.toggle('open', isOpen);
    icon.className = isOpen ? 'bi bi-x-lg' : 'bi bi-list';
    document.body.style.overflow = isOpen ? 'hidden' : '';
}
/* Close mobile menu on link click */
document.querySelectorAll('.mob-link, .mob-auth a').forEach(function (el) {
    el.addEventListener('click', function () {
        document.getElementById('mobileDrawer').classList.remove('open');
        document.getElementById('navToggler').classList.remove('open');
        document.getElementById('togglerIcon').className = 'bi bi-list';
        document.body.style.overflow = '';
    });
});
</script>
