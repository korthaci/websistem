<?php if (! defined('otoban')) exit('!vad');

$yorumlar_liste = [];
$sql = "SELECT no, uye_no, tablo, tablo_no, yorum_no, adi_soyadi, email_adresi, mesaj, tarih, ip
        FROM {$do_}yorumlar
        WHERE yayin = 1 AND tablo = '' AND tablo_no = 0
        ORDER BY tarih DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$yorumlar = $stmt->fetchAll(PDO::FETCH_OBJ);

if ($yorumlar && count($yorumlar) > 0) {
	foreach($yorumlar as $y_) {
		$sql_uye = "SELECT no, fotograf, adi FROM {$do_}uyeler WHERE no = :uye_no";
		$stmt_uye = $pdo->prepare($sql_uye);
		$stmt_uye->bindParam(':uye_no', $y_->uye_no, PDO::PARAM_INT);
		$stmt_uye->execute();
		$uye_b = $stmt_uye->fetch(PDO::FETCH_OBJ);
		$uye_foto = $uye_b && is_file($uye_b->fotograf??'') ? $uye_b->fotograf : IMG . '/fg1.jpg';

		$y_->adi = !empty($uye_b->adi) ? $uye_b->adi : $y_->adi_soyadi;
		$y_->tarih = tarih_dt($y_->tarih);
		$y_->uye_foto = $uye_foto;
		$y_->mesaj = nl2br($y_->mesaj);

		$yorumlar_liste[] = $y_;
	
	}
}

$yorum_uye_b__ = null;
if (isset($u_no__) && $u_no__ > 0) {
	$sql_current_user = "SELECT adi, email_adresi, fotograf FROM {$do_}uyeler WHERE no = :uye_no";
	$stmt_current_user = $pdo->prepare($sql_current_user);
	$stmt_current_user->bindParam(':uye_no', $u_no__, PDO::PARAM_INT);
	$stmt_current_user->execute();
	$yorum_uye_b__ = $stmt_current_user->fetch(PDO::FETCH_OBJ);
}

$yeni_yorum_uye_adi = ($yorum_uye_b__) ? $yorum_uye_b__->adi : '';
$yeni_yorum_uye_email_adresi = ($yorum_uye_b__) ? $yorum_uye_b__->email_adresi : '';
$yeni_yorum_uye_foto_var = ($yorum_uye_b__ && is_file($yorum_uye_b__->fotograf??''));
$yeni_yorum_uye_foto = $yeni_yorum_uye_foto_var ? $yorum_uye_b__->fotograf : IMG.'/fg1.jpg';

	$modul_sablon_yaz = new sablon_yaz;
	$modul_sablon_yaz->dosya_icerik(__DIR__ . '/sablon/yorumlar_genel.html');
	$modul_sablon_yaz->vars = [
		'__degisken' => [
			'yorumun' => $u_no__,
			'uye_adi' => $yeni_yorum_uye_adi,
			'uye_email' => $yeni_yorum_uye_email_adresi,
			'uye_foto' => $yeni_yorum_uye_foto,
		],
		'__foreach' => [
			'yorumlar_liste' => $yorumlar_liste
		],
		'__if' => [
			'uye_giris' => (int)$u_no__ > 0,
			'foto_var' => (int)$yeni_yorum_uye_foto_var,
			'yorum_var' => (int)$yorumlar
		]
	];
	echo $modul_sablon_yaz->render();

?>