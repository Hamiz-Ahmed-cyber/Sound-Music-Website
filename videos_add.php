<?php
$page_title = 'Add Video';
$breadcrumb = [['Videos','../videos/videos_index.php'],['Add Video',null]];
require_once '../../includes/db.php';
require_once '../includes/header.php';

function getCats($conn,$type){$t=mysqli_real_escape_string($conn,$type);$r=mysqli_query($conn,"SELECT id,name FROM categories WHERE type='$t' ORDER BY name");return $r?mysqli_fetch_all($r,MYSQLI_ASSOC):[];}
$artists=getCats($conn,'artist');$albums=getCats($conn,'album');$genres=getCats($conn,'genre');$languages=getCats($conn,'language');$years=getCats($conn,'year');

$errors=[];$success='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $title=trim($_POST['title']??'');$description=trim($_POST['description']??'');
    $artist_id=(int)($_POST['artist_id']??0);$album_id=(int)($_POST['album_id']??0);
    $genre_id=(int)($_POST['genre_id']??0);$language_id=(int)($_POST['language_id']??0);$year_id=(int)($_POST['year_id']??0);
    if(empty($title))$errors['title']='Title is required.';

    $image_name='';
    if(!empty($_FILES['image']['name'])){
        $ext=strtolower(pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION));
        if(!in_array($ext,['jpg','jpeg','png','webp'])){$errors['image']='Image must be JPG,PNG or WEBP.';}
        else{$image_name='vimg_'.time().'_'.rand(100,999).'.'.$ext;move_uploaded_file($_FILES['image']['tmp_name'],'../../uploads/'.$image_name);}
    }
    $video_name='';
    if(!empty($_FILES['video_file']['name'])){
        $ext=strtolower(pathinfo($_FILES['video_file']['name'],PATHINFO_EXTENSION));
        if(!in_array($ext,['mp4','webm','ogg','mov'])){$errors['video_file']='Video must be MP4,WEBM,OGG or MOV.';}
        elseif($_FILES['video_file']['size']>500*1024*1024){$errors['video_file']='Video must be under 500MB.';}
        else{$video_name='vid_'.time().'_'.rand(100,999).'.'.$ext;move_uploaded_file($_FILES['video_file']['tmp_name'],'../../uploads/'.$video_name);}
    }

    if(empty($errors)){
        $t=mysqli_real_escape_string($conn,$title);$d=mysqli_real_escape_string($conn,$description);
        $ai=$artist_id?:'NULL';$li=$album_id?:'NULL';$gi=$genre_id?:'NULL';$la=$language_id?:'NULL';$yi=$year_id?:'NULL';
        $im=mysqli_real_escape_string($conn,$image_name);$vf=mysqli_real_escape_string($conn,$video_name);
        $ins=mysqli_query($conn,"INSERT INTO videos(title,artist_id,album_id,genre_id,language_id,year_id,description,image,video_file,created_at)VALUES('$t',$ai,$li,$gi,$la,$yi,'$d','$im','$vf',NOW())");
        if($ins)$success='Video added successfully!';else $errors['general']='Database error.';
    }
}
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Add New Video</h1>
        <p class="admin-page-sub">Upload video file and fill in details</p>
    </div>
    <a href="videos_index.php" class="btn-action btn-edit"><i class="bi bi-arrow-left"></i> Back to Videos</a>
</div>

<?php if($success): ?>
<div class="admin-alert admin-alert-success fade-up">
    <i class="bi bi-check-circle-fill"></i>
    <span><?= $success ?> <a href="videos_index.php" style="color:var(--green);font-weight:600;">View all</a> or <a href="videos_add.php" style="color:var(--green);font-weight:600;">add another</a>.</span>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div class="row g-4">
    <div class="col-lg-8">
        <div class="admin-card fade-up">
            <div class="admin-card-header">
                <div class="admin-card-title" style="color:var(--blue);"><i class="bi bi-info-circle"></i> Video Information</div>
            </div>
            <div class="admin-card-body">
                <div class="form-group">
                    <label class="form-label">Title <span class="req">*</span></label>
                    <input type="text" name="title" value="<?= htmlspecialchars($_POST['title']??'') ?>"
                           placeholder="Enter video title…" class="form-control-admin <?= isset($errors['title'])?'is-error':'' ?>">
                    <?php if(isset($errors['title'])): ?><p class="form-error"><i class="bi bi-exclamation-circle"></i><?= $errors['title'] ?></p><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" placeholder="About this video…" class="form-control-admin"><?= htmlspecialchars($_POST['description']??'') ?></textarea>
                </div>
                <div class="row g-3">
                <?php $selects=[['artist_id','Artist',$artists,'bi-person'],['album_id','Album',$albums,'bi-disc'],['genre_id','Genre',$genres,'bi-tag'],['language_id','Language',$languages,'bi-translate'],['year_id','Year',$years,'bi-calendar3']];
                foreach($selects as [$name,$label,$data,$icon]):$sel=(int)($_POST[$name]??0);?>
                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label"><i class="bi <?= $icon ?>" style="color:var(--blue);margin-right:4px;"></i><?= $label ?></label>
                        <select name="<?= $name ?>" class="form-control-admin">
                            <option value="0">— Select <?= $label ?> —</option>
                            <?php foreach($data as $item): ?><option value="<?= $item['id'] ?>" <?= $sel==$item['id']?'selected':'' ?>><?= htmlspecialchars($item['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="admin-card fade-up mb-4" style="animation-delay:.06s;">
            <div class="admin-card-header"><div class="admin-card-title"><i class="bi bi-image"></i> Thumbnail</div></div>
            <div class="admin-card-body">
                <div class="upload-zone" onclick="document.getElementById('imgInp').click()">
                    <input type="file" id="imgInp" name="image" accept="image/*" style="display:none;" onchange="pImg(this)">
                    <i class="bi bi-image upload-icon"></i>
                    <p>Upload thumbnail</p><span>JPG, PNG, WEBP · Max 5MB</span>
                </div>
                <?php if(isset($errors['image'])): ?><p class="form-error mt-2"><?= $errors['image'] ?></p><?php endif; ?>
                <div class="preview-wrap" id="iPrev"><img src="" alt="Preview"></div>
            </div>
        </div>
        <div class="admin-card fade-up" style="animation-delay:.1s;">
            <div class="admin-card-header"><div class="admin-card-title" style="color:var(--blue);"><i class="bi bi-file-play"></i> Video File</div></div>
            <div class="admin-card-body">
                <div class="upload-zone" onclick="document.getElementById('vidInp').click()">
                    <input type="file" id="vidInp" name="video_file" accept="video/*" style="display:none;" onchange="pVid(this)">
                    <i class="bi bi-camera-video upload-icon" style="color:var(--blue);"></i>
                    <p>Upload video file</p><span>MP4, WEBM, MOV · Max 500MB</span>
                </div>
                <?php if(isset($errors['video_file'])): ?><p class="form-error mt-2"><?= $errors['video_file'] ?></p><?php endif; ?>
                <div class="preview-wrap" id="vPrev" style="padding:8px;">
                    <video controls style="width:100%;border-radius:8px;max-height:200px;background:#000;"></video>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 fade-up" style="animation-delay:.15s;">
        <button type="submit" class="btn-action btn-primary-admin" style="background:#f0f0f0;border-color:#f0f0f0;padding:12px 32px;font-size:.92rem;">
            <i class="bi bi-check2-circle"></i> Save Video
        </button>
        <a href="videos_add.php" class="btn-action btn-edit" style="padding:12px 24px;margin-left:10px;">
            <i class="bi bi-x-circle"></i> Cancel
        </a>
    </div>
</div>
</form>

<script>
function pImg(i){if(!i.files[0])return;const w=document.getElementById('iPrev');w.querySelector('img').src=URL.createObjectURL(i.files[0]);w.style.display='block';}
function pVid(i){if(!i.files[0])return;const w=document.getElementById('vPrev');const v=w.querySelector('video');v.src=URL.createObjectURL(i.files[0]);v.load();w.style.display='block';}
</script>

<?php require_once '../../includes/footer.php'; ?>
