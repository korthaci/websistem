<?php
/**
 * websistem v1.0 - Standalone Installer
 * Design: UIS2 Indigo Premium
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$root_dir = dirname(__DIR__);
$config_file = $root_dir . '/rlv_.php';
$installed_file = __DIR__ . '/installed';

$step = isset($_GET['step']) && !empty($_GET['step']) ? (int)$_GET['step'] : 1;

$error = '';
$success = '';

// 1. Language Detection & Translations
$browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en', 0, 2);
$lang = ($browser_lang === 'tr') ? 'tr' : 'en';
require_once __DIR__ . '/i18n.php';
function t($key) {
    global $texts, $lang;
    return $texts[$lang][$key] ?? $key;
}

// Check if already installed (skip for step 5 - completion page)
if ($step !== 5 && (file_exists($installed_file) || file_exists($config_file))) {
    die(t('already_installed'));
}


// Helper function for UI redirects
function goStep($s) {
    header("Location: index.php?step=$s");
    exit;
}

// Helper to split SQL file correctly, ignoring semicolons inside quotes
// Helper to split SQL file correctly, handling escaped quotes properly
function splitSqlFile($sql, $delimiter = ';') {
    $queries = [];
    $token = "";
    $inString = false;
    $escaped = false;
    $len = strlen($sql);
    
    for ($i = 0; $i < $len; $i++) {
        $char = $sql[$i];
        
        if ($escaped) {
            $token .= $char;
            $escaped = false;
            continue;
        }
        
        if ($char === '\\') {
            $escaped = true;
            $token .= $char;
            continue;
        }
        
        // Handle quotes
        if ($char === "'") {
            // Check for '' (escaped quote in SQL)
            if ($inString && $i + 1 < $len && $sql[$i+1] === "'") {
                $token .= "''";
                $i++; // Skip next quote
                continue;
            }
            $inString = !$inString;
        }
        
        // Check for delimiter
        if (!$inString && $char === $delimiter) {
            if (trim($token) !== "") {
                $queries[] = trim($token);
            }
            $token = "";
        } else {
            $token .= $char;
        }
    }
    
    // Add remaining token if any
    if (trim($token) !== "") {
        $queries[] = trim($token);
    }
    
    return $queries;
}

// Convert MySQL SQL to SQLite compatible SQL
function mysqlToSqlite($sql) {
    // 0. Preliminary: Handle MySQL-style string escapes for SQLite
    $sql = preg_replace_callback("/'([^'\\\\]*(?:\\\\.[^'\\\\]*)*)'/s", function($m) {
        $str = $m[1];
        // Unescape MySQL characters to SQL/SQLite standard
        // \n, \r, \t become actual characters. \' becomes ''
        $str = str_replace(
            ["\\'", "\\\"", "\\\\", "\\n", "\\r", "\\t"],
            ["''",   "\"",   "\\",   "\n",  "\r",  "\t"],
            $str
        );
        return "'" . $str . "'";
    }, $sql);

    // 1. Remove MySQL specific commands and comments
    $sql = preg_replace('/^SET\s+.*?;/im', '', $sql);
    $sql = preg_replace('/\/\*\!.*?\*\/;?/s', '', $sql);
    $sql = preg_replace('/START TRANSACTION;/i', '', $sql);
    $sql = preg_replace('/COMMIT;/i', '', $sql);
    
    // Remove MySQL table and column options
    $sql = preg_replace('/(ENGINE|DEFAULT\s+CHARSET|COLLATE|CHARACTER\s+SET|ROW_FORMAT|AUTO_INCREMENT)\s*=?\s*[^; \n,]+/i', '', $sql);
    $sql = preg_replace('/\b(AUTO_INCREMENT|CHARACTER\s+SET|COLLATE)\b/i', '', $sql);
    
    // Remove MySQL COMMENTs (e.g., COMMENT 'sample text')
    $sql = preg_replace('/\bCOMMENT\s+([\'"])(?:(?!\1).|\\\\\1)*\1/i', '', $sql);
    
    // 2. Data types
    $sql = preg_replace('/\btinyint\(\d+\)/i', 'INTEGER', $sql);
    $sql = preg_replace('/\bint\(\d+\)/i', 'INTEGER', $sql);
    $sql = preg_replace('/\bmediumtext\b|\blongtext\b/i', 'TEXT', $sql);
    $sql = preg_replace('/\bdatetime\b/i', 'DATETIME', $sql);
    $sql = preg_replace('/\bdouble\b/i', 'REAL', $sql);
    $sql = preg_replace('/current_timestamp\(\)/i', 'CURRENT_TIMESTAMP', $sql);

    // 3. Handle standalone PRIMARY KEY from ALTER TABLE
    if (preg_match_all('/ALTER TABLE [`"]?(\w+)[`"]?\s+ADD PRIMARY KEY\s*\([`"]?(\w+)[`"]?\);/i', $sql, $pk_matches, PREG_SET_ORDER)) {
        foreach ($pk_matches as $match) {
            $table = $match[1];
            $col = $match[2];
            
            // Re-inject PK into CREATE TABLE if not already there
            $sql = preg_replace_callback('/(CREATE TABLE (?:IF NOT EXISTS )?[`"]?'.$table.'[`"]?\s*\()(.*?)\);/is', function($m) use ($col) {
                $content = $m[2];
                // Check if Table already has an internal PRIMARY KEY
                if (stripos($content, 'PRIMARY KEY') === false) {
                    // Exact match for column name using word boundaries
                    $content = preg_replace('/(\b[`"]?'.$col.'[`"]?\s+INTEGER)(\s+NOT NULL)?\b/i', '$1$2 PRIMARY KEY AUTOINCREMENT', $content);
                }
                return $m[1] . $content . ');';
            }, $sql);
            
            $sql = str_replace($match[0], '', $sql);
        }
    }

    // 4. Remove other ALTER TABLE statements
    $sql = preg_replace('/ALTER TABLE .*? MODIFY .*?;/i', '', $sql);

    // 5. Handle inline PRIMARY KEY in CREATE TABLE
    if (preg_match_all('/CREATE TABLE (IF NOT EXISTS )?[`"]?(\w+)[`"]?\s*\((.*?)\);/s', $sql, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $table_sql = $match[3];
            if (stripos($table_sql, 'PRIMARY KEY') !== false) {
                 if (preg_match('/PRIMARY KEY\s*\([`"]?(\w+)[`"]?\)/i', $table_sql, $pk_match)) {
                    $pk_col = $pk_match[1];
                    // Inject only if not already injected by Step 3
                    if (strpos($table_sql, 'PRIMARY KEY AUTOINCREMENT') === false) {
                        // Exact match for column name using word boundaries
                        $new_table_sql = preg_replace('/(\b[`"]?'.$pk_col.'[`"]?\s+INTEGER)(\s+NOT NULL)?\b/i', '$1$2 PRIMARY KEY AUTOINCREMENT', $table_sql);
                    } else {
                        $new_table_sql = $table_sql;
                    }
                    $new_table_sql = preg_replace('/,\s*PRIMARY KEY\s*\([`"]?'.$pk_col.'[`"]?\)/i', '', $new_table_sql);
                    $sql = str_replace($match[3], $new_table_sql, $sql);
                 }
            }
        }
    }
    
    // Unique keys and cleanup
    $sql = preg_replace('/UNIQUE KEY\s+[`"]?\w+[`"]?\s*\((.*?)\)/i', 'UNIQUE ($1)', $sql);
    $sql = preg_replace('/,\s*KEY\s+[`"]?\w+[`0-9]*?\s*\(.*?\)/i', '', $sql);
    
    return $sql;
}

// Add MySQL polyfills for SQLite
function registerSqliteFunctions($pdo) {
    // NOW() polyfill
    $pdo->sqliteCreateFunction('NOW', function() {
        return date('Y-m-d H:i:s');
    }, 0);
    
    // MD5() polyfill
    $pdo->sqliteCreateFunction('MD5', function($string) {
        return md5($string);
    }, 1);
}

// POST processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 1: // Requirements confirmed
            goStep(2);
            break;

        case 2: // Database Settings
            $_SESSION['db_type'] = $_POST['db_type'] ?? 'mysql';
            $_SESSION['db_host'] = $_POST['db_host'] ?? 'localhost';
            $_SESSION['db_name'] = $_POST['db_name'] ?? '';
            $_SESSION['db_user'] = $_POST['db_user'] ?? '';
            $_SESSION['db_pass'] = $_POST['db_pass'] ?? '';
            $_SESSION['db_prefix'] = $_POST['db_prefix'] ?? 'r_';
            $_SESSION['sub_dir'] = $_POST['sub_dir'] ?? '';

            // Server Settings
            $_SESSION['server_type'] = $_POST['server_type'] ?? 'apache';
            $_SESSION['use_https'] = isset($_POST['use_https']) ? 1 : 0;
            $_SESSION['www_pref'] = $_POST['www_pref'] ?? 'none'; // none, www, non-www

            // Validate MySQL database name
            if ($_SESSION['db_type'] === 'mysql' && empty(trim($_SESSION['db_name']))) {
                $error = t('mysql_db_required');
                break;
            }

            // Test connection
            try {
                if ($_SESSION['db_type'] === 'sqlite') {
                    $sqlite_dir = $root_dir . "/php/sqlite_data";
                    $pdo_dsn = "sqlite:" . $sqlite_dir . "/" . $_SESSION['db_name'] . ".sqlite";
                    if (!is_dir($sqlite_dir)) mkdir($sqlite_dir, 0755, true);
                    $pdo = new PDO($pdo_dsn);
                    $pdo->exec("PRAGMA busy_timeout = 5000");
                    $pdo->exec("PRAGMA journal_mode = WAL");
                } else {
                    $pdo = new PDO(
                        "mysql:host={$_SESSION['db_host']};dbname={$_SESSION['db_name']};charset=utf8mb4",
                        $_SESSION['db_user'],
                        $_SESSION['db_pass'],
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                }
                goStep(3);
            } catch (PDOException $e) {
                $error = t('db_conn_error') . $e->getMessage();
            }
            break;

        case 3: // Admin Settings
            $_SESSION['admin_email'] = $_POST['admin_email'] ?? '';
            $_SESSION['admin_pass'] = $_POST['admin_pass'] ?? '';
            $_SESSION['site_name'] = $_POST['site_name'] ?? 'Websistem v1';

            if (empty($_SESSION['admin_email']) || empty($_SESSION['admin_pass'])) {
                $error = t('admin_req');
            } else {
                goStep(4);
            }
            break;

        case 4: // Final Installation
            try {
                $db_type = $_SESSION['db_type'];
                $db_name = $_SESSION['db_name'];
                $db_prefix = $_SESSION['db_prefix'];
                $sub_dir = $_SESSION['sub_dir'];
                $sub_dir_slash = empty($sub_dir) ? '/' : ($sub_dir . '/');

                if ($db_type === 'sqlite') {
                    $sqlite_dir = $root_dir . "/php/sqlite_data";
                    if (!is_dir($sqlite_dir)) mkdir($sqlite_dir, 0755, true);
                    $pdo = new PDO("sqlite:" . $sqlite_dir . "/" . $db_name . ".sqlite");
                    $pdo->exec("PRAGMA busy_timeout = 5000");
                    $pdo->exec("PRAGMA journal_mode = WAL");
                    registerSqliteFunctions($pdo);
                } else {
                    $pdo = new PDO("mysql:host={$_SESSION['db_host']};dbname=$db_name;charset=utf8mb4", $_SESSION['db_user'], $_SESSION['db_pass']);
                }
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                if ($db_type === 'sqlite') {
                    $pdo->beginTransaction();
                }

                // 0. Drop all existing tables
                if ($db_type === 'sqlite') {
                    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($tables as $table) {
                        $pdo->exec("DROP TABLE IF EXISTS `$table` ");
                    }
                } else {
                    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
                    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($tables as $table) {
                        $pdo->exec("DROP TABLE IF EXISTS `$table` ");
                    }
                    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
                }

                // 1. Load SQL
                $sql_file = __DIR__ . '/websistem.sql';
                if (!file_exists($sql_file)) throw new Exception(t('sql_not_found') . " ($sql_file)");
                
                $sql_content = file_get_contents($sql_file);
                
                // Convert to SQLite if needed
                if ($db_type === 'sqlite') {
                    $sql_content = mysqlToSqlite($sql_content);
                }

                if ($db_prefix !== 'r_') {
                    $sql_content = str_replace(['`r_', ' r_'], ['`'.$db_prefix, ' '.$db_prefix], $sql_content);
                }

                // Execute SQL with simpler, safer splitting or simpler regex
                $sql_content = preg_replace('/^--.*$/m', '', $sql_content); // Remove comments
                $queries = splitSqlFile($sql_content); // Use helper function
                
                foreach ($queries as $query) {
                    if (!empty($query)) {
                        $pdo->exec($query);
                    }
                }

                // 1.1 Theme-Specific Block Import
                // Get active theme
                $stmt = $pdo->prepare("SELECT deger FROM {$db_prefix}genel_ayarlar WHERE anahtar = 'tema'");
                $stmt->execute();
                $active_theme = $stmt->fetchColumn();
                $stmt->closeCursor(); // Ensure SQLite releases lock
                
                if ($active_theme) {
                    $theme_sql_file = $root_dir . "/tema/{$active_theme}/__install_db_sample/blok_html.sql";
                    if (file_exists($theme_sql_file)) {
                        $theme_sql = file_get_contents($theme_sql_file);
                        
                        // Drop existing blok_html table as requested (to ensure clean slate)
                        $pdo->exec("DROP TABLE IF EXISTS {$db_prefix}blok_html");
                        
                        // Convert if SQLite
                        if ($db_type === 'sqlite') {
                            $theme_sql = mysqlToSqlite($theme_sql);
                        }
                        
                        // Handle prefix
                        if ($db_prefix !== 'r_') {
                            $theme_sql = str_replace(['`r_', ' r_'], ['`'.$db_prefix, ' '.$db_prefix], $theme_sql);
                        }
                        
                        $theme_sql = preg_replace('/^--.*$/m', '', $theme_sql);
                        $theme_queries = splitSqlFile($theme_sql);
                        foreach ($theme_queries as $tq) {
                            if (!empty($tq)) {
                                try {
                                    $pdo->exec($tq);
                                } catch (Exception $e) {
                                    // Soft fail for theme blocks if minor errors occur
                                }
                            }
                        }
                    }
                }

                // 2. Update General Settings
                $proto = $_SESSION['use_https'] ? "https" : "http";
                $site_url = $proto . "://" . $_SERVER['HTTP_HOST'] . $sub_dir;
                
                $stmt = $pdo->prepare("UPDATE {$db_prefix}genel_ayarlar SET deger = ? WHERE anahtar = 'site_adi'");
                $stmt->execute([$_SESSION['site_name']]);
                
                $stmt = $pdo->prepare("UPDATE {$db_prefix}genel_ayarlar SET deger = ? WHERE anahtar = 'www_site'");
                $stmt->execute([$site_url]);

                // 3. Create Admin User
                $hashed_pass = password_hash($_SESSION['admin_pass'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE {$db_prefix}uyeler SET email = ?, k_adi = ?, sifre = ?, adi = 'Yönetici' WHERE no = 2");
                $stmt->execute([$_SESSION['admin_email'], $_SESSION['admin_email'], $hashed_pass]);

                if ($db_type === 'sqlite' && $pdo->inTransaction()) {
                    $pdo->commit();
                }

                // 4. Write .env
                $encryption_key = bin2hex(random_bytes(32));
                $env_content = "S4_AES_ENCRYPTION_KEY=$encryption_key\n"
                             . "DB_KULLANICI={$_SESSION['db_user']}\n"
                             . "DB_SIFRE={$_SESSION['db_pass']}\n\n"
                             . "# ─── Mail Ayarları (Fallback) ──────────\n"
                             . "# NOT: Mail ayarları öncelikle Admin Paneli > Genel Ayarlar üzerinden yönetilir.\n"
                             . "SMTP_SIFRE=\n"
                             . "SMTP_AWS_K_ADI=\n"
                             . "SMTP_AWS_SIFRE=\n\n"
                             . "MICROSOFT_TRANSLATOR_KEY=\n"
                             . "MICROSOFT_TRANSLATOR_REGION=\n"
                             . "DEEPL_API_KEY=\n"
                             . "APP_ENV=production\n"
                             . "UPDATE_PATH=\n\n"
                             . "# ─── PREMIUM / LICENSE OPTIONS ───────────────────\n"
                             . "LC_REMOTE_URL=\n"
                             . "# Ajans lisans API Anahtarı\n"
                             . "LC_API_KEY=\n"
                             . "# İmza Doğrulama (Verify) Anahtarı\n"
                             . "LC_VERIFY_KEY=\n";
                file_put_contents($root_dir . '/.env', $env_content);

                // 5. Write rlv_.php
                $sample_content = file_get_contents($root_dir . '/rlv_.sample.php');
                $replacements = [
                    "define ('DOMAIN', 'localhost')" => "define ('DOMAIN', '" . $_SERVER['HTTP_HOST'] . "')",
                    "define ('SUB_DIR', '')" => "define ('SUB_DIR', '$sub_dir')",
                    "define ('DB_TYPE', 'mysql')" => "define ('DB_TYPE', '$db_type')",
                    "define ('DB_HOST', 'localhost')" => "define ('DB_HOST', '{$_SESSION['db_host']}')",
                    "define ('VERITABANI', 'websistem')" => "define ('VERITABANI', '$db_name')",
                    "define ('DB_PREFIX', 'r_')" => "define ('DB_PREFIX', '$db_prefix')",
                    "define ('HTTPS', 'http')" => "define ('HTTPS', '$proto')"
                ];
                file_put_contents($root_dir . '/rlv_.php', strtr($sample_content, $replacements));

                // 6. Write .htaccess (Recommended for all, required for Apache)
                // Always write .htaccess as a fallback (some setups like Nginx + Apache/Litespeed support it)
                $ht = "RewriteEngine On\n\n";
                $ht .= "<FilesMatch \"^\\.\">\n    Require all denied\n</FilesMatch>\n\n";
                $ht .= "<FilesMatch \"\\.sql$\">\n    Require all denied\n</FilesMatch>\n\n";
                $ht .= "# Block . directories, allow acme-challenge\n"; 
                $ht .= "RewriteRule ^\\.well-known/acme-challenge - [L]\n";
                $ht .= "RewriteRule \"(^|/)\\.\" - [F]\n\n";
                $ht .= "RewriteRule ^php/sqlite_data/.*$ - [F,L]\n\n";

                $ht .= "<FilesMatch \"^(composer\\.(json|lock)|package\\.json)$\">\n";
                $ht .= "Require all denied\n";
                $ht .= "</FilesMatch>\n\n";

                $ht .= "<FilesMatch \"\\.(env|ini|log|conf|sql|bak|sh|yaml|yml)$\">\n";
                $ht .= "Require all denied\n";
                $ht .= "</FilesMatch>\n\n";

                $ht .= "RewriteBase $sub_dir_slash\n";
                $ht .= "ErrorDocument 404 {$sub_dir_slash}404.html\n\n";

                // HTTPS Redirect
                if ($_SESSION['use_https']) {
                    $ht .= "RewriteCond %{HTTPS} off\nRewriteRule .* https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]\n\n";
                } else if ($_SERVER['HTTP_HOST'] === 'localhost') {
                     // Local environment usually prefers http
                     $ht .= "RewriteCond %{HTTPS} on\nRewriteRule .* http://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]\n\n";
                }

                // WWW Redirect
                if ($_SESSION['www_pref'] === 'www') {
                    $ht .= "RewriteCond %{HTTP_HOST} !^www\\. [NC]\nRewriteRule ^(.*)$ http" . ($_SESSION['use_https'] ? 's' : '') . "://www.%{HTTP_HOST}/$1 [R=301,L]\n\n";
                } else if ($_SESSION['www_pref'] === 'non-www') {
                    $ht .= "RewriteCond %{HTTP_HOST} ^www\\.(.*)$ [NC]\nRewriteRule ^(.*)$ http" . ($_SESSION['use_https'] ? 's' : '') . "://%1/$1 [R=301,L]\n\n";
                }

                $ht .= "RewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\n";
                $ht .= "RewriteRule ^([^/]+)/?([^/]*)$ index.php?$1=$2 [QSA,L]\n\n";
                $ht .= "AddDefaultCharset UTF-8\n";
                file_put_contents($root_dir . '/.htaccess', $ht);

                $nginx_conf = "";
                if ($_SESSION['server_type'] === 'nginx') {
                    $nginx_conf .= "location / {\n";
                    $nginx_conf .= "    try_files \$uri \$uri/ /index.php?\$args;\n";
                    $nginx_conf .= "}\n\n";
                    
                    $nginx_conf .= "location ~ /\\.(?!well-known) {\n";
                    $nginx_conf .= "    deny all;\n";
                    $nginx_conf .= "}\n\n";
                    
                    $nginx_conf .= "location ~* \\.(env|ini|log|conf|sql|bak|sh|yaml|yml)$ {\n";
                    $nginx_conf .= "    deny all;\n";
                    $nginx_conf .= "}\n\n";
                    
                    $nginx_conf .= "location ^~ /php/sqlite_data/ {\n";
                    $nginx_conf .= "    deny all;\n";
                    $nginx_conf .= "}\n\n";
                    
                    if ($_SESSION['use_https']) {
                        $nginx_conf .= "# Enforce HTTPS\n";
                        $nginx_conf .= "if (\$scheme != \"https\") {\n";
                        $nginx_conf .= "    return 301 https://\$host\$request_uri;\n";
                        $nginx_conf .= "}\n\n";
                    }
                    
                    if ($_SESSION['www_pref'] === 'www') {
                        $nginx_conf .= "# Force WWW\n";
                        $nginx_conf .= "if (\$host !~* ^www\\.) {\n";
                        $nginx_conf .= "    return 301 \$scheme://www.\$host\$request_uri;\n";
                        $nginx_conf .= "}\n\n";
                    } else if ($_SESSION['www_pref'] === 'non-www') {
                        $nginx_conf .= "# Force Non-WWW\n";
                        $nginx_conf .= "if (\$host ~* ^www\\.(.*)) {\n";
                        $nginx_conf .= "    set \$non_www \$1;\n";
                        $nginx_conf .= "    return 301 \$scheme://\$non_www\$request_uri;\n";
                        $nginx_conf .= "}\n\n";
                    }
                }

                // Create installed file to prevent reinstallation
                $install_info = [
                    'installed_at' => date('Y-m-d H:i:s'),
                    'db_type' => $_SESSION['db_type'],
                    'site_url' => $site_url
                ];
                file_put_contents(__DIR__ . '/installed', json_encode($install_info, JSON_PRETTY_PRINT));

                // Save session data before destroying
                $saved_session = [
                    'use_https' => $_SESSION['use_https'] ?? 0,
                    'sub_dir' => $_SESSION['sub_dir'] ?? '',
                    'nginx_conf' => $nginx_conf ?? ''
                ];

                session_destroy();
                
                // Restore needed data for step 5
                session_start();
                $_SESSION['install_complete'] = $saved_session;
                $success = t('install_finished');
                goStep(5);
            } catch (Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = t('install_error') . $e->getMessage();
            }
            break;
    }
}

// UI Rendering
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('title') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="./install.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>

<div class="install-container">
    <div class="install-card">
        <div class="install-hdr">
            <h1>Websistem v1</h1>
            <p><?= t('subtitle') ?></p>
        </div>

        <div class="install-body">
            <div class="step-dots">
                <div class="dot <?= $step >= 1 ? ($step > 1 ? 'done' : 'active') : '' ?>"></div>
                <div class="dot <?= $step >= 2 ? ($step > 2 ? 'done' : 'active') : '' ?>"></div>
                <div class="dot <?= $step >= 3 ? ($step > 3 ? 'done' : 'active') : '' ?>"></div>
                <div class="dot <?= $step >= 4 ? ($step > 4 ? 'done' : 'active') : '' ?>"></div>
                <div class="dot <?= $step >= 5 ? ($step > 5 ? 'done' : 'active') : '' ?>"></div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <h3 class="mb-4"><?= t('requirements') ?></h3>
                <div class="mb-5">
                    <?php 
                    $reqs = [
                        t('php_version') => [version_compare(PHP_VERSION, '8.0.0', '>='), PHP_VERSION],
                        t('pdo_extension') => [extension_loaded('pdo'), ''],
                        t('writable_php') => [is_writable($root_dir . '/php'), ''],
                        t('writable_root') => [is_writable($root_dir), ''],
                        t('sql_files') => [file_exists(__DIR__ . '/websistem.sql'), '']
                    ];
                    $all_ok = true;
                    foreach($reqs as $label => $data): 
                        if (!$data[0]) $all_ok = false;
                    ?>
                        <div class="req-item">
                            <span><?= $label ?> <small class="text-muted"><?= $data[1] ?></small></span>
                            <span class="status-icon">
                                <?= $data[0] ? '<i class="fa-solid fa-circle-check status-ok"></i>' : '<i class="fa-solid fa-circle-xmark status-fail"></i>' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form method="post">
                    <button type="submit" class="btn btn-primary w-100" <?= !$all_ok ? 'disabled' : '' ?>>
                        <?= t('start') ?> <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

            <?php elseif ($step === 2): ?>
                <h3 class="mb-4"><?= t('db_settings') ?></h3>
                <form method="post" id="dbForm">
                    <div class="flx gap-4 mb-4 db-row">
                        <div class="db-col">
                            <div class="db-card active" data-type="mysql">
                                <i class="fa-solid fa-database"></i>
                                <strong>MySQL</strong>
                                <input type="radio" name="db_type" value="mysql" class="d-none" checked>
                            </div>
                        </div>
                        <div class="db-col">
                            <div class="db-card" data-type="sqlite">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                                <strong>SQLite</strong>
                                <input type="radio" name="db_type" value="sqlite" class="d-none">
                            </div>
                        </div>
                    </div>

                    <div id="mysql_fields">
                        <div class="form-group">
                            <label class="form-label"><?= t('db_host') ?> <small class="text-muted">(<?= t('db_host_desc') ?>)</small></label>
                            <input type="text" name="db_host" class="inp" value="localhost">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= t('db_name') ?></label>
                            <input type="text" name="db_name" id="db_name_input" class="inp" placeholder="<?= t('placeholder_db') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= t('db_user_pass') ?> <small class="text-muted">(<?= t('db_user_pass_desc') ?>)</small></label>
                            <div class="flx gap-2">
                                <div style="position: relative; flex: 1;">
                                    <input type="text" name="db_user" class="inp" placeholder="root">
                                </div>
                                <div style="position: relative; flex: 1;">
                                    <input type="password" name="db_pass" id="db_pass" class="inp" placeholder="<?= t('placeholder_pass') ?>">
                                    <i class="fa-solid fa-eye toggle-password" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #64748b;" data-target="db_pass"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="sqlite_fields" class="d-none">
                        <div class="form-group">
                            <label class="form-label"><?= t('sqlite_file') ?></label>
                            <div class="flx aic gap-2">
                                <input type="text" name="db_name_sqlite" class="inp" value="websistem">
                                <span>.sqlite</span>
                            </div>
                        </div>
                    </div>

                    <div class="adv-toggle mb-2"><i class="fa-solid fa-gears"></i> <?= t('advanced_settings') ?></div>
                    <div class="adv-content mb-4">
                        <div class="form-group">
                            <label class="form-label"><?= t('server_software') ?></label>
                            <select name="server_type" class="sel">
                                <option value="apache"><?= t('apache_desc') ?></option>
                                <option value="nginx"><?= t('nginx_desc') ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="flx aic gap-2">
                                <input type="checkbox" name="use_https" id="use_https" checked>
                                <label for="use_https" style="margin:0; font-weight:500;"><?= t('https_redirect') ?></label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= t('www_pref') ?></label>
                            <select name="www_pref" class="sel">
                                <option value="none"><?= t('none') ?></option>
                                <option value="www"><?= t('force_www') ?></option>
                                <option value="non-www"><?= t('force_non_www') ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= t('sub_dir') ?></label>
                            <?php 
                            $script_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
                            $detected_sub = str_replace('/install/index.php', '', $script_path);
                            ?>
                            <input type="text" name="sub_dir" class="inp" value="<?= $detected_sub ?>">
                            <small class="text-muted">Örn: <?= $detected_sub ?: '/websistem' ?> (<?= t('root_dir_msg') ?>)</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= t('db_prefix') ?></label>
                            <input type="text" name="db_prefix" class="inp" value="r_">
                        </div>
                    </div>

                    <div class="flx gap-2">
                        <a href="?step=1" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i></a>
                        <button type="submit" class="btn btn-primary w-100"><?= t('test_conn') ?></button>
                    </div>
                </form>

                <script>
                    $('.db-card').click(function() {
                        $('.db-card').removeClass('active');
                        $(this).addClass('active').find('input').prop('checked', true);
                        if ($(this).data('type') === 'sqlite') { 
                            $('#mysql_fields').addClass('d-none'); 
                            $('#sqlite_fields').removeClass('d-none'); 
                            $('input[name="db_name"]').val($('input[name="db_name_sqlite"]').val()).removeAttr('required');
                        } else { 
                            $('#sqlite_fields').addClass('d-none'); 
                            $('#mysql_fields').removeClass('d-none'); 
                            $('#db_name_input').attr('required', 'required');
                        }
                    });
                    $('.adv-toggle').click(function() { $('.adv-content').slideToggle(); $(this).find('i').toggleClass('fa-spin-slow'); });
                    $('input[name="db_name_sqlite"]').on('input', function() { if ($('input[name="db_type"]:checked').val() === 'sqlite') $('input[name="db_name"]').val($(this).val()); });
                    
                    // Form submit validation
                    $('#dbForm').on('submit', function(e) {
                        var dbType = $('input[name="db_type"]:checked').val();
                        var dbName = $('input[name="db_name"]').val().trim();
                        
                        if (dbType === 'mysql' && dbName === '') {
                            e.preventDefault();
                            alert('MySQL için veritabanı adı zorunludur.');
                            $('#db_name_input').focus();
                            return false;
                        }
                    });
                </script>

            <?php elseif ($step === 3): ?>
                <h3 class="mb-4"><?= t('admin_settings') ?></h3>
                <form method="post">
                    <div class="form-group">
                        <label class="form-label"><?= t('site_name') ?></label>
                        <input type="text" name="site_name" class="inp" value="Websistem v1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= t('admin_email') ?></label>
                        <input type="email" name="admin_email" class="inp" placeholder="<?= t('placeholder_email') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= t('admin_pass') ?></label>
                        <div style="position: relative;">
                            <input type="password" name="admin_pass" id="admin_pass" class="inp" placeholder="<?= t('placeholder_admin_pass') ?>" required>
                            <i class="fa-solid fa-eye toggle-password" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #64748b;" data-target="admin_pass"></i>
                        </div>
                    </div>
                    <div class="flx gap-2">
                        <a href="?step=2" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i></a>
                        <button type="submit" class="btn btn-primary w-100"><?= t('confirm') ?> <i class="fa-solid fa-arrow-right"></i></button>
                    </div>
                </form>

            <?php elseif ($step === 4): ?>
                <h3 class="mb-4"><?= t('ready_to_install') ?></h3>
                <div class="mb-5 p-4" style="background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0;">
                    <table class="summary-table">
                        <tr><td class="summary-label"><?= t('db_type') ?></td><td class="summary-val"><?= strtoupper($_SESSION['db_type']) ?> (<?= $_SESSION['db_name'] ?>)</td></tr>
                        <tr><td class="summary-label"><?= t('protocol') ?></td><td class="summary-val"><?= $_SESSION['use_https'] ? 'HTTPS' : 'HTTP' ?></td></tr>
                        <tr><td class="summary-label"><?= t('server') ?></td><td class="summary-val"><?= ucfirst($_SESSION['server_type']) ?></td></tr>
                        <tr><td class="summary-label"><?= t('sub_dir') ?>:</td><td class="summary-val"><?= $_SESSION['sub_dir'] ?: '(Root)' ?></td></tr>
                    </table>
                </div>
                <form method="post">
                    <div class="alert alert-warning">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span><?= t('warning_db') ?></span>
                    </div>
                    <div class="flx gap-2">
                        <a href="?step=3" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i></a>
                        <button type="submit" class="btn btn-primary w-100"><?= t('finish_install') ?> <i class="fa-solid fa-flag-checkered"></i></button>
                    </div>
                </form>

            <?php elseif ($step === 5): ?>
                <?php
                // Get saved session data
                $install_data = $_SESSION['install_complete'] ?? [];
                $use_https = $install_data['use_https'] ?? 0;
                $sub_dir = $install_data['sub_dir'] ?? '';
                
                $protocol = $use_https ? 'https' : 'http';
                $site_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . $sub_dir;
                
                // Create installed file if it doesn't exist
                if (!file_exists($installed_file)) {
                    $install_info = [
                        'installed_at' => date('Y-m-d H:i:s'),
                        'site_url' => $site_url
                    ];
                    file_put_contents($installed_file, json_encode($install_info, JSON_PRETTY_PRINT));
                }
                ?>
                <div class="text-center">
                    <i class="fa-solid fa-circle-check fa-4x uis-text-s mb-4" style="color:var(--success);"></i>
                    <h3 class="mb-2"><?= t('congrats') ?></h3>
                    <p class="text-muted mb-4"><?= t('install_success') ?></p>
                    
                    <div class="alert alert-success" style="text-align: left;">
                        <i class="fa-solid fa-check-circle"></i>
                        <span><?= t('install_complete') ?></span>
                    </div>

                    <a href="<?= $site_url ?>/uis/uyegiris" class="btn btn-primary w-100"><?= t('go_to_site') ?> <i class="fa-solid fa-right-to-bracket"></i></a>

                    <?php if (!empty($install_data['nginx_conf'])): ?>
                        <div class="mt-5" style="text-align: left;">
                            <h4 class="mb-2" style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-server" style="color: #6366f1;"></i>
                                <?= t('nginx_config_title') ?>
                            </h4>
                            <p class="text-muted small mb-3"><?= t('nginx_config_desc') ?></p>
                            
                            <div class="alert alert-info py-2 mb-3" style="font-size: 0.85rem; border: none; background: #e0f2fe; color: #0369a1;">
                                <i class="fa-solid fa-circle-info"></i>
                                <?= t('nginx_htaccess_note') ?>
                            </div>
                            
                            <div style="position: relative; background: #0f172a; border-radius: 12px; padding: 20px; border: 1px solid #1e293b; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                                <pre id="nginxCode" style="margin: 0; color: #e2e8f0; font-family: 'Consolas', 'Monaco', 'Andale Mono', 'Ubuntu Mono', monospace; font-size: 13px; line-height: 1.6; overflow-x: auto;"><?= htmlspecialchars($install_data['nginx_conf']) ?></pre>
                                <button onclick="copyNginxConfig()" class="btn btn-sm" style="position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(4px);">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                            
                            <script>
                            function copyNginxConfig() {
                                const code = document.getElementById('nginxCode').innerText;
                                navigator.clipboard.writeText(code).then(() => {
                                    const btn = event.currentTarget;
                                    const icon = btn.querySelector('i');
                                    icon.className = 'fa-solid fa-check';
                                    setTimeout(() => { icon.className = 'fa-regular fa-copy'; }, 2000);
                                });
                            }
                            </script>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Şifre göz butonu
$(document).on('click', '.toggle-password', function() {
    const targetId = $(this).data('target');
    const input = $('#' + targetId);
    const icon = $(this);
    
    if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        input.attr('type', 'password');
        icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
});
</script>

</body>
</html>
