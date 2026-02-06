<?php if ( ! defined('otoban')) exit("Vad.");

if (n0_($u_no__)) {

	if (set_dolu('yenikayit','p') && set_dolu('emailadresi', 'p')) {
		$adi = (set_dolu('adi', 'p')) ? z($_POST['adi']) : '';
		$emailadresi = email_duzenle($_POST['emailadresi']);
		$tarih = date("Y-m-d H:i:s");
		$sifre = password_hash($_POST['yenisifre'], PASSWORD_DEFAULT);

		if (!empty($emailadresi)) {
			$stmt = $pdo->prepare("INSERT INTO {$do_}uyeler (adi, email, k_adi, sifre, yetki_no, aktivasyon, tarih) 
								VALUES (:adi, :email, :k_adi, :sifre, :yetki_no, NULL, :tarih)");
			$stmt->execute([
				'adi' => $adi,
				'email' => $emailadresi,
				'k_adi' => $emailadresi,
				'sifre' => $sifre,
				'yetki_no' => 5,
				'tarih' => $tarih
			]);

			header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
			exit();
		}
	}
	$form_link = 'ui=uyeler';
	$yazi_arama_value = (isset($_GET['yazi_arama'])) ? z($_GET['yazi_arama']) : '';
	$where_conditions = ['no > 0'];
	$params = [];

	if (!empty($yazi_arama_value)) {
		$where_conditions[] = "(adi LIKE :search OR email LIKE :search)";
		$params['search'] = "%{$yazi_arama_value}%";
	}

	$where_clause = implode(' AND ', $where_conditions);    

	echo '
	<div class="uis-page-header">
		<h2 class="uis-page-title"><i class="fa fa-users uis-text-p"></i> '.yc("Üyeler").'</h2>
		<div class="uis-action-group">
			<div class="yeni">
				<div class="yeniac uis-btn uis-btn-s" data-toggle=".form_uyeler"><i class="fa fa-plus uis-me-1"></i> '.yc("Yeni Üye").'</div>
				<form name="konu" action="'.href('index', $form_link).'" method="POST" class="dyok uis-flex uis-align-center uis-gap-2 form_uyeler uis-mt-2">
					<input type="text" name="adi" value="" class="uis-input uis-w-auto" maxlength="200" placeholder="'.yc("Adı Soyadı").'" required />
					<input type="email" name="emailadresi" class="uis-input uis-w-auto" placeholder="'.yc("E-posta").'" required>
					<input type="text" name="yenisifre" class="uis-input uis-w-auto" placeholder="'.yc("Şifre").'" required>
					<button type="submit" name="yenikayit" class="uis-btn uis-btn-s">'.yc("Ekle").'</button> 
				</form>
			</div>
		</div>
	</div>

	<div class="uis-action-bar">
		<form method="GET" action="'.href('index', $form_link).'" class="uis-flex uis-align-center uis-gap-2">
			<input type="hidden" name="ui" value="uyeler" />
			<input name="yazi_arama" type="text" class="uis-input uis-w-auto" value="'.$yazi_arama_value.'" placeholder="'.yc("Ara").'..." maxlength="30">
			<button type="submit" class="uis-btn uis-btn-p"><i class="fa fa-search"></i></button>
			<a href="'.href('index', $form_link).'" class="uis-btn uis-btn-outline"><i class="fa fa-sync-alt"></i></a>
		</form>
	</div>';


	$siralama = new siralama(60);
	
	$stmt = $pdo->prepare("SELECT COUNT(*) FROM {$do_}uyeler WHERE " . $where_clause);
	$stmt->execute($params);
	$uye_sayisi = $stmt->fetchColumn();
	
	$siralama->limit_sira_ = ceil($uye_sayisi / $siralama->limit2_bu);
	
	$stmt = $pdo->prepare("SELECT u.* 
						FROM {$do_}uyeler u 
						WHERE " . $where_clause . " 
						ORDER BY u.no DESC 
						LIMIT :offset, :limit");
						
	$stmt->bindValue(':offset', $siralama->limit1, PDO::PARAM_INT);
	$stmt->bindValue(':limit', $siralama->limit2_bu, PDO::PARAM_INT);
	foreach ($params as $key => $value) {
		$stmt->bindValue($key, $value);
	}
	$stmt->execute();
	$uyeler = $stmt->fetchAll(PDO::FETCH_OBJ);

	if ($uyeler) {
		echo '
		<div class=" uis-table">
			<table class="uis-table uis-w-100">
				<thead>
					<tr>
						<th class="g_20_">'.count($uyeler).'</th>
						<th>·Ad Soyad·</th>
						<th>·E-posta·</th>
						<th class="g_80_">'.yc("Son giriş").'</th>
						<th class="g_20_">'.yc("Yetki").'</th>
						<th class="g_20_" title="'.yc("Yayın").'">🏴</th>
						<th class="g_20_">x</th>
					</tr>
				</thead>
				<tbody>';

		foreach ($uyeler as $uye) {

			echo '<tr>
					<td>'.$uye->no.'</td>
					<td><a href="ui/uyeduzenle?n='.$uye->no.'">'.$uye->adi.'</a></td>
					<td>'.$uye->email.'</td>
					<td class="yazi1">'.format_tarih($uye->son_giris, "d.m.y H:i").'</td>
					<td>'.$uye->yetki_no.'</td>
					<td><span class="db01 ok' . $uye->yayin . '" data-nta="'.sifrele($uye->no.',,uyeler,,yayin').'" >' . $uye->yayin . '</span></td>
					<td><span class="sil" data-nt="'.sifrele($uye->no.',,uyeler').'"></span></td>
				</tr>';
		}
		
		echo '</tbody></table></div>';
		
		echo $siralama->siralama_yaz();
		
	} else {
		echo '<div class="uis-alert uis-alert-info">'.yc("Üye bulunmamaktadır.").'</div>';
	}
}
