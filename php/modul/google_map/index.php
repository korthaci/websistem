<?php if (! defined('otoban')) exit('!vad');

$modul_gmap_sablon_yaz = new sablon_yaz;
$modul_gmap_sablon_yaz->dosya_icerik(__DIR__ . '/sablon/gmap.html');

$modul_gmap_sablon_yaz->vars = [
    '__degisken' => [
        'GOOGLE_MAP_KEY' => $so_->d('GOOGLE_MAP_KEY'),
    ],
    '__if' => [
        'script' => (int)1
    ]
];

echo $modul_gmap_sablon_yaz->render();

?>