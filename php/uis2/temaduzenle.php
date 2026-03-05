<?php if (!defined('otoban')) exit('vad.');

$tema_url = isset($_GET['tema']) ? z($_GET['tema']) : $so_->d('tema');
$tema_yolu = TEMA_DIR . '/' . $tema_url;

if (!is_dir($tema_yolu)) {
    echo '<div class="uis-alert uis-alert-d">'.yc("Geçersiz tema dizini.").'</div>';
    return;
}

// Güvenlik kilidi: Sabitlenmiş fallback tema hiçbir koşulda düzenlenemez.
if ($tema_url === '.sabitlenmis_tema') {
    echo '<div class="uis-alert uis-alert-d"><b>' . yc("Erişim Engellendi") . ':</b> ' . yc("Sabitlenmiş  tema güvenlik nedeniyle düzenlenemez.") . '</div>';
    return;
}

$dosyalar_raw = dosya_listele($tema_yolu, [
    'include' => ['*.html', '*.css', '*.js'],
    'exclude' => ['screenshot.*', '*__install_db_sample*'],
    'recursive' => true,
]);
sort($dosyalar_raw);

$dosyalar = [
    'html' => [],
    'css' => [],
    'js' => []
];

foreach ($dosyalar_raw as $d) {
    if (str_ends_with($d, '.html')) $dosyalar['html'][] = $d;
    elseif (str_ends_with($d, '.css')) $dosyalar['css'][] = $d;
    elseif (str_ends_with($d, '.js')) $dosyalar['js'][] = $d;
}

$secili_dosya = isset($_GET['dosya']) ? z($_GET['dosya']) : '';
$dosya_icerik = '';
$kayit_mesaj = '';

if (isset($_GET['recreate_success'])) {
    $kayit_mesaj = '<div class="uis-alert uis-alert-s">'.yc("Bloklar başarıyla temanın orijinal haline döndürüldü.").'</div>';
    echo '<div class="url_reset" data-delay="1000" data-reset-exclude="tema"></div>';
}

$recreate_notice = '';
if (isset($_GET['recreate_notice'])) {
    $recreate_notice = '
    <div class="uis-alert uis-alert-i uis-mb-3">
        <b>[!]</b> 
        '.yc("Yeni bir temaya geçtiniz! Her temanın kendine has bir yapısı olduğu için anasayfa görünümünün tam oturması adına sağ üstteki").' <b>"·Blokları Yeniden Oluştur·"</b> '.yc("butonunu kullanarak temanın varsayılan bloklarını yüklemenizi öneririz.").'
    </div>';
}

// Blokları Yeniden Oluştur işlemi
if (isset($_POST['recreate_blocks'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Güvenlik hatası: CSRF doğrulaması başarısız.');
    }

    $theme_sql_file = $tema_yolu . '/__install_db_sample/blok_html.sql';
    if (file_exists($theme_sql_file)) {
        $db_prefix = DB_PREFIX;
        $db_type = DB_TYPE;
        
        // --- GÜVENLİK YEDEĞİ ---
        // Tabloyu silmeden önce mevcut blokları yedekle (JSON formatında)
        try {
            $stmt_backup = $pdo->query("SELECT * FROM {$db_prefix}blok_html");
            $current_blocks = $stmt_backup->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($current_blocks)) {
                $backup_dir = $tema_yolu . '/.backups';
                if (!is_dir($backup_dir)) mkdir($backup_dir, 0755, true);
                $backup_file = $backup_dir . '/blocks_pre_reset_' . date('Ymd_His') . '.json';
                file_put_contents($backup_file, json_encode($current_blocks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        } catch (Exception $e) {
            // Yedekleme hatası işlemi durdurmasın
        }
        // -----------------------

        $theme_sql = file_get_contents($theme_sql_file);
        
        // Tabloyu tamamen silmek yerine o temaya ait mevcut kayıtları temizle
        try {
            $pdo->prepare("DELETE FROM {$db_prefix}blok_html WHERE tema = :tema")->execute(['tema' => $tema_url]);
        } catch (Exception $e) {
            // Tabloda 'tema' sütunu yoksa veya geçersizse yoksay (güvenlik için)
        }
        
        // SQLite dönüşümü
        if ($db_type === 'sqlite') {
            $theme_sql = mysqlToSqlite($theme_sql);
        }
        if ($db_prefix !== 'r_') {
            $theme_sql = str_replace(['`r_', ' r_'], ['`'.$db_prefix, ' '.$db_prefix], $theme_sql);
        }
        
        $theme_sql = preg_replace('/^--.*$/m', '', $theme_sql);
        $queries = splitSqlFile($theme_sql);
        
        foreach ($queries as $q) {
            if (empty(trim($q))) continue;
            // DROP ve CREATE komutlarını yoksayıp sadece INSERT komutlarını çalıştır
            if (stripos($q, 'INSERT INTO') !== false) {
                try {
                    $pdo->exec($q);
                } catch (Exception $e) {
                    // Hata durumunda devam et (örneğin Duplicate entry no:32 vs)
                }
            }
        }
        
        header('Location: ' . LOCAL .'/ui/temaduzenle?tema=' . $tema_url . '&recreate_success=1');
        exit;
    } else {
        $kayit_mesaj = '<div class="uis-alert uis-alert-d">'.yc("Temaya ait blok_html.sql dosyası bulunamadı.").'</div>';
    }
}

// Dosya kaydetme işlemi (Eğer POST varsa)
if (isset($_POST['dosya_kaydet']) && !empty($secili_dosya)) {
    // CSRF Kontrolü
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Güvenlik hatası: CSRF doğrulaması başarısız.');
    }

    $yeni_icerik = $_POST['dosya_icerik'];
    $dosya_yolu = $tema_yolu . '/' . $secili_dosya;
    
    // Korunan Dosyalar
    $protected_files = ['index.html'];
    $can_save = true;

    // Kaydetmeden önce mevcut dosyanın yedeğini al
    if (is_file($dosya_yolu)) {
        
        // Korunan dosya kontrolü
        if (in_array($secili_dosya, $protected_files)) {
            $validation_result = validate_layout_structure($yeni_icerik);
            if ($validation_result !== true) {
                $kayit_mesaj = '<div class="uis-alert uis-alert-d"><b>' . yc("Kayıt Engellendi!") . '</b> ' . $validation_result . '</div>';
                // Hata durumunda yeni içeriği editörde tut
                $dosya_icerik = $yeni_icerik; 
                $can_save = false;
            }
        }

        if ($can_save) {
            $mevcut_icerik = file_get_contents($dosya_yolu);
            tema_dosya_yedek_al($tema_url, $secili_dosya, $mevcut_icerik);
        }
    }
    
    if ($can_save) {
        if (file_put_contents($dosya_yolu, $yeni_icerik) !== false) {
            $kayit_mesaj = '<div class="uis-alert uis-alert-s">'.yc("Dosya başarıyla kaydedildi. Yedek otomatik olarak oluşturuldu.").'</div>';
        } else {
            $kayit_mesaj = '<div class="uis-alert uis-alert-d">'.yc("Dosya kaydedilirken bir hata oluştu.").'</div>';
        }

        header('Location: ' . LOCAL .'/ui/temaduzenle?tema=' . $tema_url . '&dosya=' . $secili_dosya);
        exit;
    }
}

if (!empty($secili_dosya) && is_file($tema_yolu . '/' . $secili_dosya)) {
    $dosya_icerik = file_get_contents($tema_yolu . '/' . $secili_dosya);
    
    // Açılışta sadece hiç yedek yoksa yedek al (F5 ile sürekli yedek almayı engelle)
    $mevcut_yedekler = tema_dosya_yedek_listesi($tema_url, $secili_dosya);
    if (empty($mevcut_yedekler)) {
        tema_dosya_yedek_al($tema_url, $secili_dosya, $dosya_icerik);
    }
}

// Yedek listesi (sadece dosya seçiliyse)
$yedek_listesi = [];
if (!empty($secili_dosya)) {
    $yedek_listesi = tema_dosya_yedek_listesi($tema_url, $secili_dosya);
}

echo '
<div class="uis-page-header uis-mb-1 uis-p-0">
    <h5 class="uis-m-0 uis-p-0">/ ' . ucfirst($tema_url) . ' ' . yc("Temasını Düzenle") .'</h5>
    <div class="uis-action-bar">
        <button type="button" class="uis-btn uis-btn-sm uis-btn-primary uis-btn-light tema-blok-yedek-al" data-tema="' . hs($tema_url) . '">
            <i class="fa-solid fa-cloud-upload"></i> ' . yc("Blokları Yedekle") . '
        </button>
        <button type="button" class="uis-btn uis-btn-sm uis-btn-primary tema-blok-listesi-btn" data-tema="' . hs($tema_url) . '">
            <i class="fa-solid fa-history"></i> ' . yc("Blokları Yapılandır") . '
        </button>
        <a href="' . href('index', 'ui=temalar') . '" class="uis-btn uis-btn-sm uis-btn-outline">< ' . yc("Temalara Dön") .'</a>
    </div>
</div>

' . $kayit_mesaj . '
' . $recreate_notice . '

<div class="uis-row">
    <div class="uis-col uis-col-lg-2">
        <div class="uis-table-card">
            <div class="theme-editor-sidebar-header">' . yc("Tema Dosyaları") .'</div>
            <div class="theme-file-list">';
            
            $groups = [
                'html' => ['label' => yc('Şablonlar')],
                'css' => ['label' => yc('Stiller')],
                'js' => ['label' => yc('Scriptler')]
            ];

            foreach ($groups as $ext => $info) {
                if (empty($dosyalar[$ext])) continue;
                echo '<div class="theme-file-group-label">> ' . $info['label'] . '</div>';
                foreach ($dosyalar[$ext] as $dosya) {
                    $active_class = ($dosya === $secili_dosya) ? 'active' : '';
                    echo '<a href="' . href('index', 'ui=temaduzenle&tema=' . $tema_url . '&dosya=' . $dosya) . '" class="theme-file-item ' . $active_class .'">📄 ' . basename($dosya) . '</a>';
                }
            }

			/*değişkenleri tanımlama başlangıcı*/
			$tema_degiskenleri = [];
			$php_dosya = str_replace('.html', '.php', $secili_dosya);
			
			if ($secili_dosya === 'index.html') {
				$php_dosya_yolu = R_PHP . '/index1.php';
			} else {
				$php_dosya_yolu = YONLENDIR_D . '/' . $php_dosya;
			}

			if (!empty($secili_dosya) && str_ends_with($secili_dosya, '.html')) {
				if (!is_file($php_dosya_yolu)) {
					$php_dosya_alt = ltrim($php_dosya, '_');
					$php_dosya_yolu_alt = YONLENDIR_D . '/' . $php_dosya_alt;
					if (is_file($php_dosya_yolu_alt)) {
						$php_dosya_yolu = $php_dosya_yolu_alt;
					}
				}

				if (is_file($php_dosya_yolu)) {
					$php_kod = file_get_contents($php_dosya_yolu);
					
					$bolum_sinirlari = [];
					$anahtar_kelimeler = ['__degisken', '__foreach', '__if'];
					
					foreach ($anahtar_kelimeler as $ak) {
						if ($pos = strpos($php_kod, "'$ak'")) $bolum_sinirlari[$ak] = $pos;
						elseif ($pos = strpos($php_kod, "\"$ak\"")) $bolum_sinirlari[$ak] = $pos;
					}
					asort($bolum_sinirlari);
					
					$aks = array_keys($bolum_sinirlari);
					for ($i = 0; $i < count($aks); $i++) {
						$bas = $bolum_sinirlari[$aks[$i]];
						$son = isset($aks[$i+1]) ? $bolum_sinirlari[$aks[$i+1]] : strlen($php_kod);
						$snippet = substr($php_kod, $bas, $son - $bas);
						
						$target = str_replace('__', '', $aks[$i]);
						if (preg_match_all("/['\"]([^'\"]+)['\"]\s*=>/", $snippet, $vars_m)) {
							foreach($vars_m[1] as $v) {
								if ($v !== $aks[$i] && (!isset($tema_degiskenleri[$target]) || !in_array($v, $tema_degiskenleri[$target]))) {
									$tema_degiskenleri[$target][] = $v;
								}
							}
						}
					}
				}
			}

			$tema_degiskenleri_yaz = '';
			if (!empty($secili_dosya) && !empty($tema_degiskenleri)) {
				$tema_degiskenleri_yaz .= '<div class="theme-variables-bar">';
				$tema_degiskenleri_yaz .= '<b>' . yc("Değişkenler") . ':</b> ';
				
				foreach ($tema_degiskenleri as $tip => $liste) {
					foreach ($liste as $dx) {
						$prefix = ($tip == 'degisken' ? '{{$:' : ($tip == 'foreach' ? '{{__foreach %' : '{{__if%'));
						$suffix = ($tip == 'degisken' ? '}}' : '% }}');
						$tema_degiskenleri_yaz .= '<span class="theme-variable-badge panoya_kopyala" title="' . $tip . '">' . $prefix . $dx . $suffix . '</span>';
					}
				}
				
				$tema_degiskenleri_yaz .= '</div>';
			}
			/*değişkenleri tanımlama sonu*/
echo '      </div>
        </div>
    </div>
    <div class="uis-col uis-col-lg-10">
		' . $tema_degiskenleri_yaz . '
        <div class="uis-card uis-card-nopad overflow-hidden">
            <form action="' . href('index', 'ui=temaduzenle&tema=' . $tema_url . '&dosya=' . $secili_dosya) . '" method="POST">
                <input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">
                <div class="uis-flex uis-justify-between uis-align-center theme-editor-header">
                    <span class="font-weight-bold">' . ($secili_dosya ?: yc("Dosya Seçiniz")) . '</span>
                    <div class="uis-flex uis-gap-2">
                        ' . (!empty($secili_dosya) && !empty($yedek_listesi) ? '<button type="button" class="uis-btn uis-btn-sm uis-btn-outline tema-yedek-listesi-btn" data-tema="' . hs($tema_url) . '" data-dosya="' . hs($secili_dosya) . '">' . yc("Yedekler") . ' (' . count($yedek_listesi) . ')</button>' : '') . '
                        ' . (!empty($secili_dosya) ? '<button type="submit" name="dosya_kaydet" class="uis-btn uis-btn-sm uis-btn-p">' . yc("Kaydet") .'</button>' : '') . '
                    </div>
                </div>

                <textarea
                    name="dosya_icerik"
                    class="theme-editor-textarea' . (strpos($dosya_icerik,"{{") === false ? (str_ends_with($secili_dosya, '.css') ? ' format_css' : (str_ends_with($secili_dosya, '.js') ? ' format_js' : ' format_html')) : ' is-template') . '"
                    spellcheck="false"
                    autocomplete="off"
                    autocapitalize="off"
                    wrap="' . (strpos($dosya_icerik, "{{") === false ? 'off' : 'soft') . '"
                    ' . (empty($secili_dosya) ? 'disabled placeholder="'.yc("Lütfen düzenlemek için bir dosya seçin...").'"' : '') . '
                >' . hs($dosya_icerik) . '</textarea>

            </form>
        </div>

        <!-- Blok Yedekleri Paneli -->
        <div class="uis-card uis-mt-3" id="blok-yedek-listesi-panel" style="display: none;">
            <div class="uis-card-header uis-flex uis-justify-between uis-align-center">
                <h3 class="uis-card-title">** ' . yc("Blok Yapılandırma & Yedekleri") .'</h3>
                <small style="color:var(--uis-muted)">' . yc("JSON formatında saklanan anasayfa blokları") . '</small>
            </div>
            <div class="uis-card-body">
                <div class="uis-table-responsive">
                    <table class="uis-table">
                        <thead>
                            <tr>
                                <th>' . yc("Yedek Adı / Tipi") .'</th>
                                <th>' . yc("Tarih") .'</th>
                                <th>' . yc("İşlem") .'</th>
                            </tr>
                        </thead>
                        <tbody id="blok-yedek-liste-body">
                            <!-- Orijinal blok_html.sql -->
                            <tr>
                                <td><b>blok_html.sql</b> <span class="uis-badge uis-badge-info uis-ms-2">' . yc("Fabrika Verisi") . '</span></td>
                                <td>-</td>
                                <td>
                                    <form data-onsubmit-confirm="'. yc("Tüm bloklar silinecek ve temanın orijinal blokları yüklenecektir. Emin misiniz").'?" method="POST" action="' . href('index', 'ui=temaduzenle&tema=' . $tema_url) . '">
                                        <input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">
                                        <button type="submit" name="recreate_blocks" class="uis-btn uis-btn-sm uis-btn-p">
                                            ' . yc("Orijinali Yükle") . '
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        ' . (!empty($secili_dosya) && !empty($yedek_listesi) ? '
        <div class="uis-card uis-mt-3" id="yedek-listesi-panel" style="display: none;">
            <div class="uis-card-header">
                <h3 class="uis-card-title">** ' . yc("Dosya Yedekleri") .'</h3>
            </div>
            <div class="uis-card-body">
                <div class="uis-table-responsive">
                    <table class="uis-table">
                        <thead>
                            <tr>
                                <th>' . yc("Tarih") .'</th>
                                <th>' . yc("Boyut") .'</th>
                                <th>' . yc("İşlem") .'</th>
                            </tr>
                        </thead>
                        <tbody>
                            ' . implode('', array_map(function($yedek) use ($tema_url, $secili_dosya) {
                                $boyut_kb = round($yedek['boyut'] / 1024, 2);
                                return '<tr>
                                    <td>' . hs($yedek['tarih_okunabilir']) . '</td>
                                    <td>' . $boyut_kb . ' KB</td>
                                    <td>
                                        <div class="uis-flex uis-gap-1">
                                            <button type="button" class="uis-btn uis-btn-sm uis-btn-outline tema-yedek-geri-yukle" 
                                                    data-tema="' . hs($tema_url) . '" 
                                                    data-dosya="' . hs($secili_dosya) . '" 
                                                    data-tarih="' . hs($yedek['tarih']) . '">
                                                ' . yc("Geri Yükle") . '
                                            </button>
                                            <button type="button" class="uis-btn uis-btn-sm uis-btn-outline uis-btn-d tema-yedek-sil" 
                                                    data-tema="' . hs($tema_url) . '" 
                                                    data-dosya="' . hs($secili_dosya) . '" 
                                                    data-tarih="' . hs($yedek['tarih']) . '">
                                                ' . yc("Sil") . '
                                            </button>
                                        </div>
                                    </td>
                                </tr>';
                            }, $yedek_listesi)) . '
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ' : '') . '
    </div>
</div>';

echo '<script src="' . LOCAL . '/assets/js/uis2_theme.js" defer></script>';
?>
