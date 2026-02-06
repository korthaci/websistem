<?php if (! defined('otoban')) exit('!vad');
/* modül tablo adı dışında tüm parametreleri içeriyor. daha esnek bir yapıda olması için tablo adı içermiyor. kullanıldığı yerde tablo adı data-yorumt parametresine aktarılmalı. <div data-yorumt="urunler" data-yorumtn="n"><form>...</form></div>
*/
/* yorum altı yorumları yap */
if ($u_no__ > 0) {

	$sql = "SELECT adi, email_adresi, fotograf FROM {$do_}uyeler WHERE no = :uye_no";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':uye_no', $u_no__, PDO::PARAM_INT);
	$stmt->execute();
	$yorum_uye_b__ = $stmt->fetch(PDO::FETCH_OBJ);
	if ($yorum_uye_b__) {

		$uye_foto = is_file($yorum_uye_b__->fotograf) ? $yorum_uye_b__->fotograf : IMG.'/fg1.jpg';

		$modul_sablon_yaz = new sablon_yaz;
		$modul_sablon_yaz->dosya_icerik(__DIR__ . '/sablon/yorumlar_form.html');
		$modul_sablon_yaz->vars = [
			'__degisken' => [
				'uye_adi' => $yorum_uye_b__->adi,
				'uye_email' => $yorum_uye_b__->email_adresi,
				'uye_foto' => $uye_foto,
			]
		];
		echo $modul_sablon_yaz->render();
	
	}
}
?>