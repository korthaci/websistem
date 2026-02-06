<?php
declare(strict_types=1);
if (!defined('otoban')) {exit('Erişim engellendi');}
header('Content-Type: text/html');

$_SESSION['mesaj_sayisi'] = ($_SESSION['mesaj_sayisi'] ?? 0) + 1;
if ($_SESSION['mesaj_sayisi'] > 15) {
    exit('Something wrong? Please restart browser and try again');
}
$adisoyadi    = trim(z($_POST['adisoyadi'] ?? ''));
$emailadresi  = trim(html_d($_POST['emailadresi'] ?? ''));
$telefon      = trim(z($_POST['telefon'] ?? ''));
$mesaj_raw    = trim(html_d($_POST['mesaj'] ?? ''));

$formbosmesaj = '';
$formbosmesaj .= (mb_strlen($adisoyadi) < 2) ? yc("Adınız Soyadınız") . ' ?<br>' : '';
$formbosmesaj .= (!filter_var($emailadresi, FILTER_VALIDATE_EMAIL)) ? yc("E Posta Adresiniz") . ' ?<br>' : '';
$formbosmesaj .= (mb_strlen($telefon) < 7) ? yc("Telefon") . ' ?<br>' : '';
$formbosmesaj .= (mb_strlen($mesaj_raw) < 4) ? yc("Mesajınız") . ' ?<br>' : '';
if ($formbosmesaj) {
    echo '<br>' . $formbosmesaj . '<br>! <b>' . yc("Mesaj gönderilemedi") . '</b>';
    exit;
}

$mesajbu = nl2br(htmlspecialchars($mesaj_raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

$to       = [$so_->d('email_adresi')];
$subject  = strtr($so_->d('site_adi') . ' mail, ' . $adisoyadi, $degistir2);
$body     = "<html><body><div>"
          . htmlspecialchars($adisoyadi, ENT_QUOTES | ENT_HTML5, 'UTF-8')
          . "<br>--------<br>"
          . yc("Telefon") . " : " . htmlspecialchars($telefon, ENT_QUOTES | ENT_HTML5, 'UTF-8')
          . "<br/>" . yc("Mail") . " : " . htmlspecialchars($emailadresi, ENT_QUOTES | ENT_HTML5, 'UTF-8')
          . "<br/>" . $mesajbu
          . "<br/>" . ip_()
          . "</div></body></html>";

$phpmailer->gonder($to, $subject, $body, $emailadresi, 'bcc');

if ($phpmailer->gonderildi) {
    echo '<br>&radic; <b style="color:#093">' . yc("Mesaj gönderildi") . '</b>';
}
