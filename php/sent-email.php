<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: text/plain; charset=UTF-8');


//in hosting server cpanel 
 // $autoload = __DIR__ . '/../vendor/autoload.php';
 require_once __DIR__ . '/../vendor/autoload.php';



// if (!is_file($autoload)) {
//     http_response_code(500);
//     error_log('MMGSS contact form: Composer autoload file not found: ' . $autoload);
//     exit('The contact form is temporarily unavailable. Please contact the company directly.');
// }
// require_once $autoload;
    

$smtpHost     = 'mail.myanmargss.com';
$smtpUsername = 'no-reply@myanmargss.com';
$smtpPassword = 'MItLF5vJKNxqhAX5';
$recipient    = 'thwel4186@gmail.com';
$smtpPort     = 465;

// if (
//     $smtpPassword === '' ||
//     $smtpPassword === 'MItLF5vJKNxqhAX5'
// ) {
//     http_response_code(500);
//     error_log('MMGSS contact form: SMTP password is not configured in sent-email.php.');
//     exit('The contact form is temporarily unavailable. Please contact the company directly.');
// }

// if (
//     !filter_var($smtpUsername, FILTER_VALIDATE_EMAIL) ||
//     !filter_var($recipient, FILTER_VALIDATE_EMAIL) ||
//     trim($smtpHost) === ''
// ) {
//     http_response_code(500);
//     error_log('MMGSS contact form: Invalid SMTP configuration.');
//     exit('The contact form is temporarily unavailable. Please contact the company directly.');
// }

// // Accept POST requests only.
// if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
//     http_response_code(405);
//     header('Allow: POST');
//     exit('Please submit the contact form.');
// }

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

   
    if ($smtpPort === 465) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
    
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }

    $mail->Port = $smtpPort;
    $mail->CharSet = 'UTF-8';
    $mail->Timeout = 20;
    $mail->SMTPDebug = 0; // Never expose SMTP diagnostics to public visitors.

    $mail->setFrom($smtpUsername, 'Myanmar GSS Website');
    $mail->addAddress($recipient);
    $mail->addReplyTo($email, $nameForHeader);

    // $mail->isHTML(true);
    // $mail->Subject = 'New Contact Form Message from ' . $nameForHeader;
    // $mail->Body = "
    //     <div style=\"font-family:Arial,sans-serif;padding:20px;color:#333\">
    //         <h2 style=\"color:#b50909\">New Contact Form Submission</h2>
    //         <hr style=\"border:0;border-top:1px solid #eee;margin:15px 0\">
    //         <p><strong>Name:</strong> {$safeName}</p>
    //         <p><strong>Company:</strong> {$safeCompany}</p>
    //         <p><strong>Email:</strong> {$safeEmail}</p>
    //         <p><strong>Phone:</strong> {$safePhone}</p>
    //         <p><strong>Inquiry topic:</strong> {$safeInterest}</p>
    //         <p><strong>Message:</strong></p>
    //         <div style=\"background:#f9f9f9;padding:15px;border-left:4px solid #b50909;margin-top:10px\">
    //             {$safeMessage}
    //         </div>
    //     </div>
    // ";

    // $mail->AltBody =
    //     "New Contact Form Submission\n\n" .
    //     "Name: {$name}\n" .
    //     "Company: {$company}\n" .
    //     "Email: {$email}\n" .
    //     "Phone: {$phone}\n" .
    //     "Inquiry topic: {$interest}\n\n" .
    //     "Message:\n{$message}";

    
$mail->isHTML(true);
$mail->CharSet = 'UTF-8';
$mail->Subject = 'New Contact Form Submission - ' . $nameForHeader;

// Generate a reference for this notification.
// Use a database ID if you require guaranteed uniqueness.
$referenceNumber = 'GSS-' . date('Ymd-His');
$receivedDate = date('d F Y');
$receivedTime = date('H:i') . ' (MMT)';

// $safeMessage must already be escaped with htmlspecialchars().
$safeMessageHtml = nl2br($safeMessage);

$mail->Body = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Contact Form Submission - Myanmar GSS</title>
</head>

<body style="margin:0;padding:0;background-color:#edf0f3;
font-family:Arial,Helvetica,sans-serif;color:#263247;">

<table role="presentation" width="100%" cellspacing="0"
cellpadding="0" border="0" style="background-color:#edf0f3;">
<tr>
<td align="center" style="padding:20px 10px;">

<table role="presentation" width="700" cellspacing="0"
cellpadding="0" border="0"
style="width:100%;max-width:700px;background:#ffffff;">

<!-- COMPANY BRANDING HEADER -->
<tr>
<td style="padding:0;background:#ffffff;">
    <table role="presentation" width="100%" cellspacing="0"
    cellpadding="0" border="0">
    <tr>
        <td width="45%" valign="middle"
        style="padding:24px 20px 24px 28px;">
            <div style="font-size:25px;font-weight:bold;
            letter-spacing:-1px;color:#a50c16;">
                Myanmar GSS
            </div>
            <div style="font-size:13px;font-weight:bold;
            color:#263247;margin-top:6px;">
                Myanmar GSS Co., Ltd.
            </div>
            <div style="font-size:10px;letter-spacing:1px;
            color:#858b96;margin-top:5px;">
                DOMESTIC RECRUITMENT SERVICES
            </div>
        </td>

        <td valign="middle" align="right"
        style="padding:24px 25px;background:#f8f5f5;
        border-left:1px solid #e5e7eb;">
            <div style="font-size:12px;line-height:1.8;
            font-weight:bold;color:#a50c16;">
                CONNECTING PEOPLE<br>
                CREATING OPPORTUNITIES
            </div>
            <div style="height:3px;width:45px;
            background:#b50909;margin:10px 0 0 auto;"></div>
        </td>
    </tr>
    </table>
</td>
</tr>

<!-- RED TITLE BANNER -->
<tr>
<td style="padding:0;background:#a50912;">
    <table role="presentation" width="100%" cellspacing="0"
    cellpadding="0" border="0">
    <tr>
        <td valign="middle" width="75" align="center"
        style="padding:25px 0 25px 18px;">
            <table role="presentation" cellspacing="0"
            cellpadding="0" border="0"
            style="background:#ffffff;border-radius:50%;
            width:62px;height:62px;">
            <tr>
                <td align="center" valign="middle"
                style="font-size:29px;color:#b50909;">
                    &#9993;
                </td>
            </tr>
            </table>
        </td>

        <td valign="middle"
        style="padding:23px 18px;">
            <div style="font-size:11px;letter-spacing:2px;
            font-weight:bold;color:#ffdede;margin-bottom:8px;">
                NEW CONTACT INQUIRY
            </div>

            <div style="font-size:24px;line-height:1.35;
            font-weight:bold;color:#ffffff;">
                New Contact Form Submission
            </div>

            <div style="font-size:13px;line-height:1.7;
            color:#ffffff;margin-top:8px;">
                A new message has been submitted through
                the Myanmar GSS website.
            </div>
        </td>
    </tr>
    </table>
</td>
</tr>

<!-- RECEIVED DATE -->
<tr>
<td style="padding:20px 25px 0;">
    <table role="presentation" width="100%" cellspacing="0"
    cellpadding="0" border="0"
    style="background:#fff5f5;border:1px solid #f1dede;">
    <tr>
        <td style="padding:13px 15px;">
            <div style="font-size:10px;font-weight:bold;
            letter-spacing:1px;color:#858b96;">
                INQUIRY REFERENCE
            </div>
            <div style="font-size:15px;font-weight:bold;
            color:#a50912;margin-top:5px;">
                {$referenceNumber}
            </div>
        </td>
        <td align="right" style="padding:13px 15px;">
            <div style="font-size:10px;font-weight:bold;
            letter-spacing:1px;color:#858b96;">
                RECEIVED ON
            </div>
            <div style="font-size:13px;font-weight:bold;
            color:#263247;margin-top:5px;">
                {$receivedDate}
            </div>
            <div style="font-size:12px;color:#555d6a;
            margin-top:4px;">
                {$receivedTime}
            </div>
        </td>
    </tr>
    </table>
</td>
</tr>

<!-- MAIN CONTENT CARD -->
<tr>
<td style="padding:18px 15px 0;">
    <table role="presentation" width="100%" cellspacing="0"
    cellpadding="0" border="0"
    style="background:#ffffff;border:1px solid #e7e9ed;">

    <!-- CUSTOMER INFORMATION HEADING -->
    <tr>
        <td style="padding:22px 20px 16px;">
            <table role="presentation" width="100%"
            cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td width="5" style="background:#c5101b;"></td>
                <td style="padding-left:12px;">
                    <div style="font-size:20px;font-weight:bold;
                    color:#17243b;">
                        Customer Information
                    </div>
                </td>
            </tr>
            </table>
        </td>
    </tr>

    <!-- CUSTOMER DETAILS -->
    <tr>
        <td style="padding:0 20px;">
            <table role="presentation" width="100%"
            cellspacing="0" cellpadding="0" border="0">

            <tr>
                <td width="35%" style="padding:13px 8px;
                border-top:1px solid #e7e9ed;
                color:#687286;font-size:13px;">
                    Full Name
                </td>
                <td style="padding:13px 8px;
                border-top:1px solid #e7e9ed;
                color:#17243b;font-size:14px;font-weight:bold;">
                    {$safeName}
                </td>
            </tr>

            <tr>
                <td style="padding:13px 8px;
                border-top:1px solid #e7e9ed;
                color:#687286;font-size:13px;">
                    Company
                </td>
                <td style="padding:13px 8px;
                border-top:1px solid #e7e9ed;
                color:#17243b;font-size:14px;">
                    {$safeCompany}
                </td>
            </tr>

            <tr>
                <td style="padding:13px 8px;
                border-top:1px solid #e7e9ed;
                color:#687286;font-size:13px;">
                    Email Address
                </td>
                <td style="padding:13px 8px;
                border-top:1px solid #e7e9ed;
                font-size:14px;overflow-wrap:anywhere;">
                    <a href="mailto:{$safeEmail}"
                    style="color:#1769c2;text-decoration:underline;">
                        {$safeEmail}
                    </a>
                </td>
            </tr>

            <tr>
                <td style="padding:13px 8px;
                border-top:1px solid #e7e9ed;
                color:#687286;font-size:13px;">
                    Phone Number
                </td>
                <td style="padding:13px 8px;
                border-top:1px solid #e7e9ed;
                color:#17243b;font-size:14px;">
                    {$safePhone}
                </td>
            </tr>

            <tr>
                <td style="padding:13px 8px;
                border-top:1px solid #e7e9ed;
                border-bottom:1px solid #e7e9ed;
                color:#687286;font-size:13px;">
                    Inquiry Topic
                </td>
                <td style="padding:13px 8px;
                border-top:1px solid #e7e9ed;
                border-bottom:1px solid #e7e9ed;">
                    <span style="display:inline-block;
                    background:#fff0f0;color:#a50912;
                    font-size:12px;font-weight:bold;
                    padding:7px 10px;">
                        {$safeInterest}
                    </span>
                </td>
            </tr>

            </table>
        </td>
    </tr>

    <!-- MESSAGE HEADING -->
    <tr>
        <td style="padding:25px 20px 15px;">
            <table role="presentation" width="100%"
            cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td width="5" style="background:#c5101b;"></td>
                <td style="padding-left:12px;">
                    <div style="font-size:20px;font-weight:bold;
                    color:#17243b;">
                        Message Details
                    </div>
                </td>
            </tr>
            </table>
        </td>
    </tr>

    <!-- MESSAGE BODY -->
    <tr>
        <td style="padding:0 20px 20px;">
            <table role="presentation" width="100%"
            cellspacing="0" cellpadding="0" border="0"
            style="background:#fff3f3;border:1px solid #f8e6e6;">
            <tr>
                <td valign="top" width="48"
                style="padding:18px 0 18px 15px;
                color:#b50909;font-size:22px;">
                    &#128172;
                </td>
                <td style="padding:18px 15px 18px 8px;
                color:#30394b;font-size:14px;
                line-height:1.9;overflow-wrap:anywhere;
                word-break:break-word;">
                    <div style="font-weight:bold;
                    margin-bottom:8px;color:#17243b;">
                        Customer Message
                    </div>
                    {$safeMessageHtml}
                </td>
            </tr>
            </table>
        </td>
    </tr>

    <!-- ACTION REQUIRED NOTICE -->
    <tr>
        <td style="padding:0 20px 22px;">
            <table role="presentation" width="100%"
            cellspacing="0" cellpadding="0" border="0"
            style="background:#e8f2ff;border:1px solid #d8e8ff;">
            <tr>
                <td width="55" align="center" valign="middle"
                style="padding:15px 0 15px 10px;
                color:#1769c2;font-size:25px;font-weight:bold;">
                    &#9432;
                </td>
                <td style="padding:15px 15px 15px 10px;">
                    <div style="font-size:14px;font-weight:bold;
                    color:#17458c;margin-bottom:6px;">
                        Action Required
                    </div>
                    <div style="font-size:12px;line-height:1.8;
                    color:#354762;">
                        Please review this inquiry and respond
                        to the customer as soon as possible.
                        You can reply directly to this email
                        using your email client's
                        <strong>Reply</strong> function.
                    </div>
                </td>
            </tr>
            </table>
        </td>
    </tr>

    </table>
</td>
</tr>

<!-- COMPANY FOOTER -->
<tr>
<td align="center" style="padding:24px 15px 28px;">
    <div style="font-size:16px;font-weight:bold;color:#a50912;">
        Myanmar GSS Co., Ltd.
    </div>
    <div style="font-size:10px;letter-spacing:2px;
    color:#858b96;margin-top:6px;">
        DOMESTIC RECRUITMENT SERVICES
    </div>

    <table role="presentation" cellspacing="0"
    cellpadding="0" border="0" style="margin-top:16px;">
    <tr>
        <td align="center" style="padding:5px 8px;
        color:#687286;font-size:11px;">
            Yangon, Myanmar
        </td>
        <td style="color:#b4b8c1;">|</td>
        <td align="center" style="padding:5px 8px;
        color:#687286;font-size:11px;">
            Myanmar GSS Website
        </td>
    </tr>
    </table>

    <div style="font-size:10px;line-height:1.8;
    color:#9297a1;margin-top:12px;">
        This is an automated notification generated by
        the Myanmar GSS website.
    </div>
</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>
HTML;

$mail->AltBody =
    "MYANMAR GSS CO., LTD.\n" .
    "NEW CONTACT FORM SUBMISSION\n\n" .
    "Reference: {$referenceNumber}\n" .
    "Received: {$receivedDate} {$receivedTime}\n\n" .
    "CUSTOMER INFORMATION\n" .
    "Full Name: {$name}\n" .
    "Company: {$company}\n" .
    "Email: {$email}\n" .
    "Phone: {$phone}\n" .
    "Inquiry Topic: {$interest}\n\n" .
    "MESSAGE DETAILS\n" .
    "{$message}\n\n" .
    "ACTION REQUIRED\n" .
    "Please review this inquiry and respond to the customer.\n";


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
