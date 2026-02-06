<?php if (! defined('otoban')) exit('Vad.');

if (isset($_POST['yenikayit']) && set_dolu('emailadresi','p') && set_dolu('sifre','p')) {

	$adi					= isset($_POST['adi']) ? z($_POST['adi']) : '';
	$emailadresi			= isset($_POST['emailadresi']) ? email_duzenle($_POST['emailadresi']) : '';
	$sifre					= password_hash(trim($_POST['sifre']), PASSWORD_DEFAULT);
	$aktivasyon_kodu		= bin2hex(random_bytes(16));

	if (!empty($emailadresi)) {

		$uyeler_insert = [
			'adi' => $adi,
			'email' => $emailadresi,
			'k_adi' => $emailadresi,
			'sifre' => $sifre,
			'yetki_no' => 5,
			'aktivasyon' => $aktivasyon_kodu,
			'yayin' => 0,
			'tarih' => 'NOW()',
			'son_giris' => 'NOW()',
		];

		if (pdo_insert($pdo, $do_.'uyeler', $uyeler_insert)) {

		$yeni_kno = $pdo->lastInsertId();

			$body = '<html><body><br/>' . yc("Hoşgeldiniz") . ' '. $adi.',<br/><br/><a href="'.LOCAL.'/uis/ua?akodu='.$aktivasyon_kodu.'">'.yc("Hesabını aktifleştir").'</a></body></html>';
			$subject = $adi . ' ' . yc("Aktivasyon kodu").' - '.$so_->d('site_adi') . ' ' . date("Y-m-d H:i");
			Global_::$phpmailer->gonder([$emailadresi], $subject, $body, SMTP_K_ADI, 'bcc');
			
				if(Global_::$phpmailer->gonderildi == true) {
					echo '<div class="umesaj __1">'.
					yc("E-posta adresinize aktivasyon kodu gönderildi").'. ! ' . yc("Üyelik işlemi henüz tamamlanmadı"). '<br><br>'.
					yc("Spam, Gereksiz, Gelen Kutusunu kontrol etmeyi unutmayın").
					'</div><br/>';
				} else {
					if (!empty($aktivasyon_kodu)) { 
						kayit_sil_($pdo, $do_.'uyeler', $yeni_kno);
						autoi_fix($pdo, $do_.'uyeler');						
					}
					echo yc("Aktivasyon kodu gönderilemedi");
				}
		} else {
			echo '! '.yc("Kaydedilemedi").'. '.yc("Bir hata oluştu").'. '.yc("Lütfen daha sonra tekrar deneyin").'.';
		}
	} else {
		echo '! '.yc("Kaydedilemedi").'.';
	}
}

if (n0_($u_no__)) {

	if (isset($_POST['gonder_uyegiris']) && syetki([2])) {
		$location_uri = LOCAL . '/ui/dashboard';
		header("Location: $location_uri", true, 303);
	}
	
	echo '
	<div class="container">
		<div class="columns mt-5">
			<div class="column is-6">
				<aside class="menu">
					<ul class="menu-list">
						<li>
							<a href="' . href('index', 'uis=bilgileriduzenle') . '">
								<i class="fas fa-user-edit mr-2"></i> ' . yc("Bilgileri düzenle") . '
							</a>
						</li>
						<li>
							<a href="' . href('index', 'uis=sifredegistir') . '">
								<i class="fas fa-key mr-2"></i> ' . yc("Şifre değiştir") . '
							</a>
						</li>
						<li>
							<a class="uye_cikis">
								<i class="fas fa-sign-out-alt mr-2"></i> ' . yc("Güvenli çıkış") . '
							</a>
						</li>
					</ul>
				</aside>
			</div>

			<div class="column is-6">
				<div class="uye_islemler">
				</div>
			</div>
		</div>
	</div>';


} elseif (!isset($_POST['yenikayit'])) {


	echo '
	<div class="container d-flex align-items-center pt-2">
		<div class="row justify-content-center w-100">

			<div class="col-lg-5 col-md-6 loginForm">
				<h2 class="h2 text-uppercase mb-4">'.yc("Üye girişi").'</h2>
				<form method="POST" action="uis/uyegiris">
				' . (set_dolu("redirect_uri","g") ? '<input type="hidden" class="ldevam" name="ldevam" value="'.urlencode($_GET["redirect_uri"]).'"/>' : '') . '
					<div class="card">    
						<div class="card-body">
							<div class="mb-3">
								<label class="form-label" for="kullaniciadi">'.yc("E-posta adresi").'</label>
								<input type="text" id="kullaniciadi" name="kullaniciadi" class="form-control" placeholder="'.yc("E-posta adresi").'" required>
							</div>
							<div class="mb-3">
								<label class="form-label" for="sifre">'.yc("Şifre").'</label>
								<input type="password" id="sifre" name="sifre" class="form-control" placeholder="'.yc("Şifre").'" required>
							</div>
							<div class="mb-3 d-flex justify-content-between align-items-center">
								<div class="form-check">
									<input type="checkbox" name="beni_hatirla" class="form-check-input" id="beni_hatirla">
									<label class="form-check-label" for="beni_hatirla">'.yc("Beni hatırla").'</label>
								</div>
								<a href="uis/sifrereset" class="text-decoration-none">'.yc("Şifre resetle").'</a>
							</div>
							<input type="text" name="webunion" value="" class="form-control dyok" maxlength="10" placeholder="Web *">
							<div class="d-grid mb-3">
								<input type="submit" name="gonder_uyegiris" value="'.yc("Giriş yap").'" class="button is-primary" />
							</div>
							<p class="text-center mb-0">
								<a href="#" class="goRegister text-decoration-none">'.yc("Yeni üyelik oluştur").'</a>
							</p>

						</div>

						<div id="hiddenGoogleBtn" class="dyok"></div>
						<div class="google-login-wrapper mt-5 mb-5">							
							<a class="google_login_buton">'.yc("Google ile giriş yap").'</a>
						</div>

					</div>
					
				</form>
				
			</div>


			<!-- Yeni Üyelik -->
			<div class="col-lg-5 col-md-6 registerForm dyok">
				<h2 class="h2 text-uppercase mb-4">'.yc("Yeni üyelik").'</h2>
				<form method="POST" action="uis/uyegiris">
					<div class="card">    
						<div class="card-body">
							<div class="mb-3">
								<label class="form-label" for="adi">'.yc("Adınız Soyadınız").' *</label>
								<input type="text" id="adi" name="adi" class="form-control" maxlength="110" placeholder="'.yc("Adınız Soyadınız").'" required>
							</div>
							<div class="mb-3">
								<label class="form-label" for="emailadresi">'.yc("E-posta adresiniz").' *</label>
								<input type="email" id="emailadresi" name="emailadresi" class="form-control" maxlength="100" placeholder="'.yc("E-posta adresiniz").'" required>
							</div>
							<div class="mb-3">
								<label class="form-label" for="sifre_n">'.yc("Yeni şifre").' *</label>
								<input type="password" id="sifre_n" name="sifre" class="form-control" maxlength="100" placeholder="'.yc("Yeni şifre").'" required>
							</div>
							<div class="mb-3">
								<label class="form-label" for="sifre_tekrar">'.yc("Yeni Şifre Tekrar").' *</label>
								<input type="password" id="sifre_tekrar" name="sifretekrar" class="form-control" maxlength="100" placeholder="'.yc("Yeni Şifre Tekrar").'" required>
							</div>

							<input type="text" name="webunion" value="" class="form-control dyok" maxlength="10" placeholder="Web *">

							<div class="d-grid mb-3">
								<input type="submit" name="yenikayit" value="'.yc("Kayıt ol").'" class="button is-danger" />
							</div>

							<p class="text-center mb-0">
								<a href="#" class="goLogin text-decoration-none">'.yc("Zaten üye misiniz? Giriş yapın").'</a>
							</p>
						</div>

						<div class="google-login-wrapper mt-5 mb-5">							
							<a class="google_login_buton">'.yc("Google ile kayıt ol").'</a>
						</div>

					</div>
				</form>
			</div>

		</div>
	</div>
	';

}

