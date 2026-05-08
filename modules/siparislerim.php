<?php
require_once __DIR__ . '/../src/core/db.php';
require_once __DIR__ . '/../src/services/siparis.php';
require_once __DIR__ . '/../src/services/restoran.php';

if (!girisYapildi()) { header('Location: ' . SITE_URL . '/modules/giris.php'); exit; }

$db = getDB();
$hata = ''; $basari = '';

// Yorum gönder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['yorum_gonder'])) {
    $restoran_id = intval($_POST['restoran_id'] ?? 0);
    $siparis_id  = intval($_POST['siparis_id'] ?? 0);
    $puan        = intval($_POST['puan'] ?? 5);
    $yorum       = trim($_POST['yorum'] ?? '');

    if ($restoran_id && $yorum && $puan >= 1 && $puan <= 5) {
        // Daha önce yorum yapılmış mı kontrol et
        $kontrol = $db->prepare("SELECT id FROM yorumlar WHERE kullanici_id=? AND siparis_id=?");
        $kontrol->execute([$_SESSION['kullanici_id'], $siparis_id]);
        if ($kontrol->fetch()) {
            $hata = 'Bu sipariş için zaten yorum yaptınız.';
        } else {
            yorumEkle($_SESSION['kullanici_id'], $restoran_id, $puan, $yorum);
            // Sipariş ile ilişkilendir
            $db->prepare("UPDATE yorumlar SET siparis_id=? WHERE kullanici_id=? AND restoran_id=? ORDER BY id DESC LIMIT 1")
               ->execute([$siparis_id, $_SESSION['kullanici_id'], $restoran_id]);
            $basari = 'Yorumunuz başarıyla eklendi!';
        }
    } else {
        $hata = 'Lütfen yorum yazın ve puan seçin.';
    }
}

$siparisler = kullaniciSiparisleri($_SESSION['kullanici_id']);

// Her sipariş için yorum yapılıp yapılmadığını kontrol et
$yorumYapildi = [];
foreach ($siparisler as $s) {
    $k = $db->prepare("SELECT id FROM yorumlar WHERE kullanici_id=? AND siparis_id=?");
    $k->execute([$_SESSION['kullanici_id'], $s['id']]);
    $yorumYapildi[$s['id']] = (bool)$k->fetch();
}

$durumRenk = [
    'beklemede'    => 'badge-sari',
    'hazirlaniyor' => 'badge-mavi',
    'yolda'        => 'badge-yesil',
    'teslim_edildi'=> 'badge-yesil',
    'iptal'        => 'badge-kirmizi',
];
$durumText = [
    'beklemede'    => '⏳ Beklemede',
    'hazirlaniyor' => '👨‍🍳 Hazırlanıyor',
    'yolda'        => '🛵 Yolda',
    'teslim_edildi'=> '✅ Teslim Edildi',
    'iptal'        => '❌ İptal',
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siparişlerim - GurmeCepte</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/src/ui/style.css">
    <style>
        .yorum-formu {
            background: var(--acik-gri);
            border-radius: 12px;
            padding: 16px;
            margin-top: 14px;
            display: none;
        }
        .yorum-formu.acik { display: block; }
        .yildiz-sec { display:flex; gap:4px; margin-bottom:10px; }
        .yildiz-sec .y {
            font-size:1.6rem;
            cursor:pointer;
            color:#D6D3D1;
            transition: transform .15s;
        }
        .yildiz-sec .y.aktif { color:var(--altin); }
        .yildiz-sec .y:hover { transform:scale(1.2); }
    </style>
</head>
<body>
<?php include __DIR__ . '/../src/ui/navbar.php'; ?>

<div style="background:var(--koyu);padding:32px 6%;color:white">
    <h1 style="font-family:'Nunito',sans-serif;font-size:1.9rem;font-weight:800">📦 Siparişlerim</h1>
    <p style="color:#A8A29E;margin-top:4px">Tüm siparişleriniz ve yorum yapabileceğiniz siparişler</p>
</div>

<div style="max-width:900px;margin:32px auto;padding:0 5%">

    <?php if ($hata): ?><div class="alert alert-hata">⚠️ <?= htmlspecialchars($hata) ?></div><?php endif; ?>
    <?php if ($basari): ?><div class="alert alert-basari">✅ <?= $basari ?></div><?php endif; ?>

    <?php if (empty($siparisler)): ?>
        <div class="bos-durum">
            <div class="emoji">📦</div>
            <h3>Henüz sipariş vermediniz</h3>
            <p>Lezzetli yemekler sizi bekliyor!</p>
            <a href="<?= SITE_URL ?>/modules/restoranlar.php" class="btn btn-primary" style="margin-top:16px">Restoranlara Göz At</a>
        </div>
    <?php else: ?>
        <?php foreach ($siparisler as $siparis): ?>
            <?php $detaylar = siparisDetay($siparis['id']); ?>
            <div class="panel-kart" style="margin-bottom:20px">

                <!-- BAŞLIK -->
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
                    <div>
                        <div style="font-weight:800;font-size:1.05rem;font-family:'Nunito',sans-serif">
                            #<?= $siparis['id'] ?> — <?= htmlspecialchars($siparis['restoran_isim']) ?>
                        </div>
                        <div style="color:var(--gri);font-size:0.82rem;margin-top:3px">
                            📅 <?= date('d.m.Y H:i', strtotime($siparis['olusturma_tarihi'])) ?>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        <span class="badge <?= $durumRenk[$siparis['durum']] ?? 'badge-sari' ?>">
                            <?= $durumText[$siparis['durum']] ?? $siparis['durum'] ?>
                        </span>
                        <span style="font-family:'Nunito',sans-serif;font-size:1.2rem;font-weight:800;color:var(--turuncu)">
                            ₺<?= number_format($siparis['toplam_fiyat'],2) ?>
                        </span>
                    </div>
                </div>

                <!-- DETAYLAR -->
                <div style="background:var(--acik-gri);border-radius:10px;padding:12px 14px;margin-bottom:12px">
                    <?php foreach ($detaylar as $d): ?>
                        <div style="display:flex;justify-content:space-between;font-size:0.87rem;margin-bottom:5px;align-items:center">
                            <span>
                                <?= $d['adet'] ?>× <?= htmlspecialchars($d['yemek_isim']) ?>
                                <?php if ($d['cikarilan_malzemeler']): ?>
                                    <span style="font-size:0.75rem;color:var(--gri)"> (−<?= htmlspecialchars($d['cikarilan_malzemeler']) ?>)</span>
                                <?php endif; ?>
                            </span>
                            <span style="font-weight:700">₺<?= number_format($d['birim_fiyat']*$d['adet'],2) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- ALT BİLGİ -->
                <div style="font-size:0.82rem;color:var(--gri);display:flex;gap:14px;flex-wrap:wrap;margin-bottom:12px">
                    <?php
                    // Güvenli adres gösterimi - hangi alan dolu ise onu göster
                    $adres = $siparis['teslimat_adres'] ?? $siparis['teslimat_adresi'] ?? '';
                    if ($adres): ?>
                        <span>📍 <?= htmlspecialchars($adres) ?></span>
                    <?php endif; ?>
                    <span>💳 <?= htmlspecialchars($siparis['odeme_yontemi'] ?? 'nakit') ?></span>
                    <?php if (!empty($siparis['kart_son4'])): ?>
                        <span>•••• <?= $siparis['kart_son4'] ?></span>
                    <?php endif; ?>
                    <?php if ($siparis['kupon_kullanildi'] > 0): ?>
                        <span>🏆 <?= $siparis['kupon_kullanildi'] ?> puan kullanıldı</span>
                    <?php endif; ?>
                </div>

                <!-- YORUM BUTONU -->
                <?php if ($siparis['durum'] === 'teslim_edildi'): ?>
                    <?php if ($yorumYapildi[$siparis['id']]): ?>
                        <div style="display:flex;align-items:center;gap:8px;font-size:0.83rem;color:var(--yesil);font-weight:600;padding:8px 0">
                            ✅ Bu sipariş için yorum yaptınız
                            <a href="<?= SITE_URL ?>/modules/restoran.php?id=<?= $siparis['restoran_id'] ?>"
                               style="color:var(--turuncu);font-size:0.8rem;margin-left:4px">Yorumları gör →</a>
                        </div>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-sm" onclick="yorumFormAc(<?= $siparis['id'] ?>)">
                            ⭐ Yorum Yap
                        </button>

                        <!-- YORUM FORMU -->
                        <div class="yorum-formu" id="yorum-form-<?= $siparis['id'] ?>">
                            <div style="font-weight:700;font-size:0.9rem;margin-bottom:10px">
                                <?= htmlspecialchars($siparis['restoran_isim']) ?> için yorum yaz
                            </div>
                            <form method="POST">
                                <input type="hidden" name="restoran_id" value="<?= $siparis['restoran_id'] ?>">
                                <input type="hidden" name="siparis_id" value="<?= $siparis['id'] ?>">
                                <input type="hidden" name="puan" id="puan-<?= $siparis['id'] ?>" value="5">

                                <div style="font-size:0.8rem;color:var(--gri);margin-bottom:6px">Puanınız:</div>
                                <div class="yildiz-sec" id="yildiz-<?= $siparis['id'] ?>">
                                    <?php for($i=1;$i<=5;$i++): ?>
                                        <span class="y aktif"
                                              data-puan="<?= $i ?>"
                                              data-id="<?= $siparis['id'] ?>"
                                              onclick="yildizSec(<?= $siparis['id'] ?>, <?= $i ?>)">★</span>
                                    <?php endfor; ?>
                                </div>

                                <div class="form-grup">
                                    <textarea name="yorum" rows="3"
                                              placeholder="Yemeğiniz nasıldı? Teslimat hızı, lezzet, sunum hakkında yorumlarınızı paylaşın..."
                                              required
                                              style="width:100%;padding:10px 14px;border:1.5px solid #E7E5E4;border-radius:10px;font-family:'Poppins',sans-serif;font-size:0.88rem;outline:none;resize:vertical"></textarea>
                                </div>

                                <div style="display:flex;gap:8px">
                                    <button type="submit" name="yorum_gonder" class="btn btn-primary btn-sm">
                                        Yorumu Gönder
                                    </button>
                                    <button type="button" class="btn btn-sm" onclick="yorumFormKapat(<?= $siparis['id'] ?>)"
                                            style="background:var(--acik-gri);color:var(--gri)">
                                        İptal
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                <?php elseif ($siparis['durum'] !== 'iptal'): ?>
                    <div style="font-size:0.8rem;color:var(--gri);font-style:italic">
                        💬 Teslim edildikten sonra yorum yapabilirsiniz
                    </div>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../src/ui/footer.php'; ?>
<script src="<?= SITE_URL ?>/src/ui/main.js"></script>
<script>
function yorumFormAc(id) {
    document.querySelectorAll('.yorum-formu').forEach(f => f.classList.remove('acik'));
    document.getElementById('yorum-form-' + id).classList.add('acik');
}

function yorumFormKapat(id) {
    document.getElementById('yorum-form-' + id).classList.remove('acik');
}

function yildizSec(sipId, puan) {
    document.getElementById('puan-' + sipId).value = puan;
    const yildizlar = document.querySelectorAll('#yildiz-' + sipId + ' .y');
    yildizlar.forEach((y, i) => {
        y.classList.toggle('aktif', i < puan);
    });
}

// Hover efekti
document.querySelectorAll('.yildiz-sec').forEach(grup => {
    const yildizlar = grup.querySelectorAll('.y');
    const sipId = grup.id.replace('yildiz-', '');
    yildizlar.forEach((y, i) => {
        y.addEventListener('mouseover', () => {
            yildizlar.forEach((y2, j) => y2.classList.toggle('aktif', j <= i));
        });
        y.addEventListener('mouseleave', () => {
            const secili = parseInt(document.getElementById('puan-' + sipId).value) - 1;
            yildizlar.forEach((y2, j) => y2.classList.toggle('aktif', j <= secili));
        });
    });
});
</script>
</body>
</html>
