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
	$modul_sablon_dosyalar = dosya_listesi(MODUL_DIR . '/' . $modul_url . '/sablon', ['index.html', 'index1.html']);
	$md_dosya_url = (isset($_GET['mddosya'])) ? z($_GET['mddosya']) : '';
	$md_dosya = $md_dosya_url . '.html';
	$dosya_icerik = '';
	$kayit_mesaj = '';

	if (isset($_POST['mddosya_text']) && !empty($md_dosya_url)) {
		if (dosya_yaz(MODUL_DIR . '/' . $modul_url . '/sablon/'. $md_dosya, html_d(strtr($_POST['mddosya_text'], ['__textarea__'=>'textarea'])))) {
			$kayit_mesaj = '<div class="uis-alert uis-alert-s">'.yc("Şablon başarıyla kaydedildi.").'</div>';
		}
	}

	if (!empty($md_dosya_url) && is_file(MODUL_DIR . '/' . $modul_url . '/sablon/'. $md_dosya)) {
		$dosya_icerik = file_get_contents(MODUL_DIR . '/' . $modul_url . '/sablon/'. $md_dosya);
		$dosya_icerik = strtr($dosya_icerik, ['textarea'=>'__textarea__']);
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
				sort($modul_sablon_dosyalar, SORT_STRING | SORT_FLAG_CASE);
				foreach($modul_sablon_dosyalar as $msd) {
					$msd_yazi = strtr(trim($msd,"_"), ['.html'=>'']);
					$is_active = ($msd === $md_dosya);
					$active_class = $is_active ? 'uis-btn-p' : 'uis-btn-outline';
					echo '<a href="' . href('index', 'ui=modulsablonlar&n='.$n.'&mddosya='.strtr($msd,['.html'=>''])) . '" class="uis-btn uis-btn-sm ' . $active_class . '">
						<i class="fa-regular fa-file-code"></i> ' . $msd_yazi . '
					</a>';
				}
			} else {
				echo '<div class="uis-text-muted uis-text-s">'.yc("Şablon dosyası bulunamadı.").'</div>';
			}

	echo '	</div>
		</div>
	</div>

	<div class="uis-card uis-card-nopad overflow-hidden">
		<form action="' . href('index','ui=modulsablonlar&n='.$n.'&mddosya='.$md_dosya_url) . '" method="POST">
			<div class="uis-flex uis-justify-between uis-align-center theme-editor-header">
				<span class="font-weight-bold">' . ($md_dosya_url ? $md_dosya : yc("Şablon Seçiniz")) . '</span>
				<div class="uis-flex uis-gap-2">
					' . (!empty($md_dosya_url) ? '<button type="submit" name="mddosya_kaydet" class="uis-btn uis-btn-sm uis-btn-p"><i class="fa-solid fa-floppy-disk"></i> ' . yc("Kaydet") . '</button>' : '') . '
				</div>
			</div>
			<textarea name="mddosya_text" 
			class="theme-editor-textarea format_html" style="min-height: 500px;" spellcheck="false" autocomplete="off" autocapitalize="off" wrap="off" ' . (empty($md_dosya_url) ? 'disabled placeholder="'.yc("Lütfen düzenlemek için bir şablon seçin").'..." ' : '') . '>' . ($dosya_icerik) . '</textarea>
		</form>
	</div>
	
	<div class="uis-mt-3 uis-text-muted uis-text-s uis-p-2">
		<i class="fa-solid fa-circle-info"></i> '.yc("Bu şablonlar FRONTEND tarafında modülün nasıl görüneceğini belirler. Standart HTML ve sistem etiketlerini içerebilir.").'
	</div>';

}

echo '<script src="' . LOCAL . '/assets/js/uis2_theme.js" defer></script>';

