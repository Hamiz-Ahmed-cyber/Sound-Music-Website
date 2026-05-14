<?php
$page_title = 'Search';
require_once '../includes/db.php';
require_once '../includes/header.php';

/* ══ Load filter options from categories ══ */
function getCategory(mysqli $conn, string $type): array {
    $t   = mysqli_real_escape_string($conn, $type);
    $res = mysqli_query($conn,
        "SELECT id, name FROM categories WHERE type='$t' ORDER BY name ASC");
    return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
}
$artists   = getCategory($conn, 'artist');
$albums    = getCategory($conn, 'album');
$genres    = getCategory($conn, 'genre');
$languages = getCategory($conn, 'language');
$years     = getCategory($conn, 'year');

/* ══ Inputs ══ */
$f_tab      = in_array($_GET['tab'] ?? '', ['video']) ? 'video' : 'music';
$f_keyword  = trim($_GET['keyword']  ?? '');
$f_artist   = isset($_GET['artist'])   && is_numeric($_GET['artist'])   ? (int)$_GET['artist']   : 0;
$f_album    = isset($_GET['album'])    && is_numeric($_GET['album'])    ? (int)$_GET['album']    : 0;
$f_genre    = isset($_GET['genre'])    && is_numeric($_GET['genre'])    ? (int)$_GET['genre']    : 0;
$f_language = isset($_GET['language']) && is_numeric($_GET['language']) ? (int)$_GET['language'] : 0;
$f_year     = isset($_GET['year'])     && is_numeric($_GET['year'])     ? (int)$_GET['year']     : 0;

$searched = isset($_GET['keyword']) || $f_artist || $f_album || $f_genre || $f_language || $f_year;

/* ══ Build dynamic WHERE ══ */
function buildWhere(mysqli $conn, string $kw, int $artist, int $album,
                   int $genre, int $lang, int $year, string $titleCol='m.title'): string {
    $where = ['1=1'];
    if ($kw !== '') {
        $k = mysqli_real_escape_string($conn, $kw);
        $where[] = "($titleCol LIKE '%$k%' OR m.description LIKE '%$k%')";
    }
    if ($artist) $where[] = "m.artist_id   = $artist";
    if ($album)  $where[] = "m.album_id    = $album";
    if ($genre)  $where[] = "m.genre_id    = $genre";
    if ($lang)   $where[] = "m.language_id = $lang";
    if ($year)   $where[] = "m.year_id     = $year";
    return implode(' AND ', $where);
}

$results = []; $total = 0;
$table   = $f_tab === 'video' ? 'videos' : 'music';
$fileCol = $f_tab === 'video' ? 'video_file' : 'music_file';
$mtype   = $f_tab === 'video' ? 'video'      : 'music';

if ($searched) {
    $whereSQL = buildWhere($conn, $f_keyword, $f_artist, $f_album,
                           $f_genre, $f_language, $f_year, 'm.title');
    $sql = "
        SELECT
            m.id, m.title, m.image, m.description, m.created_at, m.{$fileCol},
            artist.name AS artist_name,
            album.name  AS album_name,
            genre.name  AS genre_name,
            lang.name   AS language_name,
            yr.name     AS year_name,
            COALESCE(AVG(r.rating),0) AS avg_rating,
            COUNT(r.id)               AS review_count
        FROM {$table} m
        LEFT JOIN categories artist ON artist.id = m.artist_id   AND artist.type='artist'
        LEFT JOIN categories album  ON album.id  = m.album_id    AND album.type='album'
        LEFT JOIN categories genre  ON genre.id  = m.genre_id    AND genre.type='genre'
        LEFT JOIN categories lang   ON lang.id   = m.language_id AND lang.type='language'
        LEFT JOIN categories yr     ON yr.id     = m.year_id     AND yr.type='year'
        LEFT JOIN reviews r         ON r.media_id=m.id           AND r.media_type='$mtype'
        WHERE $whereSQL
        GROUP BY m.id
        ORDER BY m.created_at DESC
    ";
    $res     = mysqli_query($conn, $sql);
    $results = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    $total   = count($results);
}

/* ── User ratings ── */
$user_ratings = [];
if (isset($_SESSION['user_id']) && !empty($results)) {
    $uid = (int)$_SESSION['user_id'];
    $rq  = mysqli_query($conn,
        "SELECT media_id, rating FROM reviews
         WHERE user_id=$uid AND media_type='$mtype'");
    while ($rr = mysqli_fetch_assoc($rq))
        $user_ratings[(int)$rr['media_id']] = (int)$rr['rating'];
}

/* ── Stars helper ── */
function starsHtml(float $avg): string {
    $full  = (int)floor($avg);
    $frac  = $avg - $full;
    $half  = ($frac >= 0.25 && $frac < 0.75) ? 1 : 0;
    if ($frac >= 0.75) { $full++; $half = 0; }
    return str_repeat('⭐',$full).($half?'✨':'').str_repeat('☆',max(0,5-$full-$half));
}

$isVideo  = $f_tab === 'video';
$tabAccent = $isVideo ? '#f0f0f0' : 'var(--accent)';
$tabGlow   = $isVideo ? 'rgba(240,240,240,0.08)' : 'rgba(29,185,84,0.1)';$detailPage = $isVideo ? 'video_details.php' : 'music_details.php';
?>

<style>
/* ── Search page ── */
.search-wrap { padding: 44px 0 90px; }

/* page header */
.search-hero {
    padding: 52px 0 44px;
    border-bottom: 1px solid var(--border);
    background: radial-gradient(ellipse 70% 50% at 50% -5%,
        rgba(245,166,35,0.09) 0%, transparent 70%);
    margin-bottom: 0;
}

/* ── Media type tabs ── */
.type-tabs {
    display: flex; gap: 0;
    border: 1px solid var(--border);
    border-radius: 13px; overflow: hidden;
    width: fit-content; margin-bottom: 28px;
}
.type-tab {
    padding: 11px 28px;
    font-family: var(--font-body); font-size: 0.88rem;
    font-weight: 600; cursor: pointer; border: none;
    background: transparent; transition: all .25s;
    display: flex; align-items: center; gap: 7px;
    text-decoration: none;
}
.type-tab.music-tab {
    color: <?= !$isVideo ? '#000' : 'var(--text-muted)' ?>;
    background: <?= !$isVideo ? 'var(--accent)' : 'transparent' ?>;
}
.type-tab.video-tab {
    color: <?= $isVideo ? '#000' : 'var(--text-muted)' ?>;
    background: <?= $isVideo ? '#f0f0f0' : 'transparent' ?>;
    border-left: 1px solid var(--border);
}
.type-tab:hover { opacity: .85; }

/* ── Filter form card ── */
.filter-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px; padding: 28px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    margin-bottom: 36px;
    animation: fadeUp .4s ease forwards;
    position: relative; overflow: hidden;
}
.filter-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg,
        <?= $tabAccent ?>, transparent);
}

.filter-keyword {
    position: relative; margin-bottom: 20px;
}
.filter-keyword i {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%);
    color: var(--text-sub); font-size: 0.9rem;
    pointer-events: none; transition: color .2s;
}
.filter-keyword input {
    width: 100%;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border); border-radius: 12px;
    color: var(--text-primary);
    font-family: var(--font-body); font-size: 0.95rem;
    padding: 13px 16px 13px 44px;
    outline: none; transition: all .3s;
}
.filter-keyword input:focus {
    border-color: <?= $tabAccent ?>;
    background: <?= $tabGlow ?>;
    box-shadow: 0 0 0 3px <?= $isVideo ? 'rgba(126,184,247,0.08)' : 'rgba(245,166,35,0.08)' ?>;
}
.filter-keyword input::placeholder { color: var(--text-sub); }

.dropdowns-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 14px; margin-bottom: 22px;
}
.dd-wrap { position: relative; }
.dd-wrap label {
    display: block; font-size: 0.73rem; font-weight: 600;
    color: var(--text-muted); margin-bottom: 6px;
    letter-spacing: 0.05em; text-transform: uppercase;
}
.dd-wrap .dd-icon {
    position: absolute; left: 11px; bottom: 11px;
    color: <?= $tabAccent ?>; font-size: 0.78rem;
    pointer-events: none;
}
.dd-wrap select {
    width: 100%;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border); border-radius: 10px;
    color: var(--text-muted);
    font-family: var(--font-body); font-size: 0.84rem;
    padding: 10px 32px 10px 32px;
    outline: none; cursor: pointer;
    appearance: none; -webkit-appearance: none;
    transition: border-color .25s;
}
.dd-wrap select:focus,
.dd-wrap select.chosen { border-color: <?= $tabAccent ?>; color: var(--text-primary); }
.dd-arrow {
    position: absolute; right: 10px; bottom: 12px;
    color: var(--text-sub); font-size: 0.72rem;
    pointer-events: none;
}

.filter-btns { display: flex; gap: 10px; flex-wrap: wrap; }

.btn-search {
    background: <?= $tabAccent ?>; border: none; border-radius: 11px;
    color: #000; font-family: var(--font-body);
    font-size: 0.9rem; font-weight: 700;
    padding: 11px 30px; cursor: pointer;
    transition: all .2s;
    display: flex; align-items: center; gap: 8px;
}
.btn-search:hover {
    opacity: .88; transform: translateY(-1px);
    box-shadow: 0 8px 20px <?= $isVideo ? 'rgba(240,240,240,0.2)' : 'rgba(245,166,35,0.3)' ?>;
}
.btn-clear {
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--border); border-radius: 11px;
    color: var(--text-muted); font-family: var(--font-body);
    font-size: 0.9rem; font-weight: 500;
    padding: 11px 22px; cursor: pointer;
    transition: all .2s; text-decoration: none;
    display: flex; align-items: center; gap: 7px;
}
.btn-clear:hover { border-color: #e05c5c; color: #e05c5c; }

/* ── Result header ── */
.results-header {
    display: flex; align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 26px; flex-wrap: wrap; gap: 12px;
}
.result-count {
    font-family: var(--font-display);
    font-size: 1.4rem; font-weight: 700;
    color: var(--text-primary);
}
.result-count span { color: <?= $tabAccent ?>; }

/* active filter pills */
.filter-pills { display: flex; flex-wrap: wrap; gap: 6px; }
.fpill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.72rem; font-weight: 600;
    padding: 4px 12px; border-radius: 20px;
    background: <?= $tabGlow ?>;
    border: 1px solid <?= $isVideo ? 'rgba(240,240,240,0.18)' : 'rgba(245,166,35,0.25)' ?>;
    color: <?= $tabAccent ?>;
}

/* ── Empty states ── */
.empty-state {
    text-align: center; padding: 80px 20px;
    border: 1px dashed var(--border); border-radius: 18px;
    animation: fadeUp .4s ease;
}
.empty-icon {
    width: 72px; height: 72px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 18px; font-size: 1.8rem;
}
.empty-state h3 {
    font-family: var(--font-display); font-size: 1.3rem;
    color: var(--text-primary); margin-bottom: 8px;
}
.empty-state p { color: var(--text-muted); font-size: 0.9rem; max-width:380px;margin:0 auto 16px; }

/* stagger card animations */
.result-col:nth-child(1)  { animation-delay:.03s; }
.result-col:nth-child(2)  { animation-delay:.08s; }
.result-col:nth-child(3)  { animation-delay:.13s; }
.result-col:nth-child(4)  { animation-delay:.18s; }
.result-col:nth-child(5)  { animation-delay:.23s; }
.result-col:nth-child(6)  { animation-delay:.28s; }
.result-col:nth-child(7)  { animation-delay:.33s; }
.result-col:nth-child(8)  { animation-delay:.38s; }
</style>

<!-- ══ PAGE HERO ══ -->
<section class="search-hero">
    <div class="container">
        <div class="fade-up">
            <span class="section-label">
                <i class="bi bi-search me-1"></i>
                <?= $isVideo ? 'Find Videos' : 'Find Music' ?>
            </span>
            <h1 class="section-title">Search & Discover</h1>
            <p style="color:var(--text-muted);font-size:0.9rem;margin-top:8px;">
                Filter by artist, album, genre, language and year
            </p>
        </div>
    </div>
</section>

<div class="search-wrap">
    <div class="container">

        <!-- ══ TYPE TABS ══ -->
        <div class="type-tabs fade-up">
            <a href="search.php?tab=music<?= $f_keyword ? '&keyword='.urlencode($f_keyword) : '' ?>"
               class="type-tab music-tab">
                <i class="bi bi-music-note-beamed"></i> Music
                <?php if (!$isVideo && $searched && $total > 0): ?>
                <span style="
                    background:rgba(0,0,0,0.2);border-radius:20px;
                    padding:1px 7px;font-size:0.7rem;
                "><?= $total ?></span>
                <?php endif; ?>
            </a>
            <a href="search.php?tab=video<?= $f_keyword ? '&keyword='.urlencode($f_keyword) : '' ?>"
               class="type-tab video-tab">
                <i class="bi bi-camera-video"></i> Videos
                <?php if ($isVideo && $searched && $total > 0): ?>
                <span style="
                    background:rgba(0,0,0,0.2);border-radius:20px;
                    padding:1px 7px;font-size:0.7rem;
                "><?= $total ?></span>
                <?php endif; ?>
            </a>
        </div>

        <!-- ══ FILTER FORM ══ -->
        <div class="filter-card">
            <form method="GET" action="">
                <input type="hidden" name="tab" value="<?= $f_tab ?>">

                <!-- Keyword -->
                <div class="filter-keyword">
                    <i class="bi bi-search"></i>
                    <input type="text" name="keyword"
                           value="<?= htmlspecialchars($f_keyword) ?>"
                           placeholder="Search <?= $isVideo ? 'video' : 'song' ?> title or description…">
                </div>

                <!-- Dropdowns -->
                <div class="dropdowns-grid">
                    <?php
                    $drops = [
                        ['artist',   'Artist',   $artists,   $f_artist,   'bi-person'],
                        ['album',    'Album',    $albums,    $f_album,    'bi-disc'],
                        ['genre',    'Genre',    $genres,    $f_genre,    'bi-tag'],
                        ['language', 'Language', $languages, $f_language, 'bi-translate'],
                        ['year',     'Year',     $years,     $f_year,     'bi-calendar3'],
                    ];
                    foreach ($drops as [$name,$label,$data,$sel,$icon]): ?>
                    <div class="dd-wrap">
                        <label><?= $label ?></label>
                        <i class="bi <?= $icon ?> dd-icon"></i>
                        <select name="<?= $name ?>"
                                class="<?= $sel ? 'chosen' : '' ?>"
                                onchange="this.classList.toggle('chosen',this.value!='0')">
                            <option value="0" style="background:#111116;">All <?= $label ?>s</option>
                            <?php foreach ($data as $item): ?>
                            <option value="<?= $item['id'] ?>"
                                    style="background:#111116;"
                                    <?= $sel == $item['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($item['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="bi bi-chevron-down dd-arrow"></i>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Buttons -->
                <div class="filter-btns">
                    <button type="submit" class="btn-search">
                        <i class="bi bi-search"></i> Search <?= $isVideo ? 'Videos' : 'Music' ?>
                    </button>
                    <?php if ($searched): ?>
                    <a href="search.php?tab=<?= $f_tab ?>" class="btn-clear">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- ══ RESULTS ══ -->
        <?php if (!$searched): ?>
        <!-- Default state -->
        <div class="empty-state fade-up">
            <div class="empty-icon" style="
                background:<?= $tabGlow ?>;
                border:1px solid <?= $isVideo ? 'rgba(240,240,240,0.15)' : 'rgba(245,166,35,0.2)' ?>;
            ">
                <i class="bi bi-<?= $isVideo ? 'camera-video' : 'music-note-beamed' ?>"
                   style="color:<?= $tabAccent ?>;"></i>
            </div>
            <h3>Discover <?= $isVideo ? 'Videos' : 'Music' ?></h3>
            <p>Use the filters above to search our entire <?= $isVideo ? 'video' : 'music' ?> library by artist, genre, language and more.</p>
        </div>

        <?php elseif ($total === 0): ?>
        <!-- No results -->
        <div class="empty-state fade-up">
            <div class="empty-icon" style="
                background:rgba(255,255,255,0.04);
                border:1px solid var(--border);
            ">
                <i class="bi bi-search" style="color:var(--text-sub);"></i>
            </div>
            <h3>No Results Found</h3>
            <p>No <?= $isVideo ? 'videos' : 'tracks' ?> matched your filters. Try broadening your search.</p>
            <a href="search.php?tab=<?= $f_tab ?>" style="
                color:<?= $tabAccent ?>;font-size:0.86rem;font-weight:600;
                text-decoration:none;border-bottom:1px solid <?= $isVideo ? 'rgba(240,240,240,0.2)' : 'rgba(245,166,35,0.3)' ?>;
                padding-bottom:2px;
            ">Clear all filters</a>
        </div>

        <?php else: ?>
        <!-- Results header -->
        <div class="results-header fade-up">
            <div>
                <p style="font-size:0.72rem;font-weight:600;letter-spacing:0.1em;
                           text-transform:uppercase;color:var(--text-sub);margin-bottom:4px;">
                    Search Results
                </p>
                <p class="result-count">
                    <?= $total ?> <?= $isVideo ? 'video' : 'track' ?><?= $total !== 1 ? 's' : '' ?> found
                    <?php if ($f_keyword): ?>
                    for <span>"<?= htmlspecialchars($f_keyword) ?>"</span>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Active filter pills -->
            <div class="filter-pills">
                <?php
                $tagMap = [
                    ['artist',   $f_artist,   $artists,   'bi-person'],
                    ['album',    $f_album,    $albums,    'bi-disc'],
                    ['genre',    $f_genre,    $genres,    'bi-tag'],
                    ['language', $f_language, $languages, 'bi-translate'],
                    ['year',     $f_year,     $years,     'bi-calendar3'],
                ];
                foreach ($tagMap as [$key,$val,$list,$icon]) {
                    if (!$val) continue;
                    $name = '';
                    foreach ($list as $item) {
                        if ($item['id'] == $val) { $name = $item['name']; break; }
                    }
                    if ($name) echo '<span class="fpill">
                        <i class="bi '.$icon.'"></i>'.htmlspecialchars($name).'</span>';
                }
                ?>
            </div>
        </div>

        <!-- Result cards grid -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4">
            <?php foreach ($results as $i => $item):
                $avg      = round((float)$item['avg_rating'], 1);
                $count    = (int)$item['review_count'];
                $myRating = $user_ratings[(int)$item['id']] ?? 0;
                $imgSrc   = !empty($item['image'])
                    ? '/music_site/uploads/'.htmlspecialchars($item['image'])
                    : '/music_site/assets/placeholder-'.($isVideo?'video':'music').'.jpg';
            ?>
            <div class="col result-col fade-up">
                <div class="media-card" style="height:100%;">

                    <!-- Thumbnail -->
                    <div class="card-thumb">
                        <img src="<?= $imgSrc ?>"
                             alt="<?= htmlspecialchars($item['title']) ?>"
                             onerror="this.src='/music_site/assets/placeholder-<?= $isVideo?'video':'music' ?>.jpg'">
                        <div class="thumb-overlay">
                            <?php if (!$isVideo): ?>
<button class="btn-play-circle play-track-btn"
        data-id="<?= $item['id'] ?>"
        data-title="<?= htmlspecialchars($item['title']) ?>"
        data-artist="<?= htmlspecialchars($item['artist_name'] ?? '') ?>"
        data-file="<?= !empty($item['music_file'] ?? '') ? '/music_site/uploads/'.htmlspecialchars($item['music_file'] ?? '') : '' ?>"
        data-image="<?= !empty($item['image']) ? '/music_site/uploads/'.htmlspecialchars($item['image']) : '' ?>">
    <i class="bi bi-play-fill ms-1"></i>
</button>
<?php else: ?>
    <?php if (!$isVideo): ?>
<button class="btn-play-circle play-track-btn"
        data-id="<?= $item['id'] ?>"
        data-title="<?= htmlspecialchars($item['title']) ?>"
        data-artist="<?= htmlspecialchars($item['artist_name'] ?? '') ?>"
        data-file="<?= !empty($item['music_file'] ?? '') ? '/music_site/uploads/'.htmlspecialchars($item['music_file'] ?? '') : '' ?>"
        data-image="<?= !empty($item['image']) ? '/music_site/uploads/'.htmlspecialchars($item['image']) : '' ?>">
    <i class="bi bi-play-fill ms-1"></i>
</button>
<?php else: ?>
<a href="<?= $detailPage ?>?id=<?= $item['id'] ?>"
   class="btn-play-circle">
    <i class="bi bi-play-fill ms-1"></i>
</a>
<?php endif; ?>
<?php endif; ?>
                        </div>
                        <span class="card-badge <?= $mtype ?>">
                            <i class="bi bi-<?= $isVideo?'camera-video':'music-note-beamed' ?> me-1"></i>
                            <?= ucfirst($mtype) ?>
                        </span>
                        <?php if (!empty($item['year_name'])): ?>
                        <span style="
                            position:absolute;bottom:10px;right:10px;
                            background:rgba(0,0,0,0.65);backdrop-filter:blur(8px);
                            border:1px solid <?= $isVideo?'rgba(240,240,240,0.15)':'rgba(245,166,35,0.2)' ?>;
                            border-radius:20px;font-size:0.66rem;font-weight:600;
                            padding:3px 10px;color:<?= $tabAccent ?>;
                        "><?= htmlspecialchars($item['year_name']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Body -->
                    <div class="card-body-pad">
                        <p class="card-title-text"><?= htmlspecialchars($item['title']) ?></p>

                        <p class="card-artist-line">
                            <i class="bi bi-person" style="font-size:.75rem;"></i>
                            <span style="color:<?= $tabAccent ?>;">
                                <?= htmlspecialchars($item['artist_name'] ?? 'Unknown') ?>
                            </span>
                        </p>

                        <?php if (!empty($item['album_name'])): ?>
                        <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:6px;">
                            <i class="bi bi-disc" style="font-size:.73rem;margin-right:4px;"></i>
                            <?= htmlspecialchars($item['album_name']) ?>
                        </p>
                        <?php endif; ?>

                        <!-- Tags -->
                        <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:11px;">
                            <?php if (!empty($item['genre_name'])): ?>
                            <span class="genre-tag" style="margin-bottom:0;
                                color:<?= $tabAccent ?>;
                                border-color:<?= $isVideo?'rgba(240,240,240,0.15)':'rgba(245,166,35,0.2)' ?>;">
                                <?= htmlspecialchars($item['genre_name']) ?>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($item['language_name'])): ?>
                            <span class="genre-tag" style="margin-bottom:0;">
                                <?= htmlspecialchars($item['language_name']) ?>
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- Rating -->
                        <div class="rating-row">
                            <div class="rating-info">
                                <span class="stars-rendered"><?= starsHtml($avg) ?></span>
                                <span class="rating-avg">
                                    <?= $avg > 0 ? number_format($avg,1) : '—' ?>
                                </span>
                            </div>
                            <span class="rating-count">
                                <?= $count ?> Review<?= $count!==1?'s':'' ?>
                            </span>
                        </div>

                        <!-- User stars -->
                        <div style="margin-bottom:13px;">
                            <p style="font-size:.7rem;color:var(--text-sub);margin-bottom:4px;">
                                <?= $myRating > 0 ? 'Your rating:' : 'Rate this:' ?>
                            </p>
                            <div class="user-stars"
                                 data-id="<?= $item['id'] ?>"
                                 data-type="<?= $mtype ?>"
                                 data-rated="<?= $myRating ?>">
                                <?php for ($j=1;$j<=5;$j++): ?>
                                <button class="s <?= $j<=$myRating?'lit':'' ?>"
                                        style="color:<?= $j<=$myRating?$tabAccent:'var(--text-sub)' ?>">★</button>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="card-btns">
                            <?php if (!$isVideo): ?>
<button class="cbtn cbtn-play play-track-btn"
        style="background:<?= $tabAccent ?>;border-color:<?= $tabAccent ?>;color:#000;"
        data-id="<?= $item['id'] ?>"
        data-title="<?= htmlspecialchars($item['title']) ?>"
        data-artist="<?= htmlspecialchars($item['artist_name'] ?? '') ?>"
        data-file="<?= !empty($item['music_file'] ?? '') ? '/music_site/uploads/'.htmlspecialchars($item['music_file'] ?? '') : '' ?>"
        data-image="<?= !empty($item['image']) ? '/music_site/uploads/'.htmlspecialchars($item['image']) : '' ?>">
    <i class="bi bi-play-fill"></i> Play
</button>
<?php else: ?>
    <?php if (!$isVideo): ?>
<button class="cbtn cbtn-play play-track-btn"
        style="background:<?= $tabAccent ?>;border-color:<?= $tabAccent ?>;color:#000;"
        data-id="<?= $item['id'] ?>"
        data-title="<?= htmlspecialchars($item['title']) ?>"
        data-artist="<?= htmlspecialchars($item['artist_name'] ?? '') ?>"
        data-file="<?= !empty($item['music_file'] ?? '') ? '/music_site/uploads/'.htmlspecialchars($item['music_file'] ?? '') : '' ?>"
        data-image="<?= !empty($item['image']) ? '/music_site/uploads/'.htmlspecialchars($item['image']) : '' ?>">
    <i class="bi bi-play-fill"></i> Play
</button>
<?php else: ?>
<a href="<?= $detailPage ?>?id=<?= $item['id'] ?>"
   class="cbtn cbtn-play"
   style="background:<?= $tabAccent ?>;border-color:<?= $tabAccent ?>;color:#000;">
    <i class="bi bi-play-fill"></i> Watch
</a>
<?php endif; ?>
<?php endif; ?>
                            <a href="<?= $detailPage ?>?id=<?= $item['id'] ?>"
                               class="cbtn cbtn-details">
                                <i class="bi bi-info-circle"></i> Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
