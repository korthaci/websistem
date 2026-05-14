<?php if (! defined('otoban')) exit('Veriyolu acik degil.');

/*
unsubscribe icin link bu sekilde olusturulmalı:
$uei = sifrele4('uyeler|'.$uye_no.'|'.$email);
$link = LOCAL.'/uis/emailiptal?uei='.rawurlencode($uei);
*/
$email_iptal_edildi = false;
$hata_mesaji = '';
$uei = trim((string) ($_GET['uei'] ?? ''));
$payload = $uei !== '' ? sifre_ac4($uei) : false;
$payload_parcalari = is_string($payload) ? explode('|', $payload, 3) : [];

if (count($payload_parcalari) !== 3 || $payload_parcalari[0] !== 'uyeler') {
	$hata_mesaji = 'Email iptal baglantisi gecersiz.';
} else {
	$uye_no = (int) $payload_parcalari[1];
	$email = email_duzenle($payload_parcalari[2]);

	if ($uye_no <= 0 || $email === '') {
		$hata_mesaji = 'Email iptal baglantisi gecersiz.';
	} else {
		$uye = $pdo_db->satir(
			"SELECT no, email, email_alimi FROM {$do_}uyeler WHERE no = ?",
			[$uye_no]
		);

		if (!$uye || mb_strtolower((string) $uye->email, 'UTF-8') !== mb_strtolower($email, 'UTF-8')) {
			$hata_mesaji = 'Email iptal baglantisi gecersiz.';
		} elseif ((int) $uye->email_alimi === 0) {
			$hata_mesaji = 'Email alimi zaten iptal edilmis.';
		} elseif (isset($_GET['emailiptalonay'])) {
			$email_iptal_edildi = $pdo->prepare(
				"UPDATE {$do_}uyeler SET email_alimi = 0 WHERE no = ? AND email = ?"
			)->execute([$uye_no, $email]);

			if (!$email_iptal_edildi) {
				$hata_mesaji = 'Email iptal edilemedi. Lutfen tekrar deneyin.';
			}
		} else {
			$onay_link = LOCAL . '/uis/emailiptal?uei=' . rawurlencode($uei) . '&emailiptalonay=1';
			echo '
			<div class="container">
				<div class="yazi5">Email alimini iptal etmek istiyor musunuz?</div>
				<br>
				<a href="'.hs($onay_link).'" class="gonder">Onayliyorum</a>
				<a href="'.LOCAL.'" class="gonder">Onaylamiyorum</a>
			</div>';
		}
	}
}

if ($email_iptal_edildi) {
	echo '<div class="container"><div class="yazi5">Email iptal edildi.</div></div>';
} elseif ($hata_mesaji !== '') {
	echo '<div class="container"><div class="yazi5">'.hs($hata_mesaji).'</div></div>';
}
