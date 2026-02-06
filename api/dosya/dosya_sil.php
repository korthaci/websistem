<?php
if (!syetki([2,3,4])) {
	exit(json_encode(['return' => 0, 'mesaj' => 'Yetkisiz erişim.']));
}

$dizinbu = isset($_POST['dizin']) ? z($_POST['dizin']) : null;
$dosyabu = isset($_POST['dosya']) ? z($_POST['dosya']) : null;

$tam_dizin_yolu = ROOT . '/' . $dizinbu;
$tam_dosya_yolu = $tam_dizin_yolu . '/' . $dosyabu;

if (!$dizinbu || !$dosyabu) {
	exit(json_encode(['return' => 0, 'mesaj' => '!dizin dosya']));
}

if (isset($_POST['nta'])) {
	$nta = explode(",,", sifre_ac($_POST['nta']));
	$nn 			= isset($nta[0]) ? intval($nta[0]) : 0;
	$nn_tablo		= isset($nta[1]) ? z($nta[1]) : '';
	$nn_alan 		= isset($nta[2]) ? z($nta[2]) : '';
} else {
	$nn				= isset($_POST['nn']) ? intval($_POST['nn']) : 0;
	$nn_tablo		= isset($_POST['nn_t']) && !empty($_POST['nn_t'])  ? z($_POST['nn_t']) : '';
	$nn_alan		= isset($_POST['nn_a']) && !empty($_POST['nn_a']) ? z($_POST['nn_a']) : '';
}

$b__ = (is_dir($tam_dizin_yolu . '/b')) ? '/b' : '';
$k__ = (is_dir($tam_dizin_yolu . '/k')) ? '/k' : '';

if ($nn > 0 && !empty($nn_alan) && !empty($nn_tablo)) {
	try {
		$stmt = $pdo->prepare("UPDATE {$do_}$nn_tablo SET $nn_alan = NULL WHERE no = :nn");
		$stmt->bindParam(':nn', $nn, PDO::PARAM_INT);
		$stmt->execute();
	} catch (PDOException $e) {
		error_log("DB Update Error: " . $e->getMessage());
	}
}

$dosya_silindi = $dosya_silindi_b = $dosya_silindi_k = false;

if (!empty($dizinbu) && !empty($dosyabu)) {
	$dosya_silindi = (file_exists($tam_dosya_yolu) && unlink($tam_dosya_yolu));
	if (!empty($b__)) {
		$dosya_silindi_b = (is_file($tam_dizin_yolu . $b__ . '/' . $dosyabu) && unlink($tam_dizin_yolu . $b__ . '/' . $dosyabu));
	}
	if (!empty($k__)) {
		$dosya_silindi_k = (is_file($tam_dizin_yolu . $k__ . '/' . $dosyabu) && unlink($tam_dizin_yolu . $k__ . '/' . $dosyabu));
	}
}

if ($dosya_silindi || $dosya_silindi_b || $dosya_silindi_k) {
	echo json_encode(['return' => 1, 'mesaj' => 'Dosya silindi.']);
} else {
	echo json_encode(['return' => 0, 'mesaj' => 'Dosya silinemedi.']);
}

exit;