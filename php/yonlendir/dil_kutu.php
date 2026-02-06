<?php if (! defined('otoban')) exit('vad.');

$d = Global_::$dil_yonlendir->d();

if ((int) $so_->d('yabanci_dil') === 1 && !empty(Global_::$dil_yonlendir->acik_diller_bilgi)) {
	$diller_db_foreach = [];
	//$dil_kutu_yaz = '<span class="dil_kutu"><img src="' . IMG . '/trspr.gif" class="dilbayrak dilk_' . $d . '" />' . $d . '<div class="dil_kutui">';

	foreach (Global_::$dil_yonlendir->acik_diller_bilgi as $dil) {
		if ($d !== $dil->dkod) {
			/*$dil_kutu_yaz .= '
				<a href="#" data-cookie-name="d" data-cookie-value="' . $dil->dkod . '">
					<img src="' . IMG . '/trspr.gif" class="dilbayrak dilk_' . $dil->dkod . '" alt="" />' . $dil->dkod . '
				</a>
			';*/
			$diller_db_foreach[] = $dil;
		}
	}

	//$dil_kutu_yaz .= '</div></span>';

	$diller_sablon_yaz = new sablon_yaz;
	$diller_sablon_yaz->dosya_icerik(TEMABU . '/dil_kutu.html');
	$diller_sablon_yaz->vars = [
		'__degisken' => ['d' => $d],
		'__foreach' => [
			'diller' => $diller_db_foreach
			]
	];
	echo $diller_sablon_yaz->render();
}
