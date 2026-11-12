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

        if ($this->loadComponentMetadata()) {
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

    private function loadComponentMetadata(): bool
    {
        $component = (string) ($this->params['bilesen'] ?? '');
        if ($component === '') {
            foreach ((array) (Global_::$bilesenler_url_dizi ?? []) as $candidate) {
                if (is_string($candidate) && array_key_exists($candidate, $_GET)) {
                    $component = $candidate;
                    break;
                }
            }
        }

        if ($component === '' || !preg_match('/^[A-Za-z0-9_]+$/', $component)) {
            return false;
        }

        $class = $component . '_pagemetadata_katki';
        if (!class_exists($class, false) || !method_exists($class, 'metadata')) {
            return false;
        }

        try {
            $metadata = $class::metadata([
                'pdo' => $this->pdo,
                'table_prefix' => $this->do_,
                'settings' => $this->so_,
                'params' => $this->params,
                'query' => $_GET,
            ]);

            if (!is_array($metadata) || trim((string) ($metadata['title'] ?? '')) === '') {
                return false;
            }

            $title = trim((string) $metadata['title']);
            $siteTitle = $this->getLocalizedSiteTitle();
            $this->title = $this->sanitizeString($title . ($siteTitle !== '' ? ' | ' . $siteTitle : ''));
            $this->description = $this->sanitizeString((string) ($metadata['description'] ?? ''));
            $this->tags = $this->sanitizeString((string) ($metadata['tags'] ?? ''));
            $this->image = (string) ($metadata['image'] ?? '');
            $this->pageName = $this->sanitizeString((string) ($metadata['pageName'] ?? $title));

            return true;
        } catch (Throwable $e) {
            $this->logError('Component metadata hook error: ' . $e->getMessage());
            return false;
        }
    }

    private function getLocalizedSiteTitle(): string
    {
        if (!method_exists($this->so_, 'd') || !method_exists($this->so_, 'no') || !function_exists('cc')) {
            return '';
        }

        return trim((string) $this->getSafeValue(
            'cc',
            $this->so_->d('site_title'),
            $this->so_->no('site_title'),
            'genel_ayarlar',
            'site_title'
        ));
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

        if (($this->isPage($context) || $this->isHome($context) || $this->isComponent($context)) && !$this->hasSeoContent($activeLanguage, $context)) {
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

            if (!$isDefault && ($this->isPage($context) || $this->isHome($context) || $this->isComponent($context)) && !$this->hasSeoContent($dkod, $context)) {
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
        return $type === '' || $type === 'home' || $type === 'sayfa' || $type === 'sekme' || $this->isComponent($context);
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

    private function isComponent(?array $context = null): bool
    {
        $source = is_array($context) ? $context : $this->params;
        if (($source['type'] ?? '') !== 'bilesen') {
            return false;
        }

        $path = trim((string) ($source['path'] ?? ''), '/');
        if ($path !== '') {
            return ($source['published'] ?? true) !== false;
        }

        return trim((string) ($source['bilesen'] ?? ''), '/') !== ''
            && trim((string) ($source['islem'] ?? ''), '/') !== '';
    }

    private function seoPagePath(?array $context = null): string
    {
        if ($this->isHome($context)) {
            return rtrim((string) LOCAL, '/') . '/';
        }

        if ($this->isComponent($context)) {
            $source = is_array($context) ? $context : $this->params;
            $path = trim((string) ($source['path'] ?? ''), '/');
            if ($path === '') {
                $path = trim(
                    (string) ($source['bilesen'] ?? '') . '/' . (string) ($source['islem'] ?? ''),
                    '/'
                );
            }

            return $path === '' ? '' : rtrim((string) LOCAL, '/') . '/' . $path;
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

        $source = is_array($context) ? $context : $this->params;
        $component = trim((string) ($source['bilesen'] ?? ''), '/');
        if ($component !== '' && $key === $component) {
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

        if ($this->isComponent($context)) {
            $source = is_array($context) ? $context : $this->params;
            if (array_key_exists('languages', $source) && is_array($source['languages'])) {
                foreach ($source['languages'] as $language) {
                    if (!is_array($language)) {
                        continue;
                    }

                    if ((string) ($language['dkod'] ?? '') === $dkod) {
                        return $this->seoContentCache[$cacheKey] = ($language['content_available'] ?? false) === true;
                    }
                }

                return $this->seoContentCache[$cacheKey] = false;
            }

            return $this->seoContentCache[$cacheKey] = true;
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
