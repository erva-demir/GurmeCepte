<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Siparis.php';

// Geriye uyumluluk için eski fonksiyonlar - artık Siparis sınıfını kullanıyor

function siparisOlustur($kullanici_id, $restoran_id, $sepet, $adres, $odeme, $kupon_puani = 0, $notlar = '') {
    $s = new Siparis();
    return $s->olustur($kullanici_id, $restoran_id, $sepet, '', '', $adres, $odeme, $kupon_puani, $notlar);
}

function siparisOlusturGelismis($kullanici_id, $restoran_id, $sepet, $sehir, $ilce,
                                  $adres, $odeme, $kupon_puani = 0, $notlar = '', $kart_son4 = '') {
    $s = new Siparis();
    return $s->olustur($kullanici_id, $restoran_id, $sepet, $sehir, $ilce, $adres, $odeme, $kupon_puani, $notlar, $kart_son4);
}

function kullaniciSiparisleri($kullanici_id) {
    $s = new Siparis();
    return $s->kullanicininSiparisleri($kullanici_id);
}

function siparisDetay($siparis_id) {
    $s = new Siparis($siparis_id);
    return $s->detaylariGetir();
}

function kuponeVur($kullanici_id, $puan) {
    $db = getDB();
    $stmt = $db->prepare("SELECT kupon_puani FROM kullanicilar WHERE id = ?");
    $stmt->execute([$kullanici_id]);
    $k = $stmt->fetch();
    if ($k['kupon_puani'] < $puan) return false;

    $kod = 'GC-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $indirim = floor($puan / 100) * 10;
    $db->prepare("INSERT INTO kuponlar (kullanici_id, kod, indirim_miktari, son_kullanim) VALUES (?,?,?,?)")
       ->execute([$kullanici_id, $kod, $indirim, date('Y-m-d H:i:s', strtotime('+30 days'))]);
    $db->prepare("UPDATE kullanicilar SET kupon_puani = kupon_puani - ? WHERE id = ?")
       ->execute([$puan, $kullanici_id]);
    $_SESSION['kupon_puani'] -= $puan;
    return $kod;
}
