<?php if ( ! defined('otoban')) exit('Veriyolu açık değil.');

// Handle file synchronization
if (isset($_GET['fix_file']) && set_dolu('fix_file','g')) {
	$fix_dil_kodu = z($_GET['fix_file']);
	$base_dosyasi = CEVIRI_D . '/__i18n_base.txt';
	$hedef_dosya = CEVIRI_D . '/'.$fix_dil_kodu.'.txt';
	
	if (is_file($base_dosyasi) && preg_match('/^[a-z]{2,4}$/', $fix_dil_kodu)) {
		$base_lines = file($base_dosyasi, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$hedef_lines = [];
		
		// If target file exists, preserve existing translations
		if (is_file($hedef_dosya)) {
			$existing_lines = file($hedef_dosya, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			$existing_translations = [];
			
			foreach ($existing_lines as $line) {
				$parts = explode(":::", $line);
				if (count($parts) >= 2) {
					$key = trim($parts[0]);
					$value = trim($parts[1]);
					if (!empty($key) && !empty($value)) {
						$existing_translations[$key] = $value;
					}
				}
			}
		}
		
		// Build new file content based on __i18n_base.txt structure
		foreach ($base_lines as $base_line) {
			$base_parts = explode(":::", $base_line);
			if (count($base_parts) >= 1) {
				$base_key = trim($base_parts[0]);
				if (!empty($base_key)) {
					// Use existing translation if available, otherwise empty
					$translation = isset($existing_translations[$base_key]) ? $existing_translations[$base_key] : '';
					if ($fix_dil_kodu == 'tr' && empty($translation)) $translation = $base_key;
					$hedef_lines[] = $base_key . ':::' . $translation;
				}
			}
		}
		
		// Write synchronized content
		file_put_contents($hedef_dosya, implode("\n", $hedef_lines));
		
		header("Location: " . LOCAL . "/ui/ceviri?n=" . $n, true, 303);
		exit();
	}
}

if (isset($_GET['pfile']) && preg_match('/^[a-z]{1,4}$/', $_GET['pfile']) && is_file(CEVIRI_D . '/__i18n_base.txt')) {
	copy(CEVIRI_D . '/__i18n_base.txt', CEVIRI_D . '/'.z($_GET['pfile']).'.txt');
}

try {
	$stmt = $pdo->query("SELECT no, dkod, adi, adio FROM {$do_}diller WHERE yayin = 1 
	ORDER BY CASE WHEN dkod != 'tr' THEN 0 END, adi");
	$diller = $stmt ? $stmt->fetchAll(PDO::FETCH_OBJ) : [];
} catch (PDOException $e) {
	error_log("Select languages error: " . $e->getMessage());
	$diller = [];
}

// Handle new word addition for ALL languages
if (isset($_POST['text_ceviri_yeni_gonder']) && set_dolu('yeni_text_ceviri','p')) {
	$yeni_text_ceviri = trim(trim(trim(q_($_POST['yeni_text_ceviri'])), "++"));
	if (!empty($yeni_text_ceviri)) {
		$yazi_cevir_dizi = explode("++",$yeni_text_ceviri);
		$yeni_kelimeler = [];
		foreach ($yazi_cevir_dizi as $ycd) {
			$temizlenmis_kelime = str_replace(["&#37;", "(.)", "(..)"], ["%", "%s", "%s"], q_(trim($ycd)));
			if (!empty($temizlenmis_kelime)) {
				$yeni_kelimeler[] = $temizlenmis_kelime.":::\n";
			}
		}
		
		// Add new words to ALL language files including __i18n_base.txt
		$hedef_dosya_listesi = [CEVIRI_D . '/__i18n_base.txt'];
		foreach ($diller as $dil_item) {
			$hedef_dosya_listesi[] = CEVIRI_D . '/'.$dil_item->dkod.'.txt';
		}

		foreach ($hedef_dosya_listesi as $dil_dosyasi) {
			if (is_file($dil_dosyasi)) {
				$file_lines = file($dil_dosyasi, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				$yeni_ceviri_dizi = $yeni_kelimeler;
				foreach ($file_lines as $line) {
					$yeni_ceviri_dizi[] = $line."\n";
				}
				$yeni_ceviri_dizi = array_unique($yeni_ceviri_dizi);
				$yeni_yazi = trim(implode($yeni_ceviri_dizi),"\n");
				file_put_contents($dil_dosyasi, $yeni_yazi);
			}
		}
		
		header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
		exit();
	}
}

// Get Base file line count for comparison
$base_dosyasi = CEVIRI_D . '/__i18n_base.txt';
$base_line_count = 0;
if (is_file($base_dosyasi)) {
	$base_lines = file($base_dosyasi, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$base_line_count = count($base_lines);
}

	echo '
	<div class="uis-page-header">
		<h2 class="uis-page-title"><i class="fa fa-globe uis-text-p"></i> '.yc("Çeviriler").'</h2>
		<div class="uis-action-group">' . 
			($n > 0 ? '<a class="yazi1 standart_link input_sadece_bos_alanlar" data-text="'.yc("Tüm alanlar").'">'.yc("Sadece boş alanlar").'</a> &nbsp; ' : '').'
			<input type="text" name="filtrele" class="uis-input uis-w-auto yazi_ceviri_filtrele" placeholder="'.yc("kelime ile ara").'" />
		</div>
	</div>

	<div class="uis-mb-4">';

	// Add Master Key (i18n base) link
	$master_class = ($n == -1 || empty($n)) ? 'uis-btn-p' : 'uis-btn-outline';
	echo '<a class="uis-btn uis-btn-sm '.$master_class.' uis-me-1" href="ui/ceviri?n=-1">'.yc("Master Keys").' (i18n)</a>';

	foreach ($diller as $db_dil){
		$a_class_ek = ($n == $db_dil->no) ? 'uis-btn-p' : 'uis-btn-outline';
		$ceviri_dosyasi = CEVIRI_D . '/'.$db_dil->dkod.'.txt';
		
		// Check if file exists and line count matches
		$dosya_var = is_file($ceviri_dosyasi);
		$satir_uyumu = true;
		$a_href_ek = '';
		$a_yazi_ek = '';
		
		if (!$dosya_var) {
			$a_href_ek = '&pfile='.$db_dil->dkod;
			$a_yazi_ek = ' (?)';
		} else if ($base_line_count > 0) {
			// Check line count match for all language files
			$dil_lines = file($ceviri_dosyasi, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			$dil_line_count = count($dil_lines);
			
			// Count untranslated words
			$bos_ceviri_sayisi = 0;
			foreach ($dil_lines as $dl) {
				$parts = explode(":::", $dl);
				if (isset($parts[1]) && trim($parts[1]) === "") {
					$bos_ceviri_sayisi++;
				}
			}
			if ($bos_ceviri_sayisi > 0) {
				$a_yazi_ek .= ' <span class="uis-badge uis-badge-w">⚠️ '.$bos_ceviri_sayisi.'</span>';
			}

			if ($dil_line_count != $base_line_count) {
				$satir_uyumu = false;
				$a_yazi_ek .= ' (!' . $dil_line_count . '/' . $base_line_count . ')';
				$a_class_ek = ($n == $db_dil->no) ? 'uis-btn-w' : 'uis-btn-outline';
				if ($n == $db_dil->no) $a_yazi_ek .= ' <a href="ui/ceviri?n='.$db_dil->no.'&fix_file='.$db_dil->dkod.'" class="uis-btn uis-btn-xs uis-btn-s ms-1"><i class="fa fa-sync"></i> '.yc("Sync").'</a>';
			}
		}

		echo '<a class="uis-btn uis-btn-sm '.$a_class_ek.' uis-me-1" href="ui/ceviri?n='.$db_dil->no . $a_href_ek .'">'.$db_dil->adi . $a_yazi_ek.'</a>';
	}

	echo '</div>'; // close 

	echo ((int)$n < 1 ? '
	<form action="ui/ceviri?n='.$n.'" method="POST" class="text_ceviri_yeni uis-mb-4">
		<textarea name="yeni_text_ceviri" class="uis-textarea g_98__" placeholder="To add multiple entries, insert ++ between each entry (e.g., entry1++entry2++entry3)."></textarea>
		<input type="submit" name="text_ceviri_yeni_gonder" class="uis-btn uis-btn-sm uis-btn-w" value="'.yc("Ekle").'"/>
	</form>' : '');


echo ((int)$n > 0 ? '
<div class="rside_link">
	<a class="uis-btn uis-btn-p uis-btn-sm uis-btn-outline text_ceviri_tumunu_cevir">'.yc("Tümünü çevir").'</a>
	<a class="uis-btn uis-btn-d uis-btn-sm uis-btn-outline stopScrollingInputText dyok">'.yc("Durdur").'</a>
	<a class="uis-btn uis-btn-s uis-btn-sm form_submit_link dyok" data-formid="#text_ceviri_form">'.yc("Kaydet").'</a>
	<a class="uis-btn uis-btn-s uis-btn-sm uis-btn-i" data-textarea-ceviri-id="#form_text_ceviriler">'.yc("Kaydet").'</a>
</div>' : '');
if (empty($n) || $n == -1) {
	$dil_b = (object)[
		'no' => -1,
		'dkod' => '__i18n_base',
		'adi' => yc('Master Keys')
	];
} else if ($n > 0) {
	try {
		$stmt = $pdo->prepare("SELECT no, dkod, adi FROM {$do_}diller WHERE no = :no AND yayin = 1");
		$stmt->execute([':no' => $n]);
		$dil_b = $stmt->fetch(PDO::FETCH_OBJ);
	} catch (PDOException $e) {
		error_log("Select language error: " . $e->getMessage());
		$dil_b = false;
	}
} else {
	$dil_b = false;
}

if ($dil_b) {
	$ceviri_dosya = ($dil_b->no == -1) ? (CEVIRI_D . '/__i18n_base.txt') : (CEVIRI_D . '/'.$dil_b->dkod.'.txt');
	if (is_file($ceviri_dosya)) {



		if (isset($_POST['ceviriler'])) {
			$text_bu = [];
			$ceviriler = html_d($_POST['ceviriler']);
			if (strpos($ceviriler,'____')!==false) {
				$ceviri_satirlar = explode("____", $ceviriler);
				foreach ($ceviri_satirlar as $csatir) {
					$satir = explode(":::",$csatir);
					if (isset($satir[0]) && !empty($satir[0])) {
						$satir_adi = str_replace("&#37;","%",html_d(trim($satir[0])));
						$satir_ceviri = isset($satir[1]) ? str_replace("&#37;","%",html_d(trim($satir[1]))) : '';
						$text_bu[] = $satir_adi . ':::' . $satir_ceviri . "\n";
					}
				}
				$text_bu = trim(implode($text_bu),"\n");
				file_put_contents($ceviri_dosya, $text_bu);
			}
		}

		$file_lines = file($ceviri_dosya, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		echo '
		<form name="text_ceviri_form" id="text_ceviri_form" action="ui/ceviri?n='.$n.'" method="POST">
			<textarea name="ceviriler" class="g_1__ dyok" id="form_text_ceviriler"></textarea>
		</form>';
		foreach ($file_lines as $line_no => $line) {
			$line_explode = explode(":::", $line);
			$adi = $line_explode[0];
			$ceviribu = $line_explode[1];
			$text_ceviri_buton = (empty($ceviribu) && (int)$dil_b->no > 0) ? '<span class="text_ceviri_buton" data-input-id="#c_'.$line_no.'">  </span>':'';
			$base64_adi   = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($adi));
			if ($dil_b->no != -1) {
				echo '
				<div class="input_placeholder text_ceviri g_1__" data-hedef="'.$dil_b->dkod.'">
					<input type="hidden" name="c_adi_'.$line_no.'" data-val-id="c_'.$line_no.'" value="' . $adi . '"/>
					<span class="text_ceviri_sil_buton"><a href="ui/ceviri?n='.$n.'&yazisiln='.$line_no.'&yazisil='.$base64_adi.'">x</a></span>
					'.
					$text_ceviri_buton .
					'<input type="text" id="c_'.$line_no.'" name="c_'.$line_no.'" class="g_98__" value="'.$ceviribu.'" placeholder="'. $adi . '"/>
					<label for="c_'.$line_no.'">'. $adi . '</label>
				</div>';
			}
		}
		} else {
			echo yc('Dosya bulunamadı.');
		}
	} else {
		echo yc('Dil bulunamadı.');
	}


if ($n == -1 || empty($n)) {
	/*text tr içinde olmayan kelimeler listesi */
	$directoryPath1 = ROOT . '/api';
	$directoryPath2 = R_PHP . '/bilesen';
	$directoryPath3 = R_PHP . '/modul';
	$directoryPath4 = R_PHP . '/uis2';	
	$directoryPath5 = R_PHP . '/modul';
	$directoryPath6 = R_PHP . '/yonlendir';
	$directoryPath7 = R_PHP . '/uyelik';

	$finder = new yc_kelime_tanimla();

	$patterns1 = $finder->findPatterns($directoryPath1);
	$patterns2 = $finder->findPatterns($directoryPath2);
	$patterns3 = $finder->findPatterns($directoryPath3);
	$patterns4 = $finder->findPatterns($directoryPath4);
	$patterns5 = $finder->findPatterns($directoryPath5);
	$patterns6 = $finder->findPatterns($directoryPath6);
	$patterns7 = $finder->findPatterns($directoryPath7);
	$patterns = array_unique(array_merge($patterns1, $patterns2, $patterns3, $patterns4, $patterns5, $patterns6, $patterns7));

	$var_olan_kelimeler = [];
	if (is_file(CEVIRI_D . '/__i18n_base.txt')) {
		$base_lines = file(CEVIRI_D . '/__i18n_base.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($base_lines as $line) {
			$parts = explode(":::", $line);
			if (!empty($parts[0])) {
				$var_olan_kelimeler[] = trim($parts[0]);
			}
		}
	}

	$txt_icinde_olmayan_kelimeler = $finder->diffWordLists($patterns, $var_olan_kelimeler);
	if (!empty($txt_icinde_olmayan_kelimeler)) {
		echo yc('Bulunan yeni kelimeler') . ' : ';
		echo '<div class="textarea_ekle" data-textarea-name="yeni_text_ceviri">';
		foreach ($txt_icinde_olmayan_kelimeler as $tiok) {
			echo '<span class="uis-btn uis-btn-outline uis-btn-sm uis-mt-1">' . $tiok . '</span> ';
		}
		echo '</div>';
	}
}


/*
function searchWordInFiles($directory, $word) {
    // Open the directory
    $dir = opendir($directory);

    // Iterate through each file/directory
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $filePath = $directory . '/' . $file;

            // If it's a directory, recursively search inside it
            if (is_dir($filePath)) {
                searchWordInFiles($filePath, $word);
            } else {
                // If it's a file, search for the word
                $fileContent = file_get_contents($filePath);
                if (strpos($fileContent, $word) !== false) {
                    echo "Found '$word' in file: $filePath\n<br>";
                }
            }
        }
    }

    // Close the directory
    closedir($dir);
}
searchWordInFiles(R_PHP, 'zelliker');
*/
?>