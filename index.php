<?php

define('index_php','index');

file_exists('a_.php') ? include 'a_.php' : exit('.');

ob_start();

include R_PHP.'/index1.php';

$html = ob_get_clean();

$html = middle_dot_yc($html, ($so_->d('yabanci_dil') == 1 && Global_::$d != 'tr'), ['ui']);

echo $html;
?>