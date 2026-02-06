<?php if ( ! defined('otoban')) exit('vad.');

$resim_dizini = 'resim/_r';
	echo '
	<div class="uis-page-header">
		<h2 class="uis-page-title"><i class="fa fa-images uis-text-p"></i> '.yc("Resimler").'</h2>
		<div class="uis-action-group">
			<a class="uis-btn uis-btn-outline uis-btn-sm dosya_getir" tabindex="1" 
				data-dd="'.$resim_dizini.'"
				data-yaz="#resimler" 
				data-sayi="20">'.yc("Son eklenen").'</a> 
			<a class="uis-btn uis-btn-outline uis-btn-sm dosya_getir trigger_click" tabindex="2" 
				data-dd="'.$resim_dizini.'"
				data-yaz="#resimler" 
				data-sayi="0">'.yc("Tümü").'</a> 
			<input type="text" id="foto_filtrele" placeholder="'.yc("Filtrele").'..." class="uis-input uis-w-auto"/>
		</div>
	</div>';


	echo '
	<div id="resimler" 
		data-dosyalar="" 
		data-dd="'.$resim_dizini.'" 
		data-yaz="#resimler" 
		data-t="" 
		data-a=""></div>';

	echo '
	<form enctype="multipart/form-data" class="resimyukle dropzone" 
		data-dtip="resim" 
		data-yazi="'.yc("Foto").' +"
		data-c_dosya_getir="'.$resim_dizini.'"
		data-c_dosya_gyaz="#resimler">
		<input type="hidden" name="islem" value="dyukle" />
		<input type="hidden" name="dd_" value="'.$resim_dizini.'" />
		<input type="hidden" name="bk" value="1" />
		<input type="hidden" name="adi" value="" />
		<input type="hidden" name="ow" value="0" />
		<input type="hidden" name="yg" value="1920" />
		<input type="hidden" name="rb" value="440" />
		<input type="hidden" name="filigran" value="0" />
	</form>';

/*
<div class="uppyUploader g_1__"
	data-dtip="resim"
	data-yazi="Resim +"
	data-c_dosya_getir="'.$resim_dizini.'"
	data-c_dosya_gyaz="#resimler"
	data-dd_="'.$resim_dizini.'"
	data-bk="1"
	data-adi=""
	data-ow="0"
	data-yg="1920"
	data-rb="440"
	data-filigran="0">
</div>
*/