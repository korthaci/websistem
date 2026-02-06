<?php if (! defined('otoban')) exit('!vad');

$sql = "SELECT no, adi, email_adresi, email_alimi, tarih FROM {$do_}modul_email_listesi";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$liste = $stmt->fetchAll(PDO::FETCH_OBJ);

if ($liste && count($liste) > 0){
	echo '<table class="tablo2"><thead><tr><th>Adı</th><th>Email</th><th class="g_60_">Email alımı</th></tr></thead><tbody>';
	foreach ($liste as $l) {
		echo '
		<tr>
		<td>'.$l->adi.'</td>
		<td>'.$l->email_adresi.'</td>
		<td><span class="db01 var'.$l->email_alimi.'" data-nta="'.sifrele($l->no.',,modul_email_listesi,,email_alimi').'">'.$l->email_alimi.'</span></td>
		</tr>';
	}
	echo '</tbody></table>';
}
