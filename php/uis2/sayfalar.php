<?php if ( ! defined('otoban')) exit('Vad.');

if (n0_($u_no__)) {

	$yeni_kayit = new yeni_kayit_insert($pdo, $do_, 'sayfa', 'yenikayit');
	$yeni_kayit->insert_alanlar = ['adi'];
	$yeni_kayit->url_kontrol = [$do_.'sayfa', $do_.'sekme'];
	$yeni_kayit->ek_insert_alanlar = ['tarih' => date('Y-m-d')];
	$yeni_kayit->debug = false;
	
	if ($yeni_kayit->ekle()) {
		echo $yeni_kayit->islem_mesaj();
		header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
		exit();
	} else {
		echo $yeni_kayit->islem_mesaj();
	}

	// ms_no select'i için sekme listesini PDO'dan çek (arama formu oluşturulmadan önce).
	try {
		$stmt_sekme_list = $pdo->query("SELECT no, adi FROM {$do_}sekme ORDER BY sira ASC, no DESC");
		$sekme_options_raw = $stmt_sekme_list ? $stmt_sekme_list->fetchAll(PDO::FETCH_OBJ) : [];
	} catch (PDOException $e) {
		$sekme_options_raw = [];
	}
	$sekme_options = [];
	foreach ($sekme_options_raw as $sk) {
		$sekme_options[(string) $sk->no] = $sk->adi;
	}

	$arama_form = new arama_formu([
		'yazi_arama' => ['type' => 'text', 'placeholder' => yc("Kelime ile ara"), 'class' => 'i1', 'db_kolon' => 'adi'],
		//'tarih' => ['type' => 'daterange', 'placeholder' => yc("Tarih Aralığı"), 'class' => 'i1', 'db_kolon' => 'tarih'],
		'ms_no' => ['type' => 'select', 'placeholder' => yc("Bölüm").'-'.yc("Tümü"), 'options' => $sekme_options, 'db_kolon' => 'ms_no', 'class' => 'i1 g_180_ changesubmit'],
		'yayin' => ['type' => 'select', 'placeholder' => yc("Yayın").'-'.yc("Tümü"), 'options' => ['1' => yc("Yayında"),'0' => yc("Yayında Değil")], 'class' => 'i1 g_130_ changesubmit'],
	]);

	$arama_ = $arama_form->sql_where();

	echo '
	<div class="uis-page-header">
		<h2 class="uis-page-title"><i class="fa fa-file-alt uis-text-p"></i> '.yc("Sayfalar").'</h2>
		<div class="uis-action-group">
			<div class="yeni">
				<div class="yeniac uis-btn uis-btn-s"><i class="fa fa-plus uis-me-1"></i> '.yc("Yeni Sayfa").'</div>		
				<form name="konu" action="" method="POST" class="dyok uis-mt-2">
					<input type="text" name="adi" value="" class="uis-input uis-w-auto" placeholder="'.yc("Sayfa Başlığı").'" required />
					<button type="submit" name="yenikayit" class="uis-btn uis-btn-s">'.yc("Ekle").'</button>
				</form>
			</div>
		</div>
	</div>

	<div class="uis-action-bar">
		<form name="arama_formu" method="GET" action="" class="uis-flex uis-gap-2">
			<input type="hidden" name="ui" value="sayfalar" />
			'.$arama_form->form_html().'
			<button type="submit" class="uis-btn uis-btn-p"><i class="fa fa-search"></i></button>
			<a href="'.href('index', 'ui=sayfalar').'" class="uis-btn uis-btn-outline"><i class="fa fa-sync-alt"></i></a>
		</form>
	</div>';

	$siralama = new siralama(200);
	$kayit_sayisi = $pdo_db->var("SELECT COUNT(no) FROM {$do_}sayfa WHERE no > 0 $arama_");
	$siralama->limit_sira_ = ceil($kayit_sayisi / $siralama->limit2_bu);

	try {
		$limit1 = intval($siralama->limit1);
		$limit2 = intval($siralama->limit2_bu);
		$stmt = $pdo->prepare("SELECT * FROM {$do_}sayfa
			WHERE no > 0 $arama_ ORDER BY sira ASC, no DESC LIMIT $limit1, $limit2");
		$stmt->execute();
		$sayfalar = $stmt->fetchAll();
	} catch (PDOException $e) {
		echo '<div class="uis-alert uis-alert-d">' . $e->getMessage() . '</div>';
		$sayfalar = [];
	}


	if (!empty($sayfalar)) {

		$thead_th_dizi = [
			[$kayit_sayisi, 'g_30_'],
			[yc("Sayfa adı"), ''],
			[yc("Sekme"), ''],
			[yc("Manşet"), 'g_20_'],
			['🗨', 'g_40_', yc("Yorum")],
			['🖼︎', 'g_40_', yc("Resim")],
			['⇵', 'g_20_'],
			['🖉', 'g_20_',yc("Düzenle")],
			['🏴', 'g_20_ yazi1', yc("Yayın")],
			[yc("Sil"), 'g_40_'],
		];
		if ($so_->d('yabanci_dil') == 1) {
			$thead_th_dizi = diziye_ara_eleman_ekle($thead_th_dizi, '🖼︎', ['文', 'g_40_', yc("Çeviri")]);
		}
		
		$thead_th = '';
		foreach ($thead_th_dizi as $th_){
			$thead_th .='<th class="'. ($th_[1] ?? '') .'"'. (isset($th_[2]) && $th_[2] ? ' title="'.$th_[2].'"':'') .'>'. ($th_[0] ?? '') .'</th>';
		}

		echo '<table class="uis-table"><thead><tr>'.$thead_th.'</tr></thead>
		<tbody class="sirala-liste" data-t="sayfa">';

		foreach ($sayfalar as $s) {
			$resim_dizin = 'resim/_sayfa/'.$s->no.'_'. nd_md5($s->no);
			$dosya_sayisi = dosya_sayisi("$resim_dizin/k", '/(jpe?g|gif|png|webp)$/i') ?: 0;
			$sekme_adi = db_adi::get($pdo, $do_, 'sekme', $s->ms_no);
			$nt = $s->no.',,sayfa';

			echo '
			<tr id="sira_'.$s->no.'">

			<td>'.$s->no.'</td>
			<td class="yazi1">'.$s->adi.'</td>
			<td class="yazi1">'.$sekme_adi.'</td>
			<td><span class="db01 var'.$s->manset.'" '.data_nta($nt, 'manset').'>'.$s->manset.'</span></td>
			<td><span class="db01 var'.$s->yorum_acik.'" ' . data_nta($nt, 'yorum_acik') . '>'.$s->yorum_acik.'</span></td>
			<td class="fotoresimtd"'. (is_file($s->resim ?? '') ? ' style="background-image:url('.$s->resim.');"' : '') .'><span class="fotoresimsayi dosyasayisi_'.$dosya_sayisi.'">'.$dosya_sayisi.'</span></td>' .
			( $so_->d('yabanci_dil') == 1 ? '<td class="yazi1">'. strip_tags(vcdil('adi', 'sayfa', $s->no)) .'</td>' : '' ) . '
			<td class="stut bgcenter" title="'.$s->sira.'">&nbsp;</td>			
			<td> <a href="'.href('','ui=sduzenle&n='.$s->no).'"><i class="fa fa-edit"></i></a></td>
			<td><span class="db01 ok'.$s->yayin.'" ' . data_nta($nt, 'yayin') . '">'.$s->yayin.'</span></td>
			<td><span class="sil" data-nt="'.sifrele($s->no.',,sayfa').'" data-sil="0"> </span></td>

			</tr>';
		}
		echo '</tbody></table>';

		echo $siralama->siralama_yaz();

	} else {
		echo '<br><div class="yazi3">'.yc("Bu bölümde kayıt bulunmamaktadır").'.</div><br>';
	}
}
?>