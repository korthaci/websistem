<?php if (! defined('otoban')) exit('Vad.');

if (isset($_GET['ui'])) {

	$uis2_url = z($_GET['ui']);

	$case = [
		'ga' =>					['php' => 'genelayarlar',		'yetki' => [2,3],		'_class' => 'container'],
		'uyeler' => 			['php' => 'uyeler',				'yetki' => [2,3],		'_class' => ' g_98__ margin_a'],
		'uyeduzenle' => 		['php' => 'uyeduzenle',			'yetki' => [2,3],		'_class' => ' g_98__ margin_a'],
		'sayfalar' =>			['php' => 'sayfalar',			'yetki' => [2,3],		'_class' => 'container'],
		'sduzenle' =>			['php' => 'sduzenle',			'yetki' => [2,3],		'_class' => 'container'],
		'sekmeler' =>			['php' => 'sekmeler',			'yetki' => [2,3],		'_class' => 'container'],
		'sekmeduzenle' =>		['php' => 'sekmeduzenle',		'yetki' => [2,3],		'_class' => 'container'],
		'bloklar' =>			['php' => 'bloklar',			'yetki' => [2,3],		'_class' => 'container'],
		'blokduzenle' =>		['php' => 'blok_duzenle',		'yetki' => [2,3],		'_class' => ' g_98__ margin_a'],
		'menuduzenle' =>		['php' => 'menuduzenle',		'yetki' => [2,3],		'_class' => 'container'],
		'resimler' =>			['php' => 'resimler',			'yetki' => [2,3],		'_class' => 'container'],
		'dosyalar' =>			['php' => 'dosyalar',			'yetki' => [2,3],		'_class' => 'container'],
		'ulkeler' =>			['php' => 'ulkeler',			'yetki' => [2],		'_class' => ' g_98__ margin_a'],
		'sehirler' =>			['php' => 'sehirler',			'yetki' => [2],		'_class' => ' g_98__ margin_a'],
		'diller' =>				['php' => 'diller',				'yetki' => [2],		'_class' => ' g_98__ margin_a'],
		'ceviri' =>				['php' => 'ceviriler',			'yetki' => [2],		'_class' => ' g_98__ margin_a'],
		'moduller' =>			['php' => 'moduller',			'yetki' => [2],		'_class' => ' g_98__ margin_a'],
		'modulduzenle' =>		['php' => 'modulduzenle',		'yetki' => [2],		'_class' => ' g_98__ margin_a'],
		'modulsablonlar' =>		['php' => 'modulsablonlar',		'yetki' => [2],		'_class' => ' g_98__ margin_a'],
		'bilesen' =>			['php' => 'bilesen',			'yetki' => [2],		'_class' => ' g_98__ margin_a'],
		'bilesenduzenle' =>		['php' => 'bilesenduzenle',		'yetki' => [2],		'_class' => ' g_98__ margin_a'],
		'bilesensablonlar' =>	['php' => 'bilesensablonlar',	'yetki' => [2],		'_class' => ' g_98__ margin_a'],
		'bilesenayarlar' =>		['php' => 'bilesen_ayarlar',	'yetki' => [2],		'_class' => ' g_98__ margin_a'],
		'dashboard' =>			['php' => 'kontrolpaneli',		'yetki' => [2,3],		'_class' => 'container'],
		'temalar' =>			['php' => 'temalar',			'yetki' => [2],		'_class' => ' g_98__ margin_a'],
		'temaduzenle' =>		['php' => 'temaduzenle',		'yetki' => [2],		'_class' => ' g_98__ margin_a'],
		'ai_studio' =>			['php' => 'ai_studio',			'yetki' => [2],		'_class' => 'container'],
	];

	if (array_key_exists($uis2_url, $case)) {

		if ( syetki( $case[$uis2_url]['yetki'] )) {
			echo '<div class="' . str_replace('container', 'uis-container', $case[$uis2_url]['_class']) . '  uis-mb-4">';
			//echo '<h6 class="_ls-2">'.tum_harf_buyut(preg_replace("/\d/", "", $case[$uis2_url]['php'])) . '</h6>';
			if (is_file(__DIR__ . '/' . $case[$uis2_url]['php'] . '.php')) {
				include __DIR__ . '/' . $case[$uis2_url]['php'] . '.php';
			} elseif (is_file(DEV_DIR . '/' . $case[$uis2_url]['php'] . '.php')) {
				include DEV_DIR . '/' . $case[$uis2_url]['php'] . '.php';
			}
			echo '</div>';
		} else {
			if ($u_no__ === 0) {
				$current_url = tum_url();
				if (strpos($current_url, '/uis/uyegiris') === false) {
					redirect_url_kontrol(LOCAL . "/uis/uyegiris?redirect_uri=" . urlencode($current_url));
				} else {
					header("Location: " . LOCAL . "/", true, 303);
					exit();
				}
			} else {
				echo '<div class="uis-alert uis-alert-w">! '.yc("Bu sayfada işlem yapma yetkiniz yok.").' <a href="' . LOCAL . '/">'.yc("Ana sayfaya dön").'</a></div>';
				return;
			}
			
		}
	} else {
		echo '!!';
	}
}
