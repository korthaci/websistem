<?php if (! defined('otoban')) exit;

if (isset($_POST['m_islem'])) {

$modul_islem = z($_POST['m_islem']);

	switch ($modul_islem) {

		case "yorum_gonder"	: include_once __DIR__.'/yorum_gonder.php'; break;

		default : exit("!");
	}
}

?>