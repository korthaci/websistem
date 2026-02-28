<?php if (! defined('otoban')) exit('vad.');

$arama_sablon_yaz = new sablon_yaz;
$arama_sablon_yaz->dosya_icerik(TEMABU . '/arama_kutu.html');
$arama_sablon_yaz->vars = [
	'__degisken' => [
		'local' => LOCAL,
		'IMG' => IMG,
		],
	];

echo $arama_sablon_yaz->render();
