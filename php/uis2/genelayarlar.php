<?php if (! defined('otoban')) exit('Veriyolu açık değil');

if (isset($_POST['yenikayit']) && set_dolu('adi','p')) {
	$adi = z($_POST['adi']);
	$anahtar = url_duzenle($_POST['adi']);
		if (!empty($anahtar)) {
			$stmt = $pdo->prepare("INSERT INTO {$do_}genel_ayarlar (adi, anahtar, degistir) VALUES (:adi, :anahtar, 1)");
			$stmt->execute(['adi' => $adi, 'anahtar' => $anahtar]);
			
			header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
			exit();
		}
}
	echo '
	<div class="uis-page-header">
		<h2 class="uis-page-title"><i class="fa fa-cogs uis-text-p"></i> '.yc("Genel Ayarlar").'</h2>
		<div class="uis-action-group">
		' . ( syetki([1,2]) ? '
			<div class="yeni">
				<div class="yeniac uis-btn uis-btn-s"><i class="fa fa-plus uis-me-1"></i> '.yc("Yeni Özellik").'</div>
				<form name="konu" action="ui/ga" method="POST" class="dyok uis-mt-2">
					<input type="text" name="adi" value="" class="uis-input uis-w-auto" placeholder="'.yc("Özellik Adı").'" required />
					<button type="submit" name="yenikayit" class="uis-btn uis-btn-s">'.yc("Ekle").'</button>
				</form>
			</div>' : '' ) . '
		</div>
	</div>';


$stmt = $pdo->query("SELECT no, adi, anahtar, deger, aciklama, grup, degistir FROM {$do_}genel_ayarlar ORDER BY (CASE WHEN grup = 'Site' THEN 1 WHEN grup = 'Sistem' THEN 2 WHEN grup = 'Mail' THEN 3 ELSE 4 END), no ASC");
$site_o__ = $stmt->fetchAll(PDO::FETCH_OBJ);

if ($site_o__) {
	echo '<table class="yazi1 uis-table"><thead><tr><th class="g_20_">'.count($site_o__).'</th><th>'.yc("Başlık").'</th><th>'.yc("değer").'</th><th class="g_20_"></th></tr></thead><tbody>';
	$current_group = '';
	foreach ($site_o__ as $so__){
		if ($current_group !== $so__->grup) {
			$current_group = $so__->grup;
			echo '<tr class="uis-group-row"><td colspan="4" class="uis-table-group-header"><i class="fa fa-folder-open uis-me-2"></i> '.yc($current_group ?: "Genel").'</td></tr>';
		}
		$sil_td = (syetki([1,2])) ? '<td><span class="sil" data-nt="'.sifrele($so__->no.',,genel_ayarlar').'">x</span></td>':'<td> </td>';
		echo '<tr>';
		if ($so__->degistir == "1" || syetki([1,2])) {
			echo '<td class="g_20_">'.$so__->no.'</td>';
			echo '<td class="g_350_">
				<span class="fw-700" title="'.$so__->aciklama.'">'.mb_ucfirst($so__->adi).'</span>
				<div class="uis-mt-1">
					<div class="i1 uis-text-light uis-input-xs" style="font-size:10px; cursor:pointer;" data-nta-input="'.sifrele($so__->no.',,genel_ayarlar,,aciklama').'" data-nta-class="dbtext uis-input uis-input-xs p-05">'.($so__->aciklama ?: yc("Açıklama ekle...")).'</div>
				</div>
			</td>';
			echo '<td>';
			
			$datatipi = (strpos($so__->anahtar ?? '', 'email') !== false) ? ' data-datatipi="email"' : '';

			if (in_array($so__->deger, ["0", "1"]) && strpos($so__->adi,'()')!==false) {
				echo '
				<span class="db01 ok'.$so__->deger.'" data-nta="'.sifrele($so__->no.',,genel_ayarlar,,deger').'">'.$so__->deger.'</span>
				<div class="uis-float-end">
					<a class="dyokac">***</a> &nbsp; &nbsp; 
					<input class="dyok dbtext g_60_" type="text" '.$datatipi.' value="'.$so__->deger.'" 
					data-nta="'.sifrele($so__->no.',,genel_ayarlar,,deger').'" />
				</div>';
			} elseif (strlen($so__->deger ?? '') > 200 || strpos($so__->deger ?? '', '"') !== false) {
				echo '
				<div class="uis_ajax_textarea">
					<textarea class="dbtextarea g_99__ uis-textarea" rows="1"'.$datatipi.'>'.$so__->deger.'</textarea>
					<button class="uis-btn uis-btn-sm uis-btn-w uis-float-end textkaydet" data-nta="'.sifrele($so__->no.',,genel_ayarlar,,deger').'">'.yc("Kaydet").'</button>
				</div>
				';
			} else {
				echo '<input type="text" '.$datatipi.' class="i1 dbtext g_99__ uis-input" value="'.$so__->deger.'" data-nta="'.sifrele($so__->no.',,genel_ayarlar,,deger').'" />';
			}

			echo '</td>' . $sil_td . '</tr>';

		} else {
			echo '<tr class="yazi1"><td></td><td>'.$so__->adi.'</td><td>'.$so__->deger.'</td>'.$sil_td.'</tr>';
		}
	}

	echo '</tbody></table>';
	echo '
	<br/><hr />'.yc("İki veya daha fazla eposta adresi yazabilirsiniz. Ancak eposta adreslerini virgülle ayırmalısınız. abc@abc.com , mail@rehberim.com ** İçerikte değişiklik olmadığında kayıt işlemi yapılmaz.");
}