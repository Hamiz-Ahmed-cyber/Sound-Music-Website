<?php
$page_title = 'Videos';
require_once '../includes/db.php';
require_once '../includes/header.php';

/* ── Fetch all videos with category joins ── */
$sql = "
    SELECT
        v.id,
        v.title,
        v.image,
        v.description,
        v.created_at,
        artist.name  AS artist_name,
        album.name   AS album_name,
        genre.name   AS genre_name,
        lang.name    AS language_name,
        yr.name      AS year_name,
        COALESCE(AVG(r.rating), 0) AS avg_rating,
        COUNT(r.id)                AS review_count
    FROM videos v
    LEFT JOIN categories artist ON artist.id = v.artist_id   AND artist.type = 'artist'
    LEFT JOIN categories album  ON album.id  = v.album_id    AND album.type  = 'album'
    LEFT JOIN categories genre  ON genre.id  = v.genre_id    AND genre.type  = 'genre'
    LEFT JOIN categories lang   ON lang.id   = v.language_id AND lang.type   = 'language'
    LEFT JOIN categories yr     ON yr.id     = v.year_id     AND yr.type     = 'year'
    LEFT JOIN reviews r         ON r.media_id = v.id         AND r.media_type = 'video'
    GROUP BY v.id
    ORDER BY v.created_at DESC
";
$result     = mysqli_query($conn, $sql);
$video_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
$total      = count($video_list);

/* ── User ratings (if logged in) ── */
$user_ratings = [];
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $rq  = mysqli_query($conn,
        "SELECT media_id, rating FROM reviews
         WHERE user_id = $uid AND media_type = 'video'");
    while ($rr = mysqli_fetch_assoc($rq)) {
        $user_ratings[(int)$rr['media_id']] = (int)$rr['rating'];
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
?>

<!-- ══ PAGE HEADER ══ -->
<section style="
    padding: 52px 0 42px;
    background: radial-gradient(ellipse 70% 50% at 50% -5%, rgba(126,184,247,0.07) 0%, transparent 70%);
    border-bottom: 1px solid var(--border);
">
    <div class="container">
        <div style="display:flex; align-items:flex-end; justify-content:space-between;
                    flex-wrap:wrap; gap:14px;">
            <div>
                <span class="section-label" style="color:#f0f0f0;">
                    <i class="bi bi-camera-video me-1"></i>All Videos
                </span>
                <h1 class="section-title">Video Library</h1>
                <p style="color:var(--text-muted); font-size:0.88rem; margin-top:7px;">
                    <?= $total ?> video<?= $total !== 1 ? 's' : '' ?> available
                </p>
            </div>

            <!-- Search + Filters -->
            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">

                <!-- Search -->
                <div style="position:relative;">
                    <i class="bi bi-search" style="
                        position:absolute; left:12px; top:50%; transform:translateY(-50%);
                        color:var(--text-sub); font-size:0.82rem; pointer-events:none;
                    "></i>
                    <input type="text" id="searchInput" placeholder="Search title, artist…" style="
                        background:rgba(255,255,255,0.05); border:1px solid var(--border);
                        border-radius:10px; color:var(--text-primary);
                        font-family:var(--font-body); font-size:0.84rem;
                        padding:9px 14px 9px 36px; outline:none;
                        width:220px; transition:all .3s;
                    " onfocus="this.style.borderColor='#f0f0f0'"
                       onblur="this.style.borderColor='var(--border)'">
                </div>

                <!-- Genre filter -->
                <select id="filterGenre" style="
                    background:rgba(255,255,255,0.05); border:1px solid var(--border);
                    border-radius:10px; color:var(--text-muted);
                    font-family:var(--font-body); font-size:0.84rem;
                    padding:9px 14px; outline:none; cursor:pointer;
                ">
                    <option value="">All Genres</option>
                    <?php
                    $genres = array_unique(array_column($video_list, 'genre_name'));
                    sort($genres);
                    foreach ($genres as $g) {
                        if ($g) echo '<option value="' . htmlspecialchars($g) . '">'
                                   . htmlspecialchars($g) . '</option>';
                    }
                    ?>
                </select>

                <!-- Language filter -->
                <select id="filterLang" style="
                    background:rgba(255,255,255,0.05); border:1px solid var(--border);
                    border-radius:10px; color:var(--text-muted);
                    font-family:var(--font-body); font-size:0.84rem;
                    padding:9px 14px; outline:none; cursor:pointer;
                ">
                    <option value="">All Languages</option>
                    <?php
                    $langs = array_unique(array_column($video_list, 'language_name'));
                    sort($langs);
                    foreach ($langs as $l) {
                        if ($l) echo '<option value="' . htmlspecialchars($l) . '">'
                                   . htmlspecialchars($l) . '</option>';
                    }
                    ?>
                </select>

            </div>
        </div>
    </div>
</section>

<!-- ══ VIDEO GRID ══ -->
<section style="padding: 48px 0 80px;">
    <div class="container">

        <?php if (empty($video_list)): ?>
        <div style="
            text-align:center; padding:80px 20px;
            border:1px dashed var(--border); border-radius:var(--radius);
        ">
            <i class="bi bi-camera-video" style="
                font-size:3rem; color:var(--text-sub);
                display:block; margin-bottom:14px;
            "></i>
            <p style="color:var(--text-muted); font-size:1rem; margin-bottom:6px;">
                No videos found in the library.
            </p>
            <p style="color:var(--text-sub); font-size:0.84rem;">
                Check back soon or ask an admin to add videos.
            </p>
        </div>

        <?php else: ?>

        <!-- No results message -->
        <div id="noResults" style="
            display:none; text-align:center; padding:60px 20px;
            border:1px dashed var(--border); border-radius:var(--radius);
            margin-bottom:20px;
        ">
            <i class="bi bi-search" style="
                font-size:2.5rem; color:var(--text-sub);
                display:block; margin-bottom:12px;
            "></i>
            <p style="color:var(--text-muted);">No videos match your search.</p>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4" id="videoGrid">
            <?php foreach ($video_list as $i => $v):
                $avg      = round((float)$v['avg_rating'], 1);
                $count    = (int)$v['review_count'];
                $myRating = $user_ratings[(int)$v['id']] ?? 0;
                $imgSrc   = !empty($v['image'])
                    ? '/music_site/uploads/' . htmlspecialchars($v['image'])
                    : '/music_site/assets/placeholder-video.jpg';
                $delay = ($i % 8) * 0.06;
            ?>
            <div class="col video-item fade-up"
                 data-title="<?= strtolower(htmlspecialchars($v['title'])) ?>"
                 data-artist="<?= strtolower(htmlspecialchars($v['artist_name'] ?? '')) ?>"
                 data-genre="<?= htmlspecialchars($v['genre_name'] ?? '') ?>"
                 data-lang="<?= htmlspecialchars($v['language_name'] ?? '') ?>"
                 style="animation-delay:<?= $delay ?>s;">

                <div class="media-card" style="height:100%;">

                    <!-- ── Thumbnail ── -->
                    <div class="card-thumb">
                        <img src="<?= $imgSrc ?>"
                             alt="<?= htmlspecialchars($v['title']) ?>"
                             onerror="this.src='/music_site/assets/placeholder-video.jpg'">

                        <!-- Hover overlay -->
                        <div class="thumb-overlay">
                            <a href="video_details.php?id=<?= $v['id'] ?>"
                               class="btn-play-circle" title="Watch Video">
                                <i class="bi bi-play-fill ms-1"></i>
                            </a>
                        </div>

                        <!-- Video badge -->
                        <span class="card-badge video">
                            <i class="bi bi-camera-video me-1"></i>Video
                        </span>

                        <!-- Language pill -->
                        <?php if (!empty($v['language_name'])): ?>
                        <span style="
                            position:absolute; top:10px; right:10px;
                            background:rgba(0,0,0,0.65); backdrop-filter:blur(8px);
                            border:1px solid var(--border); border-radius:20px;
                            font-size:0.66rem; font-weight:600;
                            letter-spacing:0.06em; text-transform:uppercase;
                            padding:3px 10px; color:var(--text-muted);
                        "><?= htmlspecialchars($v['language_name']) ?></span>
                        <?php endif; ?>

                        <!-- Year pill bottom-right -->
                        <?php if (!empty($v['year_name'])): ?>
                        <span style="
                            position:absolute; bottom:10px; right:10px;
                            background:rgba(0,0,0,0.65); backdrop-filter:blur(8px);
                            border:1px solid rgba(240,240,240,0.18); border-radius:20px;
                            font-size:0.66rem; font-weight:600;
                            letter-spacing:0.06em; padding:3px 10px;
                            color:#f0f0f0;
                        "><?= htmlspecialchars($v['year_name']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- ── Card Body ── -->
                    <div class="card-body-pad">

                        <!-- Title -->
                        <p class="card-title-text" style="margin-bottom:5px;">
                            <?= htmlspecialchars($v['title']) ?>
                        </p>

                        <!-- Artist -->
                        <p class="card-artist-line">
                            <i class="bi bi-person" style="font-size:0.75rem;"></i>
                            <span><?= htmlspecialchars($v['artist_name'] ?? 'Unknown Artist') ?></span>
                        </p>

                        <!-- Album -->
                        <?php if (!empty($v['album_name'])): ?>
                        <p style="font-size:0.78rem; color:var(--text-muted); margin-bottom:8px;">
                            <i class="bi bi-disc" style="font-size:0.74rem; margin-right:4px;"></i>
                            <?= htmlspecialchars($v['album_name']) ?>
                        </p>
                        <?php endif; ?>

                        <!-- Genre tag -->
                        <span class="genre-tag" style="color:#f0f0f0;border-color:rgba(240,240,240,0.15);">
                            <?= htmlspecialchars($v['genre_name'] ?? 'General') ?>
                        </span>

                        <!-- Description excerpt -->
                        <?php if (!empty($v['description'])): ?>
                        <p style="
                            font-size:0.78rem; color:var(--text-sub); line-height:1.55;
                            margin-bottom:12px; display:-webkit-box;
                            -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
                        "><?= htmlspecialchars($v['description']) ?></p>
                        <?php else: ?>
                        <div style="margin-bottom:12px;"></div>
                        <?php endif; ?>

                        <!-- ── Rating display ── -->
                        <div class="rating-row">
                            <div class="rating-info">
                                <span class="stars-rendered"><?= starsHtml($avg) ?></span>
                                <span class="rating-avg">
                                    <?= $avg > 0 ? number_format($avg, 1) : '—' ?>
                                </span>
                            </div>
                            <span class="rating-count">
                                <?= $count ?> Review<?= $count !== 1 ? 's' : '' ?>
                            </span>
                        </div>

                        <!-- ── User star input ── -->
                        <div style="margin-bottom:13px;">
                            <p style="font-size:0.7rem; color:var(--text-sub); margin-bottom:4px;">
                                <?= $myRating > 0 ? 'Your rating:' : 'Rate this:' ?>
                            </p>
                            <div class="user-stars"
                                 data-id="<?= $v['id'] ?>"
                                 data-type="video"
                                 data-rated="<?= $myRating ?>">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button class="s <?= $i <= $myRating ? 'lit' : '' ?>"
                                        title="Rate <?= $i ?> star<?= $i > 1 ? 's' : '' ?>">★</button>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- ── Action buttons ── -->
                        <div class="card-btns">
                            <a href="video_details.php?id=<?= $v['id'] ?>"
                               class="cbtn cbtn-play" style="background:#f0f0f0;border-color:#f0f0f0;color:#000;"
onmouseover="this.style.background='#ffffff';this.style.borderColor='#a8d1ff'"
                               onmouseout="this.style.background='#f0f0f0';this.style.borderColor='#f0f0f0'">
                                <i class="bi bi-play-fill"></i> Watch
                            </a>
                            <a href="video_details.php?id=<?= $v['id'] ?>"
                               class="cbtn cbtn-details">
                                <i class="bi bi-info-circle"></i> Details
                            </a>
                        </div>

                    </div><!-- /card-body-pad -->
                </div><!-- /media-card -->
            </div><!-- /col -->
            <?php endforeach; ?>
        </div><!-- /row -->
        <?php endif; ?>

    </div><!-- /container -->
</section>

<!-- ══ LIVE SEARCH + FILTER JS ══ -->
<script>
(function () {
    const searchInput = document.getElementById('searchInput');
    const filterGenre = document.getElementById('filterGenre');
    const filterLang  = document.getElementById('filterLang');
    const items       = document.querySelectorAll('.video-item');
    const noResults   = document.getElementById('noResults');

    function filterCards() {
        const q     = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const genre = filterGenre ? filterGenre.value : '';
        const lang  = filterLang  ? filterLang.value  : '';
        let visible = 0;

        items.forEach(function (item) {
            const title  = item.dataset.title  || '';
            const artist = item.dataset.artist || '';
            const g      = item.dataset.genre  || '';
            const l      = item.dataset.lang   || '';

            const matchSearch = !q     || title.includes(q) || artist.includes(q);
            const matchGenre  = !genre || g === genre;
            const matchLang   = !lang  || l === lang;

            if (matchSearch && matchGenre && matchLang) {
                item.style.display = '';
                visible++;
            } else {
                item.style.display = 'none';
            }
        });

        if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
    }

    if (searchInput) searchInput.addEventListener('input',  filterCards);
    if (filterGenre) filterGenre.addEventListener('change', filterCards);
    if (filterLang)  filterLang.addEventListener('change',  filterCards);
})();
</script>

<?php require_once '../includes/footer.php'; ?>
