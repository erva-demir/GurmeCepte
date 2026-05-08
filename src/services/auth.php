<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Kullanici.php';

// Kullanici OOP sınıfını kullanan servis fonksiyonları

function kullaniciKayit($ad, $soyad, $email, $sifre, $telefon, $rol = 'kullanici') {
    $k = new Kullanici();
    return $k->kayitOl($ad, $soyad, $email, $sifre, $telefon, $rol);
}

function kullaniciGiris($email, $sifre) {
    $k = new Kullanici();
    return $k->girisYap($email, $sifre);
}

function cikisYap() {
    $k = new Kullanici();
    $k->cikisYap();
}

// NOT: girisYapildi(), restoranGirisi(), adminGirisi(), oturumKullanici()
// fonksiyonları db.php içinde tanımlı, burada tekrar yazmıyoruz.
