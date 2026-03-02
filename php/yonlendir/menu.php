<?php if (!defined('otoban')) exit('Veriyolu açık değil.');

$menu_sablon = sanitize_php_tag(file_get_contents(TEMABU.'/menu.html'));

if (preg_match('/data-menu\s*=\s*["\']?(?:dynamic|v2)["\']?|data-version\s*=\s*["\']menu\.v2["\']/', $menu_sablon)) {
    $renderer = new dom_element($pdo, $do_);
    $menuArray = $renderer->menu_recursive_dizi(0);
    
    if (empty($menuArray)) {
        if (function_exists('syetki') && syetki([2, 3])) {
            echo '<div style="position: relative; opacity: 0.5; display: inline-block; width: 100%;">';
            echo '<a href="'.LOCAL.'/ui/menuduzenle" style="position: absolute; top: -10px; right: -10px; background: #dc3545; color: white; font-size: 11px; padding: 4px 8px; border-radius: 4px; z-index: 9999; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.2);" title="Şu an demo menüyü görüyorsunuz. Gerçek menü oluşturmak için tıklayın.">Menü Ekle</a>';
            echo $menu_sablon;
            echo '</div>';
        } else {
            // Ziyaretçiye hiçbir şey gösterme
        }
    } else {
        echo $renderer->renderFull($menu_sablon, $menuArray);
    }
} else {
    echo $menu_sablon;
}