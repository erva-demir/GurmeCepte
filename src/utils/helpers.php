<?php
// GurmeCepte - Yardımcı Fonksiyonlar

function paraFormat($tutar) {
    return '₺' . number_format($tutar, 2, ',', '.');
}

function tarihFormat($tarih) {
    return date('d.m.Y H:i', strtotime($tarih));
}

function guvenliYazi($metin) {
    return htmlspecialchars(strip_tags(trim($metin)));
}

function yonlendir($url) {
    header('Location: ' . $url);
    exit;
}

function resimVarMi($url) {
    return !empty($url);
}

function puanYildiz($puan) {
    return str_repeat('★', $puan) . str_repeat('☆', 5 - $puan);
}
