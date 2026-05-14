<?php
$page_title = 'Video Details';
require_once '../includes/db.php';
require_once '../includes/header.php';

/* ── Validate GET id ── */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="container" style="padding:80px 20px;text-align:center;">
            <p style="color:var(--text-muted);">Invalid request. <a href="videos.php" style="color:var(--accent);">Go back</a></p>
          </div>';
    require_once '../includes/footer.php';
    exit;
}
$id = (int)$_GET['id'];

/* ── Fetch video with category joins ── */
$sql = "
    SELECT
        v.*,
        artist.name  AS artist_name,
        album.name   AS album_name,
        genre.name   AS genre_name,
        lang.name    AS language_name,
        yr.name      AS year_name
    FROM videos v
    LEFT JOIN categories artist ON artist.id = v.artist_id   AND artist.type = 'artist'
    LEFT JOIN categories album  ON album.id  = v.album_id    AND album.type  = 'album'
    LEFT JOIN categories genre  ON genre.id  = v.genre_id    AND genre.type  = 'genre'
    LEFT JOIN categories lang   ON lang.id   = v.language_id AND lang.type   = 'language'
    LEFT JOIN categories yr     ON yr.id     = v.year_id     AND yr.type     = 'year'
    WHERE v.id = $id
    LIMIT 1
";
$result = mysqli_query($conn, $sql);
if (!$result || mysqli_num_rows($result) === 0) {
    echo '<div class="container" style="padding:80px 20px;text-align:center;">
            <i class="bi bi-emoji-frown" style="font-size:3rem;color:var(--text-sub);display:block;margin-bottom:14px;"></i>
            <p style="color:var(--text-muted);margin-bottom:6px;">Video not found.</p>
            <a href="videos.php" style="color:var(--accent);font-size:0.9rem;">← Back to Videos</a>
          </div>';
    require_once '../includes/footer.php';
    exit;
}
$v          = mysqli_fetch_assoc($result);
$page_title = htmlspecialchars($v['title']);

/* ── Fetch reviews ── */
$rev_sql = "
    SELECT r.*, u.name AS user_name
    FROM reviews r
    LEFT JOIN users u ON u.id = r.user_id
    WHERE r.media_id = $id AND r.media_type = 'video'
    ORDER BY r.created_at DESC
";
$rev_result = mysqli_query($conn, $rev_sql);
$reviews    = mysqli_fetch_all($rev_result, MYSQLI_ASSOC);
$rev_count  = count($reviews);
$avg_rating = $rev_count > 0
    ? round(array_sum(array_column($reviews, 'rating')) / $rev_count, 1)
    : 0;

/* ── User's existing rating ── */
$my_rating  = 0;
$my_review  = '';
if (isset($_SESSION['user_id'])) {
    $uid   = (int)$_SESSION['user_id'];
    $my_q  = mysqli_query($conn,
        "SELECT rating, review FROM reviews
         WHERE user_id=$uid AND media_id=$id AND media_type='video' LIMIT 1");
    if ($my_q && mysqli_num_rows($my_q) > 0) {
        $row       = mysqli_fetch_assoc($my_q);
        $my_rating = (int)$row['rating'];
        $my_review = $row['review'] ?? '';
    }
}

/* ── Star HTML helper ── */
function starsHtml(float $avg): string {
    $full  = (int)floor($avg);
    $frac  = $avg - $full;
    $half  = ($frac >= 0.25 && $frac < 0.75) ? 1 : 0;
    if ($frac >= 0.75) { $full++; $half = 0; }
    $empty = max(0, 5 - $full - $half);
    return str_repeat('⭐', $full) . ($half ? '✨' : '') . str_repeat('☆', $empty);
}

$imgSrc   = !empty($v['image'])
    ? '/music_site/uploads/' . htmlspecialchars($v['image'])
    : '/music_site/assets/placeholder-video.jpg';
$videoSrc = !empty($v['video_file'])
    ? '/music_site/uploads/' . htmlspecialchars($v['video_file'])
    : '';

/* detect extension for correct MIME type */
$videoExt  = strtolower(pathinfo($v['video_file'] ?? '', PATHINFO_EXTENSION));
$mimeTypes = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg'];
$videoMime = $mimeTypes[$videoExt] ?? 'video/mp4';
?>

<!-- ══ BACK LINK ══ -->
<div class="container" style="padding-top:28px;">
    <a href="videos.php" style="
        color:var(--text-muted); text-decoration:none; font-size:0.85rem;
        display:inline-flex; align-items:center; gap:6px; transition:color .2s;
    " onmouseover="this.style.color='#f0f0f0'"
       onmouseout="this.style.color='var(--text-muted)'">
        <i class="bi bi-arrow-left"></i> Back to Videos
    </a>
</div>

<!-- ══ MAIN CONTENT ══ -->
<section style="padding:32px 0 0;">
    <div class="container">

        <!-- ══ VIDEO PLAYER — full width on top ══ -->
        <?php if ($videoSrc): ?>
        <div style="
            background:#000; border-radius:18px; overflow:hidden;
            border:1px solid var(--border);
            box-shadow:0 30px 80px rgba(0,0,0,0.7);
            margin-bottom:32px; position:relative;
        ">
            <video id="videoPlayer" controls preload="metadata"
                   poster="<?= $imgSrc ?>"
                   style="width:100%;display:block;max-height:520px;background:#000;">
                <source src="<?= $videoSrc ?>" type="<?= $videoMime ?>">
                Your browser does not support the video element.
            </video>

            <!-- Video badge overlay -->
            <div style="
                position:absolute; top:16px; left:16px;
                background:rgba(0,0,0,0.7); backdrop-filter:blur(10px);
                border:1px solid rgba(240,240,240,0.2); border-radius:20px;
                padding:4px 14px; font-size:0.7rem; font-weight:600;
                letter-spacing:0.08em; text-transform:uppercase; color:#f0f0f0;
                pointer-events:none;
            ">
                <i class="bi bi-camera-video me-1"></i>Video
            </div>
        </div>

        <?php else: ?>
        <!-- No video file -->
        <div style="
            background:var(--bg-card); border:1px dashed var(--border);
            border-radius:18px; padding:60px 20px; margin-bottom:32px;
            text-align:center;
        ">
            <i class="bi bi-camera-video-off" style="
                font-size:3rem; color:var(--text-sub);
                display:block; margin-bottom:14px;
            "></i>
            <p style="color:var(--text-muted); font-size:0.95rem; margin:0;">
                Video file not available for this entry.
            </p>
        </div>
        <?php endif; ?>

        <!-- ══ TWO COLUMN: INFO + REVIEWS ══ -->
        <div class="row g-4 align-items-start">

            <!-- ── LEFT: Thumbnail + Meta + Rating ── -->
            <div class="col-lg-4 col-md-5">
                <div style="position:sticky; top:90px;">

                    <!-- Thumbnail card -->
                    <div style="
                        border-radius:16px; overflow:hidden;
                        border:1px solid var(--border);
                        box-shadow:0 20px 50px rgba(0,0,0,0.5),
                                   0 0 0 1px rgba(126,184,247,0.06);
                        aspect-ratio:16/9; background:#15151c;
                    ">
                        <img src="<?= $imgSrc ?>"
                             alt="<?= htmlspecialchars($v['title']) ?>"
                             onerror="this.src='/music_site/assets/placeholder-video.jpg'"
                             style="width:100%;height:100%;object-fit:cover;">
                    </div>

                    <!-- ── Rating Box ── -->
                    <div style="
                        background:var(--bg-card); border:1px solid var(--border);
                        border-radius:14px; padding:20px; margin-top:18px;
                    ">
                        <p style="font-size:0.72rem;font-weight:600;letter-spacing:0.1em;
                                  text-transform:uppercase;color:var(--text-sub);margin-bottom:12px;">
                            Community Rating
                        </p>

                        <!-- Average -->
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                            <span style="
                                font-family:var(--font-display);font-size:2.8rem;
                                font-weight:900;color:var(--text-primary);line-height:1;
                            "><?= $avg_rating > 0 ? number_format($avg_rating,1) : '—' ?></span>
                            <div>
                                <div style="font-size:1.2rem;letter-spacing:2px;margin-bottom:3px;">
                                    <?= starsHtml($avg_rating) ?>
                                </div>
                                <p style="font-size:0.77rem;color:var(--text-muted);margin:0;">
                                    <?= $rev_count ?> review<?= $rev_count !== 1 ? 's' : '' ?>
                                </p>
                            </div>
                        </div>

                        <!-- User star input -->
                        <div style="border-top:1px solid var(--border);padding-top:14px;">
                            <p style="font-size:0.72rem;color:var(--text-muted);margin-bottom:8px;">
                                <?= $my_rating > 0 ? 'Your rating — click to change or remove:' : 'Rate this video:' ?>
                            </p>
                            <div class="user-stars"
                                 data-id="<?= $v['id'] ?>"
                                 data-type="video"
                                 data-rated="<?= $my_rating ?>"
                                 style="display:flex;gap:4px;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button class="s <?= $i <= $my_rating ? 'lit' : '' ?>"
                                        title="<?= $i ?> star<?= $i>1?'s':'' ?>"
                                        style="font-size:1.5rem;color:<?= $i <= $my_rating ? '#f0f0f0' : 'var(--text-sub)' ?>;">★</button>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <!-- ── Meta Info Box ── -->
                    <div style="
                        background:var(--bg-card); border:1px solid var(--border);
                        border-radius:14px; padding:20px; margin-top:14px;
                    ">
                        <p style="font-size:0.72rem;font-weight:600;letter-spacing:0.1em;
                                  text-transform:uppercase;color:var(--text-sub);margin-bottom:14px;">
                            Video Info
                        </p>
                        <?php
                        $meta = [
                            ['bi-person',    'Artist',   $v['artist_name']   ?? null],
                            ['bi-disc',      'Album',    $v['album_name']    ?? null],
                            ['bi-tag',       'Genre',    $v['genre_name']    ?? null],
                            ['bi-translate', 'Language', $v['language_name'] ?? null],
                            ['bi-calendar3', 'Year',     $v['year_name']     ?? null],
                        ];
                        foreach ($meta as [$icon, $label, $val]):
                            if (!$val) continue;
                        ?>
                        <div style="
                            display:flex; align-items:center; gap:10px;
                            padding:9px 0; border-bottom:1px solid var(--border);
                        ">
                            <i class="bi <?= $icon ?>" style="
                                width:28px; height:28px;
                                background:rgba(240,240,240,0.08);
                                border-radius:7px; display:flex;
                                align-items:center; justify-content:center;
                                color:#f0f0f0; font-size:0.8rem; flex-shrink:0;
                            "></i>
                            <div>
                                <p style="font-size:0.68rem;color:var(--text-sub);margin:0;
                                          letter-spacing:0.06em;text-transform:uppercase;">
                                    <?= $label ?>
                                </p>
                                <p style="font-size:0.87rem;color:var(--text-primary);
                                          margin:0;font-weight:500;">
                                    <?= htmlspecialchars($val) ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <!-- Added date -->
                        <div style="padding-top:9px;display:flex;align-items:center;gap:10px;">
                            <i class="bi bi-clock-history" style="
                                width:28px; height:28px;
                                background:rgba(240,240,240,0.08); border-radius:7px;
                                display:flex; align-items:center; justify-content:center;
                                color:#f0f0f0; font-size:0.8rem; flex-shrink:0;
                            "></i>
                            <div>
                                <p style="font-size:0.68rem;color:var(--text-sub);margin:0;
                                          letter-spacing:0.06em;text-transform:uppercase;">Added</p>
                                <p style="font-size:0.87rem;color:var(--text-primary);
                                          margin:0;font-weight:500;">
                                    <?= date('M d, Y', strtotime($v['created_at'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>

                </div><!-- /sticky -->
            </div><!-- /col-left -->

            <!-- ── RIGHT: Title + Description + Reviews ── -->
            <div class="col-lg-8 col-md-7">

                <!-- Title block -->
                <div style="margin-bottom:28px;">
                    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
                        <?php if (!empty($v['genre_name'])): ?>
                        <span style="
                            font-size:0.7rem;font-weight:600;letter-spacing:0.1em;
                            text-transform:uppercase;color:#f0f0f0;
                            background:rgba(240,240,240,0.08);
                            border:1px solid rgba(240,240,240,0.18);
                            border-radius:20px;padding:3px 12px;
                        "><?= htmlspecialchars($v['genre_name']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($v['language_name'])): ?>
                        <span style="
                            font-size:0.7rem;font-weight:600;letter-spacing:0.1em;
                            text-transform:uppercase;color:var(--text-muted);
                            background:rgba(255,255,255,0.05);
                            border:1px solid var(--border);
                            border-radius:20px;padding:3px 12px;
                        "><?= htmlspecialchars($v['language_name']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($v['year_name'])): ?>
                        <span style="
                            font-size:0.7rem;font-weight:600;letter-spacing:0.1em;
                            text-transform:uppercase;color:var(--text-sub);
                            background:rgba(255,255,255,0.03);
                            border:1px solid var(--border);
                            border-radius:20px;padding:3px 12px;
                        "><?= htmlspecialchars($v['year_name']) ?></span>
                        <?php endif; ?>
                    </div>

                    <h1 style="
                        font-family:var(--font-display);
                        font-size:clamp(1.8rem,4vw,2.8rem);
                        font-weight:900;color:var(--text-primary);
                        line-height:1.15;margin-bottom:10px;
                    "><?= htmlspecialchars($v['title']) ?></h1>

                    <p style="font-size:1rem;color:var(--text-muted);">
                        by <span style="color:#f0f0f0;font-weight:600;">
                            <?= htmlspecialchars($v['artist_name'] ?? 'Unknown Artist') ?>
                        </span>
                        <?php if (!empty($v['album_name'])): ?>
                        &nbsp;·&nbsp;
                        <span style="color:var(--text-muted);">
                            <i class="bi bi-disc" style="font-size:0.85rem;"></i>
                            <?= htmlspecialchars($v['album_name']) ?>
                        </span>
                        <?php endif; ?>
                        <?php if (!empty($v['year_name'])): ?>
                        &nbsp;·&nbsp;
                        <span style="color:var(--text-sub);">
                            <?= htmlspecialchars($v['year_name']) ?>
                        </span>
                        <?php endif; ?>
                    </p>
                </div>

                <!-- ── DESCRIPTION ── -->
                <?php if (!empty($v['description'])): ?>
                <div style="
                    background:var(--bg-card); border:1px solid var(--border);
                    border-radius:16px; padding:24px; margin-bottom:28px;
                ">
                    <p style="font-size:0.72rem;font-weight:600;letter-spacing:0.1em;
                              text-transform:uppercase;color:var(--text-sub);margin-bottom:12px;">
                        <i class="bi bi-file-text me-2" style="color:#f0f0f0;"></i>About this Video
                    </p>
                    <p style="color:var(--text-muted);font-size:0.92rem;
                              line-height:1.75;margin:0;">
                        <?= nl2br(htmlspecialchars($v['description'])) ?>
                    </p>
                </div>
                <?php endif; ?>

                <!-- ══ REVIEWS SECTION ══ -->
                <div id="reviews">
                    <div style="
                        display:flex;align-items:center;justify-content:space-between;
                        margin-bottom:20px;flex-wrap:wrap;gap:10px;
                    ">
                        <div>
                            <p style="font-size:0.72rem;font-weight:600;letter-spacing:0.1em;
                                      text-transform:uppercase;color:var(--text-sub);margin-bottom:4px;">
                                <i class="bi bi-chat-square-text me-2" style="color:#f0f0f0;"></i>Reviews
                            </p>
                            <h3 style="font-family:var(--font-display);font-size:1.4rem;
                                       font-weight:700;color:var(--text-primary);margin:0;">
                                <?= $rev_count ?> Review<?= $rev_count !== 1 ? 's' : '' ?>
                            </h3>
                        </div>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <button onclick="document.getElementById('reviewForm').scrollIntoView({behavior:'smooth'})"
                                style="
                            background:#f0f0f0;border:none;color:#000;
                            font-family:var(--font-body);font-size:0.84rem;font-weight:600;
                            padding:9px 20px;border-radius:10px;cursor:pointer;
                            transition:background .2s;display:flex;align-items:center;gap:6px;
                        " onmouseover="this.style.background='#a8d1ff'"
                           onmouseout="this.style.background='#f0f0f0'">
                            <i class="bi bi-plus-lg"></i> Write a Review
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Reviews list -->
                    <?php if (empty($reviews)): ?>
                    <div style="
                        text-align:center;padding:40px 20px;
                        border:1px dashed var(--border);border-radius:14px;
                        margin-bottom:28px;
                    ">
                        <i class="bi bi-chat-dots" style="
                            font-size:2rem;color:var(--text-sub);
                            display:block;margin-bottom:10px;
                        "></i>
                        <p style="color:var(--text-muted);margin:0;font-size:0.9rem;">
                            No reviews yet. Be the first to review this video!
                        </p>
                    </div>

                    <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:28px;">
                        <?php foreach ($reviews as $r):
                            $rStars = (int)$r['rating'];
                        ?>
                        <div style="
                            background:var(--bg-card);border:1px solid var(--border);
                            border-radius:14px;padding:20px;transition:border-color .2s;
                        " onmouseover="this.style.borderColor='rgba(240,240,240,0.15)'"
                           onmouseout="this.style.borderColor='var(--border)'">

                            <!-- Review header -->
                            <div style="
                                display:flex;align-items:flex-start;
                                justify-content:space-between;
                                margin-bottom:10px;gap:10px;
                            ">
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <!-- Avatar -->
                                    <div style="
                                        width:38px;height:38px;border-radius:50%;
                                        background:rgba(126,184,247,0.12);
                                        border:1px solid rgba(240,240,240,0.15);
                                        display:flex;align-items:center;justify-content:center;
                                        font-family:var(--font-display);font-size:1rem;
                                        font-weight:700;color:#f0f0f0;flex-shrink:0;
                                    ">
                                        <?= strtoupper(substr($r['user_name'] ?? 'A', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p style="font-size:0.88rem;font-weight:600;
                                                  color:var(--text-primary);margin:0;">
                                            <?= htmlspecialchars($r['user_name'] ?? 'Anonymous') ?>
                                        </p>
                                        <p style="font-size:0.73rem;color:var(--text-sub);margin:0;">
                                            <?= date('M d, Y', strtotime($r['created_at'])) ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Stars -->
                                <div style="text-align:right;flex-shrink:0;">
                                    <span style="font-size:0.9rem;letter-spacing:1px;">
                                        <?= str_repeat('⭐', $rStars) . str_repeat('☆', 5 - $rStars) ?>
                                    </span>
                                    <p style="font-size:0.73rem;color:var(--text-sub);margin:0;">
                                        <?= $rStars ?>/5
                                    </p>
                                </div>
                            </div>

                            <!-- Review text -->
                            <?php if (!empty($r['review'])): ?>
                            <p style="
                                color:var(--text-muted);font-size:0.875rem;line-height:1.7;
                                margin:0;padding-top:10px;border-top:1px solid var(--border);
                            "><?= nl2br(htmlspecialchars($r['review'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- ══ REVIEW FORM ══ -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <div id="reviewForm" style="
                        background:var(--bg-card);border:1px solid var(--border);
                        border-radius:16px;padding:26px;
                    ">
                        <p style="font-size:0.72rem;font-weight:600;letter-spacing:0.1em;
                                  text-transform:uppercase;color:var(--text-sub);margin-bottom:16px;">
                            <i class="bi bi-pencil-square me-2" style="color:#f0f0f0;"></i>
                            <?= $my_rating > 0 ? 'Update Your Review' : 'Write Your Review' ?>
                        </p>

                        <form action="../includes/submit_review.php" method="POST">
                            <input type="hidden" name="media_id"   value="<?= $v['id'] ?>">
                            <input type="hidden" name="media_type" value="video">
                            <input type="hidden" name="redirect"
                                   value="video_details.php?id=<?= $v['id'] ?>">

                            <!-- Star picker -->
                            <div style="margin-bottom:18px;">
                                <label style="font-size:0.8rem;color:var(--text-muted);
                                              display:block;margin-bottom:8px;">Your Rating</label>
                                <div style="display:flex;gap:6px;" id="formStars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <button type="button"
                                            class="form-star"
                                            data-val="<?= $i ?>"
                                            style="
                                        background:none;border:none;cursor:pointer;
                                        font-size:1.8rem;
                                        color:<?= $i <= $my_rating ? '#f0f0f0' : 'var(--text-sub)' ?>;
                                        padding:2px;transition:color .15s,transform .15s;line-height:1;
                                    ">★</button>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="ratingInput"
                                       value="<?= $my_rating ?>">
                            </div>

                            <!-- Textarea -->
                            <div style="margin-bottom:18px;">
                                <label style="font-size:0.8rem;color:var(--text-muted);
                                              display:block;margin-bottom:8px;">
                                    Your Review
                                    <span style="color:var(--text-sub);">(optional)</span>
                                </label>
                                <textarea name="review" rows="4"
                                          placeholder="Share your thoughts about this video…"
                                          style="
                                    width:100%;background:rgba(255,255,255,0.04);
                                    border:1px solid var(--border);border-radius:10px;
                                    color:var(--text-primary);font-family:var(--font-body);
                                    font-size:0.88rem;padding:12px 14px;
                                    outline:none;resize:vertical;transition:border-color .2s;
                                " onfocus="this.style.borderColor='#f0f0f0'"
                                   onblur="this.style.borderColor='var(--border)'"
                                ><?= htmlspecialchars($my_review) ?></textarea>
                            </div>

                            <button type="submit" style="
                                background:#f0f0f0;border:none;color:#000;
                                font-family:var(--font-body);font-size:0.88rem;
                                font-weight:700;padding:11px 28px;border-radius:10px;
                                cursor:pointer;transition:background .2s;
                                display:flex;align-items:center;gap:7px;
                            " onmouseover="this.style.background='#a8d1ff'"
                               onmouseout="this.style.background='#f0f0f0'">
                                <i class="bi bi-send"></i>
                                <?= $my_rating > 0 ? 'Update Review' : 'Submit Review' ?>
                            </button>
                        </form>

                        <script>
                        (function(){
                            const formStars   = document.querySelectorAll('.form-star');
                            const ratingInput = document.getElementById('ratingInput');
                            let selected      = parseInt(ratingInput.value) || 0;

                            function highlight(n) {
                                formStars.forEach((s, i) => {
                                    s.style.color     = i < n ? '#f0f0f0' : 'var(--text-sub)';
                                    s.style.transform = i < n ? 'scale(1.15)' : 'scale(1)';
                                });
                            }
                            highlight(selected);

                            formStars.forEach(function(star, idx) {
                                star.addEventListener('mouseenter', () => highlight(idx + 1));
                                star.addEventListener('mouseleave', () => highlight(selected));
                                star.addEventListener('click', function() {
                                    selected = (selected === idx + 1) ? 0 : idx + 1;
                                    ratingInput.value = selected;
                                    highlight(selected);
                                });
                            });
                        })();
                        </script>
                    </div>

                    <?php else: ?>
                    <!-- Not logged in -->
                    <div style="
                        background:var(--bg-card);border:1px solid var(--border);
                        border-radius:14px;padding:24px;text-align:center;
                    ">
                        <i class="bi bi-lock" style="
                            font-size:1.8rem;color:var(--text-sub);
                            display:block;margin-bottom:10px;
                        "></i>
                        <p style="color:var(--text-muted);margin-bottom:14px;font-size:0.9rem;">
                            Login to write a review or rate this video.
                        </p>
                        <a href="/music_site/user/login.php" style="
                            background:#f0f0f0;color:#000;font-family:var(--font-body);
                            font-weight:600;font-size:0.85rem;padding:9px 24px;
                            border-radius:10px;text-decoration:none;transition:background .2s;
                            display:inline-block;
                        " onmouseover="this.style.background='#a8d1ff'"
                           onmouseout="this.style.background='#f0f0f0'">
                            Login to Review
                        </a>
                    </div>
                    <?php endif; ?>

                </div><!-- /reviews -->
            </div><!-- /col-right -->
        </div><!-- /row -->
    </div><!-- /container -->
</section>

<div style="margin-bottom:70px;"></div>

<?php require_once '../includes/footer.php'; ?>
