<?php if (! defined('otoban')) exit('Veriyolu açık değil.');

$routeParams = Global_::$routeParams;
$sekme_id = isset($routeParams['id']) ? intval($routeParams['id']) : 0;

$sayfalar_dizi = [];

if ($sekme_id > 0) {
    $sql = "SELECT * FROM {$do_}sekme WHERE no = :id AND yayin = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $sekme_id, PDO::PARAM_INT);
    $stmt->execute();
    $sekme_bilgi = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$sekme_bilgi) {
        header("HTTP/1.0 404 Not Found");
        include YONLENDIR_D.'/404.php';
        return;
    }

    $arama_ = " AND yayin = 1 AND adi != '' AND ms_no = :sekme_id ";

    $siralama = new siralama(24);
    
    $sql_count = "SELECT COUNT(no) FROM {$do_}sayfa WHERE no > 0 $arama_";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->bindParam(':sekme_id', $sekme_id, PDO::PARAM_INT);
    $stmt_count->execute();
    $toplam_sayfa_sayisi = $stmt_count->fetchColumn();
    
    $siralama->limit_sira_ = ceil($toplam_sayfa_sayisi / $siralama->limit2_bu);
    // siralama sınıfı kendi URL'ini oluşturuyor, link_ özelliği kullanılmıyor

    $sql_sayfalar = "SELECT no, url, adi, icerik, resim, tarih
                     FROM {$do_}sayfa
                     WHERE no > 0 $arama_ 
                     ORDER BY sira ASC
                     LIMIT {$siralama->limit1}, {$siralama->limit2_bu}";
    
    $stmt_sayfalar = $pdo->prepare($sql_sayfalar);
    $stmt_sayfalar->bindParam(':sekme_id', $sekme_id, PDO::PARAM_INT);
    $stmt_sayfalar->execute();
    $sayfalar = $stmt_sayfalar->fetchAll(PDO::FETCH_OBJ);

    if ($sayfalar && count($sayfalar) > 0) {

        $sekme_adi = cc($sekme_bilgi->adi, $sekme_bilgi->no, 'sekme', 'adi');

        foreach ($sayfalar as $s) {
                
            $resimbu = dosya_fe($s->resim) ? $s->resim : IMG.'/fg1.jpg';
            $link__ = href('index','sayfa='.$s->url . '.' . $s->no);

            $s->href = $link__;
            $s->resim = $resimbu;
            $sayfalar_dizi[] = $s;
        }

        $siralama_yaz = $siralama->siralama_yaz();

		$sekme_sablon_yaz = new sablon_yaz;
        $sekme_sablon_yaz->dosya_icerik(TEMABU . '/_sekme.html');
        $sekme_sablon_yaz->vars = [
            '__degisken' => [
                'sekme_adi' => $sekme_adi, 
                'siralama_yaz' => $siralama_yaz
            ],
            '__foreach' => [
                'sekme' => $sayfalar_dizi
            ]
        ];
        echo $sekme_sablon_yaz->render();
    } else {
        echo '<div style="margin:40px auto;text-align:center;"><h3>Bu sekmeye ait sayfa bulunamadı.</h3><img src="https://cdn.pixabay.com/animation/2023/06/29/06/23/06-23-06-393_512.gif"/></div>';
    }
} else {
    header("HTTP/1.0 404 Not Found");
    include YONLENDIR_D.'/404.php';
}
