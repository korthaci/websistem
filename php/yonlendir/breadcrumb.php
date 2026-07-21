<?php

$breadcrumb = new Breadcrumb($pdo, $do_);

$items = $breadcrumb->getItems(Global_::$routeParams);

if (!empty($items)) {

	$sablon = new sablon_yaz;
	$sablon->dosya_icerik(TEMABU.'/breadcrumb.html');

	$sablon->vars = [
		'__degisken' => [
			'local' => LOCAL,
			'IMG' => IMG,
			'title' => end($items)->title ?? ''
		],
		'__foreach' => [
			'breadcrumb' => $items
		],
		'__if' => [
			'indexx' => (int) $indexx,
			'uyegiris' => (int) $u_no__ > 0,
		]
	];

	echo $sablon->render();

}