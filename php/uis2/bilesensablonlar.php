<?php if ( ! defined('otoban')) exit('Vad.');

$bilesen_d_yaz = '';
$md_dosya_url = '';

try {
	$sql = "SELECT url FROM {$do_}bilesen WHERE no=:no";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':no', $n, PDO::PARAM_INT);
	$stmt->execute();
	$bilesen_url = $stmt->fetchColumn();
} catch (PDOException $e) {
	error_log('PDO error in bilesensablonlar.php: ' . $e->getMessage());
	$bilesen_url = false;
}

if ($bilesen_url) {
	$sablon_kok = BILESEN_DIR . '/' . $bilesen_url . '/sablon';
	$sablon_kok_real = realpath($sablon_kok);
	$kayit_mesaj = '';
	$dosya_icerik = '';
	$secili_dosya = '';
	$secili_dosya_yolu = '';
	$secili_dosya_gecerli = false;
	$gecersiz_dosya_mesaj = '';

	$bilesen_sablon_dosyalar = dosya_listele($sablon_kok, [
		'include' => ['*.html'],
		'exclude' => ['index.html', 'index1.html', '*.yedek'],
		'recursive' => true,
	]);
	$bilesen_sablon_dosyalar = is_array($bilesen_sablon_dosyalar) ? $bilesen_sablon_dosyalar : [];
	sort($bilesen_sablon_dosyalar, SORT_STRING | SORT_FLAG_CASE);

	$md_dosya_url = isset($_GET['mddosya']) ? z($_GET['mddosya']) : '';
	$md_dosya_url = trim(str_replace('\\', '/', $md_dosya_url), '/');

	if ($md_dosya_url !== '') {
		$secili_dosya = str_ends_with($md_dosya_url, '.html') ? $md_dosya_url : $md_dosya_url . '.html';

		if (strpos($secili_dosya, '..') !== false) {
			$gecersiz_dosya_mesaj = yc("Geçersiz dosya yolu.");
		} elseif ($sablon_kok_real === false) {
			$gecersiz_dosya_mesaj = yc("Şablon klasörü bulunamadı.");
		} else {
			$aday_dosya_yolu = $sablon_kok . '/' . $secili_dosya;
			$aday_real = realpath($aday_dosya_yolu);
			$kok_kontrol = rtrim(strtolower(str_replace('\\', '/', $sablon_kok_real)), '/') . '/';
			$dosya_kontrol = $aday_real ? strtolower(str_replace('\\', '/', $aday_real)) : '';

			if ($aday_real && is_file($aday_real) && str_starts_with($dosya_kontrol, $kok_kontrol)) {
				$secili_dosya_yolu = $aday_real;
				$secili_dosya_gecerli = true;
			} else {
				$gecersiz_dosya_mesaj = yc("Seçilen dosya bulunamadı veya izin verilen klasör dışında.");
			}
		}
	}

	if (isset($_POST['mddosya_text'])) {
		if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
			die('Güvenlik hatası: CSRF doğrulaması başarısız.');
		}
	}

	if (isset($_POST['mddosya_text']) && $secili_dosya_gecerli) {

		$mevcut_icerik = file_get_contents($secili_dosya_yolu);
		tema_dosya_yedek_al($bilesen_url, $secili_dosya, $mevcut_icerik);

		$yeni_icerik = sanitize_php_tag($_POST['mddosya_text']);
		if (dosya_yaz($secili_dosya_yolu, $yeni_icerik)) {
			header('Location: ' . LOCAL . '/ui/bilesensablonlar?n=' . $n . '&mddosya=' . preg_replace('/\.html$/', '', $secili_dosya) . '&kayit=1');
			exit;
		}

		$kayit_mesaj = '<div class="uis-alert uis-alert-d">' . yc("Dosya kaydedilirken bir hata oluştu.") . '</div>';
	}

	if (isset($_GET['kayit'])) {
		$kayit_mesaj = '<div class="uis-alert uis-alert-s">' . yc("Şablon başarıyla kaydedildi. Yedek otomatik olarak oluşturuldu.") . '</div>';
	}

	if ($gecersiz_dosya_mesaj !== '') {
		$kayit_mesaj = '<div class="uis-alert uis-alert-d">' . $gecersiz_dosya_mesaj . '</div>';
		$secili_dosya = '';
		$secili_dosya_gecerli = false;
	}

	if ($secili_dosya_gecerli) {
		$dosya_icerik = file_get_contents($secili_dosya_yolu);
	}

	$dosya_gruplari = [
		'genel' => [],
		'tema' => [],
	];

	foreach ($bilesen_sablon_dosyalar as $dosya) {
		$dosya = str_replace('\\', '/', $dosya);
		if (strpos($dosya, '/') === false) {
			$dosya_gruplari['genel'][] = $dosya;
			continue;
		}

		$parcalar = explode('/', $dosya, 2);
		$tema_adi = $parcalar[0];
		if (!isset($dosya_gruplari['tema'][$tema_adi])) {
			$dosya_gruplari['tema'][$tema_adi] = [];
		}
		$dosya_gruplari['tema'][$tema_adi][] = $dosya;
	}

	$dosya_link_yaz = function($dosya) use ($n, $secili_dosya) {
		$active_class = ($dosya === $secili_dosya) ? 'active' : '';
		$dosya_url = preg_replace('/\.html$/', '', $dosya);
		$dosya_label = basename($dosya);
		return '<a href="' . href('index', 'ui=bilesensablonlar&n=' . $n . '&mddosya=' . $dosya_url) . '" class="theme-file-item ' . $active_class . '" title="' . hs($dosya) . '">⌨ ' . hs($dosya_label) . '</a>';
	};

	if (!empty($dosya_gruplari['genel'])) {
		$bilesen_d_yaz .= '<div class="theme-file-group-label">> ' . yc("Genel Şablonlar") . '</div>';
		foreach ($dosya_gruplari['genel'] as $dosya) {
			$bilesen_d_yaz .= $dosya_link_yaz($dosya);
		}
	}

	foreach ($dosya_gruplari['tema'] as $tema_adi => $dosyalar) {
		$bilesen_d_yaz .= '<div class="theme-file-group-label">> ' . hs($tema_adi) . '/</div>';
		foreach ($dosyalar as $dosya) {
			$bilesen_d_yaz .= $dosya_link_yaz($dosya);
		}
	}

	$textarea_class = 'theme-editor-textarea';
	if (!empty($dosya_icerik)) {
		$textarea_class .= (strpos($dosya_icerik, '{{') === false) ? ' format_html' : ' is-template';
	} else {
		$textarea_class .= ' format_html';
	}

	echo '
	<div class="uis-page-header uis-mb-1 uis-p-0">
		<h5 class="uis-m-0 uis-p-0">/ ' . strtoupper(hs($bilesen_url)) . ' ' . yc("Bileşen Şablonları") . '</h5>
		<div class="uis-action-bar">
			<a href="' . href('index', 'ui=bilesen') . '" class="uis-btn uis-btn-sm uis-btn-outline">< ' . yc("Bileşenlere Dön") . '</a>
		</div>
	</div>

	' . $kayit_mesaj . '

	<div class="uis-row">
		<div class="uis-col uis-col-lg-2">
			<div class="uis-table-card">
				<div class="theme-editor-sidebar-header">' . yc("Şablon Dosyaları") . '</div>
				<div class="theme-file-list">
					' . (!empty($bilesen_d_yaz) ? $bilesen_d_yaz : '<div class="uis-text-muted uis-text-s uis-p-2">' . yc("Şablon dosyası bulunamadı.") . '</div>') . '
				</div>
			</div>
		</div>
		<div class="uis-col uis-col-lg-10">
			<div class="uis-card uis-card-nopad overflow-hidden">
				<form action="' . href('index', 'ui=bilesensablonlar&n=' . $n . '&mddosya=' . preg_replace('/\.html$/', '', $secili_dosya)) . '" method="POST">
					<input type="hidden" name="csrf_token" value="' . hs($_SESSION['csrf_token'] ?? '') . '">
					<div class="uis-flex uis-justify-between uis-align-center theme-editor-header">
						<span class="font-weight-bold">' . ($secili_dosya ?: yc("Şablon Seçiniz")) . '</span>
						<div class="uis-flex uis-gap-2">
							' . ($secili_dosya_gecerli ? '<button type="submit" name="mddosya_kaydet" class="uis-btn uis-btn-sm uis-btn-p">' . yc("Kaydet") . '</button>' : '') . '
						</div>
					</div>

					<textarea
						name="mddosya_text"
						class="' . $textarea_class . '"
						spellcheck="false"
						autocomplete="off"
						autocapitalize="off"
						wrap="' . (strpos($dosya_icerik, "{{") === false ? 'off' : 'soft') . '"
						' . (!$secili_dosya_gecerli ? 'disabled placeholder="' . yc("Lütfen düzenlemek için bir şablon seçin...") . '"' : '') . '
					>' . hs($dosya_icerik) . '</textarea>
				</form>
			</div>
		</div>
	</div>';
} else {
	echo '<div class="yazi3">' . yc("Bileşen bulunamadı.") . '</div>';
}
