<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'PHPMailer/PHPMailerAutoload.php';

$mail = new PHPMailer;
$mail->SMTPDebug = 3; // Enable verbose debug output

$mail->isSMTP();
$mail->Host = 'mail.futuredigitaltechltd.net.ng';
$mail->SMTPAuth = true;
$mail->Username = 'futuretechimk@futuredigitaltechltd.net.ng';
$mail->Password = 'Futuretechimk';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom('futuretechimk@futuredigitaltechltd.net.ng', 'SMTP Test');
$mail->addAddress('futuretechimk@futuredigitaltechltd.net.ng'); // Send to self for testing

$mail->isHTML(true);
$mail->Subject = 'SMTP Delivery Test';
$mail->Body    = 'This is a test email to confirm SMTP delivery works within the app.';
$mail->AltBody = 'This is a test email to confirm SMTP delivery works within the app.';

echo "Attempting to send email...<br>";

if(!$mail->send()) {
    echo 'Message could not be sent.<br>';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message has been sent successfully!';
}
?>