<?php if (! defined('otoban')) exit('!vad');

if (Global_::has('tema_fallback_uyari') && Global_::get('tema_fallback_uyari')) {
    echo '
    <div id="tema_fallback_uyari" style="background:#fff3cd; border:1px solid #ffc107; color:#856404; padding:8px 16px; margin-bottom:16px;">
        <strong>⚠️⚠️⚠️ Aktif tema yüklenemedi.</strong> Yedek tema kullanılıyor. Lütfen <a href="'.LOCAL.'/ui/temalar">tema dosyalarını</a> kontrol edin.
    </div>
    ';
}

$index_html_yaz = new sablon_yaz;
$index_html_yaz->dosya_icerik(TEMABU . '/index.html');

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
	'gloginc'	 => $so_->d('google_login_client_id') && $u_no__ == 0 ? trim($so_->d('google_login_client_id')) : '',
];

$script_var = 'const js_vars = ' . json_encode($baslangic_js_const_dizi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';';

$index_html_yaz->vars = [
	'__degisken' => [
		'title' => Global_::$pageMetadata->title,
		'description' => Global_::$pageMetadata->description,
		'tags' => Global_::$pageMetadata->tags,
		'image' => Global_::$pageMetadata->image,
		'structured_data' => Global_::$structuredData->output ?? '',
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
		'csrf_token_meta' => (syetki([2,3]) && !empty($_SESSION['csrf_token']))
			? '<meta name="csrf-token" content="' . hs($_SESSION['csrf_token']) . '">'
			: '',
		'script_var' => $script_var,
		'html_ekle' => is_array(Global_::get('html_ekle'))
			? implode('', Global_::get('html_ekle'))
			: (Global_::get('html_ekle', '') ?? ''),
		'google_login_client_id' => $so_->d('google_login_client_id') && $u_no__ == 0 ? trim($so_->d('google_login_client_id')) : '',
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
		'onboarding_acik' => (int) (class_exists('OnboardingManager') && syetki([2,3,4])),
	]
];

$meta_noindex_follow = explode(',', $so_->d('meta_noindex_follow') ?? '');
if (!empty($meta_noindex_follow)) {
	foreach ($meta_noindex_follow as $param) {
		if (isset($_GET[$param])) {
			$index_html_yaz->vars['__degisken']['meta_noindex_follow'] = '<meta name="robots" content="noindex, follow">';
			break;
		}
	}
}

echo $index_html_yaz->render();
