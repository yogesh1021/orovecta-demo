
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer manually.
// Expected folder: project/PHPMailer/src/
$phpMailerPath = __DIR__ . '/PHPMailer/';

if (file_exists($phpMailerPath . 'src/PHPMailer.php')) {
    $phpMailerPath .= 'src/';
}

$requiredFiles = [
    $phpMailerPath . 'Exception.php',
    $phpMailerPath . 'PHPMailer.php',
    $phpMailerPath . 'SMTP.php'
];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        http_response_code(500);
        exit('PHPMailer file not found: ' .
            htmlspecialchars(basename($file), ENT_QUOTES, 'UTF-8'));
    }
}

require_once $requiredFiles[0];
require_once $requiredFiles[1];
require_once $requiredFiles[2];

// Only accept form submissions.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: inquiry.html');
    exit;
}

// Collect form values.
$name    = trim($_POST['name'] ?? '');
$mobile  = trim($_POST['mobile'] ?? '');
$email   = trim($_POST['email'] ?? '');
$service = trim($_POST['service'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validate required fields.
if (
    $name === '' ||
    $mobile === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $service === '' ||
    $message === ''
) {
    http_response_code(400);
    exit('Please complete all required fields correctly.');
}

// Validate field lengths.
if (
    strlen($name) > 150 ||
    strlen($mobile) > 30 ||
    strlen($email) > 254 ||
    strlen($service) > 150 ||
    strlen($message) > 10000
) {
    http_response_code(400);
    exit('One or more fields exceed the allowed length.');
}

// Validate mobile number.
if (!preg_match('/^[0-9+() \-]{10,30}$/', $mobile)) {
    http_response_code(400);
    exit('Please enter a valid mobile number.');
}

// Validate service selection.
$allowedServices = [
    'Metalworking Fluids',
    'Specialty Lubricants',
    'MRO Solutions',
    'MRO Lubricants',
    'Customized Lubrication',
    'Customized Lubrication Solutions',
    'Other'
];

if (!in_array($service, $allowedServices, true)) {
    http_response_code(400);
    exit('Please select a valid service or product.');
}

// Gmail configuration.
$smtpUsername = 'skbadhai143@gmail.com';

// IMPORTANT:
// Replace this placeholder with your NEW Google App Password.
// Do not use your regular Gmail password.
$smtpPassword = 'vsum lbsh icnj hmyh';

$mail = new PHPMailer(true);

try {
    // SMTP configuration.
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUsername;
    $mail->Password   = $smtpPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';
    $mail->Timeout    = 20;

    // SMTP debugging is written to Apache's error log.
    // Do not display SMTP conversations to website visitors.
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function ($str, $level) {
        error_log("Orovecta SMTP DEBUG [$level]: $str");
    };

    // Sender and recipient.
    $mail->setFrom(
        $smtpUsername,
        'Orovecta Website Inquiry'
    );

    $mail->addAddress($smtpUsername);
    $mail->addReplyTo($email, $name);

    // Safely escape user-submitted values for HTML.
    $safeName = htmlspecialchars(
        $name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'
    );

    $safeMobile = htmlspecialchars(
        $mobile, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'
    );

    $safeEmail = htmlspecialchars(
        $email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'
    );

    $safeService = htmlspecialchars(
        $service, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'
    );

    $safeMessage = nl2br(htmlspecialchars(
        $message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'
    ));

    // Email subject and HTML body.
    $mail->isHTML(true);
    $mail->Subject = 'New Product & Service Inquiry - Orovecta';

    $mail->Body = "
    <!DOCTYPE html>
    <html>
    <body style='font-family:Arial,sans-serif;color:#333;'>
        <div style='max-width:650px;margin:auto;'>
            <h2 style='background:#173d5c;color:#fff;padding:18px;'>
                New Inquiry
            </h2>

            <p>A visitor submitted the website inquiry form.</p>

            <table cellpadding='10' cellspacing='0' border='1'
                   style='border-collapse:collapse;width:100%;'>
                <tr>
                    <th align='left'>Full Name</th>
                    <td>{$safeName}</td>
                </tr>
                <tr>
                    <th align='left'>Mobile Number</th>
                    <td>{$safeMobile}</td>
                </tr>
                <tr>
                    <th align='left'>Email Address</th>
                    <td>{$safeEmail}</td>
                </tr>
                <tr>
                    <th align='left'>Service / Product</th>
                    <td>{$safeService}</td>
                </tr>
                <tr>
                    <th align='left'>Message</th>
                    <td>{$safeMessage}</td>
                </tr>
            </table>

            <p style='margin-top:20px;'>
                <strong>Orovecta</strong><br>
                Industrial Lubrication. Engineered for Performance.
            </p>
        </div>
    </body>
    </html>";

    // Plain-text alternative.
    $mail->AltBody =
        "New Orovecta Inquiry\n\n" .
        "Name: $name\n" .
        "Mobile: $mobile\n" .
        "Email: $email\n" .
        "Service: $service\n\n" .
        "Message:\n$message";

    // Send the email.
    $mail->send();

    // Redirect after successful SMTP submission.
    header('Location: inquiry.html?status=success#inquiry');
    exit;

} catch (Exception $e) {
    // Record the actual error privately.
    error_log('Orovecta email error: ' . $mail->ErrorInfo);

    http_response_code(500);

    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Inquiry Error | Orovecta</title>
        <link href="assets/vendor/bootstrap/css/bootstrap.min.css"
              rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <div class="alert alert-danger">
                <h4>Unable to Send Inquiry</h4>
                <p>Your inquiry could not be sent. Please try again later.</p>
                <p>The technical details have been recorded in the
                   Apache error log.</p>
                <a href="inquiry.html#inquiry"
                   class="btn btn-primary">Back to Inquiry Form</a>
            </div>
        </div>
    </body>
    </html>';

    exit;
}
