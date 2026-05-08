//Kullanıcı giriş sayfası
<?php
require_once __DIR__ . '/../src/core/db.php';
require_once __DIR__ . '/../src/services/auth.php';

if (girisYapildi()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$hata = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $sifre = $_POST['sifre'] ?? '';
    if ($email && $sifre) {
        $kullanici = kullaniciGiris($email, $sifre);
        if ($kullanici) {
            header('Location: ' . SITE_URL . '/index.php');
            exit;
        } else {
            $hata = 'E-posta veya şifre hatalı.';
        }
    } else {
        $hata = 'Lütfen tüm alanları doldurun.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - GurmeCepte</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/src/ui/style.css">
</head>
<body>
<?php include __DIR__ . '/../src/ui/navbar.php'; ?>

<div style="min-height:calc(100vh - 70px);display:flex;align-items:center;background:linear-gradient(135deg,#FFF5F0,var(--krem))">
    <div class="form-container" style="margin:40px auto">
        <div style="text-align:center;margin-bottom:24px">
            <span style="font-size:3rem">🍽️</span>
        </div>
        <h1 class="form-baslik">Giriş Yap</h1>
        <p class="form-altyazi">Hesabınıza erişin, siparişlerinizi takip edin</p>

        <?php if ($hata): ?>
            <div class="alert alert-hata">⚠️ <?= htmlspecialchars($hata) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-grup">
                <label>E-posta Adresi</label>
                <input type="email" name="email" placeholder="ornek@email.com" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-grup">
                <label>Şifre</label>
                <input type="password" name="sifre" placeholder="Şifreniz" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px">
                Giriş Yap →
            </button>
        </form>

        <div style="text-align:center;margin-top:24px;color:var(--gri);font-size:0.9rem">
            Hesabınız yok mu?
            <a href="<?= SITE_URL ?>/modules/kayit.php" style="color:var(--turuncu);font-weight:600;text-decoration:none">
                Üye Olun
            </a>
        </div>
        <div style="text-align:center;margin-top:12px;color:var(--gri);font-size:0.9rem">
            Restoran sahibi misiniz?
            <a href="<?= SITE_URL ?>/modules/restoran-kayit.php" style="color:var(--turuncu);font-weight:600;text-decoration:none">
                Restoran Kaydı
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../src/ui/footer.php'; ?>
<script src="<?= SITE_URL ?>/src/ui/main.js"></script>
</body>
</html>
