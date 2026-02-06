<?php if ( ! defined('otoban')) exit('vad.');
if (!syetki([2])) {
	return;
}

$dosya_dizini = 'dosya';

	echo '
	<div class="uis-page-header">
		<h2 class="uis-page-title"><i class="fa fa-folder-open uis-text-p"></i> '.yc("Dosyalar").'</h2>
		<div class="uis-action-group">
			<a class="uis-btn uis-btn-outline uis-btn-sm dosya_getir" tabindex="1" 
				data-dd="'.$dosya_dizini.'"
				data-yaz="#dosyalar" 
				data-img="0"
				data-sayi="20">'.yc("Son eklenen").'</a> 
			<a class="uis-btn uis-btn-outline uis-btn-sm dosya_getir trigger_click" tabindex="2" 
				data-dd="'.$dosya_dizini.'"
				data-yaz="#dosyalar" 
				data-img="0"
				data-sayi="0">'.yc("Tümü").'</a> 
			<input type="text" id="foto_filtrele" placeholder="'.yc("Filtrele").'..." class="uis-input uis-w-auto"/>
		</div>
	</div>';


	echo '
	<div id="dosyalar" 
		data-dosyalar="" 
		data-dd="'.$dosya_dizini.'" 
		data-yaz="#dosyalar" 
		data-img="0"
		data-t="" 
		data-a=""></div>';


	echo '
	<form enctype="multipart/form-data" class="resimyukle dropzone" 
		data-dtip="dosya" 
		data-yazi="'.yc("Dosya").' +"
		data-c_dosya_getir="'.$dosya_dizini.'"
		data-c_dosya_gyaz="#dosyalar"
		data-yg="1920">
		<input type="hidden" name="islem" value="dyukle" />
		<input type="hidden" name="dd_" value="'.$dosya_dizini.'" />
		<input type="hidden" name="adi" value="" />
		<input type="hidden" name="ow" value="0" />
		<input type="hidden" name="filigran" value="0" />
	</form>';
