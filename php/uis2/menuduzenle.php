<?php if ( ! defined('otoban')) exit('Vad.');

$nestable_menu = new nestable_menu_yaz($pdo, $do_);
$nestable_menu->menu();

$yaz_s = '';
$yaz_sekme = '';
$yaz_b = '';
$where_char_length = DB_TYPE == 'mysql' ? "CHAR_LENGTH" : "LENGTH";

// Ortak menü öğesi oluşturma fonksiyonu
function menu_ogesi_olustur($pdo, $do_, $tablo, $tablo_adi, $where_char_length) {
    $query = "SELECT no, adi FROM {$do_}{$tablo} WHERE " . $where_char_length . "(adi) < 30 AND yayin=1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    $output = '';
    if ($items) {
        $output .= ($tablo === 'sayfa' ? '' : '<br/><br/>') . yc($tablo_adi) . ' :<br/>';
        $output .= '<select class="menu_' . $tablo . '_select chosen" data-placeholder="' . yc($tablo_adi . " seç") . '" multiple>';
        
        foreach ($items as $item) {
            $menu_query = "SELECT no FROM {$do_}menu WHERE tablo=? AND tablo_no=?";
            $stmt_menu = $pdo->prepare($menu_query);
            $stmt_menu->execute([$tablo, $item->no]);
            $menudeki_no = $stmt_menu->fetchColumn();
            $disabled = $menudeki_no ? ' disabled' : '';
            
            $output .= '<option value="' . $item->no . '" data-sn="' . $menudeki_no . '"' . $disabled . '>' . $item->adi . '</option>';
        }
        
        $output .= '</select> <button class="menu_ekle_buton uis-btn uis-btn-sm uis-btn-success" data-t="' . $tablo . '">' . yc("Ekle") . '</button>';
    }
    
    return $output;
}

// Sayfalar, Sekmeler ve Bloklar için menü öğelerini oluştur
$yaz_s = menu_ogesi_olustur($pdo, $do_, 'sayfa', 'Sayfalar', $where_char_length);
$yaz_sekme = menu_ogesi_olustur($pdo, $do_, 'sekme', 'Sekmeler', $where_char_length);
$yaz_b = menu_ogesi_olustur($pdo, $do_, 'blok_html', 'Bloklar', $where_char_length);

echo '
<h4>'. yc("Menü maddeleri").'</h4>
<div class="uis-container">
	<div class="uis-row">
		<div class="uis-col-6">
			<h3>'. yc("Menü").'</h3>
			<div class="n-dd" id="nestable3">
			' . $nestable_menu->yaz() . '
			</div>
		</div>
		<div class="uis-col-6">
		<h3>'. yc("Ekle").'</h3>
		'.$yaz_s.'
		'.$yaz_sekme.'
		'.$yaz_b.'
		<br/><br/><span class="uis-btn uis-btn-sm uis-btn-primary menu_dis_link_duzenle" data-mno="yeni">'.yc("Link").' ✎</span><br/>
		</div>
	</div>
</div>';
