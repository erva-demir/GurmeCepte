<?php
require_once __DIR__ . '/../src/core/db.php';
require_once __DIR__ . '/../src/services/restoran.php';

$kategori = $_GET['kategori'] ?? null;
$restoranlar = tumRestoranlar($kategori);
$kategoriler = tumKategoriler();

$emojiler = [
    'Pizza' => '🍕', 'Burger' => '🍔', 'Lahmacun' => '🫓', 'Kebap' => '🥙',
    'Döner' => '🌯', 'Sushi' => '🍱', 'Tatlı' => '🍰', 'İçecek' => '🥤',
    'Pide' => '🫓', 'Salata' => '🥗'
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restoranlar - GurmeCepte</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/src/ui/style.css">
</head>
<body>
<?php include __DIR__ . '/../src/ui/navbar.php'; ?>

<div style="background:linear-gradient(135deg,var(--koyu),#3D1100);padding:40px 5%;color:white;text-align:center">
    <h1 style="font-family:'Playfair Display',serif;font-size:2.5rem;margin-bottom:12px">
        Tüm <span style="color:var(--turuncu)">Restoranlar</span>
    </h1>
    <p style="color:#aaa">Lezzetli seçenekler arasından tercih yapın</p>
    <div style="max-width:500px;margin:20px auto 0;display:flex;gap:10px">
        <input type="text" id="arama-input" placeholder="Restoran veya kategori ara..."
               style="flex:1;padding:12px 18px;border-radius:10px;border:none;font-size:0.95rem;outline:none">
        <button class="btn btn-primary">🔍</button>
    </div>
</div>

<div style="padding:30px 5%;max-width:1300px;margin:0 auto">
    <!-- Kategori filtresi -->
    <div class="kategori-grid" style="margin-bottom:32px">
        <a href="?kategori=" class="kategori-kart <?= !$kategori ? 'aktif' : '' ?>" data-kategori="tumu">
            <div class="emoji">🍽️</div>
            <div class="isim">Tümü</div>
        </a>
        <?php foreach ($kategoriler as $kat): ?>
            <a href="?kategori=<?= urlencode($kat['isim']) ?>"
               class="kategori-kart <?= $kategori === $kat['isim'] ? 'aktif' : '' ?>"
               data-kategori="<?= htmlspecialchars($kat['isim']) ?>">
                <div class="emoji"><?= $kat['icon'] ?></div>
                <div class="isim"><?= htmlspecialchars($kat['isim']) ?></div>
            </a>
        <?php endforeach; ?>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem">
            <?= $kategori ? htmlspecialchars($kategori) . ' Restoranları' : 'Tüm Restoranlar' ?>
            <span style="font-size:1rem;color:var(--gri);font-family:'DM Sans',sans-serif">(<?= count($restoranlar) ?>)</span>
        </h2>
    </div>

    <?php if (empty($restoranlar)): ?>
        <div class="bos-durum">
            <div class="emoji">🏪</div>
            <h3>Bu kategoride restoran bulunamadı</h3>
            <p>Başka bir kategori deneyin</p>
            <a href="restoranlar.php" class="btn btn-primary" style="margin-top:16px">Tümünü Göster</a>
        </div>
    <?php else: ?>
        <div class="restoran-grid">
            <?php foreach ($restoranlar as $r): ?>
                <a href="<?= SITE_URL ?>/modules/restoran.php?id=<?= $r['id'] ?>"
                   class="restoran-kart"
                   data-kategori-restoran="<?= htmlspecialchars($r['kategori']) ?>"
                   data-restoran-isim="<?= htmlspecialchars($r['isim']) ?>">
                    <div class="restoran-kapak">
                        <?= $emojiler[$r['kategori']] ?? '🍽️' ?>
                        <div class="restoran-kapak-overlay"></div>
                        <span class="restoran-badge">⭐ <?= number_format($r['puan'], 1) ?></span>
                    </div>
                    <div class="restoran-bilgi">
                        <div class="restoran-isim"><?= htmlspecialchars($r['isim']) ?></div>
                        <div class="restoran-aciklama"><?= htmlspecialchars(substr($r['aciklama'], 0, 90)) ?>...</div>
                        <div class="restoran-meta">
                            <span class="meta-item">📍 <?= htmlspecialchars($r['adres']) ?></span>
                        </div>
                        <div class="restoran-meta" style="margin-top:6px">
                            <span class="meta-item">⏰ <?= $r['teslimat_suresi'] ?></span>
                            <span class="meta-item">💬 <?= $r['yorum_sayisi'] ?> yorum</span>
                            <span class="meta-item">🚚 ₺<?= number_format($r['teslimat_ucreti'], 2) ?></span>
                        </div>
                        <div style="margin-top:10px">
                            <span class="badge badge-sari"><?= htmlspecialchars($r['kategori']) ?></span>
                            <?php if ($r['min_siparis'] > 0): ?>
                                <span class="badge" style="background:#F0F8FF;color:#2980B9;margin-left:6px">
                                    Min: ₺<?= number_format($r['min_siparis'], 0) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../src/ui/footer.php'; ?>
<script src="<?= SITE_URL ?>/src/ui/main.js"></script>
</body>
</html>
