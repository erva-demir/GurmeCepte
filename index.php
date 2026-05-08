<?php
require_once __DIR__ . '/src/core/db.php';
require_once __DIR__ . '/src/services/restoran.php';

$kategoriler = tumKategoriler();
// İlçe filtresi
$kullanici_ilce = $_SESSION['ilce'] ?? null;
$kullanici_sehir = $_SESSION['sehir'] ?? null;
if ($kullanici_ilce && $kullanici_sehir) {
    $restoranlar = restoranlarIlceye($kullanici_ilce, $kullanici_sehir);
    if (empty($restoranlar)) $restoranlar = tumRestoranlar();
} else {
    $restoranlar = tumRestoranlar();
}
$indirimler  = aktifIndirimler();

$emojiler = [
    'Pizza'=>'🍕','Burger'=>'🍔','Lahmacun'=>'🫓','Kebap'=>'🥙',
    'Döner'=>'🌯','Sushi'=>'🍱','Tatlı'=>'🍰','İçecek'=>'🥤',
    'Pide'=>'🫓','Salata'=>'🥗','Pilav'=>'🍚'
];
$kapakClass = [
    'Pizza'=>'pizza','Lahmacun'=>'lahmacun','Kebap'=>'kebap','Burger'=>'burger',
    'Tatlı'=>'lahmacun','Pilav'=>'default','Döner'=>'kebap','Pide'=>'lahmacun',
    'Salata'=>'burger','Sushi'=>'default'
];
$renkler = ['renk1','renk2','renk3','renk4'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GurmeCepte - Lezzet Kapınıza Gelsin</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/src/ui/style.css">
</head>
<body>

<?php include __DIR__ . '/src/ui/navbar.php'; ?>

<!-- ===== HERO ===== -->
<section class="hero">
    <div class="hero-bg"></div>
    <!-- Yemek emojileri arka plan -->
    <div style="position:absolute;inset:0;overflow:hidden;pointer-events:none;z-index:1">
        <span style="position:absolute;font-size:8rem;opacity:0.08;top:5%;right:5%;transform:rotate(15deg)">🍕</span>
        <span style="position:absolute;font-size:6rem;opacity:0.07;top:55%;right:18%;transform:rotate(-10deg)">🥙</span>
        <span style="position:absolute;font-size:7rem;opacity:0.07;top:15%;right:28%;transform:rotate(8deg)">🍔</span>
        <span style="position:absolute;font-size:5rem;opacity:0.08;top:70%;right:5%;transform:rotate(-20deg)">🍜</span>
        <span style="position:absolute;font-size:9rem;opacity:0.06;top:30%;right:42%;transform:rotate(5deg)">🫓</span>
        <span style="position:absolute;font-size:6rem;opacity:0.07;top:75%;right:35%;transform:rotate(12deg)">🍰</span>
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-icerik">
        <div class="hero-etiket">🔥 Türkiye'nin En İyi Lezzetleri</div>
        <h1>Dilediğin Yemeği<br><span class="vurgu">Kapına Getiriyoruz</span></h1>
        <p>Yüzlerce restoran, binlerce seçenek. Hızlı teslimat, taze yemekler.</p>
        <div class="hero-arama">
            <input type="text" id="arama-input" placeholder="🔍  Restoran veya yemek ara...">
            <a href="<?= SITE_URL ?>/modules/restoranlar.php" class="btn btn-primary">Keşfet</a>
        </div>
        <div class="hero-stats">
            <div>
                <span class="stat-sayi"><?= count($restoranlar) ?>+</span>
                <span class="stat-etiket">Restoran</span>
            </div>
            <div>
                <span class="stat-sayi">30dk</span>
                <span class="stat-etiket">Ort. Teslimat</span>
            </div>
            </div>
            </div>
            <?php if ($kullanici_ilce): ?>
            <div style="margin-top:16px;display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.2);border-radius:30px;padding:8px 18px;font-size:0.83rem">
                📍 <strong><?= htmlspecialchars($kullanici_ilce.", ".$kullanici_sehir) ?></strong> için restoranlar gösteriliyor
                <a href="<?= SITE_URL ?>/modules/profilim.php" style="color:var(--altin);font-size:0.78rem;margin-left:4px">Değiştir</a>
            </div>
            <?php endif; ?>
            PLACEHOLDER</span>
            </div>
            <div>
                <span class="stat-sayi">4.8★</span>
                <span class="stat-etiket">Müşteri Puanı</span>
            </div>
        </div>
    </div>
</section>

<!-- ===== İNDİRİMLER ===== -->
<?php if (!empty($indirimler)): ?>
<section class="indirim-seksiyon">
    <div class="indirim-baslik-row">
        <span style="font-size:1.3rem">🔥</span>
        <h2>Günün Kampanyaları</h2>
        <span style="font-size:0.8rem;color:var(--gri);margin-left:auto">Üzerine gelince durur</span>
    </div>
    <div class="indirim-karusel-wrapper">
        <div class="indirim-karusel">
            <?php
            $tumInd = array_merge($indirimler, $indirimler);
            foreach ($tumInd as $idx => $ind):
                $renk = $renkler[$idx % 4];
            ?>
                <div class="indirim-kart <?= $renk ?>">
                    <div class="indirim-yuzdesi">%<?= $ind['indirim_yuzdesi'] ?></div>
                    <div class="indirim-baslik"><?= htmlspecialchars($ind['baslik']) ?></div>
                    <div class="indirim-aciklama"><?= htmlspecialchars($ind['aciklama']) ?></div>
                    <div class="indirim-alt">
                        <?php if ($ind['restoran_isim']): ?>🏪 <?= htmlspecialchars($ind['restoran_isim']) ?><?php endif; ?>
                        <?php if ($ind['bitis_tarihi']): ?> · ⏰ <?= date('d.m.Y', strtotime($ind['bitis_tarihi'])) ?><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== KATEGORİLER ===== -->
<div class="bolum">
    <h2 class="bolum-baslik">Ne yemek istersiniz?</h2>
    <p class="bolum-altyazi">Kategoriye göre filtreleyin</p>
    <div class="kategori-grid">
        <a href="#" class="kategori-kart aktif" data-kategori="tumu">
            <span class="emoji">🍽️</span> Tümü
        </a>
        <?php foreach ($kategoriler as $kat): ?>
            <a href="#" class="kategori-kart" data-kategori="<?= htmlspecialchars($kat['isim']) ?>">
                <span class="emoji"><?= $kat['icon'] ?></span> <?= htmlspecialchars($kat['isim']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- ===== RESTORANLAR ===== -->
<div style="padding: 0 6% 60px; max-width:1280px; margin:0 auto">
    <h2 class="bolum-baslik" style="margin-bottom:20px">Restoranlar</h2>
    <?php if (empty($restoranlar)): ?>
        <div class="bos-durum">
            <div class="emoji">🏪</div>
            <h3>Henüz restoran yok</h3>
        </div>
    <?php else: ?>
        <div class="restoran-grid">
            <?php foreach ($restoranlar as $r): ?>
                <a href="<?= SITE_URL ?>/modules/restoran.php?id=<?= $r['id'] ?>"
                   class="restoran-kart"
                   data-kategori-restoran="<?= htmlspecialchars($r['kategori']) ?>"
                   data-restoran-isim="<?= htmlspecialchars($r['isim']) ?>">
                    <div class="restoran-kapak <?= $kapakClass[$r['kategori']] ?? 'default' ?>">
                        <?= $emojiler[$r['kategori']] ?? '🍽️' ?>
                        <div class="restoran-puan">⭐ <?= number_format($r['puan'],1) ?></div>
                    </div>
                    <div class="restoran-bilgi">
                        <div class="restoran-isim"><?= htmlspecialchars($r['isim']) ?></div>
                        <div class="restoran-aciklama"><?= htmlspecialchars(substr($r['aciklama'],0,75)) ?>...</div>
                        <div class="restoran-meta">
                            <span>⏰ <?= $r['teslimat_suresi'] ?></span>
                            <span>💬 <?= $r['yorum_sayisi'] ?> yorum</span>
                            <span>🚚 ₺<?= number_format($r['teslimat_ucreti'],2) ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ===== PUAN TANITIM ===== -->
<section style="background:var(--koyu);padding:56px 6%;text-align:center;color:white">
    <h2 style="font-family:'Nunito',sans-serif;font-size:2.2rem;font-weight:900;margin-bottom:12px">
        🏆 Her Siparişte <span style="color:var(--altin)">Puan Kazan</span>
    </h2>
    <p style="color:#A8A29E;font-size:0.95rem;max-width:480px;margin:0 auto 36px;line-height:1.7">
        Her 10₺ siparişte 1 puan kazanırsınız. 100 puan biriktirince 10₺ indirim kuponu!
    </p>
    <div style="display:flex;gap:20px;justify-content:center;flex-wrap:wrap;margin-bottom:32px">
        <?php
        $adimlar = [
            ['🛍️','Sipariş Ver','Her 10₺ = 1 puan'],
            ['🏆','Puan Biriktir','100 puan hedefle'],
            ['🎟️','Kupona Çevir','10₺ indirim kodu al'],
        ];
        foreach ($adimlar as $a):
        ?>
            <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:16px;padding:22px 28px;min-width:160px">
                <div style="font-size:2rem;margin-bottom:8px"><?= $a[0] ?></div>
                <div style="font-weight:700;margin-bottom:4px;font-size:0.95rem"><?= $a[1] ?></div>
                <div style="color:#A8A29E;font-size:0.8rem"><?= $a[2] ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if (!girisYapildi()): ?>
        <a href="<?= SITE_URL ?>/modules/kayit.php" class="btn btn-primary btn-lg">
            Hemen Üye Ol →
        </a>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/src/ui/footer.php'; ?>
<script src="<?= SITE_URL ?>/src/ui/main.js"></script>
</body>
</html>
