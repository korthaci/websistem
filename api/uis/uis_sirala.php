<?php if (!defined('otoban')) exit;

if (!isset($_POST['sira']) || !isset($_POST['stip'])) {
    error_log("Sıralama hatası: Gerekli parametreler eksik");
    echo json_encode(['return' => '0', 'mesaj' => yc("Gerekli parametreler eksik")]);
    exit;
}

$post_stip = z($_POST['stip']);
$sayi = 0;
$hata = false;

foreach ($_POST['sira'] as $sirabu => $eleman) {
    $sayi++;
    $sira = ($sirabu * 40) + 200;
    
    $sql = "UPDATE {$do_}{$post_stip} SET sira = :sira WHERE no = :no";
    $stmt = $pdo->prepare($sql);
    
    if (!$stmt->execute([':sira' => $sira, ':no' => $eleman])) {
        error_log("Sıralama hatası: " . implode(", ", $stmt->errorInfo()));
        $hata = true;
        break;
    }
}

if (!$hata && $sayi > 0) {
    echo json_encode(['return' => '1', 'mesaj' => yc("Sıralama güncellendi")]);
} else {
    echo json_encode(['return' => '0', 'mesaj' => yc("Sıralama güncellenirken bir hata oluştu")]);
}

exit;
