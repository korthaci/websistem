<?php if (! defined('otoban')) exit('vad.');

$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

echo json_encode(['return' => 1, 'mesaj' => 'Çıkış başarılı']);
?>