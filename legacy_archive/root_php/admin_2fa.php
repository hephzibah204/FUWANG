<?php
session_start();
include_once("db_conn.php");

if (!isset($_SESSION['pending_2fa_username'])) {
    header('Location: admin_login.php');
    exit;
}

$error_message = '';
$username = $_SESSION['pending_2fa_username'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp = htmlspecialchars(trim($_POST['otp']));
    
    $stmt = $conn->prepare("SELECT 2fa_otp, 2fa_expiry FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($db_otp, $db_expiry);
    $stmt->fetch();
    $stmt->close();
    
    if ($otp === $db_otp && strtotime($db_expiry) > time()) {
        // Success! Log the user in
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['login_attempts'] = 0;
        
        // Clear OTP from DB
        $clear_stmt = $conn->prepare("UPDATE admins SET 2fa_otp = NULL, 2fa_expiry = NULL WHERE username = ?");
        $clear_stmt->bind_param("s", $username);
        $clear_stmt->execute();
        
        unset($_SESSION['pending_2fa_username']);
        log_admin_action("Admin Login", "Successful admin login with 2FA");
        
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error_message = 'Invalid or expired OTP code.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: 'Outfit', sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); padding: 2rem; border-radius: 20px; width: 100%; max-width: 400px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
        h2 { font-weight: 700; color: #6366f1; margin-bottom: 1.5rem; text-align: center; }
        .form-control { background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; border-radius: 12px; padding: 0.8rem; text-align: center; font-size: 1.5rem; letter-spacing: 0.5rem; }
        .form-control:focus { background: rgba(255, 255, 255, 0.15); border-color: #6366f1; color: white; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2); }
        .btn-primary { background: #6366f1; border: none; border-radius: 12px; padding: 0.8rem; font-weight: 600; transition: all 0.3s; }
        .btn-primary:hover { background: #4f46e5; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4); }
        .alert { border-radius: 12px; background: rgba(248, 113, 113, 0.1); border: 1px solid rgba(248, 113, 113, 0.2); color: #f87171; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Verify Account</h2>
        <p class="text-center text-muted">A verification code has been sent to your admin email address.</p>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group mb-4">
                <input type="text" name="otp" class="form-control" placeholder="000000" maxlength="6" required autofocus autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Verify Code</button>
        </form>
        
        <div class="text-center mt-4">
            <a href="admin_login.php" class="text-muted small">Back to Login</a>
        </div>
    </div>
</body>
</html>