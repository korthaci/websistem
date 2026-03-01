<?php if (! defined('otoban')) exit('!vad');

$tema_render = tema_render_kaynagi_sec($so_->d('tema'));
if (!empty($tema_render['critical_fail'])) {
	http_response_code(500);
	echo tema_critical_fail_html();
	return;
}

$GLOBALS['tema_render_dizin'] = $tema_render['tema_dir'];

$index_html_yaz = new sablon_yaz;
$index_html_yaz->dosya_icerik($tema_render['index_path']);

$modul_css_js = new modul_css_js($pdo, $do_);
$bilesen_css_js = new bilesen_css_js($pdo, $do_);
$bilesen_css_js->rand_v = (boolean)local_test();

$baslangic_js_const_dizi = [
    'local___'   => LOCAL,
    'd___'       => $d,
    'i___'       => !empty($indexx) && $indexx == 1,
    'img___'     => IMG,
    'u___'       => isset($u_no__) && n0_($u_no__),
    'ui___'      => isset($_GET['ui']) || isset($_GET['uis']),
    '__ceviri'   => $so_->d('yabanci_dil') == 1,
	'gloginc'	 => $so_->d('google_login_client_id') && $u_no__ == 0 ? $so_->d('google_login_client_id') : '',
];

$script_var = 'const js_vars = ' . json_encode($baslangic_js_const_dizi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';';

$index_html_yaz->vars = [
	'__degisken' => [
		'title' => Global_::$pageMetadata->title,
		'description' => Global_::$pageMetadata->description,
		'tags' => Global_::$pageMetadata->tags,
		'image' => Global_::$pageMetadata->image,
		'dil_kod' => Global_::$d,
		'local' => LOCAL,
		'yonlendir_index_body_class' => is_array(Global_::$yonlendir_index_body_class ?? null)
    			? implode(' ', array_map('trim', Global_::$yonlendir_index_body_class))
    			: trim(Global_::$yonlendir_index_body_class ?? ''),
		'modul_css' => $modul_css_js->css_yaz(),
		'modul_js' => $modul_css_js->js_yaz(),
		'bilesen_css' => $bilesen_css_js->css_yaz(),
		'bilesen_js' => $bilesen_css_js->js_yaz(),
		'bilesen_css_yp' => $bilesen_css_js->css_yaz('yp'),
		'bilesen_js_yp' => $bilesen_css_js->js_yaz('yp'),
		'script_var' => $script_var,
		'google_login_client_id' => $so_->d('google_login_client_id') && $u_no__ == 0 ? $so_->d('google_login_client_id') : '',
		'meta_index_follow' => !isset($_GET['bolge1']) && isset($_GET['t1']) ? '<meta name="robots" content="noindex, follow">' : '',
	],
	'__if' => [
		'indexx1' => (int)$indexx,
		'indexx0' => (int)!$indexx,
		'syetki2' => (int) syetki([2]),
		'syetki3' => (int) syetki([3]),
		'syetki4' => (int) syetki([4]),
		'syetki23' => (int) syetki([2,3]),
		'syetki234' => (int) syetki([2,3,4]),
		'uyegiris' => (int) $u_no__ > 0,
		'has_meta_image' => (int)!empty(Global_::$pageMetadata->image),
		'has_meta_tags' => (int)!empty(Global_::$pageMetadata->tags),
		'google_login_oauth' => (int)(!empty($so_->d('google_login_client_id')) && $u_no__ <= 0),
	]
];
$html = $index_html_yaz->render();

if (!empty($tema_render['used_fallback']) && syetki([2,3])) {
	$fallback_reason = hs((string)($tema_render['reason'] ?? 'unknown'));
	$notice = '<div style="background:#fff3cd;color:#5f4300;border:1px solid #ffe69c;padding:10px 14px;margin:0;text-align:center;font-size:13px;">Teknik Uyarı: Aktif tema doğrulamadan geçmedi. Bu istek <b>.sabitlenmis_tema</b> ile servis edildi. Sebep: <code>' . $fallback_reason . '</code></div>';

	if (preg_match('/<body\b[^>]*>/i', $html)) {
		$html = preg_replace('/<body\b[^>]*>/i', '$0' . $notice, $html, 1);
	} else {
		$html = $notice . $html;
	}
}

echo $html;


