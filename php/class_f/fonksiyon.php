<?php

function z($input): string {
    if (!is_string($input)) {
        return '';
    }

    $result = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
    $result = preg_replace('/<(script|iframe|object|embed|applet|meta|link|style|base|form)[^>]*>.*?<\/\1>/is', '', $result);
    $result = preg_replace('/<(script|iframe|object|embed|applet|meta|link|style|base|form)[^>]*>/is', '', $result);
    $result = preg_replace('/\son[a-z0-9-]*\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', ' ', $result);
    $result = preg_replace('/javascript\s*:/i', '', $result);
    $result = preg_replace('/data\s*:[^;>\s]+;base64[,]/i', 'data-blocked,', $result);
    // Clean style attribute
    $result = preg_replace_callback('/style\s*=\s*([\'"])(.*?)\1/i', function($m) {
        $style = $m[2];
        $style = preg_replace('/(expression|javascript|behavior|vbscript)\b/i', 'blocked', $style);
        return 'style="' . $style . '"';
    }, $result);

    // 2. Entity Normalization and Character Escaping
    $result = preg_replace_callback('/&#(\d+);?/', function($m) {
        return mb_convert_encoding('&#' . intval($m[1]) . ';', 'UTF-8', 'HTML-ENTITIES');
    }, $result);

    $result = preg_replace_callback('/&#x([a-f0-9]+);?/i', function($m) {
        return mb_convert_encoding('&#x' . $m[1] . ';', 'UTF-8', 'HTML-ENTITIES');
    }, $result);

    $sanitize_array = [
        '<?' => '&#60;&#63;',
        '?>' => '&#63;&#62;',
        "'"  => '&#39;',
        '"'  => '&quot;',
        '\\' => '&#92;',
        '{'  => '&#123;',
        '}'  => '&#125;',
        '['  => '&#91;',
        ']'  => '&#93;',
        '`'  => '&#96;',
    ];
    $result = strtr($result, $sanitize_array);

    $result = trim($result);
    $result = preg_replace('/\s+/', ' ', $result);

    return $result;
}


function q_($kelime){
	return trim(strtr($kelime, ['\''=>'', '\\'=>'', '"'=>'']));
}

function q2_($kelime) {
    return str_replace(['<?', "'", "\\"], '', $kelime);
}

function html_d($yazi) {
	if (trim($yazi) === '') {
		return '';
	}

	$yazi = xss_sil($yazi);

	$html_degistir = [
		'<?' => '&#60;&#63;',
		'?>' => '&#63;&#62;',
		'\\' => '&#92;',
		'\'' => '&#39;',
		'`'  => '&#96;',
	];

	$yazi = strtr($yazi, $html_degistir);

	return trim($yazi);
}

function html_a($yazi){
	return xss_sil($yazi ?? '');
}

function hs(?string $string, int $flags = ENT_QUOTES | ENT_SUBSTITUTE, string $encoding = 'UTF-8'): string {
	return htmlspecialchars($string ?? '', $flags, $encoding);
}
function z_js($s) {
	return json_encode($s, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}
function z_url($s) {
	return rawurlencode($s ?? '');
}

function tum_harf_buyut(?string $yazi): string {
	return mb_strtoupper($yazi ?? '', 'UTF-8');
}

function nd_md5($n) {
	return substr(md5($n), 0, 3);
}

function k_b ($k){
	return strtr($k ?? '', ['/k/' => '/b/']);
}

function dosya_fe($dosya){
	return file_exists($dosya ?? '');
}

function sanitize_php_tag($c) {
	return preg_replace(['/\<\?(?:php)?/i', '/\?>/'], ['', '&quest;'], $c);
}

function url_duzenle($url) {
	$url = trim($url);
	$url = mb_strtolower($url, 'UTF-8');
	if (function_exists('iconv')) {
		$url = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $url);
	}
	$url = str_replace(
		['ı','İ','ğ','Ğ','ü','Ü','ş','Ş','ö','Ö','ç','Ç'],
		['i','i','g','g','u','u','s','s','o','o','c','c'],
		$url
	);
	$url = preg_replace('/[^a-z0-9]+/', '-', $url);
	$url = preg_replace('/-+/', '-', $url);
	return trim($url, '-');
}


function url_kayit_kontrol_degistir(PDO $pdo, $tablo, string $url) {
	$original_url = $url;
	$tablolar = is_array($tablo) ? $tablo : [$tablo];
	
	for ($i = 1; $i < 8; $i++) {
		$bilesen_url_var = (defined('BILESEN_DIR') && is_dir(BILESEN_DIR . "/$url"));
		$url_var = false;
		
		foreach ($tablolar as $tek_tablo) {
			$sql = "SELECT COUNT(url) FROM {$tek_tablo} WHERE url = :url";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':url', $url, PDO::PARAM_STR);
			$stmt->execute();
			
			if ($stmt->fetchColumn() > 0) {
				$url_var = true;
				break;
			}
		}
		
		if ($url_var || $bilesen_url_var) {
			$url = $original_url . $i;
		} else {
			break;
		}
	}	
	return $url;
}


function dosya_sayisi(?string $dizin, ?string $desen = null): int|string {
    if (empty($dizin) || !is_dir($dizin) || !is_readable($dizin)) {
        return 0;
    }	
    try {
        $sayi = 0;
        $iterator = new DirectoryIterator($dizin);
        
        foreach ($iterator as $dosya) {
            if ($dosya->isDot()) {
                continue;
            }
            
            if ($dosya->isFile()) {
                if ($desen === null || preg_match($desen, $dosya->getFilename())) {
                    $sayi++;
                }
            }
        }
        
        return $sayi > 0 ? $sayi : '';
        
    } catch (Exception $e) {
        error_log("dosya_sayisi() hatası: " . $e->getMessage());
        return 0;
    }
}

function dosya_yaz(string $dosya, string $icerik, int $chmod = 0755): bool {
	$yazildi = @file_put_contents($dosya, $icerik);
	if ($yazildi === false) {
		return false;
	}
	if (!@chmod($dosya, $chmod)) {
		return false;
	}
	return true;
}


function dizin_listesi(string $dizin, array $exclude = []) {
    if (!is_dir($dizin)) {
        return false;
    }

    $contents = scandir($dizin);
    $dizinler = [];

    foreach ($contents as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        if (is_excluded($item, $exclude)) {
            continue;
        }

        if (is_dir($dizin . '/' . $item)) {
            $dizinler[] = $item;
        }
    }

    return $dizinler;
}

function is_excluded(string $item, array $exclude): bool {
    foreach ($exclude as $pattern) {
        if (preg_match('/^(.)[^\\1]*\\1[gimsuy]*$/', $pattern) && @preg_match($pattern, '') !== false) {
            if (preg_match($pattern, $item)) {
                return true;
            }
        } elseif (str_contains($pattern, '*') || str_contains($pattern, '?') || str_contains($pattern, '[')) {
            if (fnmatch($pattern, $item)) {
                return true;
            }
        } elseif ($pattern === $item) {
            return true;
        }
    }
    return false;
}



function dosya_listesi($dizin, $excludeFiles = []) {
	if (is_dir($dizin)) {
		$contents = scandir($dizin);
		$dosyalar = [];
		foreach ($contents as $item) {
			$file_path = $dizin . '/' . $item;
			if (is_file($file_path) && !in_array($item, $excludeFiles)) {
				$dosyalar[] = $item;
			}
		}
		return $dosyalar;
	} else {
		return false;
	}
}

function dosya_listele($dizin, $options = []) {
    if (!is_dir($dizin)) return false;
    
    $defaults = [
        'exclude' => [],
        'include' => [],
        'exclude_hidden' => true,
        'recursive' => false,
        'full_path' => false
    ];

    if (!is_array($options) || isset($options[0])) {
        $options = ['exclude' => (array)$options];
    }
    
    $options = array_merge($defaults, $options);
    
    $dosyalar = [];
    
    $scan_dir = function($current_dir, $relative_path = '') use (&$scan_dir, $options, &$dosyalar) {
        $contents = scandir($current_dir);
        
        foreach ($contents as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $file_path = $current_dir . '/' . $item;
            $relative_file = $relative_path ? $relative_path . '/' . $item : $item;
            
            if (is_dir($file_path) && $options['recursive']) {
                $scan_dir($file_path, $relative_file);
                continue;
            }
            
            if (!is_file($file_path)) continue;
            
            if ($options['exclude_hidden'] && $item[0] === '.') {
                continue;
            }
            
            if (!empty($options['include'])) {
                $included = false;
                foreach ($options['include'] as $pattern) {
                    if (fnmatch($pattern, $item) || fnmatch($pattern, $relative_file)) {
                        $included = true;
                        break;
                    }
                }
                if (!$included) continue;
            }
            
            $excluded = false;
            foreach ($options['exclude'] as $pattern) {
                if (fnmatch($pattern, $item) || fnmatch($pattern, $relative_file)) {
                    $excluded = true;
                    break;
                }
            }
            if ($excluded) continue;
            
            $dosyalar[] = $options['full_path'] ? $file_path : ($options['recursive'] ? $relative_file : $item);
        }
    };
    
    $scan_dir($dizin);
    
    return $dosyalar;
}

function dosya_sil($dosya_yolu) {
	if (is_file($dosya_yolu)) {
		return unlink($dosya_yolu);
	}
	return false;
}

function dizin_sil($dizin) {
	if (!is_dir($dizin)) {
		return false;
	}
	$dizin = rtrim($dizin, '/\\');
	$dosyalar = scandir($dizin);
	foreach ($dosyalar as $dosya) {
		if ($dosya === '.' || $dosya === '..') continue;
		$tamYol = $dizin . DIRECTORY_SEPARATOR . $dosya;
		if (is_link($tamYol)) {
			unlink($tamYol);
		} elseif (is_dir($tamYol)) {
			if (!dizin_sil($tamYol)) {
				return false;
			}
		} elseif (file_exists($tamYol)) {
			if (!unlink($tamYol)) {
				return false;
			}
		}
	}
	return rmdir($dizin);
}

function dizinleri_getir($klasorYolu) {
	$dizinler = [];
	if (is_dir($klasorYolu)) {
		$dizin = opendir($klasorYolu);
		while (($dizinAdi = readdir($dizin)) !== false) {
			$dizinYolu = $klasorYolu . '/' . $dizinAdi;
			if ($dizinAdi != '.' && $dizinAdi != '..' && is_dir($dizinYolu)) {
				$dizinler[] = $dizinAdi;
			}
		}

		closedir($dizin);
	}
	return $dizinler;
}

function recursiveCopy($source, $dest) {
    if (is_dir($source)) {
        if (!is_dir($dest)) mkdir($dest, 0755, true);
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                recursiveCopy("$source/$file", "$dest/$file");
            }
        }
    } elseif (is_file($source)) {
        copy($source, $dest);
    }
}

function rrmdir($dir) {
	if (!is_dir($dir)) return;
	foreach (scandir($dir) as $file) {
		if ($file === '.' || $file === '..') continue;
		$path = $dir . '/' . $file;
		is_dir($path) ? rrmdir($path) : unlink($path);
	}
	rmdir($dir);
}

function ucfirst_utf8($string, $lowerRest = false) {
	if ($lowerRest) {
		$string = mb_strtolower($string);
	}
	return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
}

function harfBuyut(string $metin): string {
    return mb_strtoupper($metin, 'UTF-8');
}

function ilk_harfleri_yaz(?string $metin): string {
	if (trim($metin ?? '') === '') {
		return '';
	}
	$kelimeler = explode(' ', $metin);
	$ilkHarfler = array_map(fn($kelime) => mb_substr($kelime, 0, 1), $kelimeler);
	return implode('.', $ilkHarfler);
}



function html_xss_phptag_sil(string $html): string {
	$html = str_replace(
		['<?', '?>', '<?='], 
		['&#60;&#63;', '&#63;&#62;', '&#60;&#63;='], 
		$html
	);
	// HTML ve JS için temel XSS temizliği
	return htmlspecialchars($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function random_string($length = 8) {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$str = '';
	$max = strlen($chars) - 1;
	for ($i = 0; $i < $length; $i++) {
		$str .= $chars[random_int(0, $max)];
	}
	return $str;
}

function sifrele($kelime){return base64_encode(base64_encode(base64_encode(base64_encode(trim($kelime ?? '')))));}
function sifre_ac($kelime){return base64_decode(base64_decode(base64_decode(base64_decode(trim($kelime ?? '')))));}
function sifrele2($kelime) {
	$sifreli_veri = sifrele($kelime);
	$eklenecek_karakterler = 'xzpqrst';
	$yeni_veri = '';
	$sifreli_veri_uzunluk = strlen($sifreli_veri);
	$ekleme_araligi = rand(3, 7);
	$ekleme_sayaci = 0;

	for ($i = 0; $i < $sifreli_veri_uzunluk; $i++) {
		$yeni_veri .= $sifreli_veri[$i];
		$ekleme_sayaci++;

		if ($ekleme_sayaci % $ekleme_araligi === 0) {
			$yeni_veri .= $eklenecek_karakterler[rand(0, strlen($eklenecek_karakterler) - 1)];
		}
	}
	return $yeni_veri . '.' . $ekleme_araligi . '.' . $eklenecek_karakterler;
}

function sifre_ac2($kelime) {
	$parcalar = explode('.', $kelime);
	if (count($parcalar) !== 3) {
		return false;
	}
	$sifreli_veri_parca = $parcalar[0];
	$ekleme_araligi = (int)$parcalar[1];
	$eklenecek_karakterler = $parcalar[2];

	$gercek_veri = '';
	$ekleme_sayaci = 0;

	for ($i = 0; $i < strlen($sifreli_veri_parca); $i++) {
		if (($ekleme_sayaci % $ekleme_araligi === 0) && strpos($eklenecek_karakterler, $sifreli_veri_parca[$i]) !== false) {
		} else {
			$gercek_veri .= $sifreli_veri_parca[$i];
		}
		$ekleme_sayaci++;
	}

	return sifre_ac($gercek_veri);
}
function sifrele3 ($kelime) {
	return rand(1000,9999) . strrev(sifrele($kelime)) . rand(1000,9999) . md5($kelime);
}
function sifre_ac3 ($kelime) {
	$orjinal_sifre = strrev(substr(substr($kelime, 0, -32), 4, -4));
	$md5_veri = substr($kelime, -32);
	$cozulen_veri = sifre_ac($orjinal_sifre);
	if (md5($cozulen_veri) === $md5_veri) {
		return $cozulen_veri;
	} else {
		return false;
	}
}
function getAesKey(): string {
    static $key = null;
    if ($key !== null) {
        return $key;
    }
    $envKey = $_ENV['S4_AES_ENCRYPTION_KEY'] ?? false;
    if ($envKey !== false && $envKey !== '') {
        return $key = $envKey;
    }
    return $key = random_bytes(32);
}

function sifrele4($veri, $anahtar = null) {
    if ($anahtar === null) {
        $anahtar = getAesKey();
    }
    
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $sifrelenmis_veri = openssl_encrypt(strval($veri), 'aes-256-cbc', $anahtar, 0, $iv);
    $base64_sifre = base64_encode($sifrelenmis_veri . '::' . $iv);
    $url_safe_sifre = str_replace(['+', '/'], ['-', '_'], $base64_sifre);
    return rtrim($url_safe_sifre, '=');
}

function sifre_ac4($sifreli_veri, $anahtar = null) {
    if ($anahtar === null) {
        $anahtar = getAesKey();
    }
    
    $base64_sifre = str_replace(['-', '_'], ['+', '/'], $sifreli_veri);
    $padding = strlen($base64_sifre) % 4;
    if ($padding > 0) {
        $base64_sifre .= str_repeat('=', 4 - $padding);
    }

    $decoded = base64_decode($base64_sifre, true);
    if ($decoded === false) {
        return false;
    }

    $parts = explode('::', $decoded, 2);
    if (count($parts) !== 2) {
        return false;
    }

    list($sifrelenmis_veri, $iv) = $parts;

    $iv_length = openssl_cipher_iv_length('aes-256-cbc');

    if (strlen($iv) > $iv_length) {
        $iv = substr($iv, 0, $iv_length);
    } elseif (strlen($iv) < $iv_length) {
        $iv = str_pad($iv, $iv_length, "\0");
    }

    return openssl_decrypt($sifrelenmis_veri, 'aes-256-cbc', $anahtar, 0, $iv);
}


function syetki($yetkiler){
	if ($yetkiler==='00') return true;
	$yetkiler = is_array($yetkiler) ? $yetkiler : explode(',', $yetkiler ?? '');
	return (isset($_SESSION['kullanici_yetki_no']) && in_array($_SESSION['kullanici_yetki_no'], $yetkiler));
}

function sy2($yazi, $syetki = [2]){
	return (syetki($syetki)) ? $yazi : '';
}

function set_dolu(mixed $deger, string $kaynaklar = ''): bool {
	if ($kaynaklar === '') {
		return isset($deger) && !empty($deger);
	}
	foreach (str_split($kaynaklar) as $kaynak) {
		$dolumu = match($kaynak) {
			'p' => isset($_POST[$deger]) && !empty($_POST[$deger]),
			'g' => isset($_GET[$deger]) && !empty($_GET[$deger]),
			's' => isset($_SESSION[$deger]) && !empty($_SESSION[$deger]),
			'c' => isset($_COOKIE[$deger]) && !empty($_COOKIE[$deger]),
			default => false,
		};
		if ($dolumu) {
			return true;
		}
	}

	return false;
}

function get_post_z($gp, $ilk = 'gp', $else = '') {
	if ($ilk === 'gp') {
		return set_dolu($gp,"g") ? z($_GET[$gp]) : (set_dolu($gp,"p") ? z($_POST[$gp]) : $else);
	} elseif ($ilk === 'pg'){
		return set_dolu($gp,"p") ? z($_POST[$gp]) : (set_dolu($gp,"g") ? z($_GET[$gp]) : $else);
	}
	return $else;
}

function issetgetorpost($getpost) {
	return isset($_GET[$getpost]) || isset($_POST[$getpost]);
}

function isset_numeric_post ($post, $varsayilan){
	return (isset($_POST[$post]) && is_numeric($_POST[$post])) ? intval($_POST[$post]) : $varsayilan;
}
function isset_numeric_get ($get, $varsayilan){
	return (isset($_GET[$get]) && is_numeric($_GET[$get])) ? intval($_GET[$get]) : $varsayilan;
}

/**
	* GET/POST verilerini güvenli şekilde almak, işlemek ve varsayılan değerler atamak için kullanılır.
	* 
	* Temel Kullanım:
	* $params = getpostd([
	*     'username' => ['g', 'd:guest'],                    // GET'ten al, yoksa 'guest'
	*     'page' => ['p', 'd:1', 'empty_kontrol' => false], // POST'tan al, boş string kabul et
	*     'category' => ['gp', 'd:%username']                // GET+POST'tan al, username'i referans al
	* ]);
	* 
	* Ana Parametreler:
	* - Kaynak: 'g'=GET, 'p'=POST, 'gp'=GET öncelikli, 'pg'=POST öncelikli
	* - Varsayılan: 'd:value' (sabit değer) veya 'd:%variable' (değişken referansı)
	* - Fonksiyonlar: ['z', 'trim'] (değere uygulanacak fonksiyonlar)
	* - Empty kontrol: 'empty_kontrol' => false (sadece isset kontrolü, varsayılan true)
	* 
	* Özellikler:
	* - Empty kontrol: Sadece boş string ('') empty sayılır, 0 ve false korunur
	* - Değişken referansı: d:%username ile diğer parametreleri referans alabilir
	* - Dış değişkenler: İkinci parametre ile dış scope değişkenlerini kullanabilir
	* - Güvenlik: z() gibi temizleme fonksiyonları otomatik uygulanır
	* 
	* @param array $get_post Parametre kuralları dizisi
	* @param array $external_vars Dış scope değişkenleri (opsiyonel)
	* @return array İşlenmiş parametreler dizisi
*/
function getpostd(array $get_post, array $external_vars = []): array {
	$params = [];
	foreach ($get_post as $key => $rules) {
		$value   = null;
		$default = '';
		$funcs   = [];
		$source  = '';
		$check_empty = true;
		$default_is_variable = false;
		foreach ($rules as $rule_key => $rule) {
			if (is_string($rule)) {
				if (str_starts_with($rule, 'd:')) {
					$default_value = substr($rule, 2);
					if (str_starts_with($default_value, '%')) {
						$default = substr($default_value, 1);
						$default_is_variable = true;
					} else {
						$default = $default_value;
						$default_is_variable = false;
					}
				} elseif (in_array($rule, ['gp', 'pg', 'g', 'p'], true)) {
					$source = $rule;
				}
			} elseif (is_array($rule)) {
				$funcs = $rule;
			} elseif ($rule_key === 'empty_kontrol' && is_bool($rule)) {
				$check_empty = $rule;
			}
		}
		$source_data = match ($source) {
			'gp' => $_GET + $_POST,
			'pg' => $_POST + $_GET,
			'g'  => $_GET,
			'p'  => $_POST,
			default => [],
		};
		$value = isset($source_data[$key]) ? $source_data[$key] : null;
		foreach ($funcs as $fn) {
			if (is_callable($fn)) {
				$value = $fn($value);
			}
		}
		if ($check_empty) {
			if (!isset($value) || $value === '') {
				if ($default_is_variable) {
					if (isset($params[$default])) {
						$value = $params[$default];
					} elseif (isset($external_vars[$default])) {
						$value = $external_vars[$default];
					} else {
						$value = '';
					}
				} else {
					$value = $default;
				}
			}
		} else {
			if (!isset($value)) {
				if ($default_is_variable) {
					if (isset($params[$default])) {
						$value = $params[$default];
					} elseif (isset($external_vars[$default])) {
						$value = $external_vars[$default];
					} else {
						$value = '';
					}
				} else {
					$value = $default;
				}
			}
		}
		$params[$key] = $value;
	}
	return $params;
}


function mb_str_replace($search, $replace, $subject, $encoding = 'UTF-8') {
	if (is_array($subject)) {
		foreach ($subject as &$string) {
			$string = mb_str_replace($search, $replace, $string, $encoding);
		}
		return $subject;
	}

	foreach ((array) $search as $key => $s) {
		$r = (is_array($replace) && array_key_exists($key, $replace)) ? $replace[$key] : $replace;
		$s = '/' . preg_quote($s, '/') . '/u';
		$subject = preg_replace($s, $r, $subject);
	}

	return $subject;
}

function str_replace_sil_array($yazi, $dizi){
	foreach ($dizi as $d0) {
		$yazi = str_replace($d0,'',$yazi);
	}
	return $yazi;
}

function tr_strtolower($str) {
	setlocale(LC_ALL, 'tr_TR.UTF-8');
	$lowercase = mb_strtolower($str ?? '', 'UTF-8');
	setlocale(LC_ALL, 'C');
	return $lowercase;
}

function bosluklari_sil($yazi) {
    $yazi = preg_replace('/[\t\r\n]+/', ' ', $yazi);
    $yazi = preg_replace('/ {2,}/', ' ', $yazi);
    $yazi = preg_replace('/>\s+</', '><', $yazi);
    return trim($yazi);
}

function yc($text_adi, ...$sprintf_args) {
	if ($text_adi === null) {
        return '';
    }
    return Yc::get($text_adi, ...$sprintf_args);
}


function middle_dot_yc($html, $yabanci_dil_acik = true, $exclude_get = []) {
    $__protected_map = [];

    $html = preg_replace_callback(
        '/(<(textarea|pre|code|script|style)\b[^>]*>[\s\S]*?<\/\2>)/i',
        function ($m) use (&$__protected_map) {
            $key = '___PROTECTED_BLOCK_' . count($__protected_map) . '___';
            $__protected_map[$key] = $m[1];
            return $key;
        },
        $html
    );

    $restore = function ($html) use (&$__protected_map) {
        if (!empty($__protected_map)) {
            $html = str_replace(array_keys($__protected_map), array_values($__protected_map), $html);
        }
        return $html;
    };

    $html = trim(preg_replace('/^\xEF\xBB\xBF+|[\x{200E}\x{200F}]+/u', '', $html));

    if (!empty($exclude_get)) {
        foreach ($exclude_get as $eg) {
            if (set_dolu($eg, "g")) {
                return $restore($html);
            }
        }
    }

    if ($yabanci_dil_acik === true && strpos($html, '·') !== false) {
        $html = preg_replace_callback("/·([^·]+)·/", function ($matches) {

            $content = trim($matches[1]);
            $content = preg_replace('/[\p{C}\x{200E}\x{200F}]+/u', '', $content);

            $degisken_1_sayisi = substr_count($content, '(.)');
            $degisken_2_sayisi = substr_count($content, '(..)');

            if ($degisken_2_sayisi > 1) {

                preg_match("/\(\.\)(.+)\(\.\)/is", $content, $d1m);
                preg_match("/\(\.\.\)(.+)\(\.\.\)/is", $content, $d2m);

                $degisken_1 = $d1m[1] ?? '';
                $degisken_2 = $d2m[1] ?? '';

                if ($degisken_2 === '') return yc($content);

                return yc(
                    str_replace([$d1m[0], $d2m[0]], ['%s', '%s'], $content),
                    $degisken_1,
                    $degisken_2
                );

            } elseif ($degisken_1_sayisi > 1) {

                preg_match("/\(\.\)(.+)\(\.\)/is", $content, $d1m);
                $degisken_1 = $d1m[1] ?? '';

                if ($degisken_1 === '') return yc($content);

                return yc(
                    str_replace($d1m[0], '%s', $content),
                    $degisken_1
                );

            } else {
                if (preg_match_all('/<([a-z1-6]+)\b[^>]*>.*?<\/\1>|<[a-z1-6]+\b[^>]*\/>/is', $content, $html_matches)) {
                    $tags = $html_matches[0];
                    $clean_key = preg_replace('/<([a-z1-6]+)\b[^>]*>.*?<\/\1>|<[a-z1-6]+\b[^>]*\/>/is', '%s', $content);
                    return yc($clean_key, ...$tags);
                }
                return yc($content);
            }

        }, $html);
    }

    $html = str_replace(['(.)', '(..)', '·'], '', $html);

    return $restore($html);
}



function cc($yazi, $tablo_no, $tablo, $alan) {
	global $pdo, $do_;
	$dil_no = Global_::$dil_no;
	if (empty($dil_no) || $dil_no == 0) {
		return $yazi;
	}
	
	try {
		$sql = "SELECT yazi FROM {$do_}ceviriler WHERE dil_no = :dil_no AND tablo_no = :tablo_no AND tablo = :tablo AND alan = :alan";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
			':dil_no' => $dil_no,
			':tablo_no' => $tablo_no,
			':tablo' => $tablo,
			':alan' => $alan
		]);
		$ceviri = $stmt->fetchColumn();
		return ($ceviri && !empty($ceviri)) ? $ceviri : $yazi;
	} catch (PDOException $e) {
		error_log("cc() fonksiyonu hatası: " . $e->getMessage());
		return $yazi;
	}
}

function tarayiciDili(string $varsayilan = 'tr'): string {
	if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		return $varsayilan;
	}
	$acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	$diller = [];
	foreach (explode(',', $acceptLanguage) as $parca) {
		$parca = trim($parca);
		$alt = explode(';q=', $parca);
		$dil = strtolower(substr($alt[0], 0, 2));
		$agirlik = isset($alt[1]) ? (float)$alt[1] : 1.0;
		$diller[$dil] = max($diller[$dil] ?? 0, $agirlik);
	}
	arsort($diller);
	return array_key_first($diller) ?? $varsayilan;
}


function xss_sil($input) {
	if ($input === null || $input === '') return '';
	
	$input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
	$input = preg_replace('/<(script|iframe|object|embed|applet|meta|link|style|base|form)[^>]*>.*?<\/\1>/is', '', $input);
	$input = preg_replace('/<(script|iframe|object|embed|applet|meta|link|style|base|form)[^>]*>/is', '', $input);
	
	$input = preg_replace('/\son[a-z0-9-]*\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', ' ', $input);
	$input = preg_replace('/(javascript|vbscript|expression|behavior)\s*:/i', 'blocked:', $input);
	
	$input = preg_replace_callback('/style\s*=\s*([\'"])(.*?)\1/i', function($m) {
		$style = $m[2];
		$style = preg_replace('/(expression|javascript|behavior|vbscript)\b/i', 'blocked', $style);
		return 'style="' . $style . '"';
	}, $input);
	
	$input = preg_replace('/data\s*:[^;>\s]+;base64[,]/i', 'data-blocked,', $input);
	
	return $input;
}


function rg_opendir($r_dizin_k, $r_dizin_b = '', 
					$ust_yazi = '', $en_az_resim_sayisi = 2, 
					$class = '', $a = 'href', $resimalt = ''): string|bool {

	$r_dizin_k_fs = $r_dizin_k;
	if (defined('ROOT') && !preg_match('/^(?:[a-z]:[\/\\\\]|\/|\\\\\\\\)/i', $r_dizin_k_fs)) {
		$r_dizin_k_fs = ROOT . '/' . ltrim($r_dizin_k_fs, '/\\');
	}

	if (!is_dir($r_dizin_k_fs) || !is_readable($r_dizin_k_fs)) {
		return false;
	}
	
	$resim_adi_dizi = [];
	$d_k = opendir($r_dizin_k_fs);

	if (!$d_k) {
		return false;
	}

	while (($r_s = readdir($d_k)) !== false) {
		if (preg_match('/\.(jpe?g|gif|png|webp)$/i', $r_s)) {
			$resim_adi_dizi[] = $r_s;
		}
	}

	closedir($d_k);

	$r_sayi = count($resim_adi_dizi);

	if ($r_sayi < $en_az_resim_sayisi) {
		return false;
	}

	if ($r_dizin_b === 'dresim_var') {
		return true;
	}

	sort($resim_adi_dizi, SORT_STRING);

	$yaz = '';
	if (!empty($ust_yazi)) {
		$yaz .= $ust_yazi . '<br>';
	}

	$class_attr = !empty($class) ? ' class="' . htmlspecialchars($class) . ' galeri_rs0' . $r_sayi . '"' : '';
	$yaz .= '<div' . $class_attr . '>';

	foreach ($resim_adi_dizi as $r_dosya) {
		$src = htmlspecialchars($r_dizin_k . '/' . $r_dosya);
		$alt = htmlspecialchars($resimalt);
		$href = htmlspecialchars($r_dizin_b . '/' . $r_dosya);

		if (!empty($r_dizin_b)) {
			$yaz .= '<a ' . $a . '="' . $href . '" data-fancybox="_' . $class . '_rs0' . $r_sayi . '" target="_blank">';
		}

		$yaz .= '<img alt="' . $alt . '" src="' . $src . '">';

		if (!empty($r_dizin_b)) {
			$yaz .= '</a> ';
		} else {
			$yaz .= ' ';
		}
	}

	$yaz .= '</div>';

	return $yaz;
}




/**
 * HTML içerisindeki özel etiketleri işleyerek PHP dosyalarını dahil eden fonksiyon
 * İki tür etiket işler:
 * 1. {$degisken} -> Basit değişken değeri yerleştirme
 * 2. {{i:konum:dosya}} -> PHP dosyası dahil etme
 * 
 * @param string $html_ İşlenecek HTML içeriği
 * @return string İşlenmiş HTML içeriği
 */
function sablon_include($html_) {	// Basit değişken yerleştirmeleri için: [[$degisken]]
	$html_ = preg_replace_callback('/\[\[\$([^\]]+)\]\]/', function($m_){
		global ${$m_[1]}; // Değişkeni global scope'dan al
		return ${$m_[1]}; // Değerini yerleştir
	}, $html_);

	// {{...}} formatındaki dahil etme etiketlerini işle
	$desen = '/{{(.+)}}/';
	$html_duzenle = preg_replace_callback($desen, function($m){
		global $pdo, $db, $pdo_db, $do_, $so_, $bo_, $indexx, $u_no__, $n;

		// Etiketi parçalara ayır (örn: i:tema:kolon1 -> ['i', 'tema', 'kolon1'])
		$eslesme = explode(":", q_($m[1]));

		// Son parça her zaman dosya adıdır
		$m_dosya_adi_bu = $eslesme[count($eslesme) - 1];
		$m_dosya_uzantisi_bu = 'php';
		
		if (!isset($eslesme[1])) {
			return; // Geçersiz format, işleme devam etme
		}

		// Dosyanın aranacağı dizini belirle:
		// - tema: Tema dosyaları için (örn: header.php, footer.php)
		// - yon: Yönlendirme dosyaları için (örn: kolon1.php, sayfa.php)
		//        Bu dosyalar genelde dinamik içerik veya sayfa parçaları içerir
		// - varsayılan: Ana PHP dizini
		if ($eslesme[1] === 'tema') {
			$dosya_yolu_dizin = TEMA_DIR . '/' . $so_->d('tema');
		} elseif ($eslesme[1] === 'yon') {
			$dosya_yolu_dizin = YONLENDIR_D;
		} else {
			$dosya_yolu_dizin = R_PHP;
		}
		$dosya_adi = $m_dosya_adi_bu . '.' . $m_dosya_uzantisi_bu;
		$dosya_yolu_dosya = $dosya_yolu_dizin . '/' . $dosya_adi;

		//process_log("Trying to include: " . $dosya_yolu_dosya);


		if ($eslesme[0]==='i' || $eslesme[0]==='i1'){
			
			if (strpos($eslesme[2], '+') !== false) {
				$e2 = trim($eslesme[2],'+');
				global ${$e2};
	
				if (isset($$e2) && $$e2==true) {
					ob_start();
					if (file_exists($dosya_yolu_dosya)){
						//include_once dosya aynı anda iki defa kullanılamaz. çakışmalara veya döngülere girmemesi için. bu tekrar düzenlenebilir.
						include_once ($dosya_yolu_dosya);
					}
					return ob_get_clean();
				}

			} elseif ($eslesme[1]==='$'){
				global $$m_dosya_adi_bu;/**burada $kolon1 olarak kullanılıyor {{i:$:kolon1}} -> include_once $kolon1;*/
				ob_start();
					if (file_exists($$m_dosya_adi_bu)){
						include_once ($$m_dosya_adi_bu);
					}
				return ob_get_clean();
			} elseif (file_exists($dosya_yolu_dosya)) {
				ob_start();
				include_once ($dosya_yolu_dosya);
				return ob_get_clean();
			} else {
				return $m[0];
			}
		} elseif ($eslesme[0]==='c') {
			global ${$eslesme[1]};
			$obje_ = ${$eslesme[1]};
			$obje_fn_ = $eslesme[2];
			return $obje_->$obje_fn_();
		}
		
	}, $html_);
	return $html_duzenle;
}

function html_modul($yazi) {
	global $pdo, $db, $pdo_db, $do_, $so_, $bo_, $indexx, $u_no__, $n;
		
	if (false === strpos($yazi, "[al:")) {
		return $yazi;
	}
	
	$yazi_son = '';
	$desen = '/\[[^\]]+\]/';    // [al:örnek_metin]
	$mtablo_no = 0;  // modul fonksiyon içinden alındığı için, burada oluşturulan mtablo_no, include olan dosya içinde kullanılıyor.
	
	$yazilar = preg_split($desen, $yazi);
	preg_match_all($desen, $yazi, $modulicerik);
	
	$modul_stmt = $pdo->prepare("SELECT no FROM {$do_}moduller WHERE url = :url AND yayin = 1");
	$bilesen_stmt = $pdo->prepare("SELECT no FROM {$do_}bilesen WHERE url = :url AND yayin = 1");
	
	for ($msayi = 0; $msayi < count($yazilar); $msayi++) {
		$dizi_yazilar = isset($yazilar[$msayi]) ? $yazilar[$msayi] : '';
		$yazi_son .= $dizi_yazilar;
		
		$dizi_al = isset($modulicerik[0][$msayi]) ? $modulicerik[0][$msayi] : '';
		preg_match("/\[al\:([^\:]+)\:([^\:\]]+)\:?([^\]]+)?\]/is", $dizi_al, $dizi_url);
		
		if (isset($dizi_url[1]) && isset($dizi_url[2])) {
			$dizi_modul_bilesen = preg_replace("/\W+/", "", $dizi_url[1]);
			$dizi_modul_bilesen_url = preg_replace("/\W+/", "", $dizi_url[2]);
			
			if ($dizi_modul_bilesen === 'modul') {
				$modul_stmt->execute(['url' => $dizi_modul_bilesen_url]);
				$modul_url_var = $modul_stmt->fetchColumn();
				
				$modul_include_dosya_url = MODUL_DIR . '/' . $dizi_modul_bilesen_url . '/index.php';
				
				if ($modul_url_var && is_file($modul_include_dosya_url)) {
					ob_start();
					include_once $modul_include_dosya_url;
					$yazi_son .= ob_get_clean();
				}
			}
			
			if ($dizi_modul_bilesen === 'bilesen' && isset($dizi_url[3])) {
				$bilesen_dosya = preg_replace("/\W+/", "", $dizi_url[3]);
				
				$bilesen_stmt->execute(['url' => $dizi_modul_bilesen_url]);
				$bilesen_url_var = $bilesen_stmt->fetchColumn();
				$bilesen_include_dosya_url = BILESEN_DIR . '/' . $dizi_modul_bilesen_url . '/php/b_al_modul/' . $bilesen_dosya . '.php';
				
				if ($bilesen_url_var && is_file($bilesen_include_dosya_url)) {
					ob_start();
					include_once $bilesen_include_dosya_url;
					$yazi_son .= ob_get_clean();
				}
			}
		}
	}
	
	return $yazi_son;
}

function degisken_duzenle($yazi){
	return str_replace(['{{$:local}}', '{{$:img}}'], [LOCAL, IMG], $yazi);	
}


function n0_($n) {
	return (isset($n) && is_numeric($n) && $n > 0);
}

function email_validate(?string $email): bool {
	if ($email === null) {
		return false;
	}
	$email = trim($email);
	return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function email_duzenle(?string $email): string {
	if ($email === null) {
		return '';
	}
	$email = trim($email);
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return '';
	}
	return filter_var($email, FILTER_SANITIZE_EMAIL);
}


function getIP() {
	$fallback_ip = null;
	
	foreach ([
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_X_CLUSTER_CLIENT_IP',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'REMOTE_ADDR'
	] as $key) {
		if (array_key_exists($key, $_SERVER)) {
			foreach (explode(',', $_SERVER[$key]) as $ip) {
				$ip = trim($ip);
				
				// İlk geçerli IP'yi fallback olarak sakla
				if ($fallback_ip === null && filter_var($ip, FILTER_VALIDATE_IP)) {
					$fallback_ip = $ip;
				}
				
				// Public IP bulunursa direkt döndür
				if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
					return $ip;
				}
			}
		}
	}

	// Public IP bulunamadıysa fallback kullan (local test için)
	return $fallback_ip ?? 'UNKNOWN';
}

function isValidDomain($domain, $query = true) {
	if (empty($domain)) return false;

	if ($query) {
		if (!preg_match('#^https?://#i', $domain)) {
			$domain = 'http://' . $domain;
		}

		$host = parse_url($domain, PHP_URL_HOST);
	} else {
		$host = preg_replace('/^(https?:\/\/)?(www\.)?/i', '', $domain);
		if (parse_url('http://' . $host, PHP_URL_PATH) || parse_url('http://' . $host, PHP_URL_QUERY)) {
			return false;
		}
	}
	return $host && filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
}

function file_get_contents_curl($url, $timeout = 10) {
	$ch = curl_init($url);
	
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => $timeout,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36',
		CURLOPT_HEADER => false,
	]);
	$response = curl_exec($ch);
	if (curl_errno($ch)) {
		curl_close($ch);
		return false;
	}
	curl_close($ch);
	return $response;
}

function google_captcha($google_secret_key) {
	$captcha = $_POST['g-recaptcha-response'] ?? '';
	if (empty($captcha)) return false;

	$data = [
		'secret'   => $google_secret_key,
		'response' => $captcha,
		'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
	];

	$ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 10,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => http_build_query($data),
	]);

	$response = curl_exec($ch);
	curl_close($ch);

	if (!$response) return false;

	$responseKeys = json_decode($response, true);

	return isset($responseKeys["success"]) && $responseKeys["success"] === true;
}


function cloudflare_verification($secret_key, $turnstile_response = null) { 
	if (empty($turnstile_response)) {
		$turnstile_response = $_POST['cf-turnstile-response'] ?? null;
	}
	if (empty($turnstile_response)) {
		return false;
	}

	$url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
	$data = [
		'secret' => $secret_key,
		'response' => $turnstile_response
	];

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);

	$response = curl_exec($ch);

	if ($response === false) {
		curl_close($ch);
		return false;
	}

	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($httpCode !== 200) {
		return false;
	}

	$result = json_decode($response, true);
	return !empty($result['success']);
}


function tarih_dt($tarih, bool $saat_goster = true, string $dil = 'tr'): string {
	if ($tarih === '0000-00-00 00:00:00' || !preg_match('/[1-9]/', $tarih ?? '')) {
		return '&nbsp;';
	}

	$zaman = strtotime($tarih);
	if (!$zaman) return '&nbsp;';

	// Mevcut locale'i kaydet
	$eski_locale = setlocale(LC_TIME, '0');

	// Dil kodlarını locale'e çevir
	$locale_map = [
		'tr' => ['tr_TR.UTF-8', 'tr_TR', 'turkish'],
		'en' => ['en_US.UTF-8', 'en_US', 'english'],
		'de' => ['de_DE.UTF-8', 'de_DE', 'german'],
		'fr' => ['fr_FR.UTF-8', 'fr_FR', 'french'],
		'es' => ['es_ES.UTF-8', 'es_ES', 'spanish'],
		'it' => ['it_IT.UTF-8', 'it_IT', 'italian'],
		'ru' => ['ru_RU.UTF-8', 'ru_RU', 'russian'],
		'ar' => ['ar_SA.UTF-8', 'ar_SA', 'arabic'],
		'zh' => ['zh_CN.UTF-8', 'zh_CN', 'chinese'],
		'ja' => ['ja_JP.UTF-8', 'ja_JP', 'japanese']
	];

	$locales = $locale_map[$dil] ?? $locale_map['en'];
	
	// Locale ayarla (birden fazla deneme yapar, ilk başarılı olanı kullanır)
	$locale_ayarlandi = false;
	foreach ($locales as $locale) {
		if (setlocale(LC_TIME, $locale) !== false) {
			$locale_ayarlandi = true;
			break;
		}
	}

	$gun = date("d", $zaman);
	$ay = date("m", $zaman);
	$yil = date("Y", $zaman);
	$saat_yaz = $saat_goster ? date("H:i", $zaman) : '';

	// Biçim şablonları
	$bicimler = [
		'tr' => "%d %B %Y %A",      // 16 Ekim 2024 Çarşamba
		'en' => "%B %d %Y %A",      // October 16 2024 Wednesday
		'de' => "%d. %B %Y %A",     // 16. Oktober 2024 Mittwoch
		'fr' => "%d %B %Y %A",      // 16 octobre 2024 mercredi
		'es' => "%d de %B de %Y %A",// 16 de octubre de 2024 miércoles
		'lt' => "%Y %b %d"          // Kısa format
	];

	$bicim = $bicimler[$dil] ?? $bicimler['en'];
	if ($saat_goster) {
		$bicim .= " %H:%M";
	}

	// strftime kullanarak formatla (locale'e göre otomatik çeviri)
	// PHP 8.1+ için IntlDateFormatter kullan, yoksa strftime fallback
	if ($locale_ayarlandi) {
		if (class_exists('IntlDateFormatter')) {
			// Modern yöntem: IntlDateFormatter (PHP 8.1+ önerilen)
			$locale_intl = str_replace('_', '-', explode('.', $locales[0])[0]); // tr_TR.UTF-8 → tr-TR
			
			$formatter = new IntlDateFormatter(
				$locale_intl,
				IntlDateFormatter::LONG,
				$saat_goster ? IntlDateFormatter::SHORT : IntlDateFormatter::NONE,
				date_default_timezone_get()
			);
			
			// Pattern ayarla
			$pattern_map = [
				'tr' => "d MMMM y EEEE",
				'en' => "MMMM d y EEEE",
				'de' => "d. MMMM y EEEE",
				'fr' => "d MMMM y EEEE",
				'es' => "d 'de' MMMM 'de' y EEEE"
			];
			
			$pattern = $pattern_map[$dil] ?? $pattern_map['en'];
			if ($saat_goster) {
				$pattern .= " HH:mm";
			}
			
			$formatter->setPattern($pattern);
			$formatted = $formatter->format($zaman);
			
		} elseif (function_exists('strftime')) {
			// Eski yöntem: strftime (PHP 8.1'de deprecated)
			$formatted = strftime($bicim, $zaman);
			
			// UTF-8 encoding kontrolü
			if (mb_detect_encoding($formatted, 'UTF-8', true) === false) {
				$formatted = utf8_encode($formatted);
			}
		} else {
			// Fallback
			$locale_ayarlandi = false;
		}
	}
	
	if (!$locale_ayarlandi) {
		// Fallback: strftime yoksa veya locale ayarlanamadıysa manuel çeviri (sadece TR)
		$gunler = [
			'Monday'    => 'Pazartesi',
			'Tuesday'   => 'Salı',
			'Wednesday' => 'Çarşamba',
			'Thursday'  => 'Perşembe',
			'Friday'    => 'Cuma',
			'Saturday'  => 'Cumartesi',
			'Sunday'    => 'Pazar'
		];

		$aylar = [
			'January'   => 'Ocak',
			'February'  => 'Şubat',
			'March'     => 'Mart',
			'April'     => 'Nisan',
			'May'       => 'Mayıs',
			'June'      => 'Haziran',
			'July'      => 'Temmuz',
			'August'    => 'Ağustos',
			'September' => 'Eylül',
			'October'   => 'Ekim',
			'November'  => 'Kasım',
			'December'  => 'Aralık'
		];

		$bicim_fallback = $dil === 'en' ? "F d Y l" : "d F Y l";
		if ($saat_goster) {
			$bicim_fallback .= " H:i";
		}

		$formatted = date($bicim_fallback, $zaman);

		if ($dil === 'tr') {
			$formatted = strtr($formatted, $gunler);
			$formatted = strtr($formatted, $aylar);
		}
	}

	if ($eski_locale !== false) {
		setlocale(LC_TIME, $eski_locale);
	}

	if (date("Y") == $yil) {
		$formatted = str_replace($yil, '', $formatted);
		$formatted = trim(preg_replace('/\s+/', ' ', $formatted));
	}

	if (date("d") == $gun && date("m") == $ay && date("Y") == $yil && $dil === "tr") {
		$formatted = yc("Bugün") . ($saat_goster ? " $saat_yaz" : '');
	}

	// Gece yarısı saatini kaldır
	$formatted = str_replace('00:00', '', $formatted);

	return trim($formatted);
}

/**
 * Tarih aralığını kompakt formatta gösterir
 * Örnek: "14 → 16 Ekim 25 03:00" veya "30 Ekim → 2 Kasım 25 03:00"
 * 
 * @param string $baslangic_tarih Başlangıç tarihi (Y-m-d H:i:s formatında)
 * @param string $bitis_tarih Bitiş tarihi (Y-m-d H:i:s formatında)
 * @param bool $saat_goster Saat gösterilsin mi? (varsayılan: true)
 * @return string Formatlanmış tarih aralığı
 */
/**
 * Tarih aralığını kompakt formatta gösterir
 * Örnekler:
 * - Aynı ay: "14 → 16 Ekim 25 03:00"
 * - Farklı ay: "30 Ekim → 2 Kasım 25 03:00"
 * 
 * @param string $baslangic_tarih Başlangıç tarihi (Y-m-d H:i:s)
 * @param string $bitis_tarih Bitiş tarihi (Y-m-d H:i:s)
 * @param bool $saat_goster Saat gösterilsin mi
 * @param string $dil Dil kodu (tr, en, de, fr, es, vb.)
 * @return string Formatlanmış tarih aralığı
 */
function tarih_aralik_kompakt(string $baslangic_tarih, string $bitis_tarih, bool $saat_goster = true, string $dil = ''): string {
	$baslangic_time = strtotime($baslangic_tarih);
	$bitis_time = strtotime($bitis_tarih);
	
	if (!$baslangic_time || !$bitis_time) {
		return '';
	}
	
	// Dil belirleme
	if (empty($dil)) {
		$dil = Global_::$d ?? 'tr';
	}
	
	// Locale'i kaydet ve ayarla
	$eski_locale = setlocale(LC_TIME, '0');
	
	$locale_map = [
		'tr' => ['tr_TR.UTF-8', 'tr_TR', 'turkish'],
		'en' => ['en_US.UTF-8', 'en_US', 'english'],
		'de' => ['de_DE.UTF-8', 'de_DE', 'german'],
		'fr' => ['fr_FR.UTF-8', 'fr_FR', 'french'],
		'es' => ['es_ES.UTF-8', 'es_ES', 'spanish'],
		'it' => ['it_IT.UTF-8', 'it_IT', 'italian'],
		'ru' => ['ru_RU.UTF-8', 'ru_RU', 'russian'],
		'ar' => ['ar_SA.UTF-8', 'ar_SA', 'arabic'],
		'zh' => ['zh_CN.UTF-8', 'zh_CN', 'chinese']
	];
	
	$locales = $locale_map[$dil] ?? $locale_map['en'];
	foreach ($locales as $locale) {
		if (setlocale(LC_TIME, $locale) !== false) {
			break;
		}
	}
	
	// Başlangıç tarihi bilgileri
	$bas_gun = (int)date('d', $baslangic_time);
	$bas_yil = date('y', $baslangic_time); // 2 haneli yıl
	
	// Bitiş tarihi bilgileri
	$bit_gun = (int)date('d', $bitis_time);
	$bit_yil = date('y', $bitis_time);
	$bit_saat = $saat_goster ? date('H:i', $bitis_time) : '';
	
	// Ay isimlerini locale'den al
	if (class_exists('IntlDateFormatter')) {
		// Modern yöntem: IntlDateFormatter
		$locale_intl = str_replace('_', '-', explode('.', $locales[0])[0]);
		$formatter = new IntlDateFormatter(
			$locale_intl,
			IntlDateFormatter::LONG,
			IntlDateFormatter::NONE,
			date_default_timezone_get(),
			IntlDateFormatter::GREGORIAN,
			'MMMM' // Tam ay ismi
		);
		
		$bas_ay = $formatter->format($baslangic_time);
		$bit_ay = $formatter->format($bitis_time);
		
	} else {
		// Fallback: strftime (PHP 8.0 ve öncesi)
		$bas_ay = strftime('%B', $baslangic_time);
		$bit_ay = strftime('%B', $bitis_time);
	}
	
	// Aynı ay ve yıl ise: "14 → 16 Ekim 25 03:00"
	if (date('m-Y', $baslangic_time) === date('m-Y', $bitis_time)) {
		$sonuc = "{$bas_gun} → {$bit_gun} {$bit_ay} {$bit_yil}";
	}
	// Farklı ay veya yıl: "30 Ekim → 2 Kasım 25 03:00"
	else {
		$sonuc = "{$bas_gun} {$bas_ay} → {$bit_gun} {$bit_ay} {$bit_yil}";
	}
	
	// Saat ekle
	if ($saat_goster && !empty($bit_saat)) {
		$sonuc .= " {$bit_saat}";
	}
	
	// Locale'i geri yükle
	setlocale(LC_TIME, $eski_locale);
	
	return $sonuc;
}


function format_tarih(?string $tarih, string $format = 'd.m.Y'): ?string {
    if (empty($tarih)) {
        return null;
    }

    $tarihFormatlari = [
        'd.m.Y', 'd-m-Y', 'd/m/Y',
        'd.m.y', 'd-m-y', 'd/m/y',
        'Y-m-d', 'Y/m/d', 'Y.m.d',
        'y-m-d', 'y/m/d', 'y.m.d',
    ];

    $saatFormatlari = [
        'Y-m-d H:i:s', 'Y-m-d H:i',
        'd.m.Y H:i:s', 'd.m.Y H:i',
        'd-m-Y H:i:s', 'd-m-Y H:i',
        'd/m/Y H:i:s', 'd/m/Y H:i',
    ];

    $olasiFormatlar = $tarihFormatlari;

    if (preg_match('/[Hhis]/', $format)) {
        $olasiFormatlar = [...$tarihFormatlari, ...$saatFormatlari];
    }

    foreach ($olasiFormatlar as $f) {
        $date = DateTime::createFromFormat($f, $tarih);
        if ($date && $date->format($f) === $tarih) {
            return $date->format($format);
        }
    }

    try {
        return (new DateTime($tarih))->format($format);
    } catch (Exception) {
        return null;
    }
}


function tarih_d(?string $tarih): ?string {
	return format_tarih($tarih, 'd.m.Y');
}
function tarih_dmY_Ymd(?string $tarih): ?string {
	return format_tarih($tarih, 'Y-m-d');
}

function formatDateWithMonthWord(string $dateStr, string $lang = 'en'): string {

	$date = DateTime::createFromFormat('d.m.Y', $dateStr);
	if (!$date) {
		throw new InvalidArgumentException("Invalid date format: $dateStr");
	}

	$locales = [
		'en' => 'en_US',
		'de' => 'de_DE',
		'tr' => 'tr_TR',
		'ru' => 'ru_RU',
		'fr' => 'fr_FR',
		'es' => 'es_ES',
		'it' => 'it_IT',
		'nl' => 'nl_NL',
		'pl' => 'pl_PL',
		'uk' => 'uk_UA',
		'ar' => 'ar_EG',
		'fa' => 'fa_IR',
		'hi' => 'hi_IN',
		'ja' => 'ja_JP',
		'zh' => 'zh_CN',
		'ko' => 'ko_KR',
	];

	$locale = $locales[$lang] ?? 'en_US';

	$formatter = new IntlDateFormatter(
		$locale,
		IntlDateFormatter::NONE,
		IntlDateFormatter::NONE,
		null,  // Use server's default timezone instead of UTC
		IntlDateFormatter::GREGORIAN,
		'd MMMM y'  // e.g., 27 September 2025
	);

	return $formatter->format($date);
}


function parse_float($deger, int $decimals = 2): float {
	if (is_null($deger) || $deger === '') {
		return 0.0;
	}
	if (is_string($deger)) {
		$deger = strtr($deger, [',' => '.']);
	}
	return round((float)$deger, $decimals);
}


function process_log(string $text): void {
	$timestamp = date('[Y-m-d H:i:s]');
	$message = "{$timestamp} {$text}" . PHP_EOL;
	error_log($message, 3, PROCESS_LOG_FILE);
}

function data_nta($nt, $a) {
    return ' data-nta="' . sifrele(trim($nt, ',') . ',,' . $a) . '"';
}

function yuzde(float $sayi, float $yuzde, int $artieksi = -1) { 
    $artieksicarpan = ($artieksi === 1) ? 1 : -1;
    return ceil($sayi + (($sayi * ($yuzde / 100)) * $artieksicarpan));
}



function kayit_sil_(string $tablo_from, int $n_): bool {
	global $pdo;
	if (!preg_match('/^[a-zA-Z0-9_]+$/', $tablo_from)) {
		error_log(date('[Y-m-d H:i:s] ') . 'Invalid table name: ' . $tablo_from);
		return false;
	}
	try {
		$stmt = $pdo->prepare("DELETE FROM `$tablo_from` WHERE no = :no");
		return $stmt->execute([':no' => $n_]);
	} catch (PDOException $e) {
		error_log(date('[Y-m-d H:i:s] ') . 'kayit_sil_ error: ' . $e->getMessage());
		return false;
	}
}

function kayit_dosya_sil_(string $tablo_from, int $n_, string $dosya_yolu_): bool {
	$sil = kayit_sil_($tablo_from, $n_);
	
	if ($sil && is_dir($dosya_yolu_)) {
		dosya_sil_($dosya_yolu_);
	}
	
	return $sil;
}


function dizin_olustur($dizin, $chmodDir = 0755, $chmodFile = 0644, $indexHtml = true) {
	if (!is_dir($dizin)) {
		mkdir($dizin, $chmodDir, true);
		if ($indexHtml) {
			file_put_contents($dizin . '/index.html', '');
		}
	}
	if ($indexHtml) {
		$files = glob($dizin . '/*');
		foreach ($files as $file) {
			if (is_file($file)) {
				chmod($file, $chmodFile);
			}
		}
	}
	chmod($dizin, intval($chmodDir, 8) & ~01000);
}

function dosya_glob(string $dizin, string $globdtip): array {
	$files = glob("$dizin/$globdtip", GLOB_BRACE) ?: [];

	usort(
		$files,
		fn($file_1, $file_2) => filectime($file_2) <=> filectime($file_1)
	);

	return $files;
}

function get_file_size(string $file_path): string|false {
    if (!file_exists($file_path)) {
        return false;
    }

    $bytes = filesize($file_path);
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $index = 0;
    while ($bytes >= 1024 && $index < count($units) - 1) {
        $bytes /= 1024;
        $index++;
    }

    return round($bytes, 2) . ' ' . $units[$index];
}



function vcdil(string $alan, string $tablo, int $tablo_no): string {
	global $pdo, $do_;
	$vc_dil = "";
	$sql = "SELECT dil_no FROM {$do_}ceviriler WHERE alan = :alan AND tablo = :tablo AND tablo_no = :tablo_no";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([
		':alan' => $alan,
		':tablo' => $tablo,
		':tablo_no' => $tablo_no
	]);
	$vc_diller = $stmt->fetchAll(PDO::FETCH_OBJ);
	if ($vc_diller) {
		foreach ($vc_diller as $vc_dil_) {
			$sql2 = "SELECT dkod FROM {$do_}diller WHERE no = :no";
			$stmt2 = $pdo->prepare($sql2);
			$stmt2->execute([':no' => $vc_dil_->dil_no]);
			$dil_kod = $stmt2->fetchColumn();
			if ($dil_kod) {
				$vc_dil .= '<span data-dc="' . htmlspecialchars($dil_kod) . '">' . htmlspecialchars($dil_kod) . '</span>,';
			}
		}
	}
	return trim($vc_dil, ",");
}

function vcdiller(string $alan, string $tablo, int $tablo_no): string {
	global $pdo, $do_, $so_;
	$vc_dil_yaz = "";
	$varsayilan_dil = $so_->d('varsayilan_dil');
	$sql = "SELECT no, dkod FROM {$do_}diller WHERE yayin = 1 AND dkod != :varsayilan_dil ORDER BY sira ASC, no ASC";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([':varsayilan_dil' => $varsayilan_dil]);
	$vc_diller = $stmt->fetchAll(PDO::FETCH_OBJ);
	
	if ($vc_diller) {
		foreach ($vc_diller as $vc_d) {
			$sql2 = "SELECT no FROM {$do_}ceviriler WHERE dil_no = :dil_no AND alan = :alan AND tablo = :tablo AND tablo_no = :tablo_no";
			$stmt2 = $pdo->prepare($sql2);
			$stmt2->execute([
				':dil_no' => $vc_d->no,
				':alan' => $alan,
				':tablo' => $tablo,
				':tablo_no' => $tablo_no
			]);
			$vc_dil_alan_yazi_var = $stmt2->fetchColumn();
			$vc_dil_class = 'vcdil ' . ($vc_dil_alan_yazi_var ? 'vcd_degistir' : 'vcd_ekle');
			$vc_dil_yaz .= '<span class="' . htmlspecialchars($vc_dil_class) . '" data-dc="' . htmlspecialchars($vc_d->dkod) . '">' . htmlspecialchars($vc_d->dkod) . '</span>';
		}
	}
	return trim($vc_dil_yaz);
}

/**
 * Belirtilen dizide, belirli bir elemandan sonra yeni bir eleman ekler.
 *
 * @param array $dizi        - İşlem yapılacak çok boyutlu dizi
 * @param mixed $aranan      - Aranacak değer (ilk sütundaki)
 * @param array $yeni_eleman - Eklenecek yeni alt dizi
 * @return array             - Güncellenmiş dizi
 */
function diziye_ara_eleman_ekle(array $dizi, $aranan, array $yeni_eleman) {
    foreach ($dizi as $indeks => $satir) {
        if (isset($satir[0]) && $satir[0] === $aranan) {
            array_splice($dizi, $indeks + 1, 0, [$yeni_eleman]);
            break;
        }
    }
    return $dizi;
}


function is_webp_animated($dosya_yolu) {
	$fileContent = file_get_contents($dosya_yolu);
	$offset = 0;
	$frames = 0;

	while ($frames < 2) {
		$size = unpack('v', substr($fileContent, $offset + 4, 2));
		$offset += $size[1] + 8;
		if (substr($fileContent, $offset, 4) === 'ANMF') {
			$frames++;
		} else {
			break;
		}
	}
	return $frames > 1;
}


function resim_boyutlandir_($kaynak_dosya, $boyut, $hedef_dizin, $hedef_dosya) {
	$hedef_dizin .= (substr($hedef_dizin, -1) != "/") ? "/" : "";
	$gis = @getimagesize($kaynak_dosya);
	if ($gis === false || !isset($gis[2])) {
		return false;
	}
	$type = $gis[2];

	if ($type == IMAGETYPE_GIF) {
		$imorig = @imagecreatefromgif($kaynak_dosya);
	} elseif ($type == IMAGETYPE_JPEG) {
		$imorig = @imagecreatefromjpeg($kaynak_dosya);
	} elseif ($type == IMAGETYPE_PNG) {
		$imorig = @imagecreatefrompng($kaynak_dosya);
	} elseif ($type == IMAGETYPE_WEBP) {
		$imorig = @imagecreatefromwebp($kaynak_dosya);
	} else {
		$imorig = false;
	}

	if (!$imorig) {
		return false;
	}

	$x = @imagesx($imorig);
	$y = @imagesy($imorig);

	if ($x === false || $y === false || $x == 0 || $y == 0) {
		imagedestroy($imorig);
		return false;
	}

	if ($gis[0] <= $boyut) {
		$av = $x;
		$ah = $y;
	} else {
		$yc = $y * 1;
		$d = $x > $yc ? $x : $yc;
		$c = $d > $boyut ? $boyut / $d : $boyut;
		$av = (int)round($x * $c);
		$ah = (int)round($y * $c);
	}

	$im = imagecreatetruecolor($av, $ah);
	if ($im === false) {
		imagedestroy($imorig);
		return false;
	}

	// Handle transparency for PNG and WEBP
	if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
		imagealphablending($im, false);
		imagesavealpha($im, true);
	}

	if (imagecopyresampled($im, $imorig, 0, 0, 0, 0, $av, $ah, $x, $y) === false) {
		imagedestroy($imorig);
		imagedestroy($im);
		return false;
	}

	$success = false;

	if ($type == IMAGETYPE_PNG) {
		$success = imagepng($im, $hedef_dizin . $hedef_dosya);
	} elseif ($type == IMAGETYPE_WEBP) {
		if (function_exists('imagewebp')) {
			$isAnimated = is_webp_animated($kaynak_dosya);
			if ($isAnimated) {
				$hedef_dosya_tam_yolu = $hedef_dizin . $hedef_dosya;
				$success = copy($kaynak_dosya, $hedef_dosya_tam_yolu);
			} else {
				$success = imagewebp($im, $hedef_dizin . $hedef_dosya);
			}
		}
	} else {
		$success = imagejpeg($im, $hedef_dizin . $hedef_dosya, 80);
	}

	imagedestroy($imorig);
	imagedestroy($im);

	return $success;
}

function convertToPNG($inputFilePath) {
	if (!extension_loaded('gd')) {
		return false;
	}
	$outputFilePath = pathinfo($inputFilePath, PATHINFO_FILENAME) . '.png';
	$sourceImage = imagecreatefromjpeg($inputFilePath);
	imagepng($sourceImage, $outputFilePath);
	imagedestroy($sourceImage);
	return $outputFilePath;
}

function autoi_fix(PDO $pdo, string $tablo_from) {
	if (!preg_match('/^[a-zA-Z0-9_]+$/', $tablo_from)) {
		error_log(date('[Y-m-d H:i:s] ') . 'Invalid table name: ' . $tablo_from);
		return false;
	}

	$stmt = $pdo->query("SELECT MAX(no) FROM `$tablo_from`");
	$max_no = $stmt->fetchColumn();

	$auto_increment = ($max_no !== null) ? $max_no + 1 : 1;
	return $pdo->exec("ALTER TABLE `$tablo_from` AUTO_INCREMENT = $auto_increment") !== false;
}

function a_z0_9_($yazi) {
	$yazi = strtr($yazi, [
		'ç' => 'c', 'ğ' => 'g', 'ü' => 'u', 'ş' => 's', 'ö' => 'o', 'İ' => 'I',
		'ı' => 'i', 'Ç' => 'C', 'Ğ' => 'G', 'Ü' => 'U', 'Ş' => 'S', 'Ö' => 'O'
	]);
	$yazi = strtr($yazi, [' ' => '_', '-' => '_']);
	return preg_replace('/[^a-z0-9_]/i', '', $yazi);
}


function href($index = 'index', $query = '', $get = 0) {
	global $so_;

	if ($index === 0) {
		return $query;
	}
	$query = ltrim($query, '?');
	$query = str_replace('?', '&', $query);

	$basePath = ($get != 0) ? './' : '';

	if ($so_->d('permalink') != 1) {
		return $basePath . $index . '.php?' . $query;
	}

	parse_str($query, $params);
	unset($params['d']);

	if (count($params) == 1 && reset($params) === '') {
		$href = key($params);
		return $basePath . $href;
	}

	$query = http_build_query($params);

	if (empty($query)) {
		return $basePath;
	}

	$parts = explode('&', $query);
	$first = array_shift($parts);
	
	if (strpos($first, '=') === false) {
		$href = $first;
	} else {
		list($key, $value) = explode('=', $first, 2);
		
		if ($key === 'sayfa' || $key === 'sekme') {
			$href = explode('.', $value)[0];
		} else {
			$value = str_replace('.', '-', $value);
			$href = ($key === 's') ? 's/' . $value : $key . '/' . $value;
		}
	}

	if (count($parts) > 0) {
		$href .= '?' . implode('&', $parts);
	}

	$href = rtrim($href, '/');

	return $basePath . $href;
}

function print_pre($data, $pre_tags = []) {
	if (!is_array($data) && !is_object($data)) return '!array';

	$attr_str = !empty($pre_tags) ? ' ' . implode(' ', $pre_tags) : '';
	$label = match (true) {
		$data === $_POST => '$_POST: ',
		$data === $_GET  => '$_GET: ',
		$data === $_SESSION  => '$_SESSION: ',
		default          => '',
	};

	$json = is_array($data) && count($data) <= 5 && count($data, COUNT_RECURSIVE) < 15
		? print_r($data, true)
		: json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

	return "<pre{$attr_str}>" . ($label ? "$label\n" : '') . $json . '</pre>';
}

function print_console_log($data) {
	$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	if ($json === false) {
		$json = json_encode((string) $data, JSON_UNESCAPED_UNICODE);
	}
	return '<script>console.log('. $json . ');</script>';
}



function tum_url(): string {
	$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
	$host = $_SERVER['HTTP_HOST'];
	
	// REQUEST_URI'yi al
	$uri = $_SERVER['REQUEST_URI'] ?? $_SERVER['SCRIPT_NAME'];
	
	// Eğer URI'de ? yoksa ve QUERY_STRING varsa ekle
	if (strpos($uri, '?') === false && !empty($_SERVER['QUERY_STRING'])) {
		$uri .= '?' . $_SERVER['QUERY_STRING'];
	}
	
	return $protocol . $host . $uri;
}

function guvenli_url_encode(string $url): string {
	$p = parse_url($url);
	if ($p === false) {
		return $url;
	}

	$scheme   = isset($p['scheme'])   ? $p['scheme'] . '://' : '';
	$host     = $p['host']   ?? '';
	$port     = isset($p['port'])     ? ':' . $p['port'] : '';
	$path     = $p['path']   ?? '';

	$query = '';
	if (isset($p['query'])) {
		parse_str($p['query'], $q);
		$query = '?' . http_build_query($q, '', '&', PHP_QUERY_RFC3986);
	}

	$fragment = isset($p['fragment']) ? '#' . rawurlencode($p['fragment']) : '';

	return $scheme . $host . $port . $path . $query . $fragment;
}


function guvenli_url_decode($url): string {
	if (empty($url)) {
		return '';
	}
	$decoded = urldecode($url);
	$decoded = preg_replace('/[<>"\'\x00-\x1f\x7f-\xff]/', '', $decoded);
	$decoded = str_replace(['../', '..\\', './'], '', $decoded);
	$decoded = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $decoded);
	if (preg_match('/^(javascript|data|vbscript|file):/i', $decoded)) {
		return '';
	}
	return $decoded;
}

function redirect_url_kontrol(?string $redirect_url): void {
	if ($redirect_url && 
		$redirect_url !== $_SERVER['REQUEST_URI'] &&
		$redirect_url !== LOCAL . '/uis/uyegiris') {
		
		if (strpos($redirect_url, 'http') === 0) {
			$final_url = $redirect_url;
		} elseif (strpos($redirect_url, '/') === 0) {
			$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
			$final_url = $protocol . $_SERVER['HTTP_HOST'] . $redirect_url;
		} else {
			$final_url = LOCAL . '/' . $redirect_url;
		}
		
		header("Location: " . $final_url, true, 302);
		exit();
	}
}

function sifre_harf_kontrol($sifre, $kosullar) {
	$min_uzunluk = strlen($kosullar);

	if (strlen($sifre) < $min_uzunluk) {
		return "Şifre en az $min_uzunluk karakter uzunluğunda olmalıdır.";
	}

	$buyuk = $kucuk = $rakam = $ozel = false;

	for ($i = 0; $i < strlen($kosullar); $i++) {
		$c = $kosullar[$i];

		if (ctype_lower($c)) $kucuk = true;
		elseif (ctype_upper($c)) $buyuk = true;
		elseif (ctype_digit($c)) $rakam = true;
		else $ozel = true;
	}

	if ($kucuk && !preg_match('/[a-z]/', $sifre)) return "Şifre en az bir küçük harf içermelidir.";
	if ($buyuk && !preg_match('/[A-Z]/', $sifre)) return "Şifre en az bir büyük harf içermelidir.";
	if ($rakam && !preg_match('/[0-9]/', $sifre)) return "Şifre en az bir rakam içermelidir.";
	if ($ozel && !preg_match('/[^a-zA-Z0-9]/', $sifre)) return "Şifre en az bir özel karakter içermelidir.";

	return true;
}

function c($yazi) {
	return $yazi;
}


function pdo_count(PDO $pdo, string $query, array $params = []): int {
	try {
		$stmt = $pdo->prepare($query);
		$stmt->execute($params);
		return (int)$stmt->fetchColumn();
	} catch (PDOException $e) {
		error_log("DB Sayım Hatası: " . $e->getMessage());
		return 0;
	}
}

function pdo_row(PDO $pdo, string $query, array $params = []): ?object {
	try {
		$stmt = $pdo->prepare($query);
		$stmt->execute($params);
		$row = $stmt->fetch(PDO::FETCH_OBJ);
		return $row === false ? null : $row;
	} catch (PDOException $e) {
		error_log("DB Fetch Hatası: " . $e->getMessage());
		return null;
	}
}

function pdo_fetch_obj(PDO $pdo, string $query, array $params = []) {
	$stmt = $pdo->prepare($query);
	$stmt->execute($params);
	return $stmt->fetch(PDO::FETCH_OBJ);
}


function pdo_insert(PDO $pdo, string $table, array $data): bool {
	$raw_sql_keywords = [
		'NOW()', 'CURRENT_TIMESTAMP', 'UUID()', 'NULL', 'DEFAULT',
		'CURDATE()', 'CURTIME()', 'SYSDATE()', 'RAND()', 'UNIX_TIMESTAMP()',
		'LOCALTIME()', 'LOCALTIMESTAMP()', 'CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_USER'
	];

	$columns = [];
	$placeholders = [];
	$params = [];

	foreach ($data as $key => $value) {
		$columns[] = $key;

		if (is_string($value) && in_array(strtoupper(trim($value)), $raw_sql_keywords, true)) {
			$placeholders[] = $value;
		} else {
			$placeholder = ':' . $key;
			$placeholders[] = $placeholder;
			$params[$key] = $value;
		}
	}

	$sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

	try {
		$stmt = $pdo->prepare($sql);
		return $stmt->execute($params);
	} catch (PDOException $e) {
		error_log(date('[Y-m-d H:i:s] ') . "PDO Insert Error: " . $e->getMessage() . PHP_EOL);
		return false;
	}
}

/**
 * Tüm tablo isimlerini getirir.
 *
 * @param PDO $pdo Veritabanı bağlantısı
 * @param array exclude_tables hariç bırakılacak tablolar dizisi
 * @return array Tablo başarıyla oluşturulursa dizi döner, hata olursa boş dizi döner.
*/

function tumTablolariGetir(PDO $pdo, array $exclude_tables = []): array {
	try {
		$query = $pdo->query("SHOW TABLES");
		$tablolar = [];

		while ($row = $query->fetch(PDO::FETCH_NUM)) {
			$tablo_adi = $row[0];
			if (!in_array($tablo_adi, $exclude_tables)) {
				$tablolar[] = $tablo_adi;
			}
		}

		return $tablolar;
	} catch (PDOException $e) {
		error_log("Tablolar listelenirken hata oluştu: " . $e->getMessage());
		return [];
	}
}

function tableExists(PDO $pdo, string $tableName): bool {
	if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
		return false;
	}

	try {
		$pdo->query("SELECT 1 FROM $tableName LIMIT 1");
		return true;
	} catch (PDOException $e) {
		return false;
	}
}

function columnExists(PDO $pdo, string $tableName, string $columnName): bool {
	if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName) || !preg_match('/^[a-zA-Z0-9_]+$/', $columnName)) {
		return false;
	}
	try {
		$stmt = $pdo->prepare("
		SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column
		");
		$stmt->execute([
			':table' => $tableName,
			':column' => $columnName
		]);

		return $stmt->fetchColumn() > 0;
	} catch (PDOException $e) {
		return false;
	}
}


function tablo_kopyala(PDO $pdo, $tableName, $rowId, $excludeColumns = []) {
	$stmt = $pdo->prepare("
		SELECT COLUMN_NAME 
		FROM INFORMATION_SCHEMA.COLUMNS 
		WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table
	");
	$stmt->execute([':table' => $tableName]);
	$columnNames = $stmt->fetchAll(PDO::FETCH_COLUMN);
	$columnNames = array_diff($columnNames, $excludeColumns);

	$stmt = $pdo->prepare("SELECT * FROM $tableName WHERE no = :id");
	$stmt->execute([':id' => $rowId]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($row) {
		$columnsString = implode(", ", $columnNames);
		$values = [];
		$placeholders = [];
		foreach ($columnNames as $col) {
			$values[":$col"] = $row[$col];
			$placeholders[] = ":$col";
		}

		$placeholdersString = implode(", ", $placeholders);

		$insertSql = "INSERT INTO $tableName ($columnsString) VALUES ($placeholdersString)";
		$stmt = $pdo->prepare($insertSql);
		$stmt->execute($values);

		return $pdo->lastInsertId();
	} else {
		return false;
	}
}


function db_data_tipi(PDO $pdo, $tablo, $kolon_adi, $vt = false, $output = 'str') {
	if ($vt === false) {
		$stmt = $pdo->query("SELECT DATABASE()");
		$vt = $stmt ? $stmt->fetchColumn() : false;
	}

	if ($vt) {
		$query = "SELECT DATA_TYPE, COLUMN_TYPE
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table AND COLUMN_NAME = :column";

		$stmt = $pdo->prepare($query);
		$stmt->execute([
			':schema' => $vt,
			':table' => $tablo,
			':column' => $kolon_adi
		]);

		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$result) return false;

		if ($output === 'array') {
			$length = null;

			if (preg_match('/\(([^)]+)\)/', $result['COLUMN_TYPE'], $matches)) {
				$parantez_ici = $matches[1];

				if (strpos($parantez_ici, ',') !== false) {
					$length = (int) explode(',', $parantez_ici)[0];
				} else {
					$length = (int) $parantez_ici;
				}
			}

			return [
				'DATA_TYPE'   => $result['DATA_TYPE'],
				'COLUMN_TYPE' => $result['COLUMN_TYPE'],
				'RAW_LENGTH'  => $length
			];
		} else {
			return $result['DATA_TYPE'];
		}
	}

	return false;
}


function db_data_names(PDO $pdo, $tablo, $exclude_fields = []) {
	try {
		$stmt = $pdo->query("SELECT DATABASE()");
		$vt = $stmt ? $stmt->fetchColumn() : false;

		if (!$vt) {
			return false;
		}

		$query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
				 WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table";
		
		$stmt = $pdo->prepare($query);
		$stmt->execute([
			':schema' => $vt,
			':table' => $tablo
		]);
		
		$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
		
		return array_values(array_diff($columns, $exclude_fields));
		
	} catch (PDOException $e) {
		error_log("PDO Error in db_data_names(): " . $e->getMessage());
		return false;
	}
}

function db_kolonlar_yaz(PDO $pdo, string $tablo) {
    return implode(", ", db_data_names($pdo, $tablo));
}


function parse_field($input, $default_value = null) {
	$parts = explode(':', $input, 2);
	$parts = array_pad($parts, 2, $default_value);

	$parts[0] = ($parts[0] === '') ? $default_value : $parts[0];
	$parts[1] = ($parts[1] === '') ? $default_value : $parts[1];

	return $parts;
}

function dosya_yorum_aciklama_satiri($dosya) {
	if (!is_file($dosya)) {
		return '';
	}
	$icerik = strtr(file_get_contents($dosya), ['&#45;' => '-']);
	if (preg_match('/<!--\s*aciklama:\s*(.*?)\s*-->/s', $icerik, $eslesmeler)) {
		return trim($eslesmeler[1]);
	}
	return '';
}

function bos_tablo_olustur(PDO $pdo, string $tablo, array $additional_columns = []) {
	$stmt = $pdo->prepare("SHOW TABLES LIKE :table");
	$stmt->execute([':table' => $tablo]);
	if ($stmt->rowCount() > 0) {
		return true;
	}
	$columns_sql = "`no` INT(11) AUTO_INCREMENT PRIMARY KEY";

	if (!empty($additional_columns)) {
		foreach ($additional_columns as $column_name => $definition) {
			$columns_sql .= ", `$column_name` $definition";
		}
	}
	$sql = "CREATE TABLE `$tablo` ($columns_sql) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
	return $pdo->exec($sql) !== false;
}


function alter_table_add_column_sql(string $table, string $column, string $type, ?string $length = null, ?string $default_value = null): string {
	$type = strtoupper($type);
	$defaults = [
		'INT'      => 11,
		'TINYINT'  => 1,
		'SMALLINT' => 4,
		'DECIMAL'  => 10,
		'VARCHAR'  => 255,
	];

	if (in_array($type, ['TEXT', 'DATETIME', 'DATE'])) {
		$type_sql = $type;
	} elseif ($type === 'DECIMAL') {
		if (is_numeric($length)) {
			$len = (int)$length;
		} else {
			error_log("alter_table_add_column: Geçersiz uzunluk değeri '$length' DECIMAL için, varsayılan kullanılıyor.");
			$len = $defaults['DECIMAL'];
		}
		$type_sql = "DECIMAL($len,2)";
	} else {
		if (is_numeric($length)) {
			$len = (int)$length;
		} else {
			if (!isset($defaults[$type])) {
				$type = 'INT';
				$len = 11;
				error_log("alter_table_add_column: Geçersiz veri tipi: $type");
			} else {
				$len = $defaults[$type];
				error_log("alter_table_add_column: Geçersiz uzunluk değeri '$length' $type için, varsayılan kullanılıyor.");
			}
		}
		$type_sql = "$type($len)";
	}
	
	// Handle default value
	$default_clause = "DEFAULT NULL";
	if ($default_value !== null) {
		// For string types, quote the default value
		if (in_array($type, ['VARCHAR', 'TEXT', 'DATE', 'DATETIME'])) {
			$default_value = "'" . addslashes($default_value) . "'";
		}
		// For numeric types, ensure it's a valid number
		elseif (in_array($type, ['INT', 'TINYINT', 'SMALLINT', 'DECIMAL'])) {
			if (!is_numeric($default_value)) {
				error_log("alter_table_add_column: Geçersiz varsayılan değer '$default_value' $type için, NULL kullanılıyor.");
				$default_value = null;
			}
		}
		
		$default_clause = $default_value !== null ? "DEFAULT $default_value" : "DEFAULT NULL";
	}

	return "ALTER TABLE `$table` ADD COLUMN `$column` $type_sql $default_clause;";
}

function alter_table_drop_column_sql(string $table, string $column): string {
    return "ALTER TABLE `$table` DROP COLUMN `$column`;";
}




function json_decode_safe($json, bool $associative = false, $default = null) {
	if ($json === null || $json === '') {
		return $default;
	}
	if (!is_string($json)) {
		return $default;
	}
	$decoded = json_decode($json, $associative);
	if (json_last_error() !== JSON_ERROR_NONE) {
		return $default;
	}
	if ($default !== null && is_object($default) && is_object($decoded)) {
		foreach ($default as $key => $value) {
			if (!isset($decoded->$key)) {
				$decoded->$key = $value;
			}
		}
	}
	if ($default !== null && is_array($default) && is_array($decoded)) {
		foreach ($default as $key => $value) {
			if (!isset($decoded[$key])) {
				$decoded[$key] = $value;
			}
		}
	}

	return $decoded;
}

function rezervasyon_bilgi_decode($json) {
	$array = json_decode($json, true);
	$array = (!is_array($array)) ? [] : $array;
	return new JSONVeri($array);
}


function json_temizle($data) {
	$forbiddenCharacters = ["\x00", "\x01", "\x02", "'", "<?", "?>"];
	if (is_object($data)) {
		foreach($data as $key => $value) {
			$data -> $key = json_temizle($value);
		}
	} elseif(is_array($data)) {
		foreach($data as $key => $value) {
			$data[$key] = json_temizle($value);
		}
	} elseif(is_string($data)) {
		$data = str_replace($forbiddenCharacters, '', $data);
	}
	return $data;
}

// Bu fonksiyon, PHP dizisini alır ve HTML attribute formatında yazar. Eğer 'class' anahtarı varsa, 'class_attr_ek' parametresini de ekler.
function json_key_value_attr_yaz($json, $class_attr_ek) {
    if (empty($json)) { return ''; }
    $yaz = ''; 
    $class_attr_ek = empty($class_attr_ek) ? '' : ' ' . $class_attr_ek; // class_attr_ek boşsa boş string, değilse ekle.
    foreach ($json as $k => $v) {
        if ($k == 'class' || $v != '') { // class veya boş olmayan değer varsa
            $yaz .= ' ' . $k . '="' . (($k === 'class') ? $v . $class_attr_ek : $v) . '"'; // class için özel işlem, diğerleri için basit.
        }
    }
    return $yaz;
}

// Bu fonksiyon, PHP dizisini alır ve HTML attribute formatında yazar. Boş olmayan anahtar-değer çiftleri yazılır.
function json_dizi_eleman_attr_yaz($json_eleman, $yaz = '') {
    if (!empty($json_eleman)) {

		if (is_string($json_eleman)) {
			$decoded = json_decode($json_eleman, true);
			if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
				$json_eleman = $decoded;
			} else {
				return $yaz;
			}
		}
		
        foreach ($json_eleman as $jek => $jev) {
            if (!empty($jev)) {
                $yaz .= ' ' . $jek . '="' . $jev . '"';
            }
        }
    }
    return $yaz;
}

function json_item_sayi($jsonText) {
    $data = json_decode($jsonText, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return 0;
    }

    return count($data);
}

function json_eleman_ekle($jsonText, $yeni_anahtar, $yeni_deger) {
    $arr = json_decode($jsonText, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($arr)) {
        $arr = [];
    }

    if (!array_key_exists($yeni_anahtar, $arr)) {
        $arr[$yeni_anahtar] = $yeni_deger;
    }

    return json_encode($arr, JSON_UNESCAPED_UNICODE);
}

function set_json_db(PDO $pdo, $db_table, $table_field, $keys_and_values = [], $no = 0) {
	$json_set_parts = [];
	$params = [];
	$i = 0;
	foreach ($keys_and_values as $key => $value) {
		$json_path = '$.' . $key;
		$json_set_parts[] = "JSON_SET({$table_field}, :json_path{$i}, :value{$i})";
		$params[":json_path{$i}"] = $json_path;
		$params[":value{$i}"] = $value;
		$i++;
	}

	$json_set_expr = implode(", ", $json_set_parts);
	$sql = "UPDATE {$db_table} SET {$table_field} = {$json_set_expr} WHERE id = :id";
	$params[":id"] = (int)$no;
	$stmt = $pdo->prepare($sql);
	return $stmt->execute($params);
}

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

function local_test(){
	return getenv('APP_ENV') === 'local';
}

function parseUserAgent($ua) {
	if (!$ua) return '';
	$browsers = ['Edge'=>'Edg\/','Opera'=>'OPR\/','Brave'=>'Brave\/','Chrome'=>'Chrome\/','Firefox'=>'Firefox\/','Safari'=>'Safari\/','IE'=>'MSIE|Trident'];
	$oses = ['Windows 10'=>'Windows NT 10.0','Windows 8.1'=>'Windows NT 6.3','Windows 7'=>'Windows NT 6.1','macOS'=>'Mac OS X','iOS'=>'iPhone|iPad','Android'=>'Android','Linux'=>'Linux'];

	$browser = $os = '';
	foreach ($browsers as $name => $pattern) {
		if (preg_match("/$pattern/i", $ua)) {
			$browser = $name;
			break;
		}
	}	
	foreach ($oses as $name => $pattern) {
		if (stripos($ua, $pattern) !== false) {
			$os = $name; break;
		}
	}
	return trim("$browser, $os", ', ');
}

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool {
        $needleLen = strlen($needle);
        if ($needleLen === 0) {
            return true;
        }
        return substr($haystack, -$needleLen) === $needle;
    }
}



function addPrimary(PDO $pdo): void
{
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE 'no'");
        $stmt->execute();
        $noColumn = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$noColumn) {
            continue; // 'no' sütunu yok
        }

        $isAutoIncrement = strpos($noColumn['Extra'], 'auto_increment') !== false;

        // PRIMARY KEY hangisi?
        $stmt = $pdo->prepare("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
        $stmt->execute();
        $primaryKey = null;
        if ($pk = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $primaryKey = $pk['Column_name'];
        }

        // Zaten tam istediğimiz gibi mi?
        if ($isAutoIncrement && $primaryKey === 'no') {
            continue;
        }

        echo "[$table] işleniyor...\n";

        // Başka bir PRIMARY KEY varsa, kaldır
        if ($primaryKey && $primaryKey !== 'no') {
            echo "  - PRIMARY KEY '$primaryKey' kaldırılıyor\n";
            $pdo->exec("ALTER TABLE `$table` DROP PRIMARY KEY");
        }

        // 'no' sütunu INT değilse hata alırsın — bu satırı geliştirmek istersen bildir

        // Artık PRIMARY KEY yoksa ekle
        if (!$primaryKey || $primaryKey !== 'no') {
            echo "  - 'no' sütununa AUTO_INCREMENT PRIMARY KEY atanıyor\n";
            $pdo->exec("ALTER TABLE `$table` MODIFY `no` INT NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`no`)");
        } else {
            echo "  - 'no' zaten PRIMARY KEY, sadece AUTO_INCREMENT ekleniyor\n";
            $pdo->exec("ALTER TABLE `$table` MODIFY `no` INT NOT NULL AUTO_INCREMENT");
        }
    }

    echo "Tüm işlemler tamamlandı.\n";
}



function dosyalari_birlestir_yaz ($dosyalar = [], $yeni_dosya = '', $sikistir = false) {
	$dosyalar = (array) $dosyalar;

	if (!empty($dosyalar)) {
		$birlestirilmis_icerik = "\xEF\xBB\xBF"; /*BOM karakteri UTF-8*/
		$dosya_icerikler = [];

		foreach ($dosyalar as $dosya) {
			$dosya = str_replace(LOCAL, ROOT, $dosya);
			if (file_exists($dosya)) {
				$dosya_icerik = file_get_contents($dosya);
				$dosya_hash = md5($dosya_icerik);

				if (!in_array($dosya_hash, $dosya_icerikler)) {
					$birlestirilmis_icerik .= "/*" . preg_replace('/(.+\/)(.+)\/(.+)/', "$2.$3", $dosya) . "*/\n" . $dosya_icerik . "\n";
					$dosya_icerikler[] = $dosya_hash;
				}
			}
		}

		if ($sikistir) {
			$birlestirilmis_icerik = preg_replace('/[\t\r\n]+|([^\S\r\n])\h+/', '$1', $birlestirilmis_icerik);
		}

		if ($birlestirilmis_icerik !== "\xEF\xBB\xBF") {
			file_put_contents($yeni_dosya, $birlestirilmis_icerik, FILE_TEXT);
			return true;
		} else {
			return false;
		}
	}
}

function sayi_option_html_yaz(
	int $baslangic, 
	int $bitis, 
	int $selected_numara = 0, 
	string $option0yazi = '', 
	string $sirala = 'asc',
	int $step = 1
): string {
	$step = max(1, $step);
	$options = [];
	if ($option0yazi !== '') {
		$options[] = sprintf(
			'<option value="0">%s</option>', 
			htmlspecialchars($option0yazi, ENT_QUOTES, 'UTF-8')
		);
	}

	$range = match($sirala) {
		'desc' => range(max($baslangic, $bitis), min($baslangic, $bitis), $step),
		default => range(min($baslangic, $bitis), max($baslangic, $bitis), $step)
	};

	foreach ($range as $i) {
		$selected = $i === $selected_numara ? ' selected' : '';
		$options[] = sprintf('<option value="%d"%s>%d</option>', $i, $selected, $i);
	}

	return implode('', $options);
}



/**
 * TCMB'den döviz kurlarını çeker
 * @deprecated Artık doviz_kur_yonetici class'ı kullanın: Global_::$doviz_kur->kurlari_guncelle()
 * @param string $kurlar Boşlukla ayrılmış kur kodları (örn: 'USD EUR')
 * @return string|false JSON formatında kurlar veya hata durumunda false
 */
function tcmbkur(string $kurlar = 'USD EUR'): string|false {
	// Bu fonksiyon artık kullanılmamalı, class içinde private olarak mevcut
	error_log('DEPRECATED: tcmbkur() fonksiyonu kullanıldı. Global_::$doviz_kur kullanın.');
	
	// Fallback için basit implementation
	try {
		libxml_use_internal_errors(true);
		$xml = simplexml_load_file('https://www.tcmb.gov.tr/kurlar/today.xml');
		
		if ($xml === false) {
			libxml_clear_errors();
			return false;
		}

		$kurbirim = '';
		$kurlar_array = array_filter(explode(" ", trim($kurlar)));
		
		if (empty($kurlar_array)) {
			$kurlar_array = ['USD'];
		}

		foreach ($kurlar_array as $kur) {
			foreach ($xml->Currency as $Currency) {
				if ((string)$Currency['Kod'] === $kur) {
					$kur_DS = (string)$Currency->BanknoteSelling;
					$kurbirim .= '"' . $kur . '":"' . $kur_DS . '",';
					break;
				}
			}
		}

		return empty($kurbirim) ? false : '{' . rtrim($kurbirim, ",") . '}';
		
	} catch (Exception $e) {
		error_log('TCMB kur çekme hatası: ' . $e->getMessage());
		return false;
	}
}


/**
 * Döviz kurlarını günceller (24 saatte bir)
 * @deprecated Artık Global_::$doviz_kur->kurlari_guncelle() kullanın
 * @return bool Güncelleme yapıldıysa true, yapılmadıysa false
 */
function doviz_kurlari_guncelle(): bool {
	if (isset(Global_::$doviz_kur)) {
		return Global_::$doviz_kur->kurlari_guncelle();
	}
	
	error_log('DEPRECATED: doviz_kurlari_guncelle() kullanıldı ama Global_::$doviz_kur başlatılmamış!');
	return false;
}


/**
 * Veritabanından döviz kurunu getirir
 * @deprecated Artık Global_::$doviz_kur->kur('dolar') kullanın
 * @param string $pb Para birimi ('dolar' veya 'euro')
 * @return float|null Kur değeri veya bulunamadıysa null
 */
function doviz_kuru_getir(string $pb = 'dolar'): ?float {
	if (isset(Global_::$doviz_kur)) {
		return Global_::$doviz_kur->kur($pb);
	}
	
	error_log('DEPRECATED: doviz_kuru_getir() kullanıldı ama Global_::$doviz_kur başlatılmamış!');
	return null;
}


/**
 * Para birimi çevirme fonksiyonu
 * @deprecated Artık Global_::$doviz_kur->fiyat_pb_cevir() kullanın
 * @param float|int|string $fiyat Çevrilecek fiyat
 * @param string $pb_from Kaynak para birimi ('tl', 'dolar', 'euro')
 * @param string $pb_to Hedef para birimi ('tl', 'dolar', 'euro')
 * @return string Formatlanmış fiyat (2 ondalık basamak)
 */
function fiyat_pb_cevir(float|int|string $fiyat, string $pb_from, string $pb_to): string {
	if (isset(Global_::$doviz_kur)) {
		return Global_::$doviz_kur->fiyat_pb_cevir($fiyat, $pb_from, $pb_to);
	}
	
	error_log('DEPRECATED: fiyat_pb_cevir() kullanıldı ama Global_::$doviz_kur başlatılmamış!');
	return number_format(round((float)$fiyat, 2), 2, '.', '');
}


/**
 * Fiyatı kullanıcının seçtiği para birimine çevirir
 * Global_::$pb değişkenini kullanır (kullanıcının tercih ettiği para birimi)
 * 
 * @param float|int|string $fiyat Çevrilecek fiyat
 * @param string $pb_from Kaynak para birimi ('tl', 'dolar', 'euro')
 * @param bool $simge_ekle Para birimi simgesi eklensin mi (₺, $, €)
 * @return string Formatlanmış fiyat
 */
function fiyat_pb_kullanici(float|int|string $fiyat, string $pb_from = 'tl', bool $simge_ekle = true): string {
	$pb_to = Global_::$pb ?? 'tl'; // Kullanıcının seçtiği para birimi
	
	if (isset(Global_::$doviz_kur)) {
		$cevrilen_fiyat = Global_::$doviz_kur->fiyat_pb_cevir($fiyat, $pb_from, $pb_to);
		
		if ($simge_ekle) {
			$simgeler = ['tl' => '₺', 'dolar' => '$', 'euro' => '€'];
			$simge = $simgeler[$pb_to] ?? '';
			return $simge . $cevrilen_fiyat;
		}
		
		return $cevrilen_fiyat;
	}
	
	return number_format(round((float)$fiyat, 2), 2, '.', '');
}


/**
 * Para birimi simgesini döndürür
 * @param string|null $pb Para birimi ('tl', 'dolar', 'euro'). Null ise Global_::$pb kullanır
 * @param bool $html HTML entity kullan mı (&#8378; gibi)
 * @return string Para birimi simgesi
 */
function pb_simge(?string $pb = null, bool $html = false): string {
	$pb = $pb ?? Global_::$pb ?? 'tl';
	return Global_::$pb_bilgi->simge($pb, $html);
}

/**
 * Para birimi ismini döndürür
 * @param string|null $pb Para birimi ('tl', 'dolar', 'euro'). Null ise Global_::$pb kullanır
 * @param bool $ingilizce İngilizce isim mi
 * @return string Para birimi ismi
 */
function pb_isim(?string $pb = null, bool $ingilizce = false): string {
	$pb = $pb ?? Global_::$pb ?? 'tl';
	return Global_::$pb_bilgi->isim($pb, $ingilizce);
}

/**
 * Para birimi ISO kodu döndürür (TRY, USD, EUR)
 * @param string|null $pb Para birimi ('tl', 'dolar', 'euro'). Null ise Global_::$pb kullanır
 * @return string ISO 4217 kodu
 */
function pb_kod(?string $pb = null): string {
	$pb = $pb ?? Global_::$pb ?? 'tl';
	$pb_obj = Global_::$pb_bilgi->get($pb);
	return $pb_obj->kod ?? 'TRY';
}

/**
 * Para birimi Font Awesome ikonu döndürür
 * @param string|null $pb Para birimi ('tl', 'dolar', 'euro'). Null ise Global_::$pb kullanır
 * @return string Font Awesome class (fas fa-lira-sign gibi)
 */
function pb_fa_icon(?string $pb = null): string {
	$pb = $pb ?? Global_::$pb ?? 'tl';
	$pb_obj = Global_::$pb_bilgi->get($pb);
	return $pb_obj->fa_icon ?? 'fas fa-lira-sign';
}

/**
 * Para birimi detaylı bilgisini döndürür
 * @param string|null $pb Para birimi ('tl', 'dolar', 'euro'). Null ise Global_::$pb kullanır
 * @return object|null Para birimi detaylı bilgisi
 */
function pb_detay(?string $pb = null): ?object {
	$pb = $pb ?? Global_::$pb ?? 'tl';
	return Global_::$pb_bilgi->get($pb);
}

/**
 * Array'i XML formatına dönüştürür
 * @param array $data Dönüştürülecek veri dizisi
 * @param string $rootElement Kök element adı (zorunlu)
 * @param SimpleXMLElement|null $xml İç kullanım için (recursive)
 * @return string XML çıktısı
 */
function arrayToXml(array $data, string $rootElement, ?SimpleXMLElement $xml = null): string {
	if ($xml === null) {
		$xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><{$rootElement}></{$rootElement}>");
	}
	
	foreach ($data as $key => $value) {
		if (is_array($value)) {
			if (is_numeric($key)) {
				$key = 'item';
			}
			$subnode = $xml->addChild($key);
			arrayToXml($value, $rootElement, $subnode);
		} else {
			if (is_numeric($key)) {
				$key = 'item';
			}
			$xml->addChild($key, htmlspecialchars((string)$value));
		}
	}
	
	return $xml->asXML();
}

/**
 * Tema dosyası için otomatik yedek oluşturur
 * @param string $tema_adi Tema adı
 * @param string $dosya_adi Dosya adı (örn: index.html)
 * @param string $dosya_icerik Dosya içeriği
 * @return string|false Yedek dosya yolu veya false
 */
function tema_dosya_yedek_al($tema_adi, $dosya_adi, $dosya_icerik) {
	$yedek_dizin = TEMA_DIR . '/.backups/' . $tema_adi . '/' . dirname($dosya_adi);
	$dosya_adi_temiz = basename($dosya_adi);
	
	// Dizin oluştur
	if (!is_dir($yedek_dizin)) {
		mkdir($yedek_dizin, 0755, true);
	}
	
	// Tarih-saat ile yedek dosya adı
	$tarih = date('Y-m-d_H-i-s');
	$yedek_dosya = $yedek_dizin . '/' . $dosya_adi_temiz . '.' . $tarih . '.bak';
	
	// Yedeği kaydet
	if (file_put_contents($yedek_dosya, $dosya_icerik) !== false) {
		// Eski yedekleri temizle (son 10 yedeği tut)
		tema_dosya_eski_yedekleri_temizle($tema_adi, $dosya_adi, 10);
		return $yedek_dosya;
	}
	
	return false;
}

/**
 * Tema dosyası için yedek listesini döndürür
 * @param string $tema_adi Tema adı
 * @param string $dosya_adi Dosya adı
 * @return array Yedek listesi (tarih, dosya_yolu)
 */
function tema_dosya_yedek_listesi($tema_adi, $dosya_adi) {
	$yedek_dizin = TEMA_DIR . '/.backups/' . $tema_adi . '/' . dirname($dosya_adi);
	$dosya_adi_temiz = basename($dosya_adi);
	$yedekler = [];
	
	if (!is_dir($yedek_dizin)) {
		return $yedekler;
	}
	
	$dosyalar = glob($yedek_dizin . '/' . $dosya_adi_temiz . '.*.bak');
	
	foreach ($dosyalar as $yedek_dosya) {
		$dosya_adi_full = basename($yedek_dosya);
		// Dosya adından tarihi çıkar (örn: index.html.2025-01-23_14-30-45.bak)
		if (preg_match('/\.(\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})\.bak$/', $dosya_adi_full, $matches)) {
			$tarih_str = str_replace('_', ' ', $matches[1]);
			$tarih_str = str_replace('-', ':', substr($tarih_str, 11)); // Saat kısmını düzenle
			$tarih_str = substr($matches[1], 0, 10) . ' ' . $tarih_str; // Tarih + Saat
			
			$yedekler[] = [
				'tarih' => $matches[1],
				'tarih_okunabilir' => $tarih_str,
				'dosya_yolu' => $yedek_dosya,
				'boyut' => filesize($yedek_dosya)
			];
		}
	}
	
	// Tarihe göre sırala (en yeni en üstte)
	usort($yedekler, function($a, $b) {
		return strcmp($b['tarih'], $a['tarih']);
	});
	
	return $yedekler;
}

/**
 * Mevcut tema bloklarını JSON formatında yedekler
 * @param string $tema_adi Tema adı
 * @return string|false Yedek dosya yolu veya false
 */
function tema_blok_yedek_al($tema_adi) {
	global $pdo, $do_;
	
	try {
		$stmt = $pdo->prepare("SELECT * FROM {$do_}blok_html WHERE tema = :tema");
		$stmt->execute(['tema' => $tema_adi]);
		$current_blocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		if (empty($current_blocks)) return false;
		
		$backup_dir = TEMA_DIR . '/' . $tema_adi . '/.backups';
		if (!is_dir($backup_dir)) mkdir($backup_dir, 0755, true);
		
		$tarih = date('Ymd_His');
		$backup_file = $backup_dir . '/blocks_' . $tarih . '.json';
		
		if (file_put_contents($backup_file, json_encode($current_blocks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false) {
			return $backup_file;
		}
	} catch (Exception $e) {
		error_log("tema_blok_yedek_al error: " . $e->getMessage());
	}
	
	return false;
}

/**
 * Temaya ait blok yedeklerini (JSON) listeler
 * @param string $tema_adi Tema adı
 * @return array Yedek listesi
 */
function tema_blok_yedek_listesi($tema_adi) {
	$backup_dir = TEMA_DIR . '/' . $tema_adi . '/.backups';
	$yedekler = [];
	
	if (!is_dir($backup_dir)) return $yedekler;
	
	$dosyalar = glob($backup_dir . '/blocks_*.json');
	foreach ($dosyalar as $dosya) {
		$ad = basename($dosya);
		// blocks_20250123_143045.json
		if (preg_match('/blocks_(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})\.json/', $ad, $m)) {
			$tarih_okunabilir = "{$m[3]}.{$m[2]}.{$m[1]} {$m[4]}:{$m[5]}:{$m[6]}";
			$yedekler[] = [
				'ad' => $ad,
				'tarih_okunabilir' => $tarih_okunabilir,
				'dosya_yolu' => $dosya,
				'boyut' => filesize($dosya)
			];
		}
	}
	
	usort($yedekler, fn($a, $b) => strcmp($b['ad'], $a['ad']));
	return $yedekler;
}

/**
 * Blok yedeğini (JSON) geri yükler
 * @param string $tema_adi Tema adı
 * @param string $yedek_ad Yedek dosya adı (blocks_*.json)
 * @return bool
 */
function tema_blok_yedek_geri_yukle($tema_adi, $yedek_ad) {
	global $pdo, $do_;
	
	$yedek_yolu = TEMA_DIR . '/' . $tema_adi . '/.backups/' . $yedek_ad;
	if (!file_exists($yedek_yolu)) return false;
	
	$icerik = file_get_contents($yedek_yolu);
	$bloklar = json_decode($icerik, true);
	if (empty($bloklar)) return false;
	
	try {
		$pdo->beginTransaction();
		
		// Mevcut blokları sil
		$stmt = $pdo->prepare("DELETE FROM {$do_}blok_html WHERE tema = :tema");
		$stmt->execute(['tema' => $tema_adi]);
		
		// Yedekten ekle
		foreach ($bloklar as $blok) {
			// 'no' alanını auto-increment'e bırakmak için unset yapabiliriz veya mevcut no ile ekleyebiliriz
			// Genelde ID çakışması olmaması için unset yapmak daha güvenli ama bloklar arası referans varsa korunmalı
			unset($blok['no']); 
			
			$columns = implode(", ", array_keys($blok));
			$placeholders = ":" . implode(", :", array_keys($blok));
			$sql = "INSERT INTO {$do_}blok_html ({$columns}) VALUES ({$placeholders})";
			
			$pdo->prepare($sql)->execute($blok);
		}
		
		$pdo->commit();
		return true;
	} catch (Exception $e) {
		if ($pdo->inTransaction()) $pdo->rollBack();
		error_log("tema_blok_yedek_geri_yukle error: " . $e->getMessage());
		return false;
	}
}

/**
 * Tema dosyası yedeğini geri yükler
 * @param string $tema_adi Tema adı
 * @param string $dosya_adi Dosya adı
 * @param string $yedek_tarih Yedek tarihi (örn: 2025-01-23_14-30-45)
 * @return bool Başarılı mı?
 */
function tema_dosya_yedek_geri_yukle($tema_adi, $dosya_adi, $yedek_tarih) {
	$yedek_dizin = TEMA_DIR . '/.backups/' . $tema_adi . '/' . dirname($dosya_adi);
	$dosya_adi_temiz = basename($dosya_adi);
	$yedek_dosya = $yedek_dizin . '/' . $dosya_adi_temiz . '.' . $yedek_tarih . '.bak';
	
	if (!is_file($yedek_dosya)) {
		return false;
	}
	
	$yedek_icerik = file_get_contents($yedek_dosya);
	$hedef_dosya = TEMA_DIR . '/' . $tema_adi . '/' . $dosya_adi;
	
	// Geri yüklemeden önce mevcut dosyanın yedeğini al
	if (is_file($hedef_dosya)) {
		$mevcut_icerik = file_get_contents($hedef_dosya);
		tema_dosya_yedek_al($tema_adi, $dosya_adi, $mevcut_icerik);
	}
	
	return file_put_contents($hedef_dosya, $yedek_icerik) !== false;
}

/**
 * Eski yedekleri temizler (belirtilen sayıdan fazla yedeği siler)
 * @param string $tema_adi Tema adı
 * @param string $dosya_adi Dosya adı
 * @param int $tutulacak_sayi Kaç yedek tutulacak (varsayılan: 6)
 * @return int Silinen yedek sayısı
 */
function tema_dosya_eski_yedekleri_temizle($tema_adi, $dosya_adi, $tutulacak_sayi = 6) {
	$yedekler = tema_dosya_yedek_listesi($tema_adi, $dosya_adi);
	$silinen = 0;
	
	if (count($yedekler) > $tutulacak_sayi) {
		$silinecekler = array_slice($yedekler, $tutulacak_sayi);
		foreach ($silinecekler as $yedek) {
			if (is_file($yedek['dosya_yolu'])) {
				unlink($yedek['dosya_yolu']);
				$silinen++;
			}
		}
	}
	
	return $silinen;
}

/**
 * HTML içeriğinin yapısal bütünlüğünü kontrol eder.
 * @param string $content HTML içeriği
 * @return bool|string True dönerse geçerli, string dönerse hata mesajı
 */
function validate_layout_structure(string $content) {

	if (strlen(trim($content)) < 200) {
		return 'Dosya içeriği boş veya 200 karakterden kısa olamaz.';
	}

	if (preg_match('/(?<!\{)\{\$\:/', $content)) {
		return 'Kritik Hata: Template değişkeni hatalı açılmış. Doğru kullanım: {{$:degisken}}';
	}

	if (preg_match('/\{\{\$\:[^}]+\}(?!\})/', $content, $m)) {
		return 'Kritik Hata: Template değişkeni hatalı kapatılmış → ' . $m[0];
	}

	$if_count    = preg_match_all('/\{\{__if%/', $content);
	$endif_count = preg_match_all('/__endif\}\}/', $content);

	if ($if_count !== $endif_count) {
		return 'Kritik Hata: Koşullu bloklar ({{__if}} / __endif) eşleşmiyor.';
	}

	if (stripos($content, '<html') !== false && stripos($content, '</html>') === false) {
		return 'Kritik Hata: <html> etiketi kapatılmamış.';
	}

	if (stripos($content, '<body') !== false && stripos($content, '</body>') === false) {
		return 'Kritik Hata: <body> etiketi kapatılmamış.';
	}

	$clean = $content;

	$clean = preg_replace('/<\?(php)?[\s\S]*?\?>/i', '', $clean);

	// Template placeholder’ları çıkar (basit temizlik, multiline kapalı)
    // Multiline açılırsa ([\s\S]), template tagleri arasındaki HTML'i yutabilir.
	$clean = preg_replace('/\{\{.*?\}\}/', '', $clean);

	libxml_use_internal_errors(true);

	$dom = new DOMDocument();
    // UTF-8 karakter desteği için meta tag ekle
	$wrapper = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $clean . '</body></html>';
	
    $dom->loadHTML($wrapper, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

	foreach (libxml_get_errors() as $error) {
		// Opening and ending tag mismatch
		if ($error->code === 76) {
			libxml_clear_errors();
			return 'HTML Etiket Hatası: Açılan ve kapanan etiketler eşleşmiyor. (Satır: ' . ($error->line - 1) . ')';
		}
	}

	libxml_clear_errors();

	return true;
}

/**
 * Split SQL file into individual queries
 */
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
        
        if ($char === "'") {
            // Check for '' (escaped quote in SQL)
            if ($inString && $i + 1 < $len && $sql[$i+1] === "'") {
                $token .= "''";
                $i++; // Skip next quote
                continue;
            }
            $inString = !$inString;
        }
        
        if (!$inString && $char === $delimiter) {
            if (trim($token) !== "") {
                $queries[] = trim($token);
            }
            $token = "";
        } else {
            $token .= $char;
        }
    }
    
    if (trim($token) !== "") {
        $queries[] = trim($token);
    }
    
    return $queries;
}

/**
 * Convert MySQL SQL to SQLite compatible SQL
 */
function mysqlToSqlite($sql) {
    // 0. Preliminary: Handle MySQL-style string escapes for SQLite
    $sql = preg_replace_callback("/'([^'\\\\]*(?:\\\\.[^'\\\\]*)*)'/s", function($m) {
        $str = $m[1];
        // Unescape MySQL characters to SQL/SQLite standard
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
    
    // Remove MySQL COMMENTs
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
            $sql = preg_replace_callback('/(CREATE TABLE (?:IF NOT EXISTS )?[`"]?'.$table.'[`"]?\s*\()(.*?)\);/is', function($m) use ($col) {
                $content = $m[2];
                if (stripos($content, 'PRIMARY KEY') === false) {
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
                    if (strpos($table_sql, 'PRIMARY KEY AUTOINCREMENT') === false) {
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

/**
 * Tema index.html dosyasını kritik kurallarla doğrular.
 */
function tema_index_dogrula(string $tema_adi): array {
    $index_path = TEMA_DIR . '/' . $tema_adi . '/index.html';

    if (!is_file($index_path)) {
        return ['ok' => false, 'reason' => 'index_file_missing', 'index_path' => $index_path];
    }

    if (!is_readable($index_path)) {
        return ['ok' => false, 'reason' => 'index_file_not_readable', 'index_path' => $index_path];
    }

    $content = @file_get_contents($index_path);
    if ($content === false) {
        return ['ok' => false, 'reason' => 'index_file_read_failed', 'index_path' => $index_path];
    }

    $trimmed = trim($content);
    if ($trimmed === '' || strlen($trimmed) < 200) {
        return ['ok' => false, 'reason' => 'index_file_too_short_or_empty', 'index_path' => $index_path];
    }

    if (!preg_match('/<!DOCTYPE\s+html\b/i', $content)) {
        return ['ok' => false, 'reason' => 'doctype_missing', 'index_path' => $index_path];
    }

    $required_tokens = [
        '{{i:yon:header}}',
        '{{i:yon:footer}}',
        '{{i:yon:kolon1}}',
        '{{$:local}}',
        '{{$:title}}',
    ];

    foreach ($required_tokens as $token) {
        if (strpos($content, $token) === false) {
            return ['ok' => false, 'reason' => 'required_token_missing:' . $token, 'index_path' => $index_path];
        }
    }

    return ['ok' => true, 'reason' => 'ok', 'index_path' => $index_path];
}

/**
 * Tema fallback olaylarını error_log dosyasına yazar.
 */
function tema_fallback_log(string $event, array $context = []): void {
    $payload = [
        'event' => $event,
        'time' => date('c'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'url' => $_SERVER['REQUEST_URI'] ?? '',
        'context' => $context
    ];

    $line = '[tema_fallback] ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    error_log($line);
}

/**
 * TEMABU için uygun bir tema yolu seçer: aktif tema veya fallback.
 * Global_::$tema_fallback_uyari flag'ini set eder.
 */
function tema_dogrula_ve_fallback(string $aktif_tema): string {
    $fallback_tema = '.sabitlenmis_tema';
    
    // Aktif temayı doğrula
    $aktif_kontrol = tema_index_dogrula($aktif_tema);
    
    if ($aktif_kontrol['ok']) {
        // Aktif tema geçerli
        Global_::set('tema_fallback_uyari', false);
        return TEMA_DIR . '/' . $aktif_tema;
    }
    
    // Aktif tema başarısız, fallback'e düş
    $fallback_kontrol = tema_index_dogrula($fallback_tema);
    
    if ($fallback_kontrol['ok']) {
        // Fallback tema başarılı
        tema_fallback_log('fallback_used', [
            'active_theme' => $aktif_tema,
            'fallback_theme' => $fallback_tema,
            'reason' => $aktif_kontrol['reason']
        ]);
        Global_::set('tema_fallback_uyari', true);
        return TEMA_DIR . '/' . $fallback_tema;
    }
    
    // Critical fail: her iki tema da başarısız
    tema_fallback_log('critical_fail', [
        'active_theme' => $aktif_tema,
        'active_reason' => $aktif_kontrol['reason'],
        'fallback_theme' => $fallback_tema,
        'fallback_reason' => $fallback_kontrol['reason']
    ]);
    Global_::set('tema_fallback_uyari', true);
    
    // Acil durumda minimal yazı dön (en azından HTTP 200 verelim)
    return TEMA_DIR . '/' . $fallback_tema;
}


if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($string, $encoding = 'UTF-8') {
        $first = mb_substr($string, 0, 1, $encoding);
        return mb_convert_case($first, MB_CASE_UPPER, $encoding) . mb_substr($string, 1, null, $encoding);
    }
}

