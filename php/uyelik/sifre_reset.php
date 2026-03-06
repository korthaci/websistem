<?php if (! defined('otoban')) exit('Veriyolu açık değil.');

$sifre_sifirlama_baglanti_saat = 2;
echo '<h2>' . yc("Şifre Sıfırla") . '</h2>';

$form_yaz = true;

if (isset($_POST['gonder_sifre_reset'])) {
    $girdi = trim($_POST['kullaniciadi_email']);

    try {
        $stmt = $pdo->prepare("SELECT no, adi, email, k_adi FROM {$do_}uyeler WHERE k_adi = ? OR email = ?");
        $stmt->execute([$girdi, $girdi]);
        $uye = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$uye) {
            echo yc("Kullanıcı bulunamadı.");
        } else {
            $token = bin2hex(random_bytes(50));
            $expire = date('Y-m-d H:i:s', strtotime('+'.$sifre_sifirlama_baglanti_saat.' hours'));
            
            $stmt = $pdo->prepare("UPDATE {$do_}uyeler SET sifre_reset_token = ?, sifre_reset_token_expire = ? WHERE no = ?");
            $stmt->execute([$token, $expire, $uye->no]);

            $yenile_linki = LOCAL . '/uis/sifreyenile?token=' . $token;

            $body = '<html><body>
                        <h3>' . yc("Şifrenizi sıfırlamak için aşağıdaki linke tıklayın") . ':</h3>
                        <p><a href="'.$yenile_linki.'">' . yc("Şifremi Sıfırla") . '</a></p>
                        <p>' . yc("Bağlantı %s saat geçerlidir.", $sifre_sifirlama_baglanti_saat) . '</p>
                      </body></html>';

            $subject = yc("Şifre Sıfırlama") . ' - ' . $so_->d('site_adi');

            Global_::$phpmailer->gonder([$uye->email], $subject, $body, SMTP_K_ADI, 'bcc');

            if (Global_::$phpmailer->gonderildi === true) {
                echo yc("E-posta adresinize şifre sıfırlama bağlantısı gönderildi.");
                $form_yaz = false;
            } else {
                echo yc("Mail gönderilirken bir hata oluştu.");
            }
        }
    } catch (Exception $e) {
        echo yc("Bir hata oluştu").'.';
        error_log("!reset : " . $e->getMessage());
    }
}

echo ($form_yaz ? '
<div class="ug-field">
    <label class="ug-label">'.yc("Kullanıcı adı veya E-posta").'</label>
    <div class="ug-control">
        <input type="text" name="kullaniciadi_email" required class="ug-input g_99__" placeholder="' . yc("E-posta veya kullanıcı adı girin") . '" />
    </div>
</div>
<div class="ug-field">
    <div class="ug-control">
        <button type="submit" name="gonder_sifre_reset" class="ug-btn ug-btn--primary">
            <i class="fas fa-envelope"></i> '.yc("Gönder").'
        </button>
    </div>
</div>' : '');