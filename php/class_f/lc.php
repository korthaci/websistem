<?php if (! defined('otoban')) exit();

/**
 * LC — License Checker
 * Remote sunucudan dönen HMAC-SHA256 imzalı token'ı doğrular.
 * Secret key bu dosyada YOK. Sadece imza doğrulaması yapılır.
 *
 * Token formatı (remote'un ürettiği):
 *   payload = domain + ":" + feature + ":" + expires_at
 *   token   = payload + "." + base64url( HMAC-SHA256(payload, SECRET_KEY) )
 *
 * .env'de tanımlı olması gereken:
 *   LC_REMOTE_URL=http://localhost/site/websistem_remote
 *   LC_API_KEY=ajans-api-anahtari
 *   LC_VERIFY_KEY=remote-public-verify-key
 */
class LC {

    /** Cache süresi (saniye) — remote'a her sorguda gitme */
    private static int $cache_ttl = 3600;
    private static bool $debug = false;

    /**
     * Verilen feature için lisansı doğrula.
     *
     * @param  string $feature  Örn: "ai_studio", "premium_tema_xyz"
     * @param  string $domain   Boş bırakılırsa $_SERVER['HTTP_HOST'] kullanılır
     * @return bool
     */
    public static function verify(string $feature, string $domain = ''): bool
    {
        if (empty($domain)) {
            $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        }
        if(self::$debug) error_log("LC DEBUG: verify called for $feature on $domain");

        // 1. Önce cache'e bak
        $cached = self::cache_get($feature, $domain);
        if ($cached !== null) {
            // error_log("LC DEBUG: Cache hit for $feature: " . ($cached ? 'valid' : 'invalid'));
            return $cached;
        }

        // 2. Remote'a sor
        $token = self::remote_ask($feature, $domain);
        if ($token === null) {
            if(self::$debug) error_log("LC DEBUG: remote_ask returned null for $feature");
            return false;
        }

        // 3. Token'ı doğrula
        $valid = self::token_verify($token, $feature, $domain);
        if (!$valid) {
            if(self::$debug) error_log("LC DEBUG: token_verify FAILED for $feature. Token: " . substr($token, 0, 20) . "...");
        }

        // 4. Cache'e yaz
        self::cache_set($feature, $domain, $valid);

        return $valid;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Remote iletişim
    // ─────────────────────────────────────────────────────────────────────────

    private static function remote_ask(string $feature, string $domain): ?string
    {
        $remote_url = rtrim(getenv('LC_REMOTE_URL') ?: '', '/') . '/api/verify_license.php';
        $api_key    = getenv('LC_API_KEY') ?: '';

        if (empty($remote_url) || empty($api_key)) {
            if(self::$debug) error_log("LC DEBUG: Missing config. URL: '$remote_url', Key: '$api_key'");
            return null;
        }

        $payload = json_encode([
            'api_key' => $api_key,
            'domain'  => $domain,
            'feature' => $feature,
        ]);

        $ssl_verify = !(function_exists('local_test') && local_test());

        $ch = curl_init($remote_url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_SSL_VERIFYPEER => $ssl_verify,
            CURLOPT_SSL_VERIFYHOST => $ssl_verify ? 2 : 0,
        ]);
        $response = curl_exec($ch);
        $err      = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err || $response === false) {
            if(self::$debug) error_log('LC DEBUG: Remote connection error — ' . $err);
            return null;
        }

        if ($http_code !== 200) {
            if(self::$debug) error_log("LC DEBUG: Remote server returned HTTP $http_code. Response: " . substr($response, 0, 100));
        }

        $data = json_decode($response, true);
        if (!($data['token'] ?? null)) {
            if(self::$debug) error_log("LC DEBUG: No token in response. Data: " . print_r($data, true));
        }
        return $data['token'] ?? null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Token doğrulama
    // ─────────────────────────────────────────────────────────────────────────

    private static function token_verify(string $token, string $feature, string $domain): bool
    {
        $verify_key = getenv('LC_VERIFY_KEY') ?: '';
        if (empty($verify_key)) {
            if(self::$debug) error_log('LC: LC_VERIFY_KEY tanımlı değil.');
            return false;
        }

        // token = "domain:feature:expires_at.base64url_signature"
        $dot_pos = strrpos($token, '.');
        if ($dot_pos === false) return false;

        $payload   = substr($token, 0, $dot_pos);
        $sig_b64   = substr($token, $dot_pos + 1);
        $signature = base64_decode(strtr($sig_b64, '-_', '+/'));

        // İmzayı doğrula
        $expected = hash_hmac('sha256', $payload, $verify_key, true);
        if (!hash_equals($expected, $signature)) {
            if(self::$debug) error_log('LC DEBUG: Signature verification FAILED for ' . $feature);
            return false;
        }

        // Payload ayrıştır: domain:feature:expires_at
        $parts = explode(':', $payload, 3);
        if (count($parts) !== 3) return false;

        [$t_domain, $t_feature, $t_expires] = $parts;

        if ($t_domain !== $domain)   return false;
        if ($t_feature !== $feature) return false;
        if ((int)$t_expires < time()) return false;

        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Basit dosya cache (session kullanmamak için)
    // ─────────────────────────────────────────────────────────────────────────

    private static function cache_key(string $feature, string $domain): string
    {
        return 'lc_' . md5($domain . '_' . $feature);
    }

    private static function cache_get(string $feature, string $domain): ?bool
    {
        $key  = self::cache_key($feature, $domain);
        $file = sys_get_temp_dir() . '/' . $key . '.cache';

        if (!is_file($file)) return null;

        $data = json_decode(file_get_contents($file), true);
        if (!$data || !isset($data['expires'], $data['valid'])) return null;
        if ($data['expires'] < time()) {
            @unlink($file);
            return null;
        }

        return (bool)$data['valid'];
    }

    private static function cache_set(string $feature, string $domain, bool $valid): void
    {
        $key  = self::cache_key($feature, $domain);
        $file = sys_get_temp_dir() . '/' . $key . '.cache';
        file_put_contents($file, json_encode([
            'valid'   => $valid,
            'expires' => time() + self::$cache_ttl,
        ]));
    }

    /** Test/debug için cache'i temizle */
    public static function cache_flush(string $feature, string $domain): void
    {
        $key  = self::cache_key($feature, $domain);
        $file = sys_get_temp_dir() . '/' . $key . '.cache';
        if(self::$debug) error_log("LC DEBUG: flushing cache for $feature on $domain. File: $file");
        @unlink($file);
    }
}
