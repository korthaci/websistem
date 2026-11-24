<?php if (! defined('otoban')) exit('!vad');
if (syetki([2, 3, 4])) {
	$yaz_sidebar = new sablon_yaz;
	$yaz_sidebar->dosya_icerik(TEMABU . '/sidebar.html');
	$sidebar_core_scope = static function ($html) {
		$html = (string)$html;
		return trim($html) === ''
			? ''
			: '<div class="ws_sidebar_scope" data-sidebar-component="core">'.$html.'</div>';
	};
	
	$yp_linkler_sayfa = syetki([2]) ?
	'<div class="ws_nav-item ws_has-submenu">
		<i class="ws-ikon ws-pencil"></i> <span>'.yc("Düzenle").'</span>
	</div>
	<div class="ws_submenu">
		<a href="ui/bloklar" class="ws_nav-item"><i class="ws-ikon ws-list-alt"></i> <span>'.yc("Bloklar").'</span></a>
		<a href="ui/sekmeler" class="ws_nav-item"><i class="ws-ikon ws-th-list"></i> <span>'.yc("Sekmeler").'</span></a>
		<a href="ui/sayfalar" class="ws_nav-item"><i class="ws-ikon ws-list"></i> <span>'.yc("Sayfalar").'</span>
		</a>' .
		( isset($_GET['sayfa']) && syetki([2]) ? '
		<a href="ui/sduzenle?n='.Global_::$routeParams['id'] .'" class="ws_nav-item"><i class="ws-ikon ws-pencil"></i> <span>'.yc("Bu sayfayı düzenle").'</span></a>' : '' )
		.'
		<a href="ui/menuduzenle" class="ws_nav-item"> <i class="ws-ikon ws-project-diagram"></i> <span>'.yc("Menü yapısı").'</span></a>
	</div>' : '';


	
	$yp_linkler_ulke_sehir = syetki([2]) ?
	'<div class="ws_nav-item ws_has-submenu">
		<i class="ws-ikon ws-globe"></i> <span>'.yc("Ülke/Şehir").'</span>
	</div>
	<div class="ws_submenu">
		<a href="ui/ulkeler" class="ws_nav-item"><i class="ws-ikon ws-list-alt"></i> <span>'.yc("Ülkeler").'</span></a>
		<a href="ui/sehirler" class="ws_nav-item"><i class="ws-ikon ws-th-list"></i> <span>'.yc("Şehirler").'</span></a>
	</div>' : '';
	

	$yp_linkler_resim = syetki([2]) ? '
	<div class="ws_nav-item ws_has-submenu">
		<i class="ws-ikon ws-image"></i> <span>'.yc("Resim&Dosya").'</span>
	</div>
	<div class="ws_submenu">
		<a href="ui/resimler" class="ws_nav-item"><i class="ws-ikon ws-image"></i> <span>'.yc("Fotoğraflar").'</span></a>
		<a href="ui/dosyalar" class="ws_nav-item"><i class="ws-ikon ws-file"></i> <span>'.yc("Dosyalar").'</span></a>
	</div>' : '';

	$yp_linkler_modul = '';
	if (syetki([2])){
		$yp_linkler_modul .= '
		<div class="ws_nav-item ws_has-submenu">
			<i class="ws-ikon ws-kaaba"></i> <span>'.yc("Modüller").'</span>
		</div>
		<div class="ws_submenu">
		<a href="ui/moduller" class="ws_nav-item"><i class="ws-ikon ws-kaaba"></i> <span>'.yc("Modüller").'</span></a>
		';
		if ($modul_dizin_listesi = dizin_listesi(MODUL_DIR)) {
			foreach ($modul_dizin_listesi as $mdl) {
				$yp_liste_var = is_file(MODUL_DIR . '/' . $mdl . '/yp/_liste.php');
				if ($yp_liste_var) {
					$mdl_ui_duzenle_b = $pdo->query("SELECT no, adi FROM {$do_}moduller WHERE url = '$mdl' AND yayin = 1")->fetch();
					if ($mdl_ui_duzenle_b) {
						$mdl_adi_yaz = ucfirst_utf8(str_replace('_', ' ', ($mdl_ui_duzenle_b->adi ?? '')));
						$yp_linkler_modul .= '<a href="ui/modulduzenle?n='.($mdl_ui_duzenle_b->no ?? '').'"  class="ws_nav-item"><i class="ws-ikon ws-list" title="'.$mdl_adi_yaz.'"></i><span>'.$mdl_adi_yaz.'</span></a>';
					}
				}
			}
		}
		$yp_linkler_modul .= '</div>';
	}

	$yp_link_tema = '';
	if (syetki([2])) {
		$yp_link_tema .= '
		<a href="ui/temalar" class="ws_nav-item"><i class="ws-ikon ws-border-none"></i> <span>'.yc("Temalar").'</span></a>
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

	$yp_linkler_bilesen_core = '';
	$yp_linkler_bilesen = '';
	$sidebar_mode_configs = [];
	if (syetki([2,3,4]) && !empty($bilesen_dir)) {
		$yp_linkler_bilesen_core .= syetki([2]) ? '
		<a href="ui/bilesen" class="ws_nav-item"><i class="ws-ikon ws-border-none"></i> <span>'.yc("Bileşenler").'</span></a>
		':'';
		$ui_menu_maddeleri = '';
		foreach ($bilesen_dir as $bdir){
			if (is_file(BILESEN_DIR . "/$bdir/ui_menu.php")) {
				$bilesen_sidebar_mode_config = null;
				ob_start();
				include_once (BILESEN_DIR . "/$bdir/ui_menu.php");	
				$ui_menu_maddeleri = ob_get_clean();
				$ui_menu_maddeleri = middle_dot_yc($ui_menu_maddeleri, ($so_->d('yabanci_dil')==1));
				$menu_scope = preg_replace('/[^a-z0-9_-]/i', '-', strtolower((string)$bdir));
				$menu_scope = trim($menu_scope, '-_');
				if ($menu_scope === '') {
					continue;
				}
				$menu_scope_attr = htmlspecialchars($menu_scope, ENT_QUOTES, 'UTF-8');
				if (is_array($bilesen_sidebar_mode_config ?? null)) {
					$bilesen_sidebar_mode_config['component'] = $menu_scope;
					$sidebar_mode_configs[] = $bilesen_sidebar_mode_config;
				}
				$yp_linkler_bilesen .= '<div class="ws_sidebar_scope ws_sidebar_scope_'.$menu_scope_attr.'" data-sidebar-component="'.$menu_scope_attr.'">'.$ui_menu_maddeleri.'</div>';
			}
		}
	}

	$sidebar_mode_toggle = '';
	if (!empty($sidebar_mode_configs)) {
		usort($sidebar_mode_configs, static function ($a, $b) {
			return (int)($b['priority'] ?? 0) <=> (int)($a['priority'] ?? 0);
		});

		foreach ($sidebar_mode_configs as $sidebar_mode_config) {
			$sidebar_mode_component = trim((string)($sidebar_mode_config['component'] ?? ''));
			$sidebar_mode_open = trim((string)($sidebar_mode_config['open'] ?? ''));
			$sidebar_mode_close = trim((string)($sidebar_mode_config['close'] ?? ''));
			$sidebar_mode_yetki = $sidebar_mode_config['yetki'] ?? [2];

			if ($sidebar_mode_component === '' || $sidebar_mode_open === '' || $sidebar_mode_close === '' || !syetki($sidebar_mode_yetki)) {
				continue;
			}

			$sidebar_mode_component_attr = htmlspecialchars($sidebar_mode_component, ENT_QUOTES, 'UTF-8');
			$sidebar_mode_open_attr = htmlspecialchars($sidebar_mode_open, ENT_QUOTES, 'UTF-8');
			$sidebar_mode_close_attr = htmlspecialchars($sidebar_mode_close, ENT_QUOTES, 'UTF-8');
			$sidebar_mode_toggle = '<button type="button" class="ws_nav-item ws_sidebar_mode_toggle" data-sidebar-mode-toggle data-sidebar-component="'.$sidebar_mode_component_attr.'" data-toggle-open="'.$sidebar_mode_open_attr.'" data-toggle-close="'.$sidebar_mode_close_attr.'" aria-pressed="false" title="'.$sidebar_mode_open_attr.'">
				<i class="ws-ikon ws-list"></i><span class="ws_sidebar_mode_text">'.$sidebar_mode_open_attr.'</span>
			</button>';
			break;
		}
	}

	$yp_linkler_uyeler = syetki([2]) ? '
	<a href="ui/uyeler" class="ws_nav-item"><i class="ws-ikon ws-users"></i><span>'.yc("Üyeler").'</span></a>' : '';
	//uye_yetkiler

	
	$yp_linkler_dil_ceviri = syetki([2]) && (int)$so_->d('yabanci_dil') === 1 ? '
	<div class="ws_nav-item ws_has-submenu">
		<i class="ws-ikon ws-image"></i> <span>'.yc("Diller & Çeviriler").'</span>
	</div>
	<div class="ws_submenu">
		<a href="ui/diller" class="ws_nav-item"><i class="ws-ikon ws-language"></i> <span>'.yc("Diller").'</span></a>
		<a href="ui/ceviri" class="ws_nav-item"><i class="ws-ikon ws-language"></i> <span>'.yc("Çeviriler").'</span></a>
	</div>' : '';
	

	$yp_linkler_ga = syetki([2]) ? '
	<a href="ui/ga" class="ws_nav-item"><i class="ws-ikon ws-gear"></i><span>'.yc("Genel ayarlar").'</span></a>' : '';

	$dashboard_link = syetki([2]) ? '
	<a href="ui/dashboard" class="ws_nav-item'. ($indexx ? ' active' : '') .'">
		<i class="ws-ikon ws-tachometer"></i>
			<!--
			<i class="fas fa-sliders-h"></i>
			<i class="fas fa-tools"></i>
			-->
		<span>'.yc("Kontrol Paneli").'</span>
	</a>
	' : '';

	$giris_link = $sidebar_mode_toggle . $sidebar_core_scope($dashboard_link);
	$yp_linkler_sayfa = $sidebar_core_scope($yp_linkler_sayfa);
	$yp_linkler_resim = $sidebar_core_scope($yp_linkler_resim);
	$yp_linkler_modul = $sidebar_core_scope($yp_linkler_modul);
	$yp_linkler_bilesen = $sidebar_core_scope($yp_linkler_bilesen_core) . $yp_linkler_bilesen;
	$yp_linkler_dil_ceviri = $sidebar_core_scope($yp_linkler_dil_ceviri);
	$yp_linkler_ulke_sehir = $sidebar_core_scope($yp_linkler_ulke_sehir);
	$yp_linkler_uyeler = $sidebar_core_scope($yp_linkler_uyeler);
	$yp_linkler_tema = $sidebar_core_scope($yp_link_tema);
	$yp_linkler_ga = $sidebar_core_scope($yp_linkler_ga);

	$yaz_sidebar->vars = [
		'__degisken' => [
			'site_adi' => $so_->d('site_adi'),

			'giris_link' => $giris_link,

			'yp_linkler_sayfa' => $yp_linkler_sayfa,
			'yp_linkler_resim' => $yp_linkler_resim,
			'yp_linkler_modul' => $yp_linkler_modul,
			'yp_linkler_bilesen' => $yp_linkler_bilesen,
			'yp_linkler_uyeler' => $yp_linkler_uyeler,
			'yp_linkler_ulke_sehir' => $yp_linkler_ulke_sehir,
			'yp_linkler_dil_ceviri' => $yp_linkler_dil_ceviri,
			'yp_linkler_ga' => $yp_linkler_ga,
			'yp_linkler_tema' => $yp_linkler_tema,

			'cikis_link' => '
			<a href="#" class="ws_nav-item uye_cikis">
				<i class="ws-ikon ws-sign-out"></i>
				<span>'.yc("Çıkış").'</span>
			</a>',
		],
		'__if' => [
			'indexx' => (int) $indexx,
		]
	];
	
	echo $yaz_sidebar->render();
}

