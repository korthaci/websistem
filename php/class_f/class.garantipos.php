<?php

class GarantiPos {

	public $sanal_pos_url, $strHostAddress, $strXML, $results, $strReasonCodeValue;
	public $parametre				= [];
	public $sanal_pos_url_prod		= 'https://sanalposprov.garanti.com.tr/servlet/gt3dengine';
	//public $sanal_pos_url_test		= 'https://sanalposprovtest.garanti.com.tr/servlet/gt3dengine';
	public $sanal_pos_url_test		= 'https://sanalposprovtest.garantibbva.com.tr/servlet/gt3dengine';
	public $strTerminalUserID		= '********';
	public $strcustomeremailaddress	= '*****@*****.**';
	public $strTerminalID			= '30691297'; //TerminalID
	public $strTerminalID_			= '030691297'; //TerminalID başına 0 eklenerek 9 digite tamamlanmalıdır.
	public $strTerminalMerchantID	= '1234567'; //Üye İşyeri Numarası
	public $strStoreKey				= '12345678901234567890123456789012********'; //3D Secure şifresi key
	public $strProvisionPassword	= '123qweASD/'; //3D Secure provizyon şifresi
	public $strHostAddress_prod		= 'https://sanalposprov.garanti.com.tr/VPServlet';
	//public $strHostAddress_test		= 'https://sanalposprovtest.garanti.com.tr/VPServlet';
	public $strHostAddress_test		= 'https://sanalposprovtest.garantibbva.com.tr/VPServlet';

	public $site_url				= 'https://siteurl';
	
	public $strMode					= 'PROD';
	public $strApiVersion			= "v0.01";
	public $strTerminalProvUserID	= "PROVAUT";
	public $strType					= "sales";
	public $strAmount				= 0.00;
	public $strCurrencyCode			= "949"; //usd:840, euro:978
	public $strInstallmentCount		= ""; //Taksit Sayısı. Boş gönderilirse taksit yapılmaz
	public $strOrderID				= '';
	public $strCustomeripaddress	= '';
	public $strSuccessURL			= '/cc1/?o=1';
	public $strErrorURL				= '/cc1/?o=0';
	public $SecurityData			= '';
	public $HashData				= '';
	public $secure3dsecuritylevel	= '3D';

	private $odeme_yapildi			= false;
	public $aciklama				= '';

	public $form_input_hidden		= [
		'secure3dsecuritylevel' => 'secure3dsecuritylevel',
		'mode' => 'strMode',
		'apiversion' => 'strApiVersion',
		'terminalprovuserid' => 'strTerminalProvUserID',
		'terminaluserid' => 'strTerminalUserID',
		'terminalmerchantid' => 'strTerminalMerchantID',
		'txntype' => 'strType',
		'txnamount' => 'strAmount',
		'txncurrencycode' => 'strCurrencyCode',
		'txninstallmentcount' => 'strInstallmentCount',
		'orderid' => 'strOrderID',
		'terminalid' => 'strTerminalID',
		'successurl' => 'strSuccessURL',
		'errorurl' => 'strErrorURL',
		'customeremailaddress' => 'strcustomeremailaddress',
		'customeripaddress' => 'strCustomeripaddress',
		'secure3dhash' => 'HashData',
	];

	public $md_status_aciklama		= array(
		"0"=>"Doğrulama başarısız, 3-d Secure imzası geçersiz.",
		"1"=>"Tam doğrulama",
		"2"=>"Kart sahibi veya bankası sisteme kayıtlı değil",
		"3"=>"Banka sisteme kayıtlı değil",
		"4"=>"Doğrulama denemesi, kart sahibi sisteme daha sonra kayıt olmayı seçmiş",
		"5"=>"Doğrulama yapılamıyor",
		"6"=>"-",
		"7"=>"Sistem hatası",
		"8"=>"Bilinmeyen kart numarası"
	);

	function __construct($site_url, $strMode) {
		$this->site_url = $site_url;
		$this->strMode = $strMode;
		$this->strCustomeripaddress = $this->getIP();

		$this->parametre_test_kontrol();
	}

	public function parametre_test_kontrol() {

		if ($this->strMode === 'TEST') {

			$this->sanal_pos_url =  $this->sanal_pos_url_test;
			$this->strHostAddress = $this->strHostAddress_test;

			$this->strTerminalUserID = 'GARANTI';
			$this->strcustomeremailaddress = 'eticaret@garanti.com.tr';
			$this->strTerminalID = '30691297';
			$this->strTerminalMerchantID = '7000679';
			$this->strStoreKey = '12345678';
			$this->strProvisionPassword = '123qweASD/';
		}

	}
	
	public function parametre_duzenle_0(){
		
		$this->parametre_test_kontrol();

		if (!empty($this->parametre)){

			foreach ($this->parametre as $pk => $pv) {
				if (property_exists($this, $pk)) {
					$this->{$pk} = $pv;
				}
			}
/*
			$this->sanal_pos_url = $this->strMode === 'TEST' ? $this->sanal_pos_url_test : $this->sanal_pos_url_prod;
			$this->strHostAddress = $this->strMode === 'TEST' ? $this->strHostAddress_test : $this->strHostAddress_prod;
*/
			$this->strTerminalID = ltrim($this->strTerminalID,"0");
			$this->strTerminalID_ = str_pad($this->strTerminalID, 9, "0", STR_PAD_LEFT);
			$this->site_url = trim($this->site_url, '/');
			$this->strSuccessURL = strpos($this->strSuccessURL, $this->site_url) !== false ? $this->strSuccessURL : $this->site_url . $this->strSuccessURL;//lo0cal linkin https başlığı olup olmadığını kontrol ediyor. tamamen dış link verilmesi durumunu da ekle
			$this->strErrorURL = strpos($this->strErrorURL, $this->site_url) !== false ? $this->strErrorURL : $this->site_url . $this->strErrorURL;//

			if (array_key_exists('taksit', $this->parametre)) {
				$this->strInstallmentCount = isset($this->parametre['taksit']) ? (int) $this->parametre['taksit'] : (int) $this->strInstallmentCount;
			}
			$this->strInstallmentCount = $this->strInstallmentCount > 1 ? $this->strInstallmentCount : "";

			$this->SecurityData = strtoupper(sha1($this->strProvisionPassword.$this->strTerminalID_));
			$this->HashData = strtoupper(
				sha1($this->strTerminalID.$this->strOrderID.$this->strAmount.$this->strSuccessURL.$this->strErrorURL.$this->strType.$this->strInstallmentCount.$this->strStoreKey.$this->SecurityData)
			);
			
			// Form gönderimi öncesi hash hesaplama kontrolü
			$hashLogData = [
				'SecurityData_Input' => $this->strProvisionPassword . $this->strTerminalID_,
				'SecurityData_Hash' => $this->SecurityData,
				'HashData_Input' => $this->strTerminalID . $this->strOrderID . $this->strAmount . $this->strSuccessURL . $this->strErrorURL . $this->strType . $this->strInstallmentCount . $this->strStoreKey . $this->SecurityData,
				'HashData_Hash' => $this->HashData,
				'SecurityData_Length' => strlen($this->SecurityData),
				'HashData_Length' => strlen($this->HashData),
				'SecurityData_IsUppercase' => $this->SecurityData === strtoupper($this->SecurityData) ? 'EVET' : 'HAYIR',
				'HashData_IsUppercase' => $this->HashData === strtoupper($this->HashData) ? 'EVET' : 'HAYIR'
			];
			process_log('GarantiPOS Hash Hesaplama (Form Hazırlama): ' . print_r($hashLogData, true));
		}
	}


	public function parametre_duzenle(){ ////////////////////////////////////////////////////////////

		$this->parametre_test_kontrol();

		if (!empty($this->parametre)) {

			foreach ($this->parametre as $pk => $pv) {
				//if (property_exists($this, $pk)) {
				if (property_exists($this, $pk) && !empty($pv)) {
					$this->{$pk} = $pv;
				}
			}

			// Terminal ID düzenleme
			$this->strTerminalID = ltrim($this->strTerminalID, "0");
			$this->strTerminalID_ = str_pad($this->strTerminalID, 9, "0", STR_PAD_LEFT);

			// URL trim
			$this->site_url = trim($this->site_url, '/');
			$this->strSuccessURL = strpos($this->strSuccessURL, $this->site_url) !== false 
				? $this->strSuccessURL 
				: $this->site_url . $this->strSuccessURL;

			$this->strErrorURL = strpos($this->strErrorURL, $this->site_url) !== false 
				? $this->strErrorURL 
				: $this->site_url . $this->strErrorURL;

			// Taksit düzenleme
			if (array_key_exists('taksit', $this->parametre)) {
				$this->strInstallmentCount = isset($this->parametre['taksit']) 
					? (int) $this->parametre['taksit'] 
					: (int) $this->strInstallmentCount;
			}
			$this->strInstallmentCount = $this->strInstallmentCount > 1 ? $this->strInstallmentCount : "";

			/*
			*************************************
			*  🔥 ÖNEMLİ DÜZELTME - HASH FORMÜLÜ
			*  Form Hash = SHA1(TID + OID + Amount + SuccessURL + ErrorURL + StoreKey)
			*  SecurityData ve diğer alanlar dahil edilmez!
			*************************************
			*/

			$hash_input = 
				$this->strTerminalID .
				$this->strOrderID .
				$this->strAmount .
				$this->strSuccessURL .
				$this->strErrorURL .
				$this->strType .
				$this->strInstallmentCount .
				$this->strStoreKey;

			// Hash hesaplama (Standard Garanti 3D)
			$this->SecurityData = strtoupper(sha1($this->strProvisionPassword . $this->strTerminalID_));
			
			$hash_input = 
				$this->strTerminalID .
				$this->strOrderID .
				$this->strAmount .
				$this->strSuccessURL .
				$this->strErrorURL .
				$this->strType .
				$this->strInstallmentCount .
				$this->strStoreKey .
				$this->SecurityData;

			$this->HashData = strtoupper(sha1($hash_input));

			// DEBUG LOG
			$hashLogData = [
				'HashData_Input' => $hash_input,
				'HashData_Hash'  => $this->HashData,
				'SecurityData_Input' => $this->strProvisionPassword . $this->strTerminalID_,
				'SecurityData_Hash'  => $this->SecurityData
			];
			process_log('GarantiPOS Hash Hesaplama (Form Hazırlama - FIX): ' . print_r($hashLogData, true));
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

	public function callback($strPpassword = '') {
		try {
			process_log("GarantiPOS Callback Başladı. POST: " . print_r($_POST, true));

			if (!empty($strPpassword)) {
				$this->strProvisionPassword = $strPpassword;
			}
			
			// Yanıt kontrolü
			if (!isset($_POST["mdstatus"])) {
				$this->odeme_yapildi = false;
				$this->aciklama = "Banka yanıtı alınamadı. mdstatus değeri bulunamadı.";
				process_log("GarantiPOS Hata: " . $this->aciklama);
				return;
			}
			
			$strMDStatus = intval($_POST["mdstatus"]);
			process_log("GarantiPOS MDStatus: " . $strMDStatus . " | Açıklama=" . ($this->md_status_aciklama["$strMDStatus"] ?? 'bilinmiyor'));
			$this->logBankHashCheck();

			if ($strMDStatus == 1) {
				if (!isset($_POST['clientid']) || !isset($_POST['terminalprovuserid'])) {
					$this->odeme_yapildi = false;
					$this->aciklama = "Eksik banka yanıtı, gerekli alanlar bulunamadı.";
					return;
				}

				$strTerminalID				= html_d($_POST['clientid']);
				$strTerminalID_				= str_pad($strTerminalID, 9, "0", STR_PAD_LEFT);
				$strProvUserID				= html_d($_POST['terminalprovuserid']);
				$strUserID					= html_d($_POST['terminaluserid']);
				$strMerchantID				= html_d($_POST['terminalmerchantid']);
				$strIPAddress				= html_d($_POST['customeripaddress']);
				$strEmailAddress			= html_d($_POST['customeremailaddress']);
				$strOrderID					= html_d($_POST['orderid']);
				$strNumber					= ""; //Kart bilgilerinin boş gitmesi gerekiyor
				$strExpireDate				= ""; //Kart bilgilerinin boş gitmesi gerekiyor
				$strCVV2					= ""; //Kart bilgilerinin boş gitmesi gerekiyor
				$strAmount					= html_d($_POST['txnamount']);
				$strCurrencyCode			= html_d($_POST['txncurrencycode']);
				$strInstallmentCount		= html_d($_POST['txninstallmentcount']);
				$strCardholderPresentCode	= "13"; //3D Model işemde bu değer 13 olmalı
				$strType					= html_d($_POST['txntype']);
				$strMotoInd					= "N";
				$strAuthenticationCode		= html_d($_POST['cavv']);
				$strSecurityLevel			= html_d($_POST['eci']);
				$strTxnID					= html_d($_POST['xid']);
				$strMD						= html_d($_POST['md']);
				$SecurityData				= strtoupper(sha1($this->strProvisionPassword.$strTerminalID_));
				$HashData					= strtoupper(sha1($strOrderID.$strTerminalID.$strAmount.$SecurityData)); //Daha kısıtlı bilgileri HASH ediyoruz.

				// Callback hash hesaplama kontrolü
				$callbackHashLogData = [
					'SecurityData_Input' => $this->strProvisionPassword . $strTerminalID_,
					'SecurityData_Hash' => $SecurityData,
					'HashData_Input' => $strOrderID . $strTerminalID . $strAmount . $SecurityData,
					'HashData_Hash' => $HashData,
					'SecurityData_Length' => strlen($SecurityData),
					'HashData_Length' => strlen($HashData),
					'SecurityData_IsUppercase' => $SecurityData === strtoupper($SecurityData) ? 'EVET' : 'HAYIR',
					'HashData_IsUppercase' => $HashData === strtoupper($HashData) ? 'EVET' : 'HAYIR',
					'OrderID' => $strOrderID,
					'TerminalID' => $strTerminalID,
					'Amount' => $strAmount
				];
				process_log('GarantiPOS Hash Hesaplama (Callback Provizyon): ' . print_r($callbackHashLogData, true));

				$strXML = '<?xml version="1.0" encoding="UTF-8"?>
				<GVPSRequest>
					<Mode>'.$this->strMode.'</Mode>
					<Version>'.$this->strApiVersion.'</Version>
					<ChannelCode></ChannelCode>
					<Terminal>
						<ProvUserID>'.$strProvUserID.'</ProvUserID>
						<HashData>'.$HashData.'</HashData>
						<UserID>'.$strUserID.'</UserID>
						<ID>'.$strTerminalID.'</ID>
						<MerchantID>'.$strMerchantID.'</MerchantID>
					</Terminal>
					<Customer>
						<IPAddress>'.$strIPAddress.'</IPAddress>
						<EmailAddress>'.$strEmailAddress.'</EmailAddress>
					</Customer>
					<Card><Number>'.$strNumber.'</Number><ExpireDate>'.$strExpireDate.'</ExpireDate><CVV2>'.$strCVV2.'</CVV2></Card>
					<Order>
						<OrderID>'.$strOrderID.'</OrderID>
						<GroupID></GroupID>
						<AddressList><Address><Type>B</Type><Name></Name><LastName></LastName><Company></Company><Text></Text><District></District><City></City><PostalCode></PostalCode><Country></Country><PhoneNumber></PhoneNumber></Address></AddressList>
					</Order>
					<Transaction>
						<Type>'.$strType.'</Type>
						<InstallmentCnt>'.$strInstallmentCount.'</InstallmentCnt>
						<Amount>'.$strAmount.'</Amount>
						<CurrencyCode>'.$strCurrencyCode.'</CurrencyCode>
						<CardholderPresentCode>'.$strCardholderPresentCode.'</CardholderPresentCode>
						<MotoInd>'.$strMotoInd.'</MotoInd>
						<Secure3D>
							<AuthenticationCode>'.$strAuthenticationCode.'</AuthenticationCode>
							<SecurityLevel>'.$strSecurityLevel.'</SecurityLevel>
							<TxnID>'.$strTxnID.'</TxnID>
							<Md>'.$strMD.'</Md>
						</Secure3D>
					</Transaction>
				</GVPSRequest>';

				process_log("GarantiPOS XML Request: " . $strXML);

				$ch=curl_init();
				curl_setopt($ch, CURLOPT_URL, $this->strHostAddress);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1) ;
				curl_setopt($ch, CURLOPT_POSTFIELDS, "data=".$strXML);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				$results = curl_exec($ch);
				$curl_error = curl_errno($ch);
				curl_close($ch);
				
				process_log("GarantiPOS XML Response: " . $results);

				// CURL hata kontrolü
				if ($curl_error) {
					$this->odeme_yapildi = false;
					$this->aciklama = "CURL hatası: " . $curl_error;
					process_log("GarantiPOS CURL Hatası: " . $curl_error);
					return;
				}
				
				// XML yanıt kontrolü
				if (empty($results)) {
					$this->odeme_yapildi = false;
					$this->aciklama = "Boş yanıt alındı";
					process_log("GarantiPOS Hata: Boş yanıt alındı");
					return;
				}

				$xml_parser = xml_parser_create();
				$parse_result = xml_parse_into_struct($xml_parser, $results, $vals, $index);
				$xml_error = !$parse_result ? xml_error_string(xml_get_error_code($xml_parser)) : '';
				xml_parser_free($xml_parser);
				
				// XML ayrıştırma hatası kontrolü
				if (!$parse_result || !isset($index['REASONCODE'][0])) {
					$this->odeme_yapildi = false;
					$this->aciklama = "XML yanıtı ayrıştırılamadı" . (!empty($xml_error) ? " (" . $xml_error . ")" : "");
					process_log("GarantiPOS XML Parse Hatası: " . $this->aciklama);
					return;
				}

				$this->strXML = $strXML;
				$this->results = $results;
				$this->strReasonCodeValue = $vals[$index['REASONCODE'][0]]['value'];
				
				$this->odeme_yapildi = ($this->strReasonCodeValue == "00");
				
				$this->aciklama = ($this->odeme_yapildi) ? 'İşlem başarılı' : 'İşlem başarısız ! Hata kodu : '.$this->strReasonCodeValue;
				
				process_log("GarantiPOS Sonuç: ReasonCode=" . $this->strReasonCodeValue . " - Açıklama=" . $this->aciklama);

			} else {
				$this->odeme_yapildi = false;
				$this->aciklama = isset($this->md_status_aciklama["$strMDStatus"]) ? 
					$this->md_status_aciklama["$strMDStatus"] : 
					"Bilinmeyen mdstatus: $strMDStatus";
				process_log("GarantiPOS MDStatus Başarısız: " . $this->aciklama);
			}
		} catch (Exception $e) {
			$this->odeme_yapildi = false;
			$this->aciklama = "Hata: " . $e->getMessage();
			// Log hata bilgisi
			error_log("GarantiPOS Hata: " . $e->getMessage());
			process_log("GarantiPOS Exception: " . $e->getMessage());
		}
	}
	public function odeme_yapildi(){
		return $this->odeme_yapildi;
	}

	public function getIP() {
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
			if (array_key_exists($key, $_SERVER) === true){
				foreach (explode(',', $_SERVER[$key]) as $ip){
					if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
						return $ip;
					}
				}
			}
		}
		return '127.0.0.1'; // Default fallback IP
	}

	public function order_sorgula($order_id) {
		$this->strOrderID = $order_id;
		$this->strType = 'orderhistoryinq';
		
		// Hash için gerekli parametreleri güncelle
		// OrderHistory transaction'ında Amount, Installment, URL'ler XML'de yok, Hash'te de olmamalı
		$this->strAmount = ''; 
		$this->strInstallmentCount = '';
        
        // URL'leri geçici olarak boşalt (Hash'e girmemesi için)
        $tempSuccessURL = $this->strSuccessURL;
        $tempErrorURL = $this->strErrorURL;
        $this->strSuccessURL = '';
        $this->strErrorURL = '';
		
		$this->calculate_xml_hash();
        
        // URL'leri geri yükle (gerçi bu nesne tekrar kullanılmayacaksa önemli değil)
        $this->strSuccessURL = $tempSuccessURL;
        $this->strErrorURL = $tempErrorURL;

		$strXML = '<?xml version="1.0" encoding="UTF-8"?>
		<GVPSRequest>
			<Mode>'.$this->strMode.'</Mode>
			<Version>'.$this->strApiVersion.'</Version>
			<ChannelCode></ChannelCode>
			<Terminal>
				<ProvUserID>'.$this->strTerminalProvUserID.'</ProvUserID>
				<HashData>'.$this->HashData.'</HashData>
				<UserID>'.$this->strTerminalUserID.'</UserID>
				<ID>'.$this->strTerminalID.'</ID>
				<MerchantID>'.$this->strTerminalMerchantID.'</MerchantID>
			</Terminal>
			<Order>
				<OrderID>'.$this->strOrderID.'</OrderID>
			</Order>
			<Transaction>
				<Type>'.$this->strType.'</Type>
			</Transaction>
		</GVPSRequest>';

		$results = $this->send_xml_request($strXML);
		
		// XML Parsing
		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $results, $vals, $index);
		xml_parser_free($xml_parser);

		$reasonCode = isset($index['REASONCODE'][0]) ? ($vals[$index['REASONCODE'][0]]['value'] ?? '') : '';
		$returnCode = isset($index['RETURNCODE'][0]) ? ($vals[$index['RETURNCODE'][0]]['value'] ?? '') : '';
		
		// Detaylı yanıt analizi
		$response_data = [
			'status' => ($reasonCode == '00') ? 'success' : 'error',
			'code' => $reasonCode,
			'message' => 'Reason: ' . $reasonCode . ' Return: ' . $returnCode,
			'raw_response' => $results
		];
		
		// Başarılı ise tutar bilgisini çekmeye çalış
		if ($response_data['status'] == 'success') {
			// Transaction listesinden son işlemi bulmak gerekebilir.
			// Basitçe Amount varsa al
			if (isset($index['AMOUNT'][0])) {
				$amount = $vals[$index['AMOUNT'][0]]['value'];
				$response_data['amount'] = $amount / 100;
			}
            
            // Native Type
            if (isset($index['TYPE'][0])) {
                $response_data['type'] = $vals[$index['TYPE'][0]]['value'] ?? '';
            }
		}

		return $response_data;
	}

	public function postauth_yap($orderId, $amount) {
		$this->strOrderID = $orderId;
		// Tutar kuruş cinsinden olmalı
		$this->strAmount = number_format($amount, 2, '.', '') * 100;
		$this->strType = 'postauth';
		$this->strInstallmentCount = '';

		$this->calculate_xml_hash();

		$strXML = '<?xml version="1.0" encoding="UTF-8"?>
		<GVPSRequest>
			<Mode>'.$this->strMode.'</Mode>
			<Version>'.$this->strApiVersion.'</Version>
			<ChannelCode></ChannelCode>
			<Terminal>
				<ProvUserID>'.$this->strTerminalProvUserID.'</ProvUserID>
				<HashData>'.$this->HashData.'</HashData>
				<UserID>'.$this->strTerminalUserID.'</UserID>
				<ID>'.$this->strTerminalID.'</ID>
				<MerchantID>'.$this->strTerminalMerchantID.'</MerchantID>
			</Terminal>
			<Order>
				<OrderID>'.$this->strOrderID.'</OrderID>
			</Order>
			<Transaction>
				<Type>'.$this->strType.'</Type>
				<Amount>'.$this->strAmount.'</Amount>
				<CurrencyCode>'.$this->strCurrencyCode.'</CurrencyCode>
			</Transaction>
		</GVPSRequest>';
		
		$results = $this->send_xml_request($strXML);

		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $results, $vals, $index);
		xml_parser_free($xml_parser);

		$reasonCode = $vals[$index['REASONCODE'][0]]['value'] ?? '';

		return [
			'status' => ($reasonCode == '00') ? 'success' : 'error', 
			'code' => $reasonCode,
			'message' => $vals[$index['ERRORMSG'][0]]['value'] ?? 'Code: '.$reasonCode
		];
	}

	public function iptal_yap($orderId) {
		$this->strOrderID = $orderId;
		// Void işleminde genellikle tutar gönderilmez veya 0 olabilir ama hash hesaplamasında ne kullanıldığı kritiktir.
		// Genellikle Void tüm işlemi iptal eder. Amount boş geçilir.
		$this->strAmount = ''; 
		$this->strType = 'void';
		$this->strInstallmentCount = '';

		$this->calculate_xml_hash();

		$strXML = '<?xml version="1.0" encoding="UTF-8"?>
		<GVPSRequest>
			<Mode>'.$this->strMode.'</Mode>
			<Version>'.$this->strApiVersion.'</Version>
			<ChannelCode></ChannelCode>
			<Terminal>
				<ProvUserID>'.$this->strTerminalProvUserID.'</ProvUserID>
				<HashData>'.$this->HashData.'</HashData>
				<UserID>'.$this->strTerminalUserID.'</UserID>
				<ID>'.$this->strTerminalID.'</ID>
				<MerchantID>'.$this->strTerminalMerchantID.'</MerchantID>
			</Terminal>
			<Order>
				<OrderID>'.$this->strOrderID.'</OrderID>
			</Order>
			<Transaction>
				<Type>'.$this->strType.'</Type>
			</Transaction>
		</GVPSRequest>';

		$results = $this->send_xml_request($strXML);

		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $results, $vals, $index);
		xml_parser_free($xml_parser);

		$reasonCode = $vals[$index['REASONCODE'][0]]['value'] ?? '';

		return [
			'status' => ($reasonCode == '00') ? 'success' : 'error', 
			'code' => $reasonCode,
			'message' => $vals[$index['ERRORMSG'][0]]['value'] ?? 'Code: '.$reasonCode
		];
	}

	public function iade_yap($orderId, $amount) {
		$this->strOrderID = $orderId;
		// Tutar kuruş cinsinden olmalı
		$this->strAmount = number_format($amount, 2, '.', '') * 100;
		$this->strType = 'refund';
		$this->strInstallmentCount = '';

		$this->calculate_xml_hash();

		$strXML = '<?xml version="1.0" encoding="UTF-8"?>
		<GVPSRequest>
			<Mode>'.$this->strMode.'</Mode>
			<Version>'.$this->strApiVersion.'</Version>
			<ChannelCode></ChannelCode>
			<Terminal>
				<ProvUserID>'.$this->strTerminalProvUserID.'</ProvUserID>
				<HashData>'.$this->HashData.'</HashData>
				<UserID>'.$this->strTerminalUserID.'</UserID>
				<ID>'.$this->strTerminalID.'</ID>
				<MerchantID>'.$this->strTerminalMerchantID.'</MerchantID>
			</Terminal>
			<Order>
				<OrderID>'.$this->strOrderID.'</OrderID>
			</Order>
			<Transaction>
				<Type>'.$this->strType.'</Type>
				<Amount>'.$this->strAmount.'</Amount>
				<CurrencyCode>'.$this->strCurrencyCode.'</CurrencyCode>
			</Transaction>
		</GVPSRequest>';

		$results = $this->send_xml_request($strXML);

		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $results, $vals, $index);
		xml_parser_free($xml_parser);

		$reasonCode = $vals[$index['REASONCODE'][0]]['value'] ?? '';

		return [
			'status' => ($reasonCode == '00') ? 'success' : 'error', 
			'code' => $reasonCode,
			'message' => $vals[$index['ERRORMSG'][0]]['value'] ?? 'Code: '.$reasonCode
		];
	}

	private function calculate_xml_hash() {
		// API çağrıları için hash hesaplama (SecurityData önce hesaplanmalı)
		$this->SecurityData = strtoupper(sha1($this->strProvisionPassword . $this->strTerminalID_));
		
		$hash_input = 
			$this->strOrderID .
			$this->strTerminalID .
			$this->strAmount .
			$this->SecurityData;
/*
        // Alternatif hash (Garanti bazen terminal id'yi başa ister)
        if ($this->strType == 'orderhistoryinq') {
            $hash_input = $this->strTerminalID . $this->strOrderID . $this->strAmount . $this->SecurityData;
        }
*/

		$this->HashData = strtoupper(sha1($hash_input));
		
		// Log
		$hashLog = [
			'Type' => $this->strType,
			'Input' => $hash_input,
			'Hash' => $this->HashData
		];
		process_log('GarantiPOS XML Hash: ' . print_r($hashLog, true));
	}

	private function send_xml_request($xml) {
		process_log("GarantiPOS XML Request ({$this->strType}): " . $xml);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->strHostAddress);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "data=" . $xml);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Prod ortamında 1 olmalı aslında ama sertifika sorununu aşmak için 0
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$results = curl_exec($ch);
		$curl_error = curl_errno($ch);
		curl_close($ch);

		process_log("GarantiPOS XML Response ({$this->strType}): " . $results);
		
		if ($curl_error) {
			process_log("GarantiPOS CURL Error: " . $curl_error);
			return '';
		}
		
		return $results;
	}

	public function banka_donus_kod_yazi($no, $d = 'tr'){
		$banka_donus_kodu = [
			'tr' => [
				"51"=>"Hesap Müsait Degil (Limit yetersiz)",
				"00"=>"Onay",
				"99"=>"Sistem Hatasi",
				"05"=>"Red (Banka)",
				"01"=>"Banka arama",
				"02"=>"Banka Arama",
				"03"=>"Üye isyeri kategori kodu hatali",
				"04"=>"Sahte kart",
				"06"=>"Isteminiz Kabul Edilmedi",
				"07"=>"__",/*KARTA EL KOYUNUZ*/
				"08"=>"Kimligini Kontrol Ederek Islemi Yapiniz.",
				"11"=>"Islem Gerçeklestirildi (VIP).",
				"12"=>"RED !!! Geçersiz Islem. (Banka)",
				"13"=>"Meblag Geçersiz.",
				"14"=>"Kart Numarasi Hatali.",
				"15"=>"BIN (Banka Kodu) Hatali.",
				"16"=>"Bakiye yetersiz",
				"17"=>"Islem Iptal Edildi.",
				"19"=>"Bir Kere Daha Provizyon Talep Ediniz.",
				"20"=>"Geçersiz Yanit.",
				"21"=>"Islemin Aslina Dönülemedigi Için Islem Gerçeklesmedi",
				"24"=>"Alici,Bu Islemi Yapmiyor.",
				"25"=>"Böyle Bir Bilgi Bulunamadi.",
				"26"=>"Bu Kayit Zaten Mevcut.",
				"27"=>"Mesaj Formati Hatali.",
				"28"=>"Daha Sonra Islemi Yineleyiniz.",
				"30"=>"Mesajin Formati Hatali.",
				"32"=>"Islem Kismen Gerçeklestirilebildi,Iptal Ediniz.",
				"33"=>"Kartin Süresi Dolmus !!! .",
				"34"=>"Muhtemelen Çalinti Kart !!! .",
				"38"=>"Sifre Giris Limiti Asildi !!! .",
				"41"=>"KAYIP KART !!!!   .",
				"43"=>"ÇALINTI KART !!!!   .",
				"52"=>"Böyle Bir Çek Hesabi Yok.",
				"53"=>"Böyle Bir Hesap Yok.",
				"54"=>"Vadesi Dolmus Kart.",
				"55"=>"Sifresi Hatali.",
				"56"=>"Bu Kart Mevcut Degil.",
				"57"=>"Kart Sahibi Bu Islemi Yapamaz.",
				"58"=>"Bu Islemi Yapmaniza Müsaade Edilmiyor.",
				"59"=>"Muhtemelen Sahte Kart.",
				"61"=>"Limit Asiliyor.",
				"62"=>"Sınırlı Kart.(İssuer -banka- tarafından dönen sonuç, banka işlemi kabul etmiyor.)",
				"63"=>"Bu Islemi Yapmaya Yetkili Degilsiniz.",
				"65"=>"Günlük Islem Adedi Dolmus.",
				"68"=>"Cevap Çok Geç Geldi.Islemi Iptal Ediniz.",
				"75"=>"Sifre Giris Limiti Asildi.",
				"76"=>"Sifre hatali.Sifre Giris Limiti Asildi.",
				"77"=>"Bu Islemin Bilgileri Ilgili Islem Ile Uyusmuyor",
				"80"=>"Tarih Hatali.",
				"81"=>"Mesaj Sifreleme Hatali.",
				"82"=>"Yetersiz bakiye.",
				"83"=>"Sifre Dogrulanamiyor.",
				"85"=>"Hesap Dogrulanamiyor.",
				"90"=>"Günsonu Islemleri Yapiliyor.",
				"91"=>"Bankasi (veya switch system) Kapali.",
				"92"=>"EM Kapali.",
				"93"=>"Hukuki Nedenlerle Isleminiz Rededildi.",
				"95"=>"Günlük Toplamlar hatali.",
				"96"=>"Sistem Hatasi",
				"-1"=>"Sistem Hatasi",
				"31"=>"Kayip / Çalinti Kart"
			],
			'en' => [
				"51"=>"Account is not available (Limit is insufficient)",
				"00"=>"Confirm",
				"99"=>"System Fault",
				"05"=>"Red (Bank)",
				"01"=>"Bank search",
				"02"=>"Bank Search",
				"03"=>"Member home category code is incorrect",
				"04"=>"Fake card",
				"06"=>"Your request was not accepted",
				"07"=>"__",
				"08"=>"Do your job by checking your identity.",
				"11"=>"Process has been performed (VIP).",
				"12"=>"RED !!! Invalid Transaction (Bank)",
				"13"=>"Meblag is invalid.",
				"14"=>"Card number is incorrect.",
				"15"=>"BIN (Bank Code) Invalid.",
				"16"=>"Insufficient balance",
				"17"=>"The process has been canceled.",
				"19"=>"Once more ask for a provision.",
				"20"=>"Invalid Reply.",
				"21"=>"The process did not take place because the process was not originally done",
				"24"=>"Buyer does not do this transaction.",
				"25"=>"No such information was found.",
				"26"=>"This record already exists.",
				"27"=>"Message Form is Wrong.",
				"28"=>"Repeat the process later.",
				"30"=>"Message Format is Wrong.",
				"32"=>"The process can be performed, you can cancel.",
				"33"=>"The Card is Filling Up the Slave !!!",
				"34"=>"Probably Stolen Card !!!",
				"38"=>"Password Entry Limiti Bug !!!",
				"41"=>"LOST CARD !!!!",
				"43"=>"PLAY CARD !!!!",
				"52"=>"There is no such a Check Account.",
				"53"=>"There is no such account.",
				"54"=>"Dolmus Dolmus Kart.",
				"55"=>"Invalid Password.",
				"56"=>"This card is not available.",
				"57"=>"The card owner can not do this transaction.",
				"58"=>"This is not allowed to do this operation.",
				"59"=>"Probably Fake Card.",
				"61"=>"Limit is hung.",
				"62"=>"Limited Card (Issuer - the result returned by the bank does not accept bank transaction)",
				"63"=>"You are not authorized to do this.",
				"65"=>"Daily Process Is Adedi Dolmus.",
				"68"=>"The Response Is Too Late. Please Cancel the Action.",
				"75"=>"Password Entry Limit Has Been Done.",
				"76"=>"Password is incorrect. Password Entry Limit has been set.",
				"77"=>"This transaction information does not comply with my current transaction",
				"80"=>"Date is False.",
				"81"=>"Message Encryption Invalid.",
				"82"=>"Insufficient balance.",
				"83"=>"Password Can not be Verified.",
				"85"=>"Account Can not be Verified.",
				"90"=>"Günsonu İşlemleri is being done.",
				"91"=>"Bankasi (or switch system) Off.",
				"92"=>"EM Close.",
				"93"=>"You have been rejected for legal reasons.",
				"95"=>"Daily Totals are incorrect.",
				"96"=>"System Fault",
				"-1"=>"System Fault",
				"31"=>"Lost / Stolen Card"
			]
		];

		$d = (isset($banka_donus_kodu[$d])) ? $d : 'tr';
		$mesaj = isset($banka_donus_kodu[$d][$no]) ? $banka_donus_kodu[$d][$no] : '';
		return !empty($mesaj) ? $mesaj . ' [' . $no . ']' : '';

	}

	private function logBankHashCheck() {
		$hashParams = isset($_POST['hashparams']) ? html_d($_POST['hashparams']) : '';
		$hashParamsVal = isset($_POST['hashparamsval']) ? html_d($_POST['hashparamsval']) : '';
		$hashFromBank = isset($_POST['hash']) ? html_d($_POST['hash']) : '';
		if ($hashParams === '' && $hashParamsVal === '' && $hashFromBank === '') {
			process_log('GarantiPOS Callback Hash Bilgisi: Bankadan hash parametreleri gelmedi.');
			return;
		}
		$calculatedHash = base64_encode(pack('H*', sha1($hashParamsVal.$this->strStoreKey)));
		$hashMatch = false;
		if ($hashFromBank !== '') {
			$hashMatch = function_exists('hash_equals') ? hash_equals($hashFromBank, $calculatedHash) : $hashFromBank === $calculatedHash;
		}
		
		// Hash kontrol sonuçlarını logla
		$logData = [
			'hashParams' => $hashParams,
			'hashParamsVal' => $hashParamsVal,
			'hashFromBank' => $hashFromBank,
			'calculatedHash' => $calculatedHash,
			'hashMatch' => $hashMatch ? 'EVET (Eşleşti)' : 'HAYIR (Eşleşmedi)',
			'storeKey' => substr($this->strStoreKey, 0, 8) . '...' // İlk 8 karakteri göster
		];
		process_log('GarantiPOS Hash Kontrol Sonucu: ' . print_r($logData, true));
		
	}
	
}

