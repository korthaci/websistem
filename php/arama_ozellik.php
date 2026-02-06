<?php if (! defined('otoban')) exit('vad.');

// Eski sistem (hala çalışıyor, geriye uyumluluk için)
$arama_ = '';
$arama_ .= (set_dolu('nn','g')) ? " AND no=" . intval($_GET['nn'])." " : "";
$arama_ .= (set_dolu('yazi_arama','g') && !set_dolu('etiket','g')) ? 
" AND (adi LIKE '%" . mb_str_replace('ı','I', z($_GET['yazi_arama'])) . "%' OR adi LIKE '%".mb_str_replace('I','ı', z($_GET['yazi_arama']))."%') " : "";

$arama_order = (set_dolu('sira','g')&&($_GET['sira']=='songiris')) ? " songiris DESC " : " no ASC";

/*
Yeni basit_arama_form sınıfı kullanımı:

// Örnek 1: Basit metin araması
$config = [
    'yazi_arama' => ['type' => 'text', 'placeholder' => 'Ara...']
];

// Örnek 2: Kapsamlı arama formu
$config = [
    'kelime' => [
        'type' => 'text',
        'placeholder' => 'Kelime ile ara',
        'class' => 'form-control',
        'db_kolon' => 'adi'
    ],
    'durum' => [
        'type' => 'select',
        'placeholder' => 'Durum Seç',
        'options' => ['1' => 'Aktif', '0' => 'Pasif'],
        'multiple' => true, // Çoklu seçim
        'class' => 'form-select'
    ],
    'kategori' => [
        'type' => 'select',
        'placeholder' => 'Kategori',
        'options' => ['blog' => 'Blog', 'haber' => 'Haber'],
        'db_kolon' => 'tip'
    ],
    'sadece_onemli' => [
        'type' => 'checkbox',
        'placeholder' => 'Sadece Önemli Olanlar',
        'db_kolon' => 'onemli'
    ],
    'eklenme_tarihi' => [
        'type' => 'date',
        'placeholder' => 'Eklenme Tarihi',
        'db_kolon' => 'tarih'
    ],
    'tarih_araligi' => [
        'type' => 'daterange',
        'placeholder' => 'Tarih Aralığı',
        'class' => 'date-input',
        'db_kolon' => 'created_at'
    ]
];

$form = new basit_arama_form($config);
echo $form->form_html();
$where_kosulu = $form->sql_where();
$arama_yapildi = $form->arama_yapildi_mi();

// SQL'de kullanım:
// SELECT * FROM tablo WHERE 1=1 $where_kosulu
*/
?>