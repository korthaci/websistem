<?php if (!defined('otoban')) exit('Veriyolu açık değil.');

$yeni_sifre_kosullari = $so_->d('yeni_sifre_tipi') ?: 'aaaa'; // aA1 olursa büyük küçük harf ve rakam en az 3 karakter olur. aaaa : en az 4 karakter, en az bir harf olmalı, isterse rakam veya sembol de olabilir.

$form_yaz = true;

echo '<h1 class="title is-3 mb-5">' . yc("Şifre değiştir") . '</h1>';

if (n0_($u_no__) && 
	set_dolu('simdiki_sifre', 'p') && 
	set_dolu('yeni_sifre', 'p') && set_dolu('yeni_sifre_tekrar', 'p')
	) {
    $simdiki_sifre = trim($_POST['simdiki_sifre']);
    $yeni_sifre = trim($_POST['yeni_sifre']);
    $yeni_sifre_tekrar = trim($_POST['yeni_sifre_tekrar']);

    $sifre_kontrol_sonuc = sifre_harf_kontrol($yeni_sifre, $yeni_sifre_kosullari);
    if ($sifre_kontrol_sonuc !== true) {
        echo '<div class="notification is-danger"><button class="delete"></button>' . $sifre_kontrol_sonuc . '</div>';
    } else {
        $stmt = $pdo->prepare("SELECT sifre FROM {$do_}uyeler WHERE no = :no");
        $stmt->execute(['no' => $u_no__]);
        $current_hash = $stmt->fetchColumn();

        if (!password_verify($simdiki_sifre, $current_hash)) {
            echo '<div class="notification is-danger"><button class="delete"></button>' . yc("Eski şifreyi yanlış girdiniz. Şifre değiştirilemedi.") . '</div>';
        } elseif ($yeni_sifre !== $yeni_sifre_tekrar) {
            echo '<div class="notification is-danger"><button class="delete"></button>' . yc("Yeni şifre alanları eşleşmiyor. Şifre değiştirilemedi.") . '</div>';
        } else {
            $new_password_hash = password_hash($yeni_sifre, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE {$do_}uyeler SET sifre = :sifre WHERE no = :no");
            $update_result = $stmt->execute([
                'sifre' => $new_password_hash,
                'no' => $u_no__
            ]);

            if ($update_result) {
                echo '<div class="notification is-success"><button class="delete"></button>' . yc("Şifre başarıyla değiştirildi.") . '</div>';
                $form_yaz = false;
            } else {
                echo '<div class="notification is-danger"><button class="delete"></button>' . yc("Şifre değiştirilemedi. Bir hata oluştu.") . '</div>';
            }
        }
    }
}

if ($form_yaz) {
    $stmt = $pdo->prepare("SELECT k_adi FROM {$do_}uyeler WHERE no = :no");
    $stmt->execute(['no' => $u_no__]);
    $s_kullanici_adi = $stmt->fetchColumn();

    echo '
    <div class="box">
        <form name="sifredegistir" action="' . href('index', 'uis=sifredegistir') . '" method="POST">
            <div class="field">
                <label class="label">' . yc("Kullanıcı adı") . '</label>
                <div class="control">
                    <input type="text" name="kullaniciadi" value="' . hs($s_kullanici_adi) . '" class="input" readonly />
                </div>
            </div>
            <div class="field">
                <label class="label">' . yc("Eski şifre") . '</label>
                <div class="control">
                    <input type="password" name="simdiki_sifre" class="input" required />
                </div>
            </div>
            <div class="field">
                <label class="label">' . yc("Yeni şifre") . '</label>
                <div class="control">
                    <input type="password" name="yeni_sifre" class="input" required />
                </div>
            </div>
            <div class="field">
                <label class="label">' . yc("Yeni şifre tekrar") . '</label>
                <div class="control">
                    <input type="password" name="yeni_sifre_tekrar" class="input" required />
                </div>
            </div>
            <div class="field">
                <div class="control">
                    <button type="submit" name="kaydet" class="button is-primary">' . yc("Kaydet") . '</button>
                </div>
            </div>
        </form>
    </div>';
}
?>