<?php if ( ! defined('otoban')) exit('Vad.');
if (!syetki("2")) {
	return;
}
$modul_d_yaz = '';
$md_dosya_url='';

try {
	$stmt = $pdo->prepare("SELECT url FROM {$do_}moduller WHERE no = :no LIMIT 1");
	$stmt->execute([':no' => $n]);
	$modul_url = $stmt->fetchColumn();
} catch (PDOException $e) {
	error_log('PDO error in modulsablonlar.php: ' . $e->getMessage());
	$modul_url = '';
}

if ($modul_url) {
	$sablon_kok = MODUL_DIR . '/' . $modul_url . '/sablon';
	$sablon_kok_real = realpath($sablon_kok);
	$modul_sablon_dosyalar = dosya_listele($sablon_kok, [
		'include' => ['*.html'],
		'exclude' => ['index.html', 'index1.html', '*.yedek'],
		'recursive' => true,
	]);
	$modul_sablon_dosyalar = is_array($modul_sablon_dosyalar) ? $modul_sablon_dosyalar : [];
	sort($modul_sablon_dosyalar, SORT_STRING | SORT_FLAG_CASE);

	$md_dosya_url = (isset($_GET['mddosya'])) ? z($_GET['mddosya']) : '';
	$md_dosya_url = trim(str_replace('\\', '/', $md_dosya_url), '/');
	$md_dosya = '';
	$md_dosya_yolu = '';
	$md_dosya_gecerli = false;
	$dosya_icerik = '';
	$kayit_mesaj = '';
	$gecersiz_dosya_mesaj = '';

	if ($md_dosya_url !== '') {
		$md_dosya = str_ends_with($md_dosya_url, '.html') ? $md_dosya_url : $md_dosya_url . '.html';

		if (strpos($md_dosya, '..') !== false) {
			$gecersiz_dosya_mesaj = yc("Geçersiz dosya yolu.");
		} elseif ($sablon_kok_real === false) {
			$gecersiz_dosya_mesaj = yc("Şablon klasörü bulunamadı.");
		} else {
			$aday_dosya_yolu = $sablon_kok . '/' . $md_dosya;
			$aday_real = realpath($aday_dosya_yolu);
			$kok_kontrol = rtrim(strtolower(str_replace('\\', '/', $sablon_kok_real)), '/') . '/';
			$dosya_kontrol = $aday_real ? strtolower(str_replace('\\', '/', $aday_real)) : '';

			if ($aday_real && is_file($aday_real) && str_ends_with(strtolower($aday_real), '.html') && str_starts_with($dosya_kontrol, $kok_kontrol)) {
				$md_dosya_yolu = $aday_real;
				$md_dosya_gecerli = true;
			} else {
				$gecersiz_dosya_mesaj = yc("Seçilen dosya bulunamadı veya izin verilen klasör dışında.");
			}
		}
	}

	if (isset($_POST['mddosya_text']) && $md_dosya_gecerli) {
		if (dosya_yaz($md_dosya_yolu, html_d(strtr($_POST['mddosya_text'], ['__textarea__'=>'textarea'])))) {
			$kayit_mesaj = '<div class="uis-alert uis-alert-s">'.yc("Şablon başarıyla kaydedildi.").'</div>';
		}
	}

	if ($gecersiz_dosya_mesaj !== '') {
		$kayit_mesaj = '<div class="uis-alert uis-alert-d">' . $gecersiz_dosya_mesaj . '</div>';
		$md_dosya_url = '';
		$md_dosya = '';
		$md_dosya_gecerli = false;
	}

	if ($md_dosya_gecerli) {
		$dosya_icerik = file_get_contents($md_dosya_yolu);
		$dosya_icerik = strtr($dosya_icerik, ['<textarea' => '<__textarea__','</textarea>' => '</__textarea__>']);
	}

	echo '
	<div class="uis-page-header">
		<h1 class="uis-page-title"><i class="fa-solid fa-code"></i> ' . ucfirst($modul_url) . ' ' . yc("Şablonlarını Düzenle").'</h1>
		<div class="uis-action-bar">
			<a href="' . href('index', 'ui=moduller') . '" class="uis-btn uis-btn-outline uis-btn-danger"><i class="fa-solid fa-arrow-left"></i> '.yc("Modüllere Dön").'</a>
		</div>
	</div>

	' . $kayit_mesaj . ' ';

	echo '
	<div class="uis-card uis-mb-4">
		<div class="uis-card-header">
			<h3 class="uis-card-title"><i class="fa-solid fa-list-check"></i> ' . yc("Şablon Seçimi") . '</h3>
		</div>
		<div class="uis-card-body">
			<div class="uis-flex uis-wrap uis-gap-2">';
			
			if (!empty($modul_sablon_dosyalar)) {
				foreach($modul_sablon_dosyalar as $msd) {
					$msd = str_replace('\\', '/', $msd);
					$msd_yazi = strtr(trim(basename($msd),"_"), ['.html'=>'']);
					$is_active = ($msd === $md_dosya);
					$active_class = $is_active ? 'uis-btn-p' : 'uis-btn-outline';
					$msd_url = preg_replace('/\.html$/', '', $msd);
					$msd_baslik = (strpos($msd, '/') !== false) ? $msd : $msd_yazi;
					echo '<a href="' . href('index', 'ui=modulsablonlar&n='.$n.'&mddosya='.$msd_url) . '" class="uis-btn uis-btn-sm ' . $active_class . '" title="' . hs($msd) . '">
						<i class="fa-regular fa-file-code"></i> ' . hs($msd_baslik) . '
					</a>';
				}
			} else {
				echo '<div class="uis-text-muted uis-text-s">'.yc("Şablon dosyası bulunamadı.").'</div>';
			}

	echo '	</div>
		</div>
	</div>

	<div class="uis-card uis-card-nopad overflow-hidden">
		<form action="' . href('index','ui=modulsablonlar&n='.$n.'&mddosya='.preg_replace('/\.html$/', '', $md_dosya)) . '" method="POST">
			<div class="uis-flex uis-justify-between uis-align-center theme-editor-header">
				<span class="font-weight-bold">' . ($md_dosya_url ? $md_dosya : yc("Şablon Seçiniz")) . '</span>
				<div class="uis-flex uis-gap-2">
					' . (!empty($md_dosya_url) ? '<button type="submit" name="mddosya_kaydet" class="uis-btn uis-btn-sm uis-btn-p"><i class="fa-solid fa-floppy-disk"></i> ' . yc("Kaydet") . '</button>' : '') . '
				</div>
			</div>
			<textarea name="mddosya_text textarea_tag_kontrol" 
			class="theme-editor-textarea format_html" style="min-height: 500px;" spellcheck="false" autocomplete="off" autocapitalize="off" wrap="off" ' . (empty($md_dosya_url) ? 'disabled placeholder="'.yc("Lütfen düzenlemek için bir şablon seçin").'..." ' : '') . '>' . ($dosya_icerik) . '</textarea>
		</form>
	</div>
	
	<div class="uis-mt-3 uis-text-muted uis-text-s uis-p-2">
		<i class="fa-solid fa-circle-info"></i> '.yc("Bu şablonlar FRONTEND tarafında modülün nasıl görüneceğini belirler. Standart HTML ve sistem etiketlerini içerebilir.").'
	</div>';

}
