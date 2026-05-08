<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Restoran.php';

// Geriye uyumluluk için eski fonksiyonlar - artık Restoran sınıfını kullanıyor

function tumRestoranlar($kategori = null) {
    $r = new Restoran();
    return $r->tumunuGetir($kategori);
}

function restoranGetir($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM restoranlar WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function restoranYemekleri($restoran_id) {
    $r = new Restoran($restoran_id);
    return $r->yemekleriGetir();
}

function yemekMalzemeleri($yemek_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM malzemeler WHERE yemek_id = ?");
    $stmt->execute([$yemek_id]);
    return $stmt->fetchAll();
}

function yemekEkle($restoran_id, $kategori_id, $isim, $aciklama, $fiyat, $resim = null) {
    $r = new Restoran($restoran_id);
    return $r->yemekEkle($kategori_id, $isim, $aciklama, $fiyat, $resim);
}

function yemekFiyatGuncelle($yemek_id, $fiyat, $restoran_id) {
    $r = new Restoran($restoran_id);
    return $r->fiyatGuncelle($yemek_id, $fiyat);
}

function restoranYorumlar($restoran_id) {
    $r = new Restoran($restoran_id);
    return $r->yorumlariGetir();
}

function yorumEkle($kullanici_id, $restoran_id, $puan, $yorum) {
    $r = new Restoran($restoran_id);
    return $r->yorumEkle($kullanici_id, $puan, $yorum);
}

function aktifIndirimler() {
    $r = new Restoran();
    return $r->aktifIndirimleriGetir();
}

function tumKategoriler() {
    $db = getDB();
    return $db->query("SELECT * FROM kategoriler ORDER BY isim")->fetchAll();
}

function kullanicininRestorani($kullanici_id) {
    $r = new Restoran();
    return $r->kullanicininRestorani($kullanici_id);
}

function restoranlarIlceye($ilce, $sehir) {
    $r = new Restoran();
    return $r->ilceyeGoreGetir($ilce, $sehir);
}
