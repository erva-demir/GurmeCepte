<?php
require_once __DIR__ . '/../src/core/db.php';
require_once __DIR__ . '/../src/services/restoran.php';

if (!girisYapildi() || !restoranGirisi()) {
    header('Location: ' . SITE_URL . '/modules/giris.php');
    exit;
}

$restoran = kullanicininRestorani($_SESSION['kullanici_id']);
if (!$restoran) {
    header('Location: ' . SITE_URL . '/modules/restoran-kayit.php');
    exit;
}

$db = getDB();
$hata = '';
$basari = '';
$aktifSayfa = $_GET['sayfa'] ?? 'panel';

// Yemek ekle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['yemek_ekle'])) {
    $isim = trim($_POST['isim'] ?? '');
    $aciklama = trim($_POST['aciklama'] ?? '');
    $fiyat = floatval($_POST['fiyat'] ?? 0);
    $kategori_id = intval($_POST['kategori_id'] ?? 0);
    if ($isim && $fiyat > 0) {
        yemekEkle($restoran['id'], $kategori_id, $isim, $aciklama, $fiyat);
        $basari = 'Yemek başarıyla eklendi!';
    } else {
        $hata = 'Lütfen isim ve fiyat girin.';
    }
}

// Fiyat güncelle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fiyat_guncelle'])) {
    $yemek_id = intval($_POST['yemek_id']);
    $yeni_fiyat = floatval($_POST['yeni_fiyat']);
    if ($yeni_fiyat > 0) {
        yemekFiyatGuncelle($yemek_id, $yeni_fiyat, $restoran['id']);
        $basari = 'Fiyat güncellendi!';
    }
}

// Malzeme ekle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['malzeme_ekle'])) {
    $yemek_id = intval($_POST['yemek_id']);
    $isim = trim($_POST['malzeme_isim'] ?? '');
    $cikarilabilir = isset($_POST['cikarilabilir']) ? 1 : 0;
    if ($isim) {
        $db->prepare("INSERT INTO malzemeler (yemek_id, isim, cikarilabilir) VALUES (?,?,?)")->execute([$yemek_id, $isim, $cikarilabilir]);
        $basari = 'Malzeme eklendi!';
    }
}

$yemekler = restoranYemekleri($restoran['id']);
$kategoriler = tumKategoriler();

// Sipariş istatistikleri
$stmt = $db->prepare("SELECT COUNT(*) as sayi, SUM(toplam_fiyat) as toplam FROM siparisler WHERE restoran_id = ?");
$stmt->execute([$restoran['id']]);
$istatistik = $stmt->fetch();

$stmt2 = $db->prepare("SELECT COUNT(*) as bekleyen FROM siparisler WHERE restoran_id = ? AND durum = 'beklemede'");
$stmt2->execute([$restoran['id']]);
$bekleyen = $stmt2->fetch();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restoran Panel - GurmeCepte</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/src/ui/style.css">
</head>
<body>
<?php include __DIR__ . '/../src/ui/navbar.php'; ?>

<div class="panel-grid">
    <!-- SIDEBAR -->
    <div class="panel-sidebar">
        <div class="panel-sidebar-baslik">
            🏪 <?= htmlspecialchars($restoran['isim']) ?>
        </div>
        <a href="?sayfa=panel" class="panel-nav-link <?= $aktifSayfa === 'panel' ? 'aktif' : '' ?>">
            📊 Genel Bakış
        </a>
        <a href="?sayfa=menu" class="panel-nav-link <?= $aktifSayfa === 'menu' ? 'aktif' : '' ?>">
            🍽️ Menü Yönetimi
        </a>
        <a href="?sayfa=yemek-ekle" class="panel-nav-link <?= $aktifSayfa === 'yemek-ekle' ? 'aktif' : '' ?>">
            ➕ Yemek Ekle
        </a>
        <a href="?sayfa=siparisler" class="panel-nav-link <?= $aktifSayfa === 'siparisler' ? 'aktif' : '' ?>">
            📦 Siparişler
        </a>
        <a href="<?= SITE_URL ?>/modules/restoran.php?id=<?= $restoran['id'] ?>" class="panel-nav-link">
            👁️ Restorana Git
        </a>
    </div>

    <!-- İÇERİK -->
    <div class="panel-icerik">

        <?php if ($hata): ?>
            <div class="alert alert-hata">⚠️ <?= $hata ?></div>
        <?php endif; ?>
        <?php if ($basari): ?>
            <div class="alert alert-basari">✅ <?= $basari ?></div>
        <?php endif; ?>

        <?php if ($aktifSayfa === 'panel'): ?>
            <!-- GENEL BAKIŞ -->
            <h2 style="font-family:'Playfair Display',serif;font-size:1.8rem;margin-bottom:24px">📊 Genel Bakış</h2>

            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
                <div class="panel-kart" style="text-align:center">
                    <div style="font-size:2rem;margin-bottom:8px">📦</div>
                    <div style="font-size:2rem;font-weight:700;color:var(--turuncu)"><?= $istatistik['sayi'] ?? 0 ?></div>
                    <div style="font-size:0.85rem;color:var(--gri)">Toplam Sipariş</div>
                </div>
                <div class="panel-kart" style="text-align:center">
                    <div style="font-size:2rem;margin-bottom:8px">⏳</div>
                    <div style="font-size:2rem;font-weight:700;color:var(--altin)"><?= $bekleyen['bekleyen'] ?? 0 ?></div>
                    <div style="font-size:0.85rem;color:var(--gri)">Bekleyen</div>
                </div>
                <div class="panel-kart" style="text-align:center">
                    <div style="font-size:2rem;margin-bottom:8px">💰</div>
                    <div style="font-size:1.5rem;font-weight:700;color:var(--yesil)">₺<?= number_format($istatistik['toplam'] ?? 0, 0) ?></div>
                    <div style="font-size:0.85rem;color:var(--gri)">Toplam Gelir</div>
                </div>
                <div class="panel-kart" style="text-align:center">
                    <div style="font-size:2rem;margin-bottom:8px">⭐</div>
                    <div style="font-size:2rem;font-weight:700;color:var(--altin)"><?= number_format($restoran['puan'], 1) ?></div>
                    <div style="font-size:0.85rem;color:var(--gri)">Ortalama Puan</div>
                </div>
            </div>

        <?php elseif ($aktifSayfa === 'menu'): ?>
            <!-- MENÜ YÖNETİMİ -->
            <h2 style="font-family:'Playfair Display',serif;font-size:1.8rem;margin-bottom:24px">🍽️ Menü Yönetimi</h2>

            <?php if (empty($yemekler)): ?>
                <div class="panel-kart">
                    <div class="bos-durum">
                        <div class="emoji">🍽️</div>
                        <h3>Henüz menü eklenmemiş</h3>
                        <a href="?sayfa=yemek-ekle" class="btn btn-primary" style="margin-top:16px">Yemek Ekle</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="panel-kart">
                    <table>
                        <thead>
                            <tr>
                                <th>Yemek</th>
                                <th>Kategori</th>
                                <th>Fiyat</th>
                                <th>Güncelle</th>
                                <th>Malzeme Ekle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($yemekler as $y): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:600"><?= htmlspecialchars($y['isim']) ?></div>
                                        <div style="font-size:0.8rem;color:var(--gri)"><?= htmlspecialchars(substr($y['aciklama'] ?? '', 0, 50)) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($y['kategori_isim'] ?? '-') ?></td>
                                    <td>
                                        <form method="POST" style="display:flex;gap:6px;align-items:center">
                                            <input type="hidden" name="yemek_id" value="<?= $y['id'] ?>">
                                            <input type="number" name="yeni_fiyat" value="<?= $y['fiyat'] ?>"
                                                   step="0.01" min="0"
                                                   style="width:80px;padding:6px 8px;border:1px solid #ddd;border-radius:6px;font-size:0.85rem">
                                            <button type="submit" name="fiyat_guncelle" class="btn btn-sm btn-primary">✓</button>
                                        </form>
                                    </td>
                                    <td>
                                        <span style="font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:var(--turuncu)">
                                            ₺<?= number_format($y['fiyat'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" onclick="toggleMalzemeForm(<?= $y['id'] ?>)">
                                            + Malzeme
                                        </button>
                                        <div id="malzeme-form-<?= $y['id'] ?>" style="display:none;margin-top:8px">
                                            <form method="POST" style="display:flex;flex-direction:column;gap:6px">
                                                <input type="hidden" name="yemek_id" value="<?= $y['id'] ?>">
                                                <input type="text" name="malzeme_isim" placeholder="Malzeme adı"
                                                       style="padding:6px 10px;border:1px solid #ddd;border-radius:6px;font-size:0.85rem">
                                                <label style="font-size:0.8rem;display:flex;align-items:center;gap:4px">
                                                    <input type="checkbox" name="cikarilabilir" checked> Müşteri çıkarabilir
                                                </label>
                                                <button type="submit" name="malzeme_ekle" class="btn btn-sm btn-primary">Ekle</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        <?php elseif ($aktifSayfa === 'yemek-ekle'): ?>
            <!-- YEMEK EKLE -->
            <h2 style="font-family:'Playfair Display',serif;font-size:1.8rem;margin-bottom:24px">➕ Yeni Yemek Ekle</h2>

            <div class="panel-kart" style="max-width:600px">
                <form method="POST">
                    <div class="form-grup">
                        <label>Yemek Adı *</label>
                        <input type="text" name="isim" placeholder="Örn: Adana Kebap" required>
                    </div>
                    <div class="form-grup">
                        <label>Açıklama</label>
                        <textarea name="aciklama" rows="3" placeholder="Yemeğin kısa açıklaması..."></textarea>
                    </div>
                    <div class="form-grid">
                        <div class="form-grup">
                            <label>Fiyat (₺) *</label>
                            <input type="number" name="fiyat" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="form-grup">
                            <label>Kategori</label>
                            <select name="kategori_id">
                                <option value="">Kategori Seçin</option>
                                <?php foreach ($kategoriler as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['isim']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="yemek_ekle" class="btn btn-primary btn-lg">
                        ➕ Yemek Ekle
                    </button>
                </form>
            </div>

        <?php elseif ($aktifSayfa === 'siparisler'): ?>
            <!-- SİPARİŞLER -->
            <h2 style="font-family:'Playfair Display',serif;font-size:1.8rem;margin-bottom:24px">📦 Siparişler</h2>

            <?php
            $stmt = $db->prepare("SELECT s.*, k.ad, k.soyad, k.telefon FROM siparisler s JOIN kullanicilar k ON s.kullanici_id = k.id WHERE s.restoran_id = ? ORDER BY s.olusturma_tarihi DESC");
            $stmt->execute([$restoran['id']]);
            $siparisler = $stmt->fetchAll();

            $durumRenk = ['beklemede'=>'badge-sari','hazirlaniyor'=>'badge-mavi','yolda'=>'badge-yesil','teslim_edildi'=>'badge-yesil','iptal'=>'badge-kirmizi'];
            $durumText = ['beklemede'=>'Beklemede','hazirlaniyor'=>'Hazırlanıyor','yolda'=>'Yolda','teslim_edildi'=>'Teslim Edildi','iptal'=>'İptal'];

            if (isset($_POST['durum_guncelle'])) {
                $s_id = intval($_POST['siparis_id']);
                $yeni_durum = $_POST['yeni_durum'];
                $db->prepare("UPDATE siparisler SET durum = ? WHERE id = ? AND restoran_id = ?")->execute([$yeni_durum, $s_id, $restoran['id']]);
                header('Location: ?sayfa=siparisler');
                exit;
            }
            ?>

            <?php if (empty($siparisler)): ?>
                <div class="panel-kart">
                    <div class="bos-durum"><div class="emoji">📦</div><h3>Henüz sipariş yok</h3></div>
                </div>
            <?php else: ?>
                <div class="panel-kart">
                    <table>
                        <thead>
                            <tr><th>#</th><th>Müşteri</th><th>Tutar</th><th>Durum</th><th>Tarih</th><th>İşlem</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($siparisler as $s): ?>
                                <tr>
                                    <td>#<?= $s['id'] ?></td>
                                    <td>
                                        <div style="font-weight:600"><?= htmlspecialchars($s['ad'] . ' ' . $s['soyad']) ?></div>
                                        <div style="font-size:0.8rem;color:var(--gri)"><?= htmlspecialchars($s['teslimat_adres'] ?? $s['teslimat_adresi'] ?? '') ?></div>
                                    </td>
                                    <td style="font-weight:700;color:var(--turuncu)">₺<?= number_format($s['toplam_fiyat'], 2) ?></td>
                                    <td><span class="badge <?= $durumRenk[$s['durum']] ?? '' ?>"><?= $durumText[$s['durum']] ?? $s['durum'] ?></span></td>
                                    <td style="font-size:0.85rem"><?= date('d.m H:i', strtotime($s['olusturma_tarihi'])) ?></td>
                                    <td>
                                        <form method="POST" style="display:flex;gap:6px">
                                            <input type="hidden" name="siparis_id" value="<?= $s['id'] ?>">
                                            <select name="yeni_durum" style="padding:4px 8px;border:1px solid #ddd;border-radius:6px;font-size:0.8rem">
                                                <option value="beklemede" <?= $s['durum']==='beklemede'?'selected':'' ?>>Beklemede</option>
                                                <option value="hazirlaniyor" <?= $s['durum']==='hazirlaniyor'?'selected':'' ?>>Hazırlanıyor</option>
                                                <option value="yolda" <?= $s['durum']==='yolda'?'selected':'' ?>>Yolda</option>
                                                <option value="teslim_edildi" <?= $s['durum']==='teslim_edildi'?'selected':'' ?>>Teslim Edildi</option>
                                                <option value="iptal" <?= $s['durum']==='iptal'?'selected':'' ?>>İptal</option>
                                            </select>
                                            <button type="submit" name="durum_guncelle" class="btn btn-sm btn-primary">✓</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</div>

<?php include __DIR__ . '/../src/ui/footer.php'; ?>
<script src="<?= SITE_URL ?>/src/ui/main.js"></script>
<script>
function toggleMalzemeForm(id) {
    const f = document.getElementById('malzeme-form-' + id);
    f.style.display = f.style.display === 'none' ? 'block' : 'none';
}
</script>
</body>
</html>
