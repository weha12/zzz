<?php
// mail.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:\xampp\htdocs\yyy\PHPMailer\PHPMailer\src\Exception.php';
require 'C:\xampp\htdocs\yyy\PHPMailer\PHPMailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\yyy\PHPMailer\PHPMailer\src\SMTP.php';

function sendOtpEmail($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ohayotalaga@gmail.com';
        $mail->Password = 'pmri dzeo iifb vmmy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('ohayotalaga@gmail.com', 'View');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'OTP for Password Reset';
        $mail->Body = "Use this OTP to reset your password: <b>$otp</b>";

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
