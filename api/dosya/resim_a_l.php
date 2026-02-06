
<?php

$dizinbu = isset($_POST['dizin']) ? z($_POST['dizin']) : null;
$dosya_adi = isset($_POST['dosya']) ? z($_POST['dosya']) : null;

if (!$dizinbu || !$dosya_adi) {
	echo json_encode(['return' => 0, 'mesaj' => 'Dizin veya dosya adı eksik.']);
	exit;
}

$tam_dizin_yolu = ROOT . '/' . $dizinbu;
$dosyabu = $dizinbu && $dosya_adi ? $dizinbu . '/' . $dosya_adi : null;
$nn = isset($_POST['nn_']) ? intval($_POST['nn_']) : 0;
$tablo = isset($_POST['t_']) ? z($_POST['t_']) : '';
$alan = isset($_POST['a_']) ? z($_POST['a_']) : '';


if (empty($tablo) || empty($alan) || !is_file(ROOT . '/' . $dosyabu)) {
	echo json_encode(['return' => 0, 'mesaj' => '!dosyabu: ' . $dosyabu]);
	exit;
}

try {
	$stmt = $pdo->prepare("SELECT $alan FROM {$do_}$tablo WHERE no = :nn LIMIT 1");
	$stmt->execute([':nn' => $nn]);
	$mevcut = $stmt->fetchColumn();


	if ($mevcut === $dosyabu) {
		$stmt2 = $pdo->prepare("UPDATE {$do_}$tablo SET $alan = NULL WHERE no = :nn");
		$update = $stmt2->execute([':nn' => $nn]);
		if ($update) {
			echo json_encode(['return' => 2, 'mesaj' => 'Profil resmi kaldırıldı.']);
		} else {
			echo json_encode(['return' => 0, 'mesaj' => 'Profil resmi kaldırılamadı.']);
		}
		exit;
	}

	$stmt2 = $pdo->prepare("UPDATE {$do_}$tablo SET $alan = :dosya WHERE no = :nn");
	$update = $stmt2->execute([':dosya' => $dosyabu, ':nn' => $nn]);

	if ($update) {
		echo json_encode(['return' => 1, 'mesaj' => 'Profil resmi güncellendi.']);
	} else {
		echo json_encode(['return' => 0, 'mesaj' => 'Güncelleme başarısız.']);
	}
} catch (PDOException $e) {
	echo json_encode(['return' => 0, 'mesaj' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
