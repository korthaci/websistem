<?php if (!defined('otoban')) exit;

if (!syetki("2,3,4")) {
    echo json_encode(['return' => 0, 'mesaj' => yc("Yetkisiz erişim.")]);
    exit;
}

$response = ['return' => 0, 'mesaj' => yc("Bilinmeyen bir hata oluştu.")];

try {
    $n = 0;
    $tablo = '';
    $alan = '';

    if (isset($_POST['nta'])) {
        $nta = explode(",,", sifre_ac($_POST['nta']));
        $n = isset($nta[0]) ? intval($nta[0]) : 0;
        $tablo = isset($nta[1]) ? z($nta[1]) : '';
        $alan = isset($nta[2]) ? z($nta[2]) : '';
    } elseif (isset($_POST['n']) && isset($_POST['t']) && isset($_POST['a'])) {
        $n = is_numeric($_POST['n']) ? intval($_POST['n']) : ( !empty(trim($_POST['n'])) ? intval(sifre_ac($_POST['n'])) : 0 );
        $tablo = z($_POST['t']);
        $alan = z($_POST['a']);
    } else {
        error_log("uis_db01.php Hatası: Gerekli parametreler eksik (n, t, a veya nta). POST: " . print_r($_POST, true));
        $response['mesaj'] = yc("Gerekli bilgiler eksik.");
        echo json_encode($response);
        exit;
    }

    if ($n === 0 || empty($tablo) || empty($alan)) {
        error_log("uis_db01.php Hatası: Geçersiz veya eksik veri. n:{$n}, tablo:{$tablo}, alan:{$alan}");
        $response['mesaj'] = yc("Geçersiz veya eksik veri.");
        echo json_encode($response);
        exit;
    }

    $stmt_select = $pdo->prepare("SELECT $alan FROM {$do_}$tablo WHERE no = :n");
    $stmt_select->bindParam(':n', $n, PDO::PARAM_INT);
    $stmt_select->execute();
    $current_status = (int) $stmt_select->fetchColumn();

    $new_status = ($current_status === 1) ? 0 : 1;

    $stmt_update = $pdo->prepare("UPDATE {$do_}$tablo SET $alan = :new_status WHERE no = :n");
    $stmt_update->bindParam(':new_status', $new_status, PDO::PARAM_INT);
    $stmt_update->bindParam(':n', $n, PDO::PARAM_INT);
    $degistir = $stmt_update->execute();

    if ($degistir) {
        $response = ['return' => 1, 'mesaj' => yc("Seçenek başarıyla güncellendi.")];
    } else {
        $response = ['return' => 0, 'mesaj' => '!' . yc("Değişiklik yapılmadı.")];
    }

} catch (PDOException $e) {
    error_log("uis_db01.php PDO Hatası: " . $e->getMessage() . " SQL: " . ($stmt_update->queryString ?? 'N/A'));
    $response = ['return' => 0, 'mesaj' => yc("Veritabanı hatası.")];
} catch (Exception $e) {
    error_log("uis_db01.php Genel Hata: " . $e->getMessage());
    $response = ['return' => 0, 'mesaj' => yc("Bir hata oluştu.")];
}

echo json_encode($response);
