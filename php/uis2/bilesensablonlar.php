<?php if ( ! defined('otoban')) exit('Vad.');

$bilesen_d_yaz = '';
$md_dosya_url='';

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
	$bilesen_sablon_dosyalar = dosya_listesi(BILESEN_DIR . '/' . $bilesen_url . '/sablon', ['index.html', 'index1.html']);
	
	if (! empty($bilesen_sablon_dosyalar)) {
		sort($bilesen_sablon_dosyalar, SORT_STRING | SORT_FLAG_CASE | SORT_DESC);
		$md_dosya_url =(isset($_GET['mddosya'])) ? z($_GET['mddosya']) : '';
		$md_dosya = $md_dosya_url . '.html';

		foreach($bilesen_sablon_dosyalar as $msd) {
			$msd_yazi = strtr(trim($msd,"_"), ['.html'=>'','_'=>' ']);
			$msd_href = href('index','ui=bilesensablonlar&n='.$n.'&mddosya='.strtr($msd,['.html'=>'']));
			$msd_class = ($msd === $md_dosya) ? ' btn-primary' : ' btn-secondary';
			$bilesen_d_yaz .= ' <a class="dib btn btn-sm '.$msd_class.'" href="'.$msd_href.'">'.$msd_yazi.'</a> ';
		}
	}


if (!empty($md_dosya_url)) {

	if (isset($_POST['mddosya_text'])) {
		dosya_yaz(BILESEN_DIR . '/' . $bilesen_url . '/sablon/'. $md_dosya, html_xss_phptag_sil(strtr($_POST['mddosya_text'], ['__textarea__'=>'textarea'])));
	}

	$dosya_icerik = file_get_contents(BILESEN_DIR . '/' . $bilesen_url . '/sablon/'. $md_dosya);
	$dosya_icerik = strtr($dosya_icerik, ['textarea'=>'__textarea__']);
	$form_action = href('index','ui=bilesensablonlar&n='.$n.'&mddosya='.$md_dosya_url);
	$bilesen_d_yaz .= '
	<div class="mt-4">
	<form name="" class="" action="'.$form_action.'" method="POST">		
	<a class="dib btn btn-sm btn-light" data-jodit_ekle=".texticerik_textarea">wysiwyg editor</a>
	<div class="f_r text-right"><input type="submit" class="btn btn-sm btn-info" name="mddosya_kaydet" value="'.yc("Kaydet").'" /> </div>
	<textarea class="texticerik_textarea  g_1__ minw-400" name="mddosya_text">'.$dosya_icerik.'</textarea>
	<div class="text-right"><input type="submit" class="btn btn-sm btn-primary" name="mddosya_kaydet" value="'.yc("Kaydet").'" /> </div>
	</form>
	</div>';
}

echo '
<div class="container">
'.strtoupper($bilesen_url).' ·Bileşen şablonları.· <br/><a href="ui/bilesen">'. yc("Bileşenler").'</a>
	<br/><br/>'.$bilesen_d_yaz.'
</div>';

} else {
	echo '<div class="yazi3">'.yc("Bileşen bulunamadı.").'</div>';
}
