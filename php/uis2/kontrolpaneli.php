<?php if ( ! defined('otoban')) exit('Vad.');

// İstatistikleri Çek
try {
    $total_pages = $pdo->query("SELECT COUNT(*) FROM {$do_}sayfa WHERE yayin=1")->fetchColumn();
    $total_modules = $pdo->query("SELECT COUNT(*) FROM {$do_}moduller WHERE yayin=1")->fetchColumn();
    $total_users = 0;
    
    // Üyeler tablosu varsa say
    $check_users = $pdo->query("SHOW TABLES LIKE '{$do_}uyeler'")->rowCount();
    if ($check_users > 0) {
        $total_users = $pdo->query("SELECT COUNT(*) FROM {$do_}uyeler WHERE yayin=1")->fetchColumn();
    }
    
    // Son logları çek
    $logs = [];
    $check_logs = $pdo->query("SHOW TABLES LIKE '{$do_}log_db'")->rowCount();
    if ($check_logs > 0) {
        $logs = $pdo->query("SELECT * FROM {$do_}log_db ORDER BY tarih DESC LIMIT 10")->fetchAll(PDO::FETCH_OBJ);
    }
    
} catch (PDOException $e) {
    // Hata durumunda sessiz kal veya logla
    $total_pages = 0;
    $total_modules = 0;
}

$dashboard_content = '
<div class="uis-container">
    <div class="uis-row">
        <div class="uis-col-12">
            <h3 class="uis-mb-2">'.yc("Kontrol Paneli").'</h3>
            <p class="uis-text-muted">'.yc("Hoşgeldiniz, sistem durumu ve hızlı işlemler.").'</p>
        </div>
    </div>

    <!-- İstatistik Kartları -->
    <div class="uis-row">
        <div class="uis-col-lg-3 uis-col-md-3 uis-mb-4">
            <div class="dashboard-card">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>'.number_format($total_pages).'</h3>
                        <p>'.yc("Aktif Sayfa").'</p>
                    </div>
                    <div class="stat-icon blue">
                        <i class="fa fa-file-alt"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="uis-col-lg-3 uis-col-md-3 uis-mb-4">
            <div class="dashboard-card">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>'.number_format($total_modules).'</h3>
                        <p>'.yc("Aktif Modül").'</p>
                    </div>
                    <div class="stat-icon purple">
                        <i class="fa fa-puzzle-piece"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="uis-col-lg-3 uis-col-md-3 uis-mb-4">
            <div class="dashboard-card">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>'.number_format($total_users).'</h3>
                        <p>'.yc("Kullanıcılar").'</p>
                    </div>
                    <div class="stat-icon green">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="uis-col-lg-3 uis-col-md-3 uis-mb-4">
            <div class="dashboard-card">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>v'.(defined('VERSION') ? VERSION : '1.0').'</h3>
                        <p>'.yc("Sistem Sürümü").'</p>
                    </div>
                    <div class="stat-icon orange">
                        ' . (syetki([2]) && is_file(R_PHP.'/_dev/update/sistem_guncelle.php') ? '<a href="ui/sistem_guncelle"><i class="fa fa-code-branch"></i></a>': '<i class="fa fa-code-branch"></i>') . '
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="uis-row">
        <!-- Hızlı İşlemler -->
        <div class="uis-col-lg-8 uis-mb-4">
            <div class="dashboard-card">
                <h5 class="uis-mb-4">'.yc("Hızlı İşlemler & Yönetim").'</h5>
                <div class="quick-actions">
                    <a href="ui/sayfalar" class="quick-btn">
                        <i class="fa fa-plus-circle text-primary"></i>
                        <span>'.yc("İçerik Yönetimi").'</span>
                    </a>
                    <a href="ui/moduller" class="quick-btn">
                        <i class="fa fa-cubes text-info"></i>
                        <span>'.yc("Modüller").'</span>
                    </a>
                    <a href="ui/ga" class="quick-btn">
                        <i class="fa fa-cogs text-secondary"></i>
                        <span>'.yc("Genel Ayarlar").'</span>
                    </a>
                    <a href="ui/dosyalar" class="quick-btn">
                        <i class="fa fa-folder-open text-warning"></i>
                        <span>'.yc("Dosya Yöneticisi").'</span>
                    </a>
                </div>

                <div class="">
                    <h5 class="uis-mb-3">'.yc("Son Aktiviteler").'</h5>';
                    
                    if (empty($logs)) {
                        $dashboard_content .= '<p class="uis-text-muted"><small>'.yc("Henüz kayıtlı aktivite yok.").'</small></p>';
                    } else {
                        $dashboard_content .= '<ul class="activity-list">';
                        foreach ($logs as $log) {
                            $dashboard_content .= '
                            <li class="activity-item">
                                <span class="activity-time">
                                    <i class="far fa-clock uis-me-1"></i> 
                                    '.date('d.m.Y H:i', strtotime($log->tarih)).'
                                </span>
                                <span class="activity-content">
                                    '.yc("<strong>%s</strong> tablosunda <em>%s</em> işlemi yapıldı.", htmlspecialchars($log->tablo_adi), htmlspecialchars($log->islem)).'
                                    <small class="uis-text-muted">(IP: '.$log->ip_adresi.')</small>
                                </span>
                            </li>';
                        }
                        $dashboard_content .= '</ul>';
                    }

            $dashboard_content .= '
                </div>
            </div>
        </div>

        <!-- Sistem Bilgileri -->
        <div class="uis-col-lg-4 uis-mb-4">
            <div class="dashboard-card">
                <h5 class="uis-mb-3">'.yc("Sistem Bilgileri").'</h5>
                <table class="uis-w-100 system-info-table uis-text-muted small">
                    <tr>
                        <td>'.yc("PHP Sürümü").'</td>
                        <td class="uis-text-end">'.phpversion().'</td>
                    </tr>
                    <tr>
                        <td>'.yc("Sunucu Yazılımı").'</td>
                        <td class="uis-text-end">'.($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown').'</td>
                    </tr>
                    <tr>
                        <td>'.yc("Veritabanı Host").'</td>
                        <td class="uis-text-end">'.DB_HOST.'</td>
                    </tr>
                    <tr>
                        <td>'.yc("Veritabanı Prefix").'</td>
                        <td class="uis-text-end">'.DB_PREFIX.'</td>
                    </tr>
                </table>
                <div class="">
                    <div class="uis-alert uis-alert-info uis-py-2 small">
                        <i class="fa fa-info-circle uis-me-1"></i>
                        '.yc("Sistem v%s sürümünde çalışmaktadır.", (defined('VERSION') ? VERSION : '1.0')).'
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

echo $dashboard_content;
