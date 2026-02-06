<?php if (!defined('otoban')) exit;

if (!syetki("2,3,4")) {
	echo json_encode(['return' => 0, 'mesaj' => yc("Yetkisiz erişim.")]);
	exit;
}
$response = ['return' => 0, 'mesaj' => yc("Bilinmeyen bir hata oluştu.")];

try {
	$n = 0;
	$tablo = '';
	$alan = '';

	if (isset($_POST['nta'])) {
		$nta = explode(",,", sifre_ac($_POST['nta']));
		$n = isset($nta[0]) ? intval($nta[0]) : 0;
		$tablo = isset($nta[1]) ? z($nta[1]) : '';
		$alan = isset($nta[2]) ? z($nta[2]) : '';
	} elseif (isset($_POST['n']) && isset($_POST['t']) && isset($_POST['a'])) {
		$n = is_numeric($_POST['n']) ? intval($_POST['n']) : ( !empty(trim($_POST['n'])) ? intval(sifre_ac($_POST['n'])) : 0 );
		$tablo = z($_POST['t']);
		$alan = z($_POST['a']);
	} else {
		error_log("uis_dbtext.php Hatası: Gerekli parametreler eksik (n, t, a veya nta). POST: " . print_r($_POST, true));
		$response['mesaj'] = yc("Gerekli bilgiler eksik.");
		echo json_encode($response);
		exit;
	}

	if ($n === 0 || empty($tablo) || empty($alan)) {
		error_log("uis_dbtext.php Hatası: Geçersiz veya eksik veri. n:{$n}, tablo:{$tablo}, alan:{$alan}");
		$response['mesaj'] = yc("Geçersiz veya eksik veri.");
		echo json_encode($response);
		exit;
	}

	$stmt_eski = $pdo->prepare("SELECT $alan FROM {$do_}$tablo WHERE no = :n");
	$stmt_eski->bindParam(':n', $n, PDO::PARAM_INT);
	$stmt_eski->execute();
	$eski_deger = $stmt_eski->fetchColumn();

	$deger = html_d($_POST['deger']);

	$datatipi = set_dolu('datatipi','p') ? z($_POST['datatipi']) : false;
	if ($datatipi === 'email') {
		$explode_deger = explode(",", $deger);
		$email_adresleri = [];
		foreach ($explode_deger as $ed) {
			if (filter_var(trim($ed), FILTER_VALIDATE_EMAIL)) {
				$email_adresleri[] = trim($ed);
			}
		}
		$deger = implode(",", $email_adresleri);
	} elseif (in_array($datatipi, ['date', 'datetime', 'datetime-local'])) {
		$timestamp = strtotime($deger);
		if ($timestamp) {
			$deger = ($datatipi === 'date') ? date("Y-m-d", $timestamp) : date("Y-m-d H:i:s", $timestamp);
		} else {
			$deger = '';
		}
	}

	$datajson = set_dolu('datajson','p') ? z($_POST['datajson']) : false;

	$onceki_deger = null;
	$degistir = false;

	if ($datajson === false) {
		$stmt_select_old = $pdo->prepare("SELECT $alan FROM {$do_}$tablo WHERE no = :n");
		$stmt_select_old->bindParam(':n', $n, PDO::PARAM_INT);
		$stmt_select_old->execute();
		$onceki_deger = $stmt_select_old->fetchColumn();

		if ($onceki_deger == $deger && !in_array($datatipi, ['date', 'datetime', 'datetime-local'])) {
			$response = ['return' => 2, 'mesaj' => yc("Değer değişmedi.")];
			echo json_encode($response);
			exit;
		}

		$update_value = ($deger === '') ? NULL : $deger;

		$stmt_update = $pdo->prepare("UPDATE {$do_}$tablo SET $alan = :deger WHERE no = :n");
		if ($update_value === NULL) {
			$stmt_update->bindValue(':deger', NULL, PDO::PARAM_NULL);
		} else {
			$stmt_update->bindParam(':deger', $update_value, PDO::PARAM_STR);
		}
		$stmt_update->bindParam(':n', $n, PDO::PARAM_INT);
		$degistir = $stmt_update->execute();
	} else {
		$stmt_select_old = $pdo->prepare("SELECT JSON_UNQUOTE(JSON_EXTRACT($alan, '$.{$datajson}')) FROM {$do_}$tablo WHERE no = :n");
		$stmt_select_old->bindParam(':n', $n, PDO::PARAM_INT);
		$stmt_select_old->execute();
		$onceki_deger = $stmt_select_old->fetchColumn();

		if ($onceki_deger == $deger) {
			$response = ['return' => 2, 'mesaj' => yc("Değer değişmedi.")];
			echo json_encode($response);
			exit;
		}

		$stmt_update = $pdo->prepare("UPDATE {$do_}$tablo SET $alan = JSON_SET($alan, '$.{$datajson}', :deger) WHERE no = :n");
		$stmt_update->bindParam(':deger', $deger, PDO::PARAM_STR);
		$stmt_update->bindParam(':n', $n, PDO::PARAM_INT);
		$degistir = $stmt_update->execute();
	}
	
	if ($degistir) {
		$response = ['return' => 1, 'mesaj' => yc("Değer kaydedildi.")];
	} else {
		$response = ['return' => 0, 'mesaj' => yc("Değer kaydedilemedi.")];
	}

} catch (PDOException $e) {
	error_log("uis_dbtext.php PDO Hatası: " . $e->getMessage() . " SQL: " . ($stmt_update->queryString ?? 'N/A'));
	$response = ['return' => 0, 'mesaj' => yc("Veritabanı hatası.")];
} catch (Exception $e) {
	error_log("uis_dbtext.php Genel Hata: " . $e->getMessage());
	$response = ['return' => 0, 'mesaj' => yc("Bir hata oluştu.")];
}

if (class_exists('log_db')) {
	if ($response['return'] == 1) {
		$kullanici_adi = db_adi::get($pdo, $do_, 'uyeler', $u_no__);
		$eski = [$alan => $eski_deger];
		$yeni = [$alan => $deger];
		$islem = "$tablo -> $alan değeri [$kullanici_adi($u_no__)] tarafından değiştirildi. Eski değer : {$eski_deger}. Yeni değer : {$deger}.";

		$log_db = new log_db($pdo, $do_);
		$log_db->yaz($u_no__, $tablo, $n, $islem, $eski, $yeni);
	}	
}

echo json_encode($response);
