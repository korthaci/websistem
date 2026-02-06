<?php if (! defined('otoban')) exit;
$_SESSION['yorum_gonderildi'] = $_SESSION['yorum_gonderildi'] ?? 0;
if ($_SESSION['yorum_gonderildi'] > 80) {exit(yc("Yorum gönderilemedi")."!limit");}

if (isset($u_no__) && n0_($u_no__)) {
	$tablo_			= isset($_POST['t']) ? z($_POST['t']) : 0;
	$tablo_no_		= isset($_POST['tn']) ? substr(substr(z($_POST['tn']), 0, -32), 32) : 0;
	$adisoyadi		= set_dolu('adisoyadi','p') ? z($_POST['adisoyadi']) : '';
	$emailadresi	= set_dolu('emailadresi','p') ? z($_POST['emailadresi']) : '';
	$yorum			= set_dolu('yorum','p') ? nl2br(html_d($_POST['yorum'])) : '';
	$ip_			= ip_();
	$db_yorum		= webAdresiSil($yorum);
	$tarih			= date("Y-m-d H:i:s");
	$yorum_yayin	= 1;
	
	if (strlen_($yorum) < 6) {
		exit('<h5 class="y0">'.yc("Daha uzun yorum yazmalısınız").'</h5>');
	}

	$sql = "SELECT no, fotograf, adi FROM {$do_}uyeler WHERE no = :uye_no";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':uye_no', $u_no__, PDO::PARAM_INT);
	$stmt->execute();
	$uye_b = $stmt->fetch(PDO::FETCH_OBJ);
	$uye_foto = is_file($uye_b->fotograf) ? $uye_b->fotograf : IMG . '/fg1.jpg';
	$mesaj = nl2br($yorum);
	
	$sql_insert = "INSERT INTO {$do_}yorumlar
		(uye_no, tablo, tablo_no, adi_soyadi, email_adresi, mesaj, ip, tarih, yayin)
		VALUES (:uye_no, :tablo, :tablo_no, :adi_soyadi, :email, :mesaj, :ip, :tarih, :yayin)";
	$stmt_insert = $pdo->prepare($sql_insert);
	$stmt_insert->bindParam(':uye_no', $u_no__, PDO::PARAM_INT);
	$stmt_insert->bindParam(':tablo', $tablo_, PDO::PARAM_STR);
	$stmt_insert->bindParam(':tablo_no', $tablo_no_, PDO::PARAM_INT);
	$stmt_insert->bindParam(':adi_soyadi', $adisoyadi, PDO::PARAM_STR);
	$stmt_insert->bindParam(':email', $emailadresi, PDO::PARAM_STR);
	$stmt_insert->bindParam(':mesaj', $db_yorum, PDO::PARAM_STR);
	$stmt_insert->bindParam(':ip', $ip_, PDO::PARAM_STR);
	$stmt_insert->bindParam(':tarih', $tarih, PDO::PARAM_STR);
	$stmt_insert->bindParam(':yayin', $yorum_yayin, PDO::PARAM_INT);
	$kaydet = $stmt_insert->execute();

	if ($kaydet === true) {
		$_SESSION['yorum_gonderildi']++;
	}

	$modul_sablon_yaz = new sablon_yaz;
	$modul_sablon_yaz->dosya_icerik(__DIR__ . '/../sablon/yorum_gonder.html');
	$modul_sablon_yaz->vars = [
		'__degisken' => [
			'uye_adi' => $uye_b->adi,
			'uye_foto' => $uye_foto,
			'tarih' => tarih_dt($tarih),
			'yorum' => $mesaj
		],
		'__if' => [
			'yorum_yazildi' => (int) $kaydet
		]
	];
	echo $modul_sablon_yaz->render();
} else {
	echo '<h5 class="y0">'.yc("Yorum gönderilemedi").'</h5>';
}
?>