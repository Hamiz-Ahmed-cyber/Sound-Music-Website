<?php
session_start();

/* ── Already logged in? redirect immediately ── */
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin'
        ? '../admin/dashboard.php'
        : 'dashboard.php'));
    exit;
}

$page_title = 'Login';
require_once '../includes/db.php';


$error   = '';
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';
    $old_email = $email;

    /* ── Basic presence check ── */
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';

    } else {
        /* ── Fetch user by email ── */
        $safeEmail = mysqli_real_escape_string($conn, $email);
        $result    = mysqli_query($conn,
            "SELECT id, name, email, password, role
             FROM users
             WHERE email = '$safeEmail'
             LIMIT 1"
        );

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            /* ── Verify hashed password ── */
            if (password_verify($password, $user['password'])) {

                /* ── Start session ── */
                session_regenerate_id(true); // prevent session fixation
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['email']   = $user['email'];
                $_SESSION['role']    = $user['role'];

                /* ── Role-based redirect ── */
                if ($user['role'] === 'admin') {
                    header('Location: ../admin/dashboard.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit;

            } else {
                $error = 'Incorrect password. Please try again.';
            }

        } else {
            $error = 'No account found with that email address.';
        }
    }
}
require_once '../includes/header.php';
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
        max-width: 460px;
        box-shadow: 0 30px 80px rgba(0,0,0,0.4);
        animation: fadeUp 0.5s ease forwards;
    }
    @media (max-width: 576px) {
        .auth-card { padding: 32px 22px; }
    }

    .auth-logo {
        display: flex; align-items: center;
        justify-content: center; gap: 9px;
        margin-bottom: 28px; text-decoration: none;
    }
    .auth-logo .logo-icon {
        width: 36px; height: 36px;
        background: var(--accent); border-radius: 9px;
        display: flex; align-items: center;
        justify-content: center;
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
        text-align: center; margin-bottom: 6px;
    }
    .auth-sub {
        text-align: center; color: var(--text-muted);
        font-size: 0.875rem; margin-bottom: 30px;
    }

    /* ── Fields ── */
    .field-group { margin-bottom: 18px; }
    .field-group label {
        display: block; font-size: 0.8rem; font-weight: 600;
        color: var(--text-muted); margin-bottom: 7px;
        letter-spacing: 0.03em;
    }
    .field-wrap { position: relative; }
    .field-wrap .f-icon {
        position: absolute; left: 13px; top: 50%;
        transform: translateY(-50%);
        color: var(--text-sub); font-size: 0.9rem;
        pointer-events: none; transition: color .2s;
    }
    .field-wrap input {
        width: 100%;
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--border);
        border-radius: 11px; color: var(--text-primary);
        font-family: var(--font-body); font-size: 0.9rem;
        padding: 11px 14px 11px 40px;
        outline: none;
        transition: border-color .25s, background .25s;
    }
    .field-wrap input::placeholder { color: var(--text-sub); }
    .field-wrap input:focus {
        border-color: var(--accent);
        background: rgba(245,166,35,0.04);
    }
    .field-wrap input.is-error { border-color: #e05c5c; }

    .pw-toggle {
        position: absolute; right: 13px; top: 50%;
        transform: translateY(-50%);
        background: none; border: none;
        color: var(--text-sub); cursor: pointer;
        font-size: 0.95rem; padding: 2px;
        transition: color .2s;
    }
    .pw-toggle:hover { color: var(--accent); }

    /* ── Error alert ── */
    .alert-error {
        background: rgba(224,92,92,0.1);
        border: 1px solid rgba(224,92,92,0.25);
        border-radius: 11px; padding: 13px 16px;
        font-size: 0.875rem; color: #e05c5c;
        margin-bottom: 22px;
        display: flex; align-items: flex-start; gap: 9px;
    }

    /* ── Forgot link row ── */
    .forgot-row {
        display: flex; justify-content: flex-end;
        margin-top: -10px; margin-bottom: 22px;
    }
    .forgot-row a {
        font-size: 0.8rem; color: var(--text-muted);
        text-decoration: none; transition: color .2s;
    }
    .forgot-row a:hover { color: var(--accent); }

    /* ── Submit ── */
    .btn-login {
        width: 100%;
        background: var(--accent); border: none;
        border-radius: 11px; color: #000;
        font-family: var(--font-body);
        font-size: 0.95rem; font-weight: 700;
        padding: 13px; cursor: pointer;
        transition: background .2s, transform .15s;
        display: flex; align-items: center;
        justify-content: center; gap: 8px;
    }
    .btn-login:hover  { background: #ffb733; transform: translateY(-1px); }
    .btn-login:active { transform: translateY(0); }

    /* ── Divider ── */
    .auth-divider {
        display: flex; align-items: center; gap: 12px;
        margin: 24px 0; color: var(--text-sub); font-size: 0.78rem;
    }
    .auth-divider::before, .auth-divider::after {
        content:''; flex:1; height:1px; background:var(--border);
    }

    .auth-register-link {
        text-align: center; font-size: 0.86rem; color: var(--text-muted);
    }
    .auth-register-link a {
        color: var(--accent); font-weight: 600; text-decoration: none;
    }
    .auth-register-link a:hover { text-decoration: underline; }

    /* ── Demo hint box ── */
    .demo-box {
        background: rgba(245,166,35,0.06);
        border: 1px solid rgba(245,166,35,0.15);
        border-radius: 11px; padding: 13px 16px;
        margin-bottom: 24px;
    }
    .demo-box p {
        font-size: 0.75rem; color: var(--text-muted);
        margin: 0 0 4px; font-weight: 600;
        letter-spacing: 0.05em; text-transform: uppercase;
    }
    .demo-box code {
        font-size: 0.8rem; color: var(--accent);
        background: rgba(245,166,35,0.1);
        padding: 2px 7px; border-radius: 5px;
    }
</style>

<div class="auth-wrap">
    <div class="auth-card">

        <!-- Logo -->
        <a href="/music_site/index.php" class="auth-logo">
            <div class="logo-icon"><i class="bi bi-music-note-beamed"></i></div>
            <span>Sound</span>
        </a>

        <h1 class="auth-heading">Welcome back</h1>
        <p class="auth-sub">Login to your Sound account</p>

        <!-- Error alert -->
        <?php if ($error): ?>
        <div class="alert-error">
            <i class="bi bi-exclamation-circle-fill"
               style="font-size:1rem;flex-shrink:0;margin-top:1px;"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <!-- Login form -->
        <form method="POST" action="" novalidate>

            <!-- Email -->
            <div class="field-group">
                <label for="email">Email Address</label>
                <div class="field-wrap">
                    <input type="email" id="email" name="email"
                           placeholder="you@example.com"
                           value="<?= htmlspecialchars($old_email) ?>"
                           class="<?= $error ? 'is-error' : '' ?>"
                           autocomplete="email">
                    <i class="bi bi-envelope f-icon"></i>
                </div>
            </div>

            <!-- Password -->
            <div class="field-group">
                <label for="password">Password</label>
                <div class="field-wrap">
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password"
                           class="<?= $error ? 'is-error' : '' ?>"
                           style="padding-right:42px;"
                           autocomplete="current-password">
                    <i class="bi bi-lock f-icon"></i>
                    <button type="button" class="pw-toggle"
                            onclick="togglePw()" tabindex="-1">
                        <i class="bi bi-eye" id="pwIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Forgot password -->
            <div class="forgot-row">
                <a href="#">Forgot password?</a>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>

        </form>

        <div class="auth-divider">or</div>

        <p class="auth-register-link">
            Don't have an account?
            <a href="register.php">Create one free</a>
        </p>

    </div><!-- /auth-card -->
</div><!-- /auth-wrap -->

<script>
function togglePw() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('pwIcon');
    if (input.type === 'password') {
        input.type    = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type    = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
