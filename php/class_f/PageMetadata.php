<?php

class PageMetadata {
    private PDO $pdo;
    private $params;
    private $do_;
    private $so_;
    private static $instance = null;
    private ?array $seoActiveLanguages = null;
    private array $seoContentCache = [];

    public $title = '';
    public $description = '';
    public $tags = '';
    public $image = '';
    public $pageName = '';
    
    private function __construct(PDO $pdo, $do_, $so_) {
        $this->pdo = $pdo;
        $this->do_ = $do_;
        $this->so_ = $so_;
        $this->params = Global_::$routeParams;
        $this->loadMetadata();
    }

    public static function getInstance(PDO $pdo, $do_, $so_) {
        if (self::$instance === null) {
            self::$instance = new self($pdo, $do_, $so_);
        }
        return self::$instance;
    }

    private function loadMetadata() {

        // Check for araba parameter first (before type check)
        if (isset($_GET['araba'])) {
            $this->loadCarMetadata();
            return;
        }

        if (empty($this->params['type']) || $this->params['type'] === 'home') {
            $this->loadDefaultMetadata();
            return;
        }

        switch ($this->params['type']) {
            case 'sayfa':
                $this->loadPageMetadata();
                break;
            case 'sekme':
                $this->loadTabMetadata();
                break;
            case 'ara':
                $this->loadSearchMetadata();
                break;
            case 'uye':
                $this->loadUserMetadata();
                break;
            case 'uis':
                $this->pageName = 'Üye işlemleri';
                $uis_param = isset($_GET['uis']) ? z($_GET['uis']) : '';
                $this->title = strtr($this->pageName . ' - ' . ucfirst_utf8($uis_param), ['_' => ' ']);
                break;
            case 'ui':
                $this->pageName = 'Düzenleme';
                $ui_param = isset($_GET['ui']) ? z($_GET['ui']) : '';
                $this->title = strtr($this->pageName . ' - ' . ucfirst_utf8($ui_param), ['_' => ' ']);
                break;
            default:
                $this->loadDefaultMetadata();
        }

        $this->title = trim($this->title);

        if (empty($this->title)) {
            $this->title = $this->so_->d('site_adi');
        }

        $this->title = $this->sanitizeString($this->title);
        $this->description = $this->sanitizeString($this->description);
        $this->tags = $this->sanitizeString($this->tags);
    }

    private function loadDefaultMetadata() {
        try {
            $this->title = '';

            if (method_exists($this->so_, 'd') && method_exists($this->so_, 'no') && function_exists('cc')) {
                $site_adi = $this->so_->d('site_adi');
                $site_title = $this->getSafeValue('cc', $this->so_->d('site_title'), $this->so_->no('site_title'), 'genel_ayarlar', 'site_title');
                
                $this->title = trim($site_title??'') !== '' ? $site_adi . ' | ' . trim($site_title) : $site_adi;
                $this->description = $this->getSafeValue('cc', $this->so_->d('aciklama'), $this->so_->no('aciklama'), 'genel_ayarlar', 'aciklama');
                $this->tags = $this->getSafeValue('cc', $this->so_->d('etiket'), $this->so_->no('etiket'), 'genel_ayarlar', 'etiket');
            } else {
                $this->description = '';
                $this->tags = '';
            }
            
            $this->image = '';
        } catch (Exception $e) {
            $this->logError("Error loading default metadata: " . $e->getMessage());
            // Set fallback values
            $this->title = '';
            $this->description = '';
            $this->tags = '';
            $this->image = '';
        }
    }

    private function loadPageMetadata() {
        $id = $this->params['id'] ?? 0;
        if (!$id) return;
        
        try {
            $sql = "SELECT adi, aciklama, etiket, resim FROM {$this->do_}sayfa WHERE no = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $page = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($page) {
                // Safely access properties using null coalesce to prevent errors if columns don't exist
                $this->pageName = $this->getSafeValue('cc', $page->adi ?? null, $id, 'sayfa', 'adi') . ' ';
                $this->title = $this->pageName;
                $this->description = $this->getSafeValue('cc', $page->aciklama ?? null, $id, 'sayfa', 'aciklama');
                $this->tags = $this->getSafeValue('cc', $page->etiket ?? null, $id, 'sayfa', 'etiket');
                
                // Check if resim exists and if dosya_fe function exists
                if (isset($page->resim) && function_exists('dosya_fe') && function_exists('k_b') && dosya_fe($page->resim)) {
                    $this->image = LOCAL.'/'.k_b($page->resim);
                } else if (isset($page->resim)) {
                    $this->image = LOCAL.'/'.$page->resim;
                }
            }
        } catch (PDOException $e) {
            $this->logError("Error loading page metadata: " . $e->getMessage());
        }
    }

    private function loadTabMetadata() {
        $id = $this->params['id'] ?? 0;
        if (!$id) return;
        
        try {
            $sql = "SELECT adi, aciklama, etiket FROM {$this->do_}sekme WHERE no = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $tab = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($tab) {
                $this->pageName = $this->getSafeValue('cc', $tab->adi ?? null, $id, 'sekme', 'adi') . ' ';
                $this->title = $this->pageName;
                $this->description = $this->getSafeValue('cc', $tab->aciklama ?? null, $id, 'sekme', 'aciklama');
                $this->tags = $this->getSafeValue('cc', $tab->etiket ?? null, $id, 'sekme', 'etiket');
            }
        } catch (PDOException $e) {
            $this->logError("Error loading tab metadata: " . $e->getMessage());
        }
    }

    public function sayfa_url_uret(string $dkod, ?array $context = null): string
    {
        $path = $this->seoPagePath($context);
        if ($path === '') {
            return '';
        }

        $query = $this->seoQueryParams($context);
        unset($query['d']);

        if ($this->isForeignLanguageEnabled() && $dkod !== (string) $this->so_->d('varsayilan_dil')) {
            $query['d'] = $dkod;
        }

        $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        return $path . ($queryString !== '' ? '?' . $queryString : '');
    }

    public function canonical_etiketi_uret(?array $context = null): string
    {
        if (!$this->isSeoPage($context)) {
            return '';
        }

        $activeLanguage = (string) ($context['activeLanguage'] ?? Global_::$d);
        $defaultLanguage = (string) $this->so_->d('varsayilan_dil');
        $language = $activeLanguage;

        if (($this->isPage($context) || $this->isHome($context)) && !$this->hasSeoContent($activeLanguage, $context)) {
            $language = $defaultLanguage;
        }

        $url = $this->sayfa_url_uret($language, $context);
        if ($url === '') {
            return '';
        }

        return '<link rel="canonical" href="' . $this->seoEscapeAttribute($url) . '">';
    }

    public function hreflang_etiketleri_uret(?array $context = null): string
    {
        $out = '';

        foreach ($this->hreflang_url_dizisi_uret($context) as $alternative) {
            $out .= PHP_EOL . '<link rel="alternate" href="'
                . $this->seoEscapeAttribute($alternative['url'])
                . '" hreflang="' . $this->seoEscapeAttribute($alternative['hreflang']) . '">';
        }

        return $out;
    }

    public function hreflang_url_dizisi_uret(?array $context = null): array
    {
        if (!$this->isForeignLanguageEnabled() || !$this->isSeoPage($context)) {
            return [];
        }

        $defaultLanguage = (string) $this->so_->d('varsayilan_dil');
        $alternatives = [];

        foreach ($this->getSeoActiveLanguages() as $language) {
            $dkod = (string) $language->dkod;
            $isDefault = $dkod === $defaultLanguage;

            if (!$isDefault && ($this->isPage($context) || $this->isHome($context)) && !$this->hasSeoContent($dkod, $context)) {
                continue;
            }

            $url = $this->sayfa_url_uret($dkod, $context);
            if ($url === '') {
                continue;
            }

            $alternatives[] = ['hreflang' => $dkod, 'url' => $url];
        }

        $defaultUrl = $this->sayfa_url_uret($defaultLanguage, $context);
        if ($defaultUrl !== '') {
            $alternatives[] = ['hreflang' => 'x-default', 'url' => $defaultUrl];
        }

        return $alternatives;
    }

    private function isForeignLanguageEnabled(): bool
    {
        return (int) $this->so_->d('yabanci_dil') === 1;
    }

    private function isSeoPage(?array $context = null): bool
    {
        $type = (string) ($context['type'] ?? $this->params['type'] ?? '');
        return $type === '' || $type === 'home' || $type === 'sayfa' || $type === 'sekme';
    }

    private function isPage(?array $context = null): bool
    {
        return ($context['type'] ?? $this->params['type'] ?? '') === 'sayfa';
    }

    private function isHome(?array $context = null): bool
    {
        $type = (string) ($context['type'] ?? $this->params['type'] ?? '');
        return $type === '' || $type === 'home';
    }

    private function seoPagePath(?array $context = null): string
    {
        if ($this->isHome($context)) {
            return rtrim((string) LOCAL, '/') . '/';
        }

        $slug = trim((string) ($context['slug'] ?? $this->params['slug'] ?? ''), '/');
        if ($slug === '') {
            return '';
        }

        return rtrim((string) LOCAL, '/') . '/' . $slug;
    }

    private function seoQueryParams(?array $context = null): array
    {
        $query = [];
        $source = is_array($context) && array_key_exists('query', $context)
            ? (array) $context['query']
            : $_GET;

        foreach ($source as $key => $value) {
            $key = (string) $key;

            if (str_starts_with(strtolower($key), 'utm_')) {
                continue;
            }

            if ($this->isInternalSeoRouteParameter($key, $value, $context)) {
                continue;
            }

            $query[$key] = $value;
        }

        return $query;
    }

    private function isInternalSeoRouteParameter(string $key, $value, ?array $context = null): bool
    {
        if (in_array($key, ['sayfa', 'sekme', 's'], true)) {
            return true;
        }

        $slug = trim((string) ($context['slug'] ?? $this->params['slug'] ?? ''), '/');
        return $slug !== '' && $key === $slug && ($value === '' || $value === null);
    }

    private function getSeoActiveLanguages(): array
    {
        if ($this->seoActiveLanguages !== null) {
            return $this->seoActiveLanguages;
        }

        $stmt = $this->pdo->query(
            "SELECT no, dkod
             FROM {$this->do_}diller
             WHERE yayin = 1
             ORDER BY sira ASC, no ASC"
        );

        return $this->seoActiveLanguages = $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    private function hasSeoContent(string $dkod, ?array $context = null): bool
    {
        $defaultLanguage = (string) $this->so_->d('varsayilan_dil');
        if ($dkod === $defaultLanguage) {
            return true;
        }

        $cacheKey = $this->seoContentCacheKey($dkod, $context);
        if (array_key_exists($cacheKey, $this->seoContentCache)) {
            return $this->seoContentCache[$cacheKey];
        }

        $languageNo = null;
        foreach ($this->getSeoActiveLanguages() as $language) {
            if ((string) $language->dkod === $dkod) {
                $languageNo = (int) $language->no;
                break;
            }
        }

        if ($languageNo === null) {
            return $this->seoContentCache[$cacheKey] = false;
        }

        if ($this->isHome($context)) {
            $stmt = $this->pdo->prepare(
                "SELECT 1
                 FROM {$this->do_}blok_html b
                 INNER JOIN {$this->do_}ceviriler c
                    ON c.tablo = 'blok_html'
                   AND c.tablo_no = b.no
                   AND c.dil_no = :dil_no
                   AND c.alan = 'icerik'
                   AND c.yazi IS NOT NULL
                   AND TRIM(c.yazi) <> ''
                 WHERE b.yayin = 1
                   AND b.tema = :tema
                 LIMIT 1"
            );
            $stmt->execute([
                ':dil_no' => $languageNo,
                ':tema' => $this->so_->d('tema'),
            ]);

            return $this->seoContentCache[$cacheKey] = (bool) $stmt->fetchColumn();
        }

        if (!$this->isPage($context)) {
            return $this->seoContentCache[$cacheKey] = true;
        }

        $pageId = (int) ($context['id'] ?? $this->params['id'] ?? 0);
        if ($pageId <= 0) {
            return $this->seoContentCache[$cacheKey] = false;
        }

        $stmt = $this->pdo->prepare(
            "SELECT 1
             FROM {$this->do_}ceviriler
             WHERE dil_no = :dil_no
               AND tablo = 'sayfa'
               AND tablo_no = :tablo_no
               AND alan = 'icerik'
               AND yazi IS NOT NULL
               AND TRIM(yazi) <> ''
             LIMIT 1"
        );
        $stmt->execute([
            ':dil_no' => $languageNo,
            ':tablo_no' => $pageId,
        ]);

        return $this->seoContentCache[$cacheKey] = (bool) $stmt->fetchColumn();
    }

    private function seoContentCacheKey(string $dkod, ?array $context = null): string
    {
        return implode('|', [
            (string) ($context['type'] ?? $this->params['type'] ?? ''),
            (string) ($context['id'] ?? $this->params['id'] ?? ''),
            (string) ($context['slug'] ?? $this->params['slug'] ?? ''),
            (string) $this->so_->d('tema'),
            $dkod,
        ]);
    }

    private function seoEscapeAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function loadSearchMetadata() {
        try {
            // Safely call z function if it exists
            if (function_exists('z')) {
                $ara = isset($_GET['ara']) ? z($_GET['ara']) : '';
            } else {
                $ara = isset($_GET['ara']) ? htmlspecialchars($_GET['ara'], ENT_QUOTES, 'UTF-8') : '';
            }
            
            if (!empty($ara)) {
                $this->title = $ara . ' ';
                $this->description = '';
                $this->tags = '';
            }
        } catch (Exception $e) {
            $this->logError("Error loading search metadata: " . $e->getMessage());
        }
    }
    
    /**
     * Safely call functions like cc() with error handling
     * 
     * @param string $function Function name to call
     * @param mixed $args,... Arguments to pass to the function
     * @return mixed Result of function call or empty string on error
     */
    private function getSafeValue($function, ...$args) {
        if (!function_exists($function)) {
            $this->logError("Function {$function} does not exist");
            return '';
        }
        
        try {
            return call_user_func_array($function, $args);
        } catch (Exception $e) {
            $this->logError("Error calling {$function}: " . $e->getMessage());
            return '';
        }
    }

    private function loadUserMetadata() {
        $id = $this->params['id'] ?? 0;
        if (!$id) return;
        
        // Load user metadata if needed
        try {
            $sql = "SELECT adi FROM {$this->do_}uyeler WHERE no = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $username = $stmt->fetchColumn();
            
            if ($username) {
                $this->pageName = $username;
                $this->title = "Profil: " . $username;
            }
        } catch (PDOException $e) {
            $this->logError("Error loading user metadata: " . $e->getMessage());
        }
    }
    

    /*
    if isset($_GET['eo']) // araba numarası
    $araba_no = intval($_GET['eo']);
    $stmt = $pdo->prepare("SELECT 
		a.no AS ano, a.firma_no, a.sehir_no, a.marka_no, a.marka_model_no, a.model_ayrinti,
        f.no AS fno, f.adi AS fadi
			FROM {$do_}araba a, {$do_}firmalar f
				WHERE a.no=? AND a.firma_no = f.no AND a.yayin = 1 AND a.yayin1 = 1");
    $araba_bilgi = $stmt->execute([$araba_no]);

    $bolge1_sehir_adi = db_adi::get($pdo, $do_, 'araba_sehir_bolge', $_GET['b1'], 'adi');//Şehir bölge adı
    
    $marka = db_adi::get($pdo, $do_, 'araba_marka', $araba_bilgi->marka_no, 'adi');
	$model = db_adi::get($pdo, $do_, 'araba_marka_model', $araba_bilgi->marka_model_no, 'adi');

    //eğer eo varsa title şu şekilde olmalı : 
    [Şehir bölge adı ] [Araba marka Model model ayrinti] [Araba Kirala] // buradaki araba kirala yazısı aslında  (araba key araba value) değerlerinden geliyor.

    // $_GET : eğer t1 varsa ve eo yoksa o zaman yine de t1 title'a eklenmeli. 
    [Şehir bölge adı ] [araba get key ve value değerleri]

    */

    private function loadCarMetadata() {
        try {
            // Safely get araba parameter
            $araba = isset($_GET['araba']) ? (function_exists('z') ? z($_GET['araba']) : htmlspecialchars($_GET['araba'], ENT_QUOTES, 'UTF-8')) : '';
            
            if (empty($araba)) {
                $this->loadDefaultMetadata();
                return;
            }
            
            // Create base title from araba parameter
            if (function_exists('ucfirst_utf8')) {
                $arabaTitle = ucfirst_utf8($araba);
            } else {
                $arabaTitle = ucfirst($araba);
            }
            
            $titleParts = [];
            
            // Check if eo (araba no) exists
            if (isset($_GET['eo']) && !empty($_GET['eo'])) {
                $araba_no = intval($_GET['eo']);
                
                // Get car information from database
                $sql = "SELECT 
                    a.no AS ano, a.firma_no, a.sehir_no, a.marka_no, a.marka_model_no, a.model_ayrinti,
                    f.no AS fno, f.adi AS fadi
                FROM {$this->do_}araba a
                LEFT JOIN {$this->do_}firmalar f ON a.firma_no = f.no
                WHERE a.no = :araba_no AND a.yayin = 1 AND a.yayin1 = 1";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(['araba_no' => $araba_no]);
                $araba_bilgi = $stmt->fetch(PDO::FETCH_OBJ);
                
                if ($araba_bilgi) {
                    // Get city/region name if b1 exists
                    if (isset($_GET['b1']) && !empty($_GET['b1']) && class_exists('db_adi')) {
                        $bolge1_sehir_adi = db_adi::get($this->pdo, $this->do_, 'araba_sehir_bolge', $_GET['b1'], 'adi');
                        if (!empty($bolge1_sehir_adi)) {
                            $titleParts[] = $bolge1_sehir_adi;
                        }
                    }
                    
                    // Get car brand and model
                    if (class_exists('db_adi')) {
                        $marka = db_adi::get($this->pdo, $this->do_, 'araba_marka', $araba_bilgi->marka_no, 'adi');
                        $model = db_adi::get($this->pdo, $this->do_, 'araba_marka_model', $araba_bilgi->marka_model_no, 'adi');
                        
                        $carInfo = trim($marka . ' ' . $model . ' ' . $araba_bilgi->model_ayrinti);
                        if (!empty($carInfo)) {
                            $titleParts[] = $carInfo;
                        }
                    }
                    
                    // Add araba action (e.g., "Araba Kirala"). The passive detail page
                    // keeps the vehicle name as the title.
                    if ($araba !== 'goster') {
                        $titleParts[] = 'Araba ' . $arabaTitle;
                    }
                }
            } else {
                // If eo doesn't exist but t1 exists, still add city/region
                if (isset($_GET['t1']) && !empty($_GET['t1'])) {
                    if (isset($_GET['b1']) && !empty($_GET['b1']) && class_exists('db_adi')) {
                        $bolge1_sehir_adi = db_adi::get($this->pdo, $this->do_, 'araba_sehir_bolge', $_GET['b1'], 'adi');
                        if (!empty($bolge1_sehir_adi)) {
                            $titleParts[] = $bolge1_sehir_adi;
                        }
                    }
                }
                
                // Add araba action
                $titleParts[] = 'Araba ' . $arabaTitle;
            }
            
            // Build final title
            $this->title = implode(' ', $titleParts);
            $this->pageName = $this->title;
            
            // Set default metadata
            $this->description = '';
            $this->tags = '';
            $this->image = '';
            
        } catch (Exception $e) {
            $this->logError("Error loading car metadata: " . $e->getMessage());
            $this->loadDefaultMetadata();
        }
    }
    
    /**
     * Sanitize a string (replacement for ampd function)
     * 
     * @param string $str The string to sanitize
     * @return string Sanitized string
     */
    private function sanitizeString($str) {
        if (empty($str)) return '';
        // Simple HTML special chars conversion - adjust as needed
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Log an error message
     * 
     * @param string $message The error message to log
     */
    private function logError($message) {
        error_log('[PageMetadata] ' . $message);
    }
}
