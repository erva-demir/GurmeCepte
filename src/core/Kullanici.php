//OOP Sınıfı
<?php
require_once __DIR__ . '/db.php';

/**
 * SINIF 1: Kullanici (Ana Sınıf)
 * OOP: ENCAPSULATION (Kapsülleme) + INHERITANCE (Kalıtım) için temel sınıf
 * 
 * Private değişkenler dışarıdan erişilemiyor,
 * sadece public metodlar aracılığıyla erişim sağlanıyor.
 */
class Kullanici {

    // ENCAPSULATION: Private değişkenler - dışarıdan doğrudan erişilemez
    private $db;
    protected $id;
    protected $ad;
    protected $soyad;
    protected $email;
    protected $rol;
    protected $kupon_puani;

    public function __construct() {
        $this->db = getDB();
    }

    // Kayıt ol
    public function kayitOl($ad, $soyad, $email, $sifre, $telefon, $rol = 'kullanici') {
        $sifreHash = password_hash($sifre, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            "INSERT INTO kullanicilar (ad, soyad, email, sifre, telefon, rol) VALUES (?,?,?,?,?,?)"
        );
        return $stmt->execute([$ad, $soyad, $email, $sifreHash, $telefon, $rol]);
    }

    // Giriş yap
    public function girisYap($email, $sifre) {
        $stmt = $this->db->prepare("SELECT * FROM kullanicilar WHERE email = ?");
        $stmt->execute([$email]);
        $kullanici = $stmt->fetch();
        if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
            $this->id          = $kullanici['id'];
            $this->ad          = $kullanici['ad'];
            $this->soyad       = $kullanici['soyad'];
            $this->email       = $kullanici['email'];
            $this->rol         = $kullanici['rol'];
            $this->kupon_puani = $kullanici['kupon_puani'];
            $_SESSION['kullanici_id'] = $kullanici['id'];
            $_SESSION['ad']           = $kullanici['ad'];
            $_SESSION['soyad']        = $kullanici['soyad'];
            $_SESSION['email']        = $kullanici['email'];
            $_SESSION['rol']          = $kullanici['rol'];
            $_SESSION['kupon_puani']  = $kullanici['kupon_puani'];
            $_SESSION['sehir']        = $kullanici['sehir'] ?? '';
            $_SESSION['ilce']         = $kullanici['ilce'] ?? '';
            return $kullanici;
        }
        return false;
    }

    // Çıkış yap
    public function cikisYap() {
        session_destroy();
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }

    // Puan kazan
    public function puanKazan($siparisTutari) {
        $kazanilan = floor($siparisTutari / 10);
        $db = getDB();
        $db->prepare("UPDATE kullanicilar SET kupon_puani = kupon_puani + ? WHERE id = ?")
           ->execute([$kazanilan, $_SESSION['kullanici_id']]);
        $_SESSION['kupon_puani'] += $kazanilan;
        return $kazanilan;
    }

    // Kupona çevir
    public function kuponeVur($puan) {
        if ($puan < 100 || $puan % 100 !== 0) return false;
        $db = getDB();
        $stmt = $db->prepare("SELECT kupon_puani FROM kullanicilar WHERE id = ?");
        $stmt->execute([$_SESSION['kullanici_id']]);
        $k = $stmt->fetch();
        if ($k['kupon_puani'] < $puan) return false;
        $kod = 'GC-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $indirim = floor($puan / 100) * 10;
        $db->prepare("INSERT INTO kuponlar (kullanici_id, kod, indirim_miktari, son_kullanim) VALUES (?,?,?,?)")
           ->execute([$_SESSION['kullanici_id'], $kod, $indirim, date('Y-m-d H:i:s', strtotime('+30 days'))]);
        $db->prepare("UPDATE kullanicilar SET kupon_puani = kupon_puani - ? WHERE id = ?")
           ->execute([$puan, $_SESSION['kullanici_id']]);
        $_SESSION['kupon_puani'] -= $puan;
        return $kod;
    }

    // Adres güncelle
    public function adresGuncelle($sehir, $ilce, $adres, $telefon) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE kullanicilar SET sehir=?, ilce=?, adres_detay=?, telefon=? WHERE id=?");
        $r = $stmt->execute([$sehir, $ilce, $adres, $telefon, $_SESSION['kullanici_id']]);
        if ($r) { $_SESSION['sehir'] = $sehir; $_SESSION['ilce'] = $ilce; }
        return $r;
    }

    // ENCAPSULATION: Getter metodları - private değişkenlere kontrollü erişim
    public function getId()    { return $this->id; }
    public function getAd()    { return $this->ad; }
    public function getSoyad() { return $this->soyad; }
    public function getEmail() { return $this->email; }
    public function getRol()   { return $this->rol; }
    public function getPuan()  { return $this->kupon_puani; }

    // POLYMORPHISM için: Alt sınıfların override edeceği metod
    public function getBilgi() {
        return "Kullanıcı: " . $this->ad . " " . $this->soyad;
    }

    // Rol kontrolü
    public function yetkiVarMi($gerekliRol) {
        return $_SESSION['rol'] === $gerekliRol;
    }

    // Static metodlar
    public static function girisYapildi() { return isset($_SESSION['kullanici_id']); }
    public static function restoranMi()   { return isset($_SESSION['rol']) && $_SESSION['rol'] === 'restoran'; }
    public static function adminMi()      { return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'; }
}


/**
 * SINIF 2: RestoranSahibi
 * OOP: INHERITANCE (Kalıtım) — Kullanici sınıfından türetildi
 * Kullanici'nın tüm özelliklerini miras alır + kendi özelliklerini ekler
 */
class RestoranSahibi extends Kullanici {

    private $restoran_id;
    private $restoran_isim;

    public function __construct() {
        parent::__construct(); // Üst sınıfın constructor'ını çağır
    }

    // POLYMORPHISM: getBilgi() metodunu override et
    public function getBilgi() {
        return "Restoran Sahibi: " . $this->ad . " | Restoran: " . $this->restoran_isim;
    }

    // Restoranı yükle
    public function restoraniBul($kullanici_id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM restoranlar WHERE kullanici_id = ?");
        $stmt->execute([$kullanici_id]);
        $r = $stmt->fetch();
        if ($r) {
            $this->restoran_id   = $r['id'];
            $this->restoran_isim = $r['isim'];
        }
        return $r;
    }

    // Yemek ekle (sadece restoran sahibi yapabilir)
    public function yemekEkle($kategori_id, $isim, $aciklama, $fiyat) {
        $db = getDB();
        $stmt = $db->prepare(
            "INSERT INTO yemekler (restoran_id, kategori_id, isim, aciklama, fiyat) VALUES (?,?,?,?,?)"
        );
        return $stmt->execute([$this->restoran_id, $kategori_id, $isim, $aciklama, $fiyat]);
    }

    // Fiyat güncelle
    public function fiyatGuncelle($yemek_id, $yeni_fiyat) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE yemekler SET fiyat = ? WHERE id = ? AND restoran_id = ?");
        return $stmt->execute([$yeni_fiyat, $yemek_id, $this->restoran_id]);
    }

    // Sipariş durumunu güncelle
    public function siparisDurumGuncelle($siparis_id, $yeni_durum) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE siparisler SET durum = ? WHERE id = ? AND restoran_id = ?");
        return $stmt->execute([$yeni_durum, $siparis_id, $this->restoran_id]);
    }

    // Getter
    public function getRestoranId()   { return $this->restoran_id; }
    public function getRestoranIsim() { return $this->restoran_isim; }
}


/**
 * SINIF 3: Admin
 * OOP: INHERITANCE (Kalıtım) — Kullanici sınıfından türetildi
 * POLYMORPHISM — getBilgi() metodunu farklı şekilde override eder
 */
class Admin extends Kullanici {

    public function __construct() {
        parent::__construct();
    }

    // POLYMORPHISM: getBilgi() metodunu override et
    public function getBilgi() {
        return "Admin: " . $this->ad . " | Tüm yetkilere sahip";
    }

    // Tüm kullanıcıları getir (sadece admin yapabilir)
    public function tumKullanicilariGetir() {
        $db = getDB();
        return $db->query("SELECT id, ad, soyad, email, rol, kupon_puani FROM kullanicilar ORDER BY id DESC")->fetchAll();
    }

    // Tüm siparişleri getir
    public function tumSiparisleriGetir() {
        $db = getDB();
        return $db->query(
            "SELECT s.*, k.ad, k.soyad, r.isim as restoran_isim
             FROM siparisler s
             JOIN kullanicilar k ON s.kullanici_id = k.id
             JOIN restoranlar r ON s.restoran_id = r.id
             ORDER BY s.olusturma_tarihi DESC"
        )->fetchAll();
    }

    // Restoran aktiflik durumu değiştir
    public function restoranDurumDegistir($restoran_id, $aktif) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE restoranlar SET aktif = ? WHERE id = ?");
        return $stmt->execute([$aktif, $restoran_id]);
    }
}
