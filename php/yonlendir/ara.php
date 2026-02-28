<?php if (! defined('otoban')) exit('Veriyolu açık değil.');

$sayfalar_dizi = [];

$arama_string = isset($_GET['ara']) ? z($_GET['ara']) : '';

// Başlık ayarı
$ara_adi = yc("Arama Sonuçları");
if (!empty($arama_string)) {
    $ara_adi .= ': ' . htmlspecialchars($arama_string);
}

if (!empty($arama_string)) {
    
    $arama_str_like = "%" . $arama_string . "%";
    $arama_sql = " AND yayin = 1 AND (adi LIKE :arama_string OR icerik LIKE :arama_string) ";
    
    $siralama = new siralama(24);
    
    $sql_count = "SELECT COUNT(no) FROM {$do_}sayfa WHERE no > 0 $arama_sql";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->bindParam(':arama_string', $arama_str_like, PDO::PARAM_STR);
    $stmt_count->execute();
    $toplam_sayfa_sayisi = $stmt_count->fetchColumn();
    
    $siralama->limit_sira_ = ceil($toplam_sayfa_sayisi / $siralama->limit2_bu);

    $sql_sayfalar = "SELECT no, url, adi, icerik, resim, tarih
                     FROM {$do_}sayfa
                     WHERE no > 0 $arama_sql 
                     ORDER BY sira ASC
                     LIMIT {$siralama->limit1}, {$siralama->limit2_bu}";
    
    $stmt_sayfalar = $pdo->prepare($sql_sayfalar);
    $stmt_sayfalar->bindParam(':arama_string', $arama_str_like, PDO::PARAM_STR);
    $stmt_sayfalar->execute();
    $sayfalar = $stmt_sayfalar->fetchAll(PDO::FETCH_OBJ);

    if ($sayfalar && count($sayfalar) > 0) {
        foreach ($sayfalar as $s) {
            $resimbu = dosya_fe($s->resim) ? $s->resim : IMG.'/fg1.jpg';
            $link__ = href('index','sayfa='.$s->url . '.' . $s->no);

            $s->href = $link__;
            $s->resim = $resimbu;
            $s->icerik = mb_strimwidth(strip_tags($s->icerik) , 0, 100, "...");
            $sayfalar_dizi[] = $s;
        }

        $siralama_yaz = $siralama->siralama_yaz();

		$ara_sablon_yaz = new sablon_yaz;
        $ara_sablon_yaz->dosya_icerik(TEMABU . '/_ara.html');
        $ara_sablon_yaz->vars = [
            '__degisken' => [
                'ara_adi' => $ara_adi, 
                'siralama_yaz' => $siralama_yaz
            ],
            '__foreach' => [
                'ara' => $sayfalar_dizi
            ]
        ];
        echo $ara_sablon_yaz->render();
    } else {
        echo '<div style="margin:40px auto;text-align:center;"><h3>"' . htmlspecialchars($arama_string) . '" / ' . yc("Eşleşen sonuç bulunamadı.") . '</h3><img src="https://cdn.pixabay.com/animation/2023/06/29/06/23/06-23-06-393_512.gif"/></div>';
    }
} else {
    echo '<div style="margin:40px auto;text-align:center;"><h3>' . yc("Lütfen bir arama kelimesi giriniz.") . '</h3></div>';
}
