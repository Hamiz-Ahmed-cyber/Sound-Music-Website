</main><!-- /admin-main -->

<style>
.admin-footer {
    text-align: center;
    padding: 18px 28px;
    margin-left: var(--sidebar-w);
    border-top: 1px solid var(--border);
    font-size: .78rem; color: var(--text-sub);
    background: var(--bg-base);
    transition: margin-left .3s;
}
.admin-footer span { color: var(--accent); }
@media(max-width:991px){ .admin-footer { margin-left:0; } }
</style>

<footer class="admin-footer">
    &copy; <?= date('Y') ?> Sound Admin Panel &mdash;
    Built with <span>♥</span> &mdash;
    Logged in as <span><?= htmlspecialchars($_SESSION['name']) ?></span>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
