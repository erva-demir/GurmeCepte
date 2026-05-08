//ödeme sayfası-kart kupon
<?php
require_once __DIR__ . '/../src/core/db.php';
require_once __DIR__ . '/../src/services/siparis.php';
require_once __DIR__ . '/../src/data/sehirler.php';

if (!girisYapildi()) { header('Location: ' . SITE_URL . '/modules/giris.php'); exit; }

$db = getDB();
$hata = ''; $basari = ''; $siparis_id = null;

$stmt = $db->prepare("SELECT * FROM kullanicilar WHERE id=?");
$stmt->execute([$_SESSION['kullanici_id']]);
$kullanici = $stmt->fetch();

// Kuponları çek
$stmt2 = $db->prepare("SELECT * FROM kuponlar WHERE kullanici_id=? AND kullanildi=0 AND (son_kullanim IS NULL OR son_kullanim > NOW()) ORDER BY id DESC");
$stmt2->execute([$_SESSION['kullanici_id']]);
$aktifKuponlar = $stmt2->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sepet_json  = $_POST['sepet_json'] ?? '[]';
    $restoran_id = intval($_POST['restoran_id'] ?? 0);
    $sehir       = trim($_POST['sehir'] ?? '');
    $ilce        = trim($_POST['ilce'] ?? '');
    $adres       = trim($_POST['adres_detay'] ?? '');
    $odeme       = $_POST['odeme'] ?? 'nakit';
    $kupon_puan  = intval($_POST['kupon_puan'] ?? 0);
    $kupon_kod   = trim($_POST['kupon_kod'] ?? '');
    $notlar      = trim($_POST['notlar'] ?? '');
    $kart_son4   = '';

    $sepet = json_decode($sepet_json, true);
    if (!is_array($sepet)) $sepet = [];

    if (empty($sepet))      { $hata = 'Sepetiniz boş! Önce ürün ekleyin.'; }
    elseif (!$sehir || !$ilce) { $hata = 'Lütfen şehir ve ilçe seçin.'; }
    elseif (!$adres)        { $hata = 'Açık adresinizi yazın.'; }
    elseif (!$restoran_id)  { $hata = 'Restoran bilgisi eksik. Lütfen sepete ürün ekleyip tekrar deneyin.'; }
    else {
        // Kupon kodu kontrolü
        $kupon_indirim = 0;
        if ($kupon_kod) {
            $kstmt = $db->prepare("SELECT * FROM kuponlar WHERE kod=? AND kullanici_id=? AND kullanildi=0 AND (son_kullanim IS NULL OR son_kullanim > NOW())");
            $kstmt->execute([$kupon_kod, $_SESSION['kullanici_id']]);
            $kupon = $kstmt->fetch();
            if ($kupon) {
                $kupon_indirim = $kupon['indirim_miktari'];
            } else {
                $hata = 'Geçersiz veya kullanılmış kupon kodu.';
            }
        }

        if (!$hata) {
            if ($odeme === 'online') {
                $kart_no  = preg_replace('/\s+/', '', $_POST['kart_no'] ?? '');
                $kart_ad  = trim($_POST['kart_ad'] ?? '');
                $kart_ay  = $_POST['kart_ay'] ?? '';
                $kart_yil = $_POST['kart_yil'] ?? '';
                $kart_cvv = $_POST['kart_cvv'] ?? '';
                if (strlen($kart_no) < 16 || !$kart_ad || !$kart_ay || !$kart_yil || strlen($kart_cvv) < 3) {
                    $hata = 'Lütfen kart bilgilerini eksiksiz girin.';
                }
                $kart_son4 = substr($kart_no, -4);
            }
        }

        if (!$hata) {
            $tam_adres = $adres . ', ' . $ilce . ', ' . $sehir;
            $siparis_id = siparisOlusturGelismis(
                $_SESSION['kullanici_id'], $restoran_id, $sepet,
                $sehir, $ilce, $tam_adres, $odeme, $kupon_puan, $notlar, $kart_son4
            );

            // Kupon kodunu kullanıldı yap
            if ($siparis_id && $kupon_kod && $kupon_indirim > 0) {
                $db->prepare("UPDATE kuponlar SET kullanildi=1 WHERE kod=? AND kullanici_id=?")
                   ->execute([$kupon_kod, $_SESSION['kullanici_id']]);
            }

            if ($siparis_id) {
                $basari = 'Siparişiniz başarıyla alındı!';
            } else {
                $hata = 'Bir hata oluştu, lütfen tekrar deneyin.';
            }
        }
    }
}

$kullanici_puani = (int)($kullanici['kupon_puani'] ?? 0);
$sehirler = getSehirler();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme - GurmeCepte</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/src/ui/style.css">
    <style>
        .kart-gorsel {
            background: linear-gradient(135deg, #1C1917, #3D2B1F);
            border-radius: 16px;
            padding: 24px;
            color: white;
            margin-bottom: 18px;
            position: relative;
            min-height: 180px;
            overflow: hidden;
        }
        .kart-gorsel::before { content:''; position:absolute; top:-40px; right:-40px; width:160px; height:160px; background:rgba(249,115,22,0.15); border-radius:50%; }
        .kart-gorsel::after  { content:''; position:absolute; bottom:-60px; left:-40px; width:200px; height:200px; background:rgba(251,191,36,0.08); border-radius:50%; }
        .kart-no-display { font-size:1.3rem; letter-spacing:4px; font-family:monospace; margin:20px 0 8px; position:relative; z-index:1; }
        .kart-tipi { display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap; }
        .kart-badge { padding:5px 14px; border-radius:8px; border:1.5px solid #E7E5E4; font-size:0.8rem; font-weight:700; cursor:pointer; transition:all .2s; }
        .kart-badge.visa { color:#1A1F71; } .kart-badge.mastercard { color:#EB001B; }
        .kart-badge.troy { color:#009B4D; } .kart-badge.amex { color:#007BC1; }
        .kart-badge.aktif { border-color:var(--turuncu); background:#FFF7F2; }

        .kupon-item { display:flex; align-items:center; justify-content:space-between; padding:12px 14px; border:1.5px solid #E7E5E4; border-radius:10px; margin-bottom:8px; cursor:pointer; transition:all .2s; }
        .kupon-item:hover, .kupon-item.secili { border-color:var(--turuncu); background:#FFF7F2; }
        .kupon-item .kupon-kod { font-weight:800; font-family:monospace; letter-spacing:2px; font-size:0.9rem; }
        .kupon-item .kupon-indirim { color:var(--yesil); font-weight:700; font-size:0.9rem; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../src/ui/navbar.php'; ?>

<div style="background:var(--koyu);padding:28px 6%;color:white">
    <h1 style="font-family:'Nunito',sans-serif;font-size:1.8rem;font-weight:800">🛒 Siparişi Tamamla</h1>
</div>

<?php if ($basari && $siparis_id): ?>
<!-- BAŞARI SAYFASI -->
<div style="max-width:560px;margin:60px auto;text-align:center;padding:0 20px">
    <div style="background:white;border-radius:20px;padding:48px;box-shadow:var(--golge-buyuk);border:1px solid #F0EDE9">
        <div style="font-size:5rem;margin-bottom:16px">🎉</div>
        <h2 style="font-family:'Nunito',sans-serif;font-size:2rem;font-weight:900;margin-bottom:10px;color:var(--yesil)">Sipariş Alındı!</h2>
        <div style="background:var(--acik-gri);border-radius:12px;padding:16px;margin-bottom:20px">
            <div style="font-size:0.82rem;color:var(--gri);margin-bottom:4px">Sipariş Numaranız</div>
            <div style="font-family:'Nunito',sans-serif;font-size:2rem;font-weight:900;color:var(--turuncu)">#<?= str_pad($siparis_id,6,'0',STR_PAD_LEFT) ?></div>
        </div>
        <p style="color:var(--gri);font-size:0.88rem;margin-bottom:24px">
            🏆 Bu siparişten <strong>puan kazandınız!</strong><br>
            Restoranınız siparişinizi aldı ve hazırlamaya başladı.
        </p>
        <div style="display:flex;gap:12px;justify-content:center">
            <a href="<?= SITE_URL ?>/modules/siparislerim.php" class="btn btn-primary">Siparişimi Takip Et</a>
            <a href="<?= SITE_URL ?>/index.php" class="btn btn-secondary">Ana Sayfa</a>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ÖDEME FORMU -->
<div class="odeme-grid">
    <div>
        <?php if ($hata): ?>
            <div class="alert alert-hata">⚠️ <?= htmlspecialchars($hata) ?></div>
        <?php endif; ?>

        <form method="POST" id="odeme-form" onsubmit="return formGonder(event)">
            <!-- Gizli sepet alanları -->
            <input type="hidden" name="sepet_json" id="sepet-json-input" value="">
            <input type="hidden" name="restoran_id" id="restoran-id-input" value="">

            <!-- TESLİMAT ADRESİ -->
            <div class="panel-kart">
                <h3>📍 Teslimat Adresi</h3>
                <div class="form-grid">
                    <div class="form-grup">
                        <label>Şehir *</label>
                        <select name="sehir" id="sehir-select" onchange="ilceleriYukle(this.value)" required>
                            <option value="">Şehir seçin</option>
                            <?php foreach (array_keys($sehirler) as $s): ?>
                                <option value="<?= $s ?>" <?= ($kullanici['sehir']===$s)?'selected':'' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grup">
                        <label>İlçe *</label>
                        <select name="ilce" id="ilce-select" required>
                            <?php if ($kullanici['sehir'] && isset($sehirler[$kullanici['sehir']])): ?>
                                <?php foreach ($sehirler[$kullanici['sehir']] as $i): ?>
                                    <option value="<?= $i ?>" <?= ($kullanici['ilce']===$i)?'selected':'' ?>><?= $i ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">Önce şehir seçin</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="form-grup">
                    <label>Açık Adres *</label>
                    <input type="text" name="adres_detay" placeholder="Mahalle, cadde, bina no, daire..." required
                           value="<?= htmlspecialchars($kullanici['adres_detay'] ?? '') ?>">
                </div>
                <div class="form-grup">
                    <label>Kapıya Not</label>
                    <input type="text" name="notlar" placeholder="Kapı kodu, kat, tarif...">
                </div>
            </div>

            <!-- ÖDEME YÖNTEMİ -->
            <div class="panel-kart">
                <h3>💳 Ödeme Yöntemi</h3>
                <div class="odeme-yontem-kart secili" onclick="secOdeme(this,'nakit')">
                    <span class="odeme-yontem-icon">💵</span>
                    <div><div class="odeme-yontem-isim">Kapıda Nakit</div><div style="font-size:0.78rem;color:var(--gri)">Teslimat sırasında nakit öde</div></div>
                </div>
                <div class="odeme-yontem-kart" onclick="secOdeme(this,'kart_kapi')">
                    <span class="odeme-yontem-icon">💳</span>
                    <div><div class="odeme-yontem-isim">Kapıda Kart</div><div style="font-size:0.78rem;color:var(--gri)">Teslimat sırasında kartla öde</div></div>
                </div>
                <div class="odeme-yontem-kart" onclick="secOdeme(this,'online')">
                    <span class="odeme-yontem-icon">🌐</span>
                    <div><div class="odeme-yontem-isim">Online Ödeme</div><div style="font-size:0.78rem;color:var(--gri)">Kredi / Banka Kartı ile öde</div></div>
                </div>
                <input type="hidden" name="odeme" id="odeme-secim" value="nakit">

                <!-- KART FORMU -->
                <div id="kart-formu" style="display:none;margin-top:20px">
                    <div class="kart-gorsel">
                        <div style="display:flex;justify-content:space-between;align-items:center;position:relative;z-index:1">
                            <span style="font-size:0.73rem;opacity:0.6;text-transform:uppercase;letter-spacing:1px">GurmeCepte Pay</span>
                            <span id="kart-tip-logo" style="font-size:1rem;font-weight:700">💳</span>
                        </div>
                        <div class="kart-no-display" id="kart-no-display">•••• •••• •••• ••••</div>
                        <div style="display:flex;justify-content:space-between;position:relative;z-index:1;font-size:0.8rem">
                            <div><div style="opacity:0.6;font-size:0.68rem;text-transform:uppercase">Kart Sahibi</div><div id="kart-ad-display" style="font-weight:700;margin-top:2px">AD SOYAD</div></div>
                            <div style="text-align:right"><div style="opacity:0.6;font-size:0.68rem;text-transform:uppercase">Son Kull.</div><div id="kart-tarih-display" style="font-weight:700;margin-top:2px">AA/YY</div></div>
                        </div>
                    </div>
                    <div style="font-size:0.78rem;font-weight:700;color:var(--gri);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px">Kabul Edilen Kartlar</div>
                    <div class="kart-tipi">
                        <span class="kart-badge visa aktif" onclick="kartTipSec(this)">VISA</span>
                        <span class="kart-badge mastercard" onclick="kartTipSec(this)">Mastercard</span>
                        <span class="kart-badge troy" onclick="kartTipSec(this)">TROY</span>
                        <span class="kart-badge amex" onclick="kartTipSec(this)">Amex</span>
                    </div>
                    <div class="form-grup">
                        <label>Kart Numarası</label>
                        <input type="text" name="kart_no" id="kart-no-input" placeholder="0000 0000 0000 0000" maxlength="19" oninput="kartNoFormatla(this)" style="font-family:monospace;letter-spacing:2px">
                    </div>
                    <div class="form-grup">
                        <label>Kart Üzerindeki Ad</label>
                        <input type="text" name="kart_ad" placeholder="AD SOYAD" style="text-transform:uppercase" oninput="document.getElementById('kart-ad-display').textContent=this.value.toUpperCase()||'AD SOYAD'">
                    </div>
                    <div class="form-grid">
                        <div class="form-grup">
                            <label>Son Kullanım</label>
                            <div style="display:flex;gap:8px">
                                <select name="kart_ay" id="kart-ay" onchange="tarihGuncelle()" style="flex:1;padding:11px 10px;border:1.5px solid #E7E5E4;border-radius:10px;font-family:'Poppins',sans-serif;font-size:0.88rem;outline:none">
                                    <option value="">Ay</option>
                                    <?php for($m=1;$m<=12;$m++): ?>
                                        <option value="<?= str_pad($m,2,'0',STR_PAD_LEFT) ?>"><?= str_pad($m,2,'0',STR_PAD_LEFT) ?></option>
                                    <?php endfor; ?>
                                </select>
                                <select name="kart_yil" id="kart-yil" onchange="tarihGuncelle()" style="flex:1;padding:11px 10px;border:1.5px solid #E7E5E4;border-radius:10px;font-family:'Poppins',sans-serif;font-size:0.88rem;outline:none">
                                    <option value="">Yıl</option>
                                    <?php for($y=date('Y');$y<=date('Y')+10;$y++): ?>
                                        <option value="<?= substr($y,2) ?>"><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-grup">
                            <label>CVV / CVC</label>
                            <input type="password" name="kart_cvv" placeholder="•••" maxlength="4" style="letter-spacing:4px;font-family:monospace;font-size:1rem">
                        </div>
                    </div>
                    <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;padding:10px 14px;font-size:0.78rem;color:#16A34A;display:flex;align-items:center;gap:8px">
                        🔒 Ödeme bilgileriniz 256-bit SSL şifreleme ile korunmaktadır.
                    </div>
                </div>
            </div>

            <!-- KUPON / PUAN -->
            <div class="panel-kart">
                <h3>🎟️ İndirim & Kupon</h3>

                <!-- Aktif kuponlar -->
                <?php if (!empty($aktifKuponlar)): ?>
                    <div style="font-size:0.83rem;font-weight:600;color:var(--gri);margin-bottom:10px">Kullanılabilir Kuponlarım</div>
                    <?php foreach ($aktifKuponlar as $k): ?>
                        <div class="kupon-item" onclick="kuponSec('<?= $k['kod'] ?>', <?= $k['indirim_miktari'] ?>, this)">
                            <div>
                                <div class="kupon-kod"><?= $k['kod'] ?></div>
                                <div style="font-size:0.75rem;color:var(--gri);margin-top:2px">⏰ <?= date('d.m.Y', strtotime($k['son_kullanim'])) ?>'e kadar</div>
                            </div>
                            <div class="kupon-indirim">-₺<?= number_format($k['indirim_miktari'],0) ?></div>
                        </div>
                    <?php endforeach; ?>
                    <div id="secili-kupon-bilgi" style="display:none;background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;padding:10px 14px;font-size:0.83rem;color:#16A34A;margin-top:8px">
                        ✅ Kupon uygulandı! Sepet toplamından indirim düşülecek.
                    </div>
                <?php endif; ?>

                <!-- Manuel kupon kodu -->
                <div style="margin-top:<?= !empty($aktifKuponlar)?'16px':'0' ?>">
                    <div class="form-grup" style="margin-bottom:8px">
                        <label>Kupon Kodu Girin</label>
                        <div style="display:flex;gap:8px">
                            <input type="text" id="kupon-kod-input" placeholder="GC-XXXXXXXX" style="flex:1;text-transform:uppercase;letter-spacing:1px;font-family:monospace">
                            <button type="button" onclick="kuponUygula()" class="btn btn-secondary" style="white-space:nowrap">Uygula</button>
                        </div>
                    </div>
                    <div id="kupon-mesaj" style="font-size:0.82rem;margin-top:4px"></div>
                    <input type="hidden" name="kupon_kod" id="kupon-kod-hidden" value="">
                </div>

                <!-- Puan kullan -->
                <?php if ($kullanici_puani >= 100): ?>
                    <div style="margin-top:16px;padding-top:16px;border-top:1px solid #F0EDE9">
                        <div style="font-size:0.83rem;font-weight:600;color:var(--gri);margin-bottom:10px">
                            🏆 Puan Kullan — Bakiye: <strong style="color:var(--turuncu)"><?= $kullanici_puani ?> puan</strong>
                        </div>
                        <select name="kupon_puan" onchange="puanHesapla(this.value)"
                                style="width:100%;padding:10px 14px;border:1.5px solid #E7E5E4;border-radius:10px;font-family:'Poppins',sans-serif;font-size:0.88rem;outline:none">
                            <option value="0">Puan kullanma</option>
                            <?php for($i=100;$i<=$kullanici_puani;$i+=100): ?>
                                <option value="<?= $i ?>"><?= $i ?> puan = ₺<?= $i/10 ?> indirim</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="kupon_puan" value="0">
                    <?php if (!empty($aktifKuponlar)): // boşsa hiç gösterme ?>
                    <?php else: ?>
                    <div style="font-size:0.82rem;color:var(--gri);margin-top:10px">
                        💡 Sipariş vererek puan kazanabilirsiniz. 100 puan = 10₺ indirim.
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg" style="font-size:1.05rem">
                ✅ Siparişi Onayla
            </button>
            <p style="text-align:center;font-size:0.78rem;color:var(--gri);margin-top:10px">
                Siparişi onaylayarak <a href="#" style="color:var(--turuncu)">kullanım koşullarını</a> kabul etmiş olursunuz.
            </p>
        </form>
    </div>

    <!-- ÖZET -->
    <div>
        <div class="panel-kart" style="position:sticky;top:80px">
            <h3>🛒 Sipariş Özeti</h3>
            <div id="odeme-sepet-ozet">
                <div style="text-align:center;color:var(--gri);padding:16px;font-size:0.88rem">Sepet yükleniyor...</div>
            </div>
            <hr style="margin:14px 0;border:none;border-top:1.5px solid #F0EDE9">
            <div class="toplam-satir"><span>Ara toplam</span><span id="ozet-ara">₺0.00</span></div>
            <div class="toplam-satir"><span>Teslimat</span><span>Restoranda belirlenir</span></div>
            <div class="toplam-satir" id="ozet-kupon-satir" style="display:none;color:var(--yesil);font-weight:600">
                <span>Kupon İndirimi</span><span id="ozet-kupon-miktar"></span>
            </div>
            <div class="toplam-satir" id="ozet-puan-satir" style="display:none;color:var(--yesil);font-weight:600">
                <span>Puan İndirimi</span><span id="ozet-puan-miktar"></span>
            </div>
            <hr style="margin:10px 0;border:none;border-top:1.5px solid #F0EDE9">
            <div style="display:flex;justify-content:space-between;font-weight:800">
                <span style="font-size:1rem">Toplam</span>
                <span class="toplam-buyuk" id="odeme-toplam">₺0.00</span>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../src/ui/footer.php'; ?>
<script src="<?= SITE_URL ?>/src/ui/main.js"></script>
<script>
const sehirler = <?= json_encode(getSehirler(), JSON_UNESCAPED_UNICODE) ?>;
let secilenKuponIndirim = 0;
let secilenPuanIndirim = 0;

// Form gönderilmeden önce sepet verilerini doldur
function formGonder(e) {
    document.getElementById('sepet-json-input').value = JSON.stringify(sepet);
    const restoranId = aktifRestoranId || localStorage.getItem('gurmecepte_restoran') || '';
    document.getElementById('restoran-id-input').value = restoranId;

    if (sepet.length === 0) {
        e.preventDefault();
        alert('Sepetiniz boş! Lütfen önce ürün ekleyin.');
        return false;
    }
    if (!restoranId) {
        e.preventDefault();
        alert('Restoran bilgisi bulunamadı. Lütfen restoran sayfasına gidip tekrar ekleyin.');
        return false;
    }
    return true;
}

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

function secOdeme(el, deger) {
    document.querySelectorAll('.odeme-yontem-kart').forEach(k => k.classList.remove('secili'));
    el.classList.add('secili');
    document.getElementById('odeme-secim').value = deger;
    document.getElementById('kart-formu').style.display = deger === 'online' ? 'block' : 'none';
}

function kartNoFormatla(input) {
    let v = input.value.replace(/\D/g,'').substring(0,16);
    let parts = []; for (let i=0;i<v.length;i+=4) parts.push(v.substring(i,i+4));
    input.value = parts.join(' ');
    let disp = v.padEnd(16,'•'); let dp = [];
    for (let i=0;i<16;i+=4) dp.push(disp.substring(i,i+4));
    document.getElementById('kart-no-display').textContent = dp.join(' ');
    const tip = document.getElementById('kart-tip-logo');
    if (v.startsWith('4')) tip.textContent = '💙 VISA';
    else if (v.startsWith('5')||v.startsWith('2')) tip.textContent = '🔴 MC';
    else if (v.startsWith('9')) tip.textContent = '🟢 TROY';
    else if (v.startsWith('3')) tip.textContent = '💠 Amex';
    else tip.textContent = '💳';
}

function tarihGuncelle() {
    const ay = document.getElementById('kart-ay').value;
    const yil = document.getElementById('kart-yil').value;
    document.getElementById('kart-tarih-display').textContent = (ay||'AA')+'/'+(yil||'YY');
}

function kartTipSec(el) {
    document.querySelectorAll('.kart-badge').forEach(b => b.classList.remove('aktif'));
    el.classList.add('aktif');
}

function kuponSec(kod, indirim, el) {
    // Toggle
    const secili = el.classList.contains('secili');
    document.querySelectorAll('.kupon-item').forEach(k => k.classList.remove('secili'));
    const bilgi = document.getElementById('secili-kupon-bilgi');
    if (secili) {
        secilenKuponIndirim = 0;
        document.getElementById('kupon-kod-hidden').value = '';
        if (bilgi) bilgi.style.display = 'none';
    } else {
        el.classList.add('secili');
        secilenKuponIndirim = parseFloat(indirim);
        document.getElementById('kupon-kod-hidden').value = kod;
        if (bilgi) bilgi.style.display = 'block';
    }
    toplamGuncelle();
}

function kuponUygula() {
    const kod = document.getElementById('kupon-kod-input').value.trim().toUpperCase();
    const mesaj = document.getElementById('kupon-mesaj');
    if (!kod) { mesaj.innerHTML = '<span style="color:var(--kirmizi)">Kupon kodu girin.</span>'; return; }
    fetch('<?= SITE_URL ?>/modules/kupon-kontrol.php?kod=' + encodeURIComponent(kod))
        .then(r => r.json())
        .then(data => {
            if (data.gecerli) {
                secilenKuponIndirim = data.indirim;
                document.getElementById('kupon-kod-hidden').value = kod;
                mesaj.innerHTML = '<span style="color:var(--yesil)">✅ ' + data.indirim + '₺ indirim uygulandı!</span>';
                toplamGuncelle();
            } else {
                mesaj.innerHTML = '<span style="color:var(--kirmizi)">❌ ' + data.mesaj + '</span>';
            }
        })
        .catch(() => { mesaj.innerHTML = '<span style="color:var(--kirmizi)">Bir hata oluştu.</span>'; });
}

function puanHesapla(puan) {
    const p = parseInt(puan)||0;
    secilenPuanIndirim = Math.floor(p/100)*10;
    const satir = document.getElementById('ozet-puan-satir');
    const miktar = document.getElementById('ozet-puan-miktar');
    if (secilenPuanIndirim > 0) {
        satir.style.display = 'flex';
        miktar.textContent = '-₺' + secilenPuanIndirim;
    } else {
        satir.style.display = 'none';
    }
    toplamGuncelle();
}

function toplamGuncelle() {
    const ara = sepet.reduce((t,i) => t + i.fiyat*i.adet, 0);
    const kuponSatir = document.getElementById('ozet-kupon-satir');
    const kuponMiktar = document.getElementById('ozet-kupon-miktar');
    if (secilenKuponIndirim > 0) {
        kuponSatir.style.display = 'flex';
        kuponMiktar.textContent = '-₺' + secilenKuponIndirim.toFixed(2);
    } else {
        kuponSatir.style.display = 'none';
    }
    const toplam = Math.max(0, ara - secilenKuponIndirim - secilenPuanIndirim);
    document.getElementById('odeme-toplam').textContent = '₺' + toplam.toFixed(2);
}

document.addEventListener('DOMContentLoaded', () => {
    const ozet = document.getElementById('odeme-sepet-ozet');
    const araEl = document.getElementById('ozet-ara');
    if (ozet) {
        if (sepet.length > 0) {
            let ara = 0;
            ozet.innerHTML = sepet.map(item => {
                const f = item.fiyat * item.adet; ara += f;
                return `<div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:0.87rem;align-items:start">
                    <span style="flex:1;margin-right:8px">${item.adet}× ${item.isim}${item.cikarilan&&item.cikarilan.length?' <span style="font-size:0.72rem;color:var(--gri)">(−'+item.cikarilan.join(', ')+')</span>':''}</span>
                    <span style="font-weight:700;white-space:nowrap">₺${f.toFixed(2)}</span>
                </div>`;
            }).join('');
            if (araEl) araEl.textContent = '₺'+ara.toFixed(2);
            document.getElementById('odeme-toplam').textContent = '₺'+ara.toFixed(2);
        } else {
            ozet.innerHTML = '<div style="text-align:center;color:var(--gri);font-size:0.88rem;padding:16px">Sepetiniz boş<br><a href="<?= SITE_URL ?>/modules/restoranlar.php" style="color:var(--turuncu);font-weight:600">Restoranlara git →</a></div>';
        }
    }
});
</script>
</body>
</html>
