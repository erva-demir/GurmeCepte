//Restoran detay ve menu sayfası 
<?php
require_once __DIR__ . '/../src/core/db.php';
require_once __DIR__ . '/../src/services/restoran.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . SITE_URL . '/modules/restoranlar.php'); exit; }

$restoran = restoranGetir($id);
if (!$restoran) { header('Location: ' . SITE_URL . '/modules/restoranlar.php'); exit; }

$yemekler = restoranYemekleri($id);
$yorumlar = restoranYorumlar($id);

$yorumHata = ''; $yorumBasari = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['yorum'])) {
    if (!girisYapildi()) { $yorumHata = 'Yorum yapabilmek için giriş yapın.'; }
    else {
        $puan = intval($_POST['puan'] ?? 5);
        $yorum = trim($_POST['yorum']);
        if ($yorum && $puan >= 1 && $puan <= 5) {
            yorumEkle($_SESSION['kullanici_id'], $id, $puan, $yorum);
            $yorumBasari = 'Yorumunuz eklendi!';
            $yorumlar = restoranYorumlar($id);
        } else { $yorumHata = 'Puan ve yorum gerekli.'; }
    }
}

$emojiler = ['Pizza'=>'🍕','Burger'=>'🍔','Lahmacun'=>'🫓','Kebap'=>'🥙','Döner'=>'🌯','Sushi'=>'🍱','Tatlı'=>'🍰','Pide'=>'🫓'];
$gruplu = [];
foreach ($yemekler as $y) { $gruplu[$y['kategori_isim'] ?? 'Diğer'][] = $y; }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($restoran['isim']) ?> - GurmeCepte</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/src/ui/style.css">
</head>
<body>
<?php include __DIR__ . '/../src/ui/navbar.php'; ?>

<input type="hidden" id="restoran-id" value="<?= $restoran['id'] ?>">

<!-- Başlık -->
<div style="background:var(--koyu);padding:40px 6%;color:white">
    <div style="max-width:1200px;margin:0 auto;display:flex;align-items:center;gap:20px;flex-wrap:wrap">
        <div style="font-size:4.5rem"><?= $emojiler[$restoran['kategori']] ?? '🍽️' ?></div>
        <div style="flex:1">
            <div style="font-size:0.78rem;color:#A8A29E;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px"><?= htmlspecialchars($restoran['kategori']) ?></div>
            <h1 style="font-family:'Nunito',sans-serif;font-size:2.2rem;font-weight:900;margin-bottom:8px"><?= htmlspecialchars($restoran['isim']) ?></h1>
            <p style="color:#D6D3D1;margin-bottom:14px;font-size:0.9rem"><?= htmlspecialchars($restoran['aciklama']) ?></p>
            <div style="display:flex;gap:18px;flex-wrap:wrap;font-size:0.83rem;color:#A8A29E">
                <span>⭐ <strong style="color:var(--altin)"><?= number_format($restoran['puan'],1) ?></strong> (<?= $restoran['yorum_sayisi'] ?> yorum)</span>
                <span>⏰ <?= $restoran['teslimat_suresi'] ?></span>
                <span>🚚 ₺<?= number_format($restoran['teslimat_ucreti'],2) ?> teslimat</span>
                <span>📍 <?= htmlspecialchars($restoran['ilce'].', '.$restoran['sehir']) ?></span>
                <?php if ($restoran['min_siparis'] > 0): ?>
                    <span>🛒 Min ₺<?= number_format($restoran['min_siparis'],0) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div style="max-width:1200px;margin:0 auto;padding:28px 6%;display:grid;grid-template-columns:1fr 340px;gap:28px">

    <!-- MENÜ -->
    <div>
        <h2 style="font-family:'Nunito',sans-serif;font-size:1.6rem;font-weight:800;margin-bottom:20px">🍽️ Menü</h2>
        <?php if (empty($yemekler)): ?>
            <div class="bos-durum"><div class="emoji">🍽️</div><h3>Menü henüz eklenmedi</h3></div>
        <?php else: ?>
            <?php foreach ($gruplu as $katIsim => $katYemekler): ?>
                <h3 style="font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--gri);margin:20px 0 12px;padding-bottom:8px;border-bottom:1.5px solid #F0EDE9">
                    <?= htmlspecialchars($katIsim) ?>
                </h3>
                <?php foreach ($katYemekler as $y): ?>
                    <?php $malzemeler = yemekMalzemeleri($y['id']); ?>
                    <div class="yemek-kart" data-yemek="<?= $y['id'] ?>" style="display:flex;gap:0;margin-bottom:12px;flex-direction:row;align-items:stretch">
                        <!-- Fotoğraf -->
                        <div style="width:130px;flex-shrink:0;border-radius:var(--radius) 0 0 var(--radius);overflow:hidden;background:#F5F5F4">
                            <?php if ($y['resim']): ?>
                                <img src="<?= htmlspecialchars($y['resim']) ?>" alt="<?= htmlspecialchars($y['isim']) ?>"
                                     style="width:100%;height:100%;object-fit:cover;display:block"
                                     onerror="this.parentNode.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;font-size:2.5rem\'><?= $emojiler[$restoran['kategori']] ?? '🍽️' ?></div>'">
                            <?php else: ?>
                                <div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:2.5rem">
                                    <?= $emojiler[$restoran['kategori']] ?? '🍽️' ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- Bilgi -->
                        <div style="flex:1;padding:14px 16px;display:flex;flex-direction:column;justify-content:space-between">
                            <div>
                                <div style="font-weight:700;font-size:0.95rem;margin-bottom:4px"><?= htmlspecialchars($y['isim']) ?></div>
                                <?php if ($y['aciklama']): ?>
                                    <div style="font-size:0.8rem;color:var(--gri);margin-bottom:8px;line-height:1.5"><?= htmlspecialchars($y['aciklama']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($malzemeler)): ?>
                                    <div style="font-size:0.73rem;color:var(--gri);margin-bottom:5px">İçindekiler (çıkarmak için tıkla):</div>
                                    <div class="malzeme-etiketler">
                                        <?php foreach ($malzemeler as $m): ?>
                                            <?php if ($m['cikarilabilir']): ?>
                                                <span class="malzeme-etiket" data-malzeme="<?= htmlspecialchars($m['isim']) ?>" onclick="malzemeCikar(this)">
                                                    <?= htmlspecialchars($m['isim']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="malzeme-etiket" style="opacity:0.5;cursor:default"><?= htmlspecialchars($m['isim']) ?></span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px">
                                <div style="font-family:'Nunito',sans-serif;font-size:1.2rem;font-weight:800;color:var(--turuncu)">
                                    ₺<?= number_format($y['fiyat'],2) ?>
                                </div>
                                <button class="btn btn-primary btn-sm" onclick="sepeteEkleYemek(<?= $y['id'] ?>, '<?= addslashes($y['isim']) ?>', <?= $y['fiyat'] ?>)">
                                    + Ekle
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- YORUMLAR -->
        <div style="margin-top:40px">
            <h2 style="font-family:'Nunito',sans-serif;font-size:1.6rem;font-weight:800;margin-bottom:18px">💬 Yorumlar</h2>
            <?php if (girisYapildi()): ?>
                <div class="panel-kart" style="margin-bottom:20px">
                    <h3>Yorum Yaz</h3>
                    <?php if ($yorumHata): ?><div class="alert alert-hata"><?= $yorumHata ?></div><?php endif; ?>
                    <?php if ($yorumBasari): ?><div class="alert alert-basari"><?= $yorumBasari ?></div><?php endif; ?>
                    <form method="POST">
                        <div class="yildiz-secici">
                            <?php for ($i=1;$i<=5;$i++): ?>
                                <span class="yildiz aktif">★</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" id="puan-input" name="puan" value="5">
                        <div class="form-grup">
                            <textarea name="yorum" rows="3" placeholder="Deneyiminizi paylaşın..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Gönder</button>
                    </form>
                </div>
            <?php else: ?>
                <div style="background:#FFF7F2;border-radius:12px;padding:14px;margin-bottom:18px;text-align:center;font-size:0.88rem">
                    <a href="<?= SITE_URL ?>/modules/giris.php" style="color:var(--turuncu);font-weight:700">Giriş yapın</a> ve yorum yapın!
                </div>
            <?php endif; ?>
            <?php if (empty($yorumlar)): ?>
                <div class="bos-durum"><div class="emoji">💬</div><h3>İlk yorumu siz yapın!</h3></div>
            <?php else: ?>
                <?php foreach ($yorumlar as $y): ?>
                    <div class="yorum-kart">
                        <div class="yorum-header">
                            <div class="yorum-avatar"><?= strtoupper(mb_substr($y['ad'],0,1)) ?></div>
                            <div>
                                <div class="yorum-isim"><?= htmlspecialchars($y['ad'].' '.$y['soyad']) ?></div>
                                <div class="yorum-tarih"><?= date('d.m.Y', strtotime($y['tarih'])) ?></div>
                            </div>
                            <div style="margin-left:auto" class="yildizlar"><?= str_repeat('★',$y['puan']).str_repeat('☆',5-$y['puan']) ?></div>
                        </div>
                        <p style="font-size:0.88rem;color:var(--gri);line-height:1.6"><?= htmlspecialchars($y['yorum']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- SAĞ BİLGİ -->
    <div>
        <div style="position:sticky;top:80px">
            <div class="panel-kart">
                <h3>🏪 Bilgiler</h3>
                <div style="font-size:0.87rem;display:flex;flex-direction:column;gap:10px;color:var(--gri)">
                    <div>📍 <?= htmlspecialchars($restoran['adres']) ?></div>
                    <div>📞 <?= htmlspecialchars($restoran['telefon']) ?></div>
                    <div>⏰ Teslimat: <?= $restoran['teslimat_suresi'] ?></div>
                    <div>🚚 Teslimat ücreti: ₺<?= number_format($restoran['teslimat_ucreti'],2) ?></div>
                    <?php if ($restoran['min_siparis']>0): ?>
                        <div>🛒 Minimum sipariş: ₺<?= number_format($restoran['min_siparis'],0) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="panel-kart" style="margin-top:14px;background:linear-gradient(135deg,var(--turuncu),var(--altin));color:white;border:none">
                <div style="font-size:1.3rem;margin-bottom:6px">🏆</div>
                <div style="font-weight:700;margin-bottom:4px;font-family:'Nunito',sans-serif">Puan Kazan!</div>
                <div style="font-size:0.82rem;opacity:0.9">Her 10₺ siparişte 1 puan kazanırsın.</div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../src/ui/footer.php'; ?>
<script src="<?= SITE_URL ?>/src/ui/main.js"></script>
<script>
function sepeteEkleYemek(yemekId, isim, fiyat) {
    const cikarilan = seciliMalzemeleriAl(yemekId);
    const emojis = ['🍕','🫓','🥙','🌯','🍔','🍱','🍰','🥗','🍽️'];
    sepeteEkle(yemekId, isim, fiyat, emojis[Math.floor(Math.random()*emojis.length)], cikarilan);
}
</script>
</body>
</html>
