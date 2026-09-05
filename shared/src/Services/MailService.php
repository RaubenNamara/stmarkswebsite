<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use StMarks\Shared\Config\Config;
use StMarks\Shared\Support\Logger;

/**
 * Thin PHPMailer/SMTP wrapper. Every caller (ContactService today) treats a send failure as
 * non-fatal - the underlying record (e.g. the contact message) is already saved before mail is
 * attempted, matching the old app's behavior of never failing the user-facing action just
 * because notification email didn't go out. Failures are logged instead of silently swallowed.
 */
class MailService
{
    public function send(string $toAddress, string $toName, string $subject, string $htmlBody): bool
    {
        $config = Config::getMailConfig();
        if (!$config['host'] || !$config['username']) {
            Logger::warning('MailService: no MAIL_HOST/MAIL_USERNAME configured, skipping send', ['subject' => $subject]);
            return false;
        }

        $mailer = new PHPMailer(true);

        try {
            $mailer->isSMTP();
            $mailer->Host = $config['host'];
            $mailer->Port = $config['port'];
            $mailer->SMTPAuth = true;
            $mailer->Username = $config['username'];
            $mailer->Password = $config['password'];
            $mailer->SMTPSecure = $config['encryption'] ?: PHPMailer::ENCRYPTION_STARTTLS;

            $mailer->setFrom($config['from_address'] ?: $config['username'], $config['from_name']);
            $mailer->addAddress($toAddress, $toName);
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $htmlBody;
            $mailer->AltBody = trim(strip_tags($htmlBody));

            $mailer->send();
            return true;
        } catch (PHPMailerException $e) {
            Logger::error('MailService: send failed', ['subject' => $subject, 'error' => $mailer->ErrorInfo]);
            return false;
        }
    }

    /** @param array<string> $recipients */
    public function sendToMany(array $recipients, string $subject, string $htmlBody): void
    {
        foreach ($recipients as $recipient) {
            $this->send(trim($recipient), '', $subject, $htmlBody);
        }
    }
}
