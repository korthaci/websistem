<?php
if (is_file('./webunion.php')){include_once './webunion.php';}

session_start();

define('otoban', 'ROOT');

$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

(file_exists(__DIR__ .'/rlv_.php')) ? require __DIR__ .'/rlv_.php' : exit('Site Bakımda.!');
require_once R_CLASS_F . '/ini_set.php';
require_once R_CLASS_F . '/Global_.php';
require_once R_CLASS_F . '/fonksiyon.php';
require_once R_CLASS_F . '/class_.php';
require_once R_CLASS_F . '/phpmailer_mail.php';
require_once R_CLASS_F . '/pdo_db_helper.php';
require_once R_CLASS_F . '/form_c.php';
require_once R_CLASS_F . '/class.dom_element_menu.php';
require_once R_CLASS_F . '/PageMetadata.php';

if (DB_TYPE === 'sqlite') {
    $sqlite_dir = R_PHP . "/sqlite_data";
    $pdo_dsn = "sqlite:" . $sqlite_dir . "/" . VERITABANI . ".sqlite";
    if (!is_dir($sqlite_dir)) mkdir($sqlite_dir, 0755, true);
    $pdo = new PDO($pdo_dsn);
    $pdo->exec("PRAGMA busy_timeout = 5000");
    $pdo->exec("PRAGMA journal_mode = WAL");
    
    // MySQL polyfills for SQLite
    $pdo->sqliteCreateFunction('NOW', function() { return date('Y-m-d H:i:s'); }, 0);
    $pdo->sqliteCreateFunction('MD5', function($s) { return md5($s); }, 1);
} else {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . VERITABANI . ";charset=utf8mb4", DB_KULLANICI, DB_SIFRE);
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

$do_ = DB_PREFIX;
$pdo_db = new pdo_db_helper($pdo);
$so_ = new genel_ayarlar($pdo, $do_);
if (!$so_) exit('ga');

define('TEMABU', TEMA_DIR . '/' . $so_->d('tema'));
define('IMG', LOCAL . '/assets/img');

Global_::$dil_yonlendir = new dil_yonlendir($pdo, $do_, $so_->d('yabanci_dil'), $so_->d('varsayilan_dil'));
$d = Global_::$d = Global_::$dil_yonlendir->d();
Global_::$dil_no = Global_::$dil_yonlendir->dil_no;
$ceviri_dizi = Global_::$dil_yonlendir->ceviri_dizi;

$bo_ = new bilesen_json_ayarlar($pdo, $do_);

Global_::$bilesenler_url_dizi = [];
$bilesenler = $pdo->query("SELECT url FROM {$do_}bilesen WHERE yayin = 1")->fetchAll() ?? [];
foreach ($bilesenler as $b__) {
    Global_::$bilesenler_url_dizi[] = $b__->url;
    is_file($f__ = BILESEN_DIR . "/{$b__->url}/php/classf/require_b.php") && require_once $f__;
}

$smtp_dizi = array(
	'varsayilan' => 'local',
	/*
	'driver' => 'phpmailer',  // symfony
    'method' => 'smtp',       // api
	*/
	'local'	=> ['port' => SMTP_PORT_TLS, 'from' => false, 'host' => SMTP_HOST, 'k_adi' => SMTP_K_ADI, 'sifre' => SMTP_SIFRE],
	'amazon'=> ['port' => SMTP_PORT_TLS, 'from' => SMTP_K_ADI, 'host' => 'email-smtp.us-east-1.amazonaws.com', 'k_adi' => SMTP_AWS_K_ADI, 'sifre' => SMTP_AWS_SIFRE],
	'sendgrid' => ['port' => SMTP_PORT_TLS, 'from' => SMTP_K_ADI, 'host' => 'smtp.sendgrid.net',      'k_adi' => 'apikey', 'sifre' => 'SG.XXXX'],
    'mailgun' => ['port' => SMTP_PORT_TLS, 'from' => SMTP_K_ADI, 'host' => 'smtp.mailgun.org', 'k_adi' => 'postmaster@mg.abc.com', 'sifre' => 'XXXX']
);
Global_::$phpmailer = new phpmailer_mail($smtp_dizi, 0); //debug:0

include_once R_PHP.'/uye_giris_kontrol.php';

if (defined('index_php')) {
	include_once R_PHP.'/arama_ozellik.php';
	include_once R_PHP.'/yonlendir.php';
}
