<?php
// Show all PHP errors (development mode) | Set to 0 in production
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Application version
define ('VERSION', '1.0');

// Root directory of the project
define ('ROOT', realpath(__DIR__));

// Main domain name
define ('DOMAIN', 'localhost');

// Subdirectory if the project runs inside a folder (e.g. /panel)
define ('SUB_DIR', '');

// Protocol type (http or https)
define ('HTTPS', 'http');

// Base URL of the application
define ('LOCAL', HTTPS . '://' . DOMAIN . SUB_DIR);

// Database type (mysql or sqlite)
define ('DB_TYPE', 'mysql');

// Database server host
define ('DB_HOST', 'localhost');

// Database username (from environment or fallback)
define ('DB_KULLANICI', $_ENV['DB_KULLANICI'] ?? 'YOUR_DB_USERNAME');

// Database password (from environment or fallback)
define ('DB_SIFRE', $_ENV['DB_SIFRE'] ?? 'YOUR_DB_PASSWORD');

// Database name
define ('VERITABANI', 'websistem');

// Database table prefix
define ('DB_PREFIX', 'r_');

// PHP main directory
define ('R_PHP', ROOT.'/php');

// Classes directory
define ('R_CLASS_F', R_PHP.'/class_f');

// Theme directory
define ('TEMA_DIR', ROOT.'/tema');

// Redirect handlers directory
define ('YONLENDIR_D', R_PHP.'/yonlendir');

// Modules directory
define ('MODUL_DIR', R_PHP.'/modul');

// Components directory
define ('BILESEN_DIR', R_PHP.'/bilesen');

// Translation / language files directory
define ('CEVIRI_D', R_PHP.'/ceviri');

// CDN base URL (leave empty if unused)
define ('_CDN_', '');

// SMTP default port (non-secure)
define ('SMTP_PORT', 25);

// SMTP SSL port
define ('SMTP_PORT_SSL', 465);

// SMTP TLS port
define ('SMTP_PORT_TLS', 587);

// SMTP mail server host
define ('SMTP_HOST', 'mail.' . DOMAIN);
// SMTP username
define ('SMTP_K_ADI', 'mail@' . DOMAIN);
// SMTP password (from environment or fallback)
define ('SMTP_SIFRE', $_ENV['SMTP_SIFRE'] ?? 'YOUR_SMTP_PASSWORD');

// AWS SMTP access key (optional)
define ('SMTP_AWS_K_ADI', $_ENV['SMTP_AWS_K_ADI'] ?? 'YOUR_AWS_KEY');

// AWS SMTP secret key (optional)
define ('SMTP_AWS_SIFRE', $_ENV['SMTP_AWS_SIFRE'] ?? 'YOUR_AWS_SECRET');

// Default application timezone
define('DEFAULT_TIMEZONE', 'Europe/Istanbul');

// PHP error log file path
define('ERROR_LOG_FILE', ROOT . '/php_error.log');

// Custom process log file path
define('PROCESS_LOG_FILE', ROOT . '/php_process.log');
