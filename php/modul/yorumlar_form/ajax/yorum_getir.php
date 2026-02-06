<?php if (! defined('otoban')) exit;

$tablo_		= isset($_POST['t']) ? z($_POST['t']) : false;
$tablo_no_	= isset($_POST['tn']) ? substr(substr(z($_POST['tn']), 0, -32), 32) : 0;

if ($tablo_ != false && $tablo_no_ > 0) {
	$yorumlar_liste = [];
	$sql = "SELECT no, uye_no, tablo, tablo_no, yorum_no, adi_soyadi, email_adresi, mesaj, tarih, ip
			FROM {$do_}yorumlar
			WHERE yayin = 1 AND tablo = :tablo AND tablo_no = :tablo_no
			ORDER BY tarih DESC";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':tablo', $tablo_, PDO::PARAM_STR);
	$stmt->bindParam(':tablo_no', $tablo_no_, PDO::PARAM_INT);
	$stmt->execute();
	$yorumlar = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	if ($yorumlar && count($yorumlar) > 0) {
		foreach($yorumlar as $y_) {
			$sql_uye = "SELECT no, fotograf, adi FROM {$do_}uyeler WHERE no = :uye_no";
			$stmt_uye = $pdo->prepare($sql_uye);
			$stmt_uye->bindParam(':uye_no', $y_->uye_no, PDO::PARAM_INT);
			$stmt_uye->execute();
			$uye_b = $stmt_uye->fetch(PDO::FETCH_OBJ);
			$uye_foto = is_file($uye_b->fotograf) ? $uye_b->fotograf : IMG . '/fg1.jpg';

			$y_->adi = $uye_b->adi;
			$y_->tarih = tarih_dt($y_->tarih);
			$y_->uye_foto = $uye_foto;
			$y_->mesaj = nl2br($y_->mesaj);

			$yorumlar_liste[] = $y_;
		
		}

		$modul_sablon_yaz = new sablon_yaz;
		$modul_sablon_yaz->dosya_icerik(__DIR__ . '/../sablon/yorum_getir.html');
		$modul_sablon_yaz->vars = [
			'__foreach' => [
				'yorumlar_liste' => $yorumlar_liste
			]
		];
		echo $modul_sablon_yaz->render();
	}
} else {
	echo '!';
}

?>