<?php if ( ! defined('otoban')) exit('Vad.');

if (!syetki([2])) {
	return;
}

	echo '
	<div class="uis-page-header">
		<h1 class="uis-page-title"><i class="fa-solid fa-puzzle-piece"></i>'.yc("Modül Yönetimi").'</h1>
		<div class="uis-action-bar">
			<a href="' . href('index', 'ui=moduller') . '" class="uis-btn uis-btn-outline uis-btn-primary uis-btn-light"><i class="fa-solid fa-sync"></i> '.yc("Tazele").'</a>
		</div>
	</div>';

$db_url_liste = [];

try {
	$sql = "SELECT no, url, adi, hash, yayin FROM {$do_}moduller ORDER BY yayin DESC, adi ASC";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$db_moduller = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
	error_log('PDO error in moduller.php: ' . $e->getMessage());
	$db_moduller = [];
}

if (!empty($db_moduller)) {

	echo '
	<div class="uis-card uis-card-nopad">
		<div class="uis-table-responsive">
			<table id="ts_" class="tablesorter uis-table">
				<thead>
					<tr>
						<th class="uis-pl-4">'.yc("Modül Adı").'</th>
						<th class="g_160_">'.yc("Kod (Hash)").'</th>
						<th>'.yc("Kısayol").'</th>
						<th class="uis-text-center g_60_">'.yc("Ayarlar").'</th>
						<th class="uis-text-center g_60_">'.yc("Şablonlar").'</th>		
						<th class="uis-text-center g_30_">'.yc("Aktif").'</th>
						<th class="uis-text-center g_30_"><i class="fa-solid fa-trash-can"></i></th>
					</tr>
				</thead>
				<tbody>';

	foreach ($db_moduller as $s) {
		$is_active = $s->yayin == 1;
		$status_class = $is_active ? 'uis-badge-active' : 'uis-badge-inactive';
		$status_text = $is_active ? yc("Aktif") : yc("Pasif");

		echo '
		<tr>
			<td class="uis-pl-4">
				<input class="dbtext uis-input uis-input-flat w-100" type="text" value="'.$s->adi.'" data-nta="'.sifrele($s->no.',,moduller,,adi').'" placeholder="'.yc("Modül Adı").'" />
			</td>
			<td>
				<input class="dbtext uis-input uis-input-flat w-100 uis-text-muted" style="font-family: monospace; font-size: 0.85rem;" type="text" value="'.$s->hash.'" data-nta="'.sifrele($s->no.',,moduller,,hash').'" placeholder="'.yc("Hash").'" />
			</td>
			<td>
				<div class="uis-flex uis-align-center uis-gap-2">
					<code class="uis-code-inline">[al:modul:'.$s->url.']</code>
					<button class="uis-btn uis-btn-icon-sm uis-kopyala" data-kopyala="[al:modul:'.$s->url.']" title="'.yc("Kopyala").'"><i class="fa-regular fa-copy"></i></button>
				</div>
			</td>
			<td class="uis-text-center">
				<a href="'.href('','ui=modulduzenle&n='.$s->no).'" class="uis-btn uis-btn-icon uis-btn-outline" title="'.yc("Yönetim Dosyaları").'"><i class="fa-solid fa-sliders"></i></a>
			</td>
			<td class="uis-text-center">
				<a href="'.href('','ui=modulsablonlar&n='.$s->no).'" class="uis-btn uis-btn-icon uis-btn-outline" title="'.yc("Görünüm Şablonları").'"><i class="fa-solid fa-code"></i></a>
			</td>
			<td><span class="db01 ok'.$s->yayin.'" data-nta="'.sifrele($s->no.',,moduller,,yayin').'"></span></td>

			<td class="uis-text-center">
				<span class="sil uis-text-d pointer" data-nt="'.sifrele($s->no.',,moduller').'" data-sil="0" data-reload=""><i class="fa-solid fa-circle-xmark"></i></span>
			</td>
		</tr>';
		
		$db_url_liste[] = $s->url;
	}

	echo '</tbody></table>
		</div>
	</div>';

	if (defined('MODUL_CSS_DOSYA') && defined('MODUL_JS_DOSYA')) {
		echo '
		<div class="uis-mt-4 uis-flex uis-align-center uis-gap-3 uis-p-3 uis-bg-light uis-rounded">
			<div class="uis-text-muted uis-text-s"><i class="fa-solid fa-bolt"></i> '.yc("Performans için CSS ve JS dosyalarını birleştirebilirsiniz.").'</div>
			<form method="POST" action="ui/moduller" class="submitconfirm">
			' . ( file_exists(ROOT.'/'.MODUL_CSS_DOSYA) && file_exists(ROOT.'/'.MODUL_JS_DOSYA) 
			? '<button type="submit" name="modul_css_js_dosyalari_sil" class="uis-btn uis-btn-sm uis-btn-d"><i class="fa-solid fa-broom"></i> '.yc("Birleşik Dosyaları Sil").'</button>' 
			: '<button type="submit" name="modul_css_js_dosyalari_birlestir" class="uis-btn uis-btn-sm uis-btn-p"><i class="fa-solid fa-compress"></i> '.yc("Dosyaları Birleştir").'</button>' ) . '
			</form>
		</div>';
	}

} else {
	echo '<div class="uis-alert uis-alert-i uis-mt-4">'.yc("Henüz kurulmuş bir modül bulunmuyor.").'</div>';
}

$moduller_url = dizinleri_getir(MODUL_DIR);
$filter_moduller_url = array_filter($moduller_url, function ($value) use ($db_url_liste) {
	return !in_array($value, $db_url_liste);
});

if (!empty($filter_moduller_url)){
	echo '<div class="uis-mt-5">';
	echo '<h3 class="uis-section-title"><i class="fa-solid fa-download"></i> '.yc("Kurulabilir Modüller").'</h3>';
	echo '<div class="uis-row uis-mt-3">';
	foreach ($filter_moduller_url as $m) {
		echo '
		<div class="uis-col uis-col-md-4 uis-col-lg-3">
			<div class="uis-card uis-p-3 uis-flex uis-justify-between uis-align-center">
				<div>
					<div class="font-weight-bold">' . ucfirst($m) . '</div>
					<small class="uis-text-muted">' . $m . '</small>
				</div>
				<button class="uis-btn uis-btn-sm uis-btn-p modulkur" data-m="'.$m.'">'.yc("Kur").'</button>
			</div>
		</div>';
	}
	echo '</div></div>';
}
