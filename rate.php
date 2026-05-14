<?php
/**
 * /music_site/includes/rate.php
 * AJAX endpoint — handles add / update / remove ratings
 * Called by the star widget in footer.php
 */

session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

/* ── Auth check ── */
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required to rate.']);
    exit;
}

/* ── Input validation ── */
$user_id    = (int)$_SESSION['user_id'];
$media_id   = isset($_POST['media_id'])   ? (int)$_POST['media_id']               : 0;
$media_type = isset($_POST['media_type']) ? trim($_POST['media_type'])             : '';
$rating     = isset($_POST['rating'])     ? (int)$_POST['rating']                 : 0;

if (!in_array($media_type, ['music', 'video']) || $media_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

if ($rating < 0 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be 0–5.']);
    exit;
}

/* ── Check existing rating ── */
$check = mysqli_query($conn,
    "SELECT id FROM reviews
     WHERE user_id = $user_id
       AND media_id = $media_id
       AND media_type = '" . mysqli_real_escape_string($conn, $media_type) . "'"
);
$existing = mysqli_fetch_assoc($check);

if ($rating === 0) {
    /* Remove rating */
    if ($existing) {
        mysqli_query($conn,
            "DELETE FROM reviews WHERE id = " . (int)$existing['id']
        );
    }
} elseif ($existing) {
    /* Update */
    mysqli_query($conn,
        "UPDATE reviews SET rating = $rating, created_at = NOW()
         WHERE id = " . (int)$existing['id']
    );
} else {
    /* Insert */
    $mt = mysqli_real_escape_string($conn, $media_type);
    mysqli_query($conn,
        "INSERT INTO reviews (user_id, media_id, media_type, rating, created_at)
         VALUES ($user_id, $media_id, '$mt', $rating, NOW())"
    );
}

/* ── Recalculate stats ── */
$mt  = mysqli_real_escape_string($conn, $media_type);
$res = mysqli_query($conn,
    "SELECT COALESCE(AVG(rating),0) AS avg_r, COUNT(id) AS cnt
     FROM reviews
     WHERE media_id = $media_id AND media_type = '$mt'"
);
$stats = mysqli_fetch_assoc($res);
$avg   = round((float)$stats['avg_r'], 1);
$count = (int)$stats['cnt'];

/* ── Build star HTML for live DOM update ── */
function buildStarsHtml(float $avg): string {
    $full  = (int)floor($avg);
    $frac  = $avg - $full;
    $half  = ($frac >= 0.25 && $frac < 0.75) ? 1 : 0;
    if ($frac >= 0.75) { $full++; $half = 0; }
    $empty = 5 - $full - $half;
    return str_repeat('⭐', $full)
         . ($half ? '✨' : '')
         . str_repeat('☆', max(0, $empty));
}

echo json_encode([
    'success'    => true,
    'avg'        => $avg > 0 ? number_format($avg, 1) : '—',
    'count'      => $count,
    'stars_html' => buildStarsHtml($avg),
]);
