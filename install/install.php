<?php
/**
 * Websistem v1.0 - Easy Web Installer
 * This script downloads and extracts the full project zip from the remote server.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if files are already here
if (file_exists(__DIR__ . '/websistem.sql') || file_exists(__DIR__ . '/installed')) {
    header("Location: index.php");
    exit;
}

$remote_zip_url = 'https://n0n1.net/websistem/websistem.zip';
$local_zip_file = __DIR__ . '/websistem_temp.zip';
$extract_path = dirname(__DIR__) . '/'; // Parent directory (root)

// AJAX Handler
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'download') {
        try {
            // Check ZipArchive
            if (!class_exists('ZipArchive')) {
                throw new Exception('PHP ZipArchive eklentisi yüklü değil.');
            }

            // Download Zip
            $content = @file_get_contents($remote_zip_url);
            if ($content === false) {
                throw new Exception('Uzak sunucudan dosya indirilemedi. (URL: ' . $remote_zip_url . ')');
            }
            
            if (file_put_contents($local_zip_file, $content) === false) {
                throw new Exception('Zip dosyası sunucuya yazılamadı. Klasör izinlerini kontrol edin.');
            }

            echo json_encode(['status' => 'success', 'message' => 'Dosya başarıyla indirildi.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($_GET['action'] === 'extract') {
        try {
            if (!file_exists($local_zip_file)) {
                throw new Exception('İndirilen dosya bulunamadı.');
            }

            $zip = new ZipArchive;
            if ($zip->open($local_zip_file) === TRUE) {
                $zip->extractTo($extract_path);
                $zip->close();
                
                // Success - Clean up
                @unlink($local_zip_file);
                
                // Self-destruct flag for the final step
                // Note: We don't delete install.php here yet to avoid race condition with the response
                
                echo json_encode(['status' => 'success', 'message' => 'Dosyalar başarıyla çıkartıldı.']);
            } else {
                throw new Exception('Zip dosyası açılamadı.');
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($_GET['action'] === 'cleanup') {
        echo json_encode(['status' => 'success']);
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Websistem - Kolay Kurulum</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --success: #10b981;
            --error: #ef4444;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        .card {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            font-size: 28px;
            margin: 0 0 10px 0;
            color: var(--primary);
            font-weight: 800;
        }
        p {
            color: var(--text-light);
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }
        .btn:disabled {
            background-color: #cbd5e1;
            cursor: not-allowed;
            transform: none;
        }
        .status-box {
            margin-top: 25px;
            display: none;
        }
        .loader {
            width: 40px;
            height: 40px;
            border: 3px solid #e2e8f0;
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            display: inline-block;
            animation: spin 1s linear infinite;
            margin-bottom: 15px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .status-text {
            font-size: 14px;
            font-weight: 500;
        }
        .error-msg {
            color: var(--error);
            background: #fef2f2;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-top: 20px;
            display: none;
        }
        .success-icon {
            color: var(--success);
            font-size: 40px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card" id="main-card">
        <h1>Websistem v1.0</h1>
        <p>Sistemi otomatik olarak indirmek ve kurulumu başlatmak için butona tıklayın.</p>
        
        <button id="start-btn" class="btn">
            Sistemi Kurmayı Başlat
        </button>

        <div class="status-box" id="status-box">
            <div id="loader-area">
                <div class="loader"></div>
            </div>
            <div class="status-text" id="status-text">Bağlanılıyor...</div>
        </div>

        <div class="error-msg" id="error-msg"></div>
    </div>
</div>

<script>
    const startBtn = document.getElementById('start-btn');
    const statusBox = document.getElementById('status-box');
    const statusText = document.getElementById('status-text');
    const errorMsg = document.getElementById('error-msg');
    const loaderArea = document.getElementById('loader-area');

    startBtn.addEventListener('click', async () => {
        startBtn.style.display = 'none';
        statusBox.style.display = 'block';
        errorMsg.style.display = 'none';

        try {
            // STEP 1: Download
            statusText.innerText = 'Sistem dosyaları uzak sunucudan indiriliyor...';
            const downloadRes = await fetch('?action=download');
            const downloadData = await downloadRes.json();
            if (downloadData.status === 'error') throw new Exception(downloadData.message);

            // STEP 2: Extract
            statusText.innerText = 'Dosyalar dışa çıkartılıyor...';
            const extractRes = await fetch('?action=extract');
            const extractData = await extractRes.json();
            if (extractData.status === 'error') throw new Exception(extractData.message);

            // STEP 3: Cleanup and Redirect
            statusText.innerText = 'İşlem tamamlandı, yönlendiriliyorsunuz...';
            loaderArea.innerHTML = '<div class="success-icon">✓</div>';
            
            // Call cleanup just before redirect
            fetch('?action=cleanup');
            
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1500);

        } catch (err) {
            statusBox.style.display = 'none';
            startBtn.style.display = 'flex';
            errorMsg.innerText = 'Hata: ' + (err.message || 'Bir sorun oluştu.');
            errorMsg.style.display = 'block';
        }
    });

    function Exception(message) {
        this.message = message;
    }
</script>

</body>
</html>
