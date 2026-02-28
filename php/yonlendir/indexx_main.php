<?php if (! defined('otoban')) exit('vad');

$stmt = $pdo->prepare("SELECT no, adi, icerik, hash FROM {$do_}blok_html WHERE yayin = 1 AND tema = :tema ORDER BY sira ASC, no DESC");
$stmt->execute([':tema' => $so_->d('tema')]);

foreach ($stmt->fetchAll() as $blok) {
	echo degisken_duzenle(
		html_modul(
			html_a(cc($blok->icerik, $blok->no, 'blok_html', 'icerik'))
		)
	);
	echo '<a class="cb_" id="blok_' . $blok->hash . '"></a>';
}