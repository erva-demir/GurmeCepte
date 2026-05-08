# GurmeCepte 🍽️

**Yemeksepeti benzeri tam özellikli yemek sipariş platformu.**

## Proje Amacı

GurmeCepte, kullanıcıların restoranlardan online yemek siparişi verebileceği, restoran sahiplerinin menülerini yönetebileceği ve müşterilerin puan kazanarak kupon elde edebileceği bir web platformudur.

---

## Özellikler

### Kullanıcılar
- Kayıt ve giriş sistemi
- Restoranları kategoriye göre filtreleme ve arama
- Yemek seçerken malzeme çıkarabilme
- Sepete ekleme ve sipariş tamamlama
- Her siparişte puan kazanma (10₺ = 1 puan)
- 100 puanı 10₺ kupona çevirme
- Restoranlara yorum ve puan verme
- Sipariş geçmişi takibi

### Restoran Sahipleri
- Restoran kayıt ve giriş
- Yemek ekleme ve fiyat güncelleme
- Malzeme yönetimi (müşterinin çıkarabilmesi için)
- Sipariş takibi ve durum güncelleme
- Satış istatistikleri

### Genel
- Kayan indirim paneli (carousel)
- Kategori bazlı filtreleme
- Duyarlı (responsive) tasarım

---

## Kurulum

### Gereksinimler
- XAMPP (PHP 7.4+ ve MySQL)
- Web tarayıcı

### Adımlar

1. `GurmeCepte` klasörünü `C:/xampp/htdocs/` içine koyun.

2. XAMPP'ı başlatın (Apache + MySQL).

3. PhpMyAdmin'e gidin: `http://localhost/phpmyadmin`

4. Yeni veritabanı oluşturun: **gurmecepte**

5. `data/gurmecepte.sql` dosyasını import edin.

6. Tarayıcıda açın: `http://localhost/GurmeCepte`

### Varsayılan Hesaplar

| Rol | E-posta | Şifre |
|-----|---------|-------|
| Kullanıcı | user@gurmecepte.com | test123 |
| Restoran | restoran1@gurmecepte.com | test123 |

> ⚠️ Gerçek şifreler `data/gurmecepte.sql` dosyasında hash olarak tutulmaktadır. Yukarıdaki şifreler yalnızca demo içindir — veritabanına kayıt yaparken gerçek şifre hash'i oluşturulmalıdır.

---

## Klasör Yapısı

```
GurmeCepte/
├── docs/
│   ├── GereksinimAnalizi.pdf
│   └── UML_Diyagramlari.pdf
├── src/
│   ├── core/
│   │   └── db.php
│   ├── modules/         (iş mantığı modülleri)
│   ├── services/
│   │   ├── auth.php
│   │   ├── restoran.php
│   │   └── siparis.php
│   ├── data/
│   ├── ui/
│   │   ├── style.css
│   │   ├── main.js
│   │   ├── navbar.php
│   │   └── footer.php
│   └── utils/
├── data/
│   └── gurmecepte.sql
├── tests/
├── modules/
│   ├── giris.php
│   ├── kayit.php
│   ├── restoranlar.php
│   ├── restoran.php
│   ├── restoran-kayit.php
│   ├── restoran-panel.php
│   ├── odeme.php
│   ├── siparislerim.php
│   ├── profilim.php
│   └── cikis.php
├── index.php
├── README.md
└── .gitignore
```

---

## Teknolojiler

- **Backend:** PHP 7.4+
- **Veritabanı:** MySQL (XAMPP)
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Fontlar:** Google Fonts (Playfair Display, DM Sans)
