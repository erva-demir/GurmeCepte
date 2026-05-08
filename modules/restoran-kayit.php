<?php
require_once __DIR__ . '/../src/core/db.php';
require_once __DIR__ . '/../src/services/auth.php';

$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = trim($_POST['ad'] ?? '');
    $soyad = trim($_POST['soyad'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sifre = $_POST['sifre'] ?? '';
    $sifre2 = $_POST['sifre2'] ?? '';
    $telefon = trim($_POST['telefon'] ?? '');
    $restoran_isim = trim($_POST['restoran_isim'] ?? '');
    $restoran_adres = trim($_POST['restoran_adres'] ?? '');
    $restoran_telefon = trim($_POST['restoran_telefon'] ?? '');
    $restoran_aciklama = trim($_POST['restoran_aciklama'] ?? '');
    $restoran_kategori = $_POST['restoran_kategori'] ?? '';
    $min_siparis = floatval($_POST['min_siparis'] ?? 0);
    $teslimat_ucreti = floatval($_POST['teslimat_ucreti'] ?? 0);
    $teslimat_suresi = trim($_POST['teslimat_suresi'] ?? '');

    if (!$ad || !$email || !$sifre || !$restoran_isim || !$restoran_adres) {
        $hata = 'Lütfen zorunlu alanları doldurun.';
    } elseif ($sifre !== $sifre2) {
        $hata = 'Şifreler eşleşmiyor.';
    } else {
        try {
            $db = getDB();
            $sifreHash = password_hash($sifre, PASSWORD_BCRYPT);
            $db->prepare("INSERT INTO kullanicilar (ad, soyad, email, sifre, telefon, rol) VALUES (?,?,?,?,?,'restoran')")->execute([$ad, $soyad, $email, $sifreHash, $telefon]);
            $uid = $db->lastInsertId();
            $db->prepare("INSERT INTO restoranlar (kullanici_id, isim, aciklama, adres, telefon, kategori, min_siparis, teslimat_ucreti, teslimat_suresi) VALUES (?,?,?,?,?,?,?,?,?)")->execute([$uid, $restoran_isim, $restoran_aciklama, $restoran_adres, $restoran_telefon, $restoran_kategori, $min_siparis, $teslimat_ucreti, $teslimat_suresi]);
            kullaniciGiris($email, $sifre);
            header('Location: ' . SITE_URL . '/modules/restoran-panel.php');
            exit;
        } catch (Exception $e) {
            $hata = 'Bu e-posta adresi zaten kayıtlı veya bir hata oluştu.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restoran Kaydı - GurmeCepte</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/src/ui/style.css">
</head>
<body>
<?php include __DIR__ . '/../src/ui/navbar.php'; ?>

<div style="background:linear-gradient(135deg,var(--koyu),#3D1100);padding:40px 5%;color:white;text-align:center">
    <h1 style="font-family:'Playfair Display',serif;font-size:2.5rem">🏪 Restoranınızı Açın</h1>
    <p style="color:#aaa;margin-top:8px">GurmeCepte'ye katılın, binlerce müşteriye ulaşın</p>
</div>

<div style="max-width:700px;margin:40px auto;padding:0 20px 40px">

    <?php if ($hata): ?>
        <div class="alert alert-hata">⚠️ <?= htmlspecialchars($hata) ?></div>
    <?php endif; ?>

    <form method="POST">
        <!-- Kişisel Bilgiler -->
        <div class="panel-kart" style="margin-bottom:20px">
            <h3>👤 Hesap Bilgileri</h3>
            <div class="form-grid">
                <div class="form-grup">
                    <label>Ad *</label>
                    <input type="text" name="ad" required value="<?= htmlspecialchars($_POST['ad'] ?? '') ?>">
                </div>
                <div class="form-grup">
                    <label>Soyad</label>
                    <input type="text" name="soyad" value="<?= htmlspecialchars($_POST['soyad'] ?? '') ?>">
                </div>
            </div>
            <div class="form-grup">
                <label>E-posta *</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-grup">
                <label>Telefon</label>
                <input type="tel" name="telefon" value="<?= htmlspecialchars($_POST['telefon'] ?? '') ?>">
            </div>
            <div class="form-grid">
                <div class="form-grup">
                    <label>Şifre *</label>
                    <input type="password" name="sifre" required>
                </div>
                <div class="form-grup">
                    <label>Şifre Tekrar *</label>
                    <input type="password" name="sifre2" required>
                </div>
            </div>
        </div>

        <!-- Restoran Bilgileri -->
        <div class="panel-kart">
            <h3>🏪 Restoran Bilgileri</h3>
            <div class="form-grup">
                <label>Restoran Adı *</label>
                <input type="text" name="restoran_isim" required placeholder="Örn: Kebapçı Mehmet Usta" value="<?= htmlspecialchars($_POST['restoran_isim'] ?? '') ?>">
            </div>
            <div class="form-grup">
                <label>Açıklama</label>
                <textarea name="restoran_aciklama" rows="3" placeholder="Restoranınızı kısaca tanıtın..."><?= htmlspecialchars($_POST['restoran_aciklama'] ?? '') ?></textarea>
            </div>
            <div class="form-grup">
                <label>Adres *</label>
                <input type="text" name="restoran_adres" required placeholder="Mahalle, ilçe, şehir" value="<?= htmlspecialchars($_POST['restoran_adres'] ?? '') ?>">
            </div>
            <div class="form-grid">
                <div class="form-grup">
                    <label>Restoran Telefon</label>
                    <input type="tel" name="restoran_telefon" value="<?= htmlspecialchars($_POST['restoran_telefon'] ?? '') ?>">
                </div>
                <div class="form-grup">
                    <label>Kategori</label>
                    <select name="restoran_kategori">
                        <option value="">Seçin</option>
                        <option value="Pizza">🍕 Pizza</option>
                        <option value="Burger">🍔 Burger</option>
                        <option value="Lahmacun">🫓 Lahmacun</option>
                        <option value="Kebap">🥙 Kebap</option>
                        <option value="Döner">🌯 Döner</option>
                        <option value="Sushi">🍱 Sushi</option>
                        <option value="Tatlı">🍰 Tatlı</option>
                        <option value="Pide">🫓 Pide</option>
                        <option value="Salata">🥗 Salata</option>
                    </select>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-grup">
                    <label>Minimum Sipariş (₺)</label>
                    <input type="number" name="min_siparis" step="1" min="0" value="<?= $_POST['min_siparis'] ?? 0 ?>">
                </div>
                <div class="form-grup">
                    <label>Teslimat Ücreti (₺)</label>
                    <input type="number" name="teslimat_ucreti" step="0.5" min="0" value="<?= $_POST['teslimat_ucreti'] ?? 10 ?>">
                </div>
            </div>
            <div class="form-grup">
                <label>Teslimat Süresi</label>
                <input type="text" name="teslimat_suresi" placeholder="Örn: 30-45 dk" value="<?= htmlspecialchars($_POST['teslimat_suresi'] ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">
                🏪 Restoranı Kaydet →
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../src/ui/footer.php'; ?>
<script src="<?= SITE_URL ?>/src/ui/main.js"></script>
</body>
</html>
