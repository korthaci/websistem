<?php

class isbankPOS {

	public $prod_test			= 'PROD';
	public $version				= 'v4.0';

	/*parametre değişkeni uygulama içinde değiştirilebilir.  */
	public $parametre			= [];
	public $site_url 			= 'https://';
	public $domain	 			= 'https://';
	public $clientid			= '*************';//700669830929
	public $clientid_test		= '700321123123';//700655000200//istest:700321123123
	public $storekey			= '**********';//MVDM546864
	public $storekey_test		= 'TestTest123.';//TRPS0200//istest:TestTest123.
    public $banka_sp_url		= 'https://sanalpos.isbank.com.tr/fim/est3Dgate';
	public $banka_sp_url_test	= 'https://istest.asseco-see.com.tr/fim/est3Dgate'; //https://entegrasyon.asseco-see.com.tr/fim/est3Dgate

	public $api_url 			= "https://sanalpos.isbank.com.tr/fim/api";
	public $api_url_test		= "https://istest.asseco-see.com.tr/fim/api";//https://entegrasyon.asseco-see.com.tr/fim/api //istest: https://istest.asseco-see.com.tr/fim/api

	public $b_sp_url_v3_gecis	= '/cc_gonder.php';
	public $localhost__			= false;

    public $oid					= ''; //siparis no
    public $okUrl				= '/cc/v3/ccv3.php?odemesonuc=1';
    public $failUrl				= '/cc/v3/ccv3.php?odemesonuc=0';
	public $callbackUrl			= '';
	public $rnd					= ''; //microtime();
	public $islemtipi			= 'Auth';
	public $storetype			= '3d_pay';
	public $lang				= 'tr';
	public $currency			= '949';
	public $hashAlgorithm		= 'ver3';
	public $taksit				= "";
	public $amount				= 0.00;

	public $debug				= true;
	public $debug_yaz			= '';
	public $debug_mail			= true;
	public $debug_mail_icerik	= '';
	public $aciklama 			= '';
	public $ErrMsg				= '';
	
	public $odeme_yapildi		= false;
	public $gecis_form_target   = '_self';
	//v4 ile eklendi
	public $session_order_id	= 'strOrderID';
	public $hash_kontrol		= true;
	public $hash_kontrol_log	= true;
	public $api_username		= '';
	public $api_password		= '';
	public $api_username_test	= '700669830929api';//'isbankapi';
	public $api_password_test	= 'TESTtest1234.';//'isbankapi2020';
	
	public $session_ajax = false;//ajax ile kullanımlarda, true olarak ayarlanmalı

	/*
	form_input_hidden Sadece gönderilen forma hidden inputlar eklemek için eklendi. (Parametre değişkeni ile ilgisi yok.) form input verisine eklenmeyen sabit değerler için kullanılır
	islemtipi => "islemtipi" : key name attr için. value parametre değerinin ismi. burda ikisi de aynı çünkü parametre isimlendirilirken name değerleri baz alındı. for_input_hidden fonksiyonunda şuna dönüşecek : <input type="hidden" name="islemtipi" value="Auth"/> $this->islemtipi=Auth. 
	Uygulama içinde şu şekilde değiştirmeler yapılabilir : 
	$pos->form_input_hidden['currency'] = '840'; daha sonra bunları text'e çevirecek fonksiyon :
	$pos->form_input_hidden();
	*/
	public $form_input_hidden	= [
		'islemtipi' => "islemtipi",
		'storetype' => "storetype",
		'lang' => 'lang',
		'currency' => 'currency',
		'hashAlgorithm' => 'hashAlgorithm',
	];

	//v4 ile eklendi
	/*
	gecis_form_key_sil: Geçiş formunda bankaya gönderilmeyecek POST anahtarları.
	Bu anahtarlar hem form inputlarına eklenmez hem de hash hesaplamasına dahil edilmez.
	*/
	public $gecis_form_key_sil = ['b', 'b_islem', '_get'];


	function __construct($site_url, $strMode) {
		$this->prod_test = $strMode;
		$this->site_url = $site_url;
		$this->domain = preg_replace('/https?:\/\/(www\.)?/', '', $this->site_url);
		$this->localhost__ = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);

		if ($strMode === 'TEST') {
            $this->banka_sp_url = $this->banka_sp_url_test;
            $this->clientid = $this->clientid_test;
			$this->storekey = $this->storekey_test;

			$this->api_url = $this->api_url_test;
			$this->api_username = $this->api_username_test;
			$this->api_password = $this->api_password_test;
		}
	}

	public function parametre_duzenle(){
		if (!empty($this->parametre)){

			foreach ($this->parametre as $pk => $pv) {
				if (property_exists($this, $pk)) {
					$this->{$pk} = $pv;
				}
			}

			if ($this->prod_test === 'TEST') {
				$this->clientid = $this->clientid_test;
				$this->storekey = $this->storekey_test;
			}

			// Ensure okUrl is absolute URL
			if (!empty($this->okUrl)) {
				if (strpos($this->okUrl, 'http') !== 0) {
					$this->okUrl = trim($this->site_url, "/") . '/'. trim($this->okUrl, "/");
				}
			}
			
			// Ensure failUrl is absolute URL
			if (!empty($this->failUrl)) {
				if (strpos($this->failUrl, 'http') !== 0) {
					$this->failUrl = trim($this->site_url, "/") . '/'. trim($this->failUrl, "/");
				}
			}
			
			// Ensure callbackUrl is absolute URL
			if (!empty($this->callbackUrl)) {
				if (strpos($this->callbackUrl, 'http') !== 0) {
					$this->callbackUrl = trim($this->site_url, "/") . '/'. trim($this->callbackUrl, "/");
				}
			}
		}
	}

	public function form_input_hidden() {
		$unique_inputs = [];
		
		foreach ($this->form_input_hidden as $k => $v) {
			if (!isset($unique_inputs[$k])) {
				$unique_inputs[$k] = '<input type="hidden" name="'.$k.'" value="'.$this->$v.'"/>';
			}
		}

		return implode("\n", $unique_inputs);
	}



	public function callback(){

		usleep(500000);

		// Tüm POST parametrelerini logla
		process_log("Callback POST parameters: " . json_encode($_POST, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL);

		if ($this->debug_mail){
			$parametreler_yaz = [];
			foreach ($_POST as $key1 => $value1){
				$parametreler_yaz[] = $key1 .':' . z($value1) . "\n";
			}			
			$this->debug_mail_icerik = $this->domain.' parametreler.' . $this->debug_yaz . "\n" . ( !empty($parametreler_yaz) ? implode($parametreler_yaz) : '' );
		}

		// Hash doğrulaması için verifyHash fonksiyonunu kullan
		$hashResult = $this->verifyHash($_POST);
		
		// v4 ile eklendi: Hash kontrol ayarı
		if (!$this->hash_kontrol) {
			if ($this->hash_kontrol_log) {
				process_log("⚠️ WARNING: Hash control disabled - proceeding without verification" . PHP_EOL);
			}
			$hashResult['valid'] = true;
		}
		
		process_log("Hash Validation: " . json_encode([
			'timestamp' => date('Y-m-d H:i:s'),
			'order_id' => isset($_POST['oid']) ? $_POST['oid'] : 'N/A',
			'calculated_hash' => $hashResult['calculated'],
			'retrieved_hash' => $hashResult['received'],
			'hash_match' => $hashResult['valid'],
			'hashval_string' => $hashResult['hashval_string'],
		]) . PHP_EOL);

		if ($hashResult['valid']) {
			$mdStatus = intval($_POST["mdStatus"]);
			$ProcReturnCode = isset($_POST["ProcReturnCode"]) ? $_POST["ProcReturnCode"] : '';
			$Response = isset($_POST["Response"]) ? z($_POST["Response"]) : '';

			$this->ErrMsg .= (isset($_POST["ErrMsg"])) ? z($_POST["ErrMsg"]) : '';
			process_log("ErrMsg: " . $this->ErrMsg . PHP_EOL);

			if (in_array($mdStatus, [1,2,3,4])) {
				if ($Response == "Approved" || $ProcReturnCode == "00") {
					$this->odeme_yapildi = true;

					usleep(100000);
					if ($this->session_ajax === false) {
						session_write_close();
					}

				} else {
					process_log("Payment Failed - Response: $Response, ProcReturnCode: $ProcReturnCode, mdStatus: $mdStatus, Order: " . (isset($_POST['oid']) ? $_POST['oid'] : 'N/A') . PHP_EOL);
				}
			} else {
				$this->ErrMsg .= $this->md_status_mesaj($mdStatus);

				process_log("Invalid mdStatus: $mdStatus, Order: " . (isset($_POST['oid']) ? $_POST['oid'] : 'N/A') . PHP_EOL);
			}
            
            process_log(json_encode([
                'timestamp' => date('Y-m-d H:i:s'),
                'order_id' => isset($_POST['oid']) ? $_POST['oid'] : '',
                'mdStatus' => $mdStatus,
                'ProcReturnCode' => $ProcReturnCode,
                'Response' => $Response,
                'payment_status' => $this->odeme_yapildi ? 'SUCCESS' : 'FAIL'
            ]) . PHP_EOL);
		} else {
			if ($this->hash_kontrol_log) {
				process_log("❌ Hash Validation Failed: " . json_encode([
					'timestamp' => date('Y-m-d H:i:s'),
					'order_id' => isset($_POST['oid']) ? $_POST['oid'] : 'N/A',
					'error' => 'Hash validation failed',
					'calculated_hash' => $hashResult['calculated'],
					'retrieved_hash' => $hashResult['received']
				]) . PHP_EOL);
			}
		}
	}

	public function odeme_yapildi(){
		return $this->odeme_yapildi;
	}

	//v4 ile eklendi
	/**
	 * Webhook'tan gelen POST verisini temizle ve normalize et
	 * @param array $postData Ham POST verisi
	 * @return array Temizlenmiş POST verisi
	 */
	public function normalizeWebhookData($postData) {
		foreach ($postData as $key => $value) {
			if (is_string($value)) {
				$postData[$key] = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
				$postData[$key] = trim($postData[$key]);
			}
		}
		
		// v4 ile eklendi: Gereksiz/boş parametreleri kaldır (İşbank test ortamında gelebiliyor)
		$removeIfEmpty = ['NATIONALIDNO'];
		foreach ($removeIfEmpty as $param) {
			if (isset($postData[$param]) && $postData[$param] === '') {
				unset($postData[$param]);
			}
		}
		
		return $postData;
	}

	public function verifyHash($postData, $storeKey = null) {
		if ($storeKey === null) {
			$storeKey = $this->storekey;
		}
		
		$hashval = '';
		$params = array_keys($postData);
		natcasesort($params);
		
		foreach($params as $param) {
			$lower = strtolower($param);
			
			if ($lower == "hash" || $lower == "encoding") {
				continue;
			}
			
			$value = $postData[$param];
			$value = is_string($value) ? trim($value) : $value;// v4 ile eklendi: Döküman harici ekleme
			$val = str_replace("|", "\\|", str_replace("\\", "\\\\", $value));
			$hashval .= $val . "|";
		}
		
		$escapedStoreKey = str_replace("|", "\\|", str_replace("\\", "\\\\", $storeKey));
		$hashval .= $escapedStoreKey;
		
		$calculatedHash = base64_encode(pack('H*', hash('sha512', $hashval)));
		
		return [
			'valid' => ($calculatedHash === ($postData['HASH'] ?? '')),
			'calculated' => $calculatedHash,
			'received' => $postData['HASH'] ?? '',
			'hashval_string' => $hashval
		];
	}

	function gecis_form_olustur_yaz($banka_sp_url, $storekey) {
		usleep(200000);
		
		$this->log_yaz("Form URLs - okUrl: " . (isset($_POST['okUrl']) ? $_POST['okUrl'] : 'N/A') . 
				  ", failUrl: " . (isset($_POST['failUrl']) ? $_POST['failUrl'] : 'N/A'));
		
		$yaz = '';
		$yaz .= '<form name="pay_form" method="post" action="'.$banka_sp_url.'" target="'.$this->gecis_form_target.'">';
	
		$postParams = array();
		foreach ($_POST as $key => $value){
			if (in_array($key, $this->gecis_form_key_sil)) {
				continue;
			}
			array_push($postParams, $key);
			$yaz .= "<input type=\"hidden\" name=\"" .$key ."\" value=\"" .$value."\" />\n";
		}
		
		natcasesort($postParams);
		
		$hashval = "";					
		foreach ($postParams as $param){				
			$paramValue = $_POST[$param];
			$escapedParamValue = str_replace("|", "\\|", str_replace("\\", "\\\\", $paramValue));	
				
			$lowerParam = strtolower($param);
			if($lowerParam != "hash" && $lowerParam != "encoding" )	{
				$hashval = $hashval . $escapedParamValue . "|";
			}
		}
		
		$escapedStoreKey = str_replace("|", "\\|", str_replace("\\", "\\\\", $storekey));
		$hashval = $hashval . $escapedStoreKey;
		
		$calculatedHashValue = hash('sha512', $hashval);
		$hash = base64_encode (pack('H*',$calculatedHashValue));
		
		$yaz .= "<input type=\"hidden\" name=\"HASH\" value=\"" .$hash."\" />\n";
			
		$yaz .= '</form>';

		if ($this->debug) {
			$this->log_yaz($yaz);
		}
		
		$yaz .= '<script>document.addEventListener(\'DOMContentLoaded\', function() {setTimeout(function() {document.pay_form.submit();}, 1600);});</script>';
		return $yaz;
	}

	//v4 ile eklendi
	private function log_yaz($mesaj) {
		$root = defined('ROOT') ? ROOT : __DIR__;
		$log_dosya = $root . '/php_banka.log';
		$tarih = date('Y-m-d H:i:s');
		$log_icerik = "[{$tarih}] {$mesaj}" . PHP_EOL;
		error_log($log_icerik, 3, $log_dosya);	
	}

	public function md_status_mesaj($mdstatus_no) {

		$mesajlar = [
			"0" => "3D doğrulama başarısız. İşlem reddedildi.",
			"1" => "3D doğrulama başarılı.",
			"2" => "Kart sahibi 3D Secure sistemine kayıtlı değil.",
			"3" => "Kart bankası 3D Secure sistemine kayıtlı değil.",
			"4" => "3D doğrulama denemesi başarısız.",
			"5" => "Sistem hatası nedeniyle doğrulama tamamlanamadı.",
			"6" => "3D Secure servis sağlayıcısında geçici hata oluştu.",
			"7" => "Hatalı doğrulama bilgisi (OTP yanlış veya süre doldu).",
			"8" => "Geçersiz doğrulama mesajı.",
			"9" => "Üye iş yeri doğrulamayı kabul etmedi."
		];

		if (!isset($mesajlar[$mdstatus_no])) {
			return "!mdStatus: " . $mdstatus_no;
		}

		return $mesajlar[$mdstatus_no];
	}


	//v4 ile eklendi
	/**
	 * İşbank Order Status Query (Ödeme / Provizyon durumu sorgulama)
	 *
	 * @param string $orderId   Sorgulanacak sipariş numarası
	 * @param string $username  API kullanıcı adı (opsiyonel, property'den alınır)
	 * @param string $password  API şifresi (opsiyonel, property'den alınır)
	 * @return array            Sorgu sonucu
	 *
	 * Örnek kullanım:
	 *   $result = $pos->order_sorgula(
	 *     "ORD20251031001",    // Sipariş (OrderId)
	 *     "apiuser_query",     // İşbank API kullanıcı adı (opsiyonel)
	 *     "apipassword"        // API şifresi (opsiyonel)
	 *   );
	 *   if ($result['status'] === 'success') { ... }
	 *
	 * DÖNEN ALANLAR:
	 *   status: 'success' | 'failed'
	 *   code:   '00'=başarılı, '05'=reddedildi, '99'=bulunamadı, diğer=bankadan hata
	 *   message: Banka açıklaması
	 *   chargeType: 'S'=Satış, 'P'=Ön Provizyon
	 *   transactionStatus: 'S'=Başarılı, 'V'=İptal, 'E'=Hata, 'P'=Beklemede
	 *   amount: TL cinsinden tutar
	 *   authCode: Banka onay kodu
	 *   trxDate: İşlem tarih-saat
	 *   raw: Ham XML yanıtı
	 */

	public function order_sorgula($orderId, $username = null, $password = null) {
		$username = $username ?? $this->api_username;
		$password = $password ?? $this->api_password;
		
		if (empty($username) || empty($password)) {
			return [
				'status' => 'failed',
				'message' => 'API credentials not provided',
				'code' => 'CREDENTIALS_ERROR'
			];
		}
		
		$url = $this->api_url;

		$xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<CC5Request>
	<Name>{$username}</Name>
	<Password>{$password}</Password>
	<ClientId>{$this->clientid}</ClientId>
	<OrderId>{$orderId}</OrderId>
	<Extra>
		<ORDERSTATUS>QUERY</ORDERSTATUS>
	</Extra>
</CC5Request>
XML;

		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => "DATA=" . urlencode($xml),
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYPEER => !$this->localhost__,
			CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
		]);

		$response = curl_exec($ch);
		$curlErr  = curl_error($ch);
		curl_close($ch);

		if ($response === false) {
			return [
				'status' => 'failed',
				'message' => 'cURL Error: ' . $curlErr,
			];
		}

		$xmlResponse = @simplexml_load_string($response);
		if (!$xmlResponse) {
			return [
				'status' => 'failed',
				'message' => 'XML Parse Error',
				'raw' => $response,
			];
		}

		$procCode  = (string)($xmlResponse->ProcReturnCode ?? '');
		$errMsg    = (string)($xmlResponse->ErrMsg ?? '');
		$chargeType = (string)($xmlResponse->Extra->CHARGE_TYPE_CD ?? '');
		$transStat  = (string)($xmlResponse->Extra->TRANS_STAT ?? '');
		$authCode   = (string)($xmlResponse->Extra->AUTH_CODE ?? '');
		$trxDate    = (string)($xmlResponse->Extra->AUTH_DTTM ?? '');
		$cap_amt = (string)($xmlResponse->Extra->CAPTURE_AMT ?? '');
		$org_amt = (string)($xmlResponse->Extra->ORIG_TRANS_AMT ?? '');
		$amountRaw = (!empty($cap_amt) && $cap_amt !== '0') ? $cap_amt : $org_amt;
		$amountTl   = number_format(((float)$amountRaw) / 100, 2, '.', '');

		$success = ($procCode === '00' && (strtoupper($transStat) === 'S' || strtoupper($transStat) === 'A'));

		return [
			'status'       => $success ? 'success' : 'failed',
			'code'         => $procCode,
			'message'      => $errMsg,
			'chargeType'   => $chargeType,
			'transactionStatus' => $transStat,
			'amount'       => $amountTl,
			'authCode'     => $authCode,
			'trxDate'      => $trxDate,
			'raw'          => $response,
		];
	}


	//v4 ile eklendi
	/**
	 * İşbank PostAuth (ön provizyon tamamlama) işlemi
	 *
	 * @param string $orderId   Daha önce alınan PreAuth orderId
	 * @param float  $amount    TL cinsinden tutar (örn: 1358.00)
	 * @param string $username  API kullanıcı adı (opsiyonel, property'den alınır)
	 * @param string $password  API şifresi (opsiyonel, property'den alınır)
	 * @return array            İşlem sonucu bilgileri
	 */


	public function postauth_yap($orderId, $amount, $username = null, $password = null) {
		$username = $username ?? $this->api_username;
		$password = $password ?? $this->api_password;
		
		if (empty($username) || empty($password)) {
			return [
				'status' => 'failed',
				'message' => 'API credentials not provided',
				'code' => 'CREDENTIALS_ERROR'
			];
		}
		
		$url = $this->api_url;

		// kuruş formatına çevir (örn: 1358.00 → 135800)
		$total = number_format((float)$amount, 2, '.', '');

		$xml = <<<XML
<CC5Request>
	<Name>{$username}</Name>
	<Password>{$password}</Password>
	<ClientId>{$this->clientid}</ClientId>
	<Type>PostAuth</Type>
	<OrderId>{$orderId}</OrderId>
	<Total>{$total}</Total>
	<Currency>949</Currency>
</CC5Request>
XML;

		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => "DATA=" . urlencode($xml),
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYPEER => !$this->localhost__,
		]);

		$response = curl_exec($ch);
		$curlErr = curl_error($ch);
		curl_close($ch);

		if ($response === false) {
			return [
				'status' => 'failed',
				'message' => 'cURL Error: ' . $curlErr,
			];
		}

		$resp = @simplexml_load_string($response);
		if ($resp === false) {
			return [
				'status' => 'failed',
				'message' => 'XML Parse Error',
				'raw' => $response,
			];
		}

		$procCode = (string)($resp->ProcReturnCode ?? '');
		$respText = (string)($resp->Response ?? '');
		$errMsg   = (string)($resp->ErrMsg ?? '');
		$authCode = (string)($resp->AuthCode ?? '');
		$transId  = (string)($resp->TransId ?? '');

		$success = ($procCode === "00" && strtoupper($respText) === "APPROVED");

		return [
			'status' => $success ? 'success' : 'failed',
			'code' => $procCode,
			'response' => $respText,
			'message' => $errMsg,
			'authCode' => $authCode,
			'transId' => $transId,
			'raw' => $response,
		];
	}



	public function iptal_yap($orderId, $username = null, $password = null) {
		$username = $username ?? $this->api_username;
		$password = $password ?? $this->api_password;

		if (empty($username) || empty($password)) {
			return [
				'status' => 'failed',
				'message' => 'API credentials not provided',
				'code' => 'CREDENTIALS_ERROR'
			];
		}

		$url = $this->api_url;

		$xml = <<<XML
	<CC5Request>
		<Name>{$username}</Name>
		<Password>{$password}</Password>
		<ClientId>{$this->clientid}</ClientId>
		<Type>Void</Type>
		<OrderId>{$orderId}</OrderId>
	</CC5Request>
	XML;

		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => "DATA=" . urlencode($xml),
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYPEER => !$this->localhost__
		]);

		$response = curl_exec($ch);
		$curlErr = curl_error($ch);
		curl_close($ch);

		if ($response === false) {
			return [
				'status' => 'failed',
				'message' => 'cURL Error: ' . $curlErr,
			];
		}

		$resp = @simplexml_load_string($response);
		if (!$resp) {
			return [
				'status' => 'failed',
				'message' => 'XML Parse Error',
				'raw' => $response,
			];
		}

		$proc = (string)($resp->ProcReturnCode ?? '');
		$text = (string)($resp->Response ?? '');
		$err = (string)($resp->ErrMsg ?? '');

		$success = ($proc === "00" && strtoupper($text) === "APPROVED");

		return [
			'status' => $success ? 'success' : 'failed',
			'code' => $proc,
			'response' => $text,
			'message' => $err,
			'raw' => $response
		];
	}



	public function iade_yap($orderId, $amount, $username = null, $password = null) {
		$username = $username ?? $this->api_username;
		$password = $password ?? $this->api_password;

		if (empty($username) || empty($password)) {
			return [
				'status' => 'failed',
				'message' => 'API credentials not provided',
				'code' => 'CREDENTIALS_ERROR'
			];
		}

		$url = $this->api_url;

		$total = number_format((float)$amount, 2, '.', '');

		$xml = <<<XML
	<CC5Request>
		<Name>{$username}</Name>
		<Password>{$password}</Password>
		<ClientId>{$this->clientid}</ClientId>
		<Type>Refund</Type>
		<OrderId>{$orderId}</OrderId>
		<Total>{$total}</Total>
		<Currency>949</Currency>
	</CC5Request>
	XML;

		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => "DATA=" . urlencode($xml),
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYPEER => !$this->localhost__
		]);

		$response = curl_exec($ch);
		$curlErr = curl_error($ch);
		curl_close($ch);

		if ($response === false) {
			return [
				'status' => 'failed',
				'message' => 'cURL Error: ' . $curlErr,
			];
		}

		$resp = @simplexml_load_string($response);
		if (!$resp) {
			return [
				'status' => 'failed',
				'message' => 'XML Parse Error',
				'raw' => $response,
			];
		}

		$proc = (string)($resp->ProcReturnCode ?? '');
		$text = (string)($resp->Response ?? '');
		$err = (string)($resp->ErrMsg ?? '');

		$success = ($proc === "00" && strtoupper($text) === "APPROVED");

		return [
			'status' => $success ? 'success' : 'failed',
			'code' => $proc,
			'response' => $text,
			'message' => $err,
			'raw' => $response
		];
	}


	
}


