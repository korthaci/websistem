<?php if (! defined('otoban')) exit;

if (isset($_POST['cdiln']) && isset($_POST['a']) && isset($_POST['t']) && isset($_POST['tn'])) {

	$cdiln = z($_POST['cdiln']);
	$alan = z($_POST['a']);
	$tablo = z($_POST['t']);
	$tablo_no = intval($_POST['tn']);
	$yazi = html_d($_POST['yazi']);
	$yeni_kayit = z($_POST['yeni']);

	if (!empty($yazi)) {
		if ($yeni_kayit == '1') {
			$sql = "INSERT INTO {$do_}ceviriler (dil_no, tablo, tablo_no, alan, yazi) VALUES (:dil_no, :tablo, :tablo_no, :alan, :yazi)";
			$stmt = $pdo->prepare($sql);
			$res = $stmt->execute([
				':dil_no' => $cdiln,
				':tablo' => $tablo,
				':tablo_no' => $tablo_no,
				':alan' => $alan,
				':yazi' => $yazi
			]);
			echo json_encode(['return' => $res ? 1 : 0, 'mesaj' => $res ? 'Kayıt eklendi' : 'Kayıt eklenemedi']);
		} else {
			$sql = "UPDATE {$do_}ceviriler SET yazi = :yazi WHERE dil_no = :dil_no AND tablo = :tablo AND tablo_no = :tablo_no AND alan = :alan";
			$stmt = $pdo->prepare($sql);
			$res = $stmt->execute([
				':yazi' => $yazi,
				':dil_no' => $cdiln,
				':tablo' => $tablo,
				':tablo_no' => $tablo_no,
				':alan' => $alan
			]);
			echo json_encode(['return' => $res ? 1 : 0, 'mesaj' => $res ? 'Kayıt güncellendi' : 'Kayıt güncellenemedi']);
		}
	} else {
		echo json_encode(['return' => 0, 'mesaj' => 'Boş veri']);
	}

} elseif (isset($_POST['siln'])) {

	$siln = intval($_POST['siln']);

	$sql = "DELETE FROM {$do_}ceviriler WHERE no = :no";
	$stmt = $pdo->prepare($sql);
	$res = $stmt->execute([':no' => $siln]);

	echo json_encode(['return' => $res ? 1 : 0, 'mesaj' => $res ? 'Kayıt silindi' : 'Kayıt silinemedi']);

} else {
	echo json_encode(['return' => 0, 'mesaj' => 'Hatalı istek']);
}