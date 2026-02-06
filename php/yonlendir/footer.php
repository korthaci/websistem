<?php if (! defined('otoban')) exit('!vad');

$footer_html_yaz = new sablon_yaz;
$footer_html_yaz->dosya_icerik(TEMABU . '/footer.html');

$footer_html_yaz->vars = [
	'__degisken' => [
		'title' => 'Araçlar',
		'dil_kod' => 'tr',
		'local' => LOCAL,
	],
	'__if' => [
		'indexx' => (int) $indexx,
		'syetki2' => (int) syetki([2]),
		'syetki3' => (int) syetki([3]),
		'uyegiris' => (int) $u_no__ > 0,
	]
];
echo $footer_html_yaz->render();

?>