<?php
if (!function_exists('girisYapildi')) {
    require_once __DIR__ . '/../core/db.php';
}
$oturum = oturumKullanici();
?>
<nav class="navbar">
    <a href="<?= SITE_URL ?>/index.php" class="logo">Gurme<span>Cepte</span></a>
    <ul class="nav-links">
        <li><a href="<?= SITE_URL ?>/index.php">Ana Sayfa</a></li>
        <li><a href="<?= SITE_URL ?>/modules/restoranlar.php">Restoranlar</a></li>
        <?php if (girisYapildi()): ?>
            <li><a href="<?= SITE_URL ?>/modules/siparislerim.php">Siparişlerim</a></li>
            <li>
                <a href="<?= SITE_URL ?>/modules/profilim.php" style="color:var(--altin)">
                    🏆 <?= htmlspecialchars($oturum['kupon_puani'] ?? 0) ?> Puan
                </a>
            </li>
            <?php if (restoranGirisi()): ?>
                <li><a href="<?= SITE_URL ?>/modules/restoran-panel.php">Panel</a></li>
            <?php endif; ?>
            <li><a href="<?= SITE_URL ?>/modules/cikis.php">Çıkış</a></li>
        <?php else: ?>
            <li><a href="<?= SITE_URL ?>/modules/giris.php">Giriş Yap</a></li>
            <li><a href="<?= SITE_URL ?>/modules/kayit.php" class="btn-nav">Üye Ol</a></li>
        <?php endif; ?>
        <li>
            <a href="#" class="sepet-ac-btn" style="color:white;background:rgba(255,255,255,0.1);padding:8px 16px;border-radius:10px;display:flex;align-items:center;gap:8px;text-decoration:none">
                🛒 Sepet
                <span class="sepet-sayi" style="background:var(--turuncu);color:white;border-radius:50%;width:20px;height:20px;font-size:0.75rem;display:none;align-items:center;justify-content:center;font-weight:700">0</span>
            </a>
        </li>
    </ul>
</nav>

<!-- SEPET PANELİ -->
<div class="sepet-overlay" id="sepet-overlay"></div>
<div class="sepet-panel" id="sepet-panel">
    <div class="sepet-header">
        <h3>🛒 Sepetim</h3>
        <button class="sepet-kapat" id="sepet-kapat">✕</button>
    </div>
    <div class="sepet-items" id="sepet-items">
        <div class="bos-durum" style="padding:40px 20px">
            <div class="emoji">🛒</div>
            <h3>Sepetiniz boş</h3>
            <p>Lezzetli yemekler ekleyin!</p>
        </div>
    </div>
    <div class="sepet-toplam">
        <div class="toplam-satir">
            <span>Ara toplam</span>
            <span id="sepet-toplam-fiyat">₺0.00</span>
        </div>
        <div class="toplam-satir" style="color:var(--gri);font-size:0.8rem">
            <span>Teslimat ücreti</span>
            <span>Restoranda belirlenir</span>
        </div>
        <hr style="margin:12px 0;border:none;border-top:1px solid #eee">
        <a href="<?= SITE_URL ?>/modules/odeme.php" class="btn btn-primary btn-full" style="margin-top:8px">
            Siparişi Tamamla →
        </a>
    </div>
</div>
