<!-- ══════════════════════════════════════
     FOOTER
══════════════════════════════════════ -->
<footer class="Sound-footer">
    <div class="container">

        <!-- ── Top row ── -->
        <div class="row gy-5 mb-5">

            <!-- Brand -->
            <div class="col-lg-4 col-md-6">
                <a href="/music_site/index.php" class="footer-logo">
                    <div class="footer-logo-box">
                        <i class="bi bi-music-note-beamed"></i>
                    </div>
                    <span><b>Sound</b></span>
                </a>

                <p class="footer-tagline">
                    Your premier destination for music and video streaming.
                    Discover, rate, and enjoy endless entertainment.
                </p>

                <!-- Social links -->
                <div class="social-links">
                    <?php
                    $socials = [
                        ['bi-facebook',  '#', 'Facebook'],
                        ['bi-twitter-x', '#', 'Twitter'],
                        ['bi-instagram', '#', 'Instagram'],
                        ['bi-youtube',   '#', 'YouTube'],
                        ['bi-spotify',   '#', 'Spotify'],
                    ];
                    foreach ($socials as [$icon, $url, $label]): ?>
                    <a href="<?= $url ?>" class="social-btn" title="<?= $label ?>">
                        <i class="bi <?= $icon ?>"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Explore -->
            <div class="col-lg-2 col-md-3 col-6">
                <p class="footer-heading">Explore</p>
                <ul class="footer-links">
                    <?php foreach ([
                        ['Music Library', '/music_site/user/music.php'],
                        ['Videos',        '/music_site/user/videos.php'],
                        ['Search',        '/music_site/user/search.php'],
                        ['Artists',       '/music_site/user/search.php?tab=music'],
                        ['Genres',        '/music_site/user/search.php?tab=music'],
                    ] as [$label, $href]): ?>
                    <li>
                        <a href="<?= $href ?>" class="footer-link">
                            <i class="bi bi-chevron-right footer-link-arrow"></i>
                            <?= $label ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Account -->
            <div class="col-lg-2 col-md-3 col-6">
                <p class="footer-heading">Account</p>
                <ul class="footer-links">
                    <?php
                    $accLinks = isset($_SESSION['user_id']) ? [
                        ['Dashboard',    '/music_site/user/dashboard.php'],
                        ['Edit Profile', '/music_site/user/edit_profile.php'],
                        ['My Reviews',   '/music_site/user/dashboard.php'],
                        ['Logout',       '/music_site/user/logout.php'],
                    ] : [
                        ['Login',        '/music_site/user/login.php'],
                        ['Register',     '/music_site/user/register.php'],
                    ];
                    foreach ($accLinks as [$label, $href]): ?>
                    <li>
                        <a href="<?= $href ?>" class="footer-link <?= $label==='Logout' ? 'danger-link' : '' ?>">
                            <i class="bi bi-chevron-right footer-link-arrow"></i>
                            <?= $label ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Newsletter -->
            <div class="col-lg-4 col-md-6">
                <p class="footer-heading">Stay in the Loop</p>
                <p style="color:var(--text-muted);font-size:0.875rem;
                           line-height:1.65;margin-bottom:18px;">
                    Get the latest music drops and video releases delivered to your inbox.
                </p>
                <div class="newsletter-form">
                    <div class="newsletter-input-wrap">
                        <i class="bi bi-envelope newsletter-icon"></i>
                        <input type="email" placeholder="your@email.com"
                               id="newsletterEmail">
                    </div>
                    <button class="newsletter-btn" onclick="handleNewsletter()">
                        Subscribe
                    </button>
                </div>
                <p id="newsletterMsg" style="
                    font-size:.78rem;margin-top:8px;display:none;
                    color:#34c759;
                "><i class="bi bi-check-circle me-1"></i>You're subscribed!</p>

                <!-- Stats strip -->
                <div class="footer-stats">
                    <?php foreach ([
                        ['bi-music-note-beamed', '10K+', 'Songs'],
                        ['bi-camera-video',      '5K+',  'Videos'],
                        ['bi-people',            '50K+', 'Users'],
                    ] as [$icon, $num, $lbl]): ?>
                    <div class="fstat">
                        <i class="bi <?= $icon ?> fstat-icon"></i>
                        <span class="fstat-num"><?= $num ?></span>
                        <span class="fstat-lbl"><?= $lbl ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div><!-- /row -->

        <!-- ── Bottom bar ── -->
        <div class="footer-bottom">
            <p class="footer-copy">
                &copy; <?= date('Y') ?> Sound. All rights reserved.
                Made with <span style="color:var(--accent);">♥</span> for music lovers.
            </p>
            <div class="footer-bottom-links">
                <?php foreach (['Privacy Policy','Terms of Service','Contact'] as $link): ?>
                <a href="#" class="footer-bottom-link"><?= $link ?></a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</footer>

<style>
/* ══ Footer styles ══ */
.Sound-footer {
    background: #0d0d11;
    border-top: 1px solid var(--border);
    margin-top: 90px;
    padding: 64px 0 0;
    position: relative;
    overflow: hidden;
}
/* top glow line */
.Sound-footer::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 1px;
    background: linear-gradient(90deg,
    transparent 0%, #1db954 40%, #f0f0f0 60%, transparent 100%);
    opacity: .35;
}
/* background decoration */
.Sound-footer::after {
    content: '';
    position: absolute; bottom: 0; left: 50%;
    transform: translateX(-50%);
    width: 900px; height: 400px;
    background: radial-gradient(ellipse at bottom,
        rgba(245,166,35,0.04) 0%, transparent 70%);
    pointer-events: none;
}

/* Logo */
.footer-logo {
    display: inline-flex; align-items: center; gap: 10px;
    text-decoration: none; margin-bottom: 16px;
}
.footer-logo-box {
    width: 34px; height: 34px; background: var(--accent);
    border-radius: 8px; display: flex; align-items: center;
    justify-content: center; font-size: .95rem; color: #000;
    box-shadow: 0 4px 14px rgba(245,166,35,.3);
    transition: transform .25s;
}
.footer-logo:hover .footer-logo-box { transform: rotate(-8deg) scale(1.08); }
.footer-logo span {
    font-family: var(--font-display); font-size: 1.5rem;
    font-weight: 900; color: var(--text-primary);
}
.footer-logo .dot { color: var(--accent); }

.footer-tagline {
    color: var(--text-muted); font-size: .875rem;
    line-height: 1.7; max-width: 290px; margin-bottom: 22px;
}

/* Social */
.social-links { display: flex; gap: 9px; flex-wrap: wrap; }
.social-btn {
    width: 38px; height: 38px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 10px; display: flex; align-items: center;
    justify-content: center; color: var(--text-muted);
    text-decoration: none; font-size: .9rem;
    transition: all .22s;
}
.social-btn:hover {
    background: rgba(245,166,35,0.12);
    border-color: rgba(245,166,35,0.3);
    color: var(--accent);
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(245,166,35,.2);
}

/* Footer columns */
.footer-heading {
    font-size: .75rem; font-weight: 700;
    letter-spacing: .12em; text-transform: uppercase;
    color: var(--text-primary); margin-bottom: 18px;
}
.footer-links { list-style: none; padding: 0; display: flex; flex-direction: column; gap: 10px; }
.footer-link {
    color: var(--text-muted); text-decoration: none;
    font-size: .875rem; display: flex; align-items: center; gap: 6px;
    transition: all .2s;
}
.footer-link:hover { color: var(--accent); padding-left: 4px; }
.footer-link-arrow {
    font-size: .65rem; color: var(--text-sub);
    transition: color .2s, transform .2s;
}
.footer-link:hover .footer-link-arrow { color: var(--accent); transform: translateX(2px); }
.danger-link:hover { color: #e05c5c !important; }

/* Newsletter */
.newsletter-form { display: flex; gap: 8px; margin-bottom: 6px; }
.newsletter-input-wrap { position: relative; flex: 1; }
.newsletter-icon {
    position: absolute; left: 12px; top: 50%;
    transform: translateY(-50%);
    color: var(--text-sub); font-size: .82rem;
    pointer-events: none; transition: color .2s;
}
.newsletter-input-wrap input {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--border); border-radius: 10px;
    color: var(--text-primary); font-family: var(--font-body);
    font-size: .84rem; padding: 10px 14px 10px 36px;
    outline: none; transition: all .25s;
}
.newsletter-input-wrap input:focus {
    border-color: var(--accent);
    background: rgba(245,166,35,.05);
    box-shadow: 0 0 0 3px rgba(245,166,35,.08);
}
.newsletter-input-wrap input:focus ~ .newsletter-icon { color: var(--accent); }
.newsletter-input-wrap input::placeholder { color: var(--text-sub); }

.newsletter-btn {
    background: var(--accent); border: none; border-radius: 10px;
    color: #000; font-family: var(--font-body);
    font-size: .84rem; font-weight: 700; padding: 10px 18px;
    cursor: pointer; transition: all .22s; white-space: nowrap;
}
.newsletter-btn:hover {
    background: #ffb733;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(245,166,35,.35);
}

/* Footer stats */
.footer-stats {
    display: flex; gap: 20px; margin-top: 22px;
    padding-top: 20px; border-top: 1px solid var(--border);
}
.fstat {
    display: flex; flex-direction: column; align-items: center; gap: 3px;
}
.fstat-icon { font-size: 1rem; color: var(--accent); margin-bottom: 2px; }
.fstat-num {
    font-family: var(--font-display); font-size: 1.1rem;
    font-weight: 900; color: var(--text-primary); line-height: 1;
}
.fstat-lbl {
    font-size: .68rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: .07em;
    color: var(--text-sub);
}

/* Bottom bar */
.footer-bottom {
    border-top: 1px solid var(--border);
    padding: 22px 0;
    display: flex; align-items: center;
    justify-content: space-between; flex-wrap: wrap; gap: 12px;
    position: relative; z-index: 1;
}
.footer-copy { color: var(--text-sub); font-size: .8rem; margin: 0; }
.footer-bottom-links { display: flex; gap: 22px; flex-wrap: wrap; }
.footer-bottom-link {
    color: var(--text-sub); text-decoration: none;
    font-size: .8rem; transition: color .2s;
}
.footer-bottom-link:hover { color: var(--accent); }

/* Intersection observer fade-in for footer sections */
.footer-col-anim {
    opacity: 0; transform: translateY(24px);
    transition: opacity .55s ease, transform .55s ease;
}
.footer-col-anim.visible { opacity: 1; transform: translateY(0); }
</style>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* ── Newsletter mock handler ── */
function handleNewsletter() {
    const input = document.getElementById('newsletterEmail');
    const msg   = document.getElementById('newsletterMsg');
    if (!input.value.trim() || !input.value.includes('@')) {
        input.style.borderColor = '#e05c5c';
        setTimeout(() => input.style.borderColor = '', 1500);
        return;
    }
    input.disabled = true;
    document.querySelector('.newsletter-btn').disabled = true;
    msg.style.display = 'block';
}

/* ── Footer scroll reveal ── */
const footerEls = document.querySelectorAll('.footer-col-anim');
if ('IntersectionObserver' in window) {
    const obs = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry, i) {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 80);
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    footerEls.forEach(el => obs.observe(el));
} else {
    footerEls.forEach(el => el.classList.add('visible'));
}

/* ══════════════════════════════════════
   GLOBAL STAR RATING — AJAX
   Handles all .user-stars widgets sitewide
══════════════════════════════════════ */
document.querySelectorAll('.user-stars').forEach(function (widget) {
    const stars     = widget.querySelectorAll('.s');
    const mediaId   = widget.dataset.id;
    const mediaType = widget.dataset.type;
    let current     = parseInt(widget.dataset.rated) || 0;

    function render(n) {
        stars.forEach((s, i) => {
            s.classList.toggle('lit', i < n);
            s.style.color = i < n ? 'var(--accent)' : 'var(--text-sub)';
        });
    }
    render(current);

    stars.forEach(function (star, idx) {
        star.addEventListener('mouseenter', () => render(idx + 1));
        star.addEventListener('mouseleave', () => render(current));
        star.addEventListener('click', function () {
            current = current === idx + 1 ? 0 : idx + 1;
            render(current);

            fetch('/music_site/includes/rate.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'media_id='   + encodeURIComponent(mediaId)
                    + '&media_type=' + encodeURIComponent(mediaType)
                    + '&rating='     + encodeURIComponent(current),
            })
            .then(r => r.json())
            .then(function (data) {
                if (!data.success) return;
                const card = widget.closest('.media-card');
                if (!card) return;
                const avgEl   = card.querySelector('.rating-avg');
                const cntEl   = card.querySelector('.rating-count');
                const starsEl = card.querySelector('.stars-rendered');
                if (avgEl)   avgEl.textContent   = data.avg;
                if (cntEl)   cntEl.textContent   = data.count + ' Review' + (data.count !== 1 ? 's' : '');
                if (starsEl) starsEl.innerHTML   = data.stars_html;
                widget.dataset.rated = current;

                /* pulse feedback on card */
                card.style.transition = 'box-shadow .3s';
                card.style.boxShadow  = '0 0 0 2px var(--accent)';
                setTimeout(() => card.style.boxShadow = '', 600);
            })
            .catch(console.error);
        });
    });
});
</script>

<!-- ══ PERSISTENT MUSIC PLAYER BAR ══ -->
<div id="musicPlayerBar" style="
    display: none;
    position: fixed;
    bottom: 0; left: 0; right: 0;
    height: 80px;
    background: #121212;
    border-top: 1px solid rgba(255,255,255,0.1);
    z-index: 9999;
    padding: 0 16px;
    display: none;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    backdrop-filter: blur(20px);
">

    <!-- Left: Track Info -->
    <div style="display:flex;align-items:center;gap:12px;min-width:200px;flex:1;">
        <div id="playerThumb" style="
            width:52px;height:52px;border-radius:6px;
            background:#1a1a22;border:1px solid rgba(255,255,255,0.1);
            overflow:hidden;flex-shrink:0;
        ">
            <img id="playerImg" src="" alt=""
                 style="width:100%;height:100%;object-fit:cover;display:none;">
            <div id="playerImgPlaceholder" style="
                width:100%;height:100%;
                display:flex;align-items:center;justify-content:center;
                color:var(--accent);font-size:1.4rem;
            "><i class="bi bi-music-note-beamed"></i></div>
        </div>
        <div style="min-width:0;">
            <p id="playerTitle" style="
                font-size:.9rem;font-weight:600;
                color:#fff;margin:0 0 2px;
                white-space:nowrap;overflow:hidden;
                text-overflow:ellipsis;max-width:160px;
            ">—</p>
            <p id="playerArtist" style="
                font-size:.75rem;color:#b3b3b3;margin:0;
                white-space:nowrap;overflow:hidden;
                text-overflow:ellipsis;max-width:160px;
            ">—</p>
        </div>
        <a id="playerDetailLink" href="#" style="
            color:#b3b3b3;font-size:.85rem;
            text-decoration:none;margin-left:8px;
            transition:color .2s;flex-shrink:0;
        " title="Open details">
            <i class="bi bi-box-arrow-up-right"></i>
        </a>
    </div>

    <!-- Center: Controls + Progress -->
    <div style="flex:2;display:flex;flex-direction:column;align-items:center;gap:8px;max-width:600px;">

        <!-- Control buttons -->
        <div style="display:flex;align-items:center;gap:20px;">
            <!-- Shuffle -->
            <button id="btnShuffle" onclick="toggleShuffle()" style="
                background:none;border:none;color:#b3b3b3;
                cursor:pointer;font-size:1rem;padding:4px;
                transition:color .2s;
            " title="Shuffle"><i class="bi bi-shuffle"></i></button>

            <!-- Previous -->
            <button onclick="playerPrev()" style="
                background:none;border:none;color:#b3b3b3;
                cursor:pointer;font-size:1.2rem;padding:4px;
                transition:color .2s;
            " title="Previous"
            onmouseover="this.style.color='#fff'"
            onmouseout="this.style.color='#b3b3b3'">
                <i class="bi bi-skip-start-fill"></i>
            </button>

            <!-- Play/Pause -->
            <button id="btnPlayPause" onclick="togglePlayPause()" style="
                width:38px;height:38px;border-radius:50%;
                background:#fff;border:none;color:#000;
                cursor:pointer;font-size:1rem;
                display:flex;align-items:center;justify-content:center;
                transition:transform .15s,background .2s;
                flex-shrink:0;
            " title="Play/Pause"
            onmouseover="this.style.transform='scale(1.08)'"
            onmouseout="this.style.transform='scale(1)'">
                <i class="bi bi-pause-fill" id="playPauseIcon"></i>
            </button>

            <!-- Next -->
            <button onclick="playerNext()" style="
                background:none;border:none;color:#b3b3b3;
                cursor:pointer;font-size:1.2rem;padding:4px;
                transition:color .2s;
            " title="Next"
            onmouseover="this.style.color='#fff'"
            onmouseout="this.style.color='#b3b3b3'">
                <i class="bi bi-skip-end-fill"></i>
            </button>

            <!-- Repeat -->
            <button id="btnRepeat" onclick="toggleRepeat()" style="
                background:none;border:none;color:#b3b3b3;
                cursor:pointer;font-size:1rem;padding:4px;
                transition:color .2s;
            " title="Repeat"><i class="bi bi-repeat"></i></button>
        </div>

        <!-- Progress bar -->
        <div style="display:flex;align-items:center;gap:10px;width:100%;">
            <span id="currentTime" style="font-size:.72rem;color:#b3b3b3;min-width:35px;text-align:right;">0:00</span>
            <div id="progressBar" style="
                flex:1;height:4px;background:rgba(255,255,255,0.2);
                border-radius:10px;cursor:pointer;position:relative;
            " onclick="seekAudio(event)">
                <div id="progressFill" style="
                    height:100%;width:0%;
                    background:var(--accent);
                    border-radius:10px;
                    transition:width .1s linear;
                    position:relative;
                ">
                    <div style="
                        position:absolute;right:-5px;top:50%;
                        transform:translateY(-50%);
                        width:10px;height:10px;border-radius:50%;
                        background:#fff;
                        display:none;
                    " id="progressThumb"></div>
                </div>
            </div>
            <span id="totalTime" style="font-size:.72rem;color:#b3b3b3;min-width:35px;">0:00</span>
        </div>
    </div>

    <!-- Right: Volume + Close -->
    <div style="display:flex;align-items:center;gap:12px;flex:1;justify-content:flex-end;min-width:180px;">

        <!-- Volume icon -->
        <button onclick="toggleMute()" style="
            background:none;border:none;color:#b3b3b3;
            cursor:pointer;font-size:1rem;padding:4px;
            transition:color .2s;
        " title="Mute/Unmute">
            <i class="bi bi-volume-up-fill" id="volumeIcon"></i>
        </button>

        <!-- Volume slider -->
        <input type="range" id="volumeSlider" min="0" max="1" step="0.01" value="1"
               oninput="setVolume(this.value)"
               style="
                   width:90px;accent-color:var(--accent);cursor:pointer;
               ">

        <!-- Open details -->
        <a id="playerFullLink" href="#" style="
            color:#b3b3b3;font-size:.9rem;text-decoration:none;
            transition:color .2s;padding:4px;
        " title="Open full page"
        onmouseover="this.style.color='#fff'"
        onmouseout="this.style.color='#b3b3b3'">
            <i class="bi bi-arrows-fullscreen"></i>
        </a>

        <!-- Close player -->
        <button onclick="closePlayer()" style="
            background:none;border:none;color:#b3b3b3;
            cursor:pointer;font-size:1rem;padding:4px;
            transition:color .2s;
        " title="Close player"
        onmouseover="this.style.color='#fff'"
        onmouseout="this.style.color='#b3b3b3'">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Hidden audio element -->
    <audio id="globalAudioPlayer" style="display:none;"></audio>
</div>

<!-- Add bottom padding so content doesn't hide behind player -->
<div id="playerSpacer" style="height:0px;transition:height .3s;"></div>

<style>
/* Progress bar hover thumb */
#progressBar:hover #progressThumb { display:block !important; }
#progressBar:hover #progressFill { background: #fff; }

/* Player bar slide up animation */
@keyframes slideUp {
    from { transform:translateY(100%); opacity:0; }
    to   { transform:translateY(0);    opacity:1; }
}
#musicPlayerBar.visible {
    animation: slideUp .3s ease forwards;
}

/* Mobile responsive */
@media(max-width:768px){
    #musicPlayerBar { height:auto; padding:10px 12px; flex-wrap:wrap; }
    #musicPlayerBar > div:first-child { min-width:unset; }
    #musicPlayerBar > div:last-child  { display:none; }
}
</style>

<script>
/* ══════════════════════════════════════════
   GLOBAL MUSIC PLAYER
══════════════════════════════════════════ */
const audio        = document.getElementById('globalAudioPlayer');
const playerBar    = document.getElementById('musicPlayerBar');
const playerSpacer = document.getElementById('playerSpacer');

let playlist      = [];   // array of track objects
let currentIndex  = 0;
let isShuffle     = false;
let isRepeat      = false;
let wasMuted      = false;

/* ── Format seconds to m:ss ── */
function fmtTime(s) {
    if (isNaN(s)) return '0:00';
    const m = Math.floor(s / 60);
    const sec = Math.floor(s % 60);
    return m + ':' + (sec < 10 ? '0' : '') + sec;
}

/* ── Show the player bar ── */
function showPlayer() {
    playerBar.style.display   = 'flex';
    playerSpacer.style.height = '80px';
    playerBar.classList.add('visible');
    document.body.classList.add('player-active');
}

/* ── Close player ── */
function closePlayer() {
    audio.pause();
    playerBar.style.display   = 'none';
    playerSpacer.style.height = '0px';
    document.body.classList.remove('player-active');
    playlist     = [];
    currentIndex = 0;
}

/* ── Load and play a track ── */
function loadTrack(index) {
    if (!playlist.length) return;
    if (index < 0) index = playlist.length - 1;
    if (index >= playlist.length) index = 0;
    currentIndex = index;

    const track = playlist[currentIndex];

    /* Update audio source */
    audio.src = track.file;
    audio.load();
    audio.play().catch(() => {});

    /* Update UI */
    document.getElementById('playerTitle').textContent  = track.title;
    document.getElementById('playerArtist').textContent = track.artist || '—';
    document.getElementById('playPauseIcon').className  = 'bi bi-pause-fill';

    /* Update image */
    const img = document.getElementById('playerImg');
    const ph  = document.getElementById('playerImgPlaceholder');
    if (track.image) {
        img.src          = track.image;
        img.style.display      = 'block';
        ph.style.display       = 'none';
    } else {
        img.style.display      = 'none';
        ph.style.display       = 'flex';
    }

    /* Update detail links */
    const link = '/music_site/user/music_details.php?id=' + track.id;
    document.getElementById('playerDetailLink').href = link;
    document.getElementById('playerFullLink').href   = link;

    /* Highlight active card */
    document.querySelectorAll('.media-card').forEach(c => c.style.outline = '');
    if (track.cardEl) {
        track.cardEl.style.outline      = '2px solid var(--accent)';
        track.cardEl.style.outlineOffset = '2px';
    }

    showPlayer();
}

/* ── Play/Pause toggle ── */
function togglePlayPause() {
    if (audio.paused) {
        audio.play();
        document.getElementById('playPauseIcon').className = 'bi bi-pause-fill';
    } else {
        audio.pause();
        document.getElementById('playPauseIcon').className = 'bi bi-play-fill';
    }
}

/* ── Next track ── */
function playerNext() {
    if (isShuffle) {
        loadTrack(Math.floor(Math.random() * playlist.length));
    } else {
        loadTrack(currentIndex + 1);
    }
}

/* ── Previous track ── */
function playerPrev() {
    if (audio.currentTime > 3) {
        audio.currentTime = 0;
    } else {
        loadTrack(currentIndex - 1);
    }
}

/* ── Shuffle toggle ── */
function toggleShuffle() {
    isShuffle = !isShuffle;
    const btn = document.getElementById('btnShuffle');
    btn.style.color = isShuffle ? 'var(--accent)' : '#b3b3b3';
}

/* ── Repeat toggle ── */
function toggleRepeat() {
    isRepeat = !isRepeat;
    const btn = document.getElementById('btnRepeat');
    btn.style.color = isRepeat ? 'var(--accent)' : '#b3b3b3';
    audio.loop      = isRepeat;
}

/* ── Seek ── */
function seekAudio(e) {
    const bar  = document.getElementById('progressBar');
    const rect = bar.getBoundingClientRect();
    const pct  = (e.clientX - rect.left) / rect.width;
    audio.currentTime = pct * audio.duration;
}

/* ── Volume ── */
function setVolume(val) {
    audio.volume = val;
    const icon = document.getElementById('volumeIcon');
    if (val == 0)      icon.className = 'bi bi-volume-mute-fill';
    else if (val < 0.5) icon.className = 'bi bi-volume-down-fill';
    else               icon.className = 'bi bi-volume-up-fill';
}

function toggleMute() {
    if (audio.volume > 0) {
        wasMuted = audio.volume;
        audio.volume = 0;
        document.getElementById('volumeSlider').value = 0;
        document.getElementById('volumeIcon').className = 'bi bi-volume-mute-fill';
    } else {
        audio.volume = wasMuted || 1;
        document.getElementById('volumeSlider').value = audio.volume;
        document.getElementById('volumeIcon').className = 'bi bi-volume-up-fill';
    }
}

/* ── Audio events ── */
audio.addEventListener('timeupdate', function() {
    if (!audio.duration) return;
    const pct = (audio.currentTime / audio.duration) * 100;
    document.getElementById('progressFill').style.width = pct + '%';
    document.getElementById('currentTime').textContent  = fmtTime(audio.currentTime);
});

audio.addEventListener('loadedmetadata', function() {
    document.getElementById('totalTime').textContent = fmtTime(audio.duration);
});

audio.addEventListener('ended', function() {
    if (!isRepeat) playerNext();
});

audio.addEventListener('play', function() {
    document.getElementById('playPauseIcon').className = 'bi bi-pause-fill';
});

audio.addEventListener('pause', function() {
    document.getElementById('playPauseIcon').className = 'bi bi-play-fill';
});

/* ══════════════════════════════════════════
   CONNECT PLAY BUTTONS ON CARDS
   Called when page loads
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function() {
    buildPlaylist();
});

function buildPlaylist() {
    playlist = [];
    /* Find all music play buttons with data attributes */
    document.querySelectorAll('.play-track-btn').forEach(function(btn, idx) {
        const card = btn.closest('.media-card');
        playlist.push({
            id:     btn.dataset.id,
            title:  btn.dataset.title,
            artist: btn.dataset.artist,
            file:   btn.dataset.file,
            image:  btn.dataset.image,
            cardEl: card,
        });

        btn.addEventListener('click', function(e) {
            e.preventDefault();
            loadTrack(idx);
        });
    });
}

/* ── Global play function callable from anywhere ── */
window.playTrack = function(id, title, artist, file, image, cardEl) {
    /* Check if already in playlist */
    const existing = playlist.findIndex(t => t.id === id);
    if (existing !== -1) {
        loadTrack(existing);
        return;
    }
    /* Add to playlist and play */
    playlist.push({ id, title, artist, file, image, cardEl });
    loadTrack(playlist.length - 1);
};
</script>

</body>
</html>
