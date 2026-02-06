<?php if (! defined('otoban')) exit('Veriyolu açık değil.');

$giris_yapilamadi_yazi = '';

if (isset($_POST['gonder_uyegiris'])) {
    $limiter = new LoginRateLimiter($pdo, $do_, 15, 180);

    if (!$limiter->canAttempt()) {
        $remainingTime = $limiter->getRemainingTime();
        $attemptCount = $limiter->getAttemptCount();
        $minutes = floor($remainingTime / 60);
        $seconds = $remainingTime % 60;
        
        if ($minutes > 0) {
            $timeText = $minutes . " " . yc("dakika") . " " . $seconds . " " . yc("saniye");
        } else {
            $timeText = $seconds . " " . yc("saniye");
        }
        
        $giris_yapilamadi_yazi = yc("Çok fazla başarısız giriş denemesi") . " ({$attemptCount}). Lütfen {$timeText} sonra tekrar deneyin.";
    } else {
        $k_adi_form = (!empty($_POST['kullaniciadi'])) ? trim($_POST['kullaniciadi']) : '';
        $sifre_form = (!empty($_POST['sifre'])) ? trim($_POST['sifre']) : '';

        if (!empty($k_adi_form) && !empty($sifre_form)) {
            $stmt = $pdo->prepare("SELECT no, yetki_no, sifre FROM {$do_}uyeler WHERE k_adi = :kadi AND yayin = 1 LIMIT 1");
            $stmt->execute(['kadi' => $k_adi_form]);
            $uye = $stmt->fetch(PDO::FETCH_OBJ);

            if ($uye && password_verify($sifre_form, $uye->sifre)) {
                $_SESSION['kullanici_no'] = $uye->no;
                $_SESSION['kullanici_yetki_no'] = $uye->yetki_no;
                $_SESSION['giris_yapildi'] = true;                
                $stmt2 = $pdo->prepare("UPDATE {$do_}uyeler SET son_giris = :tarih WHERE no = :no");
                $stmt2->execute([
                    'tarih' => date("Y-m-d H:i:s"),
                    'no' => $uye->no
                ]);

                $limiter->recordAttempt(true);
            } else {
                $limiter->recordAttempt(false);
                $attemptCount = $limiter->getAttemptCount();
                $remainingAttempts = 15 - $attemptCount;
                
                if ($remainingAttempts > 0) {
                    $giris_yapilamadi_yazi = yc("Kullanıcı adı veya şifre yanlış.") . " " . yc("Kalan deneme hakkı:") . " {$remainingAttempts}";
                } else {
                    $giris_yapilamadi_yazi = yc("Kullanıcı adı veya şifre yanlış. Hesabınız 3 dakika süreyle engellenecek.");
                }
            }
        } else {
            $giris_yapilamadi_yazi = yc("Lütfen kullanıcı adı ve şifre girin.");
        }
    }
    echo '<div class="dyok" data-iziToast-mesaj="'.$giris_yapilamadi_yazi.'"></div>';
}

$giris__        = (isset($_SESSION['giris_yapildi']) && $_SESSION['giris_yapildi'] === true);
$u_no__         = (isset($_SESSION['kullanici_no']) && $_SESSION['kullanici_no'] >= 1) ? intval($_SESSION['kullanici_no']) : 0;
$u_yetki__      = (isset($_SESSION['kullanici_yetki_no']) && $u_no__ > 0) ? intval($_SESSION['kullanici_yetki_no']) : 0;

$row = pdo_fetch_obj($pdo, "SELECT * FROM {$do_}uyeler WHERE no = :no", ['no' => $u_no__]);
Global_::$u__b__ = new db_row_($row); //echo Global_::$u__b__->yaz('k_adi');
Global_::$u_no__ = $u_no__;
Global_::$u_yetki__ = $u_yetki__; // genel yetkiler

//define('GIRIS_RE', true);

if ($u_no__ > 0) {
	if (set_dolu('redirect_uri','g')) {
		$redirect_url = guvenli_url_decode($_GET['redirect_uri']);
		redirect_url_kontrol($redirect_url);
	} elseif (isset($_POST['ldevam'])) {
		$devam_getpost = guvenli_url_decode($_POST['ldevam']);
		redirect_url_kontrol($devam_getpost);
	}
}
