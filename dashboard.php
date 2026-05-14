<?php
session_start();

/* ── Session guard — redirect if not logged in ── */
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

/* ── Redirect admin away from user dashboard ── */
if ($_SESSION['role'] === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit;
}

$page_title = 'My Dashboard';
require_once '../includes/db.php';
require_once '../includes/header.php';

$user_id = (int)$_SESSION['user_id'];

/* ── Fetch full user info ── */
$user_res = mysqli_query($conn,
    "SELECT * FROM users WHERE id = $user_id LIMIT 1");
$user = mysqli_fetch_assoc($user_res);

/* ── Fetch user's reviews with media title ── */
$reviews_res = mysqli_query($conn,
    "SELECT
        r.*,
        CASE r.media_type
            WHEN 'music' THEN m.title
            WHEN 'video' THEN v.title
        END AS media_title,
        CASE r.media_type
            WHEN 'music' THEN m.image
            WHEN 'video' THEN v.image
        END AS media_image
     FROM reviews r
     LEFT JOIN music  m ON m.id = r.media_id AND r.media_type = 'music'
     LEFT JOIN videos v ON v.id = r.media_id AND r.media_type = 'video'
     WHERE r.user_id = $user_id
     ORDER BY r.created_at DESC"
);
$reviews     = mysqli_fetch_all($reviews_res, MYSQLI_ASSOC);
$rev_count   = count($reviews);

/* ── Count music and videos ── */
$music_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS c FROM music"))['c'];
$video_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS c FROM videos"))['c'];

/* ── Average rating given by user ── */
$avg_res  = mysqli_query($conn,
    "SELECT COALESCE(AVG(rating),0) AS avg_r FROM reviews WHERE user_id = $user_id");
$avg_given = round((float)mysqli_fetch_assoc($avg_res)['avg_r'], 1);
?>

<style>
    /* ── Dashboard layout ── */
    .dash-wrap {
        padding: 44px 0 80px;
        background: radial-gradient(ellipse 80% 50% at 50% -5%,
            rgba(245,166,35,0.07) 0%, transparent 65%);
    }

    /* ── Welcome banner ── */
    .welcome-banner {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 30px 32px;
        display: flex;
        align-items: center;
        gap: 22px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
    }
    .welcome-banner::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--accent), #ffb733, transparent);
    }
    .avatar-circle {
        width: 68px; height: 68px;
        border-radius: 50%;
        background: rgba(245,166,35,0.12);
        border: 2px solid rgba(245,166,35,0.3);
        display: flex; align-items: center; justify-content: center;
        font-family: var(--font-display);
        font-size: 1.8rem; font-weight: 900;
        color: var(--accent); flex-shrink: 0;
    }
    .welcome-text h2 {
        font-family: var(--font-display);
        font-size: 1.5rem; font-weight: 700;
        color: var(--text-primary); margin-bottom: 4px;
    }
    .welcome-text p {
        font-size: 0.875rem; color: var(--text-muted); margin: 0;
    }
    .welcome-text p span { color: var(--accent); font-weight: 500; }

    /* ── Stat cards ── */
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 14px; padding: 22px 20px;
        transition: transform .25s, border-color .25s, box-shadow .25s;
        height: 100%;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        border-color: rgba(245,166,35,0.2);
        box-shadow: 0 16px 40px rgba(0,0,0,0.3);
    }
    .stat-icon {
        width: 42px; height: 42px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; margin-bottom: 14px;
    }
    .stat-num {
        font-family: var(--font-display);
        font-size: 2rem; font-weight: 900;
        color: var(--text-primary); line-height: 1;
        margin-bottom: 5px;
    }
    .stat-label {
        font-size: 0.78rem; font-weight: 600;
        letter-spacing: 0.06em; text-transform: uppercase;
        color: var(--text-muted);
    }

    /* ── Section boxes ── */
    .dash-box {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px; padding: 24px;
        margin-bottom: 24px;
    }
    .dash-box-title {
        font-size: 0.72rem; font-weight: 600;
        letter-spacing: 0.1em; text-transform: uppercase;
        color: var(--text-sub); margin-bottom: 18px;
        display: flex; align-items: center; gap: 8px;
    }
    .dash-box-title i { color: var(--accent); }

    /* ── Review item ── */
    .review-item {
        display: flex; gap: 14px; align-items: flex-start;
        padding: 14px 0;
        border-bottom: 1px solid var(--border);
        transition: background .2s;
    }
    .review-item:last-child { border-bottom: none; padding-bottom: 0; }
    .review-item:first-child { padding-top: 0; }

    .review-thumb {
        width: 50px; height: 50px; border-radius: 10px;
        object-fit: cover; flex-shrink: 0;
        background: #1a1a22;
        border: 1px solid var(--border);
    }
    .review-thumb-placeholder {
        width: 50px; height: 50px; border-radius: 10px;
        background: rgba(245,166,35,0.08);
        border: 1px solid rgba(245,166,35,0.15);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .review-title {
        font-size: 0.9rem; font-weight: 600;
        color: var(--text-primary); margin-bottom: 3px;
    }
    .review-meta {
        font-size: 0.75rem; color: var(--text-sub); margin-bottom: 5px;
    }
    .review-stars { font-size: 0.82rem; letter-spacing: 1px; }
    .review-text {
        font-size: 0.8rem; color: var(--text-muted);
        line-height: 1.5; margin-top: 5px;
        display: -webkit-box; -webkit-line-clamp: 2;
        -webkit-box-orient: vertical; overflow: hidden;
    }

    /* ── Quick links ── */
    .quick-link {
        display: flex; align-items: center; gap: 13px;
        padding: 13px 16px; border-radius: 12px;
        border: 1px solid var(--border);
        text-decoration: none; margin-bottom: 10px;
        background: rgba(255,255,255,0.02);
        transition: all .2s;
    }
    .quick-link:last-child { margin-bottom: 0; }
    .quick-link:hover {
        border-color: rgba(245,166,35,0.25);
        background: rgba(245,166,35,0.05);
        transform: translateX(4px);
    }
    .quick-link .ql-icon {
        width: 36px; height: 36px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.95rem; flex-shrink: 0;
    }
    .quick-link .ql-text p {
        font-size: 0.88rem; font-weight: 600;
        color: var(--text-primary); margin: 0 0 2px;
    }
    .quick-link .ql-text span {
        font-size: 0.75rem; color: var(--text-muted);
    }
    .quick-link .ql-arrow {
        margin-left: auto; color: var(--text-sub);
        font-size: 0.8rem; transition: transform .2s;
    }
    .quick-link:hover .ql-arrow { transform: translateX(3px); color: var(--accent); }

    /* ── Profile info ── */
    .profile-row {
        display: flex; align-items: center; gap: 10px;
        padding: 11px 0; border-bottom: 1px solid var(--border);
    }
    .profile-row:last-child { border-bottom: none; }
    .profile-row .pr-icon {
        width: 30px; height: 30px; border-radius: 8px;
        background: rgba(245,166,35,0.08);
        display: flex; align-items: center; justify-content: center;
        color: var(--accent); font-size: 0.82rem; flex-shrink: 0;
    }
    .profile-row .pr-label {
        font-size: 0.7rem; color: var(--text-sub);
        text-transform: uppercase; letter-spacing: 0.05em;
    }
    .profile-row .pr-value {
        font-size: 0.875rem; color: var(--text-primary); font-weight: 500;
    }

    /* badge */
    .role-badge {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 0.7rem; font-weight: 700;
        letter-spacing: 0.08em; text-transform: uppercase;
        padding: 3px 12px; border-radius: 20px;
    }
    .role-badge.user {
        background: rgba(245,166,35,0.12);
        border: 1px solid rgba(245,166,35,0.25);
        color: var(--accent);
    }
</style>

<div class="dash-wrap">
    <div class="container">

        <!-- ══ WELCOME BANNER ══ -->
        <div class="welcome-banner fade-up">
            <div class="avatar-circle">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div class="welcome-text" style="flex:1;">
                <h2>Welcome back, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>!</h2>
                <p>
                    Logged in as <span><?= htmlspecialchars($user['email']) ?></span>
                    &nbsp;·&nbsp;
                    <span class="role-badge user">
                        <i class="bi bi-person-check"></i> Member
                    </span>
                </p>
            </div>
            <a href="logout.php" style="
                background: rgba(224,92,92,0.1);
                border: 1px solid rgba(224,92,92,0.2);
                color: #e05c5c;
                font-family: var(--font-body); font-size: 0.82rem;
                font-weight: 600; padding: 8px 18px;
                border-radius: 9px; text-decoration: none;
                transition: all .2s; flex-shrink: 0;
                display: flex; align-items: center; gap: 6px;
            " onmouseover="this.style.background='rgba(224,92,92,0.2)'"
               onmouseout="this.style.background='rgba(224,92,92,0.1)'">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>

        <!-- ══ STAT CARDS ══ -->
        <div class="row g-3 mb-4">
            <?php
            $stats = [
                [
                    'icon'  => 'bi-chat-square-heart',
                    'color' => 'rgba(245,166,35,0.12)',
                    'icolor'=> 'var(--accent)',
                    'num'   => $rev_count,
                    'label' => 'My Reviews',
                ],
                [
                    'icon'  => 'bi-star-fill',
                    'color' => 'rgba(255,183,0,0.10)',
                    'icolor'=> '#ffb700',
                    'num'   => $avg_given > 0 ? number_format($avg_given,1) : '—',
                    'label' => 'Avg Rating Given',
                ],
                [
                    'icon'  => 'bi-music-note-beamed',
                    'color' => 'rgba(52,199,89,0.10)',
                    'icolor'=> '#34c759',
                    'num'   => $music_count,
                    'label' => 'Songs Available',
                ],
                [
                    'icon'  => 'bi-camera-video',
                    'color' => 'rgba(126,184,247,0.12)',
                    'icolor'=> '#f0f0f0',
                    'num'   => $video_count,
                    'label' => 'Videos Available',
                ],
            ];
            foreach ($stats as $s): ?>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"
                         style="background:<?= $s['color'] ?>;color:<?= $s['icolor'] ?>;">
                        <i class="bi <?= $s['icon'] ?>"></i>
                    </div>
                    <div class="stat-num"><?= $s['num'] ?></div>
                    <div class="stat-label"><?= $s['label'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ══ MAIN GRID: reviews + sidebar ══ -->
        <div class="row g-4">

            <!-- ── LEFT: My Reviews ── -->
            <div class="col-lg-8">
                <div class="dash-box">
                    <p class="dash-box-title">
                        <i class="bi bi-chat-square-text"></i>
                        My Reviews
                        <span style="
                            margin-left:auto;
                            background:rgba(245,166,35,0.12);
                            border:1px solid rgba(245,166,35,0.2);
                            border-radius:20px; padding:2px 10px;
                            font-size:0.7rem; color:var(--accent);
                        "><?= $rev_count ?></span>
                    </p>

                    <?php if (empty($reviews)): ?>
                    <div style="text-align:center;padding:40px 20px;">
                        <i class="bi bi-chat-dots" style="
                            font-size:2.5rem;color:var(--text-sub);
                            display:block;margin-bottom:12px;
                        "></i>
                        <p style="color:var(--text-muted);margin-bottom:14px;font-size:0.9rem;">
                            You haven't reviewed anything yet.
                        </p>
                        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                            <a href="music.php" style="
                                background:var(--accent);color:#000;
                                font-family:var(--font-body);font-size:0.84rem;
                                font-weight:600;padding:9px 20px;border-radius:10px;
                                text-decoration:none;display:flex;align-items:center;gap:6px;
                            "><i class="bi bi-music-note-beamed"></i> Browse Music</a>
                            <a href="videos.php" style="
                                background:rgba(255,255,255,0.06);color:var(--text-primary);
                                border:1px solid var(--border);
                                font-family:var(--font-body);font-size:0.84rem;
                                font-weight:500;padding:9px 20px;border-radius:10px;
                                text-decoration:none;display:flex;align-items:center;gap:6px;
                            "><i class="bi bi-camera-video"></i> Browse Videos</a>
                        </div>
                    </div>

                    <?php else: ?>
                    <?php foreach ($reviews as $r):
                        $imgPath = !empty($r['media_image'])
                            ? '/music_site/uploads/' . htmlspecialchars($r['media_image'])
                            : '';
                        $detailUrl = $r['media_type'] === 'music'
                            ? 'music_details.php?id='  . $r['media_id']
                            : 'video_details.php?id=' . $r['media_id'];
                        $stars = (int)$r['rating'];
                    ?>
                    <div class="review-item">
                        <!-- Thumbnail -->
                        <?php if ($imgPath): ?>
                        <img src="<?= $imgPath ?>"
                             alt="<?= htmlspecialchars($r['media_title'] ?? '') ?>"
                             class="review-thumb"
                             onerror="this.style.display='none'">
                        <?php else: ?>
                        <div class="review-thumb-placeholder">
                            <i class="bi bi-<?= $r['media_type']==='music' ? 'music-note-beamed' : 'camera-video' ?>"
                               style="color:var(--accent);font-size:1.1rem;"></i>
                        </div>
                        <?php endif; ?>

                        <!-- Info -->
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:flex-start;
                                        justify-content:space-between;gap:8px;">
                                <div>
                                    <p class="review-title">
                                        <a href="<?= $detailUrl ?>" style="
                                            color:var(--text-primary);text-decoration:none;
                                            transition:color .2s;
                                        " onmouseover="this.style.color='var(--accent)'"
                                           onmouseout="this.style.color='var(--text-primary)'">
                                            <?= htmlspecialchars($r['media_title'] ?? 'Untitled') ?>
                                        </a>
                                    </p>
                                    <p class="review-meta">
                                        <span style="
                                            background:<?= $r['media_type']==='music'
                                                ? 'rgba(245,166,35,0.1)' : 'rgba(240,240,240,0.08)' ?>;
                                            border:1px solid <?= $r['media_type']==='music'
                                                ? 'rgba(245,166,35,0.2)' : 'rgba(240,240,240,0.15)' ?>;
                                            border-radius:20px;padding:1px 8px;
                                            font-size:0.68rem;font-weight:600;
                                            color:<?= $r['media_type']==='music'
                                                ? 'var(--accent)' : '#f0f0f0' ?>;
                                            text-transform:uppercase;letter-spacing:0.06em;
                                        "><?= $r['media_type'] ?></span>
                                        &nbsp;
                                        <?= date('M d, Y', strtotime($r['created_at'])) ?>
                                    </p>
                                </div>
                                <div style="text-align:right;flex-shrink:0;">
                                    <div class="review-stars">
                                        <?= str_repeat('⭐', $stars) . str_repeat('☆', 5-$stars) ?>
                                    </div>
                                    <p style="font-size:0.7rem;color:var(--text-sub);margin:2px 0 0;">
                                        <?= $stars ?>/5
                                    </p>
                                </div>
                            </div>
                            <?php if (!empty($r['review'])): ?>
                            <p class="review-text"><?= htmlspecialchars($r['review']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>

            <!-- ── RIGHT: Profile + Quick Links ── -->
            <div class="col-lg-4">

                <!-- Profile info -->
                <div class="dash-box">
                    <p class="dash-box-title">
                        <i class="bi bi-person-circle"></i> My Profile
                    </p>

                    <?php
                    $profile_fields = [
                        ['bi-person',    'Name',    $user['name']],
                        ['bi-envelope',  'Email',   $user['email']],
                        ['bi-telephone', 'Phone',   $user['phone']    ?? '—'],
                        ['bi-geo-alt',   'Address', $user['address']  ?? '—'],
                        ['bi-calendar3', 'Joined',  date('M d, Y', strtotime($user['created_at']))],
                    ];
                    foreach ($profile_fields as [$icon,$label,$val]):
                    ?>
                    <div class="profile-row">
                        <div class="pr-icon"><i class="bi <?= $icon ?>"></i></div>
                        <div>
                            <p class="pr-label"><?= $label ?></p>
                            <p class="pr-value"><?= htmlspecialchars($val) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div style="margin-top:16px;">
                        <a href="edit_profile.php" style="
                            width:100%;display:flex;align-items:center;justify-content:center;
                            gap:7px;background:rgba(255,255,255,0.04);
                            border:1px solid var(--border);border-radius:11px;
                            color:var(--text-muted);font-family:var(--font-body);
                            font-size:0.85rem;font-weight:500;padding:10px;
                            text-decoration:none;transition:all .2s;
                        " onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
                           onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)'">
                            <i class="bi bi-pencil-square"></i> Edit Profile
                        </a>
                    </div>
                </div>

                <!-- Quick links -->
                <div class="dash-box">
                    <p class="dash-box-title">
                        <i class="bi bi-grid"></i> Quick Links
                    </p>

                    <?php
                    $links = [
                        [
                            'href'   => 'music.php',
                            'icon'   => 'bi-music-note-beamed',
                            'bg'     => 'rgba(245,166,35,0.1)',
                            'color'  => 'var(--accent)',
                            'title'  => 'Music Library',
                            'sub'    => $music_count . ' tracks available',
                        ],
                        [
                            'href'   => 'videos.php',
                            'icon'   => 'bi-camera-video',
                            'bg'     => 'rgba(240,240,240,0.08)',
                            'color'  => '#f0f0f0',
                            'title'  => 'Video Library',
                            'sub'    => $video_count . ' videos available',
                        ],
                        [
                            'href'   => 'search.php',
                            'icon'   => 'bi-search',
                            'bg'     => 'rgba(52,199,89,0.1)',
                            'color'  => '#34c759',
                            'title'  => 'Search Music',
                            'sub'    => 'Filter by artist, genre & more',
                        ],
                        [
                            'href'   => '../index.php',
                            'icon'   => 'bi-house',
                            'bg'     => 'rgba(255,255,255,0.06)',
                            'color'  => 'var(--text-muted)',
                            'title'  => 'Homepage',
                            'sub'    => 'Back to Sound home',
                        ],
                    ];
                    foreach ($links as $l): ?>
                    <a href="<?= $l['href'] ?>" class="quick-link">
                        <div class="ql-icon"
                             style="background:<?= $l['bg'] ?>;color:<?= $l['color'] ?>;">
                            <i class="bi <?= $l['icon'] ?>"></i>
                        </div>
                        <div class="ql-text">
                            <p><?= $l['title'] ?></p>
                            <span><?= $l['sub'] ?></span>
                        </div>
                        <i class="bi bi-chevron-right ql-arrow"></i>
                    </a>
                    <?php endforeach; ?>

                    <!-- Logout -->
                    <a href="logout.php" class="quick-link" style="margin-top:10px;
                        border-color:rgba(224,92,92,0.2);background:rgba(224,92,92,0.05);"
                       onmouseover="this.style.borderColor='rgba(224,92,92,0.4)';this.style.background='rgba(224,92,92,0.1)'"
                       onmouseout="this.style.borderColor='rgba(224,92,92,0.2)';this.style.background='rgba(224,92,92,0.05)'">
                        <div class="ql-icon"
                             style="background:rgba(224,92,92,0.1);color:#e05c5c;">
                            <i class="bi bi-box-arrow-right"></i>
                        </div>
                        <div class="ql-text">
                            <p style="color:#e05c5c;">Logout</p>
                            <span>End your session</span>
                        </div>
                        <i class="bi bi-chevron-right ql-arrow"></i>
                    </a>
                </div>

            </div><!-- /col-right -->
        </div><!-- /row -->
    </div><!-- /container -->
</div><!-- /dash-wrap -->

<?php require_once '../includes/footer.php'; ?>
