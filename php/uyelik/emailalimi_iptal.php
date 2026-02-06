<?php if (! defined('otoban')) exit('Veriyolu açık değil.');

$email_iptal_edildi = false;
$uei = (isset($_GET['uei'])) ? z($_GET['uei']) : 0;

if ($uei != 0 && isset($_GET['emailiptalonay'])) {

	$stmt = $pdo->query("SELECT no FROM {$do_}uyeler");
	foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $u) {
		$stmt2 = $pdo->prepare("UPDATE {$do_}uyeler SET email_alimi = 0 WHERE no=?");
		$email_iptal = $stmt2->execute([$u->no]);
	}

	$email_iptal_edildi = (isset($email_iptal) && $email_iptal == true);

} else {
	echo '
	<div class="yazi5"><br>' . yc("Email alımını iptal ettiniz") . '. ' . yc("Onaylıyor musunuz") . '</div><br><br>
	<b><a href="'.href('index','uis=emailiptal&uei='.$uei.'&emailiptalonay=1&d='.$d).'" class="gonder"> ' . yc("Onaylıyorum") . ' </a> &nbsp;
	<a href="./" class="gonder"> ' . yc("Onaylamıyorum") . ' </a></b><br><br>
	';
}

if ($email_iptal_edildi) {
	echo '<div class="yazi5">✓ ' . yc("Email iptal edildi") . '</div>';
}
?>