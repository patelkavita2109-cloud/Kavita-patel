<?php
// To use this script, you need PHPMailer.
// If you are using Composer, run: composer require phpmailer/phpmailer
// Then uncomment the following line:
// require 'vendor/autoload.php';

// If you downloaded PHPMailer manually, uncomment these lines and adjust the paths:
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'env.php';
loadEnv(__DIR__ . '/.env');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = htmlspecialchars($_POST['fullname'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');

    if (empty($fullname) || empty($email) || empty($message)) {
        echo json_encode(["status" => "error", "message" => "Please complete all fields."]);
        exit;
    }

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo json_encode(["status" => "error", "message" => "PHPMailer is not installed or included! Please check the require paths at the top of mail.php."]);
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 0;                      // Disable debug output for JSON response
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = getenv('MAIL_HOST');                       // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = getenv('MAIL_USERNAME');              // SMTP username
        $mail->Password   = getenv('MAIL_PASSWORD');;                    // SMTP password (use Gmail App Password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // Enable implicit TLS encryption
        $mail->Port       = 465;                                    // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        // Recipients
        $mail->setFrom(getenv('MAIL_USERNAME'), getenv('MAIL_FROM_NAME'));
        $mail->addAddress(getenv('MAIL_USERNAME'));               // Add a recipient
        $mail->addReplyTo($email, $fullname);

        // Content
        $mail->isHTML(true);                                        // Set email format to HTML
        $mail->Subject = 'New Contact Form Submission from ' . $fullname;
        $mail->Body    = "<b>Name:</b> {$fullname}<br><b>Email:</b> {$email}<br><br><b>Message:</b><br>" . nl2br($message);
        $mail->AltBody = "Name: {$fullname}\nEmail: {$email}\n\nMessage:\n{$message}";

        $mail->send();
        echo json_encode(["status" => "success", "message" => "Thank you! Your message has been sent successfully."]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
