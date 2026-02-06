<?php if (! defined('otoban')) exit;

if (isset($_POST['m_islem'])) {

$modul_islem = z($_POST['m_islem']);

	switch ($modul_islem) {
		case "emailekle"	: include_once __DIR__.'/email_listesi_ekle.php'; break;
		default:exit;
	}
}

?>