<?php 
if (! defined('otoban')) {
	echo json_encode(['return' => 0, 'mesaj' => '!!']);
	exit;
}

$yazi = '';
//var_dump($_POST);exit;
if (isset($_POST['tag']) && isset($_POST['cdil'])) {

	$tag = strtolower(z($_POST['tag']));
	$cdil = strtolower(z($_POST['cdil']));
	$ekledegistir = z($_POST['ekledegistir']);
	$sql_cdil = "SELECT no FROM {$do_}diller WHERE dkod = :cdil LIMIT 1";
	$stmt_cdil = $pdo->prepare($sql_cdil);
	$stmt_cdil->execute([':cdil' => $cdil]);
	$cdil_no = $stmt_cdil->fetchColumn();

	$alan_tablo_tablo_no = z($_POST['atn']);
	$alan_tablo_no_dizi = explode(",", $alan_tablo_tablo_no);
	$alan = isset($alan_tablo_no_dizi[0]) ? $alan_tablo_no_dizi[0] : false;
	$tablo = isset($alan_tablo_no_dizi[1]) ? $alan_tablo_no_dizi[1] : false;
	$tablo_no = isset($alan_tablo_no_dizi[2]) ? $alan_tablo_no_dizi[2] : false;
	
	if ($alan && $tablo && $tablo_no) {
		$sql_ceviriler = "SELECT no, dil_no, tablo_no, tablo, alan, yazi FROM {$do_}ceviriler WHERE dil_no = :dil_no AND tablo = :tablo AND tablo_no = :tablo_no AND alan = :alan LIMIT 1";
		$stmt_ceviriler = $pdo->prepare($sql_ceviriler);
		$stmt_ceviriler->execute([
			':dil_no' => $cdil_no,
			':tablo' => $tablo,
			':tablo_no' => $tablo_no,
			':alan' => $alan
		]);
		$cdil_b = $stmt_ceviriler->fetch(PDO::FETCH_OBJ);
		$yazi .= '<div class="cdil_ustbilgi">'.strtoupper(str_replace("_"," ",$alan)).' ('.$cdil.') :</div>';
		$yazi .= '<div class="uis_ajax uis_ajax_ceviri">';
		
		if ($tag == 'input') {

			if ($ekledegistir=='degistir') {
				$yazi .= '<input class="g_1__ input ajax_text" value="'.($cdil_b ? $cdil_b->yazi : '').'" data-cdiln="'.$cdil_no.'" data-a="'.$alan.'" data-tn="'.$tablo_no.'" data-t="'.$tablo.'" data-yeni="0" /> ';

			} elseif ($ekledegistir=='ekle') {
				$yazi .= '<input class="g_1__ input ajax_text" value="" data-cdiln="'.$cdil_no.'" data-a="'.$alan.'" data-tn="'.$tablo_no.'" data-t="'.$tablo.'" data-yeni="1" /> ';
			}

			$yazi .= '<button class="ceviri_kaydet_input btn btn-success">'.yc("Kaydet").' <i class="fa fa-paper-plane"></i></button>';
			$yazi .= ($ekledegistir=='degistir' && $cdil_b) ? '<button class="ceviri_sil_input btn btn-danger" data-siln="'.$cdil_b->no.'">'.yc("Sil").' <i class="fa fa-trash"></i></button>' : '';

			$yazi .= '</div>';

		} elseif ($tag=='div' || $tag=='textarea') {

			if ($ekledegistir=='degistir') {
				$yazi .= '<'.$tag.' class="g_1__ texticerik_a ajax_text" id="text_'.rand(0,100000).'" data-cdiln="'.$cdil_no.'" data-a="'.$alan.'" data-tn="'.$tablo_no.'" data-t="'.$tablo.'" data-yeni="0">'.($cdil_b ? strtr(html_a($cdil_b->yazi),['textarea'=>'__textarea__']) : '').'</'.$tag.'> ';

			} elseif ($ekledegistir=='ekle') {
				$yazi .= '<'.$tag.' class="g_1__ texticerik_a ajax_text" id="text_'.rand(0,100000).'" data-cdiln="'.$cdil_no.'" data-a="'.$alan.'" data-tn="'.$tablo_no.'" data-t="'.$tablo.'" data-yeni="1"></'.$tag.'> ';
			}

			$yazi .= '<button class="ceviri_kaydet_input btn btn-success">'.yc("Kaydet").' <i class="fa fa-paper-plane"></i></button>';
			$yazi .= ($ekledegistir=='degistir' && $cdil_b) ? '<button class="ceviri_sil_input btn btn-danger" data-siln="'.$cdil_b->no.'">'.yc("Sil").' <i class="fa fa-trash"></i></button>' : '';
			
		}
	}

	echo json_encode([
		'return' => 1,
		'mesaj' => $yazi
	]);

} else {
	echo json_encode(['return' => 0, 'mesaj' => '!tag!cdil']);
}
