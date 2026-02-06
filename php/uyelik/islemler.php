<?php if (! defined('otoban')) exit('vad.');

if (isset($_GET['uis'])) {

	$uis_url = z($_GET['uis']);

	$case = [
    'uyegiris' =>			['php' => 'uye_giris',			'yetki' => '00',		'_class' => 'container'],
    'bilgileriduzenle' =>	['php' => 'bilgileri_duzenle',	'yetki' => [2,3,4,5],	'_class' => 'container'],
    'sifredegistir' =>		['php' => 'sifre_degistir',		'yetki' => [2,3,4,5],	'_class' => 'container'],
    'sifrereset' =>			['php' => 'sifre_reset',		'yetki' => '00',		'_class' => 'container'],
    'sifreyenile' =>		['php' => 'sifre_yenile',		'yetki' => '00',		'_class' => 'container'],
    'ua' =>					['php' => 'uyelik_aktivasyon',	'yetki' => '00',		'_class' => 'container'],
    'emailiptal' =>			['php' => 'emailalimi_iptal',	'yetki' => '00',		'_class' => 'container'],
	];

	if (array_key_exists($uis_url, $case)) {

		if ( syetki( $case[$uis_url]['yetki'] )) {
			echo '<div class="' . $case[$uis_url]['_class'] . ' mt-4 mb-4">';

			echo $uis_url!=='uyegiris'?'<div><a href="uis/uyegiris">' . yc("Geri") . '</a></div>':'';

			if (is_file(__DIR__ . '/' . $case[$uis_url]['php'] . '.php')) {
				include __DIR__ . '/' . $case[$uis_url]['php'] . '.php';
			} else {
				include __DIR__ . '/uye_giris.php';
			}
			echo '</div>';
		} else {
			echo '!';
		}
	} else {
		echo '!!';
	}
}
?>