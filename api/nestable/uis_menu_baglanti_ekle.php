<?php
if (!syetki([2])) {
	exit(json_encode(['return' => 0, 'mesaj' => 'Yetkisiz erişim.']));
}
if (!isset($_POST['mb_ekle_nn']) || !isset($_POST['mb_ekle_adi']) || !isset($_POST['mb_ekle_link']) ) {
	exit(json_encode(['return' => 0, 'mesaj' => '!mb']));
}

$menu_no = z($_POST['mb_ekle_nn']);
$menu_adi = z($_POST['mb_ekle_adi']);
$menu_link = html_d($_POST['mb_ekle_link']);

$response = ['return' => 0, 'mesaj' => 'İşlem başarısız.'];

try {
    if ($menu_no === 'yeni') {
        $insert_sql = "INSERT INTO {$do_}menu (tablo, adi, dis_link, sira) VALUES (:tablo, :adi, :dis_link, :sira)";
        $stmt = $pdo->prepare($insert_sql);
        $stmt->bindValue(':tablo', 'menu', PDO::PARAM_STR);
        $stmt->bindValue(':adi', $menu_adi, PDO::PARAM_STR);
        $stmt->bindValue(':dis_link', $menu_link, PDO::PARAM_STR);
        $stmt->bindValue(':sira', 20000, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $lastInsertedId = $pdo->lastInsertId();

            $update_sql = "UPDATE {$do_}menu SET tablo_no = :tablo_no WHERE no = :no";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->bindValue(':tablo_no', $lastInsertedId, PDO::PARAM_INT);
            $update_stmt->bindValue(':no', $lastInsertedId, PDO::PARAM_INT);
            
            if ($update_stmt->execute()) {
                $response = ['return' => 1, 'mesaj' => 'Menü bağlantısı başarıyla eklendi.', 'id' => $lastInsertedId];
            }
        }
    } elseif ((int)$menu_no != 0) {
        $update_sql = "UPDATE {$do_}menu SET adi = :adi, dis_link = :dis_link WHERE no = :no";
        $stmt = $pdo->prepare($update_sql);
        $stmt->bindValue(':adi', $menu_adi, PDO::PARAM_STR);
        $stmt->bindValue(':dis_link', $menu_link, PDO::PARAM_STR);
        $stmt->bindValue(':no', $menu_no, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $response = ['return' => 1, 'mesaj' => 'Menü bağlantısı başarıyla güncellendi.'];
        }
    }
} catch (PDOException $e) {
    $response = ['return' => 0, 'mesaj' => 'Veritabanı hatası: ' . $e->getMessage()];
}

echo json_encode($response);
