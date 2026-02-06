<?php if ( ! defined('otoban')) exit('vad.');

if (! n0_($u_no__)) {
	return;
}

if (isset($_POST['kaydet_blok'])) {

	$_icerik			= html_d($_POST['icerik']);

	try {
		$stmt = $pdo->prepare("UPDATE {$do_}blok_html SET 
								icerik = :icerik
								WHERE no = :no");
		
		$kayit_guncelle = $stmt->execute([
			':icerik' => $_icerik,
			':no' => $n
		]);
		
		if ($kayit_guncelle) {
			echo '✓ ' . yc("Kaydedildi.");

			if (class_exists('log_db')) {
				$kullanici_adi = db_adi::get($pdo, $do_, 'uyeler', $u_no__);
				$icerik_adi = db_adi::get($pdo, $do_, 'blok_html', $n);
				$islem = "-> $icerik_adi ($kullanici_adi) tarafından değiştirilme ";

				$log_db = new log_db($pdo, $do_);
				$log_db->yaz($u_no__, 'blok_html', $n, $islem);
			}


			//echo '<div class="url_reset" data-delay="1000" data-reset-exclude=""></div>';
			header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
			exit();
		} else {
			echo '! ' . yc("Kayıt değiştirilmedi");
		}
		
	} catch (PDOException $e) {
		error_log("Update error: " . $e->getMessage());
		echo '! ' . yc("Kayıt değiştirilmedi");
	}
}

try {
	$stmt = $pdo->prepare("SELECT * FROM {$do_}blok_html WHERE no = :no");
	$stmt->execute([':no' => $n]);
	$sb_ = $stmt->fetch();
} catch (PDOException $e) {
	error_log("Select error: " . $e->getMessage());
	$sb_ = false;
}

$form_eleman = [];
$form_satir1 = '';
$form = new form_c($pdo, $do_);

if ($sb_) {

	$form_satir1 .= '<a class="dib btn btn-sm btn-outline-primary" href="'.href('index','ui=bloklar').'">'.yc("Tüm bloklar").'</a> &nbsp; &nbsp; ';
	$form_satir1 .= '<span class="dib">' . yc("Yayın") . ' <span class="db01 ok'.$sb_->yayin.'" data-nta="'.sifrele($sb_->no.',,blok_html,,yayin').'">'.$sb_->yayin.'</span></span> &nbsp; &nbsp; &nbsp; ';
	$form_satir1 .= '<input name="kaydet_blok" type="submit" value="'.yc("Kaydet").'" class="dib btn btn-sm btn-warning" />';

	$form_eleman['adi'] = 
	'<label class="form-label">'.yc("Konu başlığı").'</label>' . 
	$form->input_text($sb_->adi, [
		'name' => 'adi', 
		'class' => 'dbtext g_1__ form-control ceviri',
		'data-ceviridil' => $so_->d('yabanci_dil') ? 'adi,blok_html,' . $sb_->no : null,
		'data-nta' => sifrele($sb_->no . ',,blok_html,,adi'),
		'maxlength' => 140,
		'placeholder' => yc("Adı"),
	]);

	$form_eleman['hash'] = 
	'<label class="form-label">Hash</label>' . 
	$form->input_text($sb_->hash, [
		'name' => 'hash', 
		'class' => 'dbtext g_1__ form-control',
		'data-nta' => sifrele($sb_->no . ',,blok_html,,hash'),
		'maxlength' => 140,
		'placeholder' => 'Hash',
	]);

	$form_eleman['icerik'] = 
	'<label class="form-label">'.yc("İçerik yazıları").'</label>' . 
	$form->textarea(html_a($sb_->icerik), [
		'name' => 'icerik', 
		'rows' => 4, 
		'cols' => 40,
		'class' => "jodit_editor dyok ceviri",
		'id' => 'texticerik_'.rand(),
		'style' => 'min-height:600px;',
		'data-ceviridil' => 'icerik,blok_html,' . $sb_->no,
	]);//'data-inline' => '1',

	echo '
	<form name="sayfa" action="' . href('','ui=blokduzenle&n=' . $n) . '" method="POST" id="formtextsayfa"> 
	<div class="container mt-4">
		<div class="row">
			<div class="col-12">' . $form_satir1 . '</div>
			<div class="col-lg-9">
			' . $form_eleman['adi'] . '
			</div>
			<div class="col-lg-3">
			' . $form_eleman['hash'] . '
			</div>
			<div class="col-12">
			' . $form_eleman['icerik'] . '
			</div>
		</div>
	</div>
	</form>	
	';
} else {
	
}
