<?php if ( ! defined('otoban')) exit('Vad.');

if (!syetki([2])) {
	return;
}
try {
	$stmt = $pdo->prepare("SELECT no, url, adi FROM {$do_}moduller WHERE no = :no LIMIT 1");
	$stmt->execute([':no' => $n]);
	$modul_b = $stmt->fetch(PDO::FETCH_OBJ);
} catch (PDOException $e) {
	error_log('PDO error in modulduzenle.php: ' . $e->getMessage());
	$modul_b = false;
}

if ($modul_b) {
	$klasor = MODUL_DIR. '/'. $modul_b->url. '/yp';
	$modul_adi = $modul_b->adi;
	$modul_no = $modul_b->no;

	echo '
	<div class="uis-page-header">
		<h1 class="uis-page-title"><i class="fa-solid fa-sliders"></i> ' . $modul_adi . ' ' . yc("Modül Yönetimi") . '</h1>
		<div class="uis-action-bar">
			<a href="ui/moduller" class="uis-btn uis-btn-outline uis-btn-danger"><i class="fa-solid fa-arrow-left"></i> ' . yc("Modüllere Dön") . '</a>
		</div>
	</div>';

	if (is_dir($klasor)) {
		$sidebar_links = '';
		$_php_dosya_sayisi = 0;
		$dosyalar = scandir($klasor);
		$active_islem = isset($_GET['islem']) ? z($_GET['islem']) : '';
		$include_dosya = false;
		$files_found = [];

		if (!empty($dosyalar)) {
			foreach ($dosyalar as $dosya) {
				if (substr($dosya, 0, 1) == "_" && pathinfo($dosya, PATHINFO_EXTENSION) == "php") {
					$dosya_yolu = $klasor . '/' . $dosya;
					$dosya_basename = basename($dosya, ".php");
					$dosya_isim = ucfirst_utf8(str_replace("_"," ",trim($dosya_basename,"_")));
					$dosya_baslangic = file_get_contents($dosya_yolu, false, null, 0, 300);
					$menude_gizle = is_string($dosya_baslangic) && preg_match('/^\s*<\?php\s*[\r\n]+\s*###\s*modul_duzenle\.php\s*->\s*panel_buton\s*:\s*0\s*###/u', $dosya_baslangic) === 1;
					
					$is_active = ($dosya_basename == $active_islem);
					$active_class = $is_active ? 'active' : '';
					
					if (!$menude_gizle) {
						$sidebar_links .= '<a href="' . href('index', 'ui=modulduzenle&n=' . $modul_no . '&islem=' . $dosya_basename) . '" class="theme-file-item ' . $active_class. '">
							<i class="fa-solid fa-file-code"></i> ' . $dosya_isim . '
						</a>';
						$_php_dosya_sayisi++;
					}
					
					$files_found[] = [
						'path' => $dosya_yolu,
						'name' => $dosya_basename,
						'hidden' => $menude_gizle
					];
				}
			}
		}

		// Otomatik yönlendirme: Eğer sadece 1 dosya varsa ve islem seçilmemişse
		if ($_php_dosya_sayisi == 1 && empty($active_islem)) {
			foreach ($files_found as $f) {
				if (empty($f['hidden'])) {
					$active_islem = $f['name'];
					break;
				}
			}
		}

		if (!empty($active_islem)) {
			foreach ($files_found as $f) {
				if ($f['name'] == $active_islem) {
					$include_dosya = $f['path'];
					break;
				}
			}
		}

		echo '
		<div class="uis-card uis-mb-4">
			<div class="uis-card-header">
				<h3 class="uis-card-title"><i class="fa-solid fa-list-check"></i> ' . yc("Yönetim Aracı Seçimi") . '</h3>
			</div>
			<div class="uis-card-body">
				<div class="uis-flex uis-wrap uis-gap-2">';
				
				if (!empty($files_found)) {
					foreach ($files_found as $f) {
						if (!empty($f['hidden'])) {
							continue;
						}
						$is_active = ($f['name'] == $active_islem);
						$active_class = $is_active ? 'uis-btn-p' : 'uis-btn-outline';
						$dosya_isim = ucfirst_utf8(str_replace("_"," ",trim($f['name'],"_")));
						
						echo '<a href="' . href('index', 'ui=modulduzenle&n=' . $modul_no . '&islem=' . $f['name']) . '" class="uis-btn uis-btn-sm ' . $active_class . '">
							<i class="fa-solid fa-file-code"></i> ' . $dosya_isim . '
						</a>';
					}
				} else {
					echo '<div class="uis-text-muted uis-text-s">'.yc("Yönetim dosyası bulunamadı.").'</div>';
				}

		echo '	</div>
			</div>
		</div>';

		if ($include_dosya) {
			echo '<div class="uis-card">';
			include_once $include_dosya;
			echo '</div>';
		} else {
			echo '
			<div class="uis-card uis-text-center uis-py-5">
				<div class="uis-text-muted">
					<i class="fa-solid fa-hand-pointer fa-3x uis-mb-3"></i>
					<p>'.yc("Lütfen işlem yapmak için yukarıdan bir dosya seçiniz.").'</p>
				</div>
			</div>';
		}

	} else {
		echo '<div class="uis-alert uis-alert-d">'.yc("Modül yönetim klasörü bulunamadı veya boş.").' (yp)</div>';
	}
}

