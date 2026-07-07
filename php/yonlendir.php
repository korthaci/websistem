<?php if (! defined('otoban')) exit('Veriyolu açık değil.');

require_once __DIR__ . '/class_f/Router.php';

$router = Router::getInstance($pdo, $do_);

$requestUri = z($_SERVER['REQUEST_URI']);
$baseUri = (SUB_DIR == '') ? '/' : '/' . trim(SUB_DIR, '/') . '/';
$uri = substr($requestUri, strlen($baseUri));

$yonlendir_dosya = $router->dispatch($uri);

$routeParams = $router->getParams();
$n = $router->getN();

Global_::$routeParams = $routeParams;

$indexx = ($yonlendir_dosya !== YONLENDIR_D.'/kolon1') ? 0 : 1;

Global_::$yonlendir_index_body_class ??= [];
Global_::$yonlendir_index_body_class[] = isset($routeParams['type']) && !empty(($routeParams['type'])) ? ' __'.$routeParams['type'].' ' : '';
Global_::$yonlendir_index_body_class[] = ' __indexx' . $indexx;

//$yonlendir_index_body_class .= ($_dmod) ? ' __dmod ' : '';
//$yonlendir_index_body_class .= ($tema_demo) ? ' ____demo ' : '';

$pageMetadata = PageMetadata::getInstance($pdo, $do_, $so_);
Global_::$pageMetadata = $pageMetadata;

$structuredData = StructuredData::getInstance($pdo, $do_, $so_);
Global_::$structuredData = $structuredData;

$kolon1 = $yonlendir_dosya.'.php';
define('KOLON_1', $kolon1);
