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

	/** @var array Depth bazlı yapı: item_tag, item_class, a_class_single, a_class_has_submenu, list_tag, list_class, a_child_wrap_tag, a_child_wrap_class */
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
		return self::findFirstDescendantList($dynamicNode);
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

	private static function extractStructureRecursive(DOMElement $listNode, $depth, &$structure) {
		$listTag = strtolower($listNode->tagName);
		$listClass = $listNode->getAttribute('class') ?: '';

		if (!isset($structure[$depth])) {
			$structure[$depth] = [
				'list_tag' => $listTag,
				'list_class' => $listClass,
				'item_tag' => 'li',
				'item_class' => '',
				'a_class_single' => '',
				'a_class_has_submenu' => '',
				'a_child_wrap_tag' => '',
				'a_child_wrap_class' => '',
			];
		}

		$items = self::getDirectListItems($listNode);
		foreach ($items as $item) {
			$itemTag = strtolower($item->tagName);
			if (!in_array($itemTag, ['li', 'div'])) continue;

			if (empty($structure[$depth]['item_class']) && $item->getAttribute('class')) {
				$structure[$depth]['item_class'] = $item->getAttribute('class');
			}

			$aNode = self::getFirstAnchor($item);
			if (!$aNode) continue;

			$aClass = $aNode->getAttribute('class') ?: '';
			$submenuList = self::getSubmenuListAfterAnchor($item, $aNode);

			if ($submenuList) {
				if (empty($structure[$depth]['a_class_has_submenu'])) {
					$structure[$depth]['a_class_has_submenu'] = $aClass;
				}
				self::extractStructureRecursive($submenuList, $depth + 1, $structure);
			} else {
				if (empty($structure[$depth]['a_class_single'])) {
					$structure[$depth]['a_class_single'] = $aClass;
				}
			}

			if (empty($structure[$depth]['a_child_wrap_tag'])) {
				$firstChild = self::getFirstElementChild($aNode);
				if ($firstChild && strtolower($firstChild->tagName) !== 'a') {
					$structure[$depth]['a_child_wrap_tag'] = $firstChild->tagName;
					$structure[$depth]['a_child_wrap_class'] = $firstChild->getAttribute('class') ?: '';
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
				if (in_array($tag, ['ul', 'ol'])) {
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

					$menu_item['adi'] = $tablo_b ? ($tablo_b->adi ?? '!?!') : ($m->adi ?? '!?!');
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
		if ($parentPattern && empty($pattern['a_class_single']) && empty($pattern['a_class_has_submenu'])) {
			$pattern['a_class_single'] = $parentPattern['a_class_single'] ?? '';
			$pattern['a_class_has_submenu'] = $parentPattern['a_class_has_submenu'] ?? '';
		}
		$listTag = $pattern['list_tag'] ?? 'ul';
		$listClass = $pattern['list_class'] ?? '';
		$listAttrs = $listClass ? ' class="'.htmlspecialchars($listClass).'"' : '';

		if (empty($menuArray)) {
			// Menü yoksa hiçbir şey döndürme
			//return '<'.$listTag.$listAttrs.'></'.$listTag.'>';
			return '';
		}

		$itemTag = $pattern['item_tag'] ?? 'li';
		$itemClass = $pattern['item_class'] ?? '';
		$aChildWrapTag = $pattern['a_child_wrap_tag'] ?? '';
		$aChildWrapClass = $pattern['a_child_wrap_class'] ?? '';

		$aChildOpen = $aChildWrapTag ? '<'.$aChildWrapTag.($aChildWrapClass ? ' class="'.htmlspecialchars($aChildWrapClass).'"' : '').'>' : '';
		$aChildClose = $aChildWrapTag ? '</'.$aChildWrapTag.'>' : '';

		$html = '<'.$listTag.$listAttrs.'>' . PHP_EOL;

		foreach ($menuArray as $md) {
			$hasSubmenu = !empty($md['submenu']);
			$aClassHas = $pattern['a_class_has_submenu'] ?? '';
			$aClassSingle = $pattern['a_class_single'] ?? '';
			$aClass = $hasSubmenu
				? (strlen($aClassHas) ? $aClassHas : $aClassSingle)
				: (strlen($aClassSingle) ? $aClassSingle : $aClassHas);
			$itemAttrs = $itemClass ? ' class="'.htmlspecialchars($itemClass).'"' : '';
			$aAttrs = $aClass ? ' class="'.htmlspecialchars($aClass).'"' : '';

			$html .= "\t".'<'.$itemTag.$itemAttrs.'>';
			$html .= '<a'.$aAttrs.' href="'.htmlspecialchars($md['href'] ?? '#').'">';
			$html .= $aChildOpen . htmlspecialchars($md['adi'] ?? '') . $aChildClose;
			$html .= '</a>';

			if ($hasSubmenu) {
				$html .= PHP_EOL . $this->render($md['submenu'], $structure, $depth + 1);
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
			'a_class_single' => '',
			'a_class_has_submenu' => '',
			'a_child_wrap_tag' => '',
			'a_child_wrap_class' => '',
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
