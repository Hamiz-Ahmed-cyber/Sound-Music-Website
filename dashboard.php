<?php
$page_title = 'Dashboard';
$breadcrumb = [['Dashboard', null]];
require_once '../includes/db.php';


/* ── Counts ── */
function count_rows($conn, $table) {
    $r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM `$table`");
    return $r ? (int)mysqli_fetch_assoc($r)['c'] : 0;
}
$total_music    = count_rows($conn, 'music');
$total_videos   = count_rows($conn, 'videos');
$total_users    = count_rows($conn, 'users');
$total_reviews  = count_rows($conn, 'reviews');
$total_cats     = count_rows($conn, 'categories');

/* avg rating overall */
$avg_r = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(ROUND(AVG(rating),1),0) AS a FROM reviews"))['a'];

/* ── Recent music (5) ── */
$recent_music = mysqli_fetch_all(mysqli_query($conn,
    "SELECT m.*, c.name AS artist_name
     FROM music m
     LEFT JOIN categories c ON c.id=m.artist_id AND c.type='artist'
     ORDER BY m.created_at DESC LIMIT 5"), MYSQLI_ASSOC);

/* ── Recent videos (5) ── */
$recent_videos = mysqli_fetch_all(mysqli_query($conn,
    "SELECT v.*, c.name AS artist_name
     FROM videos v
     LEFT JOIN categories c ON c.id=v.artist_id AND c.type='artist'
     ORDER BY v.created_at DESC LIMIT 5"), MYSQLI_ASSOC);

/* ── Recent users (5) ── */
$recent_users = mysqli_fetch_all(mysqli_query($conn,
    "SELECT * FROM users ORDER BY created_at DESC LIMIT 5"), MYSQLI_ASSOC);

/* ── Recent reviews (5) ── */
$recent_reviews = mysqli_fetch_all(mysqli_query($conn,
    "SELECT r.*, u.name AS user_name FROM reviews r
     LEFT JOIN users u ON u.id=r.user_id
     ORDER BY r.created_at DESC LIMIT 5"), MYSQLI_ASSOC);
     require_once 'includes/header.php';
?>

<!-- ══ PAGE HEADER ══ -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Dashboard</h1>
        <p class="admin-page-sub">Welcome back, <?= htmlspecialchars(explode(' ',$_SESSION['name'])[0]) ?>! Here's your site overview.</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="music/music_add.php" class="btn-action btn-primary-admin">
            <i class="bi bi-plus-lg"></i> Add Music
        </a>
        <a href="videos/videos_add.php" class="btn-action btn-primary-admin"
           style="background:#f0f0f0;border-color:#f0f0f0;">
            <i class="bi bi-plus-lg"></i> Add Video
        </a>
    </div>
</div>

<!-- ══ STAT CARDS ══ -->
<div class="row g-3 mb-4">
<?php
$stats = [
    ['bi-music-note-beamed','var(--accent)',   'rgba(245,166,35,.12)',  $total_music,   'Total Tracks',     'linear-gradient(90deg,var(--accent),#ffb733)',   'Music Library'],
    ['bi-camera-video',     'var(--blue)',     'rgba(240,240,240,0.08)', $total_videos,  'Total Videos',     'linear-gradient(90deg,var(--blue),#a8d1ff)',     'Video Library'],
    ['bi-people',           'var(--green)',    'rgba(52,199,89,.12)',   $total_users,   'Registered Users', 'linear-gradient(90deg,var(--green),#6ee29a)',    'User Base'],
    ['bi-chat-square-text', 'var(--purple)',   'rgba(167,139,250,.12)', $total_reviews, 'Reviews',          'linear-gradient(90deg,var(--purple),#c4b5fd)',   'Community'],
    ['bi-tags',             '#f97316',         'rgba(249,115,22,.12)',  $total_cats,    'Categories',       'linear-gradient(90deg,#f97316,#fb923c)',         'Organization'],
    ['bi-star-fill',        '#fbbf24',         'rgba(251,191,36,.12)',  $avg_r,         'Avg Rating',       'linear-gradient(90deg,#fbbf24,#fde68a)',         'Quality'],
];
foreach ($stats as $i => [$icon,$color,$bg,$num,$label,$grad,$sub]):
?>
<div class="col-6 col-md-4 col-xl-2 fade-up" style="animation-delay:<?= $i*.05 ?>s;">
    <div class="stat-card" style="--card-color:<?= $color ?>;">
        <style>.stat-card:nth-child(<?= $i+1 ?>)::before{background:<?= $grad ?>;}</style>
        <div class="stat-icon" style="background:<?= $bg ?>;color:<?= $color ?>;">
            <i class="bi <?= $icon ?>"></i>
        </div>
        <div class="stat-num"><?= $num ?></div>
        <div class="stat-label"><?= $label ?></div>
        <div class="stat-trend" style="color:var(--text-sub);"><?= $sub ?></div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- ══ RECENT TABLES ══ -->
<div class="row g-4">

    <!-- Recent Music -->
    <div class="col-lg-6 fade-up">
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title">
                    <i class="bi bi-music-note-beamed"></i> Recent Music
                </div>
                <a href="music/music_index.php" class="btn-action btn-view" style="font-size:.72rem;padding:5px 10px;">
                    View All
                </a>
            </div>
            <?php if (empty($recent_music)): ?>
            <div style="padding:30px;text-align:center;color:var(--text-sub);font-size:.875rem;">
                No music added yet.
                <a href="music/music_add.php" style="color:var(--accent);display:block;margin-top:8px;">+ Add first track</a>
            </div>
            <?php else: ?>
            <table class="admin-table">
                <thead><tr>
                    <th>Track</th><th>Artist</th><th>Date</th><th></th>
                </tr></thead>
                <tbody>
                <?php foreach ($recent_music as $m):
                    $img = !empty($m['image'])
                        ? '/music_site/uploads/'.htmlspecialchars($m['image'])
                        : '';
                ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <?php if ($img): ?>
                            <img src="<?= $img ?>" class="tbl-thumb"
                                 onerror="this.style.display='none'">
                            <?php else: ?>
                            <div class="tbl-thumb-placeholder">
                                <i class="bi bi-music-note"></i>
                            </div>
                            <?php endif; ?>
                            <span class="primary"><?= htmlspecialchars($m['title']) ?></span>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($m['artist_name'] ?? '—') ?></td>
                    <td><?= date('M d', strtotime($m['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:5px;">
                            <a href="music/music_edit.php?id=<?= $m['id'] ?>" class="btn-action btn-edit" style="padding:4px 8px;"><i class="bi bi-pencil"></i></a>
                            <button onclick="confirmDelete('music/music_delete.php?id=<?= $m['id'] ?>','<?= addslashes($m['title']) ?>')"
                                class="btn-action btn-delete" style="padding:4px 8px;"><i class="bi bi-trash3"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Videos -->
    <div class="col-lg-6 fade-up" style="animation-delay:.08s;">
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title">
                    <i class="bi bi-camera-video" style="color:var(--blue);"></i> Recent Videos
                </div>
                <a href="videos/videos_index.php" class="btn-action btn-view" style="font-size:.72rem;padding:5px 10px;">
                    View All
                </a>
            </div>
            <?php if (empty($recent_videos)): ?>
            <div style="padding:30px;text-align:center;color:var(--text-sub);font-size:.875rem;">
                No videos added yet.
                <a href="videos/videos_add.php" style="color:var(--blue);display:block;margin-top:8px;">+ Add first video</a>
            </div>
            <?php else: ?>
            <table class="admin-table">
                <thead><tr>
                    <th>Video</th><th>Artist</th><th>Date</th><th></th>
                </tr></thead>
                <tbody>
                <?php foreach ($recent_videos as $v):
                    $img = !empty($v['image'])
                        ? '/music_site/uploads/'.htmlspecialchars($v['image'])
                        : '';
                ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <?php if ($img): ?>
                            <img src="<?= $img ?>" class="tbl-thumb"
                                 onerror="this.style.display='none'">
                            <?php else: ?>
                            <div class="tbl-thumb-placeholder" style="background:rgba(240,240,240,0.08);border-color:rgba(240,240,240,0.15);color:var(--blue);">
                                <i class="bi bi-camera-video"></i>
                            </div>
                            <?php endif; ?>
                            <span class="primary"><?= htmlspecialchars($v['title']) ?></span>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($v['artist_name'] ?? '—') ?></td>
                    <td><?= date('M d', strtotime($v['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:5px;">
                            <a href="videos/videos_edit.php?id=<?= $v['id'] ?>" class="btn-action btn-edit" style="padding:4px 8px;"><i class="bi bi-pencil"></i></a>
                            <button onclick="confirmDelete('videos/videos_delete.php?id=<?= $v['id'] ?>','<?= addslashes($v['title']) ?>')"
                                class="btn-action btn-delete" style="padding:4px 8px;"><i class="bi bi-trash3"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="col-lg-6 fade-up" style="animation-delay:.12s;">
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title">
                    <i class="bi bi-people" style="color:var(--green);"></i> Recent Users
                </div>
                <a href="users/users_index.php" class="btn-action btn-view" style="font-size:.72rem;padding:5px 10px;">
                    View All
                </a>
            </div>
            <?php if (empty($recent_users)): ?>
            <div style="padding:30px;text-align:center;color:var(--text-sub);font-size:.875rem;">No users yet.</div>
            <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Joined</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($recent_users as $u): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:9px;">
                            <div style="
                                width:32px;height:32px;border-radius:50%;
                                background:rgba(245,166,35,.12);border:1px solid rgba(245,166,35,.2);
                                display:flex;align-items:center;justify-content:center;
                                font-family:var(--font-display);font-size:.85rem;font-weight:900;color:var(--accent);
                            "><?= strtoupper(substr($u['name'],0,1)) ?></div>
                            <span class="primary"><?= htmlspecialchars($u['name']) ?></span>
                        </div>
                    </td>
                    <td style="font-size:.8rem;"><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="badge-pill <?= $u['role']==='admin'?'badge-admin':'badge-user' ?>">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td><?= date('M d', strtotime($u['created_at'])) ?></td>
                    <td>
                        <a href="users/users_edit.php?id=<?= $u['id'] ?>" class="btn-action btn-edit" style="padding:4px 8px;"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Reviews -->
    <div class="col-lg-6 fade-up" style="animation-delay:.16s;">
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title">
                    <i class="bi bi-chat-square-text" style="color:var(--purple);"></i> Recent Reviews
                </div>
                <a href="reviews/reviews_index.php" class="btn-action btn-view" style="font-size:.72rem;padding:5px 10px;">
                    View All
                </a>
            </div>
            <?php if (empty($recent_reviews)): ?>
            <div style="padding:30px;text-align:center;color:var(--text-sub);font-size:.875rem;">No reviews yet.</div>
            <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>User</th><th>Type</th><th>Rating</th><th>Date</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($recent_reviews as $r): ?>
                <tr>
                    <td class="primary"><?= htmlspecialchars($r['user_name'] ?? 'Anonymous') ?></td>
                    <td>
                        <span class="badge-pill <?= $r['media_type']==='music'?'badge-music':'badge-video' ?>">
                            <?= ucfirst($r['media_type']) ?>
                        </span>
                    </td>
                    <td style="color:var(--accent);">
                        <?= str_repeat('★',(int)$r['rating']) ?>
                        <span style="color:var(--text-sub);font-size:.75rem;">/5</span>
                    </td>
                    <td><?= date('M d', strtotime($r['created_at'])) ?></td>
                    <td>
                        <button onclick="confirmDelete('reviews/reviews_delete.php?id=<?= $r['id'] ?>','this review')"
                            class="btn-action btn-delete" style="padding:4px 8px;"><i class="bi bi-trash3"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

</div><!-- /row -->

<?php require_once '../includes/footer.php'; ?>
