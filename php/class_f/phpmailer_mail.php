<?php if (! defined('otoban')) exit();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class phpmailer_mail {
    private array $smtp_dizi;
    private string $server;
    private int $smtp_debug;
    private bool $local_test = false;
    public bool $gonderildi;
    public string $bcc_mail = '';

    public function __construct(array $smtp_dizi, int $smtp_debug = 0) {
        $this->smtp_dizi = $smtp_dizi;
        $this->server = $smtp_dizi['varsayilan'] ?? 'local';
        $this->smtp_debug = $smtp_debug;
        $this->gonderildi = false;
        if (function_exists('local_test') && local_test()) {
            $this->local_test = true;
            $this->smtp_debug = 2;
        }/* else {
            $this->local_test = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1'])  || in_array($_SERVER['SERVER_ADDR'], ['127.0.0.1', '::1']);
        }*/

    }

    
    public function gonder(
        $to = null,
        string $subject = '',
        string $body = '',
        string $reply = '',
        string $bcc = 'bcc',
        string $textMessage = '',
        string $gonderen_adi = ''
    ): bool {

        if ($this->local_test) {
            $this->gonderildi = true;
            return true;
        }

        $driver = $this->smtp_dizi['driver'] ?? '';
        $method = $this->smtp_dizi['method'] ?? 'smtp';

        if ($driver === 'symfony' && class_exists('Symfony\Component\Mailer\Transport')) {
            return $this->symfony_ile_gonder($to, $subject, $body, $reply, $bcc, $textMessage, $method, $gonderen_adi);
        }

        if ($this->phpmailer_ile_gonder($to, $subject, $body, $reply, $bcc, $textMessage, $gonderen_adi)) {
            return true;
        } else {
            process_log("Mail Gönderilemedi");
            return false;
        }
        
    }

    private function phpmailer_ile_gonder(
        $to,
        string $subject,
        string $body,
        string $reply = '',
        string $bcc = 'bcc',
        string $textMessage = '',
        string $gonderen_adi = ''
    ): bool {
        $this->gonderildi = false;
        $mail = new PHPMailer(true);

        $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $subject_domain = strtoupper(preg_replace('/[^a-zA-Z0-9]+/i', '.', $serverName));
        $set_from_mail = $this->smtp_dizi[$this->server]['from'] ?: $this->smtp_dizi[$this->server]['k_adi'];
        $from_adi = $gonderen_adi !== '' ? $gonderen_adi : $subject_domain;

        try {
            $mail->isSMTP();
            $mail->Host = $this->smtp_dizi[$this->server]['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_dizi[$this->server]['k_adi'];
            $mail->Password = $this->smtp_dizi[$this->server]['sifre'];
            $mail->SMTPSecure = $this->smtp_dizi[$this->server]['port'] == 587 ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $this->smtp_dizi[$this->server]['port'];
            $mail->SMTPDebug = $this->smtp_debug;
            $mail->CharSet = 'UTF-8';

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ]
            ];

            $mail->setFrom($set_from_mail, $from_adi);

            if ($reply) {
                $mail->addReplyTo($reply, $subject_domain);
            }

            if (!is_array($to)) {
                $to = explode(',', (string)$to);
            } else {
                $to = array_merge(...array_map(fn($item) => (is_string($item) && strpos($item, ',') !== false) ? explode(',', $item) : [(string)$item], $to));
            }

            $to = array_map(fn($email) => trim((string)$email), $to);
            foreach ($to as $to_email) {
                $to_email = (string)$to_email;
                if (filter_var(trim($to_email), FILTER_VALIDATE_EMAIL)) {
                    $mail->addAddress(trim($to_email), "$subject_domain");
                }
            }

            if ($this->bcc_mail && $bcc === 'bcc') {
                $mail->addBCC($this->bcc_mail, "$subject BCC");
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $textMessage ?: strip_tags($body);

            return $this->gonderildi = $mail->send();
        } catch (Exception $e) {
            error_log("Email gönderilemedi: {$mail->ErrorInfo}");
            return false;
        }
    }


    private function symfony_ile_gonder(
        $to,
        string $subject,
        string $body,
        string $reply = '',
        string $bcc = 'bcc',
        string $textMessage = '',
        string $method = 'smtp',
        string $gonderen_adi = ''
    ): bool {
        $this->gonderildi = false;

        if (!class_exists('Symfony\Component\Mailer\Transport')) {
            return $this->phpmailer_ile_gonder($to, $subject, $body, $reply, $bcc, $textMessage, $gonderen_adi);
        }

        $serverConfig = $this->smtp_dizi[$this->server];

        if ($method === 'smtp' || $this->server === 'local') {
            $port = $serverConfig['port'] ?? 587;
            $protocol = 'smtp';
            if ($port == 465 || (defined('SMTP_PORT_SSL') && $port == SMTP_PORT_SSL)) {
                $protocol = 'smtps';
            }
            $dsn = "{$protocol}://{$serverConfig['k_adi']}:{$serverConfig['sifre']}@{$serverConfig['host']}:{$port}";
        } else {
            $dsn = $serverConfig['dsn'] ?? '';
        }

        try {
            $transport = \Symfony\Component\Mailer\Transport::fromDsn($dsn);
            $mailer = new \Symfony\Component\Mailer\Mailer($transport);

            // Symfony mail sürücüsü aktif edilirse, virgüllü adres ayrıştırması normalize_email_list() ile uyumlu hale getirilmeli, aksi halde dizi içindeki virgüllü adresler yanlış işlenir.
            if (!is_array($to)) {
                $to = explode(",", $to);
            }

            $from = $gonderen_adi !== ''
                ? new \Symfony\Component\Mime\Address($serverConfig['k_adi'], $gonderen_adi)
                : $serverConfig['k_adi'];

            $email = (new \Symfony\Component\Mime\Email())
                ->from($from)
                ->subject($subject)
                ->html($body)
                ->text($textMessage ?: strip_tags($body));

            foreach ($to as $to_email) {
                $email->to(trim($to_email));
            }

            if ($reply) {
                $email->replyTo($reply);
            }

            if ($this->bcc_mail && $bcc === 'bcc') {
                $email->bcc($this->bcc_mail);
            }

            $mailer->send($email);
            return $this->gonderildi = true;
        } catch (\Exception $e) {
            error_log("Symfony ile Email gönderilemedi: {$e->getMessage()}", PHP_EOL);
            return false;
        }
    }
}
