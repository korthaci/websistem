<?php if (! defined('otoban')) exit('!vad');


$modul_sablon_yaz = new sablon_yaz;
$modul_sablon_yaz->dosya_icerik(__DIR__ . '/sablon/email_listesi.html');
echo $modul_sablon_yaz->render();

