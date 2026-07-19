<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;

final class MailService
{
    public function send(string $to, string $subject, string $html, string $text = ''): void
    {
        $cfg = config('mail');
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $cfg['host'];
        $mail->Port = $cfg['port'];
        $mail->SMTPAuth = $cfg['username'] !== '';
        $mail->Username = $cfg['username'];
        $mail->Password = $cfg['password'];
        if ($cfg['encryption'] !== '') $mail->SMTPSecure = $cfg['encryption'];
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($cfg['from_address'], $cfg['from_name']);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->AltBody = $text !== '' ? $text : strip_tags($html);
        $mail->send();
    }
}
