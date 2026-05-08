//restoran sayfası
<?php
require_once __DIR__ . '/db.php';

class Restoran {
    private $db;
    private $id;
    private $isim;
    private $kategori;
    private $puan;

    public function __construct($id = null) {
        $this->db = getDB();
        if ($id) {
            $this->yukle($id);
        }
    }

    // Veriyi yükle
    private function yukle($id) {
        $stmt = $this->db->prepare("SELECT * FROM restoranlar WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if ($r) {
            $this->id       = $r['id'];
            $this->isim     = $r['isim'];
            $this->kategori = $r['kategori'];
            $this->puan     = $r['puan'];
        }
    }

    // Tüm restoranları getir
    public function tumunuGetir($kategori = null) {
        $sql    = "SELECT * FROM restoranlar WHERE aktif = 1";
        $params = [];
        if ($kategori) {
            $sql     .= " AND kategori = ?";
            $params[] = $kategori;
        }
        $sql .= " ORDER BY puan DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // İlçeye göre filtrele
    public function ilceyeGoreGetir($ilce, $sehir) {
        $stmt = $this->db->prepare(
            "SELECT * FROM restoranlar WHERE aktif=1 AND ilce=? AND sehir=? ORDER BY puan DESC"
        );
        $stmt->execute([$ilce, $sehir]);
        return $stmt->fetchAll();
    }

    // Yemekleri getir
    public function yemekleriGetir() {
        $stmt = $this->db->prepare(
            "SELECT y.*, k.isim as kategori_isim FROM yemekler y
             LEFT JOIN kategoriler k ON y.kategori_id = k.id
             WHERE y.restoran_id = ? AND y.aktif = 1"
        );
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    // Yemek ekle
    public function yemekEkle($kategori_id, $isim, $aciklama, $fiyat, $resim = null) {
        $stmt = $this->db->prepare(
            "INSERT INTO yemekler (restoran_id, kategori_id, isim, aciklama, fiyat, resim)
             VALUES (?,?,?,?,?,?)"
        );
        return $stmt->execute([$this->id, $kategori_id, $isim, $aciklama, $fiyat, $resim]);
    }

    // Fiyat güncelle
    public function fiyatGuncelle($yemek_id, $yeni_fiyat) {
        $stmt = $this->db->prepare(
            "UPDATE yemekler SET fiyat = ? WHERE id = ? AND restoran_id = ?"
        );
        return $stmt->execute([$yeni_fiyat, $yemek_id, $this->id]);
    }

    // Puan güncelle (yorum eklenince)
    public function puanGuncelle() {
        $stmt = $this->db->prepare(
            "UPDATE restoranlar SET
                puan = (SELECT AVG(puan) FROM yorumlar WHERE restoran_id = ?),
                yorum_sayisi = (SELECT COUNT(*) FROM yorumlar WHERE restoran_id = ?)
             WHERE id = ?"
        );
        return $stmt->execute([$this->id, $this->id, $this->id]);
    }

    // Yorumları getir
    public function yorumlariGetir() {
        $stmt = $this->db->prepare(
            "SELECT y.*, k.ad, k.soyad FROM yorumlar y
             JOIN kullanicilar k ON y.kullanici_id = k.id
             WHERE y.restoran_id = ? ORDER BY y.tarih DESC"
        );
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    // Yorum ekle
    public function yorumEkle($kullanici_id, $puan, $yorum_metni) {
        $stmt = $this->db->prepare(
            "INSERT INTO yorumlar (kullanici_id, restoran_id, puan, yorum) VALUES (?,?,?,?)"
        );
        $r = $stmt->execute([$kullanici_id, $this->id, $puan, $yorum_metni]);
        if ($r) $this->puanGuncelle();
        return $r;
    }

    // Kullanıcının restoranını getir
    public function kullanicininRestorani($kullanici_id) {
        $stmt = $this->db->prepare("SELECT * FROM restoranlar WHERE kullanici_id = ?");
        $stmt->execute([$kullanici_id]);
        $r = $stmt->fetch();
        if ($r) {
            $this->id       = $r['id'];
            $this->isim     = $r['isim'];
            $this->kategori = $r['kategori'];
            $this->puan     = $r['puan'];
        }
        return $r;
    }

    // Aktif indirimleri getir
    public function aktifIndirimleriGetir() {
        $stmt = $this->db->prepare(
            "SELECT i.*, r.isim as restoran_isim FROM indirimler i
             LEFT JOIN restoranlar r ON i.restoran_id = r.id
             WHERE i.aktif = 1 AND (i.bitis_tarihi IS NULL OR i.bitis_tarihi > NOW())
             ORDER BY i.id DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Getterlar
    public function getId()       { return $this->id; }
    public function getIsim()     { return $this->isim; }
    public function getKategori() { return $this->kategori; }
    public function getPuan()     { return $this->puan; }
}
