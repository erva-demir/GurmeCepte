<?php
// GurmeCepte - Veritabanı Bağlantı Testi
require_once __DIR__ . '/../src/core/db.php';

echo "<h2>GurmeCepte - Test Sonuçları</h2>";

// DB bağlantısı
try {
    $db = getDB();
    echo "✅ Veritabanı bağlantısı başarılı<br>";
} catch (Exception $e) {
    echo "❌ Veritabanı hatası: " . $e->getMessage() . "<br>";
}

// Tablo kontrolleri
$tablolar = ['kullanicilar','restoranlar','yemekler','siparisler','yorumlar','kuponlar'];
foreach ($tablolar as $tablo) {
    try {
        $stmt = $db->query("SELECT COUNT(*) as sayi FROM $tablo");
        $r = $stmt->fetch();
        echo "✅ $tablo tablosu — {$r['sayi']} kayıt<br>";
    } catch (Exception $e) {
        echo "❌ $tablo tablosu bulunamadı<br>";
    }
}
