
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=UTF-8');

// Load PHPMailer from the root folder.
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request.'
    ]);
    exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (
    $name === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $subject === '' ||
    $message === ''
) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Please fill in all fields correctly.'
    ]);
    exit;
}

$mail = new PHPMailer(true);

try {
    // Gmail SMTP configuration.
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'skbadhai143@gmail.com';

    // Enter your NEW Gmail App Password here.
    $mail->Password   = 'vsum lbsh icnj hmyh';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(
        'skbadhai143@gmail.com',
        'Orovecta Contact Form'
    );

    $mail->addAddress('skbadhai143@gmail.com');
    $mail->addReplyTo($email, $name);

    $mail->isHTML(true);
    $mail->Subject = 'Orovecta Contact: ' . $subject;

    $mail->Body = '
        <h2>New Contact Inquiry - Orovecta</h2>
        <p><strong>Name:</strong> ' .
            htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Email:</strong> ' .
            htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Subject:</strong> ' .
            htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Message:</strong><br>' .
            nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) .
        '</p>
    ';

    $mail->AltBody =
        "Name: $name\nEmail: $email\nSubject: $subject\n\n$message";

    $mail->send();

    echo json_encode([
        'status' => 'success',
        'message' => 'Your message has been sent successfully. Thank you!'
    ]);

} catch (Exception $e) {
    error_log('Contact email error: ' . $mail->ErrorInfo);

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => 'Your message could not be sent. Please try again later.'
    ]);
}
