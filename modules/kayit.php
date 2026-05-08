<?php
require_once __DIR__ . '/../src/core/db.php';
require_once __DIR__ . '/../src/services/auth.php';

if (girisYapildi()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$hata = '';
$basari = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = trim($_POST['ad'] ?? '');
    $soyad = trim($_POST['soyad'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sifre = $_POST['sifre'] ?? '';
    $sifre2 = $_POST['sifre2'] ?? '';
    $telefon = trim($_POST['telefon'] ?? '');

    if (!$ad || !$soyad || !$email || !$sifre) {
        $hata = 'Lütfen tüm alanları doldurun.';
    } elseif ($sifre !== $sifre2) {
        $hata = 'Şifreler eşleşmiyor.';
    } elseif (strlen($sifre) < 6) {
        $hata = 'Şifre en az 6 karakter olmalıdır.';
    } else {
        try {
            $r = kullaniciKayit($ad, $soyad, $email, $sifre, $telefon);
            if ($r) {
                kullaniciGiris($email, $sifre);
                header('Location: ' . SITE_URL . '/index.php');
                exit;
            }
        } catch (Exception $e) {
            $hata = 'Bu e-posta adresi zaten kayıtlı.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üye Ol - GurmeCepte</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/src/ui/style.css">
</head>
<body>
<?php include __DIR__ . '/../src/ui/navbar.php'; ?>

<div style="min-height:calc(100vh - 70px);display:flex;align-items:center;background:linear-gradient(135deg,#FFF5F0,var(--krem));padding:40px 0">
    <div class="form-container" style="margin:0 auto;max-width:520px">
        <div style="text-align:center;margin-bottom:24px">
            <span style="font-size:3rem">🏆</span>
        </div>
        <h1 class="form-baslik">Üye Ol</h1>
        <p class="form-altyazi">Hemen üye olun, sipariş verin ve puan kazanın!</p>

        <?php if ($hata): ?>
            <div class="alert alert-hata">⚠️ <?= htmlspecialchars($hata) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-grid">
                <div class="form-grup">
                    <label>Ad</label>
                    <input type="text" name="ad" placeholder="Adınız" required
                           value="<?= htmlspecialchars($_POST['ad'] ?? '') ?>">
                </div>
                <div class="form-grup">
                    <label>Soyad</label>
                    <input type="text" name="soyad" placeholder="Soyadınız" required
                           value="<?= htmlspecialchars($_POST['soyad'] ?? '') ?>">
                </div>
            </div>
            <div class="form-grup">
                <label>E-posta Adresi</label>
                <input type="email" name="email" placeholder="ornek@email.com" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-grup">
                <label>Telefon</label>
                <input type="tel" name="telefon" placeholder="05XX XXX XX XX"
                       value="<?= htmlspecialchars($_POST['telefon'] ?? '') ?>">
            </div>
            <div class="form-grid">
                <div class="form-grup">
                    <label>Şifre</label>
                    <input type="password" name="sifre" placeholder="En az 6 karakter" required>
                </div>
                <div class="form-grup">
                    <label>Şifre Tekrar</label>
                    <input type="password" name="sifre2" placeholder="Şifrenizi tekrar girin" required>
                </div>
            </div>

            <div style="background:#FFF5F0;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:0.83rem;color:var(--gri);border:1px solid #FFD0C0">
                🏆 Üye olarak her siparişte puan kazanacaksınız! 100 puan = 10₺ indirim kuponu.
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">
                Üye Ol &amp; Kazanmaya Başla →
            </button>
        </form>

        <div style="text-align:center;margin-top:24px;color:var(--gri);font-size:0.9rem">
            Zaten üye misiniz?
            <a href="<?= SITE_URL ?>/modules/giris.php" style="color:var(--turuncu);font-weight:600;text-decoration:none">
                Giriş Yapın
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../src/ui/footer.php'; ?>
<script src="<?= SITE_URL ?>/src/ui/main.js"></script>
</body>
</html>
