<?php
if (!syetki([2])) {
	exit(json_encode(['return' => 0, 'mesaj' => 'Yetkisiz erişim.']));
}

if (isset($_POST['json'])) {

	echo $json_text = html_d($_POST['json']);

	$json_dizi = json_decode($json_text, true);
	NestableMenuProcessor::processArray($pdo, $do_, $json_dizi);

}