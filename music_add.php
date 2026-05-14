<?php
$page_title = 'Add Music';
$breadcrumb = [['Music','../music/music_index.php'],['Add Track',null]];
require_once '../../includes/db.php';
require_once '../includes/header.php';

/* ── Load categories ── */
function getCats($conn, $type) {
    $t = mysqli_real_escape_string($conn, $type);
    $r = mysqli_query($conn,"SELECT id,name FROM categories WHERE type='$t' ORDER BY name");
    return $r ? mysqli_fetch_all($r, MYSQLI_ASSOC) : [];
}
$artists   = getCats($conn,'artist');
$albums    = getCats($conn,'album');
$genres    = getCats($conn,'genre');
$languages = getCats($conn,'language');
$years     = getCats($conn,'year');

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']       ?? '');
    $description = trim($_POST['description'] ?? '');
    $artist_id   = (int)($_POST['artist_id']   ?? 0);
    $album_id    = (int)($_POST['album_id']    ?? 0);
    $genre_id    = (int)($_POST['genre_id']    ?? 0);
    $language_id = (int)($_POST['language_id'] ?? 0);
    $year_id     = (int)($_POST['year_id']     ?? 0);

    if (empty($title)) $errors['title'] = 'Title is required.';

    /* ── Upload image ── */
    $uploadDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $image_name = '';
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg','jpeg','png','webp'];
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors['image'] = 'Image must be JPG, PNG or WEBP.';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors['image'] = 'Image must be under 5MB.';
        } else {
            $image_name = 'img_' . time() . '_' . rand(100,999) . '.' . $ext;
            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $uploadDir . $image_name
            );
        }
    }

    /* ── Upload music file ── */
    $music_name = '';
    if (!empty($_FILES['music_file']['name'])) {
        $allowed_audio = ['mp3','wav','ogg','m4a','aac'];
        $ext_audio = strtolower(pathinfo($_FILES['music_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext_audio, $allowed_audio)) {
            $errors['music_file'] = 'Audio must be MP3, WAV, OGG, M4A or AAC.';
        } elseif ($_FILES['music_file']['size'] > 50 * 1024 * 1024) {
            $errors['music_file'] = 'Audio file must be under 50MB.';
        } else {
            $music_name = 'audio_' . time() . '_' . rand(100,999) . '.' . $ext_audio;
           move_uploaded_file(
    $_FILES['music_file']['tmp_name'],
    $uploadDir . $music_name
);
        }
    }

    if (empty($errors)) {
        $t  = mysqli_real_escape_string($conn, $title);
        $d  = mysqli_real_escape_string($conn, $description);
        $ai = $artist_id   ?: 'NULL';
        $li = $album_id    ?: 'NULL';
        $gi = $genre_id    ?: 'NULL';
        $la = $language_id ?: 'NULL';
        $yi = $year_id     ?: 'NULL';
        $im = mysqli_real_escape_string($conn, $image_name);
        $mf = mysqli_real_escape_string($conn, $music_name);

        $ins = mysqli_query($conn,
            "INSERT INTO music
             (title,artist_id,album_id,genre_id,language_id,year_id,description,image,music_file,created_at)
             VALUES ('$t',$ai,$li,$gi,$la,$yi,'$d','$im','$mf',NOW())");

        if ($ins) {
            $success = 'Track added successfully!';
        } else {
            $errors['general'] = 'Database error. Please try again.';
        }
    }
}
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Add New Track</h1>
        <p class="admin-page-sub">Upload audio and fill in track details</p>
    </div>
    <a href="music_index.php" class="btn-action btn-edit">
        <i class="bi bi-arrow-left"></i> Back to Music
    </a>
</div>

<?php if ($success): ?>
<div class="admin-alert admin-alert-success fade-up">
    <i class="bi bi-check-circle-fill"></i>
    <span><?= $success ?> <a href="music_index.php" style="color:var(--green);font-weight:600;">View all tracks</a>
    or <a href="music_add.php" style="color:var(--green);font-weight:600;">add another</a>.</span>
</div>
<?php endif; ?>

<?php if (!empty($errors['general'])): ?>
<div class="admin-alert admin-alert-error fade-up">
    <i class="bi bi-exclamation-circle-fill"></i>
    <span><?= $errors['general'] ?></span>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div class="row g-4">

    <!-- LEFT: main info -->
    <div class="col-lg-8">
        <div class="admin-card fade-up">
            <div class="admin-card-header">
                <div class="admin-card-title">
                    <i class="bi bi-info-circle"></i> Track Information
                </div>
            </div>
            <div class="admin-card-body">

                <!-- Title -->
                <div class="form-group">
                    <label class="form-label">Title <span class="req">*</span></label>
                    <input type="text" name="title"
                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                           placeholder="Enter track title…"
                           class="form-control-admin <?= isset($errors['title'])?'is-error':'' ?>">
                    <?php if (isset($errors['title'])): ?>
                    <p class="form-error"><i class="bi bi-exclamation-circle"></i><?= $errors['title'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" placeholder="About this track…"
                              class="form-control-admin"
                    ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <!-- Categories grid -->
                <div class="row g-3">
                    <?php
                    $selects = [
                        ['artist_id',   'Artist',   $artists,   'bi-person'],
                        ['album_id',    'Album',    $albums,    'bi-disc'],
                        ['genre_id',    'Genre',    $genres,    'bi-tag'],
                        ['language_id', 'Language', $languages, 'bi-translate'],
                        ['year_id',     'Year',     $years,     'bi-calendar3'],
                    ];
                    foreach ($selects as [$name,$label,$data,$icon]):
                        $sel = (int)($_POST[$name] ?? 0);
                    ?>
                    <div class="col-md-6">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">
                                <i class="bi <?= $icon ?>" style="color:var(--accent);margin-right:4px;"></i>
                                <?= $label ?>
                            </label>
                            <select name="<?= $name ?>" class="form-control-admin">
                                <option value="0">— Select <?= $label ?> —</option>
                                <?php foreach ($data as $item): ?>
                                <option value="<?= $item['id'] ?>"
                                    <?= $sel==$item['id']?'selected':'' ?>>
                                    <?= htmlspecialchars($item['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- RIGHT: uploads -->
    <div class="col-lg-4">

        <!-- Cover image -->
        <div class="admin-card fade-up mb-4" style="animation-delay:.06s;">
            <div class="admin-card-header">
                <div class="admin-card-title">
                    <i class="bi bi-image"></i> Cover Image
                </div>
            </div>
            <div class="admin-card-body">
                <div class="upload-zone" onclick="document.getElementById('imageInput').click()">
                    <input type="file" id="imageInput" name="image"
                           accept="image/*" style="display:none;"
                           onchange="previewImg(this)">
                    <i class="bi bi-cloud-upload upload-icon"></i>
                    <p>Click or drag image here</p>
                    <span>JPG, PNG, WEBP · Max 5MB</span>
                </div>
                <?php if (isset($errors['image'])): ?>
                <p class="form-error mt-2"><i class="bi bi-exclamation-circle"></i><?= $errors['image'] ?></p>
                <?php endif; ?>
                <div class="preview-wrap" id="imagePreview">
                    <img src="" alt="Preview">
                </div>
            </div>
        </div>

        <!-- Audio file -->
        <div class="admin-card fade-up" style="animation-delay:.1s;">
            <div class="admin-card-header">
                <div class="admin-card-title">
                    <i class="bi bi-file-music"></i> Audio File
                </div>
            </div>
            <div class="admin-card-body">
                <div class="upload-zone" onclick="document.getElementById('audioInput').click()">
                    <input type="file" id="audioInput" name="music_file"
                           accept="audio/*" style="display:none;"
                           onchange="previewAudio(this)">
                    <i class="bi bi-music-note-beamed upload-icon"></i>
                    <p>Click or drag audio here</p>
                    <span>MP3, WAV, OGG, M4A · Max 50MB</span>
                </div>
                <?php if (isset($errors['music_file'])): ?>
                <p class="form-error mt-2"><i class="bi bi-exclamation-circle"></i><?= $errors['music_file'] ?></p>
                <?php endif; ?>
                <div class="preview-wrap" id="audioPreview" style="padding:8px;">
                    <audio controls style="width:100%;accent-color:var(--accent);"></audio>
                </div>
            </div>
        </div>

    </div>

    <!-- Submit -->
    <div class="col-12 fade-up" style="animation-delay:.15s;">
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn-action btn-primary-admin" style="padding:12px 32px;font-size:.92rem;">
                <i class="bi bi-check2-circle"></i> Save Track
            </button>
            <a href="music_add.php" class="btn-action btn-edit" style="padding:12px 24px;">
                <i class="bi bi-x-circle"></i> Cancel
            </a>
        </div>
    </div>

</div>
</form>

<script>
function previewImg(input) {
    if (!input.files[0]) return;
    const wrap = document.getElementById('imagePreview');
    const img  = wrap.querySelector('img');
    img.src = URL.createObjectURL(input.files[0]);
    wrap.style.display = 'block';
    document.querySelector('#imageInput ~ i, .upload-zone i.upload-icon')
}
function previewAudio(input) {
    if (!input.files[0]) return;
    const wrap  = document.getElementById('audioPreview');
    const audio = wrap.querySelector('audio');
    audio.src = URL.createObjectURL(input.files[0]);
    audio.load();
    wrap.style.display = 'block';
}
/* drag drop for image */
const imgZone = document.querySelector('.upload-zone:first-of-type');
</script>

<?php require_once '../../includes/footer.php'; ?>
0