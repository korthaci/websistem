<?php if (!defined('otoban')) exit('Veriyolu açık değil.');

$menu_sablon = sanitize_php_tag(file_get_contents(TEMABU.'/menu.html'));

if (preg_match("/data-menu([\.=])(\"dynamic\"|v2)/", $menu_sablon)) {

    $dizi = new dom_element(dom_element::assignAttributes($menu_sablon), $pdo, $do_);
    $dizi->tag_class();
    $db_menu_dizi = $dizi->menu_recursive_dizi(0);
    echo $dizi->menu_recursive_dizi_html_yaz($db_menu_dizi);

} else {
    echo $menu_sablon;
}