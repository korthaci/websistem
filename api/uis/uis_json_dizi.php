<?php if (!defined('otoban')) exit;

if (!syetki("2,3,4")) {
    echo json_encode(['return' => 0, 'mesaj' => 'Yetkisiz erişim.']);
    exit;
}
$response = ['return' => 0, 'mesaj' => 'Bilinmeyen bir hata oluştu.'];

if (isset($_POST['nta'])) {
    $nta = explode(",,", sifre_ac($_POST['nta']));
    $n = isset($nta[0]) ? intval($nta[0]) : 0;
    $tablo = isset($nta[1]) ? z($nta[1]) : '';
    $alan = isset($nta[2]) ? z($nta[2]) : '';
} elseif (isset($_POST['n']) && isset($_POST['t']) && isset($_POST['a'])) {
    $n = is_numeric($_POST['n']) ? intval($_POST['n']) : (!empty(trim($_POST['n'])) ? intval(sifre_ac($_POST['n'])) : 0);
    $tablo = z($_POST['t']);
    $alan = z($_POST['a']);
}

$deger = isset($_POST['deger']) ? $_POST['deger'] : '';

if ($n === 0 || empty($tablo) || empty($alan)) {
    $response['mesaj'] = 'Geçersiz veya eksik veri.';
    echo json_encode($response);
    exit;
}

$stmt_update = $pdo->prepare("UPDATE {$do_}$tablo SET $alan = :json_value WHERE no = :n");
$stmt_update->bindParam(':json_value', $deger, PDO::PARAM_STR);
$stmt_update->bindParam(':n', $n, PDO::PARAM_INT);
$degistir = $stmt_update->execute();

if ($degistir) {
    $response = ['return' => 1, 'mesaj' => 'JSON değeri başarıyla kaydedildi.'];
} else {
    $response = ['return' => 0, 'mesaj' => 'JSON değeri kaydedilemedi.'];
}

echo json_encode($response);