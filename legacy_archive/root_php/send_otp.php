<?php
require 'PHPMailer/PHPMailerAutoload.php';
include_once("db_conn.php");

header('Content-Type: application/json');

if (!isset($_POST['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Email is required.']);
    exit;
}

$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

// Check if the email exists in the database (using customers2 as seen in login)
$stmt = $conn->prepare("SELECT fullname FROM customers2 WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $fullname = $user['fullname'];
    
    $otp = rand(100000, 999999); // Generate a 6-digit OTP
    $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes')); // Set OTP expiry time to 15 minutes

    // Use REPLACE INTO to update existing OTP for this email
    $stmt = $conn->prepare("REPLACE INTO verification_otp (email, otp, expiry) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $otp, $expiry);

    if ($stmt->execute()) {
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'mail.futuredigitaltechltd.net.ng';
        $mail->SMTPAuth = true;
        $mail->Username = 'futuretechimk@futuredigitaltechltd.net.ng';
        $mail->Password = 'Futuretechimk';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('futuretechimk@futuredigitaltechltd.net.ng', 'Fuwa..NG Support');
        $mail->addAddress($email, $fullname);
        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password - Fuwa..NG';
        $mail->Body    = "
            <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                <h2 style='color: #6366f1;'>Password Reset</h2>
                <p>Hello <b>$fullname</b>,</p>
                <p>We received a request to reset your password for your <b>Fuwa..NG</b> account. Use the code below to proceed:</p>
                <div style='background: #f8fafc; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0;'>
                    <span style='font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #0f172a;'>$otp</span>
                </div>
                <p>This code will expire in 15 minutes. If you did not request this, please ignore this email.</p>
                <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
                <p style='font-size: 12px; color: #94a3b8; text-align: center;'>&copy; 2026 Fuwa..NG. All rights reserved.</p>
            </div>
        ";
        $mail->AltBody = "Hello $fullname, Your OTP code to reset your Fuwa..NG password is: $otp. This code will expire in 15 minutes.";

        if (!$mail->send()) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send reset code. Please try again later.']);
        } else {
            echo json_encode(['status' => 'success']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to generate security code.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'This email address is not registered.']);
}

$conn->close();
?>