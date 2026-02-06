<?php if (! defined('otoban')) exit('Veriyolu açık değil.');

echo '<h2 class="title is-4">' . yc("Yeni Şifre Belirle") . '</h2>';

$token = isset($_GET['token']) ? trim($_GET['token']) : null;

if (!$token) {
    echo "<div class='notification is-danger'>" . yc("Geçersiz istek.") . "</div>";
    return;
}

try {
    $stmt = $pdo->prepare("SELECT no FROM {$do_}uyeler WHERE sifre_reset_token = ? AND sifre_reset_token_expire > NOW()");
    $stmt->execute([$token]);
    $uye = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$uye) {
        echo "<div class='notification is-danger'>" . yc("Geçersiz veya süresi dolmuş token.") . "</div>";
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $yeni_sifre = password_hash($_POST['yeni_sifre'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE {$do_}uyeler SET sifre = ?, sifre_reset_token = NULL, sifre_reset_token_expire = NULL WHERE no = ?");
        $stmt->execute([$yeni_sifre, $uye->no]);

        echo "<div class='notification is-success'>" . yc("Şifreniz başarıyla değiştirildi.") . "</div>";
        return;
    }
} catch (PDOException $e) {
    echo "<div class='notification is-danger'>" . yc("Bir hata oluştu") . ": " . hs($e->getMessage()) . "</div>";
    return;
}

$sifre_min_chars = 6;
echo '
<form method="post">
    <div class="field">
        <label class="label">' . yc("Yeni Şifre") . '</label>
        <div class="control has-icons-left has-icons-right">
            <input type="password" name="yeni_sifre" id="yeni_sifre"
                   required minlength="' . $sifre_min_chars . '"
                   class="input is-medium"
                   placeholder="' . yc("Yeni şifre girin") . '" />
            <span class="icon is-small is-left">
                <i class="fas fa-lock"></i>
            </span>
            <span class="icon is-small is-right toggle-password zindex_10" data-action="toggle-password">
                <i class="fas fa-eye"></i>
            </span>
        </div>
        <p class="help">' . yc("En az %s karakter olmalı", $sifre_min_chars) . '</p>
    </div>

    <div class="field">
        <div class="control">
            <button type="submit" class="button is-success is-fullwidth is-medium">
                <span class="icon"><i class="fas fa-save"></i></span>
                <span>' . yc("Şifreyi Güncelle") . '</span>
            </button>
        </div>
    </div>
</form>
';
?>