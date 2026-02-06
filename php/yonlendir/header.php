<?php if (! defined('otoban')) exit('!vad');

$header_html_yaz = new sablon_yaz;
$header_html_yaz->dosya_icerik(TEMABU . '/header.html');

$header_html_yaz->vars = [
	'__degisken' => [
		'local' => LOCAL,
		'IMG' => IMG,
	],
	'__if' => [
		'indexx' => (int) $indexx,
		'syetki2' => (int) syetki([2]),
		'syetki3' => (int) syetki([3]),
		'uyegiris' => (int) $u_no__ > 0,
	]
];
echo $header_html_yaz->render();
