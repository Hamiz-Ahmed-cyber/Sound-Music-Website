<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Edit Profile';
require_once '../includes/db.php';
require_once '../includes/header.php';

$user_id = (int)$_SESSION['user_id'];
$errors  = [];
$success = '';

/* ── Fetch current user ── */
$res  = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id LIMIT 1");
$user = mysqli_fetch_assoc($res);

/* ── Handle form submission ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'profile';

    /* ════ UPDATE PROFILE INFO ════ */
    if ($action === 'profile') {
        $name    = trim($_POST['name']    ?? '');
        $phone   = trim($_POST['phone']   ?? '');
        $address = trim($_POST['address'] ?? '');
        $email   = trim($_POST['email']   ?? '');

        if (empty($name))  $errors['name']  = 'Name is required.';
        if (empty($email)) $errors['email'] = 'Email is required.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors['email'] = 'Enter a valid email address.';
        else {
            $safeEmail = mysqli_real_escape_string($conn, $email);
            $chk = mysqli_query($conn,
                "SELECT id FROM users WHERE email='$safeEmail' AND id != $user_id LIMIT 1");
            if (mysqli_num_rows($chk) > 0)
                $errors['email'] = 'This email is already taken.';
        }
        if (empty($phone)) $errors['phone'] = 'Phone is required.';

        if (empty($errors)) {
            $n = mysqli_real_escape_string($conn, $name);
            $e = mysqli_real_escape_string($conn, $email);
            $p = mysqli_real_escape_string($conn, $phone);
            $a = mysqli_real_escape_string($conn, $address);

            mysqli_query($conn,
                "UPDATE users SET name='$n', email='$e', phone='$p', address='$a'
                 WHERE id=$user_id");

            $_SESSION['name']  = $name;
            $_SESSION['email'] = $email;

            /* refresh user data */
            $res  = mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id LIMIT 1");
            $user = mysqli_fetch_assoc($res);
            $success = 'profile';
        }
    }

    /* ════ CHANGE PASSWORD ════ */
    if ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current))
            $errors['current'] = 'Current password is required.';
        elseif (!password_verify($current, $user['password']))
            $errors['current'] = 'Current password is incorrect.';

        if (empty($new))
            $errors['new'] = 'New password is required.';
        elseif (strlen($new) < 8)
            $errors['new'] = 'Password must be at least 8 characters.';
        elseif (!preg_match('/[A-Z]/', $new))
            $errors['new'] = 'Must contain at least one uppercase letter.';
        elseif (!preg_match('/[0-9]/', $new))
            $errors['new'] = 'Must contain at least one number.';

        if (empty($errors['new']) && $new !== $confirm)
            $errors['confirm'] = 'Passwords do not match.';

        if (empty($errors)) {
            $hashed = password_hash($new, PASSWORD_BCRYPT);
            mysqli_query($conn,
                "UPDATE users SET password='$hashed' WHERE id=$user_id");
            $success = 'password';
        }
    }
}
?>

<style>
/* ── Page wrapper ── */
.ep-wrap {
    min-height: calc(100vh - 80px);
    padding: 44px 0 90px;
    background: radial-gradient(ellipse 70% 50% at 50% 0%,
        rgba(245,166,35,0.07) 0%, transparent 65%);
}

/* ── Breadcrumb ── */
.breadcrumb-bar {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.82rem; color: var(--text-sub);
    margin-bottom: 30px;
}
.breadcrumb-bar a {
    color: var(--text-muted); text-decoration: none;
    transition: color .2s;
}
.breadcrumb-bar a:hover { color: var(--accent); }
.breadcrumb-bar i { font-size: 0.65rem; }

/* ── Page title ── */
.ep-header { margin-bottom: 32px; }
.ep-header .section-label { margin-bottom: 6px; }
.ep-header h1 {
    font-family: var(--font-display);
    font-size: clamp(1.6rem, 3vw, 2.2rem);
    font-weight: 900; color: var(--text-primary);
    line-height: 1.15; margin: 0;
}

/* ── Tab switcher ── */
.ep-tabs {
    display: flex; gap: 6px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 12px; padding: 5px;
    margin-bottom: 28px; width: fit-content;
}
.ep-tab {
    padding: 9px 22px; border-radius: 9px;
    font-family: var(--font-body); font-size: 0.86rem;
    font-weight: 600; cursor: pointer; border: none;
    background: transparent; color: var(--text-muted);
    transition: all .25s; display: flex;
    align-items: center; gap: 7px;
}
.ep-tab.active {
    background: var(--accent); color: #000;
    box-shadow: 0 4px 16px rgba(245,166,35,0.3);
}
.ep-tab:not(.active):hover {
    background: rgba(255,255,255,0.05);
    color: var(--text-primary);
}

/* ── Card ── */
.ep-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 18px; padding: 32px;
    position: relative; overflow: hidden;
    animation: fadeUp .45s ease forwards;
}
.ep-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--accent), #ffb733, transparent);
}
@media (max-width: 576px) { .ep-card { padding: 24px 18px; } }

/* ── Avatar section ── */
.avatar-section {
    display: flex; align-items: center; gap: 20px;
    padding: 20px; border-radius: 14px;
    background: rgba(245,166,35,0.04);
    border: 1px solid rgba(245,166,35,0.1);
    margin-bottom: 28px;
}
.avatar-big {
    width: 72px; height: 72px; border-radius: 50%;
    background: rgba(245,166,35,0.15);
    border: 2px solid rgba(245,166,35,0.3);
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font-display); font-size: 2rem;
    font-weight: 900; color: var(--accent); flex-shrink: 0;
    transition: transform .3s;
}
.avatar-big:hover { transform: scale(1.07); }
.avatar-info h3 {
    font-size: 1.05rem; font-weight: 700;
    color: var(--text-primary); margin: 0 0 4px;
}
.avatar-info p {
    font-size: 0.8rem; color: var(--text-muted); margin: 0;
}
.role-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.68rem; font-weight: 700;
    letter-spacing: 0.08em; text-transform: uppercase;
    padding: 3px 10px; border-radius: 20px;
    background: rgba(245,166,35,0.12);
    border: 1px solid rgba(245,166,35,0.25);
    color: var(--accent); margin-top: 6px;
}

/* ── Form fields ── */
.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 18px;
}
@media (max-width: 576px) {
    .field-row { grid-template-columns: 1fr; }
}
.field-group { margin-bottom: 18px; }
.field-group:last-of-type { margin-bottom: 0; }

.field-group label {
    display: block; font-size: 0.78rem; font-weight: 600;
    color: var(--text-muted); margin-bottom: 7px;
    letter-spacing: 0.04em; text-transform: uppercase;
}
.field-wrap { position: relative; }
.field-wrap .fi {
    position: absolute; left: 13px; top: 50%;
    transform: translateY(-50%);
    color: var(--text-sub); font-size: 0.88rem;
    pointer-events: none; transition: color .2s;
}
.field-wrap.ta-wrap .fi { top: 14px; transform: none; }

.field-wrap input,
.field-wrap textarea {
    width: 100%;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border);
    border-radius: 11px; color: var(--text-primary);
    font-family: var(--font-body); font-size: 0.9rem;
    padding: 11px 14px 11px 40px;
    outline: none; transition: all .25s;
}
.field-wrap textarea {
    resize: vertical; min-height: 90px; padding-top: 11px;
}
.field-wrap input::placeholder,
.field-wrap textarea::placeholder { color: var(--text-sub); }
.field-wrap input:focus,
.field-wrap textarea:focus {
    border-color: var(--accent);
    background: rgba(245,166,35,0.04);
    box-shadow: 0 0 0 3px rgba(245,166,35,0.08);
}
.field-wrap input.is-error { border-color: #e05c5c; }
.err-msg {
    font-size: 0.75rem; color: #e05c5c;
    margin-top: 5px; display: flex;
    align-items: center; gap: 5px;
}

/* eye toggle */
.pw-eye {
    position: absolute; right: 13px; top: 50%;
    transform: translateY(-50%);
    background: none; border: none; color: var(--text-sub);
    cursor: pointer; font-size: 0.95rem;
    padding: 2px; transition: color .2s;
}
.pw-eye:hover { color: var(--accent); }

/* strength bar */
.strength-bars { display: flex; gap: 4px; margin-top: 7px; }
.strength-bars .bar {
    height: 3px; flex: 1; border-radius: 10px;
    background: rgba(255,255,255,0.07); transition: background .3s;
}

/* submit button */
.btn-save {
    background: var(--accent); border: none;
    border-radius: 11px; color: #000;
    font-family: var(--font-body); font-size: 0.92rem;
    font-weight: 700; padding: 12px 32px;
    cursor: pointer; transition: all .2s;
    display: inline-flex; align-items: center; gap: 8px;
    margin-top: 24px;
}
.btn-save:hover {
    background: #ffb733;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(245,166,35,0.3);
}
.btn-save:active { transform: translateY(0); }

/* ── Alert boxes ── */
.alert {
    border-radius: 12px; padding: 14px 18px;
    font-size: 0.875rem; margin-bottom: 24px;
    display: flex; align-items: flex-start; gap: 10px;
    animation: fadeUp .35s ease;
}
.alert-ok {
    background: rgba(52,199,89,0.1);
    border: 1px solid rgba(52,199,89,0.25);
    color: #34c759;
}
.alert-err {
    background: rgba(224,92,92,0.1);
    border: 1px solid rgba(224,92,92,0.25);
    color: #e05c5c;
}

/* ── Sidebar stat ── */
.side-stat {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 14px; padding: 20px;
    margin-bottom: 16px;
    animation: fadeUp .5s ease forwards;
}
.side-stat-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; margin-bottom: 12px;
}
.side-stat-num {
    font-family: var(--font-display); font-size: 1.8rem;
    font-weight: 900; color: var(--text-primary);
    line-height: 1; margin-bottom: 4px;
}
.side-stat-lbl {
    font-size: 0.75rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.07em;
    color: var(--text-muted);
}

/* danger zone */
.danger-zone {
    background: rgba(224,92,92,0.05);
    border: 1px solid rgba(224,92,92,0.15);
    border-radius: 14px; padding: 20px;
    animation: fadeUp .55s ease forwards;
}
.danger-zone h4 {
    font-size: 0.78rem; font-weight: 700;
    letter-spacing: 0.1em; text-transform: uppercase;
    color: #e05c5c; margin-bottom: 10px;
}
.danger-zone p {
    font-size: 0.82rem; color: var(--text-muted);
    margin-bottom: 14px; line-height: 1.6;
}
.btn-danger {
    background: rgba(224,92,92,0.1);
    border: 1px solid rgba(224,92,92,0.25);
    color: #e05c5c; font-family: var(--font-body);
    font-size: 0.82rem; font-weight: 600;
    padding: 8px 18px; border-radius: 9px;
    cursor: pointer; transition: all .2s;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-danger:hover { background: rgba(224,92,92,0.2); }

/* tab panel animation */
.tab-panel { display: none; }
.tab-panel.active {
    display: block;
    animation: fadeUp .35s ease forwards;
}
</style>

<div class="ep-wrap">
    <div class="container">

        <!-- Breadcrumb -->
        <div class="breadcrumb-bar">
            <a href="dashboard.php"><i class="bi bi-house"></i> Dashboard</a>
            <i class="bi bi-chevron-right"></i>
            <span style="color:var(--accent);">Edit Profile</span>
        </div>

        <div class="row g-4 align-items-start">

            <!-- ══ LEFT: Main form ══ -->
            <div class="col-lg-8">

                <!-- Page heading -->
                <div class="ep-header fade-up">
                    <span class="section-label">
                        <i class="bi bi-pencil-square me-1"></i>Account Settings
                    </span>
                    <h1>Edit Your Profile</h1>
                </div>

                <!-- Tab switcher -->
                <div class="ep-tabs fade-up">
                    <button class="ep-tab active" onclick="switchTab('profile', this)">
                        <i class="bi bi-person-circle"></i> Profile Info
                    </button>
                    <button class="ep-tab" onclick="switchTab('password', this)">
                        <i class="bi bi-shield-lock"></i> Change Password
                    </button>
                </div>

                <!-- ══ TAB: Profile Info ══ -->
                <div id="tab-profile" class="tab-panel active">
                    <div class="ep-card">

                        <!-- Avatar section -->
                        <div class="avatar-section">
                            <div class="avatar-big">
                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            </div>
                            <div class="avatar-info">
                                <h3><?= htmlspecialchars($user['name']) ?></h3>
                                <p><?= htmlspecialchars($user['email']) ?></p>
                                <span class="role-pill">
                                    <i class="bi bi-person-check"></i>
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Success alert -->
                        <?php if ($success === 'profile'): ?>
                        <div class="alert alert-ok">
                            <i class="bi bi-check-circle-fill" style="font-size:1rem;flex-shrink:0;"></i>
                            <span>Profile updated successfully!</span>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <input type="hidden" name="action" value="profile">

                            <!-- Name + Email -->
                            <div class="field-row">
                                <div class="field-group" style="margin-bottom:0;">
                                    <label for="name">Full Name</label>
                                    <div class="field-wrap">
                                        <input type="text" id="name" name="name"
                                               placeholder="Your full name"
                                               value="<?= htmlspecialchars($user['name']) ?>"
                                               class="<?= isset($errors['name']) ? 'is-error' : '' ?>">
                                        <i class="bi bi-person fi"></i>
                                    </div>
                                    <?php if (isset($errors['name'])): ?>
                                    <p class="err-msg"><i class="bi bi-exclamation-circle"></i><?= $errors['name'] ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="field-group" style="margin-bottom:0;">
                                    <label for="email">Email Address</label>
                                    <div class="field-wrap">
                                        <input type="email" id="email" name="email"
                                               placeholder="your@email.com"
                                               value="<?= htmlspecialchars($user['email']) ?>"
                                               class="<?= isset($errors['email']) ? 'is-error' : '' ?>">
                                        <i class="bi bi-envelope fi"></i>
                                    </div>
                                    <?php if (isset($errors['email'])): ?>
                                    <p class="err-msg"><i class="bi bi-exclamation-circle"></i><?= $errors['email'] ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="field-group">
                                <label for="phone">Phone Number</label>
                                <div class="field-wrap">
                                    <input type="tel" id="phone" name="phone"
                                           placeholder="+92 300 1234567"
                                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                           class="<?= isset($errors['phone']) ? 'is-error' : '' ?>">
                                    <i class="bi bi-telephone fi"></i>
                                </div>
                                <?php if (isset($errors['phone'])): ?>
                                <p class="err-msg"><i class="bi bi-exclamation-circle"></i><?= $errors['phone'] ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Address -->
                            <div class="field-group">
                                <label for="address">Address</label>
                                <div class="field-wrap ta-wrap">
                                    <textarea id="address" name="address"
                                              placeholder="Street, City, Country"
                                    ><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                    <i class="bi bi-geo-alt fi"></i>
                                </div>
                            </div>

                            <button type="submit" class="btn-save">
                                <i class="bi bi-check2-circle"></i> Save Changes
                            </button>
                        </form>
                    </div>
                </div>

                <!-- ══ TAB: Change Password ══ -->
                <div id="tab-password" class="tab-panel">
                    <div class="ep-card">
                        <div style="margin-bottom:22px;">
                            <p style="font-size:0.72rem;font-weight:600;letter-spacing:0.1em;
                                      text-transform:uppercase;color:var(--text-sub);margin-bottom:6px;">
                                <i class="bi bi-shield-lock me-2" style="color:var(--accent);"></i>
                                Password Security
                            </p>
                            <p style="font-size:0.875rem;color:var(--text-muted);margin:0;">
                                Use a strong password with at least 8 characters, one uppercase letter and one number.
                            </p>
                        </div>

                        <?php if ($success === 'password'): ?>
                        <div class="alert alert-ok">
                            <i class="bi bi-check-circle-fill" style="font-size:1rem;flex-shrink:0;"></i>
                            <span>Password changed successfully!</span>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <input type="hidden" name="action" value="password">

                            <!-- Current password -->
                            <div class="field-group">
                                <label for="current_password">Current Password</label>
                                <div class="field-wrap">
                                    <input type="password" id="current_password"
                                           name="current_password"
                                           placeholder="Enter current password"
                                           style="padding-right:42px;"
                                           class="<?= isset($errors['current']) ? 'is-error' : '' ?>">
                                    <i class="bi bi-lock fi"></i>
                                    <button type="button" class="pw-eye"
                                            onclick="togglePw('current_password','ei0')">
                                        <i class="bi bi-eye" id="ei0"></i>
                                    </button>
                                </div>
                                <?php if (isset($errors['current'])): ?>
                                <p class="err-msg"><i class="bi bi-exclamation-circle"></i><?= $errors['current'] ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- New password -->
                            <div class="field-group">
                                <label for="new_password">New Password</label>
                                <div class="field-wrap">
                                    <input type="password" id="new_password"
                                           name="new_password"
                                           placeholder="Min 8 chars, 1 uppercase, 1 number"
                                           style="padding-right:42px;"
                                           oninput="checkStrength(this.value)"
                                           class="<?= isset($errors['new']) ? 'is-error' : '' ?>">
                                    <i class="bi bi-lock-fill fi"></i>
                                    <button type="button" class="pw-eye"
                                            onclick="togglePw('new_password','ei1')">
                                        <i class="bi bi-eye" id="ei1"></i>
                                    </button>
                                </div>
                                <!-- Strength meter -->
                                <div class="strength-bars" id="strengthBar">
                                    <div class="bar" id="sb1"></div>
                                    <div class="bar" id="sb2"></div>
                                    <div class="bar" id="sb3"></div>
                                    <div class="bar" id="sb4"></div>
                                </div>
                                <p id="strengthLabel" style="font-size:0.72rem;color:var(--text-sub);margin-top:4px;"></p>
                                <?php if (isset($errors['new'])): ?>
                                <p class="err-msg"><i class="bi bi-exclamation-circle"></i><?= $errors['new'] ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Confirm password -->
                            <div class="field-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <div class="field-wrap">
                                    <input type="password" id="confirm_password"
                                           name="confirm_password"
                                           placeholder="Re-enter new password"
                                           style="padding-right:42px;"
                                           class="<?= isset($errors['confirm']) ? 'is-error' : '' ?>">
                                    <i class="bi bi-lock fi"></i>
                                    <button type="button" class="pw-eye"
                                            onclick="togglePw('confirm_password','ei2')">
                                        <i class="bi bi-eye" id="ei2"></i>
                                    </button>
                                </div>
                                <?php if (isset($errors['confirm'])): ?>
                                <p class="err-msg"><i class="bi bi-exclamation-circle"></i><?= $errors['confirm'] ?></p>
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="btn-save">
                                <i class="bi bi-shield-check"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>

            </div><!-- /col-left -->

            <!-- ══ RIGHT: Sidebar ══ -->
            <div class="col-lg-4">

                <!-- Account stats -->
                <?php
                $rev_count = mysqli_fetch_assoc(
                    mysqli_query($conn,"SELECT COUNT(*) AS c FROM reviews WHERE user_id=$user_id"))['c'];
                $joined = date('M d, Y', strtotime($user['created_at']));
                ?>

                <div class="side-stat" style="animation-delay:.05s;">
                    <div class="side-stat-icon"
                         style="background:rgba(245,166,35,0.1);color:var(--accent);">
                        <i class="bi bi-chat-square-heart"></i>
                    </div>
                    <div class="side-stat-num"><?= $rev_count ?></div>
                    <div class="side-stat-lbl">Reviews Written</div>
                </div>

                <div class="side-stat" style="animation-delay:.1s;">
                    <div class="side-stat-icon"
                         style="background:rgba(52,199,89,0.1);color:#34c759;">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="side-stat-num" style="font-size:1.1rem;margin-bottom:6px;">
                        <?= $joined ?>
                    </div>
                    <div class="side-stat-lbl">Member Since</div>
                </div>

                <!-- Quick nav -->
                <div class="side-stat" style="animation-delay:.15s;">
                    <p style="font-size:0.72rem;font-weight:600;letter-spacing:0.1em;
                              text-transform:uppercase;color:var(--text-sub);margin-bottom:14px;">
                        <i class="bi bi-grid me-2" style="color:var(--accent);"></i>
                        Quick Navigation
                    </p>
                    <?php
                    $navs = [
                        ['dashboard.php',    'bi-speedometer2', 'var(--accent)',  'rgba(245,166,35,0.1)', 'Dashboard'],
                        ['music.php',        'bi-music-note-beamed','#34c759',    'rgba(52,199,89,0.1)',  'Music Library'],
                        ['videos.php',       'bi-camera-video', '#f0f0f0',        'rgba(240,240,240,0.08)','Video Library'],
                        ['search.php',       'bi-search',       '#a78bfa',        'rgba(167,139,250,0.1)','Search'],
                        ['logout.php',       'bi-box-arrow-right','#e05c5c',      'rgba(224,92,92,0.1)', 'Logout'],
                    ];
                    foreach ($navs as [$href,$icon,$color,$bg,$label]): ?>
                    <a href="<?= $href ?>" style="
                        display:flex;align-items:center;gap:10px;
                        padding:10px 12px;border-radius:10px;
                        border:1px solid var(--border);
                        background:rgba(255,255,255,0.02);
                        text-decoration:none;margin-bottom:8px;
                        transition:all .2s;
                    " onmouseover="this.style.borderColor='<?= $color ?>22';this.style.background='<?= $bg ?>'"
                       onmouseout="this.style.borderColor='var(--border)';this.style.background='rgba(255,255,255,0.02)'">
                        <div style="
                            width:32px;height:32px;border-radius:8px;
                            background:<?= $bg ?>;color:<?= $color ?>;
                            display:flex;align-items:center;justify-content:center;
                            font-size:0.88rem;flex-shrink:0;
                        "><i class="bi <?= $icon ?>"></i></div>
                        <span style="font-size:0.86rem;font-weight:500;color:var(--text-primary);">
                            <?= $label ?>
                        </span>
                        <i class="bi bi-chevron-right"
                           style="margin-left:auto;color:var(--text-sub);font-size:0.72rem;"></i>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Danger zone -->
                <div class="danger-zone" style="animation-delay:.2s;">
                    <h4><i class="bi bi-exclamation-triangle me-2"></i>Danger Zone</h4>
                    <p>Deleting your account is permanent and cannot be undone. All your reviews and ratings will be removed.</p>
                    <button class="btn-danger" onclick="confirmDelete()">
                        <i class="bi bi-trash3"></i> Delete Account
                    </button>
                </div>

            </div><!-- /col-right -->
        </div><!-- /row -->
    </div><!-- /container -->
</div>

<script>
/* ── Tab switcher ── */
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.ep-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
}

/* ── Auto-open password tab if password errors ── */
<?php if (!empty($errors) && isset($_POST['action']) && $_POST['action'] === 'password'): ?>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.ep-tab:nth-child(2)').click();
});
<?php endif; ?>

/* ── Password visibility toggle ── */
function togglePw(id, iconId) {
    const input = document.getElementById(id);
    const icon  = document.getElementById(iconId);
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'text' ? 'bi bi-eye-slash' : 'bi bi-eye';
}

/* ── Password strength meter ── */
function checkStrength(pw) {
    let score = 0;
    if (pw.length >= 8)           score++;
    if (/[A-Z]/.test(pw))        score++;
    if (/[0-9]/.test(pw))        score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;

    const colors = ['','#e05c5c','#f5a623','#3ec78e','#34c759'];
    const labels = ['','Weak','Fair','Good','Strong'];
    const bars   = [null,
        document.getElementById('sb1'),
        document.getElementById('sb2'),
        document.getElementById('sb3'),
        document.getElementById('sb4'),
    ];
    const lbl = document.getElementById('strengthLabel');
    for (let i = 1; i <= 4; i++) {
        bars[i].style.background = i <= score
            ? colors[score] : 'rgba(255,255,255,0.07)';
    }
    lbl.textContent = pw.length > 0 ? labels[score] : '';
    lbl.style.color = colors[score] || 'var(--text-sub)';
}

/* ── Delete account confirm ── */
function confirmDelete() {
    alert('Please contact admin to delete your account.');
}
</script>

<?php require_once '../includes/footer.php'; ?>
