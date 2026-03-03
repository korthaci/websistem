<?php if ( ! defined('otoban')) exit('Vad.');

echo '<h3>'.yc("Blok düzenleme").'</h3>';

$yeni_kayit = new yeni_kayit_insert($pdo, $do_, 'blok_html', 'yenikayit');
$yeni_kayit->insert_alanlar = ['adi'];
$yeni_kayit->ek_insert_alanlar = [
	'hash' => url_duzenle($_POST['adi'] ?? ''),
	'icerik' => '<div></div>',
	'tema' => $so_->d('tema') ?? ''
];
$yeni_kayit->debug = false;

if ($yeni_kayit->ekle()) {
	echo $yeni_kayit->islem_mesaj();
	//echo '<div class="url_reset" data-delay="1000" data-reset-exclude=""></div>';
	header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
	exit();
} else {
	echo $yeni_kayit->islem_mesaj();
}


$arama_form = new arama_formu([
	'yazi_arama' => ['type' => 'text', 'placeholder' => yc("Kelime ile ara"), 'class' => 'i1', 'db_kolon' => 'adi'],
]);
$arama_ = $arama_form->sql_where();

	echo '
	<div class="uis-page-header">
		<h2 class="uis-page-title"><i class="fa fa-th-large uis-text-p"></i> '.yc("Bloklar").'</h2>
		<div class="uis-action-group">
			<div class="yeni">
				<div class="yeniac uis-btn uis-btn-s"><i class="fa fa-plus uis-me-1"></i> '.yc("Yeni Blok").'</div>		
				<form name="konu" action="" method="POST" class="dyok uis-mt-2">
					<input type="text" name="adi" value="" class="uis-input uis-w-auto" placeholder="'.yc("Blok Başlığı").'" required />
					<button type="submit" name="yenikayit" class="uis-btn uis-btn-s">'.yc("Ekle").'</button>
				</form>
			</div>
		</div>
	</div>

	<div class="uis-action-bar">
		<form name="arama_formu" method="GET" action="" class="uis-flex uis-gap-2">
			<input type="hidden" name="ui" value="bloklar" />
			'.$arama_form->form_html().'
			<button type="submit" class="uis-btn uis-btn-p"><i class="fa fa-search"></i></button>
			<a href="'.href('index', 'ui=bloklar').'" class="uis-btn uis-btn-outline"><i class="fa fa-sync-alt"></i></a>
		</form>
	</div>';

$siralama = new siralama(200);
$kayit_sayisi = $pdo_db->var("SELECT COUNT(no) FROM {$do_}blok_html WHERE no > 0 $arama_");
$siralama->limit_sira_ = ceil($kayit_sayisi / $siralama->limit2_bu);

try {
	$limit1 = intval($siralama->limit1);
	$limit2 = intval($siralama->limit2_bu);
	$stmt = $pdo->prepare("SELECT * FROM {$do_}blok_html
		WHERE no > 0 $arama_ ORDER BY sira ASC, no DESC LIMIT $limit1, $limit2");
	$stmt->execute();
	$bloklar = $stmt->fetchAll();
} catch (PDOException $e) {
	echo '<div class="uis-alert uis-alert-d">' . $e->getMessage() . '</div>';
	$bloklar = [];
}

if (!empty($bloklar)) {

	$thead_th_dizi = [
		[$kayit_sayisi, 'g_30_'],
		[yc("Blok adı"), ''],
		['Hash', ''],
		['⇵', 'g_20_'],
		['🖉', 'g_20_',yc("Düzenle")],
		['🏴', 'g_20_ yazi1', yc("Yayın")],
		[yc("Sil"), 'g_40_'],
	];
	if ($so_->d('yabanci_dil') == 1) {
		$thead_th_dizi = diziye_ara_eleman_ekle($thead_th_dizi, 'Hash', ['文', 'g_40_', yc("Çeviri")]);
	}
	
	$thead_th = '';
	foreach ($thead_th_dizi as $th_){
		$thead_th .='<th class="'. ($th_[1] ?? '') .'"'. (isset($th_[2]) && $th_[2] ? ' title="'.$th_[2].'"':'') .'>'. ($th_[0] ?? '') .'</th>';
	}

	echo '<table class="uis-table"><thead><tr>'.$thead_th.'</tr></thead>
	<tbody class="sirala-liste" data-t="blok_html">';

	//no  adi  icerik hash tema sira yayin 
	foreach ($bloklar as $s) {
		$nt = $s->no.',,blok_html';

		echo '
		<tr id="sira_'.$s->no.'">
		<td>'.$s->no.'</td>
		<td class="yazi1"><input type="text" class="i2 dbtext uis-input"'.data_nta($nt, 'adi').' value="'.$s->adi.'"/></td>
		<td class="yazi1">'.$s->hash.'</td>' .
		( $so_->d('yabanci_dil') == 1 ? '<td class="yazi1">'. strip_tags(vcdil('icerik', 'blok_html', $s->no)) .'</td>' : '' ) . '
		<td class="stut bgcenter" title="'.$s->sira.'">&nbsp;</td>			
		<td> <a href="'.href('','ui=blokduzenle&n='.$s->no).'"><i class="fa fa-edit"></i></a></td>
		<td><span class="db01 ok'.$s->yayin.'" ' . data_nta($nt, 'yayin') . '">'.$s->yayin.'</span></td>
		<td><span class="sil" data-nt="'.sifrele($s->no.',,blok_html').'" data-sil="0"> </span></td>

		</tr>';
	}
	echo '</tbody></table>';

	echo $siralama->siralama_yaz();

} else {
	echo '<br><div class="yazi3">'.yc("Bu bölümde kayıt bulunmamaktadır").'.</div><br>';
}
