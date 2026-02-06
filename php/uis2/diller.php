<?php if ( ! defined('otoban')) exit('vad.');
	echo '
	<div class="uis-page-header">
		<h2 class="uis-page-title"><i class="fa fa-language uis-text-p"></i> '.yc("Diller").'</h2>
	</div>';

$ulkeler = [];
try {
	$sql = "SELECT no, dkod, dyon, adi, adie, adio, ltr, sira, yayin
				FROM {$do_}diller
				WHERE no>0
				ORDER BY yayin DESC, sira ASC, adi ASC";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$ulkeler = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
	error_log('PDO error in diller.php: ' . $e->getMessage());
	$ulkeler = [];
}

echo '
<table class=" uis-table"><thead><tr><th>'.count($ulkeler).'</th>
<th>'.yc("Ülke Adı").'</th>
<th>en</th>
<th>'.yc("Orjinal").'</th>
<th>dkod</th>
<th>dyon</th>
<th>Ltr</th>
<th>'.yc("Sıra").'</th>
<th>'.yc("Yayın").'</th>
<th>'.yc("Sil").'</th>
</tr></thead><tbody class="sirala-liste" data-t="diller">';

foreach ($ulkeler as $s) {
	echo '
	<tr id="sira_'.$s->no.'" class="yazi1">
	<td>'.$s->no.'</td>
	<td><input class="dbtext i2 uis-input" type="text" value="'.$s->adi . '" data-nta="' . sifrele($s->no.',,diller,,adi') . '" /></td>
	<td><input class="dbtext i2 uis-input" type="text" value="'.$s->adie . '" data-nta="' . sifrele($s->no.',,diller,,adie') . '" /></td>
	<td><input class="dbtext i2 uis-input" type="text" value="'.$s->adio.'" data-nta="' . sifrele($s->no.',,diller,,adio') . '" /></td>
	<td>'.$s->dkod.'</td>
	<td>'.$s->dyon.'</td>
	<td>' . ($s->ltr == 1 ? '' : 'rtl') . '</td>
	<td class="stut"> </td>
	<td><span class="db01 var'.$s->yayin.'" data-nta="'.sifrele($s->no.',,diller,,yayin').'">'.$s->yayin.' </span></td>
	<td><span class="sil" data-nt="'.sifrele($s->no.',,diller').'" data-sil="0"> </span></td>
	</tr>';
}
echo '</tbody></table>';
?>