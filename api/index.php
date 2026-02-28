<?php
file_exists('../a_.php') ? include_once '../a_.php' : exit(json_encode(['return' => 0, 'mesaj' => '!a_']));

$response_header = 'application/json'; //header('Content-Type: application/json'); veya text/html

if (isset($_GET['_get'])) {
	$response_header = 'text/html';
	foreach ($_GET as $key => $value) {
		if ($key !== '_get' && !isset($_POST[$key])) {
			$_POST[$key] = html_d($value);
		}
	}
}

if (isset($_POST['islem'])) {
	$islem = z($_POST['islem']);
	$include_dosya_dizi = [
		'uis_dbtext'		=> ['adi' => 'Kaydet', 'php' => 'uis/uis_dbtext.php', 'header' => 'json'],
		'uis_db01'			=> ['adi' => 'db01', 'php' => 'uis/uis_db01.php', 'header' => 'json'],
		'uis_sil'			=> ['adi' => 'sil', 'php' => 'uis/uis_sil.php', 'header' => 'json'],
		'uis_dbtextarea'	=> ['adi' => 'textarea', 'php' => 'uis/uis_dbtextarea.php', 'header' => 'json'],
		'uis_selectyaz'		=> ['adi' => 'SelectYaz', 'php' => 'uis/uis_selectyaz.php', 'header' => 'html'],
		'uis_sirala'		=> ['adi' => 'sirala', 'php' => 'uis/uis_sirala.php', 'header' => 'json'],
		'uis_json_dizi'		=> ['adi' => 'Json dizi', 'php' => 'uis/uis_json_dizi.php', 'header' => 'json'],

		'uis_modulkur'			=> ['adi' => 'Modul kur', 'php' => 'uis/uis_modul_kur.php', 'header' => 'json'],
		'uis_moduller_getir'	=> ['adi' => 'Modul getir', 'php' => 'uis/uis_moduller_getir.php', 'header' => 'json'],

		'uis_bilesenkur'		=> ['adi' => 'Bileşen kur', 'php' => 'uis/uis_bilesen_kur.php', 'header' => 'json'],

		'dyukle'			=> ['adi' => 'Dosya yükle', 'php' => 'dosya/dyukle.php', 'header' => 'json'],
		'dosyagetir'		=> ['adi' => 'Dosya getir', 'php' => 'dosya/dosya_getir.php', 'header' => 'json'],
		'dosyasil'			=> ['adi' => 'Dosya sil', 	'php' => 'dosya/dosya_sil.php', 'header' => 'json'],
		'resim_a_l'			=> ['adi' => 'Profil resmi','php' => 'dosya/resim_a_l.php', 'header' => 'json'],

		'google_login'		=> ['adi' => 'Glogin', 'php' => 'oauth/google_login.php', 'header' => 'json'],

		'uis_text_ceviri'	=> ['adi' => 'text ceviri','php' => 'ceviri/uis_text_ceviri.php', 'header' => 'json'],
		'uis_ceviri_input'	=> ['adi' => 'input','php' => 'ceviri/uis_ceviri_input.php', 'header' => 'json'],
		'uis_ceviri_kaydet'	=> ['adi' => 'kaydet','php' => 'ceviri/uis_ceviri_kaydet.php', 'header' => 'json'],
		'uis_diller_getir'	=> ['adi' => 'diller getir','php' => 'ceviri/uis_diller_getir.php', 'header' => 'json'],

		'uis_menu_nestable'	=> ['adi' => 'uis_menu_nestable','php' => 'nestable/uis_menu_nestable.php', 'header' => 'json'],
		'uis_menu_ekle_sil'	=> ['adi' => 'uis_menu_ekle_sil','php' => 'nestable/uis_menu_ekle_sil.php', 'header' => 'json'],
		'uis_menu_baglanti_ekle'	=> ['adi' => 'uis_menu_baglanti_ekle','php' => 'nestable/uis_menu_baglanti_ekle.php', 'header' => 'json'],
	
		'uye_cikis'			=> ['adi' => 'cikis', 'php' => 'uye_cikis.php', 'header' => 'json'],
		'uis_tema'			=> ['adi' => 'Tema İşlemleri', 'php' => 'uis/uis_tema.php', 'header' => 'json'],
		'ai_generator'		=> ['adi' => 'AI Dönüştürücü', 'php' => 'ai_generator.php',  'header' => 'json'],
		
	];

	if (array_key_exists($islem, $include_dosya_dizi)) {
		
		$dosya_bu = $include_dosya_dizi[$islem];
		$php_dosya_yolu = __DIR__ . '/' . $dosya_bu['php'];
		$php_dosya_yolu_dev = DEV_DIR . '/' . $dosya_bu['php'];

		if (is_file($php_dosya_yolu_dev)) {
			$php_dosya_yolu = $php_dosya_yolu_dev;
		}

		if (is_file($php_dosya_yolu)) {

			header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
			header('Cache-Control: post-check=0, pre-check=0', false);
			header('Pragma: no-cache');
			
			if (isset($dosya_bu['header']) && $dosya_bu['header'] === 'html') {
				$response_header = 'text/html';
			}

			header('Content-Type: ' . $response_header);
			ob_start();
			require_once $php_dosya_yolu;
			$html = ob_get_clean();
			$html = middle_dot_yc($html, ($so_->d('yabanci_dil')==1));
			echo $html;

		} else {
			echo json_encode(['return' => 0, 'mesaj' => yc("Geçersiz işlem") . ' : ' . yc("Dosya bulunamadı.")]);
		}
	} else {
		header('Content-Type: ' . $response_header);
		echo json_encode(['return' => 0, 'mesaj' => '!key. ' . yc("Geçersiz işlem.")]);
	}

} elseif (isset($_POST['m_islem']) && isset($_POST['m'])) {
	$modul_url_bu = z($_POST['m']);
	$ajax_dosya = MODUL_DIR.'/'.$modul_url_bu.'/ajax/ajax_islemler.php';
	if (file_exists($ajax_dosya)) {
		ob_start();
		header('Content-Type: text/html');
		include_once $ajax_dosya;
		$html = ob_get_clean();
		$html = middle_dot_yc($html, ($so_->d('yabanci_dil')==1));
		echo $html;
	} else {
		exit('!m_islem');
	}

} elseif (isset($_POST['b_islem']) && isset($_POST['b'])) {
	//process_log("b_islem,POST:". print_pre($_POST) . ', GET : '. print_pre($_GET));
	$bilesen_url_bu = z($_POST['b']);
	$ajax_dosya = BILESEN_DIR.'/'.$bilesen_url_bu.'/ajax/ajax_islemler.php';
	if (file_exists($ajax_dosya)) {
		ob_start();
		header('Content-Type: text/html');
		$ajax_bilesen_islem = z($_POST['b_islem']);
		unset($_POST['_get'], $_POST['b'], $_POST['b_islem'], $_GET['_get'], $_GET['b'], $_GET['b_islem']);

		include_once $ajax_dosya;
		$html = ob_get_clean();
		$html = middle_dot_yc($html, ($so_->d('yabanci_dil') == 1));
		echo $html;
	} else {
		exit('!b_islem');
	}
} else {
	echo json_encode(['return' => 0, 'mesaj' => '!islem. ' . yc("Geçersiz işlem.")]);
}
