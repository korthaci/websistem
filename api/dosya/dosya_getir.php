<?php
if (!syetki([2,3,4])) {
    exit(json_encode(['return' => 0, 'mesaj' => 'Yetkisiz erişim.']));
}

$dizinbu = isset($_POST['dd_']) ? ROOT . '/' . html_d($_POST['dd_']) : exit(json_encode(['return' => 0, 'mesaj' => 'Dizin Yok']));

$dosyasayi = (isset($_POST['sayi']) && $_POST['sayi'] > 0) ? intval($_POST['sayi']) : 99999999;
$data_img = isset($_POST['img_']) && intval($_POST['img_']) === 1;
$tablo = isset($_POST['t_']) ? z($_POST['t_']) : '';
$alan = isset($_POST['a_']) ? z($_POST['a_']) : '';
$nn = isset($_POST['nn_']) ? intval($_POST['nn_']) : 0;

$dosya_sayisi = 0;
$b__ = (is_dir("{$dizinbu}/b")) ? '/b' : '';
$k__ = (is_dir("{$dizinbu}/k")) ? '/k' : '';
$dosya_tum = strpos($dizinbu, 'dosya/') !== false;

$globdesen = ($data_img) 
	? "*.{jpeg,jpg,png,gif,webp,jfif}" 
	: "*.{pdf,rar,zip,doc,docx,xls,xlsx,ppt,pptx,rtf,csv,jpg,jfif,jpeg,png,gif,mp3,mp4,ogg,webp,rtf,odt,tsv,udf,txt,json,xml}";

$dosyalar = dosya_glob($dizinbu . $b__ , $globdesen);

$html = '';
if ($dosyalar == true) {
	foreach ($dosyalar as $dosya) {
		$dosya_sayisi++;
		if ($dosya_sayisi <= $dosyasayi) {
			$dizin_tam_yolu = $dizinbu . $b__;
			$dosya = str_replace($dizin_tam_yolu.'/', "", $dosya);
			$dosya_tarih = date("j.n.y/G:i", filemtime($dizin_tam_yolu . '/' . $dosya));
			$dosya_kb = get_file_size($dizin_tam_yolu . '/' . $dosya);
			$dosya_bilgi = pathinfo($dizin_tam_yolu . '/' . $dosya);
			$dosya_uzanti = $dosya_bilgi['extension'] ?? '';
			$dosya_item_img = preg_match('/^(jpe?g|png|gif|webp|jfif|bmp|tiff?)$/i', $dosya_uzanti);
			
			//process_log(( $dosya_tum ? '2' : '1' ));

			$html .= '
			<div class="__dd' . ( $dosya_item_img ? '' : '2' ) . '" title="'.$dosya.'" data-dosyalar=""';			

			if ($dosya_item_img) {
				$html .= ' style="background-image:url('.str_replace(ROOT . '/', '', $dizinbu).$k__.'/'.$dosya.'?'.rand(1000,9999).');"';
				$html .= '>';
				$html .= '
				<img src="'.str_replace(ROOT . '/', '', $dizinbu) . $b__ . '/' . $dosya . '" alt=""
				data-imgurl="'.str_replace(ROOT . '/', '', $dizinbu) . $b__ . '/' . $dosya.'">';
			} else {
				$html .= ( $dosya_item_img 
					? ' style="background-image:url('.str_replace(ROOT . '/', '', $dizinbu).$k__.'/'.$dosya.');"' 
					: '' ) .'>
				<div class="__dd2_ikon '.$dosya_uzanti.'">
					<img src="'.IMG.'/trspr.gif" alt="">
				</div>
				<div class="__dd2_yazi">'.$dosya.'</div>';
			}

			if (($data_img && !$dosya_tum) && !empty($tablo) && !empty($alan)) {
				try {
					$stmt = $pdo->prepare("SELECT COUNT(no) FROM {$do_}$tablo WHERE $alan = :path");
					$stmt->execute([':path' => str_replace(ROOT . '/', '', $dizinbu).$k__.'/'.$dosya]);
					$resim_a_l = ($stmt->fetchColumn() > 0);
				} catch (PDOException $e) {
					$resim_a_l = false;
				}
				
				$html .= '
				<div class="__dd_acilisliste" title="Açılış/Liste resmi yap"' . ( $resim_a_l ? ' style="opacity:1;"' : '' ) . '
					data-nn="'.$nn.'" 
					data-t="'.$tablo.'" 
					data-a="'.$alan.'" 
					data-dizin="'.str_replace(ROOT . '/', '', $dizinbu) . $k__ . '" 
					data-dosya="'.$dosya.'"> </div>';
			}

			$html .= '
			<div class="__dd_copytoc"> <i class="dyok">'.str_replace(ROOT . '/', '', $dizinbu).$b__.'/'.$dosya.'</i> </div>';

			$html .= '<div class="__dd_sil" 
				data-dizin="'.str_replace(ROOT . '/', '', $dizinbu).'" 
				data-dosya="'.$dosya.'" 
				data-nn="'.$nn.'" 
				data-t="'.$tablo.'" 
				data-a="'.$alan.'" 
				data-sil="0"> </div>';

			$html .= '<div class="__dd_dosya_ac"><a href="'.str_replace(ROOT . '/', '', $dizinbu).$b__.'/'.$dosya.'" target="_blank">🔗</a></div>';

			$html .= '
			<div class="__dd_tarih">'.$dosya_tarih.'</div>
			<div class="__dd_kb">'.$dosya_kb.'</div>
			';

			$html .= '</div>';
		}
	}
}

echo json_encode([
	'return' => 1,
	'mesaj' => $html ? 'Dosyalar getirildi.' : '',
	'html' => $html
]);
