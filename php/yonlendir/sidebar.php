<?php if (! defined('otoban')) exit('!vad');
if (syetki([2, 3, 4])) {
	$yaz_sidebar = new sablon_yaz;
	$yaz_sidebar->dosya_icerik(tema_render_dizin() . '/sidebar.html');
	
	$yp_linkler_sayfa = syetki([2]) ?
	'<div class="nav-item has-submenu">
		<i class="fa fa-edit"></i> <span>'.yc("Düzenle").'</span>
	</div>
	<div class="submenu">
		<a href="ui/bloklar" class="nav-item"><i class="fa fa-list-alt"></i> <span>'.yc("Bloklar").'</span></a>
		<a href="ui/sekmeler" class="nav-item"><i class="fa fa-th-list"></i> <span>'.yc("Sekmeler").'</span></a>
		<a href="ui/sayfalar" class="nav-item"><i class="fa fa-list"></i> <span>'.yc("Sayfalar").'</span>
		</a>' .
		( isset($_GET['sayfa']) && syetki([2]) ? '
		<a href="ui/sduzenle?n='.Global_::$routeParams['id'] .'" class="nav-item"><i class="fa fa-edit"></i> <span>'.yc("Bu sayfayı düzenle").'</span></a>' : '' )
		.'
		<a href="ui/menuduzenle" class="nav-item"> <i class="fas fa-project-diagram"></i> <span>'.yc("Menü yapısı").'</span></a>
	</div>' : '';


	/*
	$yp_linkler_ulke_sehir = syetki([2]) ?
	'<div class="nav-item has-submenu">
		<i class="fa-solid fa-globe"></i> <span>'.yc("Ülke/Şehir").'</span>
	</div>
	<div class="submenu">
		<a href="ui/ulkeler" class="nav-item"><i class="fa fa-list-alt"></i> <span>'.yc("Ülkeler").'</span></a>
		<a href="ui/sehirler" class="nav-item"><i class="fa fa-th-list"></i> <span>'.yc("Şehirler").'</span></a>
	</div>' : '';
	*/

	$yp_linkler_resim = syetki([2]) ? '
	<div class="nav-item has-submenu">
		<i class="fa fa-image"></i> <span>'.yc("Resim&Dosya").'</span>
	</div>
	<div class="submenu">
		<a href="ui/resimler" class="nav-item"><i class="fa fa-image"></i> <span>'.yc("Fotoğraflar").'</span></a>
		<a href="ui/dosyalar" class="nav-item"><i class="fa fa-file"></i> <span>'.yc("Dosyalar").'</span></a>
	</div>' : '';

	$yp_linkler_modul = '';
	if (syetki([2])){
		$yp_linkler_modul .= '
		<div class="nav-item has-submenu">
			<i class="fa fa-kaaba"></i> <span>'.yc("Modüller").'</span>
		</div>
		<div class="submenu">
		<a href="ui/moduller" class="nav-item"><i class="fa fa-kaaba"></i> <span>'.yc("Modüller").'</span></a>
		';
		if ($modul_dizin_listesi = dizin_listesi(MODUL_DIR)) {
			foreach ($modul_dizin_listesi as $mdl) {
				$yp_liste_var = is_file(MODUL_DIR . '/' . $mdl . '/yp/_liste.php');
				if ($yp_liste_var) {
					$mdl_ui_duzenle_b = $pdo->query("SELECT no, adi FROM {$do_}moduller WHERE url = '$mdl' AND yayin = 1")->fetch();
					if ($mdl_ui_duzenle_b) {
						$mdl_adi_yaz = ucfirst_utf8(str_replace('_', ' ', ($mdl_ui_duzenle_b->adi ?? '')));
						$yp_linkler_modul .= '<a href="ui/modulduzenle?n='.($mdl_ui_duzenle_b->no ?? '').'"  class="nav-item"><i class="fa fa-list" title="'.$mdl_adi_yaz.'"></i><span>'.$mdl_adi_yaz.'</span></a>';
					}
				}
			}
		}
		$yp_linkler_modul .= '</div>';
	}

	$yp_link_tema = '';
	if (syetki([2])) {
		$yp_link_tema .= '
		<a href="ui/temalar" class="nav-item"><i class="fa fa-border-none"></i> <span>'.yc("Temalar").'</span></a>
		';
	}
	try {
		$sql = "SELECT url FROM {$do_}bilesen WHERE yayin=1";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$bilesen_dir = $stmt->fetchAll(PDO::FETCH_COLUMN);
	} catch (PDOException $e) {
		error_log('PDO error in sidebar.php bilesen section: ' . $e->getMessage());
		$bilesen_dir = [];
	}

	$yp_linkler_bilesen = '';
	if (syetki([2]) && !empty($bilesen_dir)) {
		$yp_linkler_bilesen .= '
		<a href="ui/bilesen" class="nav-item"><i class="fa fa-border-none"></i> <span>'.yc("Bileşenler").'</span></a>
		';
		$ui_menu_maddeleri = '';
		foreach ($bilesen_dir as $bdir){
			if (is_file(BILESEN_DIR . "/$bdir/ui_menu.php")) {
				ob_start();
				include_once (BILESEN_DIR . "/$bdir/ui_menu.php");	
				$ui_menu_maddeleri = ob_get_clean();
				$ui_menu_maddeleri = middle_dot_yc($ui_menu_maddeleri, ($so_->d('yabanci_dil')==1));
				$yp_linkler_bilesen .= $ui_menu_maddeleri;
			}
		}
	}


	$yp_linkler_uyeler = syetki([2]) ? '
	<a href="ui/uyeler" class="nav-item"><i class="fa fa-users"></i><span>'.yc("Üyeler").'</span></a>' : '';
	//uye_yetkiler

	
	$yp_linkler_dil_ceviri = syetki([2]) ? '
	<div class="nav-item has-submenu">
		<i class="fa fa-image"></i> <span>'.yc("Diller & Çeviriler").'</span>
	</div>
	<div class="submenu">
		<a href="ui/diller" class="nav-item"><i class="fa fa-language"></i> <span>'.yc("Diller").'</span></a>
		<a href="ui/ceviri" class="nav-item"><i class="fa fa-language"></i> <span>'.yc("Çeviriler").'</span></a>
	</div>' : '';
	

	$yp_linkler_ga = syetki([2]) ? '
	<a href="ui/ga" class="nav-item"><i class="fa fa-cog"></i><span>'.yc("Genel ayarlar").'</span></a>' : '';

	$yaz_sidebar->vars = [
		'__degisken' => [
			'site_adi' => $so_->d('site_adi'),

			'giris_link' => (syetki([2]) ? '
			<a href="ui/dashboard" class="nav-item'. ($indexx ? ' active' : '') .'">
				<i class="fas fa-tachometer-alt"></i>
					<!--
					<i class="fas fa-sliders-h"></i>
					<i class="fas fa-tools"></i>
					-->
				<span>'.yc("Kontrol Paneli").'</span>
			</a>
			' : ''),

			'yp_linkler_sayfa' => $yp_linkler_sayfa,
			'yp_linkler_resim' => $yp_linkler_resim,
			'yp_linkler_modul' => $yp_linkler_modul,
			'yp_linkler_bilesen' => $yp_linkler_bilesen,
			'yp_linkler_uyeler' => $yp_linkler_uyeler,
			//'yp_linkler_ulke_sehir' => $yp_linkler_ulke_sehir,
			'yp_linkler_dil_ceviri' => $yp_linkler_dil_ceviri,
			'yp_linkler_ga' => $yp_linkler_ga,
			'yp_linkler_tema' => $yp_link_tema,

			'cikis_link' => '
			<a href="#" class="nav-item uye_cikis">
				<i class="fas fa-sign-out-alt"></i>
				<span>'.yc("Çıkış").'</span>
			</a>',
		],
		'__if' => [
			'indexx' => (int) $indexx,
		]
	];
	
	echo $yaz_sidebar->render();
}

