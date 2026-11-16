<?php
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/config.php';
}
$year = date('Y');
?>
<footer class="site-footer" id="kontak">
    <div class="container">
        <div class="footer-grid">
            <div>
                <a class="brand" href="<?= url('index.php') ?>">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 17h16"/>
                            <path d="M5 17a7 7 0 0 1 14 0"/>
                            <path d="M12 6V3"/>
                            <path d="M8.5 8.5c1-1.5 2.2-2 3.5-2s2.5.5 3.5 2"/>
                        </svg>
                    </span>
                    <span>Dapur Catering<small>Rasa Rumahan, Sajian Istimewa</small></span>
                </a>
                <p>Melayani pesanan nasi kotak, tumpeng, dan ayam geprek untuk berbagai acara dengan bahan segar dan cita rasa rumahan yang istimewa.</p>
                <div class="social-row">
                    <a href="https://wa.me/6281234567890" target="_blank" rel="noopener" aria-label="WhatsApp">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8 8 0 1 1 12 20Zm4.4-5.9c-.2-.1-1.4-.7-1.6-.8s-.4-.1-.6.1-.6.8-.8 1-.3.2-.6.1a6.5 6.5 0 0 1-1.9-1.2 7.2 7.2 0 0 1-1.3-1.7c-.1-.2 0-.4.1-.5l.4-.5a1.8 1.8 0 0 0 .3-.5.5.5 0 0 0 0-.5c0-.1-.6-1.4-.8-1.9s-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3A2.9 2.9 0 0 0 8 9.3a5 5 0 0 0 1 2.7 11.4 11.4 0 0 0 4.4 3.8c1.6.6 1.6.4 1.9.4a2.5 2.5 0 0 0 1.7-1.2 2.1 2.1 0 0 0 .1-1.2c0-.1-.2-.1-.4-.2Z"/></svg>
                    </a>
                    <a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
                    </a>
                    <a href="https://facebook.com" target="_blank" rel="noopener" aria-label="Facebook">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H7v3h3v6h3v-6h3l1-3h-4v-2c0-.6.4-1 1-1Z"/></svg>
                    </a>
                </div>
            </div>

            <div>
                <h4>Menu</h4>
                <ul class="footer-links">
                    <li><a href="<?= url('menu.php') ?>">Semua Menu</a></li>
                    <li><a href="<?= url('menu.php?category=nasi-kotak') ?>">Nasi Kotak</a></li>
                    <li><a href="<?= url('menu.php?category=tumpeng') ?>">Tumpeng</a></li>
                    <li><a href="<?= url('menu.php?category=ayam-geprek') ?>">Ayam Geprek</a></li>
                </ul>
            </div>

            <div>
                <h4>Informasi</h4>
                <ul class="footer-links">
                    <li><a href="<?= url('index.php') ?>#tentang">Tentang Kami</a></li>
                    <li><a href="<?= url('index.php') ?>#keunggulan">Keunggulan</a></li>
                    <li><a href="<?= url('login.php') ?>">Login Pelanggan</a></li>
                    <li><a href="<?= url('register.php') ?>">Daftar Akun</a></li>
                    <li><a href="<?= url('admin/login.php') ?>">Login Admin</a></li>
                </ul>
            </div>

            <div>
                <h4>Hubungi Kami</h4>
                <ul class="footer-contact">
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                        <span>Jl. Rasa Lezat No. 12, Kota Anda</span>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.4 2.1L8.1 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.6 2Z"/></svg>
                        <a href="https://wa.me/6281234567890" style="color:inherit">0812-3456-7890</a>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        <a href="mailto:halo@dapurcatering.id" style="color:inherit">halo@dapurcatering.id</a>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        <span>Setiap hari, 07.00 - 21.00 WIB</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; <?= $year ?> <?= e(APP_NAME) ?>. Semua hak dilindungi.</span>
            <span>Dibuat untuk memudahkan pemesanan catering Anda.</span>
        </div>
    </div>
</footer>

<div class="toast-wrap" aria-live="polite"></div>
<script src="<?= url('assets/js/main.js') ?>"></script>
</body>
</html>
