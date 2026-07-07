<?php

// ---------------------------------------------------------------------------
// SemanticEntity — sayfadaki anlamsal bir varlığı temsil eder.
// Schema.org veya JSON-LD kavramı taşımaz; yalnızca iç semantik modeldir.
// ---------------------------------------------------------------------------
class SemanticEntity {
	public string $type;
	public array $data;

	public function __construct(string $type, array $data = []) {
		$this->type = $type;
		$this->data = $data;
	}
}

// ---------------------------------------------------------------------------
// SemanticContext — bir sayfanın entity listesini toplar ve sorgular.
// ---------------------------------------------------------------------------
class SemanticContext {
	/** @var SemanticEntity[] */
	private array $entities = [];

	public function addEntity(SemanticEntity $entity): void {
		$this->entities[] = $entity;
	}

	/** @return SemanticEntity[] */
	public function getEntities(): array {
		return $this->entities;
	}

	/** @return SemanticEntity[] */
	public function getByType(string $type): array {
		return array_values(array_filter(
			$this->entities,
			static fn(SemanticEntity $e): bool => $e->type === $type
		));
	}

	public function isEmpty(): bool {
		return $this->entities === [];
	}
}

// ---------------------------------------------------------------------------
// JsonLdRenderer — Schema.org node dizisini tek <script> @graph bloğuna çevirir.
// ---------------------------------------------------------------------------
class JsonLdRenderer {
	public function render(array $nodes): string {
		if ($nodes === []) {
			return '';
		}

		$json = json_encode(
			['@context' => 'https://schema.org', '@graph' => array_values($nodes)],
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
		);

		return '<script type="application/ld+json">' . $json . '</script>';
	}
}

// ---------------------------------------------------------------------------
// StructuredData — sayfanın semantik modelini oluşturan ana orkestratör.
//
// PageMetadata ile aynı yaşam döngüsünde çalışır:
//   a_.php        → core dosyaları yükle
//   require_b.php → bileşenler katkiEkle() ile callback kaydeder
//   yonlendir.php → getInstance() çağrılır, build() çalışır
//   index1.php    → $output template değişkenine aktarılır
//
// Route kararları bu sınıfta verilir — bileşenler karar vermez.
// Bileşenler yalnızca semantic entity ekler.
// ---------------------------------------------------------------------------
class StructuredData {
	private static ?self $instance = null;

	/** @var callable[] */
	private static array $katkilar = [];

	private PDO $pdo;
	private $do_;
	private $so_;
	private array $params;

	private SemanticContext $context;
	private SchemaOrgMapper $mapper;
	private JsonLdRenderer  $renderer;

	// Site geneli sabitler — constructor'da bir kez hesaplanır
	private string $siteName  = '';
	private string $siteUrl   = '';
	private string $siteLogo  = '';
	private string $sitePhone = '';

	// Aktif route feature sinyalleri — bileşenler isActive() ile sorgulayabilir
	private array $activeFeatures = [];

	public string $output = '';

	private function __construct(PDO $pdo, $do_, $so_) {
		$this->pdo      = $pdo;
		$this->do_      = $do_;
		$this->so_      = $so_;
		$this->params   = Global_::$routeParams ?? [];
		$this->context  = new SemanticContext();
		$this->mapper   = new SchemaOrgMapper();
		$this->renderer = new JsonLdRenderer();
		$this->initSiteInfo();
		$this->build();
	}

	public static function getInstance(PDO $pdo, $do_, $so_): self {
		if (self::$instance === null) {
			self::$instance = new self($pdo, $do_, $so_);
		}
		return self::$instance;
	}

	/** Bileşen katkı callback'i kaydeder. */
	public static function katkiEkle(callable $katki): void {
		self::$katkilar[] = $katki;
	}

	/** Bileşenlerin context'e doğrudan erişmesi için. */
	public function getContext(): SemanticContext {
		return $this->context;
	}

	/**
	 * Route bazlı feature sinyali.
	 * Bileşenler "Ben bu sayfada VehicleList ekliyorum, aktif mi?" diyebilir.
	 * Kararı core verir; bileşen sadece sorar.
	 *
	 * Örnek: $sd->isActive('vehicle_list')
	 * Örnek: $sd->isActive('vehicle_detail')
	 */
	public function isActive(string $feature): bool {
		return in_array($feature, $this->activeFeatures, true);
	}

	// -----------------------------------------------------------------------
	// Site bilgisi — bir kez hesaplanır, instance'da saklanır
	// -----------------------------------------------------------------------
	private function initSiteInfo(): void {
		if (defined('LOCAL')) {
			$this->siteUrl = rtrim((string) LOCAL, '/');
		}
		
		if (method_exists($this->so_, 'd')) {
			$this->siteName = trim((string) $this->so_->d('site_adi'));
			$this->siteLogo = $this->normalizeUrl((string) $this->so_->d('site_logo'));
			$this->sitePhone = trim((string) $this->so_->d('telefon'));
		}
	}

	private function normalizeUrl(string $url): string {
		$url = trim($url);
		if ($url === '') {
			return '';
		}
		// Tam URL ise olduğu gibi bırak
		if (preg_match('~^https?://~i', $url)) {
			return $url;
		}
		// Zaten LOCAL ile başlıyorsa dokunma
		if (defined('LOCAL') && str_starts_with($url, rtrim(LOCAL, '/'))) {
			return $url;
		}
		// Relative path ise LOCAL ekle
		if (defined('LOCAL')) {
			return rtrim(LOCAL, '/') . '/' . ltrim($url, '/');
		}
		return $url;
	}

	// -----------------------------------------------------------------------
	// Canonical sayfa URL'si — REQUEST_URI'den güvenli şekilde üretir.
	//
	// LOCAL = "http://localhost/site/binaraba" (tam URL)
	// REQUEST_URI = "/site/binaraba/araba=kirala" (sadece path)
	// SUB_DIR = "/site/binaraba" (sadece path kısmı)
	//
	// Çözüm: SUB_DIR'i kırparak sadece ek path'i al, sonra scheme+host+subdir+path.
	// -----------------------------------------------------------------------
	private function currentPageUrl(): string {
		if (!defined('LOCAL')) {
			return '';
		}

		$scheme = defined('HTTPS') ? (string) HTTPS : 'http';
		$host   = defined('DOMAIN') ? (string) DOMAIN : ($_SERVER['HTTP_HOST'] ?? '');
		$subDir = defined('SUB_DIR') ? rtrim((string) SUB_DIR, '/') : '';

		$uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?#') ?: '/';

		if ($subDir !== '' && str_starts_with($uri, $subDir)) {
			$uri = substr($uri, strlen($subDir)) ?: '/';
		}

		return $scheme . '://' . $host . $subDir . '/' . ltrim($uri, '/');
	}

	// -----------------------------------------------------------------------
	// Ana build akışı
	// -----------------------------------------------------------------------
	private function build(): void {
		if ($this->shouldSkip()) {
			$this->output = '';
			return;
		}

		$this->detectFeatures();
		$this->loadCoreSiteEntities();
		$this->loadRouteEntities();
		$this->applyKatkilar();

		if ($this->context->isEmpty()) {
			$this->output = '';
			return;
		}

		try {
			$nodes        = $this->mapper->map($this->context);
			$this->output = $this->renderer->render($nodes);
		} catch (Throwable $e) {
			error_log('[StructuredData] ' . $e->getMessage());
			$this->output = '';
		}
	}

	private function shouldSkip(): bool {
		$type = $this->params['type'] ?? '';
		return in_array($type, ['uis', 'ui'], true);
	}

	// -----------------------------------------------------------------------
	// Route + GET parametrelerine göre hangi feature'ların aktif olduğunu belirle.
	// Bileşenler bu sinyalleri isActive() ile sorgulayabilir.
	// -----------------------------------------------------------------------
	private function detectFeatures(): void {
		$type = $this->params['type'] ?? '';

		// Araç detay sayfası: ?araba= ve ?eo= birlikte
		if (isset($_GET['araba']) && isset($_GET['eo']) && $_GET['eo'] !== '') {
			$this->activeFeatures[] = 'vehicle_detail';
			return; // liste özelliği aynı anda aktif olmaz
		}

		// Modül içinden gelen global bir araç listesi feature sinyali var mı?
		// Bu sayede "sayfa" route'unda gömülü [al:modul:arac_liste] kullanımı da desteklenir.
		$isGlobalVehicleListActive = (bool) Global_::get('feature_arac_listesi', false);

		// Araç liste/filtre sayfası: ?araba= var VEYA home VEYA global feature aktif
		if (isset($_GET['araba']) || $type === '' || $type === 'home' || $isGlobalVehicleListActive) {
			if (!in_array('vehicle_list', $this->activeFeatures, true)) {
				$this->activeFeatures[] = 'vehicle_list';
			}
		}

		// Home: type boş ya da 'home'
		if ($type === '' || $type === 'home') {
			if (!in_array('home', $this->activeFeatures, true)) {
				$this->activeFeatures[] = 'home';
			}
		}
	}

	// -----------------------------------------------------------------------
	// Core site entity'leri: Organization + WebSite — her sayfada üretilir
	// -----------------------------------------------------------------------
	private function loadCoreSiteEntities(): void {
		if ($this->siteName === '') {
			return;
		}

		$orgData = [
			'name' => $this->siteName,
			'url'  => $this->siteUrl,
		];

		if ($this->siteLogo !== '') {
			$orgData['logo'] = $this->siteLogo;
		}

		if ($this->sitePhone !== '') {
			$orgData['telephone'] = $this->sitePhone;
		}

		$this->context->addEntity(new SemanticEntity('organization', $orgData));

		$this->context->addEntity(new SemanticEntity('website', [
			'name' => $this->siteName,
			'url'  => $this->siteUrl,
		]));
	}

	// -----------------------------------------------------------------------
	// Route bazlı entity'ler — PageMetadata'nın switch mantığına paralel.
	// Her route kendi WebPage + gerekirse Breadcrumb'unu üretir.
	// -----------------------------------------------------------------------
	private function loadRouteEntities(): void {
		$type = $this->params['type'] ?? '';

		// ?araba= parametresi varken route tipinden önce değerlendirilir
		// (PageMetadata'nın davranışıyla paralel)
		if (isset($_GET['araba'])) {
			$this->addVehiclePageEntities();
			return;
		}

		if ($type === '' || $type === 'home') {
			$this->addHomeEntities();
			return;
		}

		switch ($type) {
			case 'sayfa':
				$this->addSayfaEntities();
				break;
			case 'sekme':
				$this->addSekmeEntities();
				break;
			case 'ara':
				$this->addSearchEntities();
				break;
			default:
				$this->addGenericWebPageEntity();
		}
	}

	// --- Home ---------------------------------------------------------------
	private function addHomeEntities(): void {
		$url  = $this->siteUrl . '/';
		$name = trim((string) (Global_::$pageMetadata->title ?? ''));

		if ($name === '') {
			$name = $this->siteName;
		}

		if ($url !== '/' && $name !== '') {
			$this->context->addEntity(new SemanticEntity('webpage', [
				'name' => $name,
				'url'  => $url,
				'description' => trim((string) (Global_::$pageMetadata->description ?? '')),
			]));
		}
		// Home'da breadcrumb üretilmez (tek öğe anlamsız)
	}

	// --- Sayfa (CMS sayfası) ------------------------------------------------
	private function addSayfaEntities(): void {
		$id = (int) ($this->params['id'] ?? 0);
		if ($id < 1) {
			$this->addGenericWebPageEntity();
			return;
		}

		try {
			$stmt = $this->pdo->prepare(
				"SELECT adi, aciklama FROM {$this->do_}sayfa WHERE no = :id LIMIT 1"
			);
			$stmt->execute(['id' => $id]);
			$row = $stmt->fetch(PDO::FETCH_OBJ);
		} catch (Throwable $e) {
			error_log('[StructuredData] Sayfa sorgusu: ' . $e->getMessage());
			$row = null;
		}

		$name = $row ? trim((string) (function_exists('cc')
			? cc($row->adi ?? '', $id, 'sayfa', 'adi')
			: ($row->adi ?? ''))) : '';

		$desc = $row ? trim((string) (function_exists('cc')
			? cc($row->aciklama ?? '', $id, 'sayfa', 'aciklama')
			: ($row->aciklama ?? ''))) : '';

		// Fallback: PageMetadata'dan al
		if ($name === '') {
			$name = trim((string) (Global_::$pageMetadata->title ?? ''));
		}
		if ($desc === '') {
			$desc = trim((string) (Global_::$pageMetadata->description ?? ''));
		}

		$pageUrl = $this->currentPageUrl();

		if ($name !== '' || $pageUrl !== '') {
			$this->context->addEntity(new SemanticEntity('webpage', [
				'name'        => $name,
				'url'         => $pageUrl,
				'description' => $desc,
			]));
		}

		$this->addBreadcrumb($name, $pageUrl);
	}

	// --- Sekme --------------------------------------------------------------
	private function addSekmeEntities(): void {
		$id = (int) ($this->params['id'] ?? 0);
		if ($id < 1) {
			$this->addGenericWebPageEntity();
			return;
		}

		try {
			$stmt = $this->pdo->prepare(
				"SELECT adi, aciklama FROM {$this->do_}sekme WHERE no = :id LIMIT 1"
			);
			$stmt->execute(['id' => $id]);
			$row = $stmt->fetch(PDO::FETCH_OBJ);
		} catch (Throwable $e) {
			error_log('[StructuredData] Sekme sorgusu: ' . $e->getMessage());
			$row = null;
		}

		$name = $row ? trim((string) (function_exists('cc')
			? cc($row->adi ?? '', $id, 'sekme', 'adi')
			: ($row->adi ?? ''))) : '';

		$desc = $row ? trim((string) (function_exists('cc')
			? cc($row->aciklama ?? '', $id, 'sekme', 'aciklama')
			: ($row->aciklama ?? ''))) : '';

		if ($name === '') {
			$name = trim((string) (Global_::$pageMetadata->title ?? ''));
		}
		if ($desc === '') {
			$desc = trim((string) (Global_::$pageMetadata->description ?? ''));
		}

		$pageUrl = $this->currentPageUrl();

		if ($name !== '' || $pageUrl !== '') {
			$this->context->addEntity(new SemanticEntity('webpage', [
				'name'        => $name,
				'url'         => $pageUrl,
				'description' => $desc,
			]));
		}

		$this->addBreadcrumb($name, $pageUrl);
	}

	// --- Arama --------------------------------------------------------------
	private function addSearchEntities(): void {
		$pageUrl = $this->currentPageUrl();
		$name    = trim((string) (Global_::$pageMetadata->title ?? ''));
		$desc    = trim((string) (Global_::$pageMetadata->description ?? ''));

		if ($name !== '' || $pageUrl !== '') {
			$this->context->addEntity(new SemanticEntity('webpage', [
				'name'        => $name,
				'url'         => $pageUrl,
				'description' => $desc,
			]));
		}
		// Arama sayfasında breadcrumb genellikle gerekmez; ileride eklenebilir
	}

	// --- Araç sayfaları (?araba=) -------------------------------------------
	// PageMetadata::loadCarMetadata() mantığıyla paralel:
	//   ?eo var → araç detay WebPage
	//   ?eo yok → araç liste WebPage
	private function addVehiclePageEntities(): void {
		$pageUrl = $this->currentPageUrl();
		// Başlık kaynağı PageMetadata (araç adı, şehir, aksiyon zaten orada hesaplanmış)
		$name = trim((string) (Global_::$pageMetadata->title ?? ''));
		$desc = trim((string) (Global_::$pageMetadata->description ?? ''));

		if ($name !== '' || $pageUrl !== '') {
			$this->context->addEntity(new SemanticEntity('webpage', [
				'name'        => $name,
				'url'         => $pageUrl,
				'description' => $desc,
			]));
		}

		// Araç liste ve detay sayfaları için breadcrumb
		$this->addBreadcrumb($name, $pageUrl);
	}

	// --- Genel fallback -----------------------------------------------------
	private function addGenericWebPageEntity(): void {
		$pageUrl = $this->currentPageUrl();
		$name    = trim((string) (Global_::$pageMetadata->title ?? ''));
		$desc    = trim((string) (Global_::$pageMetadata->description ?? ''));

		if ($name !== '' || $pageUrl !== '') {
			$this->context->addEntity(new SemanticEntity('webpage', [
				'name'        => $name,
				'url'         => $pageUrl,
				'description' => $desc,
			]));
		}

		$this->addBreadcrumb($name, $pageUrl);
	}

	// -----------------------------------------------------------------------
	// Breadcrumb: Home > [Sayfa]
	// En az 2 öğe olmak zorunda (tek öğeli breadcrumb anlamsız).
	// -----------------------------------------------------------------------
	private function addBreadcrumb(string $pageName, string $pageUrl): void {
		if ($this->siteUrl === '') {
			return;
		}

		$homeLabel = $this->siteName !== '' ? $this->siteName : 'Home';
		$items     = [['name' => $homeLabel, 'url' => $this->siteUrl . '/']];

		if ($pageName !== '' && $pageUrl !== '') {
			$items[] = ['name' => $pageName, 'url' => $pageUrl];
		}

		if (count($items) > 1) {
			$this->context->addEntity(new SemanticEntity('breadcrumb', [
				'items' => $items,
			]));
		}
	}

	// -----------------------------------------------------------------------
	// Kayıtlı bileşen katkılarını çalıştır
	// -----------------------------------------------------------------------
	private function applyKatkilar(): void {
		foreach (self::$katkilar as $katki) {
			try {
				$katki($this, $this->context);
			} catch (Throwable $e) {
				error_log('[StructuredData] Katki hatasi: ' . $e->getMessage());
			}
		}
	}
}
