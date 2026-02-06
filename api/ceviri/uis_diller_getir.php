<?php

if (isset($_POST['atn'])) {

	$alan_tablo_tablo_no = z($_POST['atn']);
	$eklenmis_vcdil = isset($_POST['eklenmis_vcdil']) ? intval($_POST['eklenmis_vcdil']) : 0;

	$alan_tablo_no_dizi = explode(",", $alan_tablo_tablo_no);

	$alan = isset($alan_tablo_no_dizi[0]) ? $alan_tablo_no_dizi[0] : false;
	$tablo = isset($alan_tablo_no_dizi[1]) ? $alan_tablo_no_dizi[1] : false;
	$no = isset($alan_tablo_no_dizi[2]) ? $alan_tablo_no_dizi[2] : false;

	if ($alan && $tablo && $no) {
		
		$vcdiller = $eklenmis_vcdil == 1 
		? strip_tags(vcdil($alan, $tablo , $no)) 
		: $vcdiller = vcdiller($alan, $tablo , $no);

		if ($vcdiller == true) {
			echo json_encode(['return' => 1, 'mesaj' => $vcdiller]);
		} else {
			echo json_encode(['return' => 0, 'mesaj' => '!']);
		}

	} else {
		echo json_encode(['return' => 0, 'mesaj' => '!!']);
	}
}