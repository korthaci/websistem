<?php if (! defined('otoban')) exit('Vad.');

if (!syetki([2, 3])) {
    return;
}

$sitemap_message = '';
$sitemap_error = '';

if (isset($_POST['sitemap_olustur'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $sitemap_error = 'Güvenlik hatası: CSRF doğrulaması başarısız.';
    } else {
        require_once R_CLASS_F . '/SitemapGenerator.php';

        try {
            $result = (new SitemapGenerator($pdo, $do_, $so_, Global_::$pageMetadata))->generate();
            $sitemap_message = 'Sitemap oluşturuldu. '
                . number_format($result['url_count']) . ' URL yazıldı. '
                . 'Son üretim: ' . $result['generated_at'];
        } catch (Throwable $e) {
            $sitemap_error = 'Sitemap oluşturulamadı: ' . $e->getMessage();
        }
    }
}

$sitemap_action = href('index', 'ui=sitemap');
$sitemap_file = ROOT . '/sitemap.xml';
$sitemap_exists = is_file($sitemap_file);
$sitemap_file_link = $sitemap_exists
    ? '<a href="' . hs(LOCAL . '/sitemap.xml') . '" target="_blank" rel="noopener" class="uis-btn uis-btn-outline">'
        . yc('sitemap.xml dosyasını aç') . '</a>'
    : '';
$sitemap_button_label = $sitemap_exists ? yc('Sitemap güncelle') : yc('Sitemap oluştur');

echo '<div class="uis-container">'
    . '<h3 class="uis-mb-2">' . yc('Sitemap oluştur') . '</h3>'
    . ($sitemap_message !== '' ? '<div class="uis-alert uis-alert-s">' . hs($sitemap_message) . '</div>' : '')
    . ($sitemap_error !== '' ? '<div class="uis-alert uis-alert-d">' . hs($sitemap_error) . '</div>' : '')
    . '<p class="uis-text-muted">' . yc('Kök dizindeki sitemap.xml dosyasını günceller.') . '</p>'
    . ($sitemap_file_link !== '' ? '<div class="uis-mb-2">' . $sitemap_file_link . '</div>' : '')
    . '<form method="POST" action="' . hs($sitemap_action) . '">'
    . '<input type="hidden" name="csrf_token" value="' . hs($_SESSION['csrf_token'] ?? '') . '">'
    . '<button type="submit" name="sitemap_olustur" class="uis-btn uis-btn-p">'
    . $sitemap_button_label . '</button>'
    . '</form>'
    . '</div>';
