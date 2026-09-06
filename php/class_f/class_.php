<?php if (! defined('otoban')) exit();

class db_row_ {
	public $db_dizi;
	public function __construct($db_dizi) {
		$this->db_dizi = $db_dizi;
	}
	public function yaz($alan='', $return=''){
		if ($this->db_dizi!==false && isset($this->db_dizi->$alan) && !empty($alan)) {
			$return = $this->db_dizi->$alan;
		}
	return $return;
	}
}

class JSONVeri { // implements JsonSerializable (şimdilik devre dışı)
	private $data = [];

	public function __construct(array $arr = []) {
		$this->data = $arr;
	}

	public function __get($name) { //Dinamik getter: Eğer property yoksa boş string döner
		// 1) Direkt key varsa onu döndür
		if (array_key_exists($name, $this->data)) {
			return $this->data[$name];
		}

		// 2) Eğer key "_" ile başlıyorsa, "_" kaldırılmış versiyonunu test et
		if (strlen($name) > 1 && $name[0] === '_') {
			$altName = substr($name, 1);
			if (array_key_exists($altName, $this->data)) {
				return $this->data[$altName];
			}
		}

		// 3) Bulunamazsa boş string döner
		return '';
	}

	public function __set($name, $value) { //Dinamik setter: Yeni property ekleyebilir veya güncelleyebilir
		$this->data[$name] = $value;
	}

	public function __isset($name) { //isset / empty kontrolü: property var mı diye bakar
		// 1) Direkt key varsa true
		if (isset($this->data[$name])) {
			return true;
		}

		// 2) "_" ile başlayanlarda fallback kontrolü
		if (strlen($name) > 1 && $name[0] === '_' && isset($this->data[substr($name, 1)])) {
			return true;
		}

		return false;
	}

	/*
	//JSON encode desteği: json_encode() çağrıldığında çalışır. mixed 8.1+ iin kullanılmalı. 7.4 için kullanılmamalı
	public function jsonSerialize(): mixed {
		return $this->data;
	}
	//Tüm veriyi dizi olarak döndürür
	public function toArray() {
		return $this->data;
	}
	*/
}



class genel_ayarlar {
	private $options;

	public function __construct($pdo, $do_ = "") {
		$this->options = new stdClass();
		
		try {
			$stmt = $pdo->prepare("SELECT no, anahtar, deger, aciklama FROM {$do_}genel_ayarlar");
			$stmt->execute();
			$results = $stmt->fetchAll();

			if ($results === false) {
				throw new PDOException('! genel ayarlar');
			}

			foreach ($results as $row) {
				$this->options->{$row->anahtar} = array(
					'no' => $row->no,
					'deger' => $row->deger,
					'aciklama' => $row->aciklama
				);
			}
		} catch (PDOException $e) {
			//throw new PDOException($e->getMessage(), (int)$e->getCode());
			exit('!ga');
		}
	}

	public function d($anahtar) {
		return isset($this->options->{$anahtar}) ? $this->options->{$anahtar}['deger'] : null;
	}

	public function a($anahtar) {
		return isset($this->options->{$anahtar}) ? $this->options->{$anahtar}['aciklama'] : null;
	}

	public function no($anahtar) {
		return isset($this->options->{$anahtar}) ? $this->options->{$anahtar}['no'] : null;
	}
}

class siralama {
	public $link_ = '';
	public $siralama_sagsol = 5;
	public $yaz = '';
	public $s_tag = 'div';
	public $s_tagi = 'a';
	public $siralama_class = 'siralama';
	public $sburda_class = 'sburda';
	public $onceki_yazi = '&lsaquo;';
	public $sonraki_yazi = '&rsaquo;';
	public $limit2_bu;
	public $sira_;
	public $limit1;
	public $limit_sira_;
	public $link_href = true;

	public function __construct($limit2_bu) {
		$this->limit2_bu = $limit2_bu;
		$this->setLimit();
	}

	private function setLimit() {
		if (isset($_POST['ajaxl']) && is_numeric($_POST['ajaxl']) && $_POST['ajaxl'] != 0) {
			$this->sira_ = intval($_POST['ajaxl']);
		} elseif (isset($_GET['l']) && is_numeric($_GET['l']) && $_GET['l'] != 0) {
			$this->sira_ = intval($_GET['l']);
		} else {
			$this->sira_ = 1;
		}
		$this->limit1 = ($this->sira_ - 1) * $this->limit2_bu;
	}

	private function createLink($page, $text, $class = '') {
		$queryParams = $_GET;
		$queryParams['l'] = $page;
		
		// Query parametrelerini string'e çevir
		$query_string = http_build_query($queryParams);
		
		// href() fonksiyonunu kullanarak permalink destekli URL oluştur
		$url = href('index', $query_string);

		return "<{$this->s_tagi} href='{$url}' class='{$class}' rel='{$page}'>{$text}</{$this->s_tagi}>";
	}

	public function siralama_yaz() {
		if ($this->limit_sira_ <= 1) return '';

		$this->yaz .= "<{$this->s_tag} class='{$this->siralama_class}'>";

		if ($this->sira_ > 1) {
			$this->yaz .= $this->createLink($this->sira_ - 1, $this->onceki_yazi, 'prev');
		}

		for ($i = max(1, $this->sira_ - $this->siralama_sagsol); $i <= min($this->limit_sira_, $this->sira_ + $this->siralama_sagsol); $i++) {
			$class = ($i == $this->sira_) ? $this->sburda_class : '';
			$this->yaz .= $this->createLink($i, $i, $class);
		}

		if ($this->sira_ < $this->limit_sira_) {
			$this->yaz .= $this->createLink($this->sira_ + 1, $this->sonraki_yazi, 'next');
		}

		$this->yaz .= "</{$this->s_tag}>";

		return $this->yaz;
	}
}



/**
 * sablon_yaz — HTML/PHP şablon dosyalarını işleyen render motoru.
 *
 * Şablon dosyası içinde kullanılabilecek etiketler:
 *
 * DEĞİŞKEN BASMA
 *   {{$:isim}}
 *     $this->vars['__degisken']['isim'] değerini basar.
 *     Eşleşme yoksa boş string döner.
 *
 * KOŞUL (if / if_2 — iç içe kullanım için 2. seviye)
 *   {{__if %isim% doğruysa_yazı || yanlışsa_yazı __endif}}
 *   {{__if_2 %isim% doğruysa_yazı || yanlışsa_yazı __endif_2}}
 *     $this->vars['__if']['isim'] truthy ise ilk kısım, değilse (varsa)
 *     "||" sonrası kısım basılır. "||" yoksa false durumunda boş basılır.
 *     Nested if gereken durumlarda dıştaki blok __if, içteki blok __if_2
 *     kullanılmalı (iki seviyeden fazlası desteklenmiyor).
 *
 * DÖNGÜ (foreach)
 *   {{__foreach %isim% item}}
 *       {{ %item.alan% }}
 *       [[__if %1_veya_0% doğruysa_yazı || yanlışsa_yazı __endif]]
 *   {{__endforeach}}
 *     $this->vars['__foreach']['isim'] dizisindeki her eleman için blok
 *     tekrarlanır. "item" kısmı, blok içinde alan erişimi için kullanılan
 *     serbest isimdir (örn. item.baslik, item.url). Eleman array veya
 *     object olabilir. Döngü içindeki koşul için normal {{__if}} değil,
 *     köşeli parantezli [[__if %1|0% ... __endif]] söz dizimi kullanılır
 *     ve değer olarak alan adı değil doğrudan 1/0 beklenir.
 *
 * CDN
 *   {:cdn:}
 *     _CDN_ sabitiyle değiştirilir.
 *
 * MODÜL / BİLEŞEN DAHİL ETME (html_modul — dış fonksiyon, değişken/foreach
 * substitution'undan SONRA çalışır)
 *   [al:modul:modul_url]
 *   [al:bilesen:bilesen_url:dosya_adi]
 *     Veritabanında yayin=1 olan ilgili modül/bileşen dosyasını include eder.
 *
 * DOSYA DAHİL ETME / GLOBAL ERİŞİM (sablon_include — class metodu, en son
 * çalışır)
 *   [[$degisken]]
 *     Global scope'taki $degisken değerini basar.
 *   {{i:tema:dosya_adi}}   → TEMA_DIR/<aktif_tema>/dosya_adi.php dahil edilir
 *   {{i:yon:dosya_adi}}    → YONLENDIR_D/dosya_adi.php dahil edilir
 *   {{i:diger:dosya_adi}}  → R_PHP/dosya_adi.php dahil edilir
 *   {{i:yon:kosul+:dosya_adi}} → global $kosul truthy ise dahil edilir
 *   {{i:$:degisken}}      → $degisken içindeki tam dosya yolu dahil edilir
 *   {{c:obje:metod}}      → global $obje->metod() çağrılır ve sonucu basılır
 *     Dosya adı kısmı otomatik olarak [A-Za-z0-9._-] dışındaki karakterlerden
 *     temizlenir (path traversal koruması). Bu etiketler yalnızca yetkili
 *     kişilerin düzenlediği şablon dosyalarında kullanılmalıdır — kullanıcı
 *     girdisinden gelen veri (örn. bir yorum metni) hiçbir zaman bu syntax'ı
 *     üretebilecek şekilde şablona basılmamalıdır.
 *
 * ÖNEMLİ — İşlenme sırası (render metodunda):
 *   render_cdn → render_foreach → render_degisken → render_if_2 → render_if
 *   → html_modul → sablon_include
 *   Kullanıcıdan/DB'den gelen veriler (__degisken, __foreach) DB'ye
 *   yazılmadan önce z() ile sanitize edilmelidir; z() fonksiyonu
 *   { } [ ] gibi karakterleri HTML entity'e çevirdiği için bu veriler
 *   render sonrası aşamada (html_modul/sablon_include) kontrol etiketi
 *   olarak yorumlanamaz.
 */
class sablon_yaz {
	public $dizin_dosya = null;
	public $yazi = '';
	public $vars = [];
	/*
	vars = ['__degisken' => ['d'=>$d],'__foreach' => ['diller'=> $diller_db_foreach]];
	d : şablondaki {{$:d}} yerine geçecek olan d, $d değişkenini gösterir.
	__foreach : şablonda __foreach ile başlayan bölüm $diller_db dizisini alır.
	*/
	public $orjinal_text = false;

	public function __construct(){
	}
	
	public function dosya_icerik ($dizin_dosya = null){
		if ($dizin_dosya !== null) {
			$this->dizin_dosya = $dizin_dosya;
		}

		if (is_file($this->dizin_dosya) && empty($this->yazi)) {
			$this->yazi = file_get_contents($this->dizin_dosya);
			$this->yazi = preg_replace('/<!--.*?-->/s', '', $this->yazi);
		}
	}

	public function render() {
		if ($this->orjinal_text!==false){return;}
		//$this->yazi = $this->xss_sil($this->yazi);
		$this->yazi = $this->render_cdn();
		$this->yazi = $this->render_foreach();
		$this->yazi = $this->render_degisken();
		$this->yazi = $this->render_if_2();
		$this->yazi = $this->render_if();
		
		$this->yazi = html_modul($this->yazi);
		$this->yazi = $this->sablon_include($this->yazi);
		return $this->yazi;
	}

	public function render_cdn(){
		if ($this->orjinal_text !== false) {
			return;
		}
		return preg_replace('/{:cdn:}/i', _CDN_, $this->yazi);
	}
	public function render_degisken(){
		if ($this->orjinal_text !== false) {
			return;
		}
		$icerik_yaz = $this->yazi;
		
		if (!empty($this->vars) && isset($this->vars['__degisken'])) {
			foreach ($this->vars['__degisken'] as $sablon_key => $key_degisken) {
				$regex_d = '/{{\$:(' . preg_quote($sablon_key) . ')}}/i';
				$icerik_yaz = preg_replace_callback($regex_d, function ($m) use ($key_degisken) {
					return $key_degisken ?? '';
				}, $icerik_yaz);
			}
		}
		
		$icerik_yaz = preg_replace('/{{\$:[^}]+}}/i', '', $icerik_yaz);
		return $icerik_yaz;
	}
	
	public function render_if() {
		if ($this->orjinal_text !== false) {
			return;
		}
		$icerik_yaz = $this->yazi;
	
		if (!empty($this->vars) && isset($this->vars['__if'])) {
			foreach ($this->vars['__if'] as $sablon_key => $key_degisken) {
				$regex_d = '/{{__if\s*%(' . preg_quote($sablon_key) . ')%\s*((?:(?!__endif}}).)+)__endif ?}}/si';
				
				if (preg_match($regex_d, $icerik_yaz, $matches)) {
					$icerik_yaz = preg_replace_callback($regex_d, function ($matches) use ($key_degisken) {
						$yazi_1_true = trim($matches[2]);
						$yazi_0_false = '';
	
						if (strpos($yazi_1_true, '||') !== false) {
							list($yazi_1_true, $yazi_0_false) = array_map('trim', explode("||", $yazi_1_true, 2));
						}
	
						return $key_degisken ? $yazi_1_true : $yazi_0_false;
					}, $icerik_yaz);
				}
			}
		}
		
		$icerik_yaz = preg_replace('/{{__if\s*%([a-zA-Z0-9_]+)%.*?__endif ?}}/si', '', $icerik_yaz);
		return $icerik_yaz;
	}
	
	// İç içe (nested) if desteği için ikinci seviye __if_2 metodu
	public function render_if_2() {
		if ($this->orjinal_text !== false) {
			return;
		}
		$icerik_yaz = $this->yazi;
	
		if (!empty($this->vars) && isset($this->vars['__if'])) {
			foreach ($this->vars['__if'] as $sablon_key => $key_degisken) {
				$regex_d = '/{{__if_2\s*%(' . preg_quote($sablon_key) . ')%\s*((?:(?!__endif_2}}).)+)__endif_2 ?}}/si';
				
				if (preg_match($regex_d, $icerik_yaz, $matches)) {
					$icerik_yaz = preg_replace_callback($regex_d, function ($matches) use ($key_degisken) {
						$yazi_1_true = trim($matches[2]);
						$yazi_0_false = '';
	
						if (strpos($yazi_1_true, '||') !== false) {
							list($yazi_1_true, $yazi_0_false) = array_map('trim', explode("||", $yazi_1_true, 2));
						}
	
						return $key_degisken ? $yazi_1_true : $yazi_0_false;
					}, $icerik_yaz);
				}
			}
		}
		
		$icerik_yaz = preg_replace('/{{__if_2\s*%([a-zA-Z0-9_]+)%.*?__endif_2 ?}}/si', '', $icerik_yaz);
		return $icerik_yaz;
	}
	
	public function render_foreach() {
		if ($this->orjinal_text !== false) {
			return;
		}
		$icerik_yaz = $this->yazi;
		
		if (!empty($this->vars) && isset($this->vars['__foreach'])) {

			foreach ($this->vars['__foreach'] as $sablon_key => $key_dizi) {

				$regex_d_foreach = '/{{ ?__foreach *%(' . preg_quote($sablon_key) . ')-?>?% *([^}]*)}}(.*?){{ ?__endforeach ?}}/si';
				while (preg_match($regex_d_foreach, $icerik_yaz)) {

					if (empty($key_dizi)) {
						$icerik_yaz = preg_replace($regex_d_foreach, '', $icerik_yaz);
						continue;
					}

					preg_match_all($regex_d_foreach, $icerik_yaz, $eslesenler, PREG_SET_ORDER);
					$current_match = $eslesenler[0];
					$e2 = z($current_match[2]);
					$e3_template = q2_($current_match[3]);
					$e4 = [];
					for ($i = 0; $i < count($key_dizi); $i++) {
						$e3 = $e3_template;
						$regex_foreach_item_html = '/{{\s*%(' . preg_quote($e2, '/') . ')\.([^}%\s]+)%?\s*}}/si';
						$e3 = preg_replace_callback(
							$regex_foreach_item_html,
							function ($m3) use ($key_dizi, $i) {
								$alan = $m3[2];
								$deger = '';
								if (is_object($key_dizi[$i]) && isset($key_dizi[$i]->{$alan})) {
									$deger = $key_dizi[$i]->{$alan};
								} elseif (is_array($key_dizi[$i]) && isset($key_dizi[$i][$alan])) {
									$deger = $key_dizi[$i][$alan];
								}
								if (is_scalar($deger)) {
									return (string)$deger;
								}
								return '';
							},
							$e3
						);
						$e3 = $this->render_foreach_if($e3);
						$e4[] = $e3;
					}

					$e4 = implode('', $e4);
					$icerik_yaz = str_replace($current_match[0], $e4, $icerik_yaz);
				}
			}
		}
		return $icerik_yaz;
	}
	
	private function render_foreach_if($icerik) {
		$regex_d_e3 = '/\[\[__if\s*%([^%]+)%((?:(?!__endif\]\]).)+)__endif\]\]/si';
		if (preg_match($regex_d_e3, $icerik, $matches_e3)) {
			return preg_replace_callback($regex_d_e3, function ($matches_e3) {
				$yazi_1_true = trim($matches_e3[2]);
				$yazi_0_false = '';
				if (strpos($yazi_1_true, '||') !== false) {
					$yazi_dizi = explode("||", $yazi_1_true);
					$yazi_1_true = $yazi_dizi[0];
					$yazi_0_false = $yazi_dizi[1];
				}
				return !empty($matches_e3[1]) && (int)$matches_e3[1] === 1 ? $yazi_1_true : $yazi_0_false;
			}, $icerik);
		}
		return $icerik;
	}

	/**
	 * HTML içerisindeki özel etiketleri işleyerek PHP dosyalarını dahil eden metod.
	 * İki tür etiket işler:
	 * 1. [[$degisken]] -> Basit değişken değeri yerleştirme
	 * 2. {{i:konum:dosya}} / {{c:obje:metod}} -> PHP dosyası dahil etme / metod çağırma
	 *
	 * NOT: Eskiden fonksiyon.php içinde dış fonksiyon (sablon_include) olarak
	 * tanımlıydı. Sadece bu class tarafından kullanıldığı için class içine
	 * taşındı ve dış fonksiyondan kaldırıldı.
	 *
	 * @param string $html_ İşlenecek HTML içeriği
	 * @return string İşlenmiş HTML içeriği
	 */
	private function sablon_include($html_) {
		// Basit değişken yerleştirmeleri için: [[$degisken]]
		$html_ = preg_replace_callback('/\[\[\$([^\]]+)\]\]/', function($m_){
			global ${$m_[1]}; // Değişkeni global scope'dan al
			return ${$m_[1]}; // Değerini yerleştir
		}, $html_);

		// {{...}} formatındaki dahil etme etiketlerini işle
		$desen = '/{{(.+)}}/';
		$html_duzenle = preg_replace_callback($desen, function($m){
			global $pdo, $db, $pdo_db, $do_, $so_, $bo_, $indexx, $u_no__, $n;

			// Etiketi parçalara ayır (örn: i:tema:kolon1 -> ['i', 'tema', 'kolon1'])
			$eslesme = explode(":", q_($m[1]));

			// Son parça her zaman dosya adıdır
			$m_dosya_adi_bu = $eslesme[count($eslesme) - 1];
			// Dosya adı whitelist: sadece harf, rakam, nokta, tire, alt çizgi.
			// path traversal (../) ve diğer özel karakterleri engeller.
			$m_dosya_adi_bu = preg_replace('/[^A-Za-z0-9._-]/', '', $m_dosya_adi_bu);
			$m_dosya_uzantisi_bu = 'php';

			if (!isset($eslesme[1])) {
				return; // Geçersiz format, işleme devam etme
			}

			// Dosyanın aranacağı dizini belirle:
			// - tema: Tema dosyaları için (örn: header.php, footer.php)
			// - yon: Yönlendirme dosyaları için (örn: kolon1.php, sayfa.php)
			//        Bu dosyalar genelde dinamik içerik veya sayfa parçaları içerir
			// - varsayılan: Ana PHP dizini
			//{{i:yon:...}}
			if ($eslesme[1] === 'tema') {
				$dosya_yolu_dizin = TEMA_DIR . '/' . $so_->d('tema');
			} elseif ($eslesme[1] === 'yon') {
				$dosya_yolu_dizin = YONLENDIR_D;
			} else {
				$dosya_yolu_dizin = R_PHP;
			}
			$dosya_adi = $m_dosya_adi_bu . '.' . $m_dosya_uzantisi_bu;
			$dosya_yolu_dosya = $dosya_yolu_dizin . '/' . $dosya_adi;

			//process_log("Trying to include: " . $dosya_yolu_dosya);

			if ($eslesme[0]==='i' || $eslesme[0]==='i1'){

				if (strpos($eslesme[2], '+') !== false) {
					$e2 = trim($eslesme[2],'+');
					global ${$e2};

					if (isset($$e2) && $$e2==true) {
						ob_start();
						if (is_file($dosya_yolu_dosya)){
							//include_once dosya aynı anda iki defa kullanılamaz. çakışmalara veya döngülere girmemesi için. bu tekrar düzenlenebilir.
							include_once ($dosya_yolu_dosya);
						}
						return ob_get_clean();
					}

				} elseif ($eslesme[1]==='$'){
					global $$m_dosya_adi_bu;/**burada $kolon1 olarak kullanılıyor {{i:$:kolon1}} -> include_once $kolon1;*/
					ob_start();
						if (is_file($$m_dosya_adi_bu)){
							include_once ($$m_dosya_adi_bu);
						}
					return ob_get_clean();
				} elseif (is_file($dosya_yolu_dosya)) {
					ob_start();
					include_once ($dosya_yolu_dosya);
					return ob_get_clean();
				} else {
					return $m[0];
				}
			} elseif ($eslesme[0]==='c') {
				global ${$eslesme[1]};
				$obje_ = ${$eslesme[1]};
				$obje_fn_ = $eslesme[2];
				return $obje_->$obje_fn_();
			}

		}, $html_);
		return $html_duzenle;
	}

	public function xss_sil($input) {
		$input = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $input);
		$input = preg_replace('/\bon\w+="[^"]*"/i', '', $input);
		return $input;
	}

}


class tablo_satir_kopyala {
	private $from_tablo;
	private $to_tablo;
	public $from_id = [];
	public $exclude_columns = [];
	public $update_columns = [];
	public $index_alanlar = [];
	public $eklenecek_kolonlar = [];
	public $unique_update = true;
	private $pdo;
	public $son_eklenen_id_dizi = [];

	function __construct($pdo, $from_tablo, $to_tablo, $from_id, $exclude_columns = [], $update_columns = []) {
		$this->pdo = $pdo;
		$this->from_tablo = $from_tablo;
		$this->to_tablo = $to_tablo;
		$this->from_id = $from_id;
		$this->exclude_columns = $exclude_columns;
		$this->update_columns = $update_columns;

		$this->index_alanlar();
		$this->eklenecek_kolonlar();
	}

	public function index_alanlar() {
		try {
			$stmt = $this->pdo->query("SHOW INDEX FROM `" . $this->from_tablo . "`");
			$indexler = $stmt->fetchAll(PDO::FETCH_OBJ);
			
			foreach ($indexler as $index) {
				if ($index->Non_unique == 0) {
					$this->index_alanlar[] = $index->Column_name;
				}
			}
			$this->index_alanlar = array_diff($this->index_alanlar, $this->exclude_columns);
		} catch (PDOException $e) {
			throw new Exception('Failed to get indexes: ' . $e->getMessage());
		}
	}

	public function eklenecek_kolonlar() {
		try {
			$stmt = $this->pdo->query("DESCRIBE $this->from_tablo");
			$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
			
			$this->eklenecek_kolonlar = array_diff($columns, $this->exclude_columns);
			$this->eklenecek_kolonlar = array_diff($this->eklenecek_kolonlar, $this->index_alanlar);
		} catch (PDOException $e) {
			throw new Exception('Failed to get columns: ' . $e->getMessage());
		}
	}

	public function kopyala() {
		if (empty($this->eklenecek_kolonlar)) {
			return false;
		}

		try {
			$this->pdo->beginTransaction();
			
			$insertColumns = implode(', ', $this->eklenecek_kolonlar);
			$kopyalandi = false;
			
			foreach ($this->from_id as $from_id) {
				$sql = "INSERT INTO $this->to_tablo ($insertColumns) 
						SELECT $insertColumns FROM $this->from_tablo WHERE no = :from_id";
				$stmt = $this->pdo->prepare($sql);
				$stmt->execute(['from_id' => $from_id]);
				
				if ($stmt->rowCount() > 0) {
					$kopyalandi = true;
					$lastId = $this->pdo->lastInsertId();
					$this->son_eklenen_id_dizi[] = $lastId;
					
					if ($this->unique_update) {
						foreach ($this->index_alanlar as $ia) {
							$stmtSelect = $this->pdo->prepare("SELECT $ia FROM $this->from_tablo WHERE no = :from_id");
							$stmtSelect->execute(['from_id' => $from_id]);
							$value = $stmtSelect->fetchColumn() . '_';
							
							$stmtUpdate = $this->pdo->prepare("UPDATE $this->to_tablo SET $ia = :value WHERE no = :last_id");
							$stmtUpdate->execute([
								'value' => $value,
								'last_id' => $lastId
							]);
						}
					}

					if (!empty($this->update_columns)) {
						foreach ($this->update_columns as $col => $new_value) {
							$stmtUpdateCol = $this->pdo->prepare("UPDATE $this->to_tablo SET $col = :val WHERE no = :last_id");
							$stmtUpdateCol->execute(['val' => $new_value, 'last_id' => $lastId]);
						}
					}
				}
			}
			
			$this->pdo->commit();
			return $kopyalandi;
		} catch (PDOException $e) {
			$this->pdo->rollBack();
			throw new Exception('Copy operation failed: ' . $e->getMessage());
		}
	}
}

//new sms("https://smsgw.mutlucell.com/smsgw-ws/sndblkex",'532---------', "123456", 'firmaorg-adi');
//new sms("http://api.smsvitrini.com/index.php",...);
class sms {
	public $url, $ka, $pwd, $org, $gonder;
	public function __construct($url, $ka='', $pwd = '', $org = '') {
		$this->url = $url;
		$this->ka = $ka;
		$this->pwd = $pwd;
		$this->org = $org;
		$this->gonder = (!empty($url) && !empty($ka) && !empty($pwd));
	}
	
	function gonder($no, $mesaj) {
		try {
			if ($this->gonder) {
				if (strpos($this->url,'mutlucell') !== false) {
					$this->gonder_mutlucell($no, $mesaj);
				} elseif (strpos($this->url,'smsvitrini') !== false) {
					$this->gonder_smsvitrini($no, $mesaj);
				}
			}
		} catch (Exception $e) {
			//echo $e->getMessage(), "\n";
		}
	}

	function gonder_mutlucell($no,$mesaj){
		$xml_data = '<?xml version="1.0" encoding="UTF-8"?>
		<smspack ka="'.$this->ka.'" pwd="'.$this->pwd.'" org="'.$this->org.'"><mesaj><metin>'.$mesaj.'</metin><nums>'.ltrim(strtr($no, array(' '=>'')),"0").'</nums></mesaj></smspack>';
		$ch = curl_init($this->url);
		/*@curl_setopt($ch, CURLOPT_MUTE, 1);*/
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml_data");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		curl_close($ch);
	}

	function gonder_smsvitrini ($numaralar, $mesaj){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1) ;
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'islem=1&user='.$this->ka.'&pass='.$this->pwd.'&mesaj='.$mesaj.'&numaralar='.$numaralar.'&baslik='.$this->org);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}	 
}


class statickidzGoogleTranslate {//@link https://statickidz.com/
	public static function translate($source, $target, $text) {
		$response = self::requestTranslation($source, $target, $text);
		return (!empty($response)) ? self::getSentencesFromJSON($response) : "";
	}

	protected static function requestTranslation($source, $target, $text) {
		if (strlen($text) >= 5000) {
			return "";
			//throw new \Exception("Maximum number of characters exceeded: 5000");
		}
		$url = "https://translate.googleapis.com/translate_a/single?client=gtx&dt=t";
		$fields = [
			'sl' => urlencode($source),
			'tl' => urlencode($target),
			'q' => urlencode($text)
		];
		$fields_string = "";
		foreach ($fields as $key => $value) {
			$fields_string .= '&' . $key . '=' . $value;
		}
		rtrim($fields_string, '&');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	protected static function getSentencesFromJSON($json) {
		$sentencesArray = json_decode($json, true);
		$sentences = "";
		if (!$sentencesArray || !isset($sentencesArray[0])) {
			//throw new \Exception("Google detected unusual traffic from your computer network, try again later (2 - 48 hours)");
		} else {
			foreach ($sentencesArray[0] as $s) {
				$sentences .= isset($s[0]) ? $s[0] : '';
			}
		}
		return $sentences;
	}
}


class microsoftTranslator{
	public $key, $location;
	public $endpoint = "https://api.cognitive.microsofttranslator.com";
	
	public function __construct($key, $location, $endpoint='') {
		if (empty($key)) {
			error_log('Microsoft Translator API key boş olamaz! .env dosyasında MICROSOFT_TRANSLATOR_KEY değerini kontrol edin.');
		}
		if (empty($location)) {
			error_log('Microsoft Translator region boş olamaz! .env dosyasında MICROSOFT_TRANSLATOR_REGION değerini kontrol edin.');
		}
		$this->key = $key;
		$this->location = $location;
		if (!empty($endpoint)) {
			$this->endpoint = $endpoint;
		}
	}
	public function gonder($from, $to, $text){
		$path = '/translate';
		$params = array(
			'api-version' => '3.0',
			'from' => 'tr',
			'to' => [$to]
		);
		$constructed_url = $this->endpoint . $path . '?' . http_build_query($params);
		$headers = array(
			'Ocp-Apim-Subscription-Key: ' . $this->key,
			'Ocp-Apim-Subscription-Region: ' . $this->location,// Location is required if you're using a multi-service or regional (not global) resource.
			'Content-type: application/json',
			'X-ClientTraceId: ' . uniqid()
		);
		$body = array(
			array(
				'text' => $text
			)
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $constructed_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($response);
		return (json_last_error() === JSON_ERROR_NONE) ? $response : false;
	}
	public function getir($response){
		if ($response !== false) {
			$decodedResponse = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
			if (isset($decodedResponse[0]['translations'][0]['text'])) {
				$translatedText = $decodedResponse[0]['translations'][0]['text'];
				return $translatedText;
			} else {
				//echo "Error: Unable to retrieve translated text.";
			}
		} else {
			//echo "Error occurred while calling the Translator API.";
		}
	}
}

class deeplTranslator {
	public $key;
	public $endpoint = "https://api-free.deepl.com/v2/translate";
	
	public function __construct($key, $endpoint = '') {
		if (empty($key)) {
			error_log('DeepL API key boş olamaz! .env dosyasında DEEPL_API_KEY değerini kontrol edin.');
		}
		$this->key = $key;
		if (!empty($endpoint)) {
			$this->endpoint = $endpoint;
		}
	}
	
	public function gonder($from, $to, $text) {
		$data = [
			'text' => $text,
			'target_lang' => strtoupper($to)
		];
		
		// Kaynak dil belirtilirse ekle, yoksa otomatik tespit
		if ($from && $from !== 'auto') {
			$data['source_lang'] = strtoupper($from);
		}
		
		$headers = [
			'Authorization: DeepL-Auth-Key ' . $this->key,
			'Content-Type: application/x-www-form-urlencoded'
		];
		
		$ch = curl_init($this->endpoint);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$response = curl_exec($ch);
		curl_close($ch);
		
		return (json_last_error() === JSON_ERROR_NONE || !empty($response)) ? $response : false;
	}
	
	public function getir($response) {
		if ($response !== false) {
			$decodedResponse = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
			if (isset($decodedResponse['translations'][0]['text'])) {
				return $decodedResponse['translations'][0]['text'];
			} else {
				// Hata mesajı varsa loglayalım
				if (isset($decodedResponse['message'])) {
					error_log('DeepL API Hatası: ' . $decodedResponse['message']);
				}
			}
		}
		return '';
	}
}

class giris_log_yaz {
	private $dosyaAdi;
	public $zamanDamgasi;

	public function __construct($dosyaAdi, $zamanDamgasi = false) {
		$this->dosyaAdi = $dosyaAdi;
		$this->zamanDamgasi = $zamanDamgasi;
		if (!file_exists($this->dosyaAdi)) {
			file_put_contents($this->dosyaAdi, date("Y-m-d H:i:s") . PHP_EOL, FILE_APPEND);
		}
	}

	public function yaz($mesaj) {
		$logMesaji = '';
		if ($this->zamanDamgasi) {
			$zamanDamgasi = date("Y-m-d H:i:s");
			$logMesaji .= "[" . $zamanDamgasi . "] ";
		}
		$logMesaji .= $mesaj . PHP_EOL;
		file_put_contents($this->dosyaAdi, $logMesaji, FILE_APPEND);
	}
}


class LoginRateLimiter {
	private PDO $pdo;
	private string $do_;
	private int $maxAttempts;
	private int $blockDuration;
	private string $ip;
	private string $tablo = 'giris_deneme_log';

	public function __construct(PDO $pdo, $do_, int $maxAttempts = 5, int $blockDuration = 300) {
		$this->pdo = $pdo;
		$this->do_ = $do_;
		$this->maxAttempts = $maxAttempts;
		$this->blockDuration = $blockDuration;
		$this->ip = getIP();
		$this->tablo = $this->do_ . $this->tablo;
	}

	public function canAttempt(): bool {
		$stmt = $this->pdo->prepare("SELECT deneme_sayisi, son_deneme FROM {$this->tablo} WHERE ip = :ip");
		$stmt->execute(['ip' => $this->ip]);
		$record = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$record) {
			return true;
		}

		$elapsed = time() - strtotime($record['son_deneme']);
		if ($record['deneme_sayisi'] >= $this->maxAttempts && $elapsed < $this->blockDuration) {
			return false;
		}

		if ($elapsed >= $this->blockDuration) {
			$reset = $this->pdo->prepare("UPDATE {$this->tablo} SET deneme_sayisi = 0 WHERE ip = :ip");
			$reset->execute(['ip' => $this->ip]);
		}

		return true;
	}

	public function recordAttempt(bool $success): void {
		if ($success) {
			$this->pdo->prepare("DELETE FROM {$this->tablo} WHERE ip = :ip")->execute(['ip' => $this->ip]);
		} else {
			$stmt = $this->pdo->prepare("SELECT deneme_sayisi FROM {$this->tablo} WHERE ip = :ip");
			$stmt->execute(['ip' => $this->ip]);
			$record = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($record) {
				$this->pdo->prepare("UPDATE {$this->tablo} SET deneme_sayisi = deneme_sayisi + 1, son_deneme = NOW() WHERE ip = :ip")
					->execute(['ip' => $this->ip]);
			} else {
				$this->pdo->prepare("INSERT INTO {$this->tablo} (ip, deneme_sayisi, son_deneme) VALUES (:ip, 1, NOW())")
					->execute(['ip' => $this->ip]);
			}
		}
	}

	/**
	 * Kullanıcının tekrar deneme yapabileceği zamana kadar kalan süreyi saniye cinsinden döndürür
	 */
	public function getRemainingTime(): int {
		$stmt = $this->pdo->prepare("SELECT deneme_sayisi, son_deneme FROM {$this->tablo} WHERE ip = :ip");
		$stmt->execute(['ip' => $this->ip]);
		$record = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$record || $record['deneme_sayisi'] < $this->maxAttempts) {
			return 0;
		}

		$elapsed = time() - strtotime($record['son_deneme']);
		$remaining = $this->blockDuration - $elapsed;
		
		return max(0, $remaining);
	}

	/**
	 * Kullanıcının yaptığı deneme sayısını döndürür
	 */
	public function getAttemptCount(): int {
		$stmt = $this->pdo->prepare("SELECT deneme_sayisi FROM {$this->tablo} WHERE ip = :ip");
		$stmt->execute(['ip' => $this->ip]);
		$record = $stmt->fetch(PDO::FETCH_ASSOC);
		
		return $record ? (int)$record['deneme_sayisi'] : 0;
	}
}

class nested_select {
	private $pdo;
	private $db_from;
	private $db_where;
	private $db_orderby;
	private $adi;
	public $option = true;

	public function __construct($pdo, $db_from, $db_where, $db_orderby, $adi) {
		$this->pdo = $pdo;
		$this->db_from = $db_from;
		$this->db_where = $db_where;
		$this->db_orderby = $db_orderby;
		$this->adi = $adi;
	}

	public function list_yaz($sabit_selected_no, $grup_no, $level = 0) {
		$yaz = '';
		$sql = "SELECT no, $this->adi FROM $this->db_from WHERE $this->db_where = :grup_no ORDER BY $this->db_orderby";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(['grup_no' => $grup_no]);
		$urun_gruplari = $stmt->fetchAll(PDO::FETCH_OBJ);
		if ($urun_gruplari) {
			foreach ($urun_gruplari as $ug) {
				if ($this->option) {
					$selected = $ug->no == $sabit_selected_no ? ' selected="selected"' : '';
					$yaz .= '<option value="'.$ug->no.'"'.$selected.' data-o-l="'.$level.'">'.str_repeat(' ​', $level * 4) . $ug->{$this->adi}.'</option>';
					//str_repeat(' ')->zwsc
				} else {
					$selected_class = $ug->no == $sabit_selected_no ? ' div_option_burada' : '';
					$yaz .= '<div class="'.$selected_class.'" data-div-option-level="'.$level.'" data-n="'.$ug->no.'">'.$ug->{$this->adi}.'</div>';
				}
				$yaz .= $this->list_yaz($sabit_selected_no, $ug->no, $level + 1);				
			}
		}
		return $yaz;
	}
}



class db_adi {
	public static function get(PDO $pdo, string $do_, string $table, ?string $where, string $column = 'adi') {
		if ($where === null) {
			return null;
		}
		try {
			$query = "SELECT {$column} FROM {$do_}{$table} WHERE no = :where";
			$stmt = $pdo->prepare($query);
			$stmt->execute(['where' => $where]);
			$result = $stmt->fetchColumn();
			return $result === false ? null : $result;
		} catch (PDOException $e) {
			error_log("PDO Error in db_adi::get(): " . $e->getMessage());
			return null;
		}
	}
}


class yeni_kayit_post {
	private $pdo;
	private $do_;
	private $post_submit;
	private $set_dolu_post_dizi = [];
	private $islem_mesaj = '';
	public $ekle = true;
	public $insert_alanlar_post_db = [];
	public $url_kontrol = '';
	public $ek_insert_alan_value = [];
	public $debug = false;

	public function __construct(PDO $pdo, string $do_, string $post_submit, array $set_dolu_post_dizi) {
		//global $db;
		$this->pdo = $pdo;
		$this->do_ = $do_;
		$this->post_submit = isset($_POST[$post_submit]) ? z($_POST[$post_submit]) : false;
		foreach ($set_dolu_post_dizi as $set_dolu_post_key => $set_dolu_post_value) {
			$this->ekle = (set_dolu($set_dolu_post_key, $set_dolu_post_value)) ? $this->ekle : false;
		}
	}

	private function url_olustur($db_tablo) {
		if (empty($this->url_kontrol)) {
			return '';
		}
		
		$url = url_duzenle($_POST[$this->url_kontrol]);
		return url_kayit_kontrol_degistir($this->pdo, $this->do_ . $db_tablo, $url);
	}

	public function ekle(string $db_tablo) {
		$return = false;
		if ($this->post_submit === false) {
			$this->islem_mesaj = '';
			return false;
		}

		if ($this->ekle) {
			$url = $this->url_olustur($db_tablo);

			$db_insert_alanlar = array_values($this->insert_alanlar_post_db);
			$db_insert_into = implode(', ', $db_insert_alanlar);
			
			if (!empty($url)) {
				$db_insert_into .= ", url";
			}

			$placeholders = [];
			$values = [];
			$post_alanlar = array_keys($this->insert_alanlar_post_db);
			
			foreach ($post_alanlar as $post_alan) {
				$placeholders[] = "?";
				$values[] = z($_POST[$post_alan]);
			}
			
			if (!empty($url)) {
				$placeholders[] = "?";
				$values[] = $url;
			}

			if (!empty($this->ek_insert_alan_value)) {
				foreach ($this->ek_insert_alan_value as $ek_alan_db => $ek_alan_deger) {
					$db_insert_into .= ', ' . $ek_alan_db;
					$placeholders[] = "?";
					$values[] = $ek_alan_deger;
				}

			}
			
			$placeholders_str = implode(', ', $placeholders);
			
			try {
				$sql = "INSERT INTO {$this->do_}{$db_tablo} ($db_insert_into) VALUES ($placeholders_str)";
				$stmt = $this->pdo->prepare($sql);
				
				$result = $stmt->execute($values);
				
				if ($result) {
					$this->islem_mesaj .= 'Kayıt Eklendi';
					$return = true;
				} else {
					$this->islem_mesaj .= 'Kayıt Eklenemedi';
					$return = false;
				}
			} catch (PDOException $e) {
				$this->islem_mesaj .= 'Kayıt Eklenemedi.' . ($this->debug ? $e->getMessage() : '');
				$return = false;
			}
		} else {
			$this->islem_mesaj .= '! Gerekli alanlar bulunamadı. Kayıt Eklenemedi';
			$return = false;
		}
		return $return;
	}

	public function islem_mesaj() {
		return $this->islem_mesaj;
	}
}



class nestable_menu_yaz { 
	private $pdo, $do_; 
	private $yaz = ''; 
	private $ust_menu_no = 0; 
	private $max_level = 10; 
	private $sayi = 0; 
	
	public function __construct($pdo, $do_) { 
		$this->pdo = $pdo; 
		$this->do_ = $do_; 
	} 
	
	public function menu() { 
		$sql = "SELECT no, tablo, tablo_no, ust_menu_no, dis_link, adi, sira 
				FROM {$this->do_}menu 
				WHERE ust_menu_no = :ust_menu_no 
				ORDER BY sira ASC";
		
		$stmt = $this->pdo->prepare($sql);
		$stmt->bindParam(':ust_menu_no', $this->ust_menu_no, PDO::PARAM_INT);
		$stmt->execute();
		$liste = $stmt->fetchAll(PDO::FETCH_OBJ);

		if ($liste) { 
			$this->yaz .= '<ul class="n-dd-list">'; 

			$this->sayi++; 
			foreach ($liste as $m) { 
				$tablo_yazi = '<sup><big><sup>('.strtoupper(str_replace("_"," ",$m->tablo)).')</sup></big></sup>'; 
				$tablo_yazi .= ($m->tablo==='menu') ? ' <span class="btn btn-xs btn-primary menu_dis_link_duzenle" data-mno="'.$m->no.'" data-adi="'.htmlspecialchars($m->adi).'" data-link="'.htmlspecialchars($m->dis_link).'">✎</span>': '' ; 
				
				$tablo_sql = "SELECT no, adi FROM {$this->do_}{$m->tablo} WHERE no = :tablo_no";
				$tablo_stmt = $this->pdo->prepare($tablo_sql);
				$tablo_stmt->bindParam(':tablo_no', $m->tablo_no, PDO::PARAM_INT);
				$tablo_stmt->execute();
				$tablo_b = $tablo_stmt->fetch(PDO::FETCH_OBJ);
				
				$tablo_adi = $tablo_b?->adi ?? '';
				
				$this->yaz .= '
				<li class="n-dd-item n-dd3-item" data-id="'.$m->no.'">
				<div class="n-dd-handle n-dd3-handle"> </div>
				<div class="n-dd3-content">
				'.$tablo_adi.' '.$tablo_yazi.'
				<div class="n-dd3-sil" data-n="'.$m->no.'" data-adi="'.$tablo_adi.'">x</div>
				</div>'; 
			
				$this->ust_menu_no = $m->no; 
				if ($this->sayi < $this->max_level) { 
					$this->menu(); 
				} 
			
				$this->yaz .= '</li>'; 
			} 

			$this->yaz .= '</ul>'; 

			$this->sayi = 0; 
		} 
	} 
	
	public function yaz() { 
		return $this->yaz; 
	}
}

class NestableMenuProcessor {
	public static function processArray(PDO $pdo, string $do_, array $array, int $ust_id = 0): void {
		$sira = 200;
		foreach ($array as $value) {
			$sira += 40;
			$sql = "UPDATE {$do_}menu SET ust_menu_no = :ust_id, sira = :sira WHERE no = :id";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([
				':ust_id' => $ust_id,
				':sira'   => $sira,
				':id'     => $value['id']
			]);

			if (!empty($value['children']) && is_array($value['children'])) {
				$new_ust_id = (syetki([2,3])) ? $value["id"] : 0;
				self::processArray($pdo, $do_, $value["children"], $new_ust_id);
			}
		}
	}
}




class yeni_kayit_insert {
    private $pdo;
    public $do_; // tablo prefix veya adı
    public $tablo = ''; // tablo adı
    public $submit = ''; // form submit alanı adı
    public $insert_alanlar = []; // ['alan_adi' veya 'alan_adi' => ['db' => 'db_adi', 'kaynak' => 'p|g']]
    public $sifreli_alanlar = []; // şifrelenmiş alanlar
    public $ek_insert_alanlar = []; // ['db_alan_adi' => 'deger']
    public $url_kontrol = null; // string veya dizi, bkz açıklama
	public $url_kontrol_alan = null;
    public $get_kontrol = false; // true ise GET'ten de veri alınır
    public $debug = false;

    private $islem_mesaj = '';

    public function __construct(PDO $pdo, string $do_, string $tablo, string $submit = 'yenikayit') {
        $this->pdo = $pdo;
        $this->do_ = $do_;
        $this->tablo = $tablo;
        $this->submit = $submit;
    }

    private function veriGetir(string $alan, string $kaynak = 'p') {
        if ($kaynak === 'p' && isset($_POST[$alan])) {
            return $_POST[$alan];
        } elseif ($kaynak === 'g' && $this->get_kontrol && isset($_GET[$alan])) {
            return $_GET[$alan];
        }
        return null;
    }

    private function islemOncesiTemizle(string $deger, string $alan) {
        $deger = trim($deger);
        if (in_array($alan, $this->sifreli_alanlar)) {
            $deger = sifre_ac($deger); // dış fonksiyon
        }
        $deger = z($deger); // sanitize fonksiyonu
        return $deger;
    }

    private function urlOlustur() {
        if (empty($this->url_kontrol)) {
            return '';
        }

        $kontrol_tablolar = [];
        $urlKaynakAlani = '';

        if (is_string($this->url_kontrol)) {
            // Kaynak alan adı string olarak verildi, kontrol sadece bu tablo
            $urlKaynakAlani = $this->url_kontrol;
            $kontrol_tablolar = [$this->do_ . $this->tablo];
        } elseif (is_array($this->url_kontrol)) {
            // Sadece kontrol tabloları listesi, kaynak 'adi' varsayılıyor
            $kontrol_tablolar = $this->url_kontrol;
            $urlKaynakAlani = $this->url_kontrol_alan ?? 'adi';
        } else {
            // Tanımsız veya hatalı değer varsa boş dön
            return '';
        }

        $urlDeger = $this->veriGetir($urlKaynakAlani, 'p');
        if ($this->get_kontrol && $urlDeger === null) {
            $urlDeger = $this->veriGetir($urlKaynakAlani, 'g');
        }

        // Eğer formdan gelmezse insert_alanlardan varsa oradan al
        if (empty($urlDeger) && isset($this->insert_alanlar[$urlKaynakAlani])) {
            $urlDeger = $_POST[$urlKaynakAlani] ?? null;
        }

        if (empty($urlDeger)) {
            return '';
        }

        $url = url_duzenle($urlDeger);
        return url_kayit_kontrol_degistir($this->pdo, $kontrol_tablolar, $url);
    }

    public function ekle(): bool {
        $submitDeger = $this->veriGetir($this->submit, 'p');
        if ($submitDeger === null && !($this->get_kontrol && $this->veriGetir($this->submit, 'g') !== null)) {
            return false;
        }

        $alanlar = [];
        $placeholders = [];
        $degerler = [];

        // insert_alanlar elemanlarını normalize et (kısa kullanım için)
        foreach ($this->insert_alanlar as $key => $val) {
            if (is_array($val)) {
                $db_adi = $val['db'] ?? $key;
                $kaynak = $val['kaynak'] ?? 'p';
            } else {
                // Eğer sadece string verilmişse (örn: ['adi']), key numeric olur
                if (is_numeric($key)) {
                    $db_adi = $val; // 'adi'
                    $kaynak = 'p';
                    $key = $val; // form alanı adı da 'adi'
                } else {
                    $db_adi = $val;
                    $kaynak = 'p';
                }
            }

            $deger = $this->veriGetir($key, $kaynak);
            if ($deger === null || $deger === '') {
                $this->islem_mesaj = "Gerekli alan ($key) eksik.";
                return false;
            }

            $deger = $this->islemOncesiTemizle($deger, $key);

            $alanlar[] = $db_adi;
            $placeholders[] = "?";
            $degerler[] = $deger;
        }

        // URL alanı otomatik ekleniyor
        $url = $this->urlOlustur();
        if (!empty($url)) {
            $alanlar[] = 'url';
            $placeholders[] = '?';
            $degerler[] = $url;
        }

        // Ekstra insert alanları
        foreach ($this->ek_insert_alanlar as $db_alan => $deger) {
            $alanlar[] = $db_alan;
            $placeholders[] = "?";
            $degerler[] = $deger;
        }

        $alanlar_str = implode(", ", $alanlar);
        $placeholders_str = implode(", ", $placeholders);
        $tablo_adi = $this->do_ . $this->tablo;

        try {
            $sql = "INSERT INTO $tablo_adi ($alanlar_str) VALUES ($placeholders_str)";
            
            if ($this->debug) {
                $this->islem_mesaj .= "SQL: $sql | Değerler: " . implode(', ', $degerler) . " | ";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $basarili = $stmt->execute($degerler);

            if ($basarili) {
                $this->islem_mesaj = "Kayıt başarıyla eklendi.";
                return true;
            } else {
                $this->islem_mesaj = "Kayıt eklenemedi.";
                return false;
            }
        } catch (PDOException $e) {
            $this->islem_mesaj = "Hata oluştu: " . ($this->debug ? $e->getMessage() : 'Veritabanı hatası');
            return false;
        }
    }

    public function islem_mesaj(): string {
        return $this->islem_mesaj;
    }
}




/*
 * Config Tabanlı Basit Arama Form Builder
 * 
 * Kullanım:
 * $config = [
 *     'field_name' => [
 *         'type' => 'text|select|checkbox|date|daterange',  // ZORUNLU: Alan tipi
 *         'placeholder' => 'Placeholder metni',              // Opsiyonel: Input placeholder/label
 *         'db_kolon' => 'veritabani_kolon_adi',             // Opsiyonel: DB kolon adı (yoksa field_name kullanılır)
 *         'class' => 'css-class-adi',                       // Opsiyonel: CSS class
 *         'options' => ['1' => 'Aktif', '0' => 'Pasif'],    // Select tipi için ZORUNLU: Seçenekler
 *         'multiple' => true,                               // Opsiyonel: Çoklu seçim (sadece select için)
 *         'data_alan' => 'custom-data'                      // Opsiyonel: data-alan attribute
 *     ]
 * ];
 * 
 * Örnek:
 * $config = [
 *     'yazi_arama' => ['type' => 'text', 'placeholder' => 'Kelime ile ara'],
 *     'durum' => ['type' => 'select', 'options' => ['1' => 'Aktif', '0' => 'Pasif'], 'multiple' => true],
 *     'tarih' => ['type' => 'daterange', 'placeholder' => 'Tarih Aralığı', 'db_kolon' => 'eklenme_tarihi'],
 *     'yayin' => ['type' => 'checkbox', 'placeholder' => 'Sadece Yayında Olanlar']
 * ];
 */
class arama_formu {
	private $config;

	public function __construct($config_dizi) {
		$this->config = [];

		foreach ($config_dizi as $field_name => $ayarlar) {
			$this->config[$field_name] = array_merge([
				'type' => 'text',
				'placeholder' => $field_name,
				'db_kolon' => $field_name,
				'class' => '',
				'data_alan' => ''
			], $ayarlar);
		}
	}

	public function form_html() {
		$html = '';
		foreach ($this->config as $field_name => $config) {
			$config_class = !empty($config['class']) ? $config['class'] . ' ' : '';
			$data_attr = !empty($config['data_alan']) ? 'data-alan="' . $config['data_alan'] . '"' : '';
			
			switch ($config['type']) {
				case 'text':
					$value = isset($_GET[$field_name]) ? htmlspecialchars($_GET[$field_name]) : '';
					$html .= '<input type="text" name="' . $field_name . '" value="' . $value . '" placeholder="' . $config['placeholder'] . '" class="' . $config_class . 'uis-input" ' . $data_attr . ' />';
					break;

				case 'select':
					$selected_values = isset($_GET[$field_name]) ? (is_array($_GET[$field_name]) ? $_GET[$field_name] : [$_GET[$field_name]]) : [];
					$multiple_attr = isset($config['multiple']) && $config['multiple'] ? 'multiple' : '';
					$name_attr = $multiple_attr ? $field_name . '[]' : $field_name;
					
					$html .= '<select name="' . $name_attr . '" ' . $multiple_attr . ' class="' . $config_class . 'uis-select" ' . $data_attr . '>';
					$html .= '<option value="">' . $config['placeholder'] . '</option>';
					
					if (isset($config['options']) && is_array($config['options'])) {
						foreach ($config['options'] as $value => $label) {
							$selected = in_array($value, $selected_values) ? 'selected' : '';
							$html .= '<option value="' . htmlspecialchars($value) . '" ' . $selected . '>' . htmlspecialchars($label) . '</option>';
						}
					}
					$html .= '</select>';
					break;

				case 'checkbox':
					$checked = isset($_GET[$field_name]) && $_GET[$field_name] == '1' ? 'checked' : '';
					$html .= '<label class="uis-label uis-d-inline"><input type="checkbox" name="' . $field_name . '" value="1" ' . $checked . ' class="' . $config_class . 'uis-input uis-w-auto" ' . $data_attr . ' /> ' . $config['placeholder'] . '</label>';
					break;

				case 'date':
					$value = isset($_GET[$field_name]) ? htmlspecialchars($_GET[$field_name]) : '';
					$html .= '<input type="date" name="' . $field_name . '" value="' . $value . '" placeholder="' . $config['placeholder'] . '" class="' . $config_class . 'uis-input" ' . $data_attr . ' />';
					break;

				case 'daterange':
					$value_start = isset($_GET[$field_name . '_start']) ? htmlspecialchars($_GET[$field_name . '_start']) : '';
					$value_end = isset($_GET[$field_name . '_end']) ? htmlspecialchars($_GET[$field_name . '_end']) : '';
					
					$html .= '<span class="uis-flex uis-gap-1">';
					$html .= '<input type="date" name="' . $field_name . '_start" value="' . $value_start . '" placeholder="' . $config['placeholder'] . ' Başlangıç" class="' . $config_class . 'uis-input" ' . $data_attr . ' />';
					$html .= '<input type="date" name="' . $field_name . '_end" value="' . $value_end . '" placeholder="' . $config['placeholder'] . ' Bitiş" class="' . $config_class . 'uis-input" ' . $data_attr . ' />';
					$html .= '</span>';
					break;
			}
		}
		return $html;
	}

	public function sql_where() {
		$conditions = [];

		foreach ($this->config as $field_name => $config) {
			$db_kolon = $config['db_kolon'];

			if ($config['type'] === 'daterange') {
				$start = $_GET[$field_name . '_start'] ?? '';
				$end = $_GET[$field_name . '_end'] ?? '';
				
				if (!empty($start)) {
					$conditions[] = "$db_kolon >= '" . z($start) . "'";
				}
				if (!empty($end)) {
					$conditions[] = "$db_kolon <= '" . z($end) . " 23:59:59'";
				}
			} else {
				if (!isset($_GET[$field_name]) || $_GET[$field_name] === '') {
					continue;
				}

				$value = $_GET[$field_name];

				switch ($config['type']) {
					case 'text':
						$conditions[] = "$db_kolon LIKE '%" . z($value) . "%'";
						break;

					case 'select':
						if (is_array($value)) {
							$safe_values = array_map('z', $value);
							$conditions[] = "$db_kolon IN ('" . implode("','", $safe_values) . "')";
						} else {
							$conditions[] = "$db_kolon = '" . z($value) . "'";
						}
						break;

					case 'checkbox':
						if ($value == '1') {
							$conditions[] = "$db_kolon = 1";
						}
						break;

					case 'date':
						$conditions[] = "DATE($db_kolon) = '" . z($value) . "'";
						break;
				}
			}
		}

		return !empty($conditions) ? ' AND ' . implode(' AND ', $conditions) : '';
	}

	public function arama_yapildi_mi() {
		foreach ($this->config as $field_name => $config) {
			if ($config['type'] === 'daterange') {
				if (!empty($_GET[$field_name . '_start']) || !empty($_GET[$field_name . '_end'])) {
					return true;
				}
			} else {
				if (!empty($_GET[$field_name])) {
					return true;
				}
			}
		}
		return false;
	}
}





/**
 * Yc Container (Internal Only)
 * Acts as the central storage and logic for content-based i18n
 */
class Yc {
	private static array $translations = [];

	public static function setTranslations(array $translations): void {
		self::$translations = $translations;
	}

	public static function get(string $text, ...$args): string {
		if (empty(trim($text))) {
			return $text;
		}

		$translated = self::$translations[$text] 
			?? $text;

		if (empty(trim($translated))) {
			$translated = $text;
		}

		if (empty($args)) {
			return $translated;
		}

		try {
			return sprintf($translated, ...$args);
		} catch (ValueError $e) {
			//error_log('Yc sprintf error: ' . $e->getMessage() . ' | text=' . $text . ' | translated=' . $translated);
			error_log(print_r([
				'TEXT' => $text,
				'TRANSLATED' => $translated,
				'ARGS' => $args,
				'TEXT_HAS_URL' => str_contains($text, '%3A'),
				'TRANSLATED_HAS_URL' => str_contains($translated, '%3A'),
				'TEXT_HAS_HTML' => str_contains($text, '<a'),
				'TRANSLATED_HAS_HTML' => str_contains($translated, '<a'),
			], true));
			return $translated;
		}
	}

	public static function getTranslations(): array {
		return self::$translations;
	}
}

class dil_yonlendir {
    private const COOKIE_KEY  = 'd';
    private const SESSION_KEY = 'd';

    private PDO $pdo;
    private string $do_;

    private string $aktif_dil;
    public string $dil_dosyasi;

    public array $ceviri_dizi = [];
    public int $dil_no = 0;
    public string $dil_adi = '';
    public string $dltr = 'ltr';

    public array $acik_diller_bilgi = [];
    public array $acik_diller_dkod_dizi = [];
    public array $acik_diller_no_dizi = [];

    public function __construct(PDO $pdo, string $do_, bool $yabanci_dil, string $varsayilan_dil = 'tr')
    {
        $this->pdo = $pdo;
        $this->do_ = $do_;

        $db_varsayilan_dil = $this->genel_ayarlar_default_dil();
        $varsayilan_dil = (!empty($db_varsayilan_dil)) ? $db_varsayilan_dil : $varsayilan_dil;
        $this->aktif_dil = $varsayilan_dil;

        if ($yabanci_dil) {
            $this->aktif_dil = $this->detectLanguage($varsayilan_dil);
            $this->loadActiveLanguages();
            $this->validateLanguage($varsayilan_dil);
            $this->loadLanguageMeta($varsayilan_dil);
            $this->persistLanguage();
        }

        $this->dil_dosyasi = CEVIRI_D . '/' . $this->aktif_dil . '.txt';
        $this->loadTranslationFile(CEVIRI_D . '/__i18n_base.txt');
        $this->loadTranslationFile($this->dil_dosyasi);

        Yc::setTranslations($this->ceviri_dizi);
    }

    /* -----------------------------
       Dil seçimi & doğrulama
    ----------------------------- */

    private function detectLanguage(string $fallback): string
    {
        if (isset($_GET[self::COOKIE_KEY]) || isset($_POST[self::COOKIE_KEY])) {
            unset($_COOKIE[self::COOKIE_KEY], $_SESSION[self::SESSION_KEY]);
            return z($_GET[self::COOKIE_KEY] ?? $_POST[self::COOKIE_KEY]);
        }

        if (isset($_COOKIE[self::COOKIE_KEY])) {
            return z($_COOKIE[self::COOKIE_KEY]);
        }

        if (isset($_SESSION[self::SESSION_KEY])) {
            return z($_SESSION[self::SESSION_KEY]);
        }

        return tarayicidili($fallback);
    }

    private function loadActiveLanguages(): void
    {
        $sql = "SELECT no, dkod, adi, adie, adio, ltr 
                FROM {$this->do_}diller 
                WHERE yayin = 1 
                ORDER BY sira ASC, adie ASC";

        $stmt = $this->pdo->query($sql);
        $this->acik_diller_bilgi = $stmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($this->acik_diller_bilgi as $dil) {
            $this->acik_diller_dkod_dizi[] = $dil->dkod;
            $this->acik_diller_no_dizi[]   = (int) $dil->no;
        }
    }

    private function validateLanguage(string $fallback): void
    {
        if (!in_array($this->aktif_dil, $this->acik_diller_dkod_dizi, true)) {
            $this->aktif_dil = $fallback;
        }
    }

    private function loadLanguageMeta(string $fallback): void
    {
        $stmt = $this->pdo->prepare(
            "SELECT no, ltr, adio 
             FROM {$this->do_}diller 
             WHERE dkod = :dkod 
             LIMIT 1"
        );
        $stmt->execute(['dkod' => $this->aktif_dil]);
        $info = $stmt->fetch(PDO::FETCH_OBJ);

        if ($info) {
            $this->dil_no  = ($info->no == $fallback) ? 0 : (int) $info->no;
            $this->dltr    = ($info->ltr == 0) ? 'rtl' : 'ltr';
            $this->dil_adi = $info->adio ?? '';
        }
    }

    private function persistLanguage(): void
    {
        setcookie(self::COOKIE_KEY, $this->aktif_dil, time() + 86400, '/');
        $_SESSION[self::SESSION_KEY] = $this->aktif_dil;
    }

    /* -----------------------------
       Çeviri yükleme
    ----------------------------- */

	private function genel_ayarlar_default_dil(){
		$stmt = $this->pdo->prepare(
            "SELECT deger
             FROM {$this->do_}genel_ayarlar
             WHERE anahtar = 'varsayilan_dil' 
             LIMIT 1"
        );
        $stmt->execute();
        $info = $stmt->fetch(PDO::FETCH_OBJ);
		return $info->deger ?? null;
	}

    private function loadTranslationFile(string $file): void
    {
        if (!is_file($file)) {
            return;
        }

        $content = file_get_contents($file);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines   = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, ':::') === false) {
                continue;
            }

            [$key, $value] = explode(':::', $line, 2);

            $key   = str_replace("&#37;", "%", html_d(trim($key)));
            $value = str_replace("&#37;", "%", html_d(trim($value)));

            if ($value === '') {
                continue;
            }

            $this->ceviri_dizi[$key] = $value;
        }
    }

    /* -----------------------------
       Public helpers
    ----------------------------- */

    public function d(): string
    {
        return $this->aktif_dil;
    }

    public function d_hreflang_yaz(): string
    {
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $url   = $proto . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $url = rtrim(
            preg_replace('/([&?])' . self::COOKIE_KEY . '=[^&]*(&|$)/', '$1', $url),
            '?&'
        );

        $amp = (strpos($url, '?') !== false) ? '&' : '?';
        $out = '';

        foreach ($this->acik_diller_bilgi as $dil) {
            if ($dil->dkod !== $this->aktif_dil) {
                $out .= PHP_EOL .
                    '<link rel="alternate" href="' .
                    $url . $amp . self::COOKIE_KEY . '=' . $dil->dkod .
                    '" hreflang="' . $dil->dkod . '" />';
            }
        }

        return $out;
    }
}




class yc_kelime_tanimla
{
    private array $results = [];

    /**
     * Taranmayacak dosya uzantilari (veri/dokum/arsiv/medya/font vb.).
     * Bunlar cevrilebilir metin icermez veya yanlis yakalamalara yol acar
     * (ornegin .sql dump icindeki ayraclar).
     */
    private const EXCLUDED_EXTENSIONS = [
        'sql', 'bak', 'dump',
        'json', 'xml', 'yml', 'yaml', 'ini', 'env', 'lock',
        'zip', 'gz', 'tar', 'rar', '7z', 'bz2',
        'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico', 'bmp', 'tiff', 'avif',
        'woff', 'woff2', 'ttf', 'eot', 'otf',
        'mp3', 'mp4', 'avi', 'mov', 'webm', 'ogg', 'wav', 'flac',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'map', 'log', 'md',
    ];

    public function findPatterns(string $directory): array
    {
        $this->results = [];
        $this->scan($directory);
        return array_keys($this->results);
    }

    private function scan(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->scan($path);
            } else {
                if (in_array(strtolower(pathinfo($item, PATHINFO_EXTENSION)), self::EXCLUDED_EXTENSIONS, true)) {
                    continue;
                }
                $this->extractFromFile($path);
            }
        }
    }

    private function extractFromFile(string $file): void
    {
        $content = @file_get_contents($file);
        if ($content === false) {
            return;
        }

        $patterns = [
            '/yc\([\'"]([^\'"]+)[\'"]\)/',
            '/·([^·]+)·/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $text) {
                    // 1. Manual markers (backward compatibility)
                    if (substr_count($text, '(.)') > 1) {
                        if (preg_match("/\(\.\)(.+)\(\.\)/is", $text, $parts)) {
                            $text = str_replace($parts[0], '%s', $text);
                        }
                    }
                    if (substr_count($text, '(..)') > 1) {
                        if (preg_match("/\(\.\..*\)(.+)\(\.\..*\)/is", $text, $parts)) {
                            $text = str_replace($parts[0], '%s', $text);
                        }
                    }

                    // 2. Automatic HTML detection (if no manual markers)
                    if (strpos($text, '(.)') === false && strpos($text, '(..)') === false) {
                        $text = preg_replace('/<([a-z1-6]+)\b[^>]*>.*?<\/\1>|<[a-z1-6]+\b[^>]*\/>/is', '%s', $text);
                    }

                    $text = str_replace(['(.)', '(..)'], '%s', $text);
                    
                    if ($this->isValidKey($text)) {
                        $this->results[$text] = true;
                    }
                }
            }
        }
    }

    private function isValidKey(string $text): bool
    {
        if (strpos($text, '`') !== false) return false;
        if (strpos($text, '(.)') !== false) return false;
        if (strpos($text, '(..)') !== false) return false;
        return true;
    }

    public function diffWordLists(array $used, array $defined): array
    {
        $used_l  = $this->normalizeList($used);
        $def_l   = $this->normalizeList($defined);
        return array_diff($used_l, $def_l);
    }

    private function normalizeList(array $list): array
    {
        return array_map(function ($item) {
            return trim($item);
        }, $list);
    }
}



class bilesen_json_ayarlar {
	public $options;

	public function __construct(PDO $pdo, $do_) {
		$this->options = new stdClass();
		
		try {
			$stmt = $pdo->prepare("SELECT no, url FROM {$do_}bilesen WHERE yayin = 1");
			$stmt->execute();
			$db_bilesen = $stmt->fetchAll(PDO::FETCH_OBJ);
			
			if (!empty($db_bilesen)) {
				foreach ($db_bilesen as $b) {
					$json_dosya = BILESEN_DIR . '/' . $b->url . '/config.json';
					if (is_file($json_dosya)) {
						$json_data = file_get_contents($json_dosya);
						$json_decode = json_decode($json_data, true);
						if ($json_decode !== null && json_last_error() === JSON_ERROR_NONE) {
							$this->options->{$b->url} = (object) $json_decode;
						}
					}
				}
			}
		} catch (PDOException $e) {
			error_log("bilesen_json_ayarlar hatası: " . $e->getMessage());
		}
	}
	
	public function d($url, $d) {
		if (isset($this->options->{$url}->{$d}['deger'])) {
			return $this->options->{$url}->{$d}['deger'];
		}
		return null;
	}
}


class log_db {
	private $pdo;
	private $do_;

	public function __construct(PDO $pdo, string $do_) {
		$this->pdo = $pdo;
		$this->do_ = $do_;
	}

	public function yaz($kullaniciNo, $tabloAdi, $kayitNo, $islem, $eskiVeri = null, $yeniVeri = null) {
		$stmt = $this->pdo->prepare("
			INSERT INTO {$this->do_}log_db (
				kullanici_no, tablo_adi, kayit_no, islem, eski_veri, yeni_veri, ip_adresi, user_agent
			) VALUES (
				:kullanici_no, :tablo_adi, :kayit_no, :islem, :eski_veri, :yeni_veri, :ip_adresi, :user_agent
			)
		");

		$stmt->execute([
			':kullanici_no' => $kullaniciNo,
			':tablo_adi' => $tabloAdi,
			':kayit_no' => $kayitNo,
			':islem' => $islem,
			':eski_veri' => $eskiVeri ? json_encode($eskiVeri, JSON_UNESCAPED_UNICODE) : null,
			':yeni_veri' => $yeniVeri ? json_encode($yeniVeri, JSON_UNESCAPED_UNICODE) : null,
			':ip_adresi' => $_SERVER['REMOTE_ADDR'],
			':user_agent' => parseUserAgent($_SERVER['HTTP_USER_AGENT']) ?? '',
		]);
	}

	public function undoLog($logId) {
		$stmt = $this->pdo->prepare("SELECT * FROM {$this->do_}log_db WHERE no = :no");
		$stmt->execute([':no' => $logId]);
		$log = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$log || empty($log['eski_veri'])) {
			return false;
		}

		$data = json_decode($log['eski_veri'], true);
		if (!is_array($data)) {
			return false;
		}

		$fields = [];
		foreach ($data as $key => $value) {
			$fields[] = "`$key` = " . $this->pdo->quote($value);
		}

		$sql = "UPDATE `{$log['tablo_adi']}` SET " . implode(', ', $fields) . " WHERE `no` = :id";
		$update = $this->pdo->prepare($sql);
		$success = $update->execute([':id' => $log['kayit_no']]);

		if ($success) {
			$kullaniciNo = $log['kullanici_no'];
			$tabloAdi = $log['tablo_adi'];
			$kayitNo = $log['kayit_no'];
			$tarih = date('Y-m-d H:i:s');
			$kullanici_adi = db_adi::get($this->pdo, $this->do_, 'uyeler', $kullaniciNo);

			$islem = "{$logId} nolu log [$kullanici_adi($kullaniciNo)] tarafından geri alındı ve {$kayitNo} nolu kayıt eski haline getirildi.";

			$this->yaz($kullaniciNo, $tabloAdi, $kayitNo, $islem, null, $data);
		}

		return $success;
	}

	//kayit_no yoksa tabloya ait tüm kayıtlar getirilir, varsa tablodaki o kayıt numarasına ait tüm kayıtlar getirilir.
	public function getHistory($tabloAdi, $kayitNo = null) {
		if ($kayitNo === null) {
			$stmt = $this->pdo->prepare("SELECT * FROM {$this->do_}log_db WHERE tablo_adi = :tablo ORDER BY tarih DESC");
			$stmt->execute([':tablo' => $tabloAdi]);
		} else {
			$stmt = $this->pdo->prepare("SELECT * FROM {$this->do_}log_db WHERE tablo_adi = :tablo AND kayit_no = :id ORDER BY tarih DESC");
			$stmt->execute([':tablo' => $tabloAdi, ':id' => $kayitNo]);
		}
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}


class modul_css_js {

	private PDO $pdo;
	private string $do_;
	private array $moduller_db = [];
	private array $css_dosyalar = [];
	private array $js_dosyalar = [];

	public function __construct(PDO $pdo, string $do_) {
		$this->pdo = $pdo;
		$this->do_ = $do_;
		$this->moduller_db_yukle();
		$this->dosyalari_tara();
	}

	private function moduller_db_yukle(): void {
		$sql = "SELECT no, url FROM {$this->do_}moduller WHERE yayin = 1";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		$this->moduller_db = $stmt->fetchAll(PDO::FETCH_OBJ);
	}

	private function dosyalari_tara(): void {
		foreach ($this->moduller_db as $modul) {
			$modul_dizin = MODUL_DIR . '/' . $modul->url;
			
			if (!is_dir($modul_dizin)) {
				continue;
			}
			
			$this->dizin_dosyalarini_ekle($modul_dizin, $modul->url);
			
			$css_dizin = $modul_dizin . '/css';
			if (is_dir($css_dizin)) {
				$this->css_dosyalarini_ekle($css_dizin, $modul->url . '/css');
			}
			
			$js_dizin = $modul_dizin . '/js';
			if (is_dir($js_dizin)) {
				$this->js_dosyalarini_ekle($js_dizin);
			}

			if (syetki([2,3]) && is_dir($modul_dizin . '/yp')) {
				$this->css_dosyalarini_ekle($modul_dizin . '/yp');
				$this->js_dosyalarini_ekle($modul_dizin . '/yp');
			}

		}
		
		$this->css_dosyalar = array_unique($this->css_dosyalar);
		$this->js_dosyalar = array_unique($this->js_dosyalar);
	}

	private function dizin_dosyalarini_ekle(string $dizin, string $url_path): void {
		$dosyalar = scandir($dizin);
		if (!$dosyalar) return;
		
		foreach ($dosyalar as $dosya) {
			if ($dosya === '.' || $dosya === '..') continue;
			
			$dosya_yolu = $dizin . '/' . $dosya;
			if (!is_file($dosya_yolu)) continue;
			
			$uzanti = pathinfo($dosya, PATHINFO_EXTENSION);
			$url = str_replace(ROOT, LOCAL, $dosya_yolu);
			
			if ($uzanti === 'css') {
				$this->css_dosyalar[] = $url;
			} elseif ($uzanti === 'js') {
				$this->js_dosyalar[] = $url;
			}
		}
	}

	private function css_dosyalarini_ekle(string $css_dizin, ?string $url_path = null): void {
		$dosyalar = scandir($css_dizin);
		if (!$dosyalar) return;
		
		foreach ($dosyalar as $dosya) {
			if ($dosya === '.' || $dosya === '..' || pathinfo($dosya, PATHINFO_EXTENSION) !== 'css') {
				continue;
			}
			
			$dosya_yolu = $css_dizin . '/' . $dosya;

			if (is_file($dosya_yolu)) {
				$url = $url_path !== null
					? rtrim($url_path, '/') . '/' . $dosya
					: str_replace(ROOT, LOCAL, $dosya_yolu);

				$this->css_dosyalar[] = $url;
			}
		}
	}

	private function js_dosyalarini_ekle(string $js_dizin, ?string $url_path = null): void {
		$dosyalar = scandir($js_dizin);
		if (!$dosyalar) return;
		
		foreach ($dosyalar as $dosya) {
			if ($dosya === '.' || $dosya === '..' || pathinfo($dosya, PATHINFO_EXTENSION) !== 'js') {
				continue;
			}
			
			$dosya_yolu = $js_dizin . '/' . $dosya;

			if (is_file($dosya_yolu)) {
				$url = $url_path !== null
					? rtrim($url_path, '/') . '/' . $dosya
					: str_replace(ROOT, LOCAL, $dosya_yolu);

				$this->js_dosyalar[] = $url;
			}
		}
	}

	public function css_liste(): array {
		return $this->css_dosyalar;
	}

	public function js_liste(): array {
		return $this->js_dosyalar;
	}

	public function css_yaz(): string {
		$output = '';
		foreach ($this->css_dosyalar as $css_dosya) {
			$output .= '<link rel="stylesheet" href="' . htmlspecialchars($css_dosya) . '" />' . PHP_EOL;
		}
		return $output;
	}

	public function js_yaz(): string {
		$output = '';
		foreach ($this->js_dosyalar as $js_dosya) {
			$output .= '<script src="' . htmlspecialchars($js_dosya) . '" defer></script>' . PHP_EOL;
		}
		return $output;
	}

	public function css_birlestir(string $hedef_dosya, bool $sikistir = false): bool {
		return dosyalari_birlestir_yaz($this->css_dosyalar, $hedef_dosya, $sikistir);
	}

	public function js_birlestir(string $hedef_dosya, bool $sikistir = false): bool {
		return dosyalari_birlestir_yaz($this->js_dosyalar, $hedef_dosya, $sikistir);
	}

	public function debug_info(): array {
		return [
			'css_dosyalar' => $this->css_dosyalar,
			'js_dosyalar' => $this->js_dosyalar,
			'toplam_css' => count($this->css_dosyalar),
			'toplam_js' => count($this->js_dosyalar)
		];
	}
}


class bilesen_css_js {

	private PDO $pdo;
	private string $do_;
	private array $bilesen_db = [];

	private array $css_dosyalar = [];
	private array $js_dosyalar = [];
	private array $css_dosyalar_yp = [];
	private array $js_dosyalar_yp = [];
	private array $css_dosyalar_tumu = [];
	private array $js_dosyalar_tumu = [];

	public bool $rand_v = false;

	public function __construct(PDO $pdo, string $do_) {
		$this->pdo = $pdo;
		$this->do_ = $do_;

		$this->bilesen_db_yukle();
		$this->dosyalari_tara();
		$this->tumu_dosyalari_birlestir();
	}

	private function bilesen_db_yukle(): void {
		// aktif bileşenleri veritabanından yükler
		$sql = "SELECT no, url FROM {$this->do_}bilesen WHERE yayin = 1";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();

		$this->bilesen_db = $stmt->fetchAll(PDO::FETCH_OBJ) ?: [];
	}

	private function dosyalari_tara(): void {
		// tüm bileşen dizinlerini tarar
		foreach ($this->bilesen_db as $bilesen) {

			$bilesen_dizin = BILESEN_DIR . '/' . $bilesen->url;

			if (!is_dir($bilesen_dizin)) {
				continue;
			}

			$this->dizin_dosyalarini_ekle($bilesen_dizin);

			$css = $bilesen_dizin . '/css';
			if (is_dir($css)) {
				$this->css_dosyalarini_ekle($css, false);

				$css_yp = $css . '/yp';
				if (is_dir($css_yp)) {
					$this->css_dosyalarini_ekle($css_yp, true);
				}
			}

			$js = $bilesen_dizin . '/js';
			if (is_dir($js)) {
				$this->js_dosyalarini_ekle($js, false);

				$js_yp = $js . '/yp';
				if (is_dir($js_yp)) {
					$this->js_dosyalarini_ekle($js_yp, true);
				}
			}
		}

		// duplicate temizleme + index reset
		$this->css_dosyalar = array_values(array_unique($this->css_dosyalar));
		$this->js_dosyalar = array_values(array_unique($this->js_dosyalar));
		$this->css_dosyalar_yp = array_values(array_unique($this->css_dosyalar_yp));
		$this->js_dosyalar_yp = array_values(array_unique($this->js_dosyalar_yp));

		// deterministik yükleme sırası (00-,10-,20- sistemi)
		sort($this->css_dosyalar);
		sort($this->js_dosyalar);
		sort($this->css_dosyalar_yp);
		sort($this->js_dosyalar_yp);
	}

	private function tumu_dosyalari_birlestir(): void {
		// tüm dosyaları birleştirir
		$this->css_dosyalar_tumu = array_values(array_unique(
			array_merge($this->css_dosyalar, $this->css_dosyalar_yp)
		));

		$this->js_dosyalar_tumu = array_values(array_unique(
			array_merge($this->js_dosyalar, $this->js_dosyalar_yp)
		));

		sort($this->css_dosyalar_tumu);
		sort($this->js_dosyalar_tumu);
	}

	private function dizin_dosyalarini_ekle(string $dizin): void {
		// root seviyesindeki css/js dosyalarını ekler
		$dosyalar = scandir($dizin);
		if (!$dosyalar) return;

		foreach ($dosyalar as $dosya) {

			if ($dosya === '.' || $dosya === '..') {
				continue;
			}

			$path = $dizin . '/' . $dosya;

			if (!is_file($path)) {
				continue;
			}

			$ext = strtolower(pathinfo($dosya, PATHINFO_EXTENSION));
			$url = $this->pathToUrl($path);

			if ($ext === 'css') {
				$this->css_dosyalar[] = $url;
			} elseif ($ext === 'js') {
				$this->js_dosyalar[] = $url;
			}
		}
	}

	private function css_dosyalarini_ekle(string $css_dizin, bool $yp): void {
		// css dosyalarını ekler
		$this->dosya_ekle($css_dizin, 'css', $yp);
	}

	private function js_dosyalarini_ekle(string $js_dizin, bool $yp): void {
		// js dosyalarını ekler
		$this->dosya_ekle($js_dizin, 'js', $yp);
	}

	private function dosya_ekle(string $dizin, string $ext_filter, bool $yp): void {
		// ortak dosya ekleme işlemi
		if (!is_dir($dizin)) {
			return;
		}

		$dosyalar = scandir($dizin);
		if (!$dosyalar) return;

		foreach ($dosyalar as $dosya) {

			if ($dosya === '.' || $dosya === '..') {
				continue;
			}

			if (pathinfo($dosya, PATHINFO_EXTENSION) !== $ext_filter) {
				continue;
			}

			$path = $dizin . '/' . $dosya;

			if (!is_file($path)) {
				continue;
			}

			$url = $this->pathToUrl($path);

			if ($ext_filter === 'css') {
				$yp ? $this->css_dosyalar_yp[] = $url : $this->css_dosyalar[] = $url;
			} else {
				$yp ? $this->js_dosyalar_yp[] = $url : $this->js_dosyalar[] = $url;
			}
		}
	}

	private function pathToUrl(string $path): string {
		// filesystem path -> public url dönüşümü
		return str_replace(ROOT, LOCAL, $path);
	}

	private function urlToPath(string $url): string {
		// public url -> filesystem path güvenli dönüşüm
		$clean = parse_url($url, PHP_URL_PATH);
		return ROOT . $clean;
	}

	public function css_liste(string $tip = ''): array {
		// css liste döndürür
		switch ($tip) {
			case 'yp':
				return $this->css_dosyalar_yp;
			case 'tumu':
				return $this->css_dosyalar_tumu;
			default:
				return $this->css_dosyalar;
		}
	}

	public function js_liste(string $tip = ''): array {
		// js liste döndürür
		switch ($tip) {
			case 'yp':
				return $this->js_dosyalar_yp;
			case 'tumu':
				return $this->js_dosyalar_tumu;
			default:
				return $this->js_dosyalar;
		}
	}

	public function css_yaz(string $tip = ''): string {
		// css html çıktısı üretir
		$dosyalar = $this->css_liste($tip);
		$output = '';

		foreach ($dosyalar as $css) {

			$ver = '';

			if ($this->rand_v) {
				$path = $this->urlToPath($css);

				if (is_file($path)) {
					$ver = '?v=' . filemtime($path);
				}
			}

			$output .= '<link rel="stylesheet" href="' . htmlspecialchars($css . $ver) . '">' . PHP_EOL;
		}

		return $output;
	}

	public function js_yaz(string $tip = ''): string {
		// js html çıktısı üretir
		$dosyalar = $this->js_liste($tip);
		$output = '';

		foreach ($dosyalar as $js) {

			$ver = '';

			if ($this->rand_v) {
				$path = $this->urlToPath($js);

				if (is_file($path)) {
					$ver = '?v=' . filemtime($path);
				}
			}

			$output .= '<script src="' . htmlspecialchars($js . $ver) . '" defer></script>' . PHP_EOL;
		}

		return $output;
	}

	public function css_yaz_tumu(): string {
		// tüm css çıktısını verir
		return $this->css_yaz('tumu');
	}

	public function js_yaz_tumu(): string {
		// tüm js çıktısını verir
		return $this->js_yaz('tumu');
	}

	public function css_birlestir(string $hedef_dosya, bool $sikistir = false, string $tip = ''): bool {
		// css dosyalarını birleştirir
		return dosyalari_birlestir_yaz($this->css_liste($tip), $hedef_dosya, $sikistir);
	}

	public function js_birlestir(string $hedef_dosya, bool $sikistir = false, string $tip = ''): bool {
		// js dosyalarını birleştirir
		return dosyalari_birlestir_yaz($this->js_liste($tip), $hedef_dosya, $sikistir);
	}

	public function debug_info(): array {
		// debug bilgisi döner
		return [
			'css_dosyalar' => $this->css_dosyalar,
			'js_dosyalar' => $this->js_dosyalar,
			'css_dosyalar_yp' => $this->css_dosyalar_yp,
			'js_dosyalar_yp' => $this->js_dosyalar_yp,
			'css_dosyalar_tumu' => $this->css_dosyalar_tumu,
			'js_dosyalar_tumu' => $this->js_dosyalar_tumu,
		];
	}
}


/**
 * Döviz Kuru Yöneticisi - Singleton Pattern
 * 
 * Performans optimizasyonu için kurları bir kere yükler ve cache'ler.
 * Her fiyat_pb_cevir() çağrısında tekrar DB'ye gitmez.
 * 
 * Kullanım:
 * $doviz = new doviz_kur_yonetici($pdo, $do_);
 * $tl_fiyat = $doviz->fiyat_pb_cevir(100, 'dolar', 'tl');
 * 
 * Global kullanım için (a_.php'de başlatılacak):
 * Global_::$doviz_kur = new doviz_kur_yonetici($pdo, $do_);
 * $fiyat = Global_::$doviz_kur->fiyat_pb_cevir(100, 'dolar', 'tl');
 */
class doviz_kur_yonetici {
	private PDO $pdo;
	private string $do_;
	private array $kurlar = [];
	private bool $yuklendi = false;
	private const GUNCELLEME_SURESI = 86400; // 24 saat

	public function __construct(PDO $pdo, string $do_) {
		$this->pdo = $pdo;
		$this->do_ = $do_;
		$this->kurlari_yukle();
	}

	/**
	 * Döviz kurlarını DB'den yükler (constructor'da bir kere çalışır)
	 */
	private function kurlari_yukle(): void {
		if ($this->yuklendi) {
			return;
		}

		try {
			$stmt = $this->pdo->prepare("SELECT deger FROM {$this->do_}genel_ayarlar WHERE anahtar = :anahtar LIMIT 1");
			$stmt->execute(['anahtar' => 'doviz_kuru']);
			$doviz_kuru_json = $stmt->fetchColumn();

			if ($doviz_kuru_json) {
				$doviz_kurlari = json_decode($doviz_kuru_json, true);
				
				if (json_last_error() === JSON_ERROR_NONE && is_array($doviz_kurlari)) {
					$this->kurlar = [
						'tl' => 1.0,
						'dolar' => isset($doviz_kurlari['USD']) ? (float)$doviz_kurlari['USD'] : null,
						'euro' => isset($doviz_kurlari['EUR']) ? (float)$doviz_kurlari['EUR'] : null,
					];
					$this->yuklendi = true;
					return;
				}
			}

			// Yükleme başarısız, varsayılan değerler
			$this->kurlar = ['tl' => 1.0, 'dolar' => null, 'euro' => null];
			$this->yuklendi = true;

		} catch (PDOException $e) {
			error_log('Döviz kurları yükleme hatası: ' . $e->getMessage());
			$this->kurlar = ['tl' => 1.0, 'dolar' => null, 'euro' => null];
			$this->yuklendi = true;
		}
	}

	/**
	 * Belirli bir para biriminin kurunu döndürür
	 * @param string $pb Para birimi ('tl', 'dolar', 'euro')
	 * @return float|null Kur değeri
	 */
	public function kur(string $pb): ?float {
		return $this->kurlar[$pb] ?? null;
	}

	/**
	 * Tüm kurları array olarak döndürür
	 * @return array Kurlar dizisi
	 */
	public function tum_kurlar(): array {
		return $this->kurlar;
	}

	/**
	 * Para birimi çevirme fonksiyonu (Ana kullanım)
	 * @param float|int|string $fiyat Çevrilecek fiyat
	 * @param string $pb_from Kaynak para birimi ('tl', 'dolar', 'euro')
	 * @param string $pb_to Hedef para birimi ('tl', 'dolar', 'euro')
	 * @param int $ondalik Ondalık basamak sayısı (varsayılan: 2)
	 * @return string Formatlanmış fiyat
	 */
	public function fiyat_pb_cevir(float|int|string $fiyat, string $pb_from, string $pb_to, int $ondalik = 2): string {
		$fiyat = (float)$fiyat;

		// Aynı para birimi kontrolü
		if ($pb_from === $pb_to) {
			return number_format(round($fiyat, $ondalik), $ondalik, '.', '');
		}

		// Geçerli para birimi kontrolü
		if (!isset($this->kurlar[$pb_from]) || !isset($this->kurlar[$pb_to])) {
			return number_format(round($fiyat, $ondalik), $ondalik, '.', '');
		}

		$kur_from = $this->kurlar[$pb_from];
		$kur_to = $this->kurlar[$pb_to];

		// Null veya sıfır kontrolü
		if ($kur_from === null || $kur_to === null || $kur_to == 0) {
			return number_format(round($fiyat, $ondalik), $ondalik, '.', '');
		}

		$cevrilen = $fiyat * ($kur_from / $kur_to);
		return number_format(round($cevrilen, $ondalik), $ondalik, '.', '');
	}

	/**
	 * Döviz kurlarını TCMB'den günceller (24 saatte bir)
	 * @return bool Güncelleme yapıldıysa true
	 */
	public function kurlari_guncelle(): bool {
		try {
			$tarih_str = time();

			// Son güncelleme tarihini çek
			$stmt = $this->pdo->prepare("SELECT deger FROM {$this->do_}genel_ayarlar WHERE anahtar = :anahtar LIMIT 1");
			$stmt->execute(['anahtar' => 'doviz_kuru_son_guncelleme']);
			$son_guncelleme = $stmt->fetchColumn();

			// Kur verisi kontrolü
			$doviz_kuru_yok = empty($this->kurlar['dolar']);
			$guncelleme_gerekli = empty($son_guncelleme) || 
								  ((int)$son_guncelleme + self::GUNCELLEME_SURESI < $tarih_str);

			// Güncelleme gerekli mi?
			if ($doviz_kuru_yok || $guncelleme_gerekli) {
				$doviz_kuru_json = $this->tcmb_kur_cek();

				if ($doviz_kuru_json === false) {
					error_log('Döviz kuru güncellenemedi: TCMB\'den veri çekilemedi');
					return false;
				}

				// DB'ye kaydet
				$stmt = $this->pdo->prepare("UPDATE {$this->do_}genel_ayarlar SET deger = :deger WHERE anahtar = :anahtar");
				$stmt->execute(['deger' => $doviz_kuru_json, 'anahtar' => 'doviz_kuru']);

				$stmt = $this->pdo->prepare("UPDATE {$this->do_}genel_ayarlar SET deger = :deger WHERE anahtar = :anahtar");
				$stmt->execute(['deger' => (string)$tarih_str, 'anahtar' => 'doviz_kuru_son_guncelleme']);

				// Cache'i güncelle
				$this->yuklendi = false;
				$this->kurlari_yukle();

				return true;
			}

			return false;

		} catch (PDOException $e) {
			error_log('Döviz kuru güncelleme hatası: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * TCMB'den döviz kurlarını çeker
	 * @param string $kurlar Boşlukla ayrılmış kur kodları
	 * @return string|false JSON formatında kurlar veya hata durumunda false
	 */
	private function tcmb_kur_cek(string $kurlar = 'USD EUR'): string|false {
		try {
			libxml_use_internal_errors(true);
			$xml = simplexml_load_file('https://www.tcmb.gov.tr/kurlar/today.xml');

			if ($xml === false) {
				error_log('TCMB XML yüklenemedi: ' . implode(', ', libxml_get_errors()));
				libxml_clear_errors();
				return false;
			}

			$kurbirim = '';
			$kurlar_array = array_filter(explode(" ", trim($kurlar)));

			if (empty($kurlar_array)) {
				$kurlar_array = ['USD'];
			}

			foreach ($kurlar_array as $kur) {
				foreach ($xml->Currency as $Currency) {
					if ((string)$Currency['Kod'] === $kur) {
						$kur_DS = (string)$Currency->BanknoteSelling;
						$kurbirim .= '"' . $kur . '":"' . $kur_DS . '",';
						break;
					}
				}
			}

			if (empty($kurbirim)) {
				return false;
			}

			return '{' . rtrim($kurbirim, ",") . '}';

		} catch (Exception $e) {
			error_log('TCMB kur çekme hatası: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Debug bilgisi - yüklü kurları gösterir
	 * @return array Debug bilgisi
	 */
	public function debug_info(): array {
		return [
			'yuklendi' => $this->yuklendi,
			'kurlar' => $this->kurlar,
			'dolar_kuru' => $this->kur('dolar'),
			'euro_kuru' => $this->kur('euro')
		];
	}
}


/**
 * Para Birimi Bilgileri
 * 
 * Her para biriminin detaylı bilgilerini tutar (simge, isim, kod, vb.)
 * Singleton pattern ile kullanılır.
 * 
 * Kullanım:
 * $pb_bilgi = new para_birimi_bilgi();
 * echo $pb_bilgi->tl->simge; // ₺
 * echo $pb_bilgi->get('dolar')->isim; // US Dollar
 * 
 * Global kullanım:
 * Global_::$pb_bilgi = new para_birimi_bilgi();
 * echo Global_::$pb_bilgi->dolar->simge; // $
 */
class para_birimi_bilgi {
	public object $tl;
	public object $dolar;
	public object $euro;
	private array $pb_dizi;

	public function __construct() {
		$this->tl = (object)[
			'key' => 'tl',
			'simge' => '₺',
			'simge_html' => '&#8378;',
			'isim' => 'Türk Lirası',
			'isim_en' => 'Turkish Lira',
			'fa_icon' => 'fas fa-lira-sign',
			'kod' => 'TRY',
			'kod_no' => '949',
			'ulke' => 'TR',
			'ulke_adi' => 'Türkiye'
		];

		$this->dolar = (object)[
			'key' => 'dolar',
			'simge' => '$',
			'simge_html' => '&#36;',
			'isim' => 'Amerikan Doları',
			'isim_en' => 'US Dollar',
			'fa_icon' => 'fas fa-dollar-sign',
			'kod' => 'USD',
			'kod_no' => '840',
			'ulke' => 'US',
			'ulke_adi' => 'Amerika Birleşik Devletleri'
		];

		$this->euro = (object)[
			'key' => 'euro',
			'simge' => '€',
			'simge_html' => '&#8364;',
			'isim' => 'Euro',
			'isim_en' => 'Euro',
			'fa_icon' => 'fas fa-euro-sign',
			'kod' => 'EUR',
			'kod_no' => '978',
			'ulke' => 'EU',
			'ulke_adi' => 'Avrupa Birliği'
		];

		// Array olarak da erişim için
		$this->pb_dizi = [
			'tl' => $this->tl,
			'dolar' => $this->dolar,
			'euro' => $this->euro
		];
	}

	/**
	 * Key ile para birimi bilgisini döndürür
	 * @param string $key Para birimi key'i ('tl', 'dolar', 'euro')
	 * @return object|null Para birimi bilgisi
	 */
	public function get(string $key): ?object {
		return $this->pb_dizi[$key] ?? null;
	}

	/**
	 * Tüm para birimlerini array olarak döndürür
	 * @return array Para birimleri dizisi
	 */
	public function tumu(): array {
		return $this->pb_dizi;
	}

	/**
	 * Para birimi var mı kontrolü
	 * @param string $key Para birimi key'i
	 * @return bool
	 */
	public function varmi(string $key): bool {
		return isset($this->pb_dizi[$key]);
	}

	/**
	 * Key'e göre simge döndürür
	 * @param string $key Para birimi key'i
	 * @param bool $html HTML entity kullan mı
	 * @return string Simge
	 */
	public function simge(string $key, bool $html = false): string {
		$pb = $this->get($key);
		if (!$pb) return '';
		return $html ? $pb->simge_html : $pb->simge;
	}

	/**
	 * Key'e göre isim döndürür
	 * @param string $key Para birimi key'i
	 * @param bool $ingilizce İngilizce isim mi
	 * @return string İsim
	 */
	public function isim(string $key, bool $ingilizce = false): string {
		$pb = $this->get($key);
		if (!$pb) return '';
		return $ingilizce ? $pb->isim_en : $pb->isim;
	}

	/**
	 * ISO 4217 kod ile para birimi bul
	 * @param string $kod ISO kodu (TRY, USD, EUR)
	 * @return object|null Para birimi bilgisi
	 */
	public function kod_ile_bul(string $kod): ?object {
		foreach ($this->pb_dizi as $pb) {
			if ($pb->kod === strtoupper($kod)) {
				return $pb;
			}
		}
		return null;
	}

	/**
	 * Dropdown için seçenekler listesi
	 * @param string|null $secili Seçili olan key
	 * @return array [['key' => 'tl', 'label' => '₺ TL - Türk Lirası', 'selected' => true], ...]
	 */
	public function dropdown_secenekler(?string $secili = null): array {
		$secenekler = [];
		foreach ($this->pb_dizi as $key => $pb) {
			$secenekler[] = [
				'key' => $key,
				'label' => "{$pb->simge} {$pb->kod} - {$pb->isim}",
				'label_kisa' => "{$pb->simge} {$pb->kod}",
				'simge' => $pb->simge,
				'selected' => $key === $secili
			];
		}
		return $secenekler;
	}

	/**
	 * Debug bilgisi
	 * @return array
	 */
	public function debug_info(): array {
		return [
			'toplam' => count($this->pb_dizi),
			'keys' => array_keys($this->pb_dizi),
			'para_birimleri' => $this->pb_dizi
		];
	}
}
