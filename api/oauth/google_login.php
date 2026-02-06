<?php
header('Content-Type: application/json; charset=utf-8');

$GOOGLE_CLIENT_ID = $so_->d('google_login_client_id') ?? '';

$idToken = $_POST['id_token'] ?? null;
if (!$idToken) {
	exit(json_encode(['status' => 'error', 'msg' => 'id_token eksik']));
}

$cacheFile = __DIR__ . '/google_certs.json';
$shouldFetch = !file_exists($cacheFile) || (time() - filemtime($cacheFile)) > 86400;
if ($shouldFetch) {
	$certJson = @file_get_contents("https://www.googleapis.com/oauth2/v3/certs");
	if ($certJson !== false) {
		file_put_contents($cacheFile, $certJson);
	} else {
		error_log("Google sertifikaları alınamadı, eski cache kullanılacak.");
	}
}

$certs = json_decode(file_get_contents($cacheFile), true);

// ID token doğrulama fonksiyonu
function verifyGoogleIdToken($idToken, $clientId, $keys)
{
	try {
		$payload = \Firebase\JWT\JWT::decode($idToken, \Firebase\JWT\JWK::parseKeySet($keys));
	} catch (Exception $e) {
		return false;
	}

	// Gerekli kontroller
	if (empty($payload->aud) || $payload->aud !== $clientId) return false;
	if (empty($payload->iss) || !in_array($payload->iss, ["accounts.google.com", "https://accounts.google.com"])) return false;
	if (empty($payload->exp) || $payload->exp < time()) return false;
	if (empty($payload->email_verified) || !$payload->email_verified) return false;

	// Kullanıcı bilgilerini döndür
	return [
		"email"   => $payload->email ?? null,
		"name"    => $payload->name ?? null,
		"picture" => $payload->picture ?? null
	];
}

// Token doğrula
$user = verifyGoogleIdToken($idToken, $GOOGLE_CLIENT_ID, $certs);

// Başarısızsa JSON error
if (!$user) {
	echo json_encode(["status" => "error", "msg" => "Geçersiz token"]);
	exit;
}

// Google'dan gelen email ile k_adi eşleşmesi kontrol et
$google_email = email_duzenle($user['email']);
$google_name = z($user['name'] ?? '');
$google_picture = z($user['picture'] ?? '');

$stmt = $pdo->prepare("SELECT no, yetki_no, yayin FROM {$do_}uyeler WHERE k_adi = :kadi LIMIT 1");
$stmt->execute(['kadi' => $google_email]);
$uye = $stmt->fetch(PDO::FETCH_OBJ);

if ($uye) {
	// Kullanıcı mevcut - yayin kontrolü
	if ($uye->yayin != 1) {
		echo json_encode(["status" => "error", "msg" => "Hesabınız aktif değil"]);
		exit;
	}
	
	// Session oluştur
	$_SESSION['kullanici_no'] = $uye->no;
	$_SESSION['kullanici_yetki_no'] = $uye->yetki_no;
	$_SESSION['giris_yapildi'] = true;
	
	// son_giris güncelle
	$stmt2 = $pdo->prepare("UPDATE {$do_}uyeler SET son_giris = :tarih WHERE no = :no");
	$stmt2->execute([
		'tarih' => date("Y-m-d H:i:s"),
		'no' => $uye->no
	]);
} else {
	// Yeni kullanıcı oluştur
	$uyeler_insert = [
		'adi' => $google_name,
		'email' => $google_email,
		'k_adi' => $google_email,
		'fotograf' => $google_picture,
		'yetki_no' => 5,
		'yayin' => 1,
		'tarih' => 'NOW()',
		'son_giris' => 'NOW()',
	];
	
	if (pdo_insert($pdo, $do_.'uyeler', $uyeler_insert)) {
		$yeni_uye_no = $pdo->lastInsertId();
		
		// Session oluştur
		$_SESSION['kullanici_no'] = $yeni_uye_no;
		$_SESSION['kullanici_yetki_no'] = 5;
		$_SESSION['giris_yapildi'] = true;
	} else {
		echo json_encode(["status" => "error", "msg" => "Kayıt oluşturulamadı"]);
		exit;
	}
}

// Başarılı
echo json_encode(["status" => "success"]);
