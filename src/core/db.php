//Veritabanı bağlantısı ve session yönetimi
<?php
// GurmeCepte - Veritabanı Bağlantısı
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'gurmecepte');
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/GurmeCepte');
if (!defined('SITE_NAME')) define('SITE_NAME', 'GurmeCepte');

$pdo = null;

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die(json_encode(['hata' => 'Veritabanı bağlantı hatası: ' . $e->getMessage()]));
}

function getDB() {
    global $pdo;
    return $pdo;
}

if (session_status() === PHP_SESSION_NONE) session_start();

function girisYapildi() {
    return isset($_SESSION['kullanici_id']);
}

function restoranGirisi() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'restoran';
}

function adminGirisi() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

function oturumKullanici() {
    return $_SESSION ?? [];
}
?>
