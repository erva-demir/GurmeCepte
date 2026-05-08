<?php
require_once __DIR__ . '/../src/core/db.php';
require_once __DIR__ . '/../src/services/siparis.php';
require_once __DIR__ . '/../src/data/sehirler.php';

if (!girisYapildi()) { header('Location: ' . SITE_URL . '/modules/giris.php'); exit; }

$db = getDB();
$hata = ''; $basari = '';

// Adres güncelle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adres_guncelle'])) {
    $sehir = trim($_POST['sehir'] ?? '');
    $ilce  = trim($_POST['ilce'] ?? '');
    $adres = trim($_POST['adres_detay'] ?? '');
    $tel   = trim($_POST['telefon'] ?? '');
    if ($sehir && $ilce) {
        $db->prepare("UPDATE kullanicilar SET sehir=?, ilce=?, adres_detay=?, telefon=? WHERE id=?")
           ->execute([$sehir, $ilce, $adres, $tel, $_SESSION['kullanici_id']]);
        $_SESSION['sehir'] = $sehir;
        $_SESSION['ilce']  = $ilce;
        $basari = 'Adresiniz kaydedildi!';
    } else { $hata = 'Şehir ve ilçe zorunlu.'; }
}

// Kupon oluştur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kupon_olustur'])) {
    $puan = intval($_POST['puan_kullan'] ?? 100);
    if ($puan % 100 !== 0 || $puan < 100) { $hata = 'Puan 100\'ün katları olmalıdır.'; }
    else {
        $kod = kuponeVur($_SESSION['kullanici_id'], $puan);
        if ($kod) $basari = "Kupon kodunuz: <strong style='letter-spacing:2px'>$kod</strong> — ₺" . floor($puan/100)*10 . " indirim!";
        else $hata = 'Yeterli puanınız yok.';
    }
}

// Güncel veri
$stmt = $db->prepare("SELECT * FROM kullanicilar WHERE id=?");
$stmt->execute([$_SESSION['kullanici_id']]);
$kullanici = $stmt->fetch();
$puan = $kullanici['kupon_puani'] ?? 0;
$_SESSION['kupon_puani'] = $puan;

$siparisler = kullaniciSiparisleri($_SESSION['kullanici_id']);
$stmt2 = $db->prepare("SELECT * FROM kuponlar WHERE kullanici_id=? ORDER BY id DESC");
$stmt2->execute([$_SESSION['kullanici_id']]);
$kuponlar = $stmt2->fetchAll();

$sehirler = getSehirler();
$aktifSehir = $kullanici['sehir'] ?? '';
$aktifIlce  = $kullanici['ilce'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim - GurmeCepte</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/src/ui/style.css">
</head>
<body>
<?php include __DIR__ . '/../src/ui/navbar.php'; ?>

<div style="background:var(--koyu);padding:32px 6%;color:white">
    <h1 style="font-family:'Nunito',sans-serif;font-size:1.9rem;font-weight:800">👤 Profilim</h1>
    <p style="color:#A8A29E;margin-top:4px"><?= htmlspecialchars($kullanici['ad'].' '.$kullanici['soyad']) ?></p>
</div>

<div style="max-width:1000px;margin:32px auto;padding:0 5%">

    <?php if ($hata): ?><div class="alert alert-hata">⚠️ <?= $hata ?></div><?php endif; ?>
    <?php if ($basari): ?><div class="alert alert-basari">✅ <?= $basari ?></div><?php endif; ?>

    <!-- PUAN -->
    <div class="puan-kart">
        <div class="puan-etiketi">Puan Bakiyeniz</div>
        <div class="puan-sayi"><?= $puan ?></div>
        <div class="puan-aciklama">100 puan = 10₺ indirim kuponu • Her 10₺ sipariş = 1 puan</div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

        <!-- BİLGİLER -->
        <div class="panel-kart">
            <h3>👤 Hesap Bilgilerim</h3>
            <div style="display:flex;flex-direction:column;gap:12px;font-size:0.88rem">
                <div style="display:flex;justify-content:space-between;padding:10px;background:var(--acik-gri);border-radius:8px">
                    <span style="color:var(--gri)">Ad Soyad</span>
                    <span style="font-weight:700"><?= htmlspecialchars($kullanici['ad'].' '.$kullanici['soyad']) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px;background:var(--acik-gri);border-radius:8px">
                    <span style="color:var(--gri)">E-posta</span>
                    <span style="font-weight:700"><?= htmlspecialchars($kullanici['email']) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px;background:var(--acik-gri);border-radius:8px">
                    <span style="color:var(--gri)">Toplam Sipariş</span>
                    <span style="font-weight:700;color:var(--turuncu)"><?= count($siparisler) ?></span>
                </div>
                <?php if ($aktifSehir): ?>
                <div style="display:flex;justify-content:space-between;padding:10px;background:var(--acik-gri);border-radius:8px">
                    <span style="color:var(--gri)">Konumum</span>
                    <span style="font-weight:700">📍 <?= htmlspecialchars($aktifIlce.', '.$aktifSehir) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- KUPON -->
        <div class="panel-kart">
            <h3>🎟️ Puan → Kupon</h3>
            <?php if ($puan >= 100): ?>
                <p style="font-size:0.83rem;color:var(--gri);margin-bottom:14px"><?= $puan ?> puanınız var. 100 puan = 10₺ kupon.</p>
                <form method="POST">
                    <div class="form-grup">
                        <label>Kullanılacak puan (100'ün katları)</label>
                        <select name="puan_kullan" style="width:100%;padding:10px 14px;border:1.5px solid #E7E5E4;border-radius:10px;font-family:'Poppins',sans-serif;font-size:0.9rem;outline:none">
                            <?php for ($i=100; $i<=$puan; $i+=100): ?>
                                <option value="<?= $i ?>"><?= $i ?> puan → ₺<?= $i/10 ?> indirim</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" name="kupon_olustur" class="btn btn-primary">🎟️ Kupon Oluştur</button>
                </form>
            <?php else: ?>
                <div class="bos-durum" style="padding:20px">
                    <div class="emoji" style="font-size:2rem">🏆</div>
                    <p style="font-size:0.85rem"><?= 100-$puan ?> puan daha kazanın, kupon açılsın!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- ADRES KAYDET -->
        <div class="panel-kart" style="grid-column:1/-1">
            <h3>📍 Teslimat Adresim</h3>
            <p style="font-size:0.83rem;color:var(--gri);margin-bottom:18px">Adresinizi kaydedin, sipariş verirken otomatik dolsun ve ilçenizdeki restoranlar gösterilsin.</p>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-grup">
                        <label>Şehir *</label>
                        <select name="sehir" id="sehir-select" onchange="ilceleriYukle(this.value)" required>
                            <option value="">Şehir seçin</option>
                            <?php foreach (array_keys($sehirler) as $s): ?>
                                <option value="<?= $s ?>" <?= $aktifSehir===$s?'selected':'' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grup">
                        <label>İlçe *</label>
                        <select name="ilce" id="ilce-select" required>
                            <option value="">Önce şehir seçin</option>
                            <?php if ($aktifSehir && isset($sehirler[$aktifSehir])): ?>
                                <?php foreach ($sehirler[$aktifSehir] as $i): ?>
                                    <option value="<?= $i ?>" <?= $aktifIlce===$i?'selected':'' ?>><?= $i ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="form-grup">
                    <label>Açık Adres</label>
                    <input type="text" name="adres_detay" placeholder="Mahalle, cadde, bina no, daire..." value="<?= htmlspecialchars($kullanici['adres_detay'] ?? '') ?>">
                </div>
                <div class="form-grup">
                    <label>Telefon</label>
                    <input type="tel" name="telefon" placeholder="05XX XXX XX XX" value="<?= htmlspecialchars($kullanici['telefon'] ?? '') ?>">
                </div>
                <button type="submit" name="adres_guncelle" class="btn btn-primary">💾 Adresi Kaydet</button>
            </form>
        </div>

        <!-- KUPONLARIM -->
        <?php if (!empty($kuponlar)): ?>
        <div class="panel-kart" style="grid-column:1/-1">
            <h3>🎟️ Kuponlarım</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">
                <?php foreach ($kuponlar as $k): ?>
                    <div style="background:<?= $k['kullanildi']?'var(--acik-gri)':'linear-gradient(135deg,var(--turuncu),var(--altin))' ?>;border-radius:14px;padding:16px;color:<?= $k['kullanildi']?'var(--gri)':'white' ?>">
                        <div style="font-size:0.72rem;opacity:0.8;margin-bottom:4px;text-transform:uppercase;letter-spacing:1px">Kupon</div>
                        <div style="font-weight:800;font-size:0.95rem;letter-spacing:2px"><?= $k['kod'] ?></div>
                        <div style="font-size:0.8rem;margin-top:8px;font-weight:700">₺<?= number_format($k['indirim_miktari'],0) ?> indirim</div>
                        <div style="font-size:0.72rem;margin-top:4px;opacity:0.75"><?= $k['kullanildi']?'✅ Kullanıldı':'⏰ '.date('d.m.Y',strtotime($k['son_kullanim'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php include __DIR__ . '/../src/ui/footer.php'; ?>
<script src="<?= SITE_URL ?>/src/ui/main.js"></script>
<script>
const sehirler = <?= json_encode(getSehirler(), JSON_UNESCAPED_UNICODE) ?>;

function ilceleriYukle(sehir) {
    const sel = document.getElementById('ilce-select');
    sel.innerHTML = '<option value="">İlçe seçin</option>';
    if (sehirler[sehir]) {
        sehirler[sehir].forEach(i => {
            const opt = document.createElement('option');
            opt.value = i; opt.textContent = i;
            sel.appendChild(opt);
        });
    }
}
</script>
</body>
</html>
