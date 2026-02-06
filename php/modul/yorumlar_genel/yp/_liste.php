<?php if ( ! defined('otoban')) exit('Vad.');
if (!syetki("2")) {
	return;
}

echo '<h3>'.yc("Genel yorumlar").'</h3>';

try {
	$stmt_check = $pdo->prepare("SHOW TABLES LIKE '{$do_}yorumlar'");
	$stmt_check->execute();
	$table_exists = $stmt_check->fetchColumn();
	if (!$table_exists) {
		echo yc("Yorumlar tablosu bulunamadı.");
		return;
	}
} catch (PDOException $e) {
	echo yc("Yorumlar tablosu bulunamadı.");
	return;
}

$siralama = new siralama(2000);
$yorum_sayisi = $pdo_db->var("SELECT COUNT(no) FROM {$do_}yorumlar WHERE no > 0 AND (tablo = '' OR tablo IS NULL) AND tablo_no = 0");
$siralama->limit_sira_ = ceil($yorum_sayisi / $siralama->limit2_bu);

try {
	$limit1 = intval($siralama->limit1);
	$limit2 = intval($siralama->limit2_bu);
	$stmt = $pdo->prepare("SELECT no, uye_no, tablo, tablo_no, yorum_no, adi_soyadi, email_adresi, mesaj, tarih, ip, yayin
			FROM {$do_}yorumlar
			WHERE no > 0 AND (tablo = '' OR tablo IS NULL) AND tablo_no = 0 
			ORDER BY no DESC
			LIMIT $limit1, $limit2");
	$stmt->execute();
	$yorumlar = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
	error_log('PDO error in yorumlar_genel _liste: ' . $e->getMessage());
	$yorumlar = [];
}

if (!empty($yorumlar)) {

	echo '
	<table id="ts_" class="yazi1 tablo2"><thead>
	<tr>
	<th class="g_30_">'.$yorum_sayisi.'</th>
	<th>'.yc("Adı Soyadı").'</th>
	<th>'.yc("Email").'</th>
	<th>'.yc("Yorum").'</th>
	<th>'.yc("Tarih").'</th>
	<th class="g_20_">'.yc("Yayın").'</th>
	<th class="g_20_">'.yc("Sil").'</th></tr>
	</thead>
	<tbody>';

	foreach ($yorumlar as $s) {	
		echo '
		<tr>
		<td>'.$s->no.'</td>
		<td class="yazi1">'.$s->adi_soyadi.'</td>
		<td class="yazi1">'.$s->email_adresi.'</td>
		<td class="yazi1">'.$s->mesaj.'</td>
		<td class="yazi1">'.tarih_dt($s->tarih).'</td>
		<td><span class="db01 ok'.$s->yayin.'" data-nta="'.sifrele($s->no.',,yorumlar,,yayin').'">'.$s->yayin.'</span></td>
		<td><span class="sil" data-nt="'.sifrele($s->no.',,yorumlar').'" data-sil="0"> </span></td>
		</tr>';
	}
	echo '</tbody></table>';

	echo $siralama->siralama_yaz();

} else {
	echo '<br><div class="yazi3">'.yc("Yorum bulunmamaktadır.").'</div><br>';
}
