<?php if (!defined('otoban')) exit('vad.');

$aktif_tema = $so_->d('tema');
$tema_dizinleri = dizin_listesi(TEMA_DIR, ['.backups', '.sabitlenmis_tema', '*deleted*']);
usort($tema_dizinleri, function($a, $b) use ($aktif_tema) {
    if ($a === $aktif_tema) return -1;
    if ($b === $aktif_tema) return 1;
    return 0;
});
$tema_sayisi = count($tema_dizinleri);

$activated_msg = '';
if (isset($_GET['activated'])) {
    $activated_name = z($_GET['activated']);
    $activated_msg = '
    <div class="uis-alert uis-alert-s uis-flex uis-justify-between uis-align-center">
        <span><i class="fa-solid fa-circle-check"></i> <b>' . ucfirst($activated_name) . '</b> '.yc("teması başarıyla etkinleştirildi. Görünümün tam olarak oturması için blokları yapılandırmanız gerekebilir.").'</span>
        <a href="' . href('index', 'ui=temaduzenle&tema=' . $activated_name . '&recreate_notice=1') . '" class="uis-btn uis-btn-sm uis-btn-p">'.yc("Blokları Yapılandır").'</a>
    </div>';
}

echo '
<div class="uis-page-header">
    <h1 class="uis-page-title"><i class="fa-solid fa-palette"></i> '. yc("Tema Yönetimi").'</h1>
    <div class="uis-action-bar">
        <button class="uis-btn uis-btn-outline tema-tazele"><i class="fa-solid fa-sync"></i> '.yc("Listeyi Yenile").'</button>'.
        (!empty($_ENV['LC_API_KEY']) && !empty($_ENV['LC_VERIFY_KEY']) ? '<a href="ui/ai_studio" class="uis-btn uis-btn-sm uis-btn-p">'.yc("AI Studio").'</a>' : '').'
    </div>
</div>

' . $activated_msg . '

<div class="uis-row">';

foreach ($tema_dizinleri as $tema) {
    $config_file = TEMA_DIR . '/' . $tema . '/config.json';
    if (file_exists($config_file)) {
        $config_content = file_get_contents($config_file);
        $config_data = json_decode($config_content, true);
        if (isset($config_data['deleted']) && $config_data['deleted'] === true) {
            continue;
        }
    }

    $is_active = ($tema === $aktif_tema);
    
    $img_url = IMG . '/fg1.jpg';
    $extensions = ['jpg', 'png', 'webp'];
    foreach ($extensions as $ext) {
        $check_file = TEMA_DIR . '/' . $tema . '/screenshot.' . $ext;
        if (file_exists($check_file)) {
            $img_url = LOCAL . '/tema/' . $tema . '/screenshot.' . $ext;
            break;
        }
    }
    
    $tema_sayi_uis_md_stil_dizi = [1 => '12', 2 => '6', 3 => '4', 4 => '3'];
    $uis_col_md_stil = 'uis-col-md-' . ($tema_sayi_uis_md_stil_dizi[$tema_sayisi] ?? '3');
    
    $active_class = $is_active ? 'border-primary shadow-lg' : '';
    $active_badge = $is_active ? '<span class="uis-status-live"> 🎗️ <i class="fa-solid fa-circle-check"></i> '.yc("Aktif").'</span>' : '';

    echo '
    <div class="uis-col ' . $uis_col_md_stil .' uis-col-sm-12">
        <div class="uis-card uis-h-100 ' . $active_class . '" data-tema="' . $tema . '">
            <div class="tema-thumbnail">
                <img src="' . $img_url . '" alt="' . $tema . '">
            </div>
            <div class="uis-flex uis-justify-between uis-align-center uis-mb-3">
                <h3 class="tema-title">' . ucfirst($tema) . '</h3>
                ' . $active_badge . '
            </div>
            
            <div class="uis-flex uis-gap-1 uis-mt-4">
                ' . (!$is_active ? '<button class="uis-btn uis-btn-sm uis-btn-p tema-etkinlestir" data-tema="' . $tema . '">'.yc("Etkinleştir").'</button>' : '') . '
                <a href="' . href('index', 'ui=temaduzenle&tema=' . $tema) . '" class="uis-btn uis-btn-sm uis-btn-outline uis-btn-danger"'.(!$is_active ? 'data-confirm="Bu tema aktif tema değildir. Yine de düzenle?"':'').'>'.yc("Düzenle").'</a>
                <button class="uis-btn uis-btn-sm uis-btn-outline tema-kopyala" data-tema="' . $tema . '" title="'.yc("Kopyala").'"><i class="fa-solid fa-copy"></i></button>
                ' . (!$is_active ? '<button class="uis-btn uis-btn-sm uis-btn-outline uis-btn-d tema-sil" data-tema="' . $tema . '" title="'.yc("Sil").'"><i class="fa-solid fa-trash"></i></button>' : '') . '
            </div>
        </div>
    </div>';
}

echo '</div>';

echo '<script src="' . LOCAL . '/assets/js/uis2_theme.js" defer></script>';
