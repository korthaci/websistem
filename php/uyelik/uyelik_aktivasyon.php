<?php if (! defined('otoban')) exit('Veriyolu açık değil.');

echo '<h1 class="title is-3 mb-5">' . yc("Yeni üyelik aktivasyonu") . '</h1>';

$_SESSION['giris_yapildi'] = false;
$_SESSION['kullanici_no'] = 0;

$aktivasyon_kodu = isset($_GET['akodu']) ? trim($_GET['akodu']) : '';

if (strlen($aktivasyon_kodu) > 8) {
    try {
        $stmt = $pdo->prepare("SELECT no FROM {$do_}uyeler WHERE aktivasyon = :akodu AND yayin = 0 LIMIT 1");
        $stmt->execute(['akodu' => $aktivasyon_kodu]);
        $a_no = $stmt->fetchColumn();

        if ($a_no) {
            $stmt = $pdo->prepare("UPDATE {$do_}uyeler SET yayin = 1, aktivasyon = NULL WHERE no = :no");
            if ($stmt->execute(['no' => $a_no])) {
                echo '<div class="notification is-success">
                    <p>' . yc("Hesabınız başarıyla aktif edildi.") . '</p>
                    <p class="title is-4 mt-4">' . yc("Hoşgeldiniz") . '</p>
                    <p class="mt-4"><a href="uis/uyegiris" class="button is-success is-inverted">' . yc("Giriş yap") . '</a></p>
                </div>';
            } else {
                echo '<div class="notification is-danger">
                    <button class="delete"></button>
                    ' . yc("Hesabınız aktif edilemedi. Lütfen daha sonra tekrar deneyiniz.") . '
                </div>';
            }
        } else {
            echo '<div class="notification is-warning">
                <button class="delete"></button>
                ' . yc("Hesabınız aktif edilemedi veya daha önce aktifleştirilmiş.") . '
            </div>';
        }
    } catch (PDOException $e) {
        error_log("Activation error: " . $e->getMessage());
        echo '<div class="notification is-danger">
            <button class="delete"></button>
            ' . yc("Bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.") . '
        </div>';
    }
} else {
    echo '<div class="notification is-danger">
        <button class="delete"></button>
        ' . yc("Aktivasyon kodu bulunamadı. Hesabınız aktif edilemedi.") . '
    </div>';
}
?>