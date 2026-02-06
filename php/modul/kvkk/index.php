<?php if (! defined('otoban')) exit('!vad');

if (!isset($_COOKIE['cerez_onay']) || intval($_COOKIE['cerez_onay'])!==1) {

	$modul_sablon_yaz = new sablon_yaz;
	$modul_sablon_yaz->dosya_icerik(__DIR__ . '/sablon/kvkk.html');
	echo $modul_sablon_yaz->render();
}
?>