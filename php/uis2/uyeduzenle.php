<?php if ( ! defined('otoban')) exit('Vad.');

if (n0_($u_no__)) {

	echo '<h3>'.yc("Üye düzenle").'</h3>';

	// Yetki seviyeleri tanımı
	$yetki_seviyeleri = [
		2 => yc("Admin Site yöneticisi"),
		3 => yc("Site Yöneticisi"),
		4 => yc("Özel bölümler yöneticisi"),
		5 => yc("Normal üyelik")
	];

	if (isset($_POST['gonder'])) {
		$adi = (set_dolu('adi', 'p')) ? z($_POST['adi']) : '';
		$_k_adi = (isset($_POST['_k_adi']) && strlen($_POST['_k_adi']) > 1) ? z($_POST['_k_adi']) : false;
		$_sifre = set_dolu('_sifre','p') ? password_hash($_POST['_sifre'], PASSWORD_DEFAULT) : false;
		
		$_yetki_no = false;
		if (isset($_POST['yetki_no'])) {
			$_yetki_no_input = intval($_POST['yetki_no']);
			$izin_verilen_seviyeler = array_keys(array_filter($yetki_seviyeleri, function($sev_key) {
				global $u_yetki__;
				return $sev_key >= $u_yetki__;
			}, ARRAY_FILTER_USE_KEY));
			
			if (in_array($_yetki_no_input, $izin_verilen_seviyeler)) {
				$_yetki_no = $_yetki_no_input;
			}
		}
		
		try {
			$update_fields = ['adi = :adi'];
			$params = ['adi' => $adi, 'n' => $n];
			
			if ($_k_adi) {
				$update_fields[] = 'k_adi = :k_adi';
				$params['k_adi'] = $_k_adi;
			}
			
			if ($_sifre) {
				$update_fields[] = 'sifre = :sifre';
				$params['sifre'] = $_sifre;
			}
			
			if ($_yetki_no !== false) {
				$update_fields[] = 'yetki_no = :yetki_no';
				$params['yetki_no'] = $_yetki_no;
			}
			
			$sql = "UPDATE {$do_}uyeler SET " . implode(', ', $update_fields) . " WHERE no = :n";
			$stmt = $pdo->prepare($sql);
			$kayit_guncelle = $stmt->execute($params);
			
			echo $kayit_guncelle ? yc("Kaydedildi") : yc("Değiştirilmedi");
		} catch (PDOException $e) {
			error_log("Error updating user: " . $e->getMessage());
			echo yc("Bir hata oluştu.");
		}

		header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
		exit();
	}

	try {
		$stmt = $pdo->prepare("SELECT * FROM {$do_}uyeler WHERE no = :n");
		$stmt->execute(['n' => $n]);
		$sb_ = $stmt->fetch(PDO::FETCH_OBJ);

		if ($sb_) {
			$gosterilebilir_yetki_seviyeleri = array_filter($yetki_seviyeleri, function($sev_key) {
				global $u_yetki__;
				return $sev_key >= $u_yetki__;
			}, ARRAY_FILTER_USE_KEY);

			echo 
			$sb_->no.' | <a href="ui/uyeler">'.yc("Liste").'</a> | 
			'.yc("Yayın").' : <span class="db01 ok'.$sb_->yayin.'" data-nta="'.sifrele($sb_->no.',,uyeler,,yayin').'">'.$sb_->yayin.'</span> | 
			'.yc("Email alımı").' : <span class="db01 ok'.$sb_->email_alimi.'" data-nta="'.sifrele($sb_->no.',,uyeler,,email_alimi').'">'.$sb_->email_alimi.'</span><br/><br/>' .

			'<form name="sayfa" action="ui/uyeduzenle?n='.$n.'" method="POST">
				<div class="uis-container">
					<div class="uis-row">
						<div class="uis-col uis-col-md-8">
							<div class="uis-mb-3">
								<label class="uis-label">'.yc("Adı Soyadı").'</label>
								<input name="adi" class="uis-input dbtext" type="text" value="'.$sb_->adi.'" placeholder="'.yc("Adı Soyadı").'" data-nta="'.sifrele($sb_->no.',,uyeler,,adi').'" />
							</div>
							<div class="uis-mb-3">
								<br>'.yc("Telefon").'
								<div class="uis-flex uis-align-center uis-gap-2">
									<i class="fas fa-phone uis-text-muted"></i>
									<input class="uis-input dbtext" type="tel" value="'.$sb_->telefon.'" placeholder="'.yc("Telefon").'" data-nta="'.sifrele($sb_->no.',,uyeler,,telefon').'" />
								</div>
							</div>
							<div class="uis-mb-3">
								<label class="uis-label">'.yc("GSM").'</label>
								<div class="uis-flex uis-align-center uis-gap-2">
									<i class="fas fa-phone uis-text-muted"></i>
									<input class="uis-input dbtext" type="tel" value="'.$sb_->gsm.'" placeholder="'.yc("GSM").'" data-nta="'.sifrele($sb_->no.',,uyeler,,gsm').'" />
								</div>
							</div>
							<div class="uis-mb-3">
								<label class="uis-label">'.yc("Email Adresi").'</label>
								<div class="uis-flex uis-align-center uis-gap-2">
									<i class="fas fa-envelope uis-text-muted"></i>
									<input class="uis-input dbtext" type="email" value="'.$sb_->email.'" placeholder="'.yc("Email adresi").'" data-nta="'.sifrele($sb_->no.',,uyeler,,email').'" />
								</div>
							</div>
						</div>

						<div class="uis-col uis-col-md-4 uis-text-start">
							<div data-placeholder="'.yc("Kullanıcı adı").'">
							'.yc("Kullanıcı adı").':<br/>
								<input name="_k_adi" class="dblreadonly g_1__ i2 uis-input" type="text" value="'.$sb_->k_adi.'"  readonly  data-nta="'.sifrele($sb_->no.',,uyeler,,k_adi').'" />
							</div><br/>
							'.yc("Şifre").':<br/>
							<div data-placeholder="'.yc("Şifre").'">
								<input name="_sifre" class="g_1__ i2 uis-input" type="password" value=""  placeholder="'.yc("Şifre").'" />
							</div><br/>

							'.yc("Yetki").' &nbsp; 
							<select name="yetki_no" class="uis-input g_99__ i2" data-nta="'.sifrele($sb_->no.',uyeler,yetki_no').'">';
			
							foreach ($gosterilebilir_yetki_seviyeleri as $sev => $label) {
								$selected = ($sb_->yetki_no == $sev) ? 'selected' : '';
								echo '<option value="'.$sev.'" '.$selected.'> '.$sev.' - '.$label.'</option>';
							}
							
							echo '</select><br/><br/>

							'.yc("Aktivasyon").' &nbsp; 
							<input class="uis-input g_60_ i2 dbtext dbldisabled" type="text" value="'.$sb_->aktivasyon.'" placeholder="'.yc("Aktivasyon").'" readonly  data-nta="'.sifrele($sb_->no.',,uyeler,,aktivasyon').'" /><br/><br/><br/>' .
							tarih_dt($sb_->tarih).'<br/><br/>' .
							'<input name="gonder" type="submit" value="'.yc("Kaydet").'" class="uis-btn uis-btn-p" />
						</div>
					</div>
				</div>
			</form>';
		}
	} catch (PDOException $e) {
		error_log("Error fetching user: " . $e->getMessage());
		echo yc("Bir hata oluştu.");
	}
}
?>