<?php if ( ! defined('otoban')) exit('Vad.');

if (!syetki([2])) {
	return;
}

try {
	$sql = "SELECT url, adi FROM {$do_}bilesen WHERE yayin=1 AND no=:no";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':no', $n, PDO::PARAM_INT);
	$stmt->execute();
	$db_bilesen = $stmt->fetch(PDO::FETCH_OBJ);
} catch (PDOException $e) {
	error_log('PDO error in bilesen_ayarlar.php: ' . $e->getMessage());
	$db_bilesen = false;
}

$json_dosya = BILESEN_DIR . '/' . $db_bilesen->url . '/config.json';

if ($db_bilesen) {
	if (file_exists($json_dosya)) {
		$json_data = file_get_contents($json_dosya);
		$json_decode = json_decode($json_data, true);
		if ($json_decode !== null && json_last_error() === JSON_ERROR_NONE) {
			if (isset($_POST['kaydet'])) {
				$degisiklik_var = false;
				foreach ($json_decode as $key => $value) {
					if (set_dolu('_hash_'.$key, 'p') && empty($_POST[$key])) {
						$_POST[$key] = sifre_ac($_POST['_hash_'.$key]);
						unset($_POST['_hash_'.$key]);
					}
					if (isset($_POST[$key])) {
						if ($_POST[$key]==='sil'){
							$_POST[$key] = '';
						}
						if ($json_decode[$key]['deger'] != z($_POST[$key])) {
							$json_decode[$key]['deger'] = z($_POST[$key]);
							$degisiklik_var = true;
						}
					}
				}
				$yeni_json = json_encode($json_decode, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
				echo ($degisiklik_var && file_put_contents($json_dosya, $yeni_json)) ? yc("Değişiklik kaydedildi") : yc("Değişiklik yapılmadı");
			}
			echo '<form action="" method="POST" autocomplete="off">';
			echo '<table class="yazi1"><thead><tr><td>·Özellik·</td><td class="g_350_">'.yc("Değer").'·</td><td><input class="f_r uis-me-1 uis-btn uis-btn-sm uis-btn-warning" type="submit" name="kaydet" value="'.yc("Kaydet").'"/></td></tr></thead><tbody>';
			
			foreach ($json_decode as $key => $value) {
				if (is_array($value)) {
					$inputname	= isset($key) ? z($key) : '';
					$adi		= isset($value['adi']) ? html_d($value['adi']) : '';
					$deger		= isset($value['deger']) ? html_d($value['deger']) : '';
					$aciklama	= isset($value['aciklama']) ? z($value['aciklama']) : '';

					$inputtype = strpos($value['adi'], '(p)') !== false ? 'password' : 'text';
					$inputtype_value = $inputtype=='password' ? '' : $deger;
					$hidden_input = $inputtype=='password' ? '<input type="hidden" name="_hash_'.$inputname.'" value="'.sifrele($deger).'"/>' : '';
					$autocomplete = $inputtype=='password' ? ' autocomplete="new-password"':'';
					$placeholder_adi = $inputtype=='password' ? '••••••••': $adi;

					echo '
					<tr title="'.$inputname.'">
					<td>'.$adi.':</td>
					<td>
					<div class="prelative">
						<div class="text_password_text">
							<input type="'.$inputtype.'" name="'.$inputname.'" placeholder="'.$placeholder_adi.'" value="'.$inputtype_value.'" class="g_95__ i2" data-inputn=""'.$autocomplete.' />
						</div>'.$hidden_input.'
					</div>
					</td>
					<td>'.$aciklama.'</td>
					</tr>';
				}
			}
			echo '</tbody></table>';
			echo '</form>';
		} else {
			echo '!json';
		}
	} else {
		echo yc("Config dosyası bulunamadı.");
	}
}
