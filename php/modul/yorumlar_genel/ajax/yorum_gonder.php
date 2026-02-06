<?php if (! defined('otoban')) exit;
$_SESSION['yorum_gonderildi'] = $_SESSION['yorum_gonderildi'] ?? 0;
if ($_SESSION['yorum_gonderildi'] > 80) {exit(yc("Yorum gönderilemedi")."!limit");}

if (isset($u_no__) && n0_($u_no__)) {
	$adisoyadi		= set_dolu('adisoyadi','p') ? z($_POST['adisoyadi']) : '';
	$emailadresi	= set_dolu('emailadresi','p') ? z($_POST['emailadresi']) : '';
	$yorum			= set_dolu('yorum','p') ? nl2br(html_d($_POST['yorum'])) : '';
	$ip_			= ip_();
	$db_yorum		= webAdresiSil($yorum);
	$tarih			= date("Y-m-d H:i:s");
	$yorum_yayin	= 0;
	
	if (strlen_($yorum) < 6) {
		exit('<h5 class="y0 __0">'.yc("Daha uzun yorum yazmalısınız").'</h5>');
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
		VALUES (:uye_no, '', 0, :adi_soyadi, :email, :mesaj, :ip, :tarih, :yayin)";
	$stmt_insert = $pdo->prepare($sql_insert);
	$stmt_insert->bindParam(':uye_no', $u_no__, PDO::PARAM_INT);
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
	echo '<h5 class="y0">'.yc("Yorum gönderildi").'</h5>';
} else {
	echo '<h5 class="y0">'.yc("Yorum gönderilemedi").'</h5>';
}
?>