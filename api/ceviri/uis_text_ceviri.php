<?php

if (!syetki([2,3])) {
	echo json_encode(['return' => 0, 'mesaj' => 'Yetkisiz erişim.']);
}

$kaynak_dil = isset($_POST['kaynak_dil']) ? z($_POST['kaynak_dil']) : 'auto';
$hedef_dil = isset($_POST['hedef_dil']) ? z($_POST['hedef_dil']) : '';
$ceviri_text = isset($_POST['ceviri_text']) ? html_d($_POST['ceviri_text']) : '';

if (!empty($hedef_dil) && !empty($ceviri_text)) {
    $result = '';
    
    // DeepL
    $deepl_key = $_ENV['DEEPL_API_KEY'] ?? '';
    if (!empty($deepl_key)) {
        $translate_deepl = new deeplTranslator($deepl_key);
        $response = $translate_deepl->gonder($kaynak_dil, $hedef_dil, $ceviri_text);
        $result = $translate_deepl->getir($response);
        process_log("DeepL: " . $result);
    }
    
    // Microsoft
    if (empty($result)) {
        $ms_key = $_ENV['MICROSOFT_TRANSLATOR_KEY'] ?? '';
        $ms_region = $_ENV['MICROSOFT_TRANSLATOR_REGION'] ?? '';
        $translate = new microsoftTranslator($ms_key, $ms_region);
        $response = $translate->gonder($kaynak_dil, $hedef_dil, $ceviri_text);
        $result = $translate->getir($response);
        process_log("Microsoft Translator: " . $result);
    }

	// Google
	if (empty($result)) {
		$translate_g = new statickidzGoogleTranslate();
		$result = $translate_g->translate($kaynak_dil, $hedef_dil, $ceviri_text);
		process_log("Google Translate: " . $result);
	}
    
    echo json_encode(['return' => 1, 'mesaj' => $result]);
}