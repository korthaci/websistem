<?php if ( ! defined('otoban')) exit('vad.');

if (! n0_($u_no__)) {
	return;
}

if (isset($_POST['kaydet_sekme'])) {

	$_icerik			= html_d($_POST['icerik']);

	try {
		$stmt = $pdo->prepare("UPDATE {$do_}sekme SET 
								icerik = :icerik, 
								WHERE no = :no");
		
		$kayit_guncelle = $stmt->execute([
			':icerik' => $_icerik,
			':no' => $n
		]);
		
		if ($kayit_guncelle) {
			echo '✓ ' . yc("Kaydedildi.");
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
	//no,url,hash,adi,icerik,manset,sira,yayin,aciklama,etiket

	$stmt = $pdo->prepare("SELECT * FROM {$do_}sekme WHERE no = :no");
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

	$form_satir1 .= '<a class="dib uis-btn uis-btn-sm uis-btn-outline-secondary" href="'. $sb_->url . '" target="_blank">'.yc("Önizleme").'</a> &nbsp; &nbsp; 
	<a class="dib uis-btn uis-btn-sm uis-btn-outline-primary" href="'.href('index','ui=sekmeler').'">'.yc("Tüm sekmeler").'</a> &nbsp; &nbsp; ';
	$form_satir1 .= '<span class="dib">' . yc("Yayın") . ' <span class="db01 ok'.$sb_->yayin.'" data-nta="'.sifrele($sb_->no.',,sekme,,yayin').'">'.$sb_->yayin.'</span></span> &nbsp; &nbsp; &nbsp; ';
	$form_satir1 .= '<input name="kaydet_sekme" type="submit" value="'.yc("Kaydet").'" class="dib uis-btn uis-btn-sm uis-btn-warning" />';

	$form_eleman['adi'] = 
	'<label class="form-label">'.yc("Sekme adı").'</label>' . 
	$form->input_text($sb_->adi, [
		'name' => 'adi', 
		'class' => 'dbtext g_1__ form-control ceviri',
		'data-ceviridil' => $so_->d('yabanci_dil') ? 'adi,sekme,' . $sb_->no : null,
		'data-nta' => sifrele($sb_->no . ',,sekme,,adi'),
		'maxlength' => 140,
		'placeholder' => yc("Konu başlığı"),
	]);
	$form_eleman['hash'] = 
	'<label class="form-label">Hash</label>' . 
	$form->input_text($sb_->adi, [
		'name' => 'hash', 
		'class' => 'dbtext g_1__ form-control',
		'data-nta' => sifrele($sb_->no . ',,sekme,,hash'),
		'maxlength' => 140,
		'placeholder' => 'hash'
	]);

	$form_eleman['aciklama'] = 
	'<label class="form-label">'.yc("Açıklama").'</label>' . 
	$form->input_text($sb_->aciklama, [
		'name' => 'aciklama', 
		'class' => 'dbtext g_1__ form-control ceviri',
		'data-ceviridil' => 'aciklama,sekme,' . $sb_->no,
		'data-nta' => sifrele($sb_->no . ',,sekme,,aciklama'),
		'maxlength' => 250,
		'placeholder' => yc("Sekme açıklamaları"),
	]);
	$form_eleman['etiket'] = 
	'<label class="form-label">'.yc("Etiketler").'</label>' . 
	$form->input_text($sb_->etiket, [
		'name' => 'etiket', 
		'class' => 'dbtext g_1__ form-control',
		'data-nta' => sifrele($sb_->no . ',,sekme,,etiket'),
		'maxlength' => 250,
		'placeholder' => yc("Etiketler"),
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
		'data-inline' => '1',
		'data-ceviridil' => 'icerik,sekme,' . $sb_->no,
	]);

	$form_eleman['manset'] = yc("Manşet") . ' : <span class="db01 var'.$sb_->manset.'" data-nta="' . sifrele($sb_->no . ',,sekme,,manset').'">'.$sb_->manset.'</span>';

	//$form_eleman['icerik_sayfada'] = yc("İçerik sayfada görüntülensin") . ' : <span class="db01 var'.$sb_->icerik_sayfada.'" data-nta="' . sifrele($sb_->no . ',,sekme,,icerik_sayfada').'">'.$sb_->icerik_sayfada.'</span>';


	echo '
	<form name="sekme" action="' . href('','ui=sekmeduzenle&n=' . $n) . '" method="POST" id="formtextsekme" class="form1"> 
	<div class="uis-container uis-mt-4">
		<div class="uis-row">
			<div class="uis-col-12 uis-flex">
				<div>' . $form_satir1 . '</div>
				<div class="ms-auto">' . $form_eleman['manset'] . ' ' . $form_eleman['icerik_sayfada'] . '</div>
			</div>
			<div class="uis-col-lg-9">
			' . $form_eleman['adi'] . '
			</div>
			<div class="uis-col-lg-3">
			' . $form_eleman['hash'] . '
			</div>
			<div class="uis-col-12">
			' . $form_eleman['icerik'] . '
			' . $form_eleman['aciklama'] . '
			' . $form_eleman['etiket'] . '
			</div>
		</div>
	</div>
	</form>
	';
} else {
	
}
