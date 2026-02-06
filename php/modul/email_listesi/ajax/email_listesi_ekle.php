<?php if (! defined('otoban')) exit;

$_SESSION['email_eks'] = $_SESSION['email_eks'] ?? 1;

if ($_SESSION['email_eks'] > 5) exit(yc('Bir sorun oluştu. Lütfen tekrar deneyin.') . ' !limit');

$adi_soyadi = '';//isset($_POST['liste_adisoyadi']) ? z($_POST['liste_adisoyadi']):'';
$e_posta_ = isset($_POST['liste_emailadresi']) ? z(email_duzenle($_POST['liste_emailadresi'])) :'';
$tarih = date("Y-m-d H:i:s");

if (filter_var($e_posta_, FILTER_VALIDATE_EMAIL)) {
	$sql_mail_check = "SELECT COUNT(no) FROM {$do_}modul_email_listesi WHERE email_adresi = :email";
	$stmt_mail = $pdo->prepare($sql_mail_check);
	$stmt_mail->bindParam(':email', $e_posta_, PDO::PARAM_STR);
	$stmt_mail->execute();
	$mail_adresi_varmi = $stmt_mail->fetchColumn();
	
	$sql_uye_check = "SELECT COUNT(no) FROM {$do_}uyeler WHERE email_adresi = :email";
	$stmt_uye = $pdo->prepare($sql_uye_check);
	$stmt_uye->bindParam(':email', $e_posta_, PDO::PARAM_STR);
	$stmt_uye->execute();
	$uye_mail_adresi_varmi = $stmt_uye->fetchColumn();

	if ($mail_adresi_varmi == 0 && $uye_mail_adresi_varmi == 0) {
		$sql_insert = "INSERT INTO {$do_}modul_email_listesi (adi, email_adresi, tarih) VALUES (:adi, :email, :tarih)";
		$stmt_insert = $pdo->prepare($sql_insert);
		$stmt_insert->bindParam(':adi', $adi_soyadi, PDO::PARAM_STR);
		$stmt_insert->bindParam(':email', $e_posta_, PDO::PARAM_STR);
		$stmt_insert->bindParam(':tarih', $tarih, PDO::PARAM_STR);
		$email_ekle = $stmt_insert->execute();
		
		echo ($email_ekle === true) ? '<br>&radic; ' . yc('Mail adresiniz kaydedildi. Teşekkür ederiz.') : '<br>! ' . yc('Bir hata oluştu. Lütfen tekrar deneyin.');
	} else {
		echo '<br>! ' . yc('Bu mail adresi kayıtlı.');
	}
} else {
	echo '<br>! ' . yc('Geçersiz mail adresi.');
}
$_SESSION['email_eks']++;