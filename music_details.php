<?php
$page_title = 'Music Details';
require_once '../includes/db.php';
require_once '../includes/header.php';

/* ── Validate GET id ── */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="container" style="padding:80px 20px;text-align:center;">
            <p style="color:var(--text-muted);">Invalid request. <a href="music.php" style="color:var(--accent);">Go back</a></p>
          </div>';
    require_once '../includes/footer.php';
    exit;
}
$id = (int)$_GET['id'];

/* ── Fetch music with category joins ── */
$sql = "
    SELECT
        m.*,
        artist.name  AS artist_name,
        album.name   AS album_name,
        genre.name   AS genre_name,
        lang.name    AS language_name,
        yr.name      AS year_name
    FROM music m
    LEFT JOIN categories artist ON artist.id = m.artist_id  AND artist.type = 'artist'
    LEFT JOIN categories album  ON album.id  = m.album_id   AND album.type  = 'album'
    LEFT JOIN categories genre  ON genre.id  = m.genre_id   AND genre.type  = 'genre'
    LEFT JOIN categories lang   ON lang.id   = m.language_id AND lang.type  = 'language'
    LEFT JOIN categories yr     ON yr.id     = m.year_id    AND yr.type     = 'year'
    WHERE m.id = $id
    LIMIT 1
";
$result = mysqli_query($conn, $sql);
if (!$result || mysqli_num_rows($result) === 0) {
    echo '<div class="container" style="padding:80px 20px;text-align:center;">
            <i class="bi bi-emoji-frown" style="font-size:3rem;color:var(--text-sub);display:block;margin-bottom:14px;"></i>
            <p style="color:var(--text-muted);margin-bottom:6px;">Track not found.</p>
            <a href="music.php" style="color:var(--accent);font-size:0.9rem;">← Back to Music</a>
          </div>';
    require_once '../includes/footer.php';
    exit;
}
$m = mysqli_fetch_assoc($result);
$page_title = htmlspecialchars($m['title']);

/* ── Fetch reviews for this track ── */
$rev_sql = "
    SELECT r.*, u.name AS user_name
    FROM reviews r
    LEFT JOIN users u ON u.id = r.user_id
    WHERE r.media_id = $id AND r.media_type = 'music'
    ORDER BY r.created_at DESC
";
$rev_result = mysqli_query($conn, $rev_sql);
$reviews    = mysqli_fetch_all($rev_result, MYSQLI_ASSOC);
$rev_count  = count($reviews);
$avg_rating = $rev_count > 0
    ? round(array_sum(array_column($reviews, 'rating')) / $rev_count, 1)
    : 0;

/* ── User's existing rating ── */
$my_rating = 0;
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $my_q = mysqli_query($conn,
        "SELECT rating FROM reviews
         WHERE user_id=$uid AND media_id=$id AND media_type='music' LIMIT 1");
    if ($my_q && mysqli_num_rows($my_q) > 0) {
        $my_rating = (int)mysqli_fetch_assoc($my_q)['rating'];
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

$imgSrc   = !empty($m['image'])
    ? '/music_site/uploads/' . htmlspecialchars($m['image'])
    : '/music_site/assets/placeholder-music.jpg';
$audioSrc = !empty($m['music_file'])
    ? '/music_site/uploads/' . htmlspecialchars($m['music_file'])
    : '';
?>

<!-- ══ BACK LINK ══ -->
<div class="container" style="padding-top:28px;">
    <a href="music.php" style="
        color:var(--text-muted); text-decoration:none; font-size:0.85rem;
        display:inline-flex; align-items:center; gap:6px;
        transition:color .2s;
    " onmouseover="this.style.color='var(--accent)'"
       onmouseout="this.style.color='var(--text-muted)'">
        <i class="bi bi-arrow-left"></i> Back to Music
    </a>
</div>

<!-- ══ HERO SECTION ══ -->
<section style="padding: 36px 0 0;">
    <div class="container">
        <div class="row g-4 align-items-start">

            <!-- ── LEFT: Cover Art ── -->
            <div class="col-lg-4 col-md-5">
                <div style="position:sticky; top:90px;">
                    <!-- Cover image -->
                    <div style="
                        border-radius:18px; overflow:hidden;
                        border:1px solid var(--border);
                        box-shadow: 0 30px 80px rgba(0,0,0,0.6),
                                    0 0 0 1px rgba(245,166,35,0.08);
                        aspect-ratio:1/1; background:#15151c;
                        position:relative;
                    ">
                        <img src="<?= $imgSrc ?>"
                             alt="<?= htmlspecialchars($m['title']) ?>"
                             onerror="this.src='/music_site/assets/placeholder-music.jpg'"
                             style="width:100%;height:100%;object-fit:cover;">

                        <!-- Music badge -->
                        <div style="
                            position:absolute; bottom:14px; left:14px;
                            background:rgba(0,0,0,0.7); backdrop-filter:blur(10px);
                            border:1px solid rgba(245,166,35,0.3);
                            border-radius:20px; padding:4px 14px;
                            font-size:0.7rem; font-weight:600;
                            letter-spacing:0.08em; text-transform:uppercase;
                            color:var(--accent);
                        ">
                            <i class="bi bi-music-note-beamed me-1"></i>Music
                        </div>
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

                        <!-- Average display -->
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                            <span style="font-family:var(--font-display);font-size:2.8rem;
                                         font-weight:900;color:var(--text-primary);line-height:1;">
                                <?= $avg_rating > 0 ? number_format($avg_rating,1) : '—' ?>
                            </span>
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
                                <?= $my_rating > 0 ? 'Your rating — click to change or remove:' : 'Rate this track:' ?>
                            </p>
                            <div class="user-stars"
                                 data-id="<?= $m['id'] ?>"
                                 data-type="music"
                                 data-rated="<?= $my_rating ?>"
                                 style="display:flex;gap:4px;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button class="s <?= $i <= $my_rating ? 'lit' : '' ?>"
                                        title="<?= $i ?> star<?= $i>1?'s':'' ?>"
                                        style="font-size:1.5rem;">★</button>
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
                            Track Info
                        </p>
                        <?php
                        $meta = [
                            ['bi-person',       'Artist',   $m['artist_name']   ?? null],
                            ['bi-disc',         'Album',    $m['album_name']    ?? null],
                            ['bi-tag',          'Genre',    $m['genre_name']    ?? null],
                            ['bi-translate',    'Language', $m['language_name'] ?? null],
                            ['bi-calendar3',    'Year',     $m['year_name']     ?? null],
                        ];
                        foreach ($meta as [$icon, $label, $val]):
                            if (!$val) continue;
                        ?>
                        <div style="
                            display:flex; align-items:center; gap:10px;
                            padding:9px 0; border-bottom:1px solid var(--border);
                        ">
                            <i class="bi <?= $icon ?>" style="
                                width:28px;height:28px;background:rgba(245,166,35,0.1);
                                border-radius:7px;display:flex;align-items:center;
                                justify-content:center;color:var(--accent);font-size:0.8rem;
                                flex-shrink:0;
                            "></i>
                            <div>
                                <p style="font-size:0.68rem;color:var(--text-sub);margin:0;
                                          letter-spacing:0.06em;text-transform:uppercase;"><?= $label ?></p>
                                <p style="font-size:0.87rem;color:var(--text-primary);margin:0;font-weight:500;">
                                    <?= htmlspecialchars($val) ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <!-- Added date -->
                        <div style="padding-top:9px;display:flex;align-items:center;gap:10px;">
                            <i class="bi bi-clock-history" style="
                                width:28px;height:28px;background:rgba(245,166,35,0.1);
                                border-radius:7px;display:flex;align-items:center;
                                justify-content:center;color:var(--accent);font-size:0.8rem;
                                flex-shrink:0;
                            "></i>
                            <div>
                                <p style="font-size:0.68rem;color:var(--text-sub);margin:0;
                                          letter-spacing:0.06em;text-transform:uppercase;">Added</p>
                                <p style="font-size:0.87rem;color:var(--text-primary);margin:0;font-weight:500;">
                                    <?= date('M d, Y', strtotime($m['created_at'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>

                </div><!-- /sticky -->
            </div><!-- /col-left -->

            <!-- ── RIGHT: Details + Player ── -->
            <div class="col-lg-8 col-md-7">

                <!-- Title block -->
                <div style="margin-bottom:28px;">
                    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
                        <?php if (!empty($m['genre_name'])): ?>
                        <span style="
                            font-size:0.7rem;font-weight:600;letter-spacing:0.1em;
                            text-transform:uppercase;color:var(--accent);
                            background:rgba(245,166,35,0.1);border:1px solid rgba(245,166,35,0.25);
                            border-radius:20px;padding:3px 12px;
                        "><?= htmlspecialchars($m['genre_name']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($m['language_name'])): ?>
                        <span style="
                            font-size:0.7rem;font-weight:600;letter-spacing:0.1em;
                            text-transform:uppercase;color:var(--text-muted);
                            background:rgba(255,255,255,0.05);border:1px solid var(--border);
                            border-radius:20px;padding:3px 12px;
                        "><?= htmlspecialchars($m['language_name']) ?></span>
                        <?php endif; ?>
                    </div>

                    <h1 style="
                        font-family:var(--font-display);
                        font-size:clamp(1.8rem,4vw,2.8rem);
                        font-weight:900;color:var(--text-primary);
                        line-height:1.15;margin-bottom:10px;
                    "><?= htmlspecialchars($m['title']) ?></h1>

                    <p style="font-size:1rem;color:var(--text-muted);">
                        by <span style="color:var(--accent);font-weight:600;">
                            <?= htmlspecialchars($m['artist_name'] ?? 'Unknown Artist') ?>
                        </span>
                        <?php if (!empty($m['album_name'])): ?>
                        &nbsp;·&nbsp;
                        <span style="color:var(--text-muted);">
                            <i class="bi bi-disc" style="font-size:0.85rem;"></i>
                            <?= htmlspecialchars($m['album_name']) ?>
                        </span>
                        <?php endif; ?>
                        <?php if (!empty($m['year_name'])): ?>
                        &nbsp;·&nbsp;
                        <span style="color:var(--text-sub);"><?= htmlspecialchars($m['year_name']) ?></span>
                        <?php endif; ?>
                    </p>
                </div>

                <!-- ── AUDIO PLAYER ── -->
                <?php if ($audioSrc): ?>
                <div style="
                    background:var(--bg-card); border:1px solid var(--border);
                    border-radius:16px; padding:24px; margin-bottom:28px;
                    box-shadow:0 8px 30px rgba(0,0,0,0.3);
                ">
                    <p style="font-size:0.72rem;font-weight:600;letter-spacing:0.1em;
                              text-transform:uppercase;color:var(--text-sub);margin-bottom:16px;">
                        <i class="bi bi-headphones me-2" style="color:var(--accent);"></i>Now Playing
                    </p>

                    <!-- Custom styled audio player -->
                    <audio id="audioPlayer" controls
                           style="width:100%;outline:none;border-radius:10px;accent-color:var(--accent);">
                        <source src="<?= $audioSrc ?>" type="audio/mpeg">
                        <source src="<?= $audioSrc ?>" type="audio/ogg">
                        Your browser does not support the audio element.
                    </audio>

                    <!-- Track label under player -->
                    <div style="
                        display:flex;align-items:center;gap:10px;
                        margin-top:14px;padding-top:14px;
                        border-top:1px solid var(--border);
                    ">
                        <div style="
                            width:36px;height:36px;background:rgba(245,166,35,0.12);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;color:var(--accent);
                            animation:spin 4s linear infinite;
                            flex-shrink:0;
                        " id="discSpin">
                            <i class="bi bi-disc" style="font-size:1.1rem;"></i>
                        </div>
                        <div>
                            <p style="font-size:0.9rem;font-weight:600;
                                      color:var(--text-primary);margin:0;">
                                <?= htmlspecialchars($m['title']) ?>
                            </p>
                            <p style="font-size:0.78rem;color:var(--text-muted);margin:0;">
                                <?= htmlspecialchars($m['artist_name'] ?? '') ?>
                            </p>
                        </div>
                    </div>
                </div>

                <style>
                @keyframes spin { to { transform: rotate(360deg); } }
                /* pause spin when audio paused */
                #discSpin.paused { animation-play-state: paused; }

                /* style the native audio element */
                #audioPlayer {
                    height: 44px;
                    filter: invert(0);
                }
                </style>
                <script>
                (function(){
                    const audio = document.getElementById('audioPlayer');
                    const disc  = document.getElementById('discSpin');
                    if (!audio || !disc) return;
                    audio.addEventListener('play',  () => disc.classList.remove('paused'));
                    audio.addEventListener('pause', () => disc.classList.add('paused'));
                    audio.addEventListener('ended', () => disc.classList.add('paused'));
                    disc.classList.add('paused'); // start paused
                })();
                </script>

                <?php else: ?>
                <div style="
                    background:var(--bg-card); border:1px dashed var(--border);
                    border-radius:16px; padding:30px; margin-bottom:28px;
                    text-align:center;
                ">
                    <i class="bi bi-music-note-beamed" style="
                        font-size:2rem;color:var(--text-sub);
                        display:block;margin-bottom:10px;
                    "></i>
                    <p style="color:var(--text-muted);font-size:0.88rem;margin:0;">
                        Audio file not available for this track.
                    </p>
                </div>
                <?php endif; ?>

                <!-- ── DESCRIPTION ── -->
                <?php if (!empty($m['description'])): ?>
                <div style="
                    background:var(--bg-card); border:1px solid var(--border);
                    border-radius:16px; padding:24px; margin-bottom:28px;
                ">
                    <p style="font-size:0.72rem;font-weight:600;letter-spacing:0.1em;
                              text-transform:uppercase;color:var(--text-sub);margin-bottom:12px;">
                        <i class="bi bi-file-text me-2" style="color:var(--accent);"></i>About this Track
                    </p>
                    <p style="color:var(--text-muted);font-size:0.92rem;line-height:1.75;margin:0;">
                        <?= nl2br(htmlspecialchars($m['description'])) ?>
                    </p>
                </div>
                <?php endif; ?>

                <!-- ══ REVIEWS SECTION ══ -->
                <div id="reviews">
                    <div style="display:flex;align-items:center;justify-content:space-between;
                                margin-bottom:20px;flex-wrap:wrap;gap:10px;">
                        <div>
                            <p style="font-size:0.72rem;font-weight:600;letter-spacing:0.1em;
                                      text-transform:uppercase;color:var(--text-sub);margin-bottom:4px;">
                                <i class="bi bi-chat-square-text me-2" style="color:var(--accent);"></i>Reviews
                            </p>
                            <h3 style="font-family:var(--font-display);font-size:1.4rem;
                                       font-weight:700;color:var(--text-primary);margin:0;">
                                <?= $rev_count ?> Review<?= $rev_count !== 1 ? 's' : '' ?>
                            </h3>
                        </div>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <button onclick="document.getElementById('reviewForm').scrollIntoView({behavior:'smooth'})"
                                style="
                            background:var(--accent); border:none; color:#000;
                            font-family:var(--font-body); font-size:0.84rem;
                            font-weight:600; padding:9px 20px; border-radius:10px;
                            cursor:pointer; transition:background .2s;
                            display:flex;align-items:center;gap:6px;
                        " onmouseover="this.style.background='#ffb733'"
                           onmouseout="this.style.background='var(--accent)'">
                            <i class="bi bi-plus-lg"></i> Write a Review
                        </button>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($reviews)): ?>
                    <div style="
                        text-align:center;padding:40px 20px;
                        border:1px dashed var(--border);border-radius:14px;
                        margin-bottom:28px;
                    ">
                        <i class="bi bi-chat-dots" style="font-size:2rem;color:var(--text-sub);
                           display:block;margin-bottom:10px;"></i>
                        <p style="color:var(--text-muted);margin:0;font-size:0.9rem;">
                            No reviews yet. Be the first to review this track!
                        </p>
                    </div>

                    <?php else: ?>

                    <!-- Review cards -->
                    <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:28px;">
                        <?php foreach ($reviews as $r):
                            $rStars = (int)$r['rating'];
                        ?>
                        <div style="
                            background:var(--bg-card); border:1px solid var(--border);
                            border-radius:14px; padding:20px;
                            transition:border-color .2s;
                        " onmouseover="this.style.borderColor='rgba(245,166,35,0.2)'"
                           onmouseout="this.style.borderColor='var(--border)'">

                            <!-- Review header -->
                            <div style="display:flex;align-items:flex-start;
                                        justify-content:space-between;margin-bottom:10px;gap:10px;">
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <!-- Avatar -->
                                    <div style="
                                        width:38px;height:38px;border-radius:50%;
                                        background:rgba(245,166,35,0.15);
                                        border:1px solid rgba(245,166,35,0.2);
                                        display:flex;align-items:center;justify-content:center;
                                        font-family:var(--font-display);font-size:1rem;
                                        font-weight:700;color:var(--accent);flex-shrink:0;
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

                                <!-- Star rating display -->
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
                                color:var(--text-muted);font-size:0.875rem;
                                line-height:1.7;margin:0;
                                padding-top:10px;border-top:1px solid var(--border);
                            ">
                                <?= nl2br(htmlspecialchars($r['review'])) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- ══ ADD REVIEW FORM ══ -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <div id="reviewForm" style="
                        background:var(--bg-card); border:1px solid var(--border);
                        border-radius:16px; padding:26px;
                    ">
                        <p style="font-size:0.72rem;font-weight:600;letter-spacing:0.1em;
                                  text-transform:uppercase;color:var(--text-sub);margin-bottom:16px;">
                            <i class="bi bi-pencil-square me-2" style="color:var(--accent);"></i>
                            <?= $my_rating > 0 ? 'Update Your Review' : 'Write Your Review' ?>
                        </p>

                        <form action="../includes/submit_review.php" method="POST">
                            <input type="hidden" name="media_id"   value="<?= $m['id'] ?>">
                            <input type="hidden" name="media_type" value="music">
                            <input type="hidden" name="redirect"
                                   value="music_details.php?id=<?= $m['id'] ?>">

                            <!-- Star picker -->
                            <div style="margin-bottom:18px;">
                                <label style="font-size:0.8rem;color:var(--text-muted);
                                              display:block;margin-bottom:8px;">Your Rating</label>
                                <div style="display:flex;gap:6px;" id="formStars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <button type="button"
                                            class="form-star <?= $i <= $my_rating ? 'lit' : '' ?>"
                                            data-val="<?= $i ?>"
                                            style="
                                        background:none;border:none;cursor:pointer;
                                        font-size:1.8rem;color:<?= $i <= $my_rating ? 'var(--accent)' : 'var(--text-sub)' ?>;
                                        padding:2px;transition:color .15s,transform .15s;line-height:1;
                                    ">★</button>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="ratingInput"
                                       value="<?= $my_rating ?>">
                            </div>

                            <!-- Review textarea -->
                            <div style="margin-bottom:18px;">
                                <label style="font-size:0.8rem;color:var(--text-muted);
                                              display:block;margin-bottom:8px;">
                                    Your Review <span style="color:var(--text-sub);">(optional)</span>
                                </label>
                                <textarea name="review" rows="4" placeholder="Share your thoughts about this track…"
                                          style="
                                    width:100%;background:rgba(255,255,255,0.04);
                                    border:1px solid var(--border);border-radius:10px;
                                    color:var(--text-primary);font-family:var(--font-body);
                                    font-size:0.88rem;padding:12px 14px;
                                    outline:none;resize:vertical;transition:border-color .2s;
                                " onfocus="this.style.borderColor='var(--accent)'"
                                   onblur="this.style.borderColor='var(--border)'"><?= $my_rating > 0
                                    ? htmlspecialchars($reviews[array_search($_SESSION['user_id'],
                                        array_column($reviews,'user_id'))]['review'] ?? '')
                                    : '' ?></textarea>
                            </div>

                            <button type="submit" style="
                                background:var(--accent);border:none;color:#000;
                                font-family:var(--font-body);font-size:0.88rem;
                                font-weight:700;padding:11px 28px;border-radius:10px;
                                cursor:pointer;transition:background .2s;
                                display:flex;align-items:center;gap:7px;
                            " onmouseover="this.style.background='#ffb733'"
                               onmouseout="this.style.background='var(--accent)'">
                                <i class="bi bi-send"></i>
                                <?= $my_rating > 0 ? 'Update Review' : 'Submit Review' ?>
                            </button>
                        </form>

                        <script>
                        (function(){
                            const formStars = document.querySelectorAll('.form-star');
                            const ratingInput = document.getElementById('ratingInput');
                            let selected = parseInt(ratingInput.value) || 0;

                            function highlight(n) {
                                formStars.forEach((s, i) => {
                                    s.style.color     = i < n ? 'var(--accent)' : 'var(--text-sub)';
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
                        <i class="bi bi-lock" style="font-size:1.8rem;color:var(--text-sub);
                           display:block;margin-bottom:10px;"></i>
                        <p style="color:var(--text-muted);margin-bottom:14px;font-size:0.9rem;">
                            Login to write a review or rate this track.
                        </p>
                        <a href="/music_site/user/login.php" style="
                            background:var(--accent);color:#000;font-family:var(--font-body);
                            font-weight:600;font-size:0.85rem;padding:9px 24px;
                            border-radius:10px;text-decoration:none;transition:background .2s;
                        " onmouseover="this.style.background='#ffb733'"
                           onmouseout="this.style.background='var(--accent)'">Login to Review</a>
                    </div>
                    <?php endif; ?>

                </div><!-- /reviews -->
            </div><!-- /col-right -->
        </div><!-- /row -->
    </div><!-- /container -->
</section>

<div style="margin-bottom:60px;"></div>



<?php require_once '../includes/footer.php'; ?>
