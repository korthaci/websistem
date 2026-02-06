<?php

$sql_tablo = $do_ . 'modul_email_listesi';


$_sql[] = "
CREATE TABLE IF NOT EXISTS $sql_tablo (
  no int(11) NOT NULL,
  adi varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  email_adresi varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  email_alimi tinyint(1) DEFAULT 1,
  tarih datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
";

$_sql[] = "
ALTER TABLE $sql_tablo
  ADD PRIMARY KEY IF NOT EXISTS (no),
  ADD UNIQUE KEY IF NOT EXISTS email_adresi (email_adresi);
";

$_sql[] = "
ALTER TABLE $sql_tablo
MODIFY no int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
";
?>