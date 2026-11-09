<?php

class SitemapGenerator
{
    private PDO $pdo;
    private string $do_;
    private $so_;
    private PageMetadata $pageMetadata;
    private int $urlCount = 0;

    public function __construct(PDO $pdo, string $do_, $so_, PageMetadata $pageMetadata)
    {
        $this->pdo = $pdo;
        $this->do_ = $do_;
        $this->so_ = $so_;
        $this->pageMetadata = $pageMetadata;
    }

    public function generate(): array
    {
        $xml = $this->buildXml();
        $target = ROOT . '/sitemap.xml';
        $this->writeAtomically($target, $xml);

        return [
            'url_count' => $this->urlCount,
            'generated_at' => date(DATE_ATOM),
            'path' => $target,
        ];
    }

    private function buildXml(): string
    {
        $this->urlCount = 0;
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
            . 'xmlns:xhtml="http://www.w3.org/1999/xhtml">' . PHP_EOL;

        $this->appendUrlEntries($xml, ['type' => 'home', 'query' => []]);

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
                'lastmod' => $page->tarih,
            ]);
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
            ]);
        }

        $xml .= '</urlset>' . PHP_EOL;
        return $xml;
    }

    private function appendUrlEntries(string &$xml, array $context): void
    {
        $alternatives = $this->pageMetadata->hreflang_url_dizisi_uret($context);
        if (empty($alternatives)) {
            $defaultLanguage = (string) $this->so_->d('varsayilan_dil');
            $defaultUrl = $this->pageMetadata->sayfa_url_uret($defaultLanguage, $context);
            if ($defaultUrl !== '') {
                $this->appendUrl($xml, $defaultUrl, [], $context['lastmod'] ?? null);
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

        foreach ($alternatives as $alternative) {
            if ($alternative['hreflang'] === 'x-default') {
                continue;
            }

            $this->appendUrl($xml, $alternative['url'], $alternatives, $context['lastmod'] ?? null);
        }

        if (!array_filter($alternatives, static function (array $alternative): bool {
            return $alternative['hreflang'] !== 'x-default';
        })) {
            $defaultLanguage = (string) $this->so_->d('varsayilan_dil');
            $defaultUrl = $this->pageMetadata->sayfa_url_uret($defaultLanguage, $context);
            if ($defaultUrl !== '') {
                $this->appendUrl($xml, $defaultUrl, $alternatives, $context['lastmod'] ?? null);
            }
        }
    }

    private function appendUrl(string &$xml, string $url, array $alternatives, $lastmod): void
    {
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

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value);
        $errors = DateTimeImmutable::getLastErrors();
        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return null;
        }

        return $date->format(DATE_ATOM);
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
