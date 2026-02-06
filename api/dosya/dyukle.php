<?php
//error_log(date('c') . " POST: " . print_r($_POST, true));
//error_log(date('c') . " OB: " . print_r(ob_get_status(), true));

if (!syetki([2,3,4])) {
	echo json_encode(['return' => 0, 'mesaj' => yc("Yetkisiz erişim.")]);
	exit;
}

use Verot\Upload\Upload;

$dizinbu = isset($_POST['dd_'])
? ROOT . '/' . z($_POST['dd_'])
: exit(json_encode(['return' => 0, 'mesaj' => yc("Dizin Yok")]));

$yresim_g		= isset($_POST['yg']) ? intval($_POST['yg']) : 1600;
$resim_k_yg		= isset($_POST['rb']) ? intval($_POST['rb']) : 400;
$resim_bk		= isset($_POST['bk']) && intval($_POST['bk']) === 1;
$dosya_tip		= isset($_POST['dtip']) ? z($_POST['dtip']) : 'dosya';
$resim_yeni_adi	= isset($_POST['adi']) ? z($_POST['adi']) : '';

if (isset($_POST['nta'])) {
	$nta = explode(",,", sifre_ac($_POST['nta']));
	$nn 			= isset($nta[0]) ? intval($nta[0]) : 0;
	$nn_tablo		= isset($nta[1]) ? z($nta[1]) : '';
	$nn_alan 		= isset($nta[2]) ? z($nta[2]) : '';
} else {
	$nn				= isset($_POST['nn']) ? intval($_POST['nn']) : 0;
	$nn_tablo		= isset($_POST['nn_t']) && !empty($_POST['nn_t'])  ? z($_POST['nn_t']) : '';
	$nn_alan		= isset($_POST['nn_a']) && !empty($_POST['nn_a']) ? z($_POST['nn_a']) : '';
}

$overwrite		= isset($_POST['ow']) && $_POST['ow'] == 1;
$filigran		= (isset($_POST['filigran']) && $_POST['filigran'] == 1);

$b__ = (($resim_bk || is_dir($dizinbu.'/b')) && $dosya_tip != 'dosya') ? '/b' : '';
$k__ = (($resim_bk || is_dir($dizinbu.'/k')) && $dosya_tip != 'dosya') ? '/k' : '';

$dosya_tum		= $dosya_tip === 'dosya';

$allowed = [
	'application/pdf', 'application/msword','application/rtf','text/rtf','text/richtext',
	'application/vnd.ms-excel','text/csv',
	'application/powerpoint','application/vnd.ms-powerpoint',
	'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
	'application/vnd.openxmlformats-officedocument.presentationml.presentation',
	'application/zip','application/x-zip','application/x-zip-compressed',
	'application/x-rar','application/rar','application/x-rar-compressed',
	'application/vnd.rar','application/x-7z-compressed',
	'text/plain','text/tab-separated-values',
	'application/vnd.oasis.opendocument.text',
	'application/x-udf',
	'application/octet-stream','audio/*','video/*',
	'application/x-shockwave-flash',
	// Image MIME types - wildcard yerine açık liste
	'image/jpeg','image/jpg','image/png','image/gif','image/webp','image/bmp','image/svg+xml'
];

$forbidden = ['text/html','text/css','application/javascript','application/json','application/xml',
	'application/x-httpd-php','application/x-php','text/x-php','application/php'];

$dosya = isset($_FILES['file']) ? $_FILES['file'] : null;

if (!$dosya) {
	echo json_encode(['return' => 0, 'mesaj' => yc("Dosya yok veya yüklenemedi.")]);
	exit;
}

// ===== GÜVENLİK KONTROL BLOĞU BAŞLANGIÇ =====

// 1. Dosya uzantısı kontrolü
$file_extension = strtolower(pathinfo($dosya['name'], PATHINFO_EXTENSION));
$dangerous_extensions = ['php','php3','php4','php5','phtml','exe','sh','bat','com','pif','cmd','vbs','js','jar','scr','cgi','pl','py'];

if (in_array($file_extension, $dangerous_extensions)) {
	echo json_encode(['return' => 0, 'mesaj' => yc("Bu dosya türü güvenlik nedeniyle yüklenemez.")]);
	exit;
}

// 2. Image dosyası için özel kontroller
$is_image_claim = strpos($dosya['type'], 'image/') === 0;

if ($is_image_claim || $dosya_tip == 'resim') {
	
	// Gerçek resim kontrolü
	$image_info = @getimagesize($dosya['tmp_name']);
	
	if ($image_info === false) {
		echo json_encode(['return' => 0, 'mesaj' => yc("Geçersiz resim dosyası. Dosya bozuk veya resim formatında değil.")]);
		exit;
	}
	
	// İzin verilen resim formatları
	$allowed_image_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP, IMAGETYPE_BMP];
	if (!in_array($image_info[2], $allowed_image_types)) {
		echo json_encode(['return' => 0, 'mesaj' => yc("Bu resim formatı desteklenmiyor.")]);
		exit;
	}
	
	// MIME type çift kontrolü
	$safe_mime_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
	if (!in_array($image_info['mime'], $safe_mime_types)) {
		echo json_encode(['return' => 0, 'mesaj' => yc("Geçersiz resim MIME type.")]);
		exit;
	}
	
	// Uzantı-format uyumu kontrolü
	$allowed_img_extensions = ['jpg','jpeg','png','gif','webp','bmp'];
	if (!in_array($file_extension, $allowed_img_extensions)) {
		echo json_encode(['return' => 0, 'mesaj' => yc("Resim dosya uzantısı geçersiz.")]);
		exit;
	}
}

// 3. Dosya içeriğinde zararlı kod kontrolü (ilk 2KB)
$file_content = file_get_contents($dosya['tmp_name'], false, null, 0, 2048);
if (preg_match('/<\?php|<\?=|<script[\s>]/i', $file_content)) {
	echo json_encode(['return' => 0, 'mesaj' => yc("Dosyada güvenlik ihlali tespit edildi.")]);
	exit;
}

// ===== GÜVENLİK KONTROL BLOĞU BİTİŞ =====

is_dir($dizinbu) ?: dizin_olustur($dizinbu, 0755);

if (!empty($b__)) {
	is_dir($dizinbu . $b__) ?: dizin_olustur($dizinbu . $b__, 0755);
	is_dir($dizinbu . $k__) ?: dizin_olustur($dizinbu . $k__, 0755);	
}

// ===== VEROT CVE-2023-6551 KORUMASI - BURAYA EKLE =====
// XSS önleme: Dosya adını tamamen sanitize et
$safe_filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($dosya['name'], PATHINFO_FILENAME));
$safe_filename = substr($safe_filename, 0, 100); // Maksimum 100 karakter

if (empty($safe_filename)) {
    $safe_filename = 'file_' . uniqid();
}

// Resim yeni adı belirlenmemişse, güvenli adı kullan
if (empty($resim_yeni_adi)) {
    $resim_yeni_adi = $safe_filename;
}

// Double extension saldırılarını engelle
$dosya['name'] = $safe_filename . '.' . $file_extension;
// ===== KORUMA BİTİŞ =====

$r__ = new Upload($dosya);

$json_result = ['return' => 0, 'mesaj' => yc("Yükleme başarısız."), 'dosya' => ''];

if ($r__->uploaded) {

	$is_image = strpos($r__->file_src_mime, 'image/') === 0;
	
	$r__->file_new_name_body    = (empty($resim_yeni_adi)) ? url_duzenle($r__->file_src_name_body) : url_duzenle($resim_yeni_adi);

	if ($is_image) {
		$r__->image_x               = $yresim_g;
		$r__->image_resize          = !($r__->image_src_x < $r__->image_x);
		$r__->image_ratio_y			= true;
	}
	$r__->file_max_size         = $is_image ? 104857600 : 10485760;
	$r__->allowed               = $allowed;
	$r__->forbidden             = $forbidden;
	$r__->file_overwrite        = $overwrite;
	

	if ($filigran && $is_image) {
		$r__->image_watermark       = ROOT . '/resim/filigran.png';
		$r__->image_watermark_position = 'C';
		
	}
	
	$r__->process($dizinbu . $b__);
	
	if ($r__->processed) {

		$resim_yaz = str_replace(ROOT . '/', '', "{$dizinbu}{$k__}/{$r__->file_dst_name}");
		$json_result['dosya'] = $resim_yaz . '?v='.uniqid();
		$json_result['return'] = 1;
		$json_result['mesaj'] = yc("Yükleme başarılı.");
		
		if (($is_image && !empty($b__))) {
			
			$rb = resim_boyutlandir_($dizinbu.$b__.'/'.$r__->file_dst_name, $resim_k_yg, $dizinbu.$k__.'/',$r__->file_dst_name );     
			if ( !$rb && is_file($dizinbu . $b__ . '/' . $r__->file_dst_name) ) {
				unlink($dizinbu . $b__ . '/' . $r__->file_dst_name);
			}
		}

		if ($nn > 0 && !empty($nn_alan) && !empty($nn_tablo)) {
			
			try {
				$sql = "UPDATE {$do_}$nn_tablo SET $nn_alan = :resim_yaz WHERE no = :no";
				$stmt = $pdo->prepare($sql);
				$stmt->execute([
					':resim_yaz' => $resim_yaz,
					':no' => $nn
				]);
			} catch (PDOException $e) {
				error_log('PDO update error: ' . $e->getMessage());
			}
		}
	} else {
		$json_result['mesaj'] = $r__->error;
	}
	$r__->clean();
	unset($r__);
} else {
	$json_result['mesaj'] = $r__->error;
}

echo json_encode($json_result);
exit;