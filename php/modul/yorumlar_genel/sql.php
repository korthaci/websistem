<?php

$sql_tablo = $do_ . 'yorumlar';


$_sql[] = "
CREATE TABLE IF NOT EXISTS $sql_tablo (
	no int(11) NOT NULL,
	uye_no int(11) DEFAULT 0,
	tablo varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	tablo_no int(11) DEFAULT 0,
	yorum_no int(11) NOT NULL DEFAULT 0,
	adi_soyadi varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	email_adresi varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	mesaj text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	ip varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
	tarih datetime DEFAULT NULL,
	yayin tinyint(1) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
";


$_sql[] = "
ALTER TABLE $sql_tablo
	ADD PRIMARY KEY IF NOT EXISTS (no);
";


$_sql[] = "
ALTER TABLE $sql_tablo
MODIFY no int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
";

?>
