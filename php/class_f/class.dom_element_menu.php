<?php

/**
 * Dinamik Menü Şablon İşleyici
 *
 * menu.html şablonunu programatik olarak analiz eder, veritabanından gelen
 * recursive menü verisini şablon yapısına uyumlu şekilde enjekte eder.
 *
 * Desteklenen işaretleyiciler: data-menu="dynamic", data-menu="v2", data-version="menu.v2"
 * Desteklenen yapılar: ul>li>a, nav>ul>li>a, ul>li>a+ul>li>... (iç içe)
 */
class dom_element {

	public $db;
	public $do_;
	public $dbtablo_url_yonlendir = ['sayfa'=>'sayfa', 'sekme'=>'sekme', 'blok_html'=>'blok_html', 'menu'=>'menu'];

	/** @var array Depth bazlı yapı: item_tag, item_class, a_class_single, a_class_has_submenu, list_tag, list_class, a_child_wrap_tag, a_child_wrap_class, submenu_wrapper_tag, submenu_wrapper_class */
	private $structure = [];

	/** @var DOMElement|null Wrapper (nav, div vb.) - liste kökünün parent'ı */
	private $wrapperElement = null;

	/** @var DOMElement|null Liste kökü (ul/ol) */
	private $listRoot = null;

	public function __construct($db, $do_) {
		$this->db = $db;
		$this->do_ = $do_;
	}

	/**
	 * data-menu veya data-version taşıyan elemanı bulur
	 * @return DOMElement|null
	 */
	public static function findDynamicElement($html) {
		$html = trim($html);
		if (empty($html)) return null;

		$dom = new DOMDocument('1.0', 'UTF-8');
		@$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		$xpath = new DOMXPath($dom);

		$queries = [
			'//*[@data-menu="dynamic"]',
			'//*[@data-menu="v2"]',
			'//*[@data-version="menu.v2"]',
		];
		foreach ($queries as $query) {
			$nodes = $xpath->query($query);
			if ($nodes->length > 0) {
				return $nodes->item(0);
			}
		}
		return null;
	}

	/**
	 * Dinamik elemandan liste kökünü (ul/ol) bulur. Wrapper varsa atlar.
	 * @return DOMElement|null
	 */
	public static function findListRoot(DOMElement $dynamicNode) {
		$tag = strtolower($dynamicNode->tagName);
		if (in_array($tag, ['ul', 'ol'])) {
			return $dynamicNode;
		}
		$listRoot = self::findFirstDescendantList($dynamicNode);
		if ($listRoot) {
			return $listRoot;
		}
		if (in_array($tag, ['div', 'nav'])) {
			return $dynamicNode;
		}
		return null;
	}

	/**
	 * İlk ul/ol descendant'ı bulur
	 */
	private static function findFirstDescendantList(DOMElement $node) {
		foreach ($node->childNodes as $child) {
			if ($child instanceof DOMElement) {
				$tag = strtolower($child->tagName);
				if (in_array($tag, ['ul', 'ol'])) {
					return $child;
				}
				$found = self::findFirstDescendantList($child);
				if ($found) return $found;
			}
		}
		return null;
	}

	/**
	 * Liste kökünden depth bazlı yapı çıkarır
	 * @return array
	 */
	public static function extractStructure(DOMElement $listRoot, DOMElement $dynamicNode = null) {
		$structure = [];
		$wrapperElement = null;

		if ($dynamicNode && $dynamicNode !== $listRoot) {
			$wrapperElement = $dynamicNode;
		}

		self::extractStructureRecursive($listRoot, 1, $structure);
		return [
			'structure' => $structure,
			'wrapper' => $wrapperElement,
			'list_root' => $listRoot,
		];
	}

	/**
	 * Find the actual list (ul/ol) inside a submenu wrapper (or return the node itself if it's a list)
	 * @param DOMElement $node
	 * @return array [$actualList, $wrapperInfo] where $wrapperInfo is ['tag' => string, 'class' => string] or null
	 */
	private static function findActualSubmenuList(DOMElement $node) {
		$tag = strtolower($node->tagName);
		if (in_array($tag, ['ul', 'ol'])) {
			// Node itself is the list, no wrapper
			return [$node, null];
		}
		// Look for a ul/ol inside this wrapper node
		$list = self::findFirstDescendantList($node);
		if ($list) {
			$wrapperInfo = [
				'tag' => $tag,
				'class' => $node->getAttribute('class') ?: ''
			];
			return [$list, $wrapperInfo];
		}
		// No list found, return original node and null
		return [$node, null];
	}

	private static function extractStructureRecursive(DOMElement $listNode, $depth, &$structure) {
		$listTag = strtolower($listNode->tagName);
		$listClass = $listNode->getAttribute('class') ?: '';

		if (!isset($structure[$depth])) {
			$structure[$depth] = [
				'list_tag' => $listTag,
				'list_class' => $listClass,
				'item_tag' => '',
				'item_class' => '',
				'item_class_single' => '',
				'item_class_has_submenu' => '',
				'item_class_single_found' => false,
				'item_class_has_submenu_found' => false,
				'a_class_single' => '',
				'a_class_has_submenu' => '',
				'a_class_single_found' => false,
				'a_class_has_submenu_found' => false,
				'a_child_wrap_tag' => '',
				'a_child_wrap_class' => '',
				'a_suffix_tag' => '',
				'a_suffix_class' => '',
				'submenu_wrapper_tag' => '',
				'submenu_wrapper_class' => '',
				'submenu_wrapper_found' => false,
			];
		}

		$items = self::getDirectListItems($listNode);
		foreach ($items as $item) {
			$itemTag = strtolower($item->tagName);
			if (!in_array($itemTag, ['li', 'div'])) continue;

			if (empty($structure[$depth]['item_tag'])) {
				$structure[$depth]['item_tag'] = $itemTag;
			}

			$itemClass = $item->getAttribute('class') ?: '';
			if (empty($structure[$depth]['item_class']) && $itemClass) {
				$structure[$depth]['item_class'] = $itemClass;
			}

			$aNode = self::getFirstAnchor($item);
			if (!$aNode) continue;

			$aClass = $aNode->getAttribute('class') ?: '';
			$submenuList = self::getSubmenuListAfterAnchor($item, $aNode);

			if ($submenuList) {
				// Process submenu list and wrapper
				list($actualSubmenuList, $wrapperInfo) = self::findActualSubmenuList($submenuList);
				if ($wrapperInfo && empty($structure[$depth]['submenu_wrapper_found'])) {
					$structure[$depth]['submenu_wrapper_tag'] = $wrapperInfo['tag'];
					$structure[$depth]['submenu_wrapper_class'] = $wrapperInfo['class'];
					$structure[$depth]['submenu_wrapper_found'] = true;
				}

				if (empty($structure[$depth]['item_class_has_submenu_found'])) {
					$structure[$depth]['item_class_has_submenu'] = $itemClass;
					$structure[$depth]['item_class_has_submenu_found'] = true;
				}
				if (empty($structure[$depth]['a_class_has_submenu_found'])) {
					$structure[$depth]['a_class_has_submenu'] = $aClass;
					$structure[$depth]['a_class_has_submenu_found'] = true;
				}
				self::extractStructureRecursive($actualSubmenuList, $depth + 1, $structure);
			} else {
				if (empty($structure[$depth]['item_class_single_found'])) {
					$structure[$depth]['item_class_single'] = $itemClass;
					$structure[$depth]['item_class_single_found'] = true;
				}
				if (empty($structure[$depth]['a_class_single_found'])) {
					$structure[$depth]['a_class_single'] = $aClass;
					$structure[$depth]['a_class_single_found'] = true;
				}
			}

			if (empty($structure[$depth]['a_child_wrap_tag'])) {
				$firstChild = self::getFirstElementChild($aNode);
				if ($firstChild && strtolower($firstChild->tagName) !== 'a' && !self::isIconElement($firstChild)) {
					$structure[$depth]['a_child_wrap_tag'] = $firstChild->tagName;
					$structure[$depth]['a_child_wrap_class'] = $firstChild->getAttribute('class') ?: '';
				}
			}

			if ($submenuList && empty($structure[$depth]['a_suffix_tag'])) {
				$iconChild = self::getFirstIconChild($aNode);
				if ($iconChild) {
					$structure[$depth]['a_suffix_tag'] = strtolower($iconChild->tagName);
					$structure[$depth]['a_suffix_class'] = $iconChild->getAttribute('class') ?: '';
				}
			}
		}
	}

	private static function getDirectListItems(DOMElement $listNode) {
		$items = [];
		foreach ($listNode->childNodes as $child) {
			if ($child instanceof DOMElement && in_array(strtolower($child->tagName), ['li', 'div'])) {
				$items[] = $child;
			}
		}
		return $items;
	}

	private static function getFirstAnchor(DOMElement $node) {
		$anchors = $node->getElementsByTagName('a');
		return $anchors->length > 0 ? $anchors->item(0) : null;
	}

	private static function getSubmenuListAfterAnchor(DOMElement $item, DOMElement $anchor) {
		$found = false;
		foreach ($item->childNodes as $child) {
			if ($child === $anchor) {
				$found = true;
				continue;
			}
			if ($found && $child instanceof DOMElement) {
				$tag = strtolower($child->tagName);
				if (in_array($tag, ['ul', 'ol', 'div'])) {
					return $child;
				}
			}
		}
		return null;
	}

	private static function getFirstElementChild(DOMElement $node) {
		foreach ($node->childNodes as $child) {
			if ($child instanceof DOMElement) {
				return $child;
			}
		}
		return null;
	}

	private static function getFirstIconChild(DOMElement $node) {
		foreach ($node->childNodes as $child) {
			if ($child instanceof DOMElement && self::isIconElement($child)) {
				return $child;
			}
		}
		return null;
	}

	private static function isIconElement(DOMElement $node) {
		$tag = strtolower($node->tagName);
		$class = ' ' . strtolower($node->getAttribute('class') ?: '') . ' ';
		if ($tag === 'i') {
			return true;
		}
		return strpos($class, ' fa-') !== false
			|| strpos($class, ' fas ') !== false
			|| strpos($class, ' far ') !== false
			|| strpos($class, ' fab ') !== false
			|| strpos($class, ' bi-') !== false
			|| strpos($class, ' icon') !== false
			|| strpos($class, 'material-icons') !== false;
	}

	/**
	 * Veritabanından recursive menü dizisi çeker (URL çözümlemesi ile)
	 * @param int $ust_menu_no
	 * @param int $level
	 * @param int $max_depth Referans
	 * @return array
	 */
	public function menu_recursive_dizi($ust_menu_no = 0, $level = 0, &$max_depth = 0) {
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
				$menu_item = [
					'no' => $m->no,
					'tablo' => $m->tablo,
					'tablo_no' => $m->tablo_no,
					// Çeviri anahtarı, hedef kayıt bulunamazsa menü satırının kendi adına düşer.
					'ceviri_tablo' => 'menu',
					'ceviri_tablo_no' => $m->no,
					'ceviri_alan' => 'adi',
					'dis_link' => $m->dis_link,
					'adi' => $m->adi ?? '!?!',
					'sira' => $sira,
					'level' => $level + 1,
					'max_depth' => $level + 1,
					'href' => '#',
					'submenu' => [],
				];

				if ($level < 8) {
					$db_tablo = $this->dbtablo_url_yonlendir[$m->tablo] ?? null;
					$tablo_b = null;
					$href = '#';

					if (preg_match("/sayfa|sekme/", $db_tablo ?? '')) {
						try {
							$stmt = $this->db->prepare("SELECT no, adi, url FROM {$this->do_}{$m->tablo} WHERE no = ?");
							$stmt->execute([$m->tablo_no]);
							$tablo_b = $stmt->fetch(PDO::FETCH_OBJ);
							$href = $tablo_b ? href('index', $m->tablo.'='.$tablo_b->url) : '#404';
						} catch (PDOException $e) {
							error_log("Sayfa/Sekme sorgu hatası: " . $e->getMessage());
						}
					} elseif ($db_tablo === 'blok_html') {
						try {
							$stmt = $this->db->prepare("SELECT no, adi, hash FROM {$this->do_}{$m->tablo} WHERE no = ?");
							$stmt->execute([$m->tablo_no]);
							$tablo_b = $stmt->fetch(PDO::FETCH_OBJ);
							$href = $tablo_b ? href('index', '#blok_'.$tablo_b->hash) : '#404';
						} catch (PDOException $e) {
							error_log("Blok HTML sorgu hatası: " . $e->getMessage());
						}
					} elseif ($db_tablo === 'menu') {
						try {
							$stmt = $this->db->prepare("SELECT no, adi, dis_link FROM {$this->do_}{$m->tablo} WHERE no = ?");
							$stmt->execute([$m->tablo_no]);
							$tablo_b = $stmt->fetch(PDO::FETCH_OBJ);
							$href = $tablo_b ? ($tablo_b->dis_link ?: '#') : '#404';
						} catch (PDOException $e) {
							error_log("Menu sorgu hatası: " . $e->getMessage());
						}
					}

					if ($tablo_b) {
						$menu_item['adi'] = $tablo_b->adi ?? '!?!';
						$menu_item['ceviri_tablo'] = $m->tablo;
						$menu_item['ceviri_tablo_no'] = $m->tablo_no;
					} else {
						$menu_item['adi'] = $m->adi ?? '!?!';
					}
					$menu_item['href'] = $href;

					$submenu_depth = 0;
					$menu_item['submenu'] = $this->menu_recursive_dizi($m->no, $level + 1, $submenu_depth);
					$menu_item['max_depth'] = max($menu_item['max_depth'], $submenu_depth);
				}

				$max_depth = max($max_depth, $menu_item['max_depth']);
				$menu_array[] = $menu_item;
			}
		}
		return $menu_array;
	}

	/**
	 * Menü dizisini şablon yapısına göre HTML'e dönüştürür
	 * @param array $menuArray
	 * @param array $structure
	 * @param int $depth
	 * @return string
	 */
	public function render($menuArray, array $structure, $depth = 1) {
		$pattern = $structure[$depth] ?? $this->defaultPattern($depth);
		$parentPattern = $structure[max(1, $depth - 1)] ?? null;
		$aClassSingleFound = array_key_exists('a_class_single_found', $pattern)
			? (bool)$pattern['a_class_single_found']
			: !empty($pattern['a_class_single']);
		$aClassHasFound = array_key_exists('a_class_has_submenu_found', $pattern)
			? (bool)$pattern['a_class_has_submenu_found']
			: !empty($pattern['a_class_has_submenu']);
		if ($parentPattern && !$aClassSingleFound && !$aClassHasFound) {
			$pattern['a_class_single'] = $parentPattern['a_class_single'] ?? '';
			$pattern['a_class_has_submenu'] = $parentPattern['a_class_has_submenu'] ?? '';
			$pattern['a_class_single_found'] = array_key_exists('a_class_single_found', $parentPattern)
				? (bool)$parentPattern['a_class_single_found']
				: !empty($pattern['a_class_single']);
			$pattern['a_class_has_submenu_found'] = array_key_exists('a_class_has_submenu_found', $parentPattern)
				? (bool)$parentPattern['a_class_has_submenu_found']
				: !empty($pattern['a_class_has_submenu']);
		}

		if (empty($menuArray)) {
			// Menü yoksa hiçbir şey döndürme
			return '';
		}

		$listTag = $pattern['list_tag'] ?? 'ul';
		$listClass = $pattern['list_class'] ?? '';
		$listAttrs = $listClass ? ' class="'.htmlspecialchars($listClass).'"' : '';

		$itemTag = !empty($pattern['item_tag']) ? $pattern['item_tag'] : (in_array($listTag, ['div', 'nav']) ? 'div' : 'li');
		$aChildWrapTag = $pattern['a_child_wrap_tag'] ?? '';
		$aChildWrapClass = $pattern['a_child_wrap_class'] ?? '';
		$aSuffixTag = $pattern['a_suffix_tag'] ?? '';
		$aSuffixClass = $pattern['a_suffix_class'] ?? '';
		$submenuWrapperTag = $pattern['submenu_wrapper_tag'] ?? '';
		$submenuWrapperClass = $pattern['submenu_wrapper_class'] ?? '';

		$aChildOpen = $aChildWrapTag ? '<'.$aChildWrapTag.($aChildWrapClass ? ' class="'.htmlspecialchars($aChildWrapClass).'"' : '').'>' : '';
		$aChildClose = $aChildWrapTag ? '</'.$aChildWrapTag.'>' : '';
		$aSuffixHtml = $aSuffixTag ? '<'.$aSuffixTag.($aSuffixClass ? ' class="'.htmlspecialchars($aSuffixClass).'"' : '').'></'.$aSuffixTag.'>' : '';

		$html = '<'.$listTag.$listAttrs.'>' . PHP_EOL;

		foreach ($menuArray as $md) {
			$hasSubmenu = !empty($md['submenu']);
			$aClassHas = $pattern['a_class_has_submenu'] ?? '';
			$aClassSingle = $pattern['a_class_single'] ?? '';
			$aClassHasFound = array_key_exists('a_class_has_submenu_found', $pattern)
				? (bool)$pattern['a_class_has_submenu_found']
				: strlen($aClassHas) > 0;
			$aClassSingleFound = array_key_exists('a_class_single_found', $pattern)
				? (bool)$pattern['a_class_single_found']
				: strlen($aClassSingle) > 0;
			$aClass = $hasSubmenu
				? ($aClassHasFound ? $aClassHas : $aClassSingle)
				: ($aClassSingleFound ? $aClassSingle : $aClassHas);
			
			// Add trigger class to items with submenus
			if ($hasSubmenu) {
				if ($depth == 1) {
					// Top level items: use nav-trigger
					if (strpos($aClass, 'nav-trigger') === false) {
						$aClass .= (trim($aClass) ? ' ' : '') . 'nav-trigger';
					}
				} else {
					// Nested items: use submenu-trigger
					if (strpos($aClass, 'submenu-trigger') === false) {
						$aClass .= (trim($aClass) ? ' ' : '') . 'submenu-trigger';
					}
				}
			}

			$itemClassHas = $pattern['item_class_has_submenu'] ?? '';
			$itemClassSingle = $pattern['item_class_single'] ?? '';
			$itemClassDefault = $pattern['item_class'] ?? '';
			$itemClassHasFound = array_key_exists('item_class_has_submenu_found', $pattern)
				? (bool)$pattern['item_class_has_submenu_found']
				: strlen($itemClassHas) > 0;
			$itemClassSingleFound = array_key_exists('item_class_single_found', $pattern)
				? (bool)$pattern['item_class_single_found']
				: strlen($itemClassSingle) > 0;
			$itemClass = $hasSubmenu
				? ($itemClassHasFound ? $itemClassHas : (strlen($itemClassDefault) ? $itemClassDefault : $itemClassSingle))
				: ($itemClassSingleFound ? $itemClassSingle : (strlen($itemClassDefault) ? $itemClassDefault : $itemClassHas));
			$itemAttrs = $itemClass ? ' class="'.htmlspecialchars($itemClass).'"' : '';
			$aAttrs = $aClass ? ' class="'.htmlspecialchars($aClass).'"' : '';

			$html .= "\t".'<'.$itemTag.$itemAttrs.'>';
			$html .= '<a'.$aAttrs.' href="'.htmlspecialchars($md['href'] ?? '#').'" aria-expanded="false" aria-haspopup="true">';
			$html .= $aChildOpen . htmlspecialchars(cc(
				$md['adi'] ?? '',
				$md['ceviri_tablo_no'] ?? ($md['tablo_no'] ?? 0),
				$md['ceviri_tablo'] ?? ($md['tablo'] ?? 'menu'),
				$md['ceviri_alan'] ?? 'adi'
			)) . $aChildClose;
			if ($hasSubmenu) {
				$html .= ($aSuffixHtml ? ' ' . $aSuffixHtml : '');
			}
			$html .= '</a>';

			if ($hasSubmenu) {
				$submenuHtml = $this->render($md['submenu'], $structure, $depth + 1);
				if ($submenuHtml && $submenuWrapperTag) {
					// Wrap submenu in wrapper div
					$html .= PHP_EOL . "\t" . '<' . $submenuWrapperTag . ($submenuWrapperClass ? ' class="'.htmlspecialchars($submenuWrapperClass).'"' : '') . '>' . PHP_EOL;
					$html .= $submenuHtml;
					$html .= "\t" . '</' . $submenuWrapperTag . '>' . PHP_EOL;
				} else {
					$html .= PHP_EOL . $submenuHtml;
				}
			}
			$html .= '</'.$itemTag.'>' . PHP_EOL;
		}

		$html .= '</'.$listTag.'>';
		return $html;
	}

	private function defaultPattern($depth) {
		return [
			'list_tag' => 'ul',
			'list_class' => '',
			'item_tag' => 'li',
			'item_class' => '',
			'item_class_single' => '',
			'item_class_has_submenu' => '',
			'item_class_single_found' => false,
			'item_class_has_submenu_found' => false,
			'a_class_single' => '',
			'a_class_has_submenu' => '',
			'a_class_single_found' => false,
			'a_class_has_submenu_found' => false,
			'a_child_wrap_tag' => '',
			'a_child_wrap_class' => '',
			'a_suffix_tag' => '',
			'a_suffix_class' => '',
			'submenu_wrapper_tag' => '',
			'submenu_wrapper_class' => '',
			'submenu_wrapper_found' => false,
		];
	}

	/**
	 * Tam akış: şablon → keşif → DB → HTML
	 * @param string $html menu.html içeriği
	 * @return string
	 */
	public function renderFull($html, $menuArray = null) {
		$html = trim($html);
		if (empty($html)) return '';

		$dynamicNode = self::findDynamicElement($html);
		if (!$dynamicNode) {
			return $html;
		}

		$listRoot = self::findListRoot($dynamicNode);
		if (!$listRoot) {
			return $html;
		}

		$extracted = self::extractStructure($listRoot, $dynamicNode);
		$structure = $extracted['structure'];
		$wrapper = $extracted['wrapper'];

		if (empty($structure)) {
			$structure[1] = $this->defaultPattern(1);
		}

		if ($menuArray === null) {
			$menuArray = $this->menu_recursive_dizi(0);
		}

		if (empty($menuArray)) {
			return '';
		}

		$listHtml = $this->render($menuArray, $structure);

		if ($wrapper) {
			$wrapperTag = $wrapper->tagName;
			$attrs = [];
			foreach ($wrapper->attributes as $attr) {
				$attrs[] = $attr->name . '="' . htmlspecialchars($attr->value) . '"';
			}
			$attrStr = implode(' ', $attrs);
			return '<'.$wrapperTag.($attrStr ? ' '.$attrStr : '').'>' . PHP_EOL . $listHtml . PHP_EOL . '</'.$wrapperTag.'>';
		}

		return $listHtml;
	}
}
