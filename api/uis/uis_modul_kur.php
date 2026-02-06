<?php if (! defined('otoban')) exit('otoban');

if (!syetki("2")) {
	echo json_encode(['return' => 0, 'mesaj' => yc("Yetkisiz erişim.")]);
	exit;
}
$response = ['return' => 0, 'mesaj' => yc("Bilinmeyen bir hata oluştu.")];

$ekle = false;

if (isset($_POST['m'])) {
	$modul_url = z($_POST['m']);
	if (file_exists( MODUL_DIR .'/'. $modul_url)){

		try {
			$stmt = $pdo->prepare("INSERT INTO {$do_}moduller (adi, url) VALUES (:adi, :url)");
			$ekle = $stmt->execute([':adi' => $modul_url, ':url' => $modul_url]);
		} catch (PDOException $e) {
			error_log('PDO insert error in uis_modul_kur.php: ' . $e->getMessage());
			$ekle = false;
		}

		if (file_exists( MODUL_DIR .'/'. $modul_url.'/sql.php')){
			include_once MODUL_DIR .'/'. $modul_url.'/sql.php';
			if (isset($sql_tablo)) {
				try {
					$stmt = $pdo->prepare("SHOW TABLES LIKE :tbl");
					$stmt->execute([':tbl' => $sql_tablo]);
					$tablo_var = $stmt->fetchColumn();
				} catch (PDOException $e) {
					error_log('PDO show table error in uis_modul_kur.php: ' . $e->getMessage());
					$tablo_var = false;
				}

				if (empty($tablo_var)) {
					if (!empty($_sql) && is_array($_sql)) {
						foreach ($_sql as $sql){
							try {
								$pdo->exec($sql);
							} catch (PDOException $e) {
								error_log('PDO exec error in uis_modul_kur.php: ' . $e->getMessage());
							}
						}
					}
				}
			}
		}
	}
}
//$pdo->query("DROP TABLE IF EXISTS $sql_tablo");

echo json_encode(['return' => (int)$ekle, 'mesaj' => '']);
