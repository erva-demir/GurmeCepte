<?php
require_once __DIR__ . '/../src/core/db.php';

header('Content-Type: application/json');

if (!girisYapildi()) {
    echo json_encode(['gecerli'=>false,'mesaj'=>'Giriş yapın.']);
    exit;
}

$kod = trim($_GET['kod'] ?? '');
if (!$kod) {
    echo json_encode(['gecerli'=>false,'mesaj'=>'Kod boş.']);
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM kuponlar WHERE kod=? AND kullanici_id=? AND kullanildi=0 AND (son_kullanim IS NULL OR son_kullanim > NOW())");
$stmt->execute([$kod, $_SESSION['kullanici_id']]);
$kupon = $stmt->fetch();

if ($kupon) {
    echo json_encode(['gecerli'=>true,'indirim'=>(float)$kupon['indirim_miktari']]);
} else {
    echo json_encode(['gecerli'=>false,'mesaj'=>'Geçersiz veya kullanılmış kupon.']);
}
