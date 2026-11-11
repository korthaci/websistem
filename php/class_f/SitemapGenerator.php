<?php

class SitemapGenerator
{
    private PDO $pdo;
    private string $do_;
    private $so_;
    private PageMetadata $pageMetadata;
    private int $urlCount = 0;
    private string $lastmod_tarih_tipi;

    public function __construct(PDO $pdo, string $do_, $so_, PageMetadata $pageMetadata, string $lastmod_tarih_tipi = 'now')
    {
        $this->pdo = $pdo;
        $this->do_ = $do_;
        $this->so_ = $so_;
        $this->pageMetadata = $pageMetadata;
        $this->lastmod_tarih_tipi = in_array($lastmod_tarih_tipi, ['tarih', 'now'], true)
            ? $lastmod_tarih_tipi
            : 'now';
    }

    public function generate(): array
    {
        $generatedAt = date(DATE_ATOM);
        $xml = $this->buildXml($generatedAt);
        $target = ROOT . '/sitemap.xml';
        $this->writeAtomically($target, $xml);

        return [
            'url_count' => $this->urlCount,
            'generated_at' => $generatedAt,
            'path' => $target,
        ];
    }

    private function buildXml(string $generatedAt): string
    {
        $this->urlCount = 0;
        $yazilan_urller = [];
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
            . 'xmlns:xhtml="http://www.w3.org/1999/xhtml">' . PHP_EOL;

        $this->appendUrlEntries($xml, [
            'type' => 'home',
            'query' => [],
            'lastmod' => $this->lastmodDegeri(null, $generatedAt),
        ], $yazilan_urller);

        $stmt = $this->pdo->query(
            "SELECT no, url, tarih
             FROM {$this->do_}sayfa
             WHERE yayin = 1
               AND url IS NOT NULL
               AND TRIM(url) <> ''
             ORDER BY no ASC"
        );
        foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $page) {
            $this->appendUrlEntries($xml, [
                'type' => 'sayfa',
                'id' => (int) $page->no,
                'slug' => (string) $page->url,
                'query' => [],
                'lastmod' => $this->lastmodDegeri($page->tarih, $generatedAt),
            ], $yazilan_urller);
        }

        $stmt = $this->pdo->query(
            "SELECT no, url
             FROM {$this->do_}sekme
             WHERE yayin = 1
               AND url IS NOT NULL
               AND TRIM(url) <> ''
             ORDER BY no ASC"
        );
        foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $tab) {
            $this->appendUrlEntries($xml, [
                'type' => 'sekme',
                'id' => (int) $tab->no,
                'slug' => (string) $tab->url,
                'query' => [],
                'sitemap_default_only' => true,
                'lastmod' => $this->lastmodDegeri(null, $generatedAt),
            ], $yazilan_urller);
        }

        $this->appendBilesenEntries($xml, $generatedAt, $yazilan_urller);

        $xml .= '</urlset>' . PHP_EOL;
        return $xml;
    }

    private function appendUrlEntries(string &$xml, array $context, array &$yazilan_urller, ?array $allowedLanguages = null): void
    {
        $alternatives = $this->pageMetadata->hreflang_url_dizisi_uret($context);
        if ($allowedLanguages !== null) {
            $alternatives = $this->filterAlternatives($alternatives, $allowedLanguages);
        }
        if (empty($alternatives)) {
            $defaultLanguage = (string) $this->so_->d('varsayilan_dil');
            $defaultUrl = $this->pageMetadata->sayfa_url_uret($defaultLanguage, $context);
            if ($defaultUrl !== '') {
                $this->appendUrl($xml, $defaultUrl, [], $context['lastmod'] ?? null, $yazilan_urller);
            }
            return;
        }

        if (!empty($context['sitemap_default_only'])) {
            $defaultLanguage = (string) $this->so_->d('varsayilan_dil');
            $defaultUrl = $this->pageMetadata->sayfa_url_uret($defaultLanguage, $context);
            $alternatives = array_values(array_filter(
                $alternatives,
                static fn(array $alternative): bool => $alternative['hreflang'] === 'x-default'
                    || $alternative['hreflang'] === $defaultLanguage
            ));

            if ($defaultUrl !== '') {
                $alternatives = array_values(array_filter(
                    $alternatives,
                    static fn(array $alternative): bool => $alternative['hreflang'] !== $defaultLanguage
                ));
                array_unshift($alternatives, ['hreflang' => $defaultLanguage, 'url' => $defaultUrl]);
            }
        }

        $defaultLanguage = (string) $this->so_->d('varsayilan_dil');
        $defaultUrl = $this->pageMetadata->sayfa_url_uret($defaultLanguage, $context);
        if ($defaultUrl !== '') {
            $this->appendUrl($xml, $defaultUrl, $alternatives, $context['lastmod'] ?? null, $yazilan_urller);
        }
    }

    private function appendBilesenEntries(string &$xml, string $generatedAt, array &$yazilan_urller): void
    {
        $bilesenler = Global_::$bilesenler_url_dizi ?? [];
        if (!is_array($bilesenler)) {
            return;
        }

        $siniflar = [];
        foreach ($bilesenler as $bilesen_adi) {
            $bilesen_adi = (string) $bilesen_adi;
            if (!preg_match('/^[A-Za-z0-9_]+$/', $bilesen_adi)) {
                continue;
            }

            $sinif = $bilesen_adi . '_sitemap_katki';
            if (!class_exists($sinif, false) || !method_exists($sinif, 'sitemap_kayitlari')) {
                continue;
            }

            $siniflar[$sinif] = true;
        }

        if ($siniflar === []) {
            return;
        }

        $ctx = [
            'pdo' => $this->pdo,
            'table_prefix' => $this->do_,
            'active_languages' => $this->getActiveLanguages(),
            'default_language' => (string) $this->so_->d('varsayilan_dil'),
            'generated_at' => $generatedAt,
        ];

        foreach (array_keys($siniflar) as $sinif) {
            try {
                $kayitlar = $sinif::sitemap_kayitlari($ctx);
                if (!is_array($kayitlar)) {
                    error_log('[BilesenSitemap] ' . $sinif . ': sitemap_kayitlari() array döndürmedi.');
                    continue;
                }

                foreach ($kayitlar as $kayit) {
                    if (!is_array($kayit) || ($kayit['published'] ?? null) !== true) {
                        continue;
                    }

                    $path = $kayit['path'] ?? null;
                    $query = $kayit['query'] ?? null;
                    if (!is_string($path) || $path === '' || !is_array($query)) {
                        continue;
                    }

                    $context = [
                        'type' => 'bilesen',
                        'path' => $path,
                        'query' => $query,
                        'id' => $kayit['id'] ?? null,
                        'published' => true,
                        'languages' => is_array($kayit['languages'] ?? null) ? $kayit['languages'] : [],
                        'content_definition' => $kayit['content_definition'] ?? null,
                        'lastmod' => $generatedAt,
                    ];

                    $this->appendUrlEntries(
                        $xml,
                        $context,
                        $yazilan_urller,
                        $context['languages']
                    );
                }
            } catch (Throwable $e) {
                error_log('[BilesenSitemap] ' . $sinif . ': ' . $e->getMessage());
            }
        }
    }

    private function getActiveLanguages(): array
    {
        $stmt = $this->pdo->query(
            "SELECT no, dkod
             FROM {$this->do_}diller
             WHERE yayin = 1
             ORDER BY sira ASC"
        );

        return array_map(
            static fn(object $language): array => [
                'no' => (int) $language->no,
                'dkod' => (string) $language->dkod,
            ],
            $stmt->fetchAll(PDO::FETCH_OBJ)
        );
    }

    private function filterAlternatives(array $alternatives, array $languages): array
    {
        $defaultLanguage = (string) $this->so_->d('varsayilan_dil');
        $allowed = [$defaultLanguage => true];

        foreach ($languages as $language) {
            if (!is_array($language)) {
                continue;
            }

            $dkod = (string) ($language['dkod'] ?? '');
            if ($dkod !== '' && ($language['content_available'] ?? false) === true) {
                $allowed[$dkod] = true;
            }
        }

        return array_values(array_filter(
            $alternatives,
            static fn(array $alternative): bool => $alternative['hreflang'] === 'x-default'
                || isset($allowed[$alternative['hreflang']])
        ));
    }

    private function appendUrl(string &$xml, string $url, array $alternatives, $lastmod, array &$yazilan_urller): void
    {
        if (isset($yazilan_urller[$url])) {
            return;
        }

        $yazilan_urller[$url] = true;
        $this->urlCount++;
        $xml .= '  <url>' . PHP_EOL;
        $xml .= '    <loc>' . $this->xmlEscape($url) . '</loc>' . PHP_EOL;

        $lastmodValue = $this->formatLastmod($lastmod);
        if ($lastmodValue !== null) {
            $xml .= '    <lastmod>' . $this->xmlEscape($lastmodValue) . '</lastmod>' . PHP_EOL;
        }

        foreach ($alternatives as $alternative) {
            $xml .= '    <xhtml:link rel="alternate" hreflang="'
                . $this->xmlEscape($alternative['hreflang'])
                . '" href="' . $this->xmlEscape($alternative['url']) . '" />' . PHP_EOL;
        }

        $xml .= '  </url>' . PHP_EOL;
    }

    private function formatLastmod($value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat(DATE_ATOM, (string) $value);
        if ($date === false) {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value);
        }
        $errors = DateTimeImmutable::getLastErrors();
        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return null;
        }

        return $date->format(DATE_ATOM);
    }

    private function lastmodDegeri($tarih, string $generatedAt): ?string
    {
        return $this->lastmod_tarih_tipi === 'now' ? $generatedAt : $tarih;
    }

    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function writeAtomically(string $target, string $contents): void
    {
        $temporary = tempnam(ROOT, '.sitemap-');
        if ($temporary === false) {
            throw new RuntimeException('Sitemap için geçici dosya oluşturulamadı. Kök dizin yazma yetkisini kontrol edin.');
        }

        $backup = null;
        try {
            $written = file_put_contents($temporary, $contents, LOCK_EX);
            if ($written !== strlen($contents)) {
                throw new RuntimeException('Sitemap geçici dosyaya yazılamadı.');
            }

            if (@rename($temporary, $target)) {
                $temporary = null;
                return;
            }

            if (is_file($target)) {
                $backup = tempnam(ROOT, '.sitemap-old-');
                if ($backup === false) {
                    throw new RuntimeException('Mevcut sitemap yedeklenemedi.');
                }
                @unlink($backup);

                if (!@rename($target, $backup)) {
                    throw new RuntimeException('Mevcut sitemap korunamadı; yeni dosya yazılmadı.');
                }
            }

            if (!@rename($temporary, $target)) {
                if ($backup !== null) {
                    @rename($backup, $target);
                }
                throw new RuntimeException('Sitemap kök dizine taşınamadı; mevcut sitemap korunmaya çalışıldı.');
            }

            $temporary = null;
            if ($backup !== null) {
                @unlink($backup);
                $backup = null;
            }
        } finally {
            if ($temporary !== null && is_file($temporary)) {
                @unlink($temporary);
            }
            if ($backup !== null && is_file($backup) && !is_file($target)) {
                @rename($backup, $target);
            }
        }
    }
}
