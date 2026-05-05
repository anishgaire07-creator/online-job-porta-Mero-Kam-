<?php

declare(strict_types=1);

namespace MeroKam\Helpers;

final class Mail
{
    public static function send(string $to, string $subject, string $body, bool $html = false): bool
    {
        $cfg = require dirname(__DIR__) . '/config/mail.php';
        $from = $cfg['from_email'];
        $name = $cfg['from_name'];
        $headers = [];
        $headers[] = 'From: ' . self::encodeHeader($name) . " <{$from}>";
        $headers[] = 'Reply-To: ' . $from;
        $headers[] = 'X-Mailer: PHP/' . PHP_VERSION;
        if ($html) {
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=UTF-8';
        } else {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        }
        return @mail($to, self::encodeSubject($subject), $body, implode("\r\n", $headers));
    }

    private static function encodeHeader(string $s): string
    {
        return '=?UTF-8?B?' . base64_encode($s) . '?=';
    }

    private static function encodeSubject(string $s): string
    {
        return '=?UTF-8?B?' . base64_encode($s) . '?=';
    }

    public static function notifyApplicationToAdmin(
        string $adminEmail,
        string $applicantName,
        string $applicantEmail,
        ?string $phone,
        string $jobTitle,
        string $companyName,
        ?string $cover
    ): void {
        $body = "New job application received.\n\n";
        $body .= "Applicant: {$applicantName}\n";
        $body .= "Email: {$applicantEmail}\n";
        $body .= 'Phone: ' . ($phone ?? '—') . "\n";
        $body .= "Job: {$jobTitle}\n";
        $body .= "Company: {$companyName}\n\n";
        $body .= "Cover letter:\n" . ($cover ?? '—') . "\n";
        self::send($adminEmail, 'New Job Application — Mero Kam', $body);
    }

    public static function notifyApplicant(string $toEmail, string $jobTitle, string $companyName): void
    {
        $body = "Thank you for applying through Mero Kam.\n\n";
        $body .= "Position: {$jobTitle}\n";
        $body .= "Company: {$companyName}\n\n";
        $body .= "We wish you the best of luck.\n— Mero Kam Team\n";
        self::send($toEmail, 'Application submitted — Mero Kam', $body);
    }

    public static function jobAlert(string $toEmail, string $subject, string $body): void
    {
        self::send($toEmail, $subject, $body);
    }
}
