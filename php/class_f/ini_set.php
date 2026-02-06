<?php

if (!isset($_SERVER['HTTP_USER_AGENT'])) exit('hua');

header("Content-Type: text/html; charset=utf-8");
ini_set("default_charset", 'utf-8');
mb_internal_encoding("UTF-8");
date_default_timezone_set(DEFAULT_TIMEZONE);
ini_set('error_log', ERROR_LOG_FILE);
ini_set('log_errors', 1);