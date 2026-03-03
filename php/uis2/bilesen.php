<?php if ( ! defined('otoban')) exit('Vad.');

if (!syetki([2])) {
	return;
}

echo '<h5>'.yc("Bileşenler").'</h5>';

$db_url_liste = [];

try {
	$sql = "SELECT no, url, adi, yayin FROM {$do_}bilesen ORDER BY yayin DESC, adi ASC";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$db_bilesen = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
	error_log('PDO error in bilesen.php: ' . $e->getMessage());
	$db_bilesen = [];
}

if (!empty($db_bilesen)) {

	echo '
	<table id="ts_" class="tablesorter uis-table"><thead>
	<tr>
	<th>'.count($db_bilesen).' '.yc("Bileşen").'</th>
	<th>[]</th>
	<th class="g_20_"><i class="fa fa-cog"></i></th>
	<th class="g_20_"><i class="fa fa-bars"></i></th>
	<th class="g_30_"><i class="fa fa-file"></i></th>		
	<th>'.yc("Aktif").'</th>
	<th>x</th>
	</tr>
	</thead><tbody>';

	foreach ($db_bilesen as $s) {
		$b_al_modul_yaz = '';
		$b_al_modul_dosya_dizi = scandir(BILESEN_DIR . '/' . $s->url . '/php/b_al_modul');
		if (!empty($b_al_modul_dosya_dizi)) {
			foreach ($b_al_modul_dosya_dizi as $b_al_dosya) {
				$dosya_yolu_dosya = BILESEN_DIR . '/' . $s->url . '/php/b_al_modul/'.$b_al_dosya;
				if (is_file($dosya_yolu_dosya) && pathinfo($dosya_yolu_dosya, PATHINFO_EXTENSION) === 'php') {
					$b_al_modul_yaz .= '<span data-kopyala="">[al:bilesen:'.$s->url.':'. pathinfo($b_al_dosya, PATHINFO_FILENAME) .']'.'</span><br/>';
				}
			}
		}

		$bilesen_yp_klasor = is_dir(BILESEN_DIR. '/'. $s->url. '/yp');

		echo '
		<tr class="yazi1">
		<td title="'.$s->no.'"> <input class="dbtext i2 g_99__ uis-input" type="text" value="'.$s->adi.'" data-nta="'.sifrele($s->no.',,bilesen,,adi').'" /></td>
		<td>'.$b_al_modul_yaz.'</td>
		<td><a href="'.href('','ui=bilesenayarlar&n='.$s->no).'"><i class="fa fa-cog"></i></a></td>
		<td><a href="'.href('','ui=bilesenduzenle&n='.$s->no).'"><i class="fa fa-bars"></i></a></td>
		<td><a href="'.href('','ui=bilesensablonlar&n='.$s->no).'"><i class="fa fa-file"></i></a></td>
		<td><span class="db01 ok'.$s->yayin.'" data-nta="'.sifrele($s->no.',,bilesen,,yayin').'">'.$s->yayin.'</span></td>
		<td><span class="sil" data-nt="'.sifrele($s->no.',,bilesen').'" data-sil="0" data-reload=""> </span></td>
		</tr>';
		
		$db_url_liste[] = $s->url;
	}

	echo '</tbody></table>';

	if (defined('BILESEN_CSS_DOSYA') && defined('BILESEN_JS_DOSYA')) {
		echo '
		<br/><br/><br/><div class="uis-btn uis-btn-sm uis-btn-outline" data-ac=".mbdosyalar" data-thishide="1">'.yc("css, js dosyalarını birleştir veya sil").'</div>
		<form method="POST" action="ui/bilesen" class="mbdosyalar dyok submitconfirm">
		' . ( file_exists(ROOT.'/'.BILESEN_CSS_DOSYA) && file_exists(ROOT.'/'.BILESEN_JS_DOSYA) 
		? '<input class="uis-btn uis-btn-sm uis-btn-d" type="submit" name="bilesen_css_js_dosyalari_sil" value="'.yc("Birleştirilmiş dosyaları sil").'"/>' 
		: '<input class="uis-btn uis-btn-sm uis-btn-w" type="submit" name="bilesen_css_js_dosyalari_birlestir" value="'.yc("Birleştirilmiş css, js dosyaları kullan").'" />' ) . '
		</form><br/>';
	}

} else {
	echo '<br><div class="yazi3">'.yc("Etkin bilesen bulunmamaktadır.").'</div><br>';
}

$bilesen_url = dizinleri_getir(BILESEN_DIR);
$filter_bilesen_url = array_filter($bilesen_url, function ($value) use ($db_url_liste) {
	return !in_array($value, $db_url_liste);
});

if (!empty($filter_bilesen_url)){
	foreach ($filter_bilesen_url as $m) {
		echo '
		<div class="modul_mdbi">
			<i class="fa fa-database"></i> '.$m.' <a class="bilesenkur" data-m="'.$m.'">'.yc("Kur").'</a>
		</div>';
	}
}
