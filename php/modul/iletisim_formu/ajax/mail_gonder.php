<?php
if (!defined('otoban')) {exit('Erişim engellendi');}
header('Content-Type: text/html');

$_SESSION['mesaj_sayisi'] = ($_SESSION['mesaj_sayisi'] ?? 0) + 1;
if ($_SESSION['mesaj_sayisi'] > 15) {
    exit('Something wrong? Please restart browser and try again');
}
$adisoyadi    = trim(strip_tags($_POST['adisoyadi'] ?? ''));
$emailadresi  = filter_var(trim($_POST['emailadresi'] ?? ''), FILTER_SANITIZE_EMAIL);
$telefon      = trim(strip_tags($_POST['telefon'] ?? ''));
$mesaj_raw    = trim(strip_tags($_POST['mesaj'] ?? ''));

$formbosmesaj = '';
$formbosmesaj .= (mb_strlen($adisoyadi) < 2) ? yc("Adınız Soyadınız") . ' ?<br>' : '';
$formbosmesaj .= (!filter_var($emailadresi, FILTER_VALIDATE_EMAIL)) ? yc("E Posta Adresiniz") . ' ?<br>' : '';
$formbosmesaj .= (mb_strlen($telefon) < 7) ? yc("Telefon") . ' ?<br>' : '';
$formbosmesaj .= (mb_strlen($mesaj_raw) < 4) ? yc("Mesajınız") . ' ?<br>' : '';
if ($formbosmesaj) {
    echo '<br>' . $formbosmesaj . '<br>!' . yc("Mesaj gönderilemedi");
    exit;
}

$mesajbu = nl2br(htmlspecialchars($mesaj_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

$to       = [$so_->d('email_adresi')];
$subject  = $so_->d('site_adi') . ' mail, ' . $adisoyadi;
$body     = "<html><body><div>"
          . htmlspecialchars($adisoyadi, ENT_QUOTES | ENT_HTML5, 'UTF-8')
          . "<br>--------<br>"
          . yc("Telefon") . " : " . htmlspecialchars($telefon, ENT_QUOTES | ENT_HTML5, 'UTF-8')
          . "<br/>" . yc("Mail") . " : " . htmlspecialchars($emailadresi, ENT_QUOTES | ENT_HTML5, 'UTF-8')
          . "<br/>" . $mesajbu
          . "<br/>" . $_SERVER['REMOTE_ADDR']
          . "</div></body></html>";

Global_::$phpmailer->gonder($to, $subject, $body, $emailadresi, 'bcc');

if (Global_::$phpmailer->gonderildi) {
    echo '<br>✓ ' . yc("Mesaj gönderildi");
}
