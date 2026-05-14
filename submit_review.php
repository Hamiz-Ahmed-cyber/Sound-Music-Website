<?php
/**
 * /music_site/includes/submit_review.php
 * Handles review form submission (insert or update)
 * Called by the review form in music_details.php, video_details.php, etc.
 */

session_start();
require_once __DIR__ . '/db.php';

/* ── Auth check ── */
if (!isset($_SESSION['user_id'])) {
    header('Location: /music_site/user/login.php');
    exit;
}

/* ── Input ── */
$user_id    = (int)$_SESSION['user_id'];
$media_id   = isset($_POST['media_id'])   ? (int)$_POST['media_id']                         : 0;
$media_type = isset($_POST['media_type']) ? trim($_POST['media_type'])                       : '';
$rating     = isset($_POST['rating'])     ? (int)$_POST['rating']                           : 0;
$review     = isset($_POST['review'])     ? trim(mysqli_real_escape_string($conn, $_POST['review'])) : '';
$redirect   = isset($_POST['redirect'])   ? trim($_POST['redirect'])                         : '';

/* ── Validate ── */
if (!in_array($media_type, ['music', 'video']) || $media_id <= 0 || $rating < 1 || $rating > 5) {
    header('Location: ' . ($redirect ?: '/music_site/index.php'));
    exit;
}

$mt = mysqli_real_escape_string($conn, $media_type);

/* ── Check if review already exists ── */
$check = mysqli_query($conn,
    "SELECT id FROM reviews
     WHERE user_id = $user_id AND media_id = $media_id AND media_type = '$mt'
     LIMIT 1"
);
$existing = mysqli_fetch_assoc($check);

if ($existing) {
    /* Update existing review */
    mysqli_query($conn,
        "UPDATE reviews
         SET rating = $rating, review = '$review', created_at = NOW()
         WHERE id = " . (int)$existing['id']
    );
} else {
    /* Insert new review */
    mysqli_query($conn,
        "INSERT INTO reviews (user_id, media_id, media_type, rating, review, created_at)
         VALUES ($user_id, $media_id, '$mt', $rating, '$review', NOW())"
    );
}

/* ── Redirect back ── */
$safe_redirect = preg_match('/^[a-zA-Z0-9_.?\-=&\/]+$/', $redirect)
    ? '/music_site/user/' . $redirect
    : '/music_site/index.php';

header('Location: ' . $safe_redirect . '#reviews');
exit;
