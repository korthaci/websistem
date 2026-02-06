<?php

exit;
// bu dosya dmod modunda modulleri getiriyor.


/*
if (!syetki("2,3")) {
	echo json_encode(['return' => 0, 'mesaj' => 'Yetkisiz erişim.']);
	exit;
}
$response = ['return' => 0, 'mesaj' => 'Bilinmeyen bir hata oluştu.'];

$html = '';
try {
	$stmt = $pdo->prepare("SELECT no, url, adi FROM {$do_}moduller WHERE yayin=1");
	$stmt->execute();
	$moduller = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if (!empty($moduller)) {
		foreach ($moduller as $m){
			$html .= '<span class="almodul_ekle" data-almodul="'.htmlspecialchars($m['url'], ENT_QUOTES).'">';
			$html .= '<div class="almodul_ekle_adi">'.htmlspecialchars($m['adi']).'</div>';
			$html .= '<img src="'.IMG.'/trspr.png" class="al_modul_img" alt="">';
			$html .= '</span>';
		}
	}
} catch (PDOException $e) {
	error_log('PDO error in uis_moduller_getir.php: ' . $e->getMessage());
}

$response['return'] = 1;
$response['mesaj'] = $html;
echo json_encode($response);
exit;
*/