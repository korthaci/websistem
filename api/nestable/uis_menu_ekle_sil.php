<?php
if (!syetki([2])) {
    exit(json_encode(['return' => 0, 'mesaj' => 'Yetkisiz erişim.']));
}

$return = ['return' => 0, 'mesaj' => ''];

if (isset($_POST['menu_degerler']) && isset($_POST['t'])) {
    $ekle_sayi = 0;
    $tablo = z($_POST['t']);

    $stmt = $pdo->prepare("INSERT INTO {$do_}menu (tablo, tablo_no, sira) VALUES (:tablo, :tablo_no, 20000)");

    foreach ($_POST['menu_degerler'] as $deger) {
        $deger = htmlspecialchars($deger, ENT_QUOTES, 'UTF-8');

        $stmt->bindValue(':tablo', $tablo, PDO::PARAM_STR);
        $stmt->bindValue(':tablo_no', $deger, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $ekle_sayi++;
        }
    }

    $return['return'] = 1;
    $return['mesaj'] = $ekle_sayi;

} elseif (isset($_POST['sil_n'])) {
    $sil_n = z($_POST['sil_n']);

    $stmt = $pdo->prepare("SELECT COUNT(no) FROM {$do_}menu WHERE ust_menu_no = :sil_n");
    $stmt->bindValue(':sil_n', $sil_n, PDO::PARAM_INT);
    $stmt->execute();
    $icindeki_menu_sayisi = (int) $stmt->fetchColumn();

    if ($icindeki_menu_sayisi < 1) {
        $stmt = $pdo->prepare("DELETE FROM {$do_}menu WHERE no = :sil_n");
        $stmt->bindValue(':sil_n', $sil_n, PDO::PARAM_INT);
        $sil = $stmt->execute();
    } else {
        $sil = false;
    }

    if ($sil) {
        $return['return'] = 1;
        $return['mesaj'] = 'Silindi';
    } else {
        $return['return'] = 0;
        $return['mesaj'] = 'Silinemedi';
    }
}

echo json_encode($return);
