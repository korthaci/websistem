<?php if (!defined('otoban')) exit;

if (!syetki("2,3,4")) {
	echo json_encode(['return' => 0, 'mesaj' => yc("Yetkisiz erişim.")]);
	exit;
}

$response = ['return' => 0, 'mesaj' => yc("Bilinmeyen bir hata oluştu.")];

try {
	$n = 0;
	$tablo = '';

	if (isset($_POST['nt'])) {
		$nt_array = explode(",,", sifre_ac($_POST['nt']));
		$n = isset($nt_array[0]) ? intval($nt_array[0]) : 0;
		$tablo = isset($nt_array[1]) ? z($nt_array[1]) : '';
	} elseif (isset($_POST['n']) && isset($_POST['t'])) {
		$n = is_numeric($_POST['n']) ? intval($_POST['n']) : ( !empty(trim($_POST['n'])) ? intval(sifre_ac($_POST['n'])) : 0 );
		$tablo = z($_POST['t']);
	} else {
		error_log("uis_sil.php Hatası: Gerekli parametreler eksik (n, t veya nt). POST: " . print_r($_POST, true));
		$response['mesaj'] = yc("Gerekli bilgiler eksik.");
		echo json_encode($response);
		exit;
	}

	if ($n === 0 || empty($tablo)) {
		error_log("uis_sil.php Hatası: Geçersiz veya eksik veri. n:{$n}, tablo:{$tablo}");
		$response['mesaj'] = yc("Geçersiz veya eksik veri.");
		echo json_encode($response);
		exit;
	}

	$degistir = false;
	
	if ($tablo === 'sayfa') {
		dizin_sil('resim/_sayfa/'.$n.'_'. nd_md5($n));
		$degistir = kayit_sil_( $do_ . $tablo, $n);
	} else {
		$stmt_delete = $pdo->prepare("DELETE FROM {$do_}$tablo WHERE no = :n");
		$stmt_delete->bindParam(':n', $n, PDO::PARAM_INT);
		$degistir = $stmt_delete->execute();
	}

	if ($degistir) {
		/*
		$stmt_delete_ceviriler = $pdo->prepare("DELETE FROM {$do_}ceviriler WHERE tablo = :tablo AND tablo_no = :n");
		$stmt_delete_ceviriler->bindParam(':tablo', $tablo, PDO::PARAM_STR);
		$stmt_delete_ceviriler->bindParam(':n', $n, PDO::PARAM_INT);
		$stmt_delete_ceviriler->execute();
		*/
		$response = ['return' => 1, 'mesaj' => yc("Kayıt başarıyla silindi.")];
	} else {
		$response = ['return' => 0, 'mesaj' => yc("Kayıt silinemedi.")];
	}

} catch (PDOException $e) {
	error_log("uis_sil.php PDO Hatası: " . $e->getMessage() . " SQL: " . ($stmt_delete->queryString ?? 'N/A'));
	$response = ['return' => 0, 'mesaj' => yc("Veritabanı hatası.")];
} catch (Exception $e) {
	error_log("uis_sil.php Genel Hata: " . $e->getMessage());
	$response = ['return' => 0, 'mesaj' => yc("Bir hata oluştu.")];
}

echo json_encode($response);