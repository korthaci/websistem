<?php if ( ! defined('otoban')) exit('Vad.');

if (!syetki([2])) {
	return;
}

try {
	$sql = "SELECT no, url, adi FROM {$do_}bilesen WHERE no=:no";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':no', $n, PDO::PARAM_INT);
	$stmt->execute();
	$bilesen_b = $stmt->fetch(PDO::FETCH_OBJ);
} catch (PDOException $e) {
	error_log('PDO error in bilesenduzenle.php: ' . $e->getMessage());
	$bilesen_b = false;
}

if ($bilesen_b) {
	$klasor = BILESEN_DIR. '/'. $bilesen_b->url. '/yp';

	if (is_dir($klasor)) {

		$liste_yaz = '';
		$_php_dosya_sayisi = 0;
		$dosyalar = scandir($klasor);
		if (!empty($dosyalar)) {
			$include_dosya = false;
			foreach ($dosyalar as $dosya) {
				$dosya_basename = basename($dosya, ".php");

				if (isset($_GET['islem'])) {
					$islem_bu = z($_GET['islem']);
					if($dosya_basename==$islem_bu) {
						$include_dosya = $klasor . '/' . $dosya;
					}
				}

				if (substr($dosya, 0, 1) == "_" && pathinfo($dosya, PATHINFO_EXTENSION) == "php") {
					$dosya_isim = ilkharfBuyut(str_replace("_"," ",trim($dosya_basename,"_")));
					$liste_yaz .= '<div><a href="ui/bilesenduzenle?n='.$n.'&islem='.$dosya_basename.'">'.$dosya_isim.'</a></div>';
					$_php_dosya_sayisi++;
					$son_dosya_adi = $dosya;
				}
			}
		}

		if ($_php_dosya_sayisi==1) {
			echo '<a class="yazi1 history_back">← ' . yc("Geri") . '</a><br/>';
			if (isset($son_dosya_adi)) {
				include_once $klasor . '/' . $son_dosya_adi;
			}
		} else {
			if ($include_dosya!=false) {
				echo '<a class="yazi1 history_back">← ' . yc("Geri") . '</a><br/>';
				include_once $include_dosya;
			} else {
				echo '<h5>'.yc("Bileşen düzenle").' ['.$bilesen_b->adi.']</h5>';
				echo '<div class="ui_modul_menu">'.$liste_yaz.'</div>';
			}
		}
	} else {
		echo '<a class="yazi1 history_back">← ' . yc("Geri") . '</a><br/>';
		echo '<small class="text-muted">'.yc("Bileşen yönetim klasörü bulunamadı veya boş.").'</small><br/>';
	}
	
}
