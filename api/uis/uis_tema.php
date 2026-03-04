<?php if (!defined('otoban')) exit('vad.');

if (!syetki([2])) {
    echo json_encode(['return' => 0, 'mesaj' => yc("Yetkisiz erişim.")]);
    exit;
}

$islem_tip = isset($_POST['islem_tip']) ? z($_POST['islem_tip']) : '';
$tema = isset($_POST['tema']) ? z($_POST['tema']) : '';

if (empty($tema) && $islem_tip !== 'list') {
    echo json_encode(['return' => 0, 'mesaj' => yc("Tema adı belirtilmedi.")]);
    exit;
}

$tema_yolu = TEMA_DIR . '/' . $tema;

switch ($islem_tip) {
    case 'activate':
        try {
            $stmt = $pdo->prepare("UPDATE {$do_}genel_ayarlar SET deger = :tema WHERE anahtar = 'tema'");
            $stmt->execute([':tema' => $tema]);
            echo json_encode(['return' => 1, 'mesaj' => yc("Tema başarıyla etkinleştirildi.")]);
        } catch (PDOException $e) {
            echo json_encode(['return' => 0, 'mesaj' => 'Hata:' . $e->getMessage()]);
        }
        break;

    case 'clone':
        $yeni_ad = isset($_POST['yeni_ad']) ? z($_POST['yeni_ad']) : '';
        if (empty($yeni_ad)) {
            echo json_encode(['return' => 0, 'mesaj' => yc("Yeni tema adı belirtilmedi.")]);
            exit;
        }
        $yeni_yol = TEMA_DIR . '/' . $yeni_ad;
        if (is_dir($yeni_yol)) {
            echo json_encode(['return' => 0, 'mesaj' => yc("Bu isimde bir tema zaten mevcut.")]);
            exit;
        }
        recursiveCopy($tema_yolu, $yeni_yol);
        echo json_encode(['return' => 1, 'mesaj' => yc("Tema başarıyla kopyalandı.")]);
        break;

    case 'delete':
        if ($tema === $so_->d('tema')) {
            echo json_encode(['return' => 0, 'mesaj' => yc("Aktif tema silinemez.")]);
            exit;
        }
        if (is_dir($tema_yolu)) {
            // Soft Delete Logic: Add flag to config.json
            $config_file = $tema_yolu . '/config.json';
            $config = [];
            if (file_exists($config_file)) {
                $content = file_get_contents($config_file);
                $config = json_decode($content, true) ?: [];
            }
            $config['deleted'] = true;
            $config['deleted_at'] = date('Y-m-d H:i:s');
            
            file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            $new_name = TEMA_DIR . '/.backups/.deleted_' . time() . '_' . $tema;
            rename($tema_yolu, $new_name);

            // recursiveDelete($tema_yolu); 

            echo json_encode(['return' => 1, 'mesaj' => yc("Tema başarıyla arşive kaldırıldı.")]);
        } else {
            echo json_encode(['return' => 0, 'mesaj' => yc("Tema bulunamadı.")]);
        }
        break;

    case 'yedek_listesi':
        $dosya = isset($_POST['dosya']) ? z($_POST['dosya']) : '';
        if (empty($dosya)) {
            echo json_encode(['return' => 0, 'mesaj' => yc("Dosya adı belirtilmedi.")]);
            exit;
        }
        $yedekler = tema_dosya_yedek_listesi($tema, $dosya);
        echo json_encode(['return' => 1, 'yedekler' => $yedekler]);
        break;

    case 'yedek_geri_yukle':
        $dosya = isset($_POST['dosya']) ? z($_POST['dosya']) : '';
        $tarih = isset($_POST['tarih']) ? z($_POST['tarih']) : '';
        if (empty($dosya) || empty($tarih)) {
            echo json_encode(['return' => 0, 'mesaj' => yc("Dosya adı veya yedek tarihi belirtilmedi.")]);
            exit;
        }
        if (tema_dosya_yedek_geri_yukle($tema, $dosya, $tarih)) {
            echo json_encode(['return' => 1, 'mesaj' => yc("Yedek başarıyla geri yüklendi.")]);
        } else {
            echo json_encode(['return' => 0, 'mesaj' => yc("Yedek geri yüklenirken bir hata oluştu.")]);
        }
        break;

    case 'yedek_sil':
        $dosya = isset($_POST['dosya']) ? z($_POST['dosya']) : '';
        $tarih = isset($_POST['tarih']) ? z($_POST['tarih']) : '';
        if (empty($dosya) || empty($tarih)) {
            echo json_encode(['return' => 0, 'mesaj' => yc("Dosya adı veya yedek tarihi belirtilmedi.")]);
            exit;
        }
        
        // Yedek dosya yolunu oluştur
        $yedek_dizin = TEMA_DIR . '/.backups/' . $tema . '/' . dirname($dosya);
        $dosya_adi_temiz = basename($dosya);
        $yedek_dosya = $yedek_dizin . '/' . $dosya_adi_temiz . '.' . $tarih . '.bak';
        
        if (dosya_sil($yedek_dosya)) {
            echo json_encode(['return' => 1, 'mesaj' => yc("Yedek dosyası silindi.")]);
        } else {
            echo json_encode(['return' => 0, 'mesaj' => yc("Yedek silinirken hata oluştu veya dosya bulunamadı.")]);
        }
        break;

    default:
        echo json_encode(['return' => 0, 'mesaj' => yc("Geçersiz işlem tipi.")]);
        break;
}
