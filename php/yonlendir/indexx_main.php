<?php if (! defined('otoban')) exit('vad');

$sql = "SELECT no, adi, icerik, hash FROM {$do_}blok_html WHERE yayin = 1 ORDER BY sira ASC, no DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$bloklar = $stmt->fetchAll();

if ($bloklar && count($bloklar) > 0) {

    $blok_yazi = '';
	foreach ($bloklar as $blok) {
		$blok_yazi .= html_modul(html_a(cc($blok->icerik, $blok->no, 'blok_html', 'icerik')));
        $blok_yazi .= '<a class="cb_" id="blok_'.$blok->hash.'"></a>';
	}
    echo $blok_yazi;
}