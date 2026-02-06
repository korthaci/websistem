<?php if (! defined('otoban')) exit('!vad');

if ($indexx == 1) {
    include_once __DIR__ . '/indexx_main.php';
} else {
    include_once KOLON_1;
}

/*
$index_html_yaz = new sablon_yaz;
$index_html_yaz->dosya_icerik(TEMABU . '/index.html');

$index_html_yaz->vars = [
	'__degisken' => [
		'title' => 'title',
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
echo $index_html_yaz->render();
*/

?>