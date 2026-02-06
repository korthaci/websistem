<?php if ( ! defined('otoban')) exit('Vad.');

$nestable_menu = new nestable_menu_yaz($pdo, $do_);
$nestable_menu->menu();

$yaz_s = '';
$yaz_sekme = '';
$yaz_b = '';

//no 	tablo 	tablo_no 	ust_menu_no 	sira 	
$sayfalar_query = "SELECT no, adi FROM {$do_}sayfa WHERE CHAR_LENGTH(adi) < 30 AND yayin=1";
$stmt = $pdo->prepare($sayfalar_query);
$stmt->execute();
$menu_sayfalar = $stmt->fetchAll(PDO::FETCH_OBJ);

if ($menu_sayfalar) {
	$yaz_s .= yc("Sayfalar") . ' :<br/>';
	$yaz_s .= '<select class="menu_sayfa_select chosen" data-placeholder="'.yc("Sayfa seç").'" multiple>';
	foreach ($menu_sayfalar as $m1){
		$menu_sayfa_query = "SELECT no FROM {$do_}menu WHERE tablo='sayfa' AND tablo_no=?";
		$stmt_sayfa = $pdo->prepare($menu_sayfa_query);
		$stmt_sayfa->execute([$m1->no]);
		$menudeki_sayfa_no = $stmt_sayfa->fetchColumn();
		$disabled = $menudeki_sayfa_no ? ' disabled' : '';
		
		$yaz_s .= '<option value="'.$m1->no.'" data-sn="'.$menudeki_sayfa_no.'"'.$disabled.'>'.$m1->adi.'</option>';
	}
	$yaz_s .= '</select>
	<button class="menu_ekle_buton btn btn-sm btn-success" data-t="sayfa">'.yc("Ekle").'</button>';
}

$sekmeler_query = "SELECT no, adi FROM {$do_}sekme WHERE CHAR_LENGTH(adi) < 30 AND yayin=1";
$stmt_sekme = $pdo->prepare($sekmeler_query);
$stmt_sekme->execute();
$menu_sekmeler = $stmt_sekme->fetchAll(PDO::FETCH_OBJ);

if ($menu_sekmeler) {
	$yaz_sekme .= '<br/><br/>'.yc("Sekmeler").' :<br/><select class="menu_sekme_select chosen" data-placeholder="'.yc("Sekme seç").'" multiple>';
	foreach ($menu_sekmeler as $m1){
		$menu_sekme_query = "SELECT no FROM {$do_}menu WHERE tablo='sekme' AND tablo_no=?";
		$stmt_sekme_no = $pdo->prepare($menu_sekme_query);
		$stmt_sekme_no->execute([$m1->no]);
		$menudeki_sekme_no = $stmt_sekme_no->fetchColumn();
		$disabled = $menudeki_sekme_no ? ' disabled' : '';
	$yaz_sekme .= '<option value="'.$m1->no.'" data-sn="'.$menudeki_sekme_no.'"'.$disabled.'>'.$m1->adi.'</option>';
	}
	$yaz_sekme .= '</select> <button class="menu_ekle_buton btn btn-sm btn-success" data-t="sekme">'.yc("Ekle").'</button>';
}

$bloklar_query = "SELECT no, adi FROM {$do_}blok_html WHERE CHAR_LENGTH(adi) < 30 AND yayin=1";
$stmt_blok = $pdo->prepare($bloklar_query);
$stmt_blok->execute();
$menu_bloklar = $stmt_blok->fetchAll(PDO::FETCH_OBJ);

if ($menu_bloklar) {
$yaz_b .= '<br/><br/>'.yc('Bloklar').' :<br/><select class="menu_blok_html_select chosen" data-placeholder="'.yc("Blok seç").'" multiple>';
	foreach ($menu_bloklar as $m1){
		$menu_blok_query = "SELECT no FROM {$do_}menu WHERE tablo='blok_html' AND tablo_no=?";
		$stmt_blok_no = $pdo->prepare($menu_blok_query);
		$stmt_blok_no->execute([$m1->no]);
		$menudeki_blok_no = $stmt_blok_no->fetchColumn();
		$disabled = $menudeki_blok_no ? ' disabled' : '';
	$yaz_b .= '<option value="'.$m1->no.'" data-sn="'.$menudeki_blok_no.'"'.$disabled.'>'.$m1->adi.'</option>';
	}
$yaz_b .= '</select> <button class="menu_ekle_buton btn btn-sm btn-success" data-t="blok_html">'.yc("Ekle").'</button>';
}

echo '
<h4>'. yc("Menü maddeleri").'</h4>
<div class="container">
	<div class="row">
		<div class="col-6">
			<h3>'. yc("Menü").'</h3>
			<div class="n-dd" id="nestable3">
			' . $nestable_menu->yaz() . '
			</div>
		</div>
		<div class="col-6">
		<h3>'. yc("Ekle").'</h3>
		'.$yaz_s.'
		'.$yaz_sekme.'
		'.$yaz_b.'
		<br/><br/><span class="btn btn-sm btn-primary menu_dis_link_duzenle" data-mno="yeni">'.yc("Link").' ✎</span><br/>
		</div>
	</div>
</div>';
