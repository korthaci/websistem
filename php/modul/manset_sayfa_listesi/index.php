<?php if (! defined('otoban')) exit('!vad');

$db_limit = 30;

$sql = "SELECT no, url, adi, icerik, resim, tarih, etiket FROM {$do_}sayfa
        WHERE manset = 1 AND yayin = 1 AND adi != '' AND resim != ''
        ORDER BY sira ASC, no DESC LIMIT 0, :limit";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':limit', $db_limit, PDO::PARAM_INT);
$stmt->execute();
$mansetler = $stmt->fetchAll(PDO::FETCH_OBJ);
$sayfalar_dizi = [];
$manset_adi = 'Manşetler';

if ($mansetler && count($mansetler) > 0) {
	foreach ($mansetler as $m_) {
		$resimbu = dosya_fe($m_->resim) ? $m_->resim : IMG.'/wqj.webp';
		$link__ = href('index','sayfa='.$m_->url.'.'.$m_->no.'&d='.$d);

		$m_->href = $link__;
		$m_->resim = $resimbu;
		$m_->icerik = yazi_tag_kes(strip_tags($m_->icerik),230);
		$m_->tarih = tarih_dt($m_->tarih);
		$sayfalar_dizi[] = $m_;
	}

	$modul_sablon_yaz = new sablon_yaz;
	$modul_sablon_yaz->dosya_icerik(__DIR__ . '/sablon/manset_sayfa_listesi.html');
	$modul_sablon_yaz->vars = [
	'__degisken' => ['manset_adi' => $manset_adi],
	'__foreach' => ['manset' => $sayfalar_dizi]
	];
	echo $modul_sablon_yaz->render();
} else {
	echo '!';
}
?>