<?php if (!defined('otoban')) exit;

if (!syetki("2,3,4")) {
	error_log("uis_selectyaz.php Hatası: Yetkisiz erişim denemesi.");
	echo '<option value="">Yetkisiz Erişim</option>';
	exit;
}

try {
	$n      = isset($_POST['ngetir']) ? intval($_POST['ngetir']) : 0;
	$tablo  = isset($_POST['tgetir']) ? z($_POST['tgetir']) : '';
	
	/*
	data-nested-ugw="ust_grup_no" -> string : listelenecek tablo hangi üst grup alanı nested olarak listelenecek : ust_grup_no, ust_menu_no ...
	data-tgetir="urun_grup" -> string : listelenecek tablo
	data-ngetir="'.$s->grup_no.'" 
	data-n="'.$s->no.'" 
	data-getirfiltre="yayin,1"
	*/
	$nested_ust_grup_where = isset($_POST['nested_ugw']) ? z($_POST['nested_ugw']) : '';
	$getir_filtre = isset($_POST['getirfiltre']) ? z($_POST['getirfiltre']) : false;
	
	echo '<option value="0">Seç</option>';

	if (empty($tablo)) {
		error_log("uis_selectyaz.php Hatası: Tablo adı eksik.");
		echo '<option value="">Tablo adı eksik.</option>';
		exit;
	}

	if (!empty($nested_ust_grup_where)) {
		$nested_select = new nested_select($pdo, $do_ . $tablo, $nested_ust_grup_where, 'adi', 'adi');
		echo $nested_select->list_yaz($n, 0);
	
	} else {
		$stmt_check_column = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = :table_name AND COLUMN_NAME = 'yayin' AND TABLE_SCHEMA = DATABASE()");
		$stmt_check_column->bindValue(':table_name', $do_ . $tablo, PDO::PARAM_STR);
		$stmt_check_column->execute();
		$has_yayin_column = (int) $stmt_check_column->fetchColumn() > 0;

		$yayin_ek = $has_yayin_column ? " AND yayin = 1 " : "";
		$db_filtre_ek = "";
		$filter_params = [];

		if ($getir_filtre) {
			$getir_filtre_dizi = explode(",", $getir_filtre);
			if (isset($getir_filtre_dizi[0]) && isset($getir_filtre_dizi[1])) {
				$filter_column = z($getir_filtre_dizi[0]);
				$filter_value = $getir_filtre_dizi[1];
				
				$db_filtre_ek = " AND $filter_column = :filter_value";
				$filter_params[':filter_value'] = $filter_value;
			}
		}
		
		$sql = "SELECT no, adi FROM {$do_}$tablo WHERE no > 0 $yayin_ek $db_filtre_ek ORDER BY adi ASC";
		$stmt_select_option = $pdo->prepare($sql);

		foreach ($filter_params as $param_name => $param_value) {
			if (filter_var($param_value, FILTER_VALIDATE_INT) !== false) {
				$stmt_select_option->bindValue($param_name, $param_value, PDO::PARAM_INT);
			} else {
				$stmt_select_option->bindValue($param_name, $param_value, PDO::PARAM_STR);
			}
		}
		
		$stmt_select_option->execute();
		$select_options = $stmt_select_option->fetchAll(PDO::FETCH_OBJ);
	
		if ($select_options){
			foreach ($select_options as $s){
				$selected = ($s->no == $n) ? ' selected="selected"' : '';
				echo '<option value="'.$s->no.'"'.$selected.'>'.htmlspecialchars($s->adi).'</option>';
			}
		}
	}

} catch (PDOException $e) {
	error_log("uis_selectyaz.php PDO Hatası: " . $e->getMessage() . " SQL: " . ($sql ?? 'N/A'));
	echo '<option value="">Veritabanı hatası.</option>';
} catch (Exception $e) {
	error_log("uis_selectyaz.php Genel Hata: " . $e->getMessage());
	echo '<option value="">Bir hata oluştu.</option>';
}
?>