<?php
$page_title = 'Create Account';
require_once '../includes/db.php';
require_once '../includes/header.php';

$errors  = [];
$success = '';
$old     = []; // repopulate form on error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ── Collect & sanitize inputs ── */
    $old['name']    = trim($_POST['name']    ?? '');
    $old['email']   = trim($_POST['email']   ?? '');
    $old['phone']   = trim($_POST['phone']   ?? '');
    $old['address'] = trim($_POST['address'] ?? '');
    $password       =      $_POST['password']        ?? '';
    $confirm        =      $_POST['confirm_password'] ?? '';

    /* ── Validate name ── */
    if (empty($old['name'])) {
        $errors['name'] = 'Full name is required.';
    } elseif (strlen($old['name']) < 2) {
        $errors['name'] = 'Name must be at least 2 characters.';
    }

    /* ── Validate email ── */
    if (empty($old['email'])) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } else {
        /* Check uniqueness */
        $safeEmail = mysqli_real_escape_string($conn, $old['email']);
        $check     = mysqli_query($conn,
            "SELECT id FROM users WHERE email = '$safeEmail' LIMIT 1");
        if (mysqli_num_rows($check) > 0) {
            $errors['email'] = 'This email is already registered.';
        }
    }

    /* ── Validate phone ── */
    if (empty($old['phone'])) {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^[0-9+\-\s()]{7,15}$/', $old['phone'])) {
        $errors['phone'] = 'Enter a valid phone number.';
    }

    /* ── Validate address ── */
    if (empty($old['address'])) {
        $errors['address'] = 'Address is required.';
    }

    /* ── Validate password ── */
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must contain at least one number.';
    }

    /* ── Validate confirm password ── */
    if (empty($confirm)) {
        $errors['confirm'] = 'Please confirm your password.';
    } elseif ($password !== $confirm) {
        $errors['confirm'] = 'Passwords do not match.';
    }

    /* ── If no errors, insert user ── */
    if (empty($errors)) {
        $hashed  = password_hash($password, PASSWORD_BCRYPT);
        $name    = mysqli_real_escape_string($conn, $old['name']);
        $email   = mysqli_real_escape_string($conn, $old['email']);
        $phone   = mysqli_real_escape_string($conn, $old['phone']);
        $address = mysqli_real_escape_string($conn, $old['address']);

        $insert = mysqli_query($conn,
            "INSERT INTO users (name, email, phone, address, password, role, created_at)
             VALUES ('$name', '$email', '$phone', '$address', '$hashed', 'user', NOW())"
        );

        if ($insert) {
            $success = 'Account created successfully! You can now <a href="login.php" style="color:var(--accent);font-weight:600;">login here</a>.';
            $old     = []; // clear form
        } else {
            $errors['general'] = 'Something went wrong. Please try again.';
        }
    }
}
?>

<style>
    .auth-wrap {
        min-height: calc(100vh - 80px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px 16px 80px;
        background: radial-gradient(ellipse 70% 60% at 50% 0%,
            rgba(245,166,35,0.07) 0%, transparent 70%);
    }

    .auth-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 44px 40px;
        width: 100%;
        max-width: 540px;
        box-shadow: 0 30px 80px rgba(0,0,0,0.4);
        animation: fadeUp 0.5s ease forwards;
    }

    @media (max-width: 576px) {
        .auth-card { padding: 32px 22px; }
    }

    .auth-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        margin-bottom: 28px;
        text-decoration: none;
    }
    .auth-logo .logo-icon {
        width: 36px; height: 36px;
        background: var(--accent); border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; color: #000;
    }
    .auth-logo span {
        font-family: var(--font-display);
        font-size: 1.6rem; font-weight: 900;
        color: var(--text-primary);
    }
    .auth-logo .dot { color: var(--accent); }

    .auth-heading {
        font-family: var(--font-display);
        font-size: 1.65rem; font-weight: 700;
        color: var(--text-primary);
        text-align: center;
        margin-bottom: 6px;
    }
    .auth-sub {
        text-align: center;
        color: var(--text-muted);
        font-size: 0.875rem;
        margin-bottom: 32px;
    }

    /* ── Form fields ── */
    .field-group { margin-bottom: 18px; }

    .field-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 7px;
        letter-spacing: 0.03em;
    }

    .field-wrap {
        position: relative;
    }
    .field-wrap .f-icon {
        position: absolute;
        left: 13px; top: 50%;
        transform: translateY(-50%);
        color: var(--text-sub);
        font-size: 0.9rem;
        pointer-events: none;
        transition: color .2s;
    }
    .field-wrap.textarea-wrap .f-icon {
        top: 14px;
        transform: none;
    }

    .field-wrap input,
    .field-wrap textarea {
        width: 100%;
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--border);
        border-radius: 11px;
        color: var(--text-primary);
        font-family: var(--font-body);
        font-size: 0.9rem;
        padding: 11px 14px 11px 40px;
        outline: none;
        transition: border-color .25s, background .25s;
    }
    .field-wrap textarea {
        resize: vertical;
        min-height: 90px;
        padding-top: 11px;
    }
    .field-wrap input::placeholder,
    .field-wrap textarea::placeholder {
        color: var(--text-sub);
    }
    .field-wrap input:focus,
    .field-wrap textarea:focus {
        border-color: var(--accent);
        background: rgba(245,166,35,0.04);
    }
    .field-wrap input:focus + .f-icon,
    .field-wrap input:focus ~ .f-icon,
    .field-wrap textarea:focus ~ .f-icon {
        color: var(--accent);
    }

    /* error state */
    .field-wrap input.is-error,
    .field-wrap textarea.is-error {
        border-color: #e05c5c;
        background: rgba(224,92,92,0.05);
    }
    .error-msg {
        font-size: 0.76rem;
        color: #e05c5c;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* password toggle */
    .pw-toggle {
        position: absolute;
        right: 13px; top: 50%;
        transform: translateY(-50%);
        background: none; border: none;
        color: var(--text-sub); cursor: pointer;
        font-size: 0.95rem; padding: 2px;
        transition: color .2s;
    }
    .pw-toggle:hover { color: var(--accent); }

    /* password strength bar */
    .pw-strength {
        margin-top: 7px;
        display: flex;
        gap: 4px;
    }
    .pw-strength .bar {
        height: 3px;
        flex: 1;
        border-radius: 10px;
        background: rgba(255,255,255,0.07);
        transition: background .3s;
    }

    /* submit button */
    .btn-register {
        width: 100%;
        background: var(--accent);
        border: none; border-radius: 11px;
        color: #000;
        font-family: var(--font-body);
        font-size: 0.95rem; font-weight: 700;
        padding: 13px;
        cursor: pointer;
        transition: background .2s, transform .15s;
        display: flex; align-items: center;
        justify-content: center; gap: 8px;
        margin-top: 8px;
    }
    .btn-register:hover {
        background: #ffb733;
        transform: translateY(-1px);
    }
    .btn-register:active { transform: translateY(0); }

    /* divider */
    .auth-divider {
        display: flex; align-items: center;
        gap: 12px; margin: 24px 0;
        color: var(--text-sub); font-size: 0.78rem;
    }
    .auth-divider::before,
    .auth-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    .auth-login-link {
        text-align: center;
        font-size: 0.86rem;
        color: var(--text-muted);
    }
    .auth-login-link a {
        color: var(--accent);
        font-weight: 600;
        text-decoration: none;
    }
    .auth-login-link a:hover { text-decoration: underline; }

    /* alert boxes */
    .alert-success {
        background: rgba(52,199,89,0.1);
        border: 1px solid rgba(52,199,89,0.25);
        border-radius: 11px;
        padding: 13px 16px;
        font-size: 0.875rem;
        color: #34c759;
        margin-bottom: 22px;
        display: flex; align-items: flex-start; gap: 9px;
    }
    .alert-error {
        background: rgba(224,92,92,0.1);
        border: 1px solid rgba(224,92,92,0.25);
        border-radius: 11px;
        padding: 13px 16px;
        font-size: 0.875rem;
        color: #e05c5c;
        margin-bottom: 22px;
        display: flex; align-items: flex-start; gap: 9px;
    }
</style>

<div class="auth-wrap">
    <div class="auth-card">

        <!-- Logo -->
        <a href="/music_site/index.php" class="auth-logo">
            <div class="logo-icon"><i class="bi bi-music-note-beamed"></i></div>
            <span>Sound</span>
        </a>

        <h1 class="auth-heading">Create your account</h1>
        <p class="auth-sub">Join Sound and start exploring music & videos</p>

        <!-- Success message -->
        <?php if ($success): ?>
        <div class="alert-success">
            <i class="bi bi-check-circle-fill" style="font-size:1rem;flex-shrink:0;margin-top:1px;"></i>
            <span><?= $success ?></span>
        </div>
        <?php endif; ?>

        <!-- General error -->
        <?php if (!empty($errors['general'])): ?>
        <div class="alert-error">
            <i class="bi bi-exclamation-circle-fill" style="font-size:1rem;flex-shrink:0;margin-top:1px;"></i>
            <span><?= htmlspecialchars($errors['general']) ?></span>
        </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="" novalidate>

            <!-- ── Full Name ── -->
            <div class="field-group">
                <label for="name">Full Name</label>
                <div class="field-wrap">
                    <input type="text" id="name" name="name"
                           placeholder="John Doe"
                           value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                           class="<?= isset($errors['name']) ? 'is-error' : '' ?>">
                    <i class="bi bi-person f-icon"></i>
                </div>
                <?php if (isset($errors['name'])): ?>
                <p class="error-msg"><i class="bi bi-exclamation-circle"></i><?= $errors['name'] ?></p>
                <?php endif; ?>
            </div>

            <!-- ── Email ── -->
            <div class="field-group">
                <label for="email">Email Address</label>
                <div class="field-wrap">
                    <input type="email" id="email" name="email"
                           placeholder="you@example.com"
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                           class="<?= isset($errors['email']) ? 'is-error' : '' ?>">
                    <i class="bi bi-envelope f-icon"></i>
                </div>
                <?php if (isset($errors['email'])): ?>
                <p class="error-msg"><i class="bi bi-exclamation-circle"></i><?= $errors['email'] ?></p>
                <?php endif; ?>
            </div>

            <!-- ── Phone ── -->
            <div class="field-group">
                <label for="phone">Phone Number</label>
                <div class="field-wrap">
                    <input type="tel" id="phone" name="phone"
                           placeholder="+92 300 1234567"
                           value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                           class="<?= isset($errors['phone']) ? 'is-error' : '' ?>">
                    <i class="bi bi-telephone f-icon"></i>
                </div>
                <?php if (isset($errors['phone'])): ?>
                <p class="error-msg"><i class="bi bi-exclamation-circle"></i><?= $errors['phone'] ?></p>
                <?php endif; ?>
            </div>

            <!-- ── Address ── -->
            <div class="field-group">
                <label for="address">Address</label>
                <div class="field-wrap textarea-wrap">
                    <textarea id="address" name="address"
                              placeholder="Street, City, Country"
                              class="<?= isset($errors['address']) ? 'is-error' : '' ?>"
                    ><?= htmlspecialchars($old['address'] ?? '') ?></textarea>
                    <i class="bi bi-geo-alt f-icon"></i>
                </div>
                <?php if (isset($errors['address'])): ?>
                <p class="error-msg"><i class="bi bi-exclamation-circle"></i><?= $errors['address'] ?></p>
                <?php endif; ?>
            </div>

            <!-- ── Password ── -->
            <div class="field-group">
                <label for="password">Password</label>
                <div class="field-wrap" style="position:relative;">
                    <input type="password" id="password" name="password"
                           placeholder="Min 8 chars, 1 uppercase, 1 number"
                           class="<?= isset($errors['password']) ? 'is-error' : '' ?>"
                           oninput="checkStrength(this.value)"
                           style="padding-right:42px;">
                    <i class="bi bi-lock f-icon"></i>
                    <button type="button" class="pw-toggle" onclick="togglePw('password','pwIcon1')" tabindex="-1">
                        <i class="bi bi-eye" id="pwIcon1"></i>
                    </button>
                </div>
                <!-- Strength bar -->
                <div class="pw-strength" id="strengthBar">
                    <div class="bar" id="bar1"></div>
                    <div class="bar" id="bar2"></div>
                    <div class="bar" id="bar3"></div>
                    <div class="bar" id="bar4"></div>
                </div>
                <p id="strengthLabel" style="font-size:0.73rem;color:var(--text-sub);margin-top:4px;"></p>
                <?php if (isset($errors['password'])): ?>
                <p class="error-msg"><i class="bi bi-exclamation-circle"></i><?= $errors['password'] ?></p>
                <?php endif; ?>
            </div>

            <!-- ── Confirm Password ── -->
            <div class="field-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="field-wrap" style="position:relative;">
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="Re-enter your password"
                           class="<?= isset($errors['confirm']) ? 'is-error' : '' ?>"
                           style="padding-right:42px;">
                    <i class="bi bi-lock-fill f-icon"></i>
                    <button type="button" class="pw-toggle" onclick="togglePw('confirm_password','pwIcon2')" tabindex="-1">
                        <i class="bi bi-eye" id="pwIcon2"></i>
                    </button>
                </div>
                <?php if (isset($errors['confirm'])): ?>
                <p class="error-msg"><i class="bi bi-exclamation-circle"></i><?= $errors['confirm'] ?></p>
                <?php endif; ?>
            </div>

            <!-- ── Submit ── -->
            <button type="submit" class="btn-register">
                <i class="bi bi-person-plus"></i> Create Account
            </button>

        </form>

        <div class="auth-divider">or</div>
        <p class="auth-login-link">
            Already have an account? <a href="login.php">Login here</a>
        </p>

        <?php endif; ?>

    </div><!-- /auth-card -->
</div><!-- /auth-wrap -->

<script>
/* ── Toggle password visibility ── */
function togglePw(fieldId, iconId) {
    const input = document.getElementById(fieldId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

/* ── Password strength meter ── */
function checkStrength(pw) {
    let score = 0;
    if (pw.length >= 8)              score++;
    if (/[A-Z]/.test(pw))           score++;
    if (/[0-9]/.test(pw))           score++;
    if (/[^A-Za-z0-9]/.test(pw))    score++;

    const colors = ['', '#e05c5c', '#f5a623', '#3ec78e', '#34c759'];
    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];

    const bars  = [null,
        document.getElementById('bar1'),
        document.getElementById('bar2'),
        document.getElementById('bar3'),
        document.getElementById('bar4'),
    ];
    const label = document.getElementById('strengthLabel');

    for (let i = 1; i <= 4; i++) {
        bars[i].style.background = i <= score
            ? colors[score]
            : 'rgba(255,255,255,0.07)';
    }
    label.textContent  = pw.length > 0 ? labels[score] : '';
    label.style.color  = colors[score] || 'var(--text-sub)';
}
</script>

<?php require_once '../includes/footer.php'; ?>
