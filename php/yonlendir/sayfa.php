<?php if (! defined('otoban')) exit('Veriyolu açık değil.');

$routeParams = Global_::$routeParams;

$sayfa_id = isset($routeParams['id']) ? intval($routeParams['id']) : 0;

if ($sayfa_id > 0) {

    $sql = "SELECT * FROM {$do_}sayfa WHERE no = :id AND yayin = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $sayfa_id, PDO::PARAM_INT);
    $stmt->execute();
    $s_b = $stmt->fetch(PDO::FETCH_OBJ);
    
    if ($s_b) {
        $resim_dizini = 'resim/_sayfa/'.$s_b->no.'_'. nd_md5($s_b->no);
        $acilis_r = k_b($s_b->resim);
        $galeri_yaz_bu = rg_opendir($resim_dizini.'/k',$resim_dizini.'/b','',2,'galeri','href',$so_->d('site_adi'));
        $sayfa_baslik = cc($s_b->adi, $s_b->no, 'sayfa', 'adi');

        $konu_icerik_yazi = html_modul(cc(html_a($s_b->icerik),$s_b->no,'sayfa','icerik'));

        $sayfa_sablon_yaz = new sablon_yaz;
        $sayfa_sablon_yaz->dosya_icerik(TEMABU . '/_sayfa.html');
        $sayfa_sablon_yaz->vars = [
        '__degisken' => [
            'sayfa_baslik' => $sayfa_baslik,
            'konu_icerik_yazi' => $konu_icerik_yazi,
            'galeri' => $galeri_yaz_bu
            ]
        ];
        echo $sayfa_sablon_yaz->render();
    }
}