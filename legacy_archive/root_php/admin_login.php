<?php
session_start();
include_once("db_conn.php");

// Ensure secure session configuration
session_regenerate_id(true); // Regenerate session ID to prevent session fixation
ini_set('session.cookie_secure', '1'); // Ensure cookies are sent over HTTPS
ini_set('session.cookie_httponly', '1'); // Ensure cookies are only accessible via HTTP
ini_set('session.use_strict_mode', '1'); // Ensure PHP is in strict session mode

// Prevent brute force attacks
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['last_attempt_time'])) {
    $_SESSION['last_attempt_time'] = time();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Throttle excessive login attempts
    $time_since_last_attempt = time() - $_SESSION['last_attempt_time'];
    if ($_SESSION['login_attempts'] >= 3 && $time_since_last_attempt < 60) {
        echo '<div class="alert alert-danger" role="alert">Too many login attempts. Please try again later.</div>';
    } else {
        $_SESSION['last_attempt_time'] = time();

        // Retrieve and sanitize form inputs
        $username = htmlspecialchars(trim($_POST['username']));
        $password = htmlspecialchars(trim($_POST['password']));

        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("SELECT email, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($admin_email, $hashed_password);
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                // Generate OTP
                $otp = rand(100000, 999999);
                $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                
                // Update admin with OTP
                $update_stmt = $conn->prepare("UPDATE admins SET 2fa_otp = ?, 2fa_expiry = ? WHERE username = ?");
                $update_stmt->bind_param("sss", $otp, $expiry, $username);
                $update_stmt->execute();
                
                // Send OTP via Email
                require 'PHPMailer/PHPMailerAutoload.php';
                $mail = new PHPMailer;
                $mail->isSMTP();
                $mail->Host = 'mail.futuredigitaltechltd.net.ng';
                $mail->SMTPAuth = true;
                $mail->Username = 'futuretechimk@futuredigitaltechltd.net.ng';
                $mail->Password = 'Futuretechimk';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                $mail->setFrom('support@futuredigitaltechltd.net.ng', 'Fuwa..NG Admin');
                $mail->addAddress($admin_email);
                $mail->isHTML(true);
                $mail->Subject = 'Admin Login OTP';
                $mail->Body    = "<h1>Verification Code</h1><p>Your admin login OTP is: <b>$otp</b></p><p>This code expires in 10 minutes.</p>";
                
                if($mail->send()) {
                    $_SESSION['pending_2fa_username'] = $username;
                    header('Location: admin_2fa.php');
                    exit;
                } else {
                    $error_message = 'Failed to send OTP. Please contact support.';
                }
            } else {
                // Failed login
                $_SESSION['login_attempts']++;
                $error_message = 'Incorrect username or password.';
            }
        } else {
            // Failed login
            $_SESSION['login_attempts']++;
            $error_message = 'Incorrect username or password.';
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://maxcdn.bootstrapcdn.com; style-src 'self' https://maxcdn.bootstrapcdn.com;">
</head>
<body>

    <!-- Embed external URL using iframe -->
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <h2 class="text-center">Admin Login</h2>
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                <form action="#" method="POST">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>