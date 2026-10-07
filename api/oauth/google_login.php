<?php
header('Content-Type: application/json; charset=utf-8');

$GOOGLE_CLIENT_ID = trim($so_->d('google_login_client_id') ?? '');
$idToken = $_POST['id_token'] ?? null;

if (!$idToken) {
    process_log('Google login error: id_token istekte bulunamadı.');
    echo json_encode(['status' => 'error', 'msg' => 'id_token eksik']);
    exit;
}

function httpGet($url) {
    $ch = curl_init();
    if ($ch === false) {
        process_log('Google login error: JWKS HTTP isteği için cURL başlatılamadı.');
        return null;
    }
    process_log('Google login: JWKS cURL oturumu başlatıldı.');

    $optionsSet = curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT => "Google-JWKS-Client"
    ]);
    process_log($optionsSet
        ? 'Google login: JWKS cURL seçenekleri ayarlandı.'
        : 'Google login error: JWKS cURL seçenekleri ayarlanamadı.');

    $response = curl_exec($ch);
    if ($response === false) {
        process_log('Google login error: JWKS isteği başarısız: ' . curl_error($ch));
    } else {
        process_log('Google login: JWKS isteği başarılı.');
    }
    curl_close($ch);
    process_log('Google login: JWKS cURL oturumu kapatıldı.');

    return $response === false ? null : $response;
}

function getGoogleCerts($force = false) {
    $cacheFile = __DIR__ . '/google_certs.json';
    $ttl = 3600;
    $cached = null;

    if (is_file($cacheFile)) {
        $raw = file_get_contents($cacheFile);
        $cached = $raw ? json_decode($raw, true) : null;
        $fresh = (time() - filemtime($cacheFile)) < $ttl;

        if (!$force && $fresh && is_array($cached) && !empty($cached['keys'])) {
            process_log('Google login: Sertifika önbelleği taze, kullanılıyor.');
            return $cached;
        }
    }

    process_log('Google login: Google JWKS yenileniyor.');
    $json = httpGet("https://www.googleapis.com/oauth2/v3/certs");
    $decoded = $json ? json_decode($json, true) : null;

    if (is_array($decoded) && !empty($decoded['keys'])) {
        @file_put_contents($cacheFile, $json, LOCK_EX);
        process_log('Google login: JWKS yenilendi ve önbelleğe yazıldı.');
        return $decoded;
    }

    if (is_array($cached) && !empty($cached['keys'])) {
        process_log('Google login error: JWKS yenilenemedi, eski önbellek kullanılıyor.');
        return $cached;
    }

    return null;
}

$certs = getGoogleCerts();

if (!is_array($certs) || empty($certs['keys'])) {
    process_log('Google login error: Google sertifikaları yüklenemedi; oturum açma durduruldu.');
    echo json_encode(["status" => "error", "msg" => "Google certs yüklenemedi"]);
    exit;
}

function verifyGoogleToken($idToken, $clientId, $keys){
    try {
        $payload = \Firebase\JWT\JWT::decode(
            $idToken,
            \Firebase\JWT\JWK::parseKeySet($keys)
        );
    } catch (\Throwable $e) {
        process_log('Google login error: JWT çözümlenemedi: ' . $e->getMessage());
        return false;
    }

    if (!$payload) {
        process_log('Google login error: JWT payload boş.');
        return false;
    }

    if (empty($payload->aud) || $payload->aud !== $clientId) {
        process_log('Google login error: JWT audience (aud) eşleşmiyor.');
        return false;
    }

    if (empty($payload->iss) ||
        !in_array($payload->iss, ["accounts.google.com", "https://accounts.google.com"])) {
        process_log('Google login error: JWT issuer (iss) geçersiz.');
        return false;
    }

    if (empty($payload->exp) || $payload->exp < time()) {
        process_log('Google login error: JWT süresi dolmuş veya exp alanı eksik.');
        return false;
    }

    if (empty($payload->email_verified) || !$payload->email_verified) {
        process_log('Google login error: Google hesabının e-posta doğrulaması yapılmamış.');
        return false;
    }

    process_log('Google login: JWT doğrulandı.');
    return [
        "email"   => $payload->email ?? null,
        "name"    => $payload->name ?? null,
        "picture" => $payload->picture ?? null
    ];
}

$user = verifyGoogleToken($idToken, $GOOGLE_CLIENT_ID, $certs);

if (!$user) {
    $freshCerts = getGoogleCerts(true);
    if (is_array($freshCerts) && !empty($freshCerts['keys'])) {
        $user = verifyGoogleToken($idToken, $GOOGLE_CLIENT_ID, $freshCerts);
    }
}

if (!$user) {
    process_log('Google login error: Token doğrulanamadı; oturum açma reddedildi.');
    echo json_encode([
        "status" => "error",
        "msg" => "Geçersiz token"
    ]);
    exit;
}

$google_email   = email_duzenle($user['email']);
$google_name    = z($user['name'] ?? '');
$google_picture = z($user['picture'] ?? '');
process_log('Google login: Kullanıcı alanları normalize edildi.');

try {
    $stmt = $pdo->prepare("SELECT no, yetki_no, yayin FROM {$do_}uyeler WHERE k_adi = :kadi LIMIT 1");
    process_log('Google login: Kullanıcı sorgusu hazırlandı.');
    $stmt->execute(['kadi' => $google_email]);
    process_log('Google login: Kullanıcı sorgusu çalıştırıldı.');
    $uye = $stmt->fetch(PDO::FETCH_OBJ);
    process_log($uye
        ? 'Google login: Mevcut kullanıcı bulundu.'
        : 'Google login: Mevcut kullanıcı bulunamadı.');
} catch (\Throwable $e) {
    process_log('Google login error: Kullanıcı sorgusu başarısız: ' . $e->getMessage());
    throw $e;
}

if ($uye) {

    if ($uye->yayin != 1) {
        process_log('Google login error: Kullanıcı hesabı aktif değil.');
        echo json_encode(["status" => "error", "msg" => "Hesabınız aktif değil"]);
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['kullanici_no'] = $uye->no;
    $_SESSION['kullanici_yetki_no'] = $uye->yetki_no;
    $_SESSION['giris_yapildi'] = true;
    process_log('Google login: Mevcut kullanıcı için oturum bilgileri oluşturuldu.');

    try {
        $stmt2 = $pdo->prepare("UPDATE {$do_}uyeler SET son_giris = :tarih WHERE no = :no");
        process_log('Google login: Son giriş güncelleme sorgusu hazırlandı.');
        $stmt2->execute([
            'tarih' => date("Y-m-d H:i:s"),
            'no' => $uye->no
        ]);
        process_log('Google login: Mevcut kullanıcının son giriş zamanı güncellendi.');
    } catch (\Throwable $e) {
        process_log('Google login error: Son giriş zamanı güncellenemedi: ' . $e->getMessage());
        throw $e;
    }

} else {

    $insert = [
        'adi' => $google_name,
        'email' => $google_email,
        'k_adi' => $google_email,
        'fotograf' => $google_picture,
        'yetki_no' => 5,
        'yayin' => 1,
        'tarih' => 'NOW()',
        'son_giris' => 'NOW()',
    ];

    try {
        $inserted = pdo_insert($pdo, $do_.'uyeler', $insert);
        process_log($inserted
            ? 'Google login: Yeni kullanıcı kaydı oluşturuldu.'
            : 'Google login error: Yeni kullanıcı kaydı oluşturulamadı.');
    } catch (\Throwable $e) {
        process_log('Google login error: Yeni kullanıcı kaydı sırasında hata oluştu: ' . $e->getMessage());
        throw $e;
    }

    if ($inserted) {

        $newId = $pdo->lastInsertId();
        process_log('Google login: Yeni kullanıcı ID bilgisi alındı.');

        session_regenerate_id(true);
        $_SESSION['kullanici_no'] = $newId;
        $_SESSION['kullanici_yetki_no'] = 5;
        $_SESSION['giris_yapildi'] = true;
        process_log('Google login: Yeni kullanıcı için oturum bilgileri oluşturuldu.');

    } else {
        process_log('Google login error: Yeni kullanıcı kaydı başarısız; oturum açma durduruldu.');
        echo json_encode([
            "status" => "error",
            "msg" => "Kayıt oluşturulamadı"
        ]);
        exit;
    }
}

process_log('Google login: Oturum açma işlemi başarıyla tamamlandı.');
echo json_encode(["status" => "success"]);