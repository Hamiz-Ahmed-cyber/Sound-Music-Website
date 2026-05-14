<?php
$page_title = 'Music';
require_once '../includes/db.php';
require_once '../includes/header.php';

/* ── Fetch all music with category joins ── */
$sql = "
    SELECT
        m.id,
        m.title,
        m.image,
        m.music_file,
        m.description,
        m.created_at,
        artist.name   AS artist_name,
        album.name    AS album_name,
        genre.name    AS genre_name,
        lang.name     AS language_name
    FROM music m
    LEFT JOIN categories artist ON artist.id = m.artist_id AND artist.type = 'artist'
    LEFT JOIN categories album  ON album.id  = m.album_id  AND album.type  = 'album'
    LEFT JOIN categories genre  ON genre.id  = m.genre_id  AND genre.type  = 'genre'
    LEFT JOIN categories lang   ON lang.id   = m.language_id AND lang.type = 'language'
    ORDER BY m.created_at DESC
";
$result = mysqli_query($conn, $sql);
$music_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
$total = count($music_list);
?>

<!-- ══ PAGE HEADER ══ -->
<section style="
    padding: 52px 0 42px;
    background: radial-gradient(ellipse 70% 50% at 50% -5%, rgba(245,166,35,0.09) 0%, transparent 70%);
    border-bottom: 1px solid var(--border);
">
    <div class="container">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:14px;">
            <div>
                <span class="section-label"><i class="bi bi-music-note-list me-1"></i>All Tracks</span>
                <h1 class="section-title">Music Library</h1>
                <p style="color:var(--text-muted); font-size:0.88rem; margin-top:7px;">
                    <?= $total ?> track<?= $total !== 1 ? 's' : '' ?> available
                </p>
            </div>

            <!-- Search + Filter bar -->
            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                <div style="position:relative;">
                    <i class="bi bi-search" style="
                        position:absolute; left:12px; top:50%; transform:translateY(-50%);
                        color:var(--text-sub); font-size:0.82rem; pointer-events:none;
                    "></i>
                    <input type="text" id="searchInput" placeholder="Search title, artist…" style="
                        background:rgba(255,255,255,0.05);
                        border:1px solid var(--border); border-radius:10px;
                        color:var(--text-primary); font-family:var(--font-body);
                        font-size:0.84rem; padding:9px 14px 9px 36px;
                        outline:none; width:220px; transition:all .3s;
                    " onfocus="this.style.borderColor='var(--accent)'"
                       onblur="this.style.borderColor='var(--border)'">
                </div>

                <select id="filterGenre" style="
                    background:rgba(255,255,255,0.05);
                    border:1px solid var(--border); border-radius:10px;
                    color:var(--text-muted); font-family:var(--font-body);
                    font-size:0.84rem; padding:9px 14px; outline:none;
                    cursor:pointer;
                ">
                    <option value="">All Genres</option>
                    <?php
                    $genres = array_unique(array_column($music_list, 'genre_name'));
                    sort($genres);
                    foreach ($genres as $g) {
                        if ($g) echo '<option value="'.htmlspecialchars($g).'">'.htmlspecialchars($g).'</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- ══ MUSIC GRID ══ -->
<section style="padding: 48px 0 80px;">
    <div class="container">

        <?php if (empty($music_list)): ?>
        <!-- Empty state -->
        <div style="
            text-align:center; padding:80px 20px;
            border:1px dashed var(--border); border-radius:var(--radius);
        ">
            <i class="bi bi-music-note-beamed" style="font-size:3rem; color:var(--text-sub); display:block; margin-bottom:14px;"></i>
            <p style="color:var(--text-muted); font-size:1rem; margin-bottom:6px;">No music found in the library.</p>
            <p style="color:var(--text-sub); font-size:0.84rem;">Check back soon or ask an admin to add tracks.</p>
        </div>

        <?php else: ?>

        <!-- No-results message (hidden by default, shown by JS) -->
        <div id="noResults" style="
            display:none; text-align:center; padding:60px 20px;
            border:1px dashed var(--border); border-radius:var(--radius);
        ">
            <i class="bi bi-search" style="font-size:2.5rem; color:var(--text-sub); display:block; margin-bottom:12px;"></i>
            <p style="color:var(--text-muted);">No tracks match your search.</p>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4" id="musicGrid">
            <?php foreach ($music_list as $i => $m):
                $imgSrc = !empty($m['image'])
                    ? '/music_site/uploads/' . htmlspecialchars($m['image'])
                    : '/music_site/assets/placeholder-music.jpg';
                $delay = ($i % 8) * 0.06;
            ?>
            <div class="col music-item fade-up"
                 data-title="<?= strtolower(htmlspecialchars($m['title'])) ?>"
                 data-artist="<?= strtolower(htmlspecialchars($m['artist_name'] ?? '')) ?>"
                 data-genre="<?= htmlspecialchars($m['genre_name'] ?? '') ?>"
                 style="animation-delay: <?= $delay ?>s;">

                <div class="media-card" style="height:100%;">

                    <!-- ── Thumbnail ── -->
                    <div class="card-thumb">
                        <img src="<?= $imgSrc ?>"
                             alt="<?= htmlspecialchars($m['title']) ?>"
                             onerror="this.src='/music_site/assets/placeholder-music.jpg'">

                        <!-- hover overlay with play -->
                        <div class="thumb-overlay">
                        <button class="btn-play-circle play-track-btn"
        data-id="<?= $m['id'] ?>"
        data-title="<?= htmlspecialchars($m['title']) ?>"
        data-artist="<?= htmlspecialchars($m['artist_name'] ?? '') ?>"
        data-file="<?= !empty($m['music_file']) ? '/music_site/uploads/'.htmlspecialchars($m['music_file']) : '' ?>"
        data-image="<?= !empty($m['image']) ? '/music_site/uploads/'.htmlspecialchars($m['image']) : '' ?>">
    <i class="bi bi-play-fill ms-1"></i>
</button>
                        </div>

                        <!-- badge -->
                        <span class="card-badge music">Music</span>

                        <!-- language pill top-right -->
                        <?php if (!empty($m['language_name'])): ?>
                        <span style="
                            position:absolute; top:10px; right:10px;
                            background:rgba(0,0,0,0.65); backdrop-filter:blur(8px);
                            border:1px solid var(--border); border-radius:20px;
                            font-size:0.66rem; font-weight:600; letter-spacing:0.06em;
                            text-transform:uppercase; padding:3px 10px;
                            color:var(--text-muted);
                        "><?= htmlspecialchars($m['language_name']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- ── Card Body ── -->
                    <div class="card-body-pad">

                        <!-- Title -->
                        <p class="card-title-text" style="margin-bottom:5px;">
                            <?= htmlspecialchars($m['title']) ?>
                        </p>

                        <!-- Artist -->
                        <p class="card-artist-line">
                            <i class="bi bi-person" style="font-size:0.75rem;"></i>
                            <span><?= htmlspecialchars($m['artist_name'] ?? 'Unknown Artist') ?></span>
                        </p>

                        <!-- Album -->
                        <?php if (!empty($m['album_name'])): ?>
                        <p style="font-size:0.78rem; color:var(--text-muted); margin-bottom:8px;">
                            <i class="bi bi-disc" style="font-size:0.74rem; margin-right:4px;"></i>
                            <?= htmlspecialchars($m['album_name']) ?>
                        </p>
                        <?php endif; ?>

                        <!-- Genre tag -->
                        <span class="genre-tag"><?= htmlspecialchars($m['genre_name'] ?? 'General') ?></span>

                        <!-- Description excerpt -->
                        <?php if (!empty($m['description'])): ?>
                        <p style="
                            font-size:0.78rem; color:var(--text-sub);
                            line-height:1.55; margin-bottom:14px;
                            display:-webkit-box; -webkit-line-clamp:2;
                            -webkit-box-orient:vertical; overflow:hidden;
                        ">
                            <?= htmlspecialchars($m['description']) ?>
                        </p>
                        <?php else: ?>
                        <div style="margin-bottom:14px;"></div>
                        <?php endif; ?>

                        <!-- ── Action Buttons ── -->
                        <div class="card-btns">
                        <button class="cbtn cbtn-play play-track-btn"
        data-id="<?= $m['id'] ?>"
        data-title="<?= htmlspecialchars($m['title']) ?>"
        data-artist="<?= htmlspecialchars($m['artist_name'] ?? '') ?>"
        data-file="<?= !empty($m['music_file']) ? '/music_site/uploads/'.htmlspecialchars($m['music_file']) : '' ?>"
        data-image="<?= !empty($m['image']) ? '/music_site/uploads/'.htmlspecialchars($m['image']) : '' ?>">
    <i class="bi bi-play-fill"></i> Play
</button>
                            <a href="music_details.php?id=<?= $m['id'] ?>"
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
    const items       = document.querySelectorAll('.music-item');
    const noResults   = document.getElementById('noResults');

    function filterCards() {
        const q     = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const genre = filterGenre ? filterGenre.value : '';
        let visible = 0;

        items.forEach(function (item) {
            const title  = item.dataset.title  || '';
            const artist = item.dataset.artist || '';
            const g      = item.dataset.genre  || '';

            const matchSearch = !q || title.includes(q) || artist.includes(q);
            const matchGenre  = !genre || g === genre;

            if (matchSearch && matchGenre) {
                item.style.display = '';
                visible++;
            } else {
                item.style.display = 'none';
            }
        });

        if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
    }

    if (searchInput) searchInput.addEventListener('input', filterCards);
    if (filterGenre) filterGenre.addEventListener('change', filterCards);
})();
</script>

<?php require_once '../includes/footer.php'; ?>
