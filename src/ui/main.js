// GurmeCepte - Ana JavaScript Dosyası

// ===== SEPET YÖNETİMİ =====
let sepet = JSON.parse(localStorage.getItem('gurmecepte_sepet') || '[]');
let aktifRestoranId = localStorage.getItem('gurmecepte_restoran') || null;

function sepetGuncelle() {
    localStorage.setItem('gurmecepte_sepet', JSON.stringify(sepet));
    const toplam = sepet.reduce((t, i) => t + i.adet, 0);
    document.querySelectorAll('.sepet-sayi').forEach(el => {
        el.textContent = toplam;
        el.style.display = toplam > 0 ? 'flex' : 'none';
    });
    sepetRender();
}

function sepetRender() {
    const itemsEl = document.getElementById('sepet-items');
    const toplamEl = document.getElementById('sepet-toplam-fiyat');
    if (!itemsEl) return;

    if (sepet.length === 0) {
        itemsEl.innerHTML = `
            <div class="bos-durum" style="padding:40px 20px">
                <div class="emoji">🛒</div>
                <h3>Sepetiniz boş</h3>
                <p>Lezzetli yemekler ekleyin!</p>
            </div>`;
        if (toplamEl) toplamEl.textContent = '₺0.00';
        return;
    }

    itemsEl.innerHTML = sepet.map((item, i) => `
        <div class="sepet-item">
            <div class="sepet-item-emoji">${item.emoji || '🍽️'}</div>
            <div class="sepet-item-bilgi">
                <div class="sepet-item-isim">${item.isim}</div>
                ${item.cikarilan && item.cikarilan.length > 0 ? `<div class="sepet-item-not">-${item.cikarilan.join(', ')}</div>` : ''}
                <div style="color:var(--turuncu);font-weight:700;font-size:0.9rem">₺${(item.fiyat * item.adet).toFixed(2)}</div>
            </div>
            <div class="adet-kontrol">
                <button class="adet-btn" onclick="sepetAdetDegistir(${i}, -1)">−</button>
                <span class="adet-sayi">${item.adet}</span>
                <button class="adet-btn" onclick="sepetAdetDegistir(${i}, 1)">+</button>
            </div>
        </div>
    `).join('');

    const toplam = sepet.reduce((t, i) => t + (i.fiyat * i.adet), 0);
    if (toplamEl) toplamEl.textContent = `₺${toplam.toFixed(2)}`;

    // Sipariş sayfasına toplam gönder
    const siparisToplam = document.getElementById('siparis-toplam');
    if (siparisToplam) siparisToplam.value = toplam.toFixed(2);
}

function sepetAdetDegistir(index, miktar) {
    sepet[index].adet += miktar;
    if (sepet[index].adet <= 0) {
        sepet.splice(index, 1);
    }
    sepetGuncelle();
}

function sepeteEkle(yemekId, isim, fiyat, emoji, cikarilan = []) {
    if (aktifRestoranId && aktifRestoranId !== String(document.getElementById('restoran-id')?.value)) {
        if (!confirm('Farklı bir restorana ait ürün var. Sepeti temizleyip devam etmek istiyor musunuz?')) return;
        sepet = [];
    }
    aktifRestoranId = String(document.getElementById('restoran-id')?.value || '');
    localStorage.setItem('gurmecepte_restoran', aktifRestoranId);

    const mevcut = sepet.find(i => i.yemek_id === yemekId && JSON.stringify(i.cikarilan) === JSON.stringify(cikarilan));
    if (mevcut) {
        mevcut.adet++;
    } else {
        sepet.push({ yemek_id: yemekId, isim, fiyat: parseFloat(fiyat), emoji, adet: 1, cikarilan });
    }
    sepetGuncelle();
    sepetAc();
    basariMesaj(`${isim} sepete eklendi! 🎉`);
}

function sepetAc() {
    document.getElementById('sepet-panel')?.classList.add('acik');
    document.getElementById('sepet-overlay')?.classList.add('acik');
}

function sepetKapat() {
    document.getElementById('sepet-panel')?.classList.remove('acik');
    document.getElementById('sepet-overlay')?.classList.remove('acik');
}

function sepetTemizle() {
    sepet = [];
    aktifRestoranId = null;
    localStorage.removeItem('gurmecepte_sepet');
    localStorage.removeItem('gurmecepte_restoran');
    sepetGuncelle();
}

// ===== MALZEME YÖNETİMİ =====
function malzemeCikar(el) {
    el.classList.toggle('cikarildi');
}

function seciliMalzemeleriAl(yemekId) {
    const cikarildi = [];
    document.querySelectorAll(`[data-yemek="${yemekId}"] .malzeme-etiket.cikarildi`).forEach(el => {
        cikarildi.push(el.dataset.malzeme);
    });
    return cikarildi;
}

// ===== YILDIZ DEĞERLENDİRME =====
function yildizKur() {
    document.querySelectorAll('.yildiz-secici').forEach(secici => {
        const yildizlar = secici.querySelectorAll('.yildiz');
        const input = document.getElementById('puan-input');
        yildizlar.forEach((y, i) => {
            y.addEventListener('mouseover', () => {
                yildizlar.forEach((y2, j) => y2.classList.toggle('aktif', j <= i));
            });
            y.addEventListener('click', () => {
                if (input) input.value = i + 1;
                yildizlar.forEach((y2, j) => y2.classList.toggle('aktif', j <= i));
            });
        });
        secici.addEventListener('mouseleave', () => {
            const secili = input ? parseInt(input.value) - 1 : -1;
            yildizlar.forEach((y2, j) => y2.classList.toggle('aktif', j <= secili));
        });
    });
}

// ===== MESAJLAR =====
function basariMesaj(mesaj) {
    const el = document.createElement('div');
    el.style.cssText = `
        position:fixed;bottom:30px;left:50%;transform:translateX(-50%);
        background:var(--yesil);color:white;padding:12px 24px;border-radius:30px;
        font-size:0.9rem;font-weight:600;z-index:9999;
        box-shadow:0 4px 20px rgba(0,0,0,0.2);animation:fadeInUp 0.3s ease;
    `;
    el.textContent = mesaj;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 2500);
}

// ===== FİLTRELEME =====
function kategoriFiltrele(kategori) {
    document.querySelectorAll('.kategori-kart').forEach(k => {
        k.classList.toggle('aktif', k.dataset.kategori === kategori || kategori === 'tumu');
    });

    const restoranlar = document.querySelectorAll('[data-kategori-restoran]');
    restoranlar.forEach(r => {
        if (kategori === 'tumu' || r.dataset.kategoriRestoran === kategori) {
            r.style.display = '';
            r.style.animation = 'fadeInUp 0.3s ease';
        } else {
            r.style.display = 'none';
        }
    });
}

// ===== ARAMA =====
function restoranAra(sorgu) {
    const restoranlar = document.querySelectorAll('[data-restoran-isim]');
    restoranlar.forEach(r => {
        const esles = r.dataset.restoranIsim.toLowerCase().includes(sorgu.toLowerCase());
        r.style.display = esles ? '' : 'none';
    });
}

// ===== KUPON PUAN =====
function kuponHesapla(puan, toplamFiyat) {
    if (puan < 100) return 0;
    return Math.min(Math.floor(puan / 100) * 10, toplamFiyat * 0.5);
}

// ===== SAYFA YÜKLENDİĞİNDE =====
document.addEventListener('DOMContentLoaded', () => {
    sepetGuncelle();
    yildizKur();

    // Sepet buton dinleyicileri
    document.querySelectorAll('.sepet-ac-btn').forEach(btn => {
        btn.addEventListener('click', sepetAc);
    });

    document.getElementById('sepet-kapat')?.addEventListener('click', sepetKapat);
    document.getElementById('sepet-overlay')?.addEventListener('click', sepetKapat);

    // Arama kutusu
    const aramaInput = document.getElementById('arama-input');
    if (aramaInput) {
        aramaInput.addEventListener('input', e => restoranAra(e.target.value));
    }

    // Kategori filtreleme
    document.querySelectorAll('.kategori-kart').forEach(k => {
        k.addEventListener('click', (e) => {
            e.preventDefault();
            kategoriFiltrele(k.dataset.kategori);
        });
    });

    // Ödeme formu - sepet verilerini gönder
    const odemeForm = document.getElementById('odeme-form');
    if (odemeForm) {
        const sepetInput = document.createElement('input');
        sepetInput.type = 'hidden';
        sepetInput.name = 'sepet_json';
        sepetInput.value = JSON.stringify(sepet);
        odemeForm.appendChild(sepetInput);

        const restoranInput = document.createElement('input');
        restoranInput.type = 'hidden';
        restoranInput.name = 'restoran_id';
        restoranInput.value = aktifRestoranId || '';
        odemeForm.appendChild(restoranInput);

        odemeForm.addEventListener('submit', () => {
            sepetTemizle();
        });
    }

    // Slider fade-in animasyonu
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateX(-50%) translateY(20px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }
    `;
    document.head.appendChild(style);
});
