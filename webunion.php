<?php
function getRealIp() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

$ip = getRealIp();

$file = './engellenen_ip.txt'; // htaccess ile korunuyor
$ttl = 7 * 86400; // 7 gün sonra IP kaydı otomatik düşer

/* ---------------------------
   Yardımcı: süresi geçmiş satırları eleyip
   yeni IP'yi ekleyerek dosyayı güncelle
---------------------------- */
function cleanAndAppend($file, $ip, $ttl) {
    $now = time();
    $lines = [];

    if (file_exists($file)) {
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $parts = explode('|', $line);
            if (count($parts) === 2 && ($now - (int)$parts[1]) < $ttl) {
                $lines[] = $line; // hâlâ geçerli, tut
            }
        }
    }

    $lines[] = $ip . '|' . $now;
    file_put_contents($file, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX);
}

/* ---------------------------
   1. IP blacklist kontrol
---------------------------- */
if (file_exists($file)) {
    $now = time();
    $ips = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $parts = explode('|', $line);
        if (count($parts) === 2 && ($now - (int)$parts[1]) < $ttl) {
            $ips[$parts[0]] = true;
        }
    }
    if (isset($ips[$ip])) {
        exit;
    }
}

/* ---------------------------
   2. Honeypot + Timing kontrol
   Sadece İKİSİ birden tetiklenirse blackliste yaz.
   Tek sinyal varsa formu sessizce reddet ama IP'yi bloklamadan
   (false positive riskine karşı).
---------------------------- */
$honeypot = $_POST['webunion'] ?? null;
$time = $_POST['webunion_time'] ?? null;

$honeypotFilled = $honeypot !== null && trim($honeypot) !== '';
$tooFast = $time !== null && is_numeric($time) && (time() - (int)$time) < 2;

if ($honeypotFilled && $tooFast) {
    // çift sinyal: neredeyse kesin bot -> blackliste yaz
    cleanAndAppend($file, $ip, $ttl);
    exit;
} elseif ($honeypotFilled || $tooFast) {
    // tek sinyal: şüpheli ama kesin değil -> reddet, bloklama
    exit;
}

/* ---------------------------
   Anti-bot protection layer (özet)
   - IP blacklist: TTL ile sınırlı, kalıcı blok yok
   - Honeypot (webunion): gizli alan, normal kullanıcı doldurmaz
   - Timing (webunion_time): çok hızlı gönderim sinyali
   - Blok kararı SADECE iki sinyal birlikte tetiklenince verilir
   - Tek sinyal -> form reddedilir ama IP bloklanmaz (false positive koruması)
   - Format geriye dönük uyumlu: ip|timestamp
---------------------------- */