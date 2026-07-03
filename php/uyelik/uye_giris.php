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

			$logo_dosya = 'assets/img/logo/logo.png';
			$logo = (defined('LOCAL') && is_file(ROOT . '/' . $logo_dosya)) ? LOCAL . '/' . $logo_dosya : '';

			$sy = new sablon_yaz();
			$sy->dosya_icerik(R_PHP . '/mail_sablon/uyelik_aktivasyon.html');
			$sy->vars = [
				'__degisken' => [
					'adi' => $adi,
					'aktivasyon_link' => LOCAL . '/uis/ua?akodu=' . $aktivasyon_kodu,
					'site_adi' => $so_->d('site_adi'),
					'logo' => $logo,
				],
				'__if' => [
					'adi_var' => !empty($adi),
					'logo_var' => !empty($logo),
				]
			];
			$body = $sy->render();
			$subject = yc("Hesabını aktifleştir") . ' — ' . $so_->d('site_adi');
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
    <div class="ug-wrapper">
        <div class="ug-grid">

            <div class="ug-box loginForm">
                <h2 class="ug-title">'.yc("Üye girişi").'</h2>
                <form method="POST" action="uis/uyegiris">
                ' . (set_dolu("redirect_uri","g") ? '<input type="hidden" class="ldevam" name="ldevam" value="'.urlencode($_GET["redirect_uri"]).'"/>' : '') . '
                    <div class="ug-card">
                        <div class="ug-card-body">
                            <div class="ug-field">
                                <label class="ug-label" for="kullaniciadi">'.yc("E-posta adresi").'</label>
                                <input type="text" id="kullaniciadi" name="kullaniciadi" class="ug-input" placeholder="'.yc("E-posta adresi").'" required>
                            </div>
                            <div class="ug-field">
                                <label class="ug-label" for="sifre">'.yc("Şifre").'</label>
                                <input type="password" id="sifre" name="sifre" class="ug-input" placeholder="'.yc("Şifre").'" required>
                            </div>
                            <div class="ug-field ug-row-between">
                                <div class="ug-check">
                                    <input type="checkbox" name="beni_hatirla" class="ug-check-input" id="beni_hatirla">
                                    <label class="ug-check-label" for="beni_hatirla">'.yc("Beni hatırla").'</label>
                                </div>
                                <a href="uis/sifrereset" class="ug-link">'.yc("Şifre resetle").'</a>
                            </div>
							
                            <input type="text" name="webunion" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;height:0;width:0;opacity:0;" maxlength="10">
							<input type="hidden" name="webunion_time" value="' . time() . '">

                            <div class="ug-field">
                                <input type="submit" name="gonder_uyegiris" value="'.yc("Giriş yap").'" class="ug-btn ug-btn--primary ug-btn--block" />
                            </div>
                            <p class="ug-text-center">
                                <a href="#" class="goRegister ug-link">'.yc("Yeni üyelik oluştur").'</a>
                            </p>
                        </div>

                        <div id="hiddenGoogleBtn" class="dyok"></div>
                        <div class="ug-google-wrapper">
                            <a class="google_login_buton">'.yc("Google ile giriş yap").'</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Yeni Üyelik -->
            <div class="ug-box registerForm dyok">
                <h2 class="ug-title">'.yc("Yeni üyelik").'</h2>
                <form method="POST" action="uis/uyegiris">
                    <div class="ug-card">
                        <div class="ug-card-body">
                            <div class="ug-field">
                                <label class="ug-label" for="adi">'.yc("Adınız Soyadınız").' *</label>
                                <input type="text" id="adi" name="adi" class="ug-input" maxlength="110" placeholder="'.yc("Adınız Soyadınız").'" required>
                            </div>
                            <div class="ug-field">
                                <label class="ug-label" for="emailadresi">'.yc("E-posta adresiniz").' *</label>
                                <input type="email" id="emailadresi" name="emailadresi" class="ug-input" maxlength="100" placeholder="'.yc("E-posta adresiniz").'" required>
                            </div>
                            <div class="ug-field">
                                <label class="ug-label" for="sifre_n">'.yc("Yeni şifre").' *</label>
                                <input type="password" id="sifre_n" name="sifre" class="ug-input" maxlength="100" placeholder="'.yc("Yeni şifre").'" required>
                            </div>
                            <div class="ug-field">
                                <label class="ug-label" for="sifre_tekrar">'.yc("Yeni Şifre Tekrar").' *</label>
                                <input type="password" id="sifre_tekrar" name="sifretekrar" class="ug-input" maxlength="100" placeholder="'.yc("Yeni Şifre Tekrar").'" required>
                            </div>

							<input type="text" name="webunion" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;height:0;width:0;opacity:0;" maxlength="10">
							<input type="hidden" name="webunion_time" value="' . time() . '">

                            <div class="ug-field">
                                <input type="submit" name="yenikayit" value="'.yc("Kayıt ol").'" class="ug-btn ug-btn--danger ug-btn--block" />
                            </div>
                            <p class="ug-text-center">
                                <a href="#" class="goLogin ug-link">'.yc("Zaten üye misiniz? Giriş yapın").'</a>
                            </p>
                        </div>

                        <div class="ug-google-wrapper">
                            <a class="google_login_buton">'.yc("Google ile kayıt ol").'</a>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
    ';

}

