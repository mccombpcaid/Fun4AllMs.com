<?php
// mail-config.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Sends an email using SMTP settings from the .env file.
 *
 * @param string $to Recipient email address
 * @param string $subject Email subject line
 * @param string $body HTML content of the email
 * @return bool True on success, false on failure
 */
function sendBookingEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // --- SMTP Settings ---
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];      // smtp.gmail.com
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USER'];      // your email
        $mail->Password   = $_ENV['MAIL_PASS'];      // 16-character App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // --- Sender & Recipient ---
        $mail->setFrom($_ENV['MAIL_USER'], 'Fun 4 All MS');
        $mail->addAddress($to);
        $mail->addReplyTo($_ENV['MAIL_USER'], 'Fun 4 All MS');

        // --- Content ---
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        // Plain text version for users with HTML disabled
        $mail->AltBody = strip_tags(str_replace(['<br>', '</div>', '</p>'], "\n", $body));

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Logs errors to Laragon's error log instead of crashing the site
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        return false;
    }
}