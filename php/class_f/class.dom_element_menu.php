<?php

class dom_element {
/**
 * @var $html - Texti alır. $html_node'ye çevirir.
 * $ilk tag ve class'ı bulunur.
 * $db, $do_ veritabanı bağlantısı için kullanılır.
 * $menu_json kullanılmıyor ancak kullanılma ihtimali ile burada
 * $dom_split_tag varsayılan olarak li olarak belirtiliyor ancak yapı içinde ayrıca bulunuyor.
 * $dbtablo_url_yonlendir veritabanı ile ilgili
 * $html_yaz kullanılmıyor ancak kullanılma ihtimali ile burada
 * $split_dizi dom'u $this->dom_split_tag'ı ile ayırıp daha sonra işlem yapmak için kullanılıyor.
 * single_class tanımlamaları tek olan a'ların class'ları şablonda değişikse a'ları onlara uyarlıyor.
 * $a_wrap ve $list_container ul ve li yerine geçer. 
 * $main_list_elements_container ise eğer varsa li elementlerinin içindeki elementleri kapsayan container yerine geçer. Şablonda yoksa boş döner.
 * 
 */
	public $html, $html_node, $db, $do_;
	public $ilk_tag = 'ul';
	public $ilk_tag_class;
	public $dom_split_tag = 'li';
	public $split_tag_node_html_dizi = [];
	public $max_depth;
	public $menu_json;
	public $dbtablo_url_yonlendir = array("sayfa"=>"sayfa","sekme"=>"sekme","blok_html"=>"blok_html","menu"=>"menu");
	public $html_yaz = [];

	public $split_dizi = [];
	
	public $a_is_single_class = [];
	public $a_is_not_single_class = [];
	public $single_class_atandi=false;
	public $single_not_class_atandi=false;

	public $main_list_elements_container = [];
	public $a_wrap = [];
	public $list_container = [];

	public $a_child_wrap_tag = '';
	public $a_child_wrap_class = '';


	public function __construct ($html, $db, $do_) {
		$this->db = $db;
		$this->do_ = $do_;
		$this->html = trim($html);
		$this->html_node = self::html_to_node($this->html); // tüm html -> node
		$this->ilk_tag = self::dom_ilk_tag_node($this->html_node)->tagName; // ul
		$this->ilk_tag_class = self::dom_ilk_tag_node($this->html_node)->getAttribute('class');
		$this->max_depth = self::item_node_max_depth($this->html_node); // 3
		$this->split_tag_node_html_dizi = self::node_item_first_childs_list($this->html_node,""); // elemanlar node dizisi, eğer sadece belirli tagları eklemek için, ikinci parametre:tag adı : li,div,a
		$this->dom_split_tag = self::element_most_tag($this->split_tag_node_html_dizi); //li
		$this->split_dizi = self::node_item_first_childs_list($this->html_node, $this->dom_split_tag);//$this->dom_split_tag = li, burası boş bırakılırsa li tagını alır
	}

	public static function findFirstChildNode(DOMNode $node) {
		$currentNode = $node->firstChild; //ilk child.
		while ($currentNode && !$currentNode instanceof DOMElement) {//ilk child kontrolü. eğer dom element değilse sonraki element ilk child
			$currentNode = $currentNode->nextSibling;
		}
		return $currentNode;
	}
	public static function findLastChildNode(DOMNode $node) {
		$currentNode = $node->lastChild;
		while ($currentNode && !$currentNode instanceof DOMElement) {
			$currentNode = $currentNode->previousSibling;
		}
		return $currentNode;
	}
	public static function previousSibling($previousSibling){
		do {
			$previousSibling = $previousSibling->previousSibling;
		} while ($previousSibling !== null && $previousSibling->nodeType !== XML_ELEMENT_NODE);
		return $previousSibling !== null && $previousSibling->nodeType === XML_ELEMENT_NODE ? $previousSibling : false;
	}
	public static function nextSibling($nextSibling){
		do {
			$nextSibling = $nextSibling->nextSibling;
		} while ($nextSibling !== null && $nextSibling->nodeType !== XML_ELEMENT_NODE);
		return $nextSibling !== null && $nextSibling->nodeType === XML_ELEMENT_NODE ? $nextSibling : null;
	}

	public static function dom_ilk_tag_node($dom){
		if ($dom->hasChildNodes()) {
			foreach ($dom->childNodes as $node) {
				if ($node->nodeType === XML_ELEMENT_NODE && !in_array($node->tagName,['html', 'head', 'body']) && !$dom->isSameNode($node)) {
					return $node;
				}
			}
		}
		return null;
	}

	public static function node_item_first_childs_list($node, $ayrac="") {
		$elements = [];
		$node = $node->documentElement;
		if ($node instanceof DOMNode && $node->hasChildNodes()) {
			foreach ($node->childNodes as $childNode) {
				if ($childNode->nodeType === XML_ELEMENT_NODE) {
					if (empty($ayrac) || $childNode->nodeName == $ayrac){
						$elements[] = $childNode;
					}
				}
			}
		}
		return $elements;
	}
	public static function element_most_tag($node) {
		$elemen_tag_array = [];
		foreach ($node as $e) {
			$elemen_tag_array[] = $e->nodeName;
		}
		$counts = array_count_values($elemen_tag_array);
		$maxe = max($counts);//en_fazla_yinelenen_element_sayi 
		$en_fazla_yinelenen_element = array_keys($counts, $maxe);
		return implode(', ', $en_fazla_yinelenen_element);
	}

	public static function html_to_node($html) {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		return $dom;
	}
	public static function node_documentElement($node) {
		return $node->documentElement;
	}
	public static function node_to_html($node) {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$importedNode = $dom->importNode($node, true);
		$dom->appendChild($importedNode);
		return $dom->saveHTML();
	}

	public static function node_closest(DOMNode $node, string $tag = "*", string $attr = "*", string $value = "*"): ?DOMNode {
		while ($node && $node->nodeType === XML_ELEMENT_NODE) {
			$tagCondition = $tag === "*" || $node->tagName === $tag;
			
			$attrCondition = $attr === "*" || (
				$node->hasAttribute($attr) &&
				($value === "*" || $node->getAttribute($attr) === $value)
			);
	
			if ($tagCondition && $attrCondition) {
				return $node;
			}
	
			$node = $node->parentNode;
		}
	
		return null;
	}
	

	public static function node_closest_xpath($node, $selector) {
		while ($node && $node->nodeType === XML_ELEMENT_NODE) {
			$xpath = new DOMXPath($node->ownerDocument);
			$result = $xpath->evaluate("ancestor::*[$selector]", $node);
			if ($result->length > 0) {
				return $result->item(0);
			}
			$node = $node->parentNode;
		}
		return null;
	}


	public static function item_node_max_depth($node) {
		//$depth = self::getElementMaxDepthFromA($node);
		return self::getElementMaxDepthFromA($node);//$depth <= 1 ? 1 : $depth;
	}

	public static function getElementMaxDepthFromA($element) {
		$depth = 0;
		$arti = 1;
		$item_a_tag_sayisi = 0;
		if ($element->hasChildNodes()) {
			foreach ($element->childNodes as $child) {
				if ($child->nodeName == 'a') {
					$item_a_tag_sayisi++;
				}
			}
			$arti = $item_a_tag_sayisi > 0 ? 1 : 0;
	
			foreach ($element->childNodes as $child) {
				if ($child->nodeType == XML_ELEMENT_NODE && $child->nodeName != 'a') {
					$depth = max($depth, self::getElementMaxDepthFromA($child));
				}			
			}
		}
		return $depth + $arti;
	}

	
	public static function assignDepthAttributes($node, $depth) {

		if (in_array($node->nodeName,["a","ul","li","div"])) {
			if ((in_array($node->nodeName,["ul","li","div"]))) {
				$node->setAttribute('data-depth', $depth + 1);
			} else {
				$node->setAttribute('data-depth', $depth);
			}
			if ( ! $node->hasChildNodes()) {
				$node->setAttribute('nochild',"");
			}
		}
		
		$item_a_tag_sayisi = 0;
		$arti = 1;
		
		foreach ($node->childNodes as $childNode) {
			if ($childNode->nodeName == 'a') {
				$item_a_tag_sayisi++;
			}
		}
		$arti = $item_a_tag_sayisi > 0 ? 1 : 0;

		foreach ($node->childNodes as $childNode) {
			if ($childNode instanceof DOMElement) {
				self::assignDepthAttributes($childNode, $depth + $arti);
			}
		}
	}
	public static function assignMaxDepthAttributes($node){

		$split_node_dizi = self::node_item_first_childs_list($node, "");
		$dom_split_tag = self::element_most_tag($split_node_dizi);
		$split_node_dizi = self::node_item_first_childs_list($node, $dom_split_tag);

		foreach ($split_node_dizi as $item) {
			$data_listid = rand(10000,99999);
			$item_max_depth = self::getElementMaxDepthFromA($item);
			$item->setAttribute('data-list_maxdepth', $item_max_depth);
			$item->setAttribute('data-listid', $data_listid);
			
			$split_node_dizi_tum_elemanlar = $item->getElementsByTagName('*');

			foreach ($split_node_dizi_tum_elemanlar as $item_) {
				$item_max_depth_ = self::getElementMaxDepthFromA($item_);
				$item_->setAttribute('a_max_d', $item_max_depth_);
				$item_->setAttribute('a_max_d', $item_max_depth_);
				$item_->setAttribute('data-listid', $data_listid);
			}
		}

		
	}

	public static function assignAttributes($node_html) {
		$dom = new DOMDocument('1.0', 'UTF-8');
		@$dom->loadHTML($node_html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		$root = $dom->documentElement;
		self::assignDepthAttributes($root, 0);
		$modifiedHTML = $dom->saveHTML();

		$dom2 = new DOMDocument('1.0', 'UTF-8');
		$dom2->loadHTML($modifiedHTML, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		
		self::assignMaxDepthAttributes($dom2);
		$modifiedHTML2 = $dom2->saveHTML();

		return $modifiedHTML2;
	}

	public static function element_child_xml_element_length($element, $find_element_node="", $all_level = true){
		$xml_element_sayi = 0;
		if ($element !== false) {
			if ($element->hasChildNodes()) {
				$find_element_node = ($all_level==true && $find_element_node=="") ? '*' : $find_element_node;
				$elements_childNodes = ($all_level==true) ? $element->getElementsByTagName($find_element_node) : $element->childNodes;
				foreach ($elements_childNodes as $child) {
					if ($child->nodeType === XML_ELEMENT_NODE && ($find_element_node=="" || $child->nodeName == $find_element_node)) {
						$xml_element_sayi++;
					}
				}	
			}
		}
		return $xml_element_sayi;
	}

	public function tag_class() {

		foreach ($this->split_dizi as $item) {

			if (!isset($en_az_iki_a_pespese_var)) {
				$item_html = self::node_to_html($item);
				$item_html = preg_replace('/(?<=>)\s+(?=<)/', '', $item_html);
				$en_az_iki_a_pespese_var = (preg_match('/<a[^>]*>.*?<\/a>\s*<a[^>]*>/', $item_html));
			}
			//a wrap kontrolü
			$a_element = $item->getElementsByTagName("a")->item(0);
			$firstXmlElementChild = false;
			if ($a_element && $a_element->hasChildNodes()) {
				foreach ($a_element->childNodes as $childNode) {
					if ($childNode->nodeType === XML_ELEMENT_NODE) {
						$firstXmlElementChild = $childNode;
						break;
					}
				}
			}
			$this->a_child_wrap_tag = ($firstXmlElementChild) ? $firstXmlElementChild->nodeName : '';
			$this->a_child_wrap_class = ($firstXmlElementChild) ? $firstXmlElementChild->getAttribute('class') : '';
			//a wrap kontrolü sonu
		}

		foreach ($this->split_dizi as $item) {
			$item_tag_name = $item->tagName;
			$item_node_value = str_replace(["\t","\n","\r", " "],"",trim($item->nodeValue));

			$item_ilk_element = self::findFirstChildNode($item);
			$item_ilk_element_tag_name = ($item_ilk_element instanceof DOMElement) ? $item_ilk_element->tagName : null;
			

			$xpath = new DOMXPath($item->ownerDocument);
			
			for ($i=1; $i <= $this->max_depth; $i++) {

				$query = ".//a[@data-depth='".$i."']";
				$anchors = $xpath->query($query, $item);
				$toplam_a_sayisi = $anchors->length;

				foreach ($anchors as $index => $anchor) {
					$a_parentNode = $anchor->parentNode;
					$a_isFirstSibling = $index === 0;
					$a_isLastSibling = $index === $anchors->length - 1;
					$a_nextElement = self::nextSibling($anchor);
					$a_prevElement = self::previousSibling($anchor);

					//$a_next_element_has_child_a_sayi = ($a_nextElement instanceof DOMElement) ? self::element_child_xml_element_length($a_nextElement,'a') : 0;
					
					//if ($i > 0) {
						$xpath_a = new DOMXPath($a_parentNode->ownerDocument);
						$query_a = ".//a[@data-depth='".($i+1)."']";
						$anchors_parent_a = $xpath->query($query_a, $a_parentNode);
						$anchors_parent_a_toplam_a_sayisi = $anchors_parent_a->length;
						if ( !isset($this->a_is_not_single_class[$i])) {
							if ($anchors_parent_a_toplam_a_sayisi > 0) {
								$this->a_is_not_single_class[$i] = $anchor->getAttribute('class');
								
							}
						}
						if (!isset($this->a_is_single_class[$i])) {
							if ($anchors_parent_a_toplam_a_sayisi == 0) {
								$this->a_is_single_class[$i] = $anchor->getAttribute('class');
								
							}
						}
					//}
					
					if ($anchor->parentNode instanceof DOMElement) {

						$a_parent_prev_element = self::previousSibling($anchor->parentNode);

						if ($a_parent_prev_element instanceof DOMElement) {
							$xpath_a_parent = new DOMXPath($a_parent_prev_element->ownerDocument);
							$query_a_parent = '//a[@data-depth="'.($i-1).'"]';
							$result_a_parent_depth_check = $xpath_a_parent->query($query_a_parent);

							if ($result_a_parent_depth_check->length == 0 || $en_az_iki_a_pespese_var) {
								if (!isset($this->a_wrap['tag'][$i])) {
									$this->a_wrap['tag'][$i] = false;
								}
								if (!isset($this->a_wrap['class'][$i])) {
									$this->a_wrap['class'][$i] = false;
								}
							} else {

								if (!isset($this->a_wrap['tag'][$i])) {
									$this->a_wrap['tag'][$i] = $anchor->parentNode->nodeName;
								}
								if (!isset($this->a_wrap['class'][$i])) {
									$this->a_wrap['class'][$i] = $anchor->parentNode->getAttribute('class');
								}

							}
							
							if (!isset($this->a_wrap['max-depth'][$i])) {
								$this->a_wrap['max-depth'][$i] = self::item_node_max_depth($item);
								$this->a_wrap['max-depth-class'][$i] = $item->getAttribute('class');
							}

							if ($i==1) {

								$xpath_list1 = new DOMXPath($item->ownerDocument);
								$query_list1 = '//*[@data-list_maxdepth="1"]';
								$nodes_list1 = $xpath_list1->query($query_list1);
								foreach ($nodes_list1 as $nl_1) {
									$this->a_wrap['tag'][1] = $nl_1->nodeName;
									$this->a_wrap['class'][1] = $nl_1->getAttribute('class');
								}
							}

							//$this->list_container
	
							if ($i > 1) {

								$node_closest_split = self::node_closest($anchor, $tag = "*", $attr = "data-list_maxdepth", $value = "*");
								$xpath_a_ul = new DOMXPath($item->ownerDocument);
								$query_a_ul = '(.//a[@data-depth="'.($i-1).'"])';	    //data depth-1 in next elementi ul
								$result_a_ul = $xpath_a_ul->query($query_a_ul, $node_closest_split);

								foreach ($result_a_ul as $result) {
									$result_next = self::nextSibling($result);
									if ($result_next instanceof DOMElement) {
										$result_next = $result_next->getAttribute("data-depth")!=$i ? self::nextSibling($result_next) : $result_next;
										if ($result_next instanceof DOMElement) {
											if (!isset($this->list_container['tag'][$i])) {
												$this->list_container['tag'][$i] = $result_next->nodeName;
											}
											if (!isset($this->list_container['class'][$i])) {
												$this->list_container['class'][$i] = $result_next->getAttribute('class');
											}
										} else {
											if (!isset($this->list_container['tag'][$i])) {
												$this->list_container['tag'][$i] = false;
											}
											if (!isset($this->list_container['class'][$i])) {
												$this->list_container['class'][$i] = false;
											}
										}
									}
								}
							}
							if ($i == 1) {
								if (!isset($this->list_container['tag'][1])) {
									$this->list_container['tag'][1] = $this->ilk_tag;
								}
								if (!isset($this->list_container['class'][1])) {
									$this->list_container['class'][1] = $this->ilk_tag_class;
								}
							}

						}


						$a_parentNode = $anchor->parentNode;
						while ( ! $item->isSameNode($a_parentNode)) {
							if ($a_parentNode instanceof DOMElement) {
								//a parent node list tag değerini en son olarak almadan hemen önce son tag aktarılıyor
								if ($i==1) {
									$this->main_list_elements_container['tag'] = $a_parentNode->nodeName;
									$this->main_list_elements_container['class'] = $a_parentNode->getAttribute('class');
								}
								$a_parentNode = $a_parentNode->parentNode;
							}							
						}
						
					}
				}
			}
		}
	}

	public function menu_recursive_dizi($ust_menu_no, $level = 0, &$max_depth = 0) {
		try {
			$stmt = $this->db->prepare("SELECT no, tablo, tablo_no, ust_menu_no, dis_link, adi, sira FROM {$this->do_}menu 
											WHERE ust_menu_no = ? ORDER BY sira ASC");
			$stmt->execute([$ust_menu_no]);
			$menu_liste = $stmt->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			error_log("Menu sorgu hatası: " . $e->getMessage());
			return [];
		}
		
		$menu_array = [];
		$sira = 0;
		if (!empty($menu_liste)) {
			
			foreach ($menu_liste as $m) {
				$sira++;
				$menu_item = array(
					'no' => $m->no,
					'tablo' => $m->tablo,
					'tablo_no' => $m->tablo_no,
					'dis_link' => $m->dis_link,
					'adi' => $m->adi,
					'sira' => $sira,
					'level' => $level + 1,
					'max_depth' => $level + 1,
				);
				if ($level < 8) {
					$submenu_depth = 0;
					$db_tablo = $this->dbtablo_url_yonlendir[$m->tablo];

					if (preg_match("/sayfa|sekme/",$db_tablo)) {
						try {
							$stmt = $this->db->prepare("SELECT no, adi, url FROM {$this->do_}{$m->tablo} WHERE no = ?");
							$stmt->execute([$m->tablo_no]);
							$tablo_b = $stmt->fetch(PDO::FETCH_OBJ);
							$href = $tablo_b ? href('index', $m->tablo.'='.$tablo_b->url) : '#404';
						} catch (PDOException $e) {
							error_log("Sayfa/Sekme sorgu hatası: " . $e->getMessage());
							$tablo_b = false;
							$href = '#404';
						}
					} elseif ($db_tablo=='blok_html') {
						try {
							$stmt = $this->db->prepare("SELECT no, adi, hash FROM {$this->do_}{$m->tablo} WHERE no = ?");
							$stmt->execute([$m->tablo_no]);
							$tablo_b = $stmt->fetch(PDO::FETCH_OBJ);
							$href = $tablo_b ? href('index','#blok_'.$tablo_b->hash) : '#404';
						} catch (PDOException $e) {
							error_log("Blok HTML sorgu hatası: " . $e->getMessage());
							$tablo_b = false;
							$href = '#404';
						}
					} elseif ($db_tablo=='menu') {
						try {
							$stmt = $this->db->prepare("SELECT no, adi, dis_link FROM {$this->do_}{$m->tablo} WHERE no = ?");
							$stmt->execute([$m->tablo_no]);
							$tablo_b = $stmt->fetch(PDO::FETCH_OBJ);/*adi->dış link adı*/
							$href = $tablo_b ? $tablo_b->dis_link : '#404';
						} catch (PDOException $e) {
							error_log("Menu sorgu hatası: " . $e->getMessage());
							$tablo_b = false;
							$href = '#404';
						}
					}
	
					$tablo_b_adi = $tablo_b ? $tablo_b->adi : '!?!';

					$menu_item['adi'] = $tablo_b ? $tablo_b->adi : '!?!';
					$menu_item['href'] = $href;
					$menu_item['submenu'] = $this->menu_recursive_dizi($m->no, $level + 1, $submenu_depth);
					$menu_item['max_depth'] = max($menu_item['max_depth'], $submenu_depth);
				}
				$max_depth = max($max_depth, $menu_item['max_depth']);
				$menu_array[] = $menu_item;
			}
			
		}
		return $menu_array;
	}
	
	public function menu_recursive_dizi_html_yaz($menudizi, $depth = 1) {
	
		$main_list_elements_container_tag = ($depth==1 && !empty($this->main_list_elements_container['tag'])) ? $this->main_list_elements_container['tag'] : false;
		$main_list_elements_container_class = ($depth==1 && !empty($this->main_list_elements_container['class'])) ? $this->main_list_elements_container['class'] : false;
		$main_list_elements_container_tag_class = $main_list_elements_container_tag ? '<'.$main_list_elements_container_tag . 
		(($main_list_elements_container_class) ? ' class="'.$main_list_elements_container_class.'"' : '') .
		 '>' : '';

		$list_container_tag = (isset($this->list_container['tag'][$depth])) ? $this->list_container['tag'][$depth] : false;
		$list_container_class = (isset($this->list_container['class'][$depth])) ? $this->list_container['class'][$depth] : false;
		$list_container_tag_class = ($list_container_tag) ? $list_container_tag. (($list_container_class) ? ' class="'.$list_container_class.'"' : '') : '';


		$a_child_wrap_tag_ac = (!empty($this->a_child_wrap_tag)) ? '<'.$this->a_child_wrap_tag . (!empty($this->a_child_wrap_tag_class) ? ' class="'.$this->a_child_wrap_tag_class.'"' : '') .  '>' : '';
		$a_child_wrap_tag_kapat = (!empty($this->a_child_wrap_tag)) ? '</'.$this->a_child_wrap_tag.'>' : '';

		$html = empty($list_container_tag_class) ? '' : '<' . $list_container_tag_class . '>' . PHP_EOL;
		foreach ($menudizi as $md) {
			$a_wrap_tag = (isset($this->a_wrap['tag'][$depth])) ? $this->a_wrap['tag'][$depth] : false;
			$a_wrap_class = (isset($this->a_wrap['class'][$depth])) ? (!empty($md['submenu']) ? (isset($this->a_wrap['max-depth-class'][$depth]) ? $this->a_wrap['max-depth-class'][$depth] : $this->a_wrap['class'][$depth]) : $this->a_wrap['class'][$depth]) : false;
			$a_wrap_tag_class = ($a_wrap_tag) ? $a_wrap_tag . (($a_wrap_class) ? ' class="' . $a_wrap_class . '"' : '') : '';
			$a_wrap_tag_class = trim($list_container_tag_class) == trim($a_wrap_tag_class) ? '' : $a_wrap_tag_class;
			$a_wrap_tag_class = empty($a_wrap_tag_class) ? '' : '<' . $a_wrap_tag_class . '>';

			$html .= $a_wrap_tag_class;

			$a_not_single_class = isset($this->a_is_not_single_class[$depth]) ? $this->a_is_not_single_class[$depth] : (isset($this->a_is_single_class[$depth]) ? $this->a_is_single_class[$depth] : '');
			$a_single_class = isset($this->a_is_single_class[$depth]) ? $this->a_is_single_class[$depth] : (isset($this->a_is_not_single_class[$depth]) ? $this->a_is_not_single_class[$depth] : '');

			if (!empty($md['submenu'])) {
				$html .= $main_list_elements_container_tag_class . "\n";
				$html .= "\t" . '<a class="' . $a_not_single_class . '" href="' . $md['href'] . '">' . $a_child_wrap_tag_ac . $md['adi'] . $a_child_wrap_tag_kapat . '</a>' . "\n";
				$html .= $this->menu_recursive_dizi_html_yaz($md['submenu'], $depth + 1);
				$html .= $main_list_elements_container_tag ? '</' . $main_list_elements_container_tag . '>' . "\n" : '';
			} else {
				$html .= "\t" . '<a class="' . $a_single_class . '" href="' . $md['href'] . '">' . $a_child_wrap_tag_ac . $md['adi'] .  $a_child_wrap_tag_kapat . '</a>' . "\n";
			}
			$html .= $a_wrap_tag ? '</'.$a_wrap_tag.'>'."\n" : '';
		}
		$html .= $list_container_tag ? '</'.$list_container_tag.'>' : '';
		$html .= PHP_EOL;
	
		return $html;
	}

}
?>