<?php if (! defined('otoban')) exit('!vad');

$sablon1 = __DIR__ . '/sablon/'.$so_->d('tema').'/iletisim_formu.html';
$sablon2 = __DIR__ . '/sablon/iletisim_formu.html';

$modul_sablon_yaz = new sablon_yaz;
$modul_sablon_yaz->dosya_icerik(file_exists($sablon1) ? $sablon1 : $sablon2);
echo $modul_sablon_yaz->render();
