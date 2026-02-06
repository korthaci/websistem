<?php
if (isset($_POST['webunion']) && !empty($_POST['webunion'])) {
	$engellenen_ip_dosya = './engellenen_ip.txt';
	$existingIPs = file_exists($engellenen_ip_dosya) ? array_map(function($line) {
		return explode(' - ', $line)[0];
    }, file($engellenen_ip_dosya, FILE_IGNORE_NEW_LINES)) : [];

    if (in_array($_SERVER['REMOTE_ADDR'], $existingIPs)) {
		exit;
    } else {
		$logEntry = $_SERVER['REMOTE_ADDR'] . ' - ' . date('Y-m-d H:i:s') . PHP_EOL;
		file_put_contents($engellenen_ip_dosya, $logEntry, FILE_APPEND | LOCK_EX);
		exit;
	}
}

//uye giriş alanında webunion adında boş gitmesi gereken bir name="webunion" ayarlandı. webunion uydurma bir name alanı. form verilerini otomatik dolduran botlar için. eğer dolu gelirse ip engelleniyor.

/*
if (isset($_POST['webunion']) && !empty($_POST['webunion'])) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $dosya = './engellenen_ip.txt';
    $ips = file_exists($dosya) ? file($dosya, FILE_IGNORE_NEW_LINES) : [];
    
    if (!in_array($ip . ' - ', array_map(fn($l) => substr($l, 0, strlen($ip) + 3), $ips))) {
        file_put_contents($dosya, $ip . ' - ' . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    exit;
}
*/