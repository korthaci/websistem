<?php if (! defined('otoban')) exit('!vad');

$header_html_yaz = new sablon_yaz;
$header_html_yaz->dosya_icerik(TEMABU . '/uye_kutu.html');

$header_html_yaz->vars = [
	'__degisken' => [
		'uye_adi' => Global_::$u__b__->yaz('adi'),
		'uye_adi_str' => ilk_harfleri_yaz(Global_::$u__b__->yaz('adi')),
		'uye_foto' => hs(Global_::$u__b__->yaz('fotograf')),
	],
	'__if' => [
		'indexx' => (int) $indexx,
		'uyegiris' => (int) $u_no__ > 0,
		'uye_foto_var' => (int) !empty(Global_::$u__b__->yaz('fotograf')),
	]
];
echo $header_html_yaz->render();
