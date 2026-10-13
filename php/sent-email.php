<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: text/plain; charset=UTF-8');

/* --------------------------------------------------------------------------
 * Composer autoload
 * Expected structure:
 * public_html/vendor/autoload.php
 * public_html/php/sent-email.php
 * -------------------------------------------------------------------------- */

$autoload = __DIR__ . '/../vendor/autoload.php';

if (!is_file($autoload)) {
    http_response_code(500);
    error_log('MMGSS contact form: Composer autoload file not found: ' . $autoload);
    exit('The contact form is temporarily unavailable. Please contact the company directly.');
}

require_once $autoload;
    
/* --------------------------------------------------------------------------
 * SMTP configuration — everything is kept in this one file.
 * Verify the hostname in cPanel > Email Accounts > Connect Devices.
 * The password below must be replaced with the NEW mailbox password.
 * -------------------------------------------------------------------------- */

$smtpHost     = 'mail.myanmargss.com';
$smtpUsername = 'no-reply@myanmargss.com';
$smtpPassword = '4TZvK7y6KI@q)yey';
$recipient    = 'seteam@nicemyanmartravel.com';
$smtpPort     = 465;

if (
    $smtpPassword === '' ||
    $smtpPassword === 'REPLACE_WITH_YOUR_NEW_MGSS_MAILBOX_PASSWORD'
) {
    http_response_code(500);
    error_log('MMGSS contact form: SMTP password is not configured in sent-email.php.');
    exit('The contact form is temporarily unavailable. Please contact the company directly.');
}

if (
    !filter_var($smtpUsername, FILTER_VALIDATE_EMAIL) ||
    !filter_var($recipient, FILTER_VALIDATE_EMAIL) ||
    trim($smtpHost) === ''
) {
    http_response_code(500);
    error_log('MMGSS contact form: Invalid SMTP configuration.');
    exit('The contact form is temporarily unavailable. Please contact the company directly.');
}

// Accept POST requests only.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Please submit the contact form.');
}

// Read and validate form data.
$name = trim((string) ($_POST['name'] ?? ''));
$company = trim((string) ($_POST['company'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$interest = trim((string) ($_POST['interest'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if (
    $name === '' || $company === '' || $email === '' ||
    $phone === '' || $interest === '' || $message === ''
) {
    http_response_code(400);
    exit('Please fill in all required fields.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit('Please enter a valid email address.');
}

$stringLength = static function (string $value): int {
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
};

if (
    $stringLength($name) > 150 ||
    $stringLength($company) > 200 ||
    $stringLength($email) > 320 ||
    $stringLength($phone) > 80 ||
    $stringLength($interest) > 200 ||
    $stringLength($message) > 10000
) {
    http_response_code(400);
    exit('One or more fields exceed the permitted length.');
}

// Remove CR/LF from values used in headers, such as the subject and Reply-To name.
$cleanHeaderValue = static function (string $value): string {
    return trim(str_replace(["\r", "\n"], '', $value));
};

$nameForHeader = $cleanHeaderValue($name);

// Escape form values before placing them into HTML email content.
$escapeHtml = static function (string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};

$safeName = $escapeHtml($name);
$safeCompany = $escapeHtml($company);
$safeEmail = $escapeHtml($email);
$safePhone = $escapeHtml($phone);
$safeInterest = $escapeHtml($interest);
$safeMessage = nl2br($escapeHtml($message));

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = $smtpHost;
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUsername;
    $mail->Password = $smtpPassword;

    // Match the working Golden Support Service settings: SMTP over implicit TLS.
    if ($smtpPort === 465) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        // Use this only if the host's Connect Devices page confirms port 587.
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }

    $mail->Port = $smtpPort;
    $mail->CharSet = 'UTF-8';
    $mail->Timeout = 20;
    $mail->SMTPDebug = 0; // Never expose SMTP diagnostics to public visitors.

    $mail->setFrom($smtpUsername, 'Myanmar GSS Website');
    $mail->addAddress($recipient);
    $mail->addReplyTo($email, $nameForHeader);

    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Message from ' . $nameForHeader;
    $mail->Body = "
        <div style=\"font-family:Arial,sans-serif;padding:20px;color:#333\">
            <h2 style=\"color:#b50909\">New Contact Form Submission</h2>
            <hr style=\"border:0;border-top:1px solid #eee;margin:15px 0\">
            <p><strong>Name:</strong> {$safeName}</p>
            <p><strong>Company:</strong> {$safeCompany}</p>
            <p><strong>Email:</strong> {$safeEmail}</p>
            <p><strong>Phone:</strong> {$safePhone}</p>
            <p><strong>Inquiry topic:</strong> {$safeInterest}</p>
            <p><strong>Message:</strong></p>
            <div style=\"background:#f9f9f9;padding:15px;border-left:4px solid #b50909;margin-top:10px\">
                {$safeMessage}
            </div>
        </div>
    ";

    $mail->AltBody =
        "New Contact Form Submission\n\n" .
        "Name: {$name}\n" .
        "Company: {$company}\n" .
        "Email: {$email}\n" .
        "Phone: {$phone}\n" .
        "Inquiry topic: {$interest}\n\n" .
        "Message:\n{$message}";

    $mail->send();
    http_response_code(200);
    echo 'Message sent successfully!';
} catch (Throwable $e) {
    // Keep detailed errors in the server log only.
    error_log(
        'MMGSS contact form mail error [' . get_class($e) . ']: ' . $e->getMessage()
    );
    error_log('MMGSS SMTP ErrorInfo: ' . $mail->ErrorInfo);

    http_response_code(500);
    echo 'Message could not be sent. Please try again later.';
}
