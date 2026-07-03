<?php
header('Content-Type: application/json; charset=utf-8');

/**
 * =========================
 * CONFIG
 * =========================
 */
$GOOGLE_CLIENT_ID = $so_->d('google_login_client_id') ?? '';
$idToken = $_POST['id_token'] ?? null;

if (!$idToken) {
    echo json_encode(['status' => 'error', 'msg' => 'id_token eksik']);
    exit;
}

/**
 * =========================
 * CURL FETCH FUNCTION
 * =========================
 */
function httpGet($url)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT => "Google-JWKS-Client"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response ?: null;
}

/**
 * =========================
 * GOOGLE JWKS CACHE SYSTEM
 * =========================
 */
$cacheFile = __DIR__ . '/google_certs.json';
$certs = null;

// 1. CACHE READ
if (is_file($cacheFile)) {
    $raw = file_get_contents($cacheFile);
    $certs = json_decode($raw, true);
}

// 2. CACHE INVALID → REFRESH
if (!is_array($certs) || empty($certs['keys'])) {

    $json = httpGet("https://www.googleapis.com/oauth2/v3/certs");

    if ($json) {
        $decoded = json_decode($json, true);

        if (is_array($decoded) && !empty($decoded['keys'])) {
            $certs = $decoded;
            @file_put_contents($cacheFile, $json);
        }
    }
}

// 3. HARD FAIL SAFE
if (!is_array($certs) || empty($certs['keys'])) {
    echo json_encode([
        "status" => "error",
        "msg" => "Google certs yüklenemedi"
    ]);
    exit;
}

/**
 * =========================
 * JWT VERIFY
 * =========================
 */
function verifyGoogleToken($idToken, $clientId, $keys)
{
    try {
        $payload = \Firebase\JWT\JWT::decode(
            $idToken,
            \Firebase\JWT\JWK::parseKeySet($keys)
        );
    } catch (\Throwable $e) {
        return false;
    }

    if (!$payload) return false;

    if (empty($payload->aud) || $payload->aud !== $clientId) return false;

    if (empty($payload->iss) ||
        !in_array($payload->iss, ["accounts.google.com", "https://accounts.google.com"])) {
        return false;
    }

    if (empty($payload->exp) || $payload->exp < time()) return false;

    if (empty($payload->email_verified) || !$payload->email_verified) return false;

    return [
        "email"   => $payload->email ?? null,
        "name"    => $payload->name ?? null,
        "picture" => $payload->picture ?? null
    ];
}

/**
 * =========================
 * VERIFY TOKEN
 * =========================
 */
$user = verifyGoogleToken($idToken, $GOOGLE_CLIENT_ID, $certs);

if (!$user) {
    echo json_encode([
        "status" => "error",
        "msg" => "Geçersiz token"
    ]);
    exit;
}

/**
 * =========================
 * NORMALIZE USER
 * =========================
 */
$google_email   = email_duzenle($user['email']);
$google_name    = z($user['name'] ?? '');
$google_picture = z($user['picture'] ?? '');

/**
 * =========================
 * DB CHECK
 * =========================
 */
$stmt = $pdo->prepare("SELECT no, yetki_no, yayin FROM {$do_}uyeler WHERE k_adi = :kadi LIMIT 1");
$stmt->execute(['kadi' => $google_email]);
$uye = $stmt->fetch(PDO::FETCH_OBJ);

if ($uye) {

    if ($uye->yayin != 1) {
        echo json_encode(["status" => "error", "msg" => "Hesabınız aktif değil"]);
        exit;
    }

    $_SESSION['kullanici_no'] = $uye->no;
    $_SESSION['kullanici_yetki_no'] = $uye->yetki_no;
    $_SESSION['giris_yapildi'] = true;

    $stmt2 = $pdo->prepare("UPDATE {$do_}uyeler SET son_giris = :tarih WHERE no = :no");
    $stmt2->execute([
        'tarih' => date("Y-m-d H:i:s"),
        'no' => $uye->no
    ]);

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

    if (pdo_insert($pdo, $do_.'uyeler', $insert)) {

        $newId = $pdo->lastInsertId();

        $_SESSION['kullanici_no'] = $newId;
        $_SESSION['kullanici_yetki_no'] = 5;
        $_SESSION['giris_yapildi'] = true;

    } else {
        echo json_encode([
            "status" => "error",
            "msg" => "Kayıt oluşturulamadı"
        ]);
        exit;
    }
}

/**
 * =========================
 * SUCCESS
 * =========================
 */
echo json_encode(["status" => "success"]);