<?php
require_once __DIR__ . '/db.php';

class Siparis {
    private $db;
    private $id;
    private $kullanici_id;
    private $restoran_id;
    private $toplam_fiyat;
    private $durum;

    public function __construct($id = null) {
        $this->db = getDB();
        if ($id) {
            $this->yukle($id);
        }
    }

    // Veriyi yükle
    private function yukle($id) {
        $stmt = $this->db->prepare("SELECT * FROM siparisler WHERE id = ?");
        $stmt->execute([$id]);
        $s = $stmt->fetch();
        if ($s) {
            $this->id           = $s['id'];
            $this->kullanici_id = $s['kullanici_id'];
            $this->restoran_id  = $s['restoran_id'];
            $this->toplam_fiyat = $s['toplam_fiyat'];
            $this->durum        = $s['durum'];
        }
    }

    // Sipariş oluştur
    public function olustur($kullanici_id, $restoran_id, $sepet, $sehir, $ilce,
                             $adres, $odeme, $kupon_puan = 0, $notlar = '', $kart_son4 = '') {
        $toplam = 0;
        foreach ($sepet as $item) {
            $toplam += $item['fiyat'] * $item['adet'];
        }

        // İndirim hesapla
        $indirim = ($kupon_puan >= 100) ? min(floor($kupon_puan / 100) * 10, $toplam * 0.5) : 0;
        $toplam -= $indirim;

        $tam_adres = $adres . ', ' . $ilce . ', ' . $sehir;

        $stmt = $this->db->prepare(
            "INSERT INTO siparisler
             (kullanici_id, restoran_id, toplam_fiyat, teslimat_sehir, teslimat_ilce,
              teslimat_adres, odeme_yontemi, kart_son4, kupon_kullanildi, notlar)
             VALUES (?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $kullanici_id, $restoran_id, $toplam, $sehir, $ilce,
            $tam_adres, $odeme, $kart_son4, $kupon_puan, $notlar
        ]);

        $this->id           = $this->db->lastInsertId();
        $this->kullanici_id = $kullanici_id;
        $this->restoran_id  = $restoran_id;
        $this->toplam_fiyat = $toplam;
        $this->durum        = 'beklemede';

        // Detayları kaydet
        $this->detaylariKaydet($sepet);

        // Puan güncelle
        $this->puanGuncelle($kullanici_id, $toplam, $kupon_puan);

        return $this->id;
    }

    // Sipariş detaylarını kaydet
    private function detaylariKaydet($sepet) {
        foreach ($sepet as $item) {
            $this->db->prepare(
                "INSERT INTO siparis_detaylari
                 (siparis_id, yemek_id, adet, birim_fiyat, cikarilan_malzemeler)
                 VALUES (?,?,?,?,?)"
            )->execute([
                $this->id,
                $item['yemek_id'],
                $item['adet'],
                $item['fiyat'],
                implode(', ', $item['cikarilan'] ?? [])
            ]);
        }
    }

    // Puan güncelle
    private function puanGuncelle($kullanici_id, $toplam, $kupon_puan) {
        $kazanilan = floor($toplam / 10);
        $this->db->prepare(
            "UPDATE kullanicilar SET kupon_puani = kupon_puani - ? + ? WHERE id = ?"
        )->execute([$kupon_puan, $kazanilan, $kullanici_id]);
        $_SESSION['kupon_puani'] = ($_SESSION['kupon_puani'] ?? 0) - $kupon_puan + $kazanilan;
    }

    // Durum güncelle
    public function durumGuncelle($yeni_durum, $restoran_id) {
        $stmt = $this->db->prepare(
            "UPDATE siparisler SET durum = ? WHERE id = ? AND restoran_id = ?"
        );
        $r = $stmt->execute([$yeni_durum, $this->id, $restoran_id]);
        if ($r) $this->durum = $yeni_durum;
        return $r;
    }

    // Kullanıcının siparişlerini getir
    public function kullanicininSiparisleri($kullanici_id) {
        $stmt = $this->db->prepare(
            "SELECT s.*, r.isim as restoran_isim
             FROM siparisler s
             JOIN restoranlar r ON s.restoran_id = r.id
             WHERE s.kullanici_id = ?
             ORDER BY s.olusturma_tarihi DESC"
        );
        $stmt->execute([$kullanici_id]);
        return $stmt->fetchAll();
    }

    // Sipariş detaylarını getir
    public function detaylariGetir() {
        $stmt = $this->db->prepare(
            "SELECT sd.*, y.isim as yemek_isim
             FROM siparis_detaylari sd
             JOIN yemekler y ON sd.yemek_id = y.id
             WHERE sd.siparis_id = ?"
        );
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    // Getterlar
    public function getId()          { return $this->id; }
    public function getToplam()      { return $this->toplam_fiyat; }
    public function getDurum()       { return $this->durum; }
    public function getRestoranId()  { return $this->restoran_id; }
}
