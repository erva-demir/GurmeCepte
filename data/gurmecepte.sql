CREATE DATABASE IF NOT EXISTS gurmecepte CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
USE gurmecepte;

CREATE TABLE kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL,
    soyad VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    sifre VARCHAR(255) NOT NULL,
    telefon VARCHAR(20),
    sehir VARCHAR(100),
    ilce VARCHAR(100),
    adres_detay TEXT,
    kupon_puani INT DEFAULT 0,
    rol ENUM('kullanici','restoran','admin') DEFAULT 'kullanici',
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE restoranlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT NOT NULL,
    isim VARCHAR(150) NOT NULL,
    aciklama TEXT,
    sehir VARCHAR(100),
    ilce VARCHAR(100),
    adres TEXT NOT NULL,
    telefon VARCHAR(20),
    logo VARCHAR(255),
    kapak_resim VARCHAR(255),
    kategori VARCHAR(100),
    puan DECIMAL(3,2) DEFAULT 0.00,
    yorum_sayisi INT DEFAULT 0,
    min_siparis DECIMAL(10,2) DEFAULT 0,
    teslimat_ucreti DECIMAL(10,2) DEFAULT 0,
    teslimat_suresi VARCHAR(50),
    aktif TINYINT(1) DEFAULT 1,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id)
);

CREATE TABLE kategoriler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    isim VARCHAR(100) NOT NULL,
    icon VARCHAR(100),
    renk VARCHAR(20)
);

CREATE TABLE yemekler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restoran_id INT NOT NULL,
    kategori_id INT,
    isim VARCHAR(150) NOT NULL,
    aciklama TEXT,
    fiyat DECIMAL(10,2) NOT NULL,
    resim VARCHAR(500),
    aktif TINYINT(1) DEFAULT 1,
    FOREIGN KEY (restoran_id) REFERENCES restoranlar(id),
    FOREIGN KEY (kategori_id) REFERENCES kategoriler(id)
);

CREATE TABLE malzemeler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    yemek_id INT NOT NULL,
    isim VARCHAR(100) NOT NULL,
    cikarilabilir TINYINT(1) DEFAULT 1,
    FOREIGN KEY (yemek_id) REFERENCES yemekler(id)
);

CREATE TABLE indirimler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restoran_id INT,
    baslik VARCHAR(200) NOT NULL,
    aciklama TEXT,
    indirim_yuzdesi INT,
    bitis_tarihi DATETIME,
    resim VARCHAR(255),
    aktif TINYINT(1) DEFAULT 1,
    FOREIGN KEY (restoran_id) REFERENCES restoranlar(id)
);

CREATE TABLE siparisler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT NOT NULL,
    restoran_id INT NOT NULL,
    toplam_fiyat DECIMAL(10,2) NOT NULL,
    teslimat_sehir VARCHAR(100),
    teslimat_ilce VARCHAR(100),
    teslimat_adres TEXT NOT NULL,
    odeme_yontemi VARCHAR(50),
    kart_son4 VARCHAR(4),
    durum ENUM('beklemede','hazirlaniyor','yolda','teslim_edildi','iptal') DEFAULT 'beklemede',
    kupon_kullanildi INT DEFAULT 0,
    notlar TEXT,
    olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id),
    FOREIGN KEY (restoran_id) REFERENCES restoranlar(id)
);

CREATE TABLE siparis_detaylari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siparis_id INT NOT NULL,
    yemek_id INT NOT NULL,
    adet INT DEFAULT 1,
    birim_fiyat DECIMAL(10,2) NOT NULL,
    cikarilan_malzemeler TEXT,
    FOREIGN KEY (siparis_id) REFERENCES siparisler(id),
    FOREIGN KEY (yemek_id) REFERENCES yemekler(id)
);

CREATE TABLE yorumlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT NOT NULL,
    restoran_id INT NOT NULL,
    siparis_id INT,
    puan INT CHECK (puan BETWEEN 1 AND 5),
    yorum TEXT,
    tarih TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id),
    FOREIGN KEY (restoran_id) REFERENCES restoranlar(id)
);

CREATE TABLE kuponlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT NOT NULL,
    kod VARCHAR(50) UNIQUE NOT NULL,
    indirim_miktari DECIMAL(10,2),
    kullanildi TINYINT(1) DEFAULT 0,
    son_kullanim DATETIME,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id)
);

-- KULLANICILAR (şifre hepsi: password)
INSERT INTO kullanicilar (ad, soyad, email, sifre, telefon, sehir, ilce, adres_detay, rol, kupon_puani) VALUES
('Admin', 'GurmeCepte', 'admin@gurmecepte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '05001234567', 'İstanbul', 'Şişli', '', 'admin', 0),
('Ahmet', 'Yılmaz', 'r1@gurmecepte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '05001234568', 'İstanbul', 'Kadıköy', '', 'restoran', 0),
('Fatma', 'Kaya', 'r2@gurmecepte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '05001234569', 'İstanbul', 'Beşiktaş', '', 'restoran', 0),
('Mehmet', 'Demir', 'r3@gurmecepte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '05001234570', 'İstanbul', 'Şişli', '', 'restoran', 0),
('Ali', 'Çelik', 'r4@gurmecepte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '05001234571', 'İstanbul', 'Kadıköy', '', 'restoran', 0),
('Zeynep', 'Arslan', 'r5@gurmecepte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '05001234572', 'İstanbul', 'Üsküdar', '', 'restoran', 0),
('Hasan', 'Öztürk', 'r6@gurmecepte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '05001234573', 'İstanbul', 'Beşiktaş', '', 'restoran', 0),
('Ayşe', 'Şahin', 'r7@gurmecepte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '05001234574', 'İstanbul', 'Şişli', '', 'restoran', 0),
('Mustafa', 'Koç', 'r8@gurmecepte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '05001234575', 'İstanbul', 'Kadıköy', '', 'restoran', 0),
('Elif', 'Yıldız', 'r9@gurmecepte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '05001234576', 'İstanbul', 'Üsküdar', '', 'restoran', 0),
('Emre', 'Polat', 'r10@gurmecepte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '05001234577', 'İstanbul', 'Beşiktaş', '', 'restoran', 0),
('Selin', 'Aydın', 'r11@gurmecepte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '05001234578', 'İstanbul', 'Şişli', '', 'restoran', 0),
('Burak', 'Kara', 'r12@gurmecepte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '05001234579', 'İstanbul', 'Kadıköy', '', 'restoran', 0),
('Test', 'Kullanıcı', 'user@gurmecepte.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '05001234580', 'İstanbul', 'Kadıköy', 'Moda Caddesi No:12 Daire:5', 'kullanici', 350);

INSERT INTO kategoriler (isim, icon, renk) VALUES
('Pizza', '🍕', '#FF6B35'),
('Burger', '🍔', '#F7C59F'),
('Lahmacun', '🫓', '#C1440E'),
('Kebap', '🥙', '#8B4513'),
('Döner', '🌯', '#D4A017'),
('Sushi', '🍱', '#2E8B57'),
('Tatlı', '🍰', '#FF69B4'),
('İçecek', '🥤', '#4169E1'),
('Pide', '🫓', '#CD853F'),
('Salata', '🥗', '#228B22'),
('Pilav', '🍚', '#DAA520'),
('Çorba', '🍜', '#8B0000');

-- RESTORANLAR (13 tane)
INSERT INTO restoranlar (kullanici_id, isim, aciklama, sehir, ilce, adres, telefon, kategori, puan, yorum_sayisi, min_siparis, teslimat_ucreti, teslimat_suresi) VALUES
(2,  'Lahmacun Ustası',      'Gaziantep usulü 40 yıllık tecrübeyle hazırlanan ince hamur lahmacunlar. Taze malzeme garantisi.',            'İstanbul','Kadıköy',  'Moda Caddesi No:45',         '02165551234','Lahmacun',4.8,234,150,25,'25-35 dk'),
(3,  'Kebapçı Hacı Baba',    'Adana usulü közde pişirilmiş kebaplar. Günlük taze et garantisi ile hizmetinizdeyiz.',                     'İstanbul','Beşiktaş', 'Barbaros Bulvarı No:12',     '02125552345','Kebap',   4.6,189,200,30,'30-45 dk'),
(4,  'Pizza Palace',         'İtalyan usulü odun fırını pizzalar. 30 farklı çeşit ile damağınıza uygun bir şeyler bulacaksınız.',        'İstanbul','Şişli',    'Halaskargazi Cad. No:78',    '02125553456','Pizza',   4.4,312,180,25,'20-30 dk'),
(5,  'Dönerci Baran',        'Tavuk ve et döner çeşitleri. Günlük taze çevrilen döneri ile İstanbul\'un favorisi.',                      'İstanbul','Kadıköy',  'Bahariye Cad. No:33',        '02165554321','Döner',   4.5,201,120,20,'15-25 dk'),
(6,  'Tatlıcı Şükrü Usta',  'Türk tatlılarının ustası. Baklava, kadayıf, künefe ve daha fazlası. Her gün taze yapılır.',                'İstanbul','Üsküdar',  'Hakimiyet Cad. No:8',        '02165555678','Tatlı',   4.9,412,100,15,'20-30 dk'),
(7,  'Pilavcı Hüseyin',      'Osmanlı mutfağından ilham alan pilav çeşitleri. Tavuklu, etli, nohutlu ve daha fazlası.',                  'İstanbul','Beşiktaş', 'Sinanpaşa Mah. No:22',       '02125556789','Pilav',   4.7,178,100,20,'20-30 dk'),
(8,  'Burger Bros',          'El yapımı 180gr dana köfte burgerleri. Ev yapımı soslar ve taze malzemeler ile.',                          'İstanbul','Şişli',    'Meşrutiyet Cad. No:55',      '02125557890','Burger',  4.3,267,150,25,'25-35 dk'),
(9,  'Sushi House',          'Japon şefler tarafından hazırlanan otantik sushi ve japon mutfağı lezzetleri.',                            'İstanbul','Kadıköy',  'Caferağa Mah. No:14',        '02165558901','Sushi',   4.6,143,250,35,'30-45 dk'),
(10, 'Çorba Evi',            'Her derde deva çorbalar. 15 çeşit çorba ile kışın sizi ısıtıyoruz. Ev yapımı ekmekle servis.',            'İstanbul','Üsküdar',  'Ahmediye Cad. No:67',        '02165559012','Çorba',   4.8,223,80, 15,'20-25 dk'),
(11, 'Salata Bahçesi',       'Taze ve sağlıklı salata çeşitleri. Detoks, protein ve akdeniz salatalarımız ile formda kalın.',           'İstanbul','Şişli',    'Cumhuriyet Cad. No:120',     '02125550123','Salata',  4.5,156,120,20,'15-25 dk'),
(12, 'Pide Fırını Selim',    'Taş fırında pişirilen geleneksel Türk pideleri. Kaşarlı, kıymalı, kuşbaşılı çeşitler.',                  'İstanbul','Kadıköy',  'Söğütlüçeşme Cad. No:9',     '02165551357','Pide',    4.7,198,150,20,'25-35 dk'),
(13, 'Künefe & Kadayıf',     'Hatay usulü künefe ve tel kadayıf uzmanı. Fıstıklı, sade ve özel çeşitler her gün taze.',               'İstanbul','Beşiktaş', 'Ortabahçe Cad. No:44',       '02125552468','Tatlı',   4.9,334,100,20,'20-30 dk');

-- YEMEKLER

-- 1) Lahmacun Ustası
INSERT INTO yemekler (restoran_id,kategori_id,isim,aciklama,fiyat,resim) VALUES
(1,3,'Klasik Lahmacun','İnce hamur, taze kıyma, bol maydanoz ve domates ile servis edilir',95,'/GurmeCepte/assets/images/lahmacun.svg'),
(1,3,'Acılı Lahmacun','Özel acı sos ve kırmızı biber ile hazırlanmış, közlenmiş biber garnisi',105,'/GurmeCepte/assets/images/lahmacun.svg'),
(1,3,'Sade Lahmacun','Az baharatlı, çocuklar için ideal hafif lahmacun',85,'/GurmeCepte/assets/images/lahmacun.svg'),
(1,9,'Karışık Pide','Kaşar peyniri, kıyma ve yumurta ile dolu dolu pide',230,'/GurmeCepte/assets/images/pide.svg'),
(1,9,'Kuşbaşılı Pide','Dana kuşbaşı ve sebzelerle hazırlanmış özel pide',265,'/GurmeCepte/assets/images/pide.svg'),
(1,8,'Ayran','Ev yapımı soğuk ayran, 400ml',35,'/GurmeCepte/assets/images/ayran.svg');

-- 2) Kebapçı Hacı Baba
INSERT INTO yemekler (restoran_id,kategori_id,isim,aciklama,fiyat,resim) VALUES
(2,4,'Adana Kebap','Közde pişirilmiş acılı dana kıyma kebap, lavaş ve söğüş ile',340,'/GurmeCepte/assets/images/kebap.svg'),
(2,4,'Urfa Kebap','Sade ve bol baharatlı dana kıyma, pilav ve salata ile',315,'/GurmeCepte/assets/images/kebap.svg'),
(2,4,'Tavuk Şiş','Marine edilmiş ızgara tavuk göğsü, pilav ve salata ile',275,'/GurmeCepte/assets/images/tavuk_sis.svg'),
(2,4,'Karışık Izgara','Adana, urfa ve tavuk şiş birlikte, 2 kişilik',620,'/GurmeCepte/assets/images/karisik_izgara.svg');

-- 3) Pizza Palace
INSERT INTO yemekler (restoran_id,kategori_id,isim,aciklama,fiyat,resim) VALUES
(3,1,'Margarita Pizza','San Marzano domates sos, taze mozzarella ve fesleğen, 28cm',285,'/GurmeCepte/assets/images/pide.svg'),
(3,1,'Pepperoni Pizza','Bol pepperoni ve eritilmiş mozzarella, 28cm',325,'/GurmeCepte/assets/images/pizza_pepperoni.svg'),
(3,1,'BBQ Tavuk Pizza','BBQ sos, ızgara tavuk, kırmızı soğan ve mısır, 28cm',345,'/GurmeCepte/assets/images/pizza_bbq.svg'),
(3,1,'4 Peynirli Pizza','Mozzarella, cheddar, parmezan ve ricotta, 28cm',365,'/GurmeCepte/assets/images/pizza_4peynir.svg');

-- 4) Dönerci Baran
INSERT INTO yemekler (restoran_id,kategori_id,isim,aciklama,fiyat,resim) VALUES
(4,5,'Tavuk Dürüm','Bol tavuk döner, domates, turşu ve sos ile dürüm',185,'/GurmeCepte/assets/images/doner_durum.svg'),
(4,5,'Et Dürüm','Dana et döner, ezme, nar ekşisi ve baharatlar ile',225,'/GurmeCepte/assets/images/doner_durum.svg'),
(4,5,'Tavuk Porsiyon','Porsiyon tavuk döner, pilav ve salata ile',245,'/GurmeCepte/assets/images/doner_durum.svg'),
(4,5,'Karışık Dürüm','Et ve tavuk karışık döner dürüm',235,'/GurmeCepte/assets/images/doner_durum.svg');

-- 5) Tatlıcı Şükrü Usta
INSERT INTO yemekler (restoran_id,kategori_id,isim,aciklama,fiyat,resim) VALUES
(5,7,'Fıstıklı Baklava','Antep fıstığı dolu geleneksel baklava, 6 dilim',185,'/GurmeCepte/assets/images/baklava.svg'),
(5,7,'Sade Baklava','Cevizli ev yapımı baklava, 6 dilim',165,'/GurmeCepte/assets/images/baklava.svg'),
(5,7,'Hatay Künefesi','Peynirli Hatay usulü künefe, tel kadayıf ile, 1 porsiyon',175,'/GurmeCepte/assets/images/kunefe.svg'),
(5,7,'Tel Kadayıf','Cevizli tel kadayıf, şurup ile ıslatılmış, 1 porsiyon',155,'/GurmeCepte/assets/images/kunefe.svg'),
(5,7,'Sütlaç','Fırında pişirilmiş geleneksel Türk sütlacı',95,'/GurmeCepte/assets/images/sutlac.svg');

-- 6) Pilavcı Hüseyin
INSERT INTO yemekler (restoran_id,kategori_id,isim,aciklama,fiyat,resim) VALUES
(6,11,'Tavuklu Pilav','Bütün tavuk parçaları ile pişirilmiş nohutlu pilav',185,'/GurmeCepte/assets/images/pilav.svg'),
(6,11,'Etli Pilav','Dana kuşbaşı ve sebzeler ile hazırlanmış pilav',215,'/GurmeCepte/assets/images/pilav.svg'),
(6,11,'Nohutlu Pilav','Sade nohutlu pilav, cacık ile servis',145,'/GurmeCepte/assets/images/pilav.svg'),
(6,11,'İç Pilav','Kuş üzümü, fıstık ve baharatlarla hazırlanan Osmanlı pilavı',195,'/GurmeCepte/assets/images/pilav.svg'),
(6,11,'Pilav Üstü Tavuk','Izgara tavuk göğsü üzerinde tereyağlı pilav',225,'/GurmeCepte/assets/images/pilav.svg');

-- 7) Burger Bros
INSERT INTO yemekler (restoran_id,kategori_id,isim,aciklama,fiyat,resim) VALUES
(7,2,'Klasik Burger','180gr dana köfte, cheddar, marul, domates ve özel sos',245,'/GurmeCepte/assets/images/burger.svg'),
(7,2,'BBQ Burger','Dana köfte, karamelize soğan, BBQ sos ve bacon',285,'/GurmeCepte/assets/images/burger.svg'),
(7,2,'Crispy Chicken Burger','Çıtır tavuk, coleslaw, jalapeno ve ranch sos',265,'/GurmeCepte/assets/images/burger_crispy.svg'),
(7,2,'Double Smash Burger','2 kat dana köfte, double cheddar, turşu ve özel sos',325,'/GurmeCepte/assets/images/burger.svg');

-- 8) Sushi House
INSERT INTO yemekler (restoran_id,kategori_id,isim,aciklama,fiyat,resim) VALUES
(8,6,'Salmon Set','8 parça somon nigiri ve 6 parça somon maki',385,'/GurmeCepte/assets/images/sushi.svg'),
(8,6,'California Roll','8 parça yengeç, avokado ve salatalık rulosu',265,'/GurmeCepte/assets/images/sushi_roll.svg'),
(8,6,'Dragon Roll','8 parça karides tempura ve avokado rulosu',345,'/GurmeCepte/assets/images/sushi_roll.svg'),
(8,6,'Karma Set','32 parça karışık sushi ve maki seti, 2-3 kişilik',685,'/GurmeCepte/assets/images/sushi.svg');

-- 9) Çorba Evi
INSERT INTO yemekler (restoran_id,kategori_id,isim,aciklama,fiyat,resim) VALUES
(9,12,'Mercimek Çorbası','Geleneksel kırmızı mercimek çorbası, limon ve ekmekle',85,'/GurmeCepte/assets/images/corba.svg'),
(9,12,'Ezogelin Çorbası','Pirinçli ve domatesli ezogelin çorbası',90,'/GurmeCepte/assets/images/corba.svg'),
(9,12,'İşkembe Çorbası','Geleneksel işkembe çorbası, sarımsak ve sirke ile',105,'/GurmeCepte/assets/images/corba.svg'),
(9,12,'Domates Çorbası','Taze domates ve fesleğen ile hazırlanmış çorba',95,'/GurmeCepte/assets/images/corba.svg'),
(9,12,'Çorba Paketi','3 farklı çorba seçimi, 3 kişilik paket',265,'/GurmeCepte/assets/images/corba.svg');

-- 10) Salata Bahçesi
INSERT INTO yemekler (restoran_id,kategori_id,isim,aciklama,fiyat,resim) VALUES
(10,10,'Sezar Salata','Romaine marul, crouton, parmezan ve sezar sos, tavuklu',195,'/GurmeCepte/assets/images/sezar_salata.svg'),
(10,10,'Akdeniz Salata','Roka, nar, ceviz, keçi peyniri ve nar ekşisi sosu',185,'/GurmeCepte/assets/images/akdeniz_salata.svg'),
(10,10,'Protein Kasesi','Izgara tavuk, kinoa, avokado ve humus ile protein kasesi',225,'/GurmeCepte/assets/images/salata.svg'),
(10,10,'Detoks Kasesi','Yeşil sebzeler, zencefil, limon sosu ve tohum karışımı',175,'/GurmeCepte/assets/images/salata.svg');

-- 11) Pide Fırını Selim
INSERT INTO yemekler (restoran_id,kategori_id,isim,aciklama,fiyat,resim) VALUES
(11,9,'Kaşarlı Pide','Bol kaşar peyniri ile hazırlanmış klasik pide',195,'/GurmeCepte/assets/images/pide.svg'),
(11,9,'Kıymalı Pide','Baharatlı dana kıyma ve sebzeli pide',215,'/GurmeCepte/assets/images/pide.svg'),
(11,9,'Sucuklu Yumurtalı Pide','Sucuk ve yumurta ile hazırlanmış pide',225,'/GurmeCepte/assets/images/pide.svg'),
(11,9,'Karışık Pide','Kıyma, kaşar ve kuşbaşı karışımı büyük boy pide',265,'/GurmeCepte/assets/images/pide.svg');

-- 12) Künefe & Kadayıf
INSERT INTO yemekler (restoran_id,kategori_id,isim,aciklama,fiyat,resim) VALUES
(12,7,'Hatay Künefesi','Geleneksel Hatay usulü tel kadayıf ile yapılan künefe',185,'/GurmeCepte/assets/images/kunefe.svg'),
(12,7,'Fıstıklı Künefe','Antep fıstığı serpilmiş özel künefe',205,'/GurmeCepte/assets/images/kunefe.svg'),
(12,7,'Tel Kadayıf','Cevizli veya fıstıklı tel kadayıf, 1 porsiyon',155,'/GurmeCepte/assets/images/baklava.svg'),
(12,7,'Sütlü Kadayıf','Sütün içinde pişirilmiş özel kadayıf tatlısı',165,'/GurmeCepte/assets/images/baklava.svg');

-- Malzemeler
INSERT INTO malzemeler (yemek_id,isim,cikarilabilir) VALUES
(1,'Maydanoz',1),(1,'Domates',1),(1,'Soğan',1),(1,'Limon',1),(1,'Acı Biber',1),
(2,'Acı Biber',1),(2,'Soğan',1),(2,'Acı Sos',1),
(3,'Soğan',1),(3,'Maydanoz',1),
(7,'Soğan',1),(7,'Domates',1),(7,'Yeşil Biber',1),(7,'Acı',1),
(8,'Soğan',1),(8,'Domates',1),
(9,'Domates',1),(9,'Soğan',1),
(11,'Fesleğen',1),(11,'Mozzarella',1),
(12,'Pepperoni',1),(12,'Mozzarella',1),
(13,'Kırmızı Soğan',1),(13,'Mısır',1),
(14,'Mısır',1),(14,'Biber',1),
(17,'Domates',1),(17,'Turşu',1),(17,'Soğan',1),
(18,'Soğan',1),(18,'Biber',1),
(27,'Cheddar',1),(27,'Marul',1),(27,'Domates',1),(27,'Turşu',1),
(28,'Karamelize Soğan',1),(28,'Jalapeño',1),
(29,'Jalapeño',1),(29,'Coleslaw',1),
(37,'Crouton',1),(37,'Parmezan',1);

INSERT INTO indirimler (restoran_id,baslik,aciklama,indirim_yuzdesi,bitis_tarihi,aktif) VALUES
(1,'2 Al 1 Öde!','Lahmacun siparişlerinde geçerli kampanya',50,DATE_ADD(NOW(),INTERVAL 7 DAY),1),
(2,'%30 İndirim','Kebap siparişlerinde özel hafta sonu indirimi',30,DATE_ADD(NOW(),INTERVAL 3 DAY),1),
(3,'Pizza Günü','Büyük boy pizzalarda geçerli indirim',25,DATE_ADD(NOW(),INTERVAL 5 DAY),1),
(5,'Tatlı Günü','Tüm tatlılarda %20 indirim',20,DATE_ADD(NOW(),INTERVAL 10 DAY),1),
(6,'Pilav Şöleni','Pilav menülerinde %15 indirim',15,DATE_ADD(NOW(),INTERVAL 4 DAY),1),
(NULL,'İlk Siparişe Özel','Yeni üyelere ilk siparişte %20 indirim',20,DATE_ADD(NOW(),INTERVAL 30 DAY),1);

INSERT INTO yorumlar (kullanici_id,restoran_id,puan,yorum) VALUES
(14,1,5,'Harika lahmacunlar, çok lezzetli ve hızlı teslimat!'),
(14,2,4,'Kebaplar gerçekten güzeldi, bir dahaki sefere de sipariş vereceğim.'),
(14,3,5,'Pizzalar çok lezzetli, hamuru mükemmel!'),
(14,5,5,'En iyi baklava burası, kesinlikle tavsiye ederim!'),
(14,6,4,'Tavuklu pilav muhteşemdi, tekrar sipariş vereceğim.');
