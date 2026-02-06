<?php if ( ! defined('otoban')) exit('Veriyolu açık değil.');

if (n0_($u_no__)) {

	if (isset($_POST['gonder'])) {
		$_adi	= set_dolu('adi','p') ? z($_POST['adi']) : '';
		$_gsm	= set_dolu('telefon','p') ? z($_POST['telefon']) : '';
		$_email	= set_dolu('email','p') ? email_duzenle($_POST['email']) : '';
		if (!empty($_email)) {	
			try {
				$stmt = $pdo->prepare("UPDATE {$do_}uyeler SET adi = ?, telefon = ?, email = ? WHERE no = ?");
				$kayit_guncelle = $stmt->execute([$_adi, $_gsm, $_email, $u_no__]);
				echo '<div class="mesaj1">' . ($kayit_guncelle ? yc("Kaydedildi.") : yc("Kayıt değiştirilmedi.")) . '</div>';
			} catch(PDOException $e) {
				echo '<div>' . yc("Bir hata oluştu.") . '</div>';
			}
		}
	}

	try {
		$stmt = $pdo->prepare("SELECT * FROM {$do_}uyeler WHERE no = ?");
		$stmt->execute([$u_no__]);
		$sb_ = $stmt->fetch();

		if ($sb_) {

			//$foto_local = (strpos($s->fotograf, 'https://') !== false && strpos($s->fotograf, LOCAL) === false);

			$fotobu = dosya_fe($sb_->fotograf) ? $sb_->fotograf : IMG . '/fg1.jpg';

			$foto_dizini = 'resim/_uye';

			$foto_var = is_file($sb_->fotograf ?? '');
			$foto_img_src = $foto_var ? $sb_->fotograf : IMG . '/fg1.jpg';

			$foto_yukle_yaz = '
			<label class="label">'.yc("Profil Fotoğrafı").'</label>
			<div class="g_160_ text-right">
				<img id="foto_preview_'.$sb_->no.'" 
					 src="'.$foto_img_src.'?v='.uniqid().'" 
					 alt="'.yc("Fotoğraf").'" 
					 style="cursor: pointer;" 
					 class="dropzone dropzone_img"
					 data-dd="'.$foto_dizini.'" 
					 data-yg="400"
					 data-dtip="resim" 
					 data-yazi="'.yc("Fotoğraf").'"
					 data-maxfiles="1"
					 data-accept=".png,.jpg,.jpeg"
					 data-imgyaz="1"
					 data-ow="0"
					 data-nta="'.sifrele($sb_->no.',,uyeler,,fotograf').'"
					 data-reload="1" />
					'. ( 
					$foto_var ? '
					<div data-dosyalar="" class="p-relative">
						<div class="__dd_sil" 
							data-dizin="'.dirname($foto_img_src).'" 
							data-dosya="'.basename($foto_img_src).'" 
							data-nta="'.sifrele($sb_->no.',,uyeler,,fotograf').'"
							data-sil="0"
							data-reload="1"> 
						</div>
					</div>' : ''
					)
					.'
			</div>';

			echo '<h3>' . yc("Bilgileri düzenle") . ' / '.$sb_->adi.'</h3>';
			echo '
			<div class="row">
				<div class="col-8">
					<form name="sayfa" action="uis/bilgileriduzenle" method="POST">
						<div class="field">
							<label class="label">'.yc("Adınız Soyadınız").'</label>
							<div class="control">
								<input type="text" name="adi" value="'.hs($sb_->adi).'" class="input" maxlength="100">
							</div>
						</div>

						<div class="field">
							<label class="label">'.yc("Telefon").'</label>
							<div class="control">
								<input type="text" name="telefon" value="'.hs($sb_->telefon).'" class="input" maxlength="255">
							</div>
						</div>

						<div class="field">
							<label class="label">'.yc("E-posta adresiniz").'</label>
							<div class="control">
								<input type="email" name="email" value="'.hs($sb_->email).'" class="input" maxlength="100">
							</div>
						</div>

						<div class="field mt-4">
							<div class="control">
								<input name="gonder" type="submit" value="'.yc("Değişiklikleri kaydet").'" class="button is-primary is-light">
							</div>
						</div>
					</form>
				</div>
				<div class="col-4">
					'.$foto_yukle_yaz.'
				</div>
			</div>
			
			';
		}
	} catch(PDOException $e) {
		echo '<div>' . yc("Bir hata oluştu.") . '</div>';
	}
} else {
	echo '!u';
}
