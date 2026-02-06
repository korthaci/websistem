<?php if (! defined('otoban')) exit('!vad');

if (!isset($_GET['ui'])) {
	$modul_sablon_yaz = new sablon_yaz;
	$modul_sablon_yaz->dosya_icerik(__DIR__ . '/sablon/tawkto.html');
	echo $modul_sablon_yaz->render();
}

?>