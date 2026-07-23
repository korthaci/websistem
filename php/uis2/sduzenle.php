<?php if ( ! defined('otoban')) exit('vad.');

if (! n0_($u_no__)) {
	return;
}

if (isset($_POST['kaydet_sayfa'])) {

	$_icerik			= html_d($_POST['icerik']);
	$_ms_no				= intval($_POST['ms_no']);

	try {
		$stmt = $pdo->prepare("UPDATE {$do_}sayfa SET 
								ms_no = :ms_no, 
								icerik = :icerik
								WHERE no = :no");
		
		$kayit_guncelle = $stmt->execute([
			':ms_no' => $_ms_no,
			':icerik' => $_icerik,
			':no' => $n
		]);
		
		if ($kayit_guncelle) {
			echo '✓ ' . yc("Kaydedildi.");

			if (class_exists('log_db')) {
				$kullanici_adi = db_adi::get($pdo, $do_, 'uyeler', $u_no__);
				$icerik_adi = db_adi::get($pdo, $do_, 'sayfa', $n);
				$islem = "-> $icerik_adi ($kullanici_adi) tarafından değiştirilme ";

				$log_db = new log_db($pdo, $do_);
				$log_db->yaz($u_no__, 'sayfa', $n, $islem);
			}

			//echo '<div class="url_reset" data-delay="1000" data-reset-exclude=""></div>';
			header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
			exit();
		} else {
			echo '⚠ ' . yc("Kayıt değiştirilmedi");
		}
		
	} catch (PDOException $e) {
		error_log("Update error: " . $e->getMessage());
		echo '⚠ ' . yc("Kayıt değiştirilmedi");
	}
}

try {
	//no, url, ms_no, uye_no, adi, icerik, manset, resim, yorum_acik, link_target, sira, yayin, tarih, aciklama, etiket
	//icerik, resim, link_target, tarih

	$stmt = $pdo->prepare("SELECT * FROM {$do_}sayfa WHERE no = :no");
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

	$form_satir1 .= '<a class="dib uis-btn uis-btn-sm uis-btn-outline" href="'.href('index','sayfa=' . $sb_->url).'" target="_blank">'.yc("Önizleme").'</a> &nbsp; &nbsp; 
	<a class="dib uis-btn uis-btn-sm uis-btn-outline" href="'.href('index','ui=sayfalar').'">'.yc("Tüm sayfalar").'</a> &nbsp; &nbsp; ';
	$form_satir1 .= '<span class="dib">' . yc("Yayın") . ' <span class="db01 ok'.$sb_->yayin.'" data-nta="'.sifrele($sb_->no.',,sayfa,,yayin').'">'.$sb_->yayin.'</span></span> &nbsp; &nbsp; &nbsp; ';
	$form_satir1 .= '<input name="kaydet_sayfa" type="submit" value="'.yc("Kaydet").'" class="sayfakaydet dib uis-btn uis-btn-sm uis-btn-w" />';

	$form_eleman['adi'] = 
	'<label class="uis-label">' . yc("Konu başlığı") . '</label>' . 
	$form->input_text($sb_->adi, [
		'name' => 'adi', 
		'class' => 'dbtext g_1__ uis-input ceviri',
		'data-ceviridil' => $so_->d('yabanci_dil') ? 'adi,sayfa,' . $sb_->no : null,
		'data-nta' => sifrele($sb_->no . ',,sayfa,,adi'),
		'maxlength' => 140,
		'placeholder' => yc("Konu başlığı"),
	]);

	$form_eleman['ms_no'] = 
	'<label class="uis-label">'.yc("Bölüm").'</label>' . 
	$form->select(
		[0, yc("Bölüm Seç")],
		['select_adi' => 'adi', 'esitlik_deger_no' => $sb_->ms_no, 'from_tablo' => 'sekme', 'value_column' => 'no', 'db_ek' => " AND yayin = 1 ORDER BY sira ASC", 
		'recursive_kolon' => 'ust_s_no', 'recursive_indent' => '— '],
		[
			'name' => 'ms_no', 
			'class' => 'dbselect uis-select',//selectyaz
			'data-nta' => sifrele($sb_->no . ',,sayfa,,ms_no'),
			/*'data-tgetir' => 'sekme',
			'data-ngetir' => $sb_->ms_no,*/
		]
	);

	$form_eleman['aciklama'] = 
	'<label class="uis-label">'.yc("Açıklama").'</label>' . 
	$form->input_text($sb_->aciklama, [
		'name' => 'aciklama', 
		'class' => 'dbtext g_1__ uis-input ceviri',
		'data-ceviridil' => 'aciklama,sayfa,' . $sb_->no,
		'data-nta' => sifrele($sb_->no . ',,sayfa,,aciklama'),
		'maxlength' => 250,
		'placeholder' => yc("Sayfa açıklamaları"),
	]);
	$form_eleman['etiket'] = 
	'<label class="uis-label">'.yc("Etiketler").'</label>' . 
	$form->input_text($sb_->etiket, [
		'name' => 'etiket', 
		'class' => 'dbtext g_1__ uis-input ceviri',
		'data-ceviridil' => 'etiket,sayfa,' . $sb_->no,
		'data-nta' => sifrele($sb_->no . ',,sayfa,,etiket'),
		'maxlength' => 250,
		'placeholder' => yc("Etiketler"),
	]);

	$form_eleman['icerik'] = 
	'<label class="uis-label">'.yc("İçerik yazıları").'</label>' . 
	$form->textarea(html_a($sb_->icerik), [
		'name' => 'icerik', 
		'rows' => 4, 
		'cols' => 40,
		'class' => "jodit_editor dyok ceviri uis-textarea",
		'id' => 'texticerik_'.rand(),
		'style' => 'min-height:600px;',
		'data-inline' => '1',
		'data-ceviridil' => 'icerik,sayfa,' . $sb_->no,
	]);

	
	$resim_dizini = 'resim/_sayfa/'.$sb_->no.'_'. nd_md5($sb_->no);

	echo '
	<form name="sayfa" action="" method="POST" id="formtextsayfa"> 
	<div class="uis-container">
		<div class="uis-row">
			<div class="uis-col-12">' . $form_satir1 . '</div>
			<div class="uis-col-lg-8">
			' . $form_eleman['adi'] . '
			</div>
			<div class="uis-col-lg-4">
			' . $form_eleman['ms_no'] . '
			</div>
			<div class="uis-col-12">
			' . $form_eleman['icerik'] . '
			' . $form_eleman['aciklama'] . '
			' . $form_eleman['etiket'] . '
			</div>
		</div>
	</div>
	</form>

	<div class="uis-container uis-mt-4">
		<div class="uis-flex uis-justify-between uis-align-center uis-mb-4">
			<h5 class="uis-m-0">'.yc("Sayfa resimleri").'</h5>
			<div class="uis-flex uis-gap-2 uis-align-center">
				<a class="uis-btn uis-btn-outline uis-btn-sm dosya_getir" tabindex="1" 
					data-dd="'.$resim_dizini.'" 
					data-nn="'.$sb_->no.'" 
					data-t="sayfa" 
					data-a="resim" 
					data-img="1" 
					data-yaz="#resimler_sayfa" 
					data-sayi="20">'.yc("Son eklenen").'</a> 
				<a class="uis-btn uis-btn-outline uis-btn-sm dosya_getir trigger_click" tabindex="2" 
					data-dd="'.$resim_dizini.'" 
					data-nn="'.$sb_->no.'" 
					data-t="sayfa" 
					data-a="resim" 
					data-img="1" 
					data-yaz="#resimler_sayfa" 
					data-sayi="0">'.yc("Tümü").'</a> 
				<input type="text" id="foto_filtrele" placeholder="'.yc("Filtrele").'..." class="uis-input uis-w-auto uis-btn-sm"/>
			</div>
		</div>

		<div id="resimler_sayfa" 
			data-dd="'.$resim_dizini.'" 
			data-nn="'.$sb_->no.'" 
			data-t="sayfa" 
			data-a="resim">
		</div>
		<hr>
		<form enctype="multipart/form-data" class="resimyukle uis-mb-4 dropzone" 
			data-dtip="resim" 
			data-yazi="'.yc("Foto").' +"
			data-c_dosya_getir="'.$resim_dizini.'"
			data-c_dosya_gyaz="#resimler_sayfa"
			data-nn="'.$sb_->no.'" 
			data-t="sayfa" 
			data-a="resim" 
			data-img="1" 
			data-nn_t="sayfa" 
			data-nn_a="resim">
			<input type="hidden" name="islem" value="dyukle" />
			<input type="hidden" name="dd_" value="'.$resim_dizini.'" />
			<input type="hidden" name="bk" value="1" />
			<input type="hidden" name="adi" value="" />
			<input type="hidden" name="ow" value="0" />
			<input type="hidden" name="yg" value="1920" />
			<input type="hidden" name="rb" value="440" />
			<input type="hidden" name="filigran" value="0" />
		</form>		
	</div>
	';
} else {
	
}
/*
<div class="uppyUploader g_1__"
			data-dtip="resim"
			data-yazi="Resim +"
			data-c_dosya_getir="'.$resim_dizini.'"
			data-c_dosya_gyaz="#resimler_sayfa"
			data-nn="'.$sb_->no.'" 
			data-nn_t="sayfa" 
			data-nn_a="resim"
			data-dd_="'.$resim_dizini.'"
			data-bk="1"
			data-adi=""
			data-ow="0"
			data-yg="1920"
			data-rb="440"
			data-filigran="0">
		</div>
*/