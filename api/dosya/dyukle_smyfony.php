<?php
// Symfony bileşenleri gerekli:
// composer require symfony/http-foundation symfony/mime
// composer require intervention/image (resim işleme için)

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Mime\MimeTypes;
use Intervention\Image\ImageManagerStatic as Image;

if (!syetki([2,3,4])) {
	echo json_encode(['return' => 0, 'mesaj' => 'Yetkisiz erişim.']);
	exit;
}

$dizinbu = isset($_POST['dd_'])
? ROOT . '/' . z($_POST['dd_'])
: exit(json_encode(['return' => 0, 'mesaj' => 'Dizin Yok']));

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
	'image/jpeg','image/jpg','image/png','image/gif','image/webp','image/bmp','image/svg+xml'
];

$forbidden = ['text/html','text/css','application/javascript','application/json','application/xml',
	'application/x-httpd-php','application/x-php','text/x-php','application/php'];

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
	echo json_encode(['return' => 0, 'mesaj' => 'Dosya yok veya yüklenemedi.']);
	exit;
}

// Symfony UploadedFile nesnesi
$dosya = new UploadedFile(
	$_FILES['file']['tmp_name'],
	$_FILES['file']['name'],
	$_FILES['file']['type'],
	$_FILES['file']['error'],
	true
);

// ===== GÜVENLİK KONTROL BLOĞU =====

$file_extension = strtolower($dosya->getClientOriginalExtension());
$dangerous_extensions = ['php','php3','php4','php5','phtml','exe','sh','bat','com','pif','cmd','vbs','js','jar','scr','cgi','pl','py'];

if (in_array($file_extension, $dangerous_extensions)) {
	echo json_encode(['return' => 0, 'mesaj' => 'Bu dosya türü güvenlik nedeniyle yüklenemez.']);
	exit;
}

$mime = $dosya->getMimeType();
$is_image_claim = strpos($mime, 'image/') === 0;

if ($is_image_claim || $dosya_tip == 'resim') {
	$image_info = @getimagesize($dosya->getPathname());
	
	if ($image_info === false) {
		echo json_encode(['return' => 0, 'mesaj' => 'Geçersiz resim dosyası.']);
		exit;
	}
	
	$allowed_image_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP, IMAGETYPE_BMP];
	if (!in_array($image_info[2], $allowed_image_types)) {
		echo json_encode(['return' => 0, 'mesaj' => 'Bu resim formatı desteklenmiyor.']);
		exit;
	}
	
	$safe_mime_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
	if (!in_array($image_info['mime'], $safe_mime_types)) {
		echo json_encode(['return' => 0, 'mesaj' => 'Geçersiz resim MIME type.']);
		exit;
	}
	
	$allowed_img_extensions = ['jpg','jpeg','png','gif','webp','bmp'];
	if (!in_array($file_extension, $allowed_img_extensions)) {
		echo json_encode(['return' => 0, 'mesaj' => 'Resim dosya uzantısı geçersiz.']);
		exit;
	}
}

// MIME type kontrolü
if (!in_array($mime, $allowed)) {
	$wildcard_allowed = false;
	foreach ($allowed as $pattern) {
		if (strpos($pattern, '*') !== false) {
			$regex = '/^' . str_replace('*', '.*', preg_quote($pattern, '/')) . '$/';
			if (preg_match($regex, $mime)) {
				$wildcard_allowed = true;
				break;
			}
		}
	}
	if (!$wildcard_allowed) {
		echo json_encode(['return' => 0, 'mesaj' => 'Bu dosya türü yüklenemez.']);
		exit;
	}
}

if (in_array($mime, $forbidden)) {
	echo json_encode(['return' => 0, 'mesaj' => 'Bu dosya türü yasaklanmış.']);
	exit;
}

// Dosya boyut kontrolü
$max_size = $is_image_claim ? 104857600 : 10485760;
if ($dosya->getSize() > $max_size) {
	echo json_encode(['return' => 0, 'mesaj' => 'Dosya boyutu çok büyük.']);
	exit;
}

// Zararlı kod kontrolü
$file_content = file_get_contents($dosya->getPathname(), false, null, 0, 2048);
if (preg_match('/<\?php|<\?=|<script[\s>]/i', $file_content)) {
	echo json_encode(['return' => 0, 'mesaj' => 'Dosyada güvenlik ihlali tespit edildi.']);
	exit;
}

// ===== GÜVENLİK KONTROL BİTİŞ =====

// Dizinleri oluştur
is_dir($dizinbu) ?: dizin_olustur($dizinbu, 0755);

if (!empty($b__)) {
	is_dir($dizinbu . $b__) ?: dizin_olustur($dizinbu . $b__, 0755);
	is_dir($dizinbu . $k__) ?: dizin_olustur($dizinbu . $k__, 0755);	
}

$json_result = ['return' => 0, 'mesaj' => 'Yükleme başarısız.', 'dosya' => ''];

try {
	// Güvenli dosya adı oluştur
	$safe_name = empty($resim_yeni_adi) 
		? url_duzenle(pathinfo($dosya->getClientOriginalName(), PATHINFO_FILENAME))
		: url_duzenle($resim_yeni_adi);
	
	$new_filename = $safe_name . '.' . $file_extension;
	
	// Overwrite kontrolü
	$target_path = $dizinbu . $b__ . '/' . $new_filename;
	if (!$overwrite && file_exists($target_path)) {
		$new_filename = $safe_name . '_' . uniqid() . '.' . $file_extension;
		$target_path = $dizinbu . $b__ . '/' . $new_filename;
	}
	
	// Dosyayı taşı
	$dosya->move($dizinbu . $b__, $new_filename);
	
	// Resim işlemleri
	if ($is_image_claim) {
		$img = Image::make($target_path);
		
		// Boyutlandırma
		if ($img->width() > $yresim_g) {
			$img->resize($yresim_g, null, function ($constraint) {
				$constraint->aspectRatio();
				$constraint->upsize();
			});
		}
		
		// Filigran
		if ($filigran && file_exists(ROOT . '/resim/filigran.png')) {
			$watermark = Image::make(ROOT . '/resim/filigran.png');
			$img->insert($watermark, 'center');
		}
		
		$img->save($target_path);
		
		// Küçük resim oluştur
		if (!empty($k__)) {
			$img_k = Image::make($target_path);
			$img_k->resize($resim_k_yg, null, function ($constraint) {
				$constraint->aspectRatio();
				$constraint->upsize();
			});
			$img_k->save($dizinbu . $k__ . '/' . $new_filename);
		}
	}
	
	$resim_yaz = str_replace(ROOT . '/', '', "{$dizinbu}{$k__}/{$new_filename}");
	$json_result['dosya'] = $resim_yaz . '?v=' . uniqid();
	$json_result['return'] = 1;
	$json_result['mesaj'] = 'Yükleme başarılı.';
	
	// Veritabanı güncelleme
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
	
} catch (FileException $e) {
	$json_result['mesaj'] = 'Dosya yükleme hatası: ' . $e->getMessage();
} catch (\Exception $e) {
	$json_result['mesaj'] = 'Hata: ' . $e->getMessage();
}

echo json_encode($json_result);
exit;