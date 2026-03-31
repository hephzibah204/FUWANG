<?php
include_once("db_conn.php");
if (isset($_COOKIE['email'])) {
    header("Location:welcome_pin.php");
    exit();
}
require 'PHPMailer/PHPMailerAutoload.php';

$developer_id = isset($_SESSION['developerId']) ? $_SESSION['developerId'] : null;
if ($developer_id === null) {
    die("Sorry, you can't connect securely. Contact the owner at 08113910395.");
}
$block_css = !$developer_id;

$ip = $_SERVER['REMOTE_ADDR'];
$response = ['status' => 'error', 'message' => 'An error occurred'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_COOKIE['email'])) {
    $response['status'] = 'success';
    $response['redirect'] = 'welcome_pin.php';
    echo json_encode($response);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["email"]) && isset($_POST["password"])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $response['message'] = "Invalid CSRF token.";
        echo json_encode($response); exit();
    }
    $email    = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST["password"]);
    $forbiddenChars = ['&','$','<','>','{','}','\\',';','!','?','[',']'];
    foreach ($_POST as $key => $value) {
        if ($key !== 'password') {
            foreach ($forbiddenChars as $char) {
                if (strpos($value, $char) !== false) {
                    $response['message'] = "Forbidden character detected in $key input.";
                    echo json_encode($response); exit();
                }
            }
        }
    }
    if (!isset($_SESSION['login_attempts'])) { $_SESSION['login_attempts'] = 0; }
    $max_attempts = 6;
    $ip_address   = $_SERVER['REMOTE_ADDR'];
    $current_time = time();
    $query = "SELECT * FROM login_attempts WHERE ip_address = ?";
    $stmt  = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $ip_address);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $last_login_time = strtotime($row['last_login']);
        $attempts = $row['attempts'];
        if ($attempts >= $max_attempts && ($current_time - $last_login_time < 24 * 60 * 60)) {
            $response['message'] = "Your device is temporarily locked due to multiple failed login attempts. Please try again after some hours.";
            echo json_encode($response); exit();
        }
    }
    $query = "SELECT * FROM customers2 WHERE email = ?";
    $stmt  = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['id']              = $user['id'];
            $_SESSION['email']           = $user['email'];
            $_SESSION['fullname']        = $user['fullname'];
            $_SESSION['transaction_pin'] = $user['transaction_pin'];
            $cookie_expire_time = time() + (86400 * 30);
            setcookie("email",    $user['email'],    $cookie_expire_time, "/");
            setcookie("fullname", $user['fullname'], $cookie_expire_time, "/");
            updateUserOnlineStatus($user['id'], "online");
            $query = "DELETE FROM login_attempts WHERE ip_address = ?";
            $stmt  = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $ip_address);
            mysqli_stmt_execute($stmt);
            sendLoginNotificationEmail($user['email'], $user['fullname']);
            $response['status']   = 'success';
            $response['redirect'] = 'dashboard.php';
            echo json_encode($response); exit();
        } else {
            handleFailedLogin($ip_address);
            $response['message'] = "Please check your password and try again.";
            echo json_encode($response); exit();
        }
    } else {
        handleFailedLogin($ip_address);
        $response['message'] = "Please check your email and try again.";
        echo json_encode($response); exit();
    }
}

function handleFailedLogin($ip_address) {
    global $conn;
    $query = "INSERT INTO login_attempts (ip_address, attempts, last_login) VALUES (?, 1, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_login = CURRENT_TIMESTAMP";
    $stmt  = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $ip_address);
    mysqli_stmt_execute($stmt);
}
function updateUserOnlineStatus($id, $status) {
    global $conn;
    $query = "UPDATE customers1 SET online_status = ? WHERE id = ?";
    $stmt  = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    mysqli_stmt_execute($stmt);
}
function sendLoginNotificationEmail($userEmail, $userName) {
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host       = 'mail.dataverify.com.ng;das103.truehost.cloud';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'support@dataverify.com.ng';
    $mail->Password   = '';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;
    $mail->setFrom('support@futuredigitaltechltd.net.ng', 'Fuwa..NG Support');
    $mail->addAddress($userEmail, $userName);
    $mail->isHTML(true);
    $mail->Subject = 'Login Notification - Fuwa..NG';
    $mail->Body    = "<h1>Login Notification</h1><p>Hello $userName,</p><p>You have successfully logged into your account. If you did not perform this action, please contact us immediately.</p><p>Contact us via WhatsApp: <a href='https://wa.me/2348113910395'>Chat with us</a></p><p>Regards,<br>Fuwa..NG Support Team</p>";
    $mail->AltBody = "Hello $userName,\nYou have successfully logged in to your Fuwa..NG account. Contact us at +2348113910395 if this wasn't you.";
    if (!$mail->send()) { error_log('Mailer Error: ' . $mail->ErrorInfo); }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="theme-color" content="#020617">
    <meta name="description" content="Login to Fuwa..NG – Your VTU & Verification Account">
    <title>Sign In – Fuwa..NG</title>
    <link rel="icon" href="images/icon_logo.png">
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4f46e5;
            --secondary: #06b6d4;
            --bg-dark: #020617;
            --surface-dark: #0f172a;
            --surface-lighter: #1e293b;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --error: #f87171;
            --success: #4ade80;
            --radius-lg: 24px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body { 
            font-family: 'Outfit', sans-serif; 
            background: var(--bg-dark); 
            color: var(--text-primary); 
            display: flex; 
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Background Effects */
        .bg-mesh {
            position: fixed;
            inset: 0;
            z-index: -2;
            background: radial-gradient(circle at 50% 50%, #0f172a 0%, #020617 100%);
        }
        .bg-glow {
            position: fixed;
            width: 60vw;
            height: 60vw;
            border-radius: 50%;
            filter: blur(120px);
            z-index: -1;
            opacity: 0.15;
            pointer-events: none;
            animation: moveGlow 20s infinite alternate;
        }
        .blob-1 { top: -20%; left: -10%; background: var(--primary); }
        @keyframes moveGlow {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(10%, 10%) scale(1.2); }
        }

        /* ── LEFT PANEL ── */
        .auth-left {
            flex: 1; display: flex; flex-direction: column; justify-content: center;
            padding: 4rem;
            position: relative;
        }
        .left-brand { 
            font-size: 1.5rem; font-weight: 800; margin-bottom: 3rem; 
            text-decoration: none; color: #fff; 
            display: flex; align-items: center; gap: 12px;
        }
        .left-brand i { color: var(--primary); font-size: 1.75rem; }
        .left-brand span { color: var(--primary); }
        
        .left-headline { 
            font-size: clamp(2rem, 4vw, 3.5rem); font-weight: 900; 
            line-height: 1.1; margin-bottom: 1.5rem; letter-spacing: -0.04em;
        }
        .gradient-text {
            background: linear-gradient(to right, #fff 20%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .left-sub { color: var(--text-secondary); font-size: 1.1rem; line-height: 1.6; max-width: 420px; margin-bottom: 2.5rem; }
        
        .trust-pills { display: flex; flex-wrap: wrap; gap: 0.75rem; }
        .trust-pill {
            display: flex; align-items: center; gap: 0.6rem;
            background: var(--glass-bg); border: 1px solid var(--glass-border);
            border-radius: 50px; padding: 0.6rem 1.25rem; font-size: 0.875rem; font-weight: 600; color: var(--text-secondary);
        }
        .trust-pill i { color: var(--primary-light); }

        /* ── RIGHT PANEL ── */
        .auth-right {
            width: 500px; min-height: 100vh;
            display: flex; flex-direction: column; justify-content: center;
            padding: 4rem;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(20px);
            border-left: 1px solid var(--glass-border);
            position: relative;
        }
        .form-top-link { position: absolute; top: 2.5rem; right: 2.5rem; font-size: 0.875rem; color: var(--text-secondary); }
        .form-top-link a { color: var(--primary-light); text-decoration: none; font-weight: 600; }

        .form-eyebrow { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--primary-light); margin-bottom: 0.75rem; }
        .form-title { font-size: 2.25rem; font-weight: 800; margin-bottom: 0.5rem; letter-spacing: -0.02em; }
        .form-subtitle { color: var(--text-secondary); font-size: 1rem; margin-bottom: 2.5rem; }

        /* ── FIELDS ── */
        .field-group { margin-bottom: 1.5rem; }
        .field-label { display: block; font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.6rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .field-wrap { position: relative; }
        .field-wrap i.field-icon { position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-size: 1rem; }
        .field-wrap input {
            width: 100%; background: var(--glass-bg);
            border: 1px solid var(--glass-border); border-radius: 16px;
            padding: 1rem 1.25rem 1rem 3.25rem;
            color: #fff; font-family: 'Outfit', sans-serif; font-size: 1rem;
            outline: none; transition: all 0.3s ease;
        }
        .field-wrap input:focus { border-color: var(--primary); background: rgba(99, 102, 241, 0.05); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
        .toggle-eye { position: absolute; right: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary); cursor: pointer; }

        .field-row { display: flex; justify-content: flex-end; margin-bottom: 2rem; }
        .forgot-link { font-size: 0.875rem; color: var(--primary-light); text-decoration: none; font-weight: 600; }
        .forgot-link:hover { text-decoration: underline; }

        .submit-btn {
            width: 100%;
            padding: 1.125rem;
            background: var(--primary);
            color: #fff; font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700;
            border: none; border-radius: 16px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 0.75rem;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
            transition: all 0.3s ease;
        }
        .submit-btn:hover { transform: translateY(-4px); background: var(--primary-dark); box-shadow: 0 15px 30px -5px rgba(99, 102, 241, 0.5); }

        .divider { display: flex; align-items: center; gap: 1rem; margin: 2rem 0; }
        .divider span { font-size: 0.75rem; color: var(--text-secondary); white-space: nowrap; text-transform: uppercase; letter-spacing: 0.1em; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--glass-border); }

        .register-link { text-align: center; font-size: 0.9375rem; color: var(--text-secondary); }
        .register-link a { color: var(--primary-light); text-decoration: none; font-weight: 600; }

        #pageLoader { position: fixed; inset: 0; z-index: 9999; background: var(--bg-dark); display: flex; align-items: center; justify-content: center; }
        .loader-ring { width: 48px; height: 48px; border-radius: 50%; border: 3px solid var(--glass-border); border-top-color: var(--primary); animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 1024px) {
            body { flex-direction: column; }
            .auth-left { padding: 4rem 2rem 2rem; }
            .auth-right { width: 100%; padding: 3rem 2rem; border-left: none; border-top: 1px solid var(--glass-border); }
        }
    </style>
</head>
<body>

<div class="bg-mesh"></div>
<div class="bg-glow blob-1"></div>

<div id="pageLoader"><div class="loader-ring"></div></div>

<div class="auth-left">
    <a href="index.php" class="left-brand">
        <i class="fa-solid fa-bolt"></i> Fuwa<span>..NG</span>
    </a>
    <h1 class="left-headline">
        Secure Access to your <br><span class="gradient-text">Digital Hub</span>
    </h1>
    <p class="left-sub">
        Sign in to manage your digital assets, verify identities, and process secure telecom transactions.
    </p>
    <div class="trust-pills">
        <div class="trust-pill"><i class="fas fa-shield-halved"></i> Bank-grade security</div>
        <div class="trust-pill"><i class="fas fa-bolt"></i> Instant settlement</div>
        <div class="trust-pill"><i class="fas fa-users"></i> 100k+ Active users</div>
    </div>
</div>

<div class="auth-right">
    <div class="form-top-link">No account? <a href="user_register.php">Register →</a></div>

    <div class="form-eyebrow">Authentication</div>
    <h2 class="form-title">Sign In</h2>
    <p class="form-subtitle">Welcome back! Please enter your credentials.</p>

    <form id="loginForm" action="#" method="POST" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <div class="field-group">
            <label class="field-label">Email Address</label>
            <div class="field-wrap">
                <i class="fas fa-envelope field-icon"></i>
                <input type="email" id="email" name="email" placeholder="john@example.com" maxlength="50" required>
            </div>
        </div>

        <div class="field-group">
            <label class="field-label">Password</label>
            <div class="field-wrap">
                <i class="fas fa-lock field-icon"></i>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
                <i class="fas fa-eye toggle-eye" id="togglePwd" onclick="togglePassword('password','togglePwd')"></i>
            </div>
        </div>

        <div class="field-row">
            <a href="forgetpword.php" class="forgot-link">Forgot password?</a>
        </div>

        <button type="submit" class="submit-btn" id="submitBtn">
            Sign In to Account <i class="fas fa-arrow-right"></i>
        </button>

        <?php
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://sadeeqdata.com.ng/script_request/index_login.php");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $remote = curl_exec($ch);
        curl_close($ch);
        
        // Remove the specific licensed link while keeping the footer structure
        $filtered_remote = preg_replace('/<a[^>]*href=[^>]*>.*?LICENSED BY GSOFT CREATIVE SERVICES.*?<\/a>/is', '', $remote);
        echo $filtered_remote;
        ?>
    </form>

    <div class="divider"><span>New to Fuwa..NG?</span></div>
    <p class="register-link"><a href="user_register.php">Create a free account today</a></p>
</div>

<script>
    // Hide loader when page ready
    window.addEventListener('load', () => {
        const loader = document.getElementById('pageLoader');
        loader.style.opacity = '0';
        setTimeout(() => loader.style.display = 'none', 400);
    });

    // Toggle password visibility
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // AJAX form submission
    const form      = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        // Clear inline errors
        document.querySelectorAll('.inline-error').forEach(el => el.style.display = 'none');
        document.querySelectorAll('input').forEach(el => el.classList.remove('is-error'));

        const email    = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        let valid = true;

        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            document.getElementById('emailError').style.display = 'block';
            document.getElementById('email').classList.add('is-error');
            valid = false;
        }
        if (!password) {
            document.getElementById('passwordError').style.display = 'block';
            document.getElementById('password').classList.add('is-error');
            valid = false;
        }
        if (!valid) return;

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Signing in…</span>';

        const formData = new FormData(form);
        try {
            const res  = await fetch('', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.status === 'success') {
                submitBtn.innerHTML = '<i class="fas fa-check"></i> <span>Success!</span>';
                Swal.fire({
                    title: 'Welcome back!',
                    text: 'Redirecting to your dashboard…',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    background: '#0b1120',
                    color: '#f8fafc'
                }).then(() => window.location.href = data.redirect || 'dashboard.php');
            } else {
                Swal.fire({
                    title: 'Login Failed',
                    text: data.message || 'An error occurred. Please try again.',
                    icon: 'error',
                    background: '#0b1120',
                    color: '#f8fafc',
                    confirmButtonColor: '#1a76d1'
                });
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-arrow-right-to-bracket"></i> <span>Sign In</span>';
            }
        } catch (err) {
            Swal.fire({ title: 'Network Error', text: 'Please check your connection.', icon: 'error', background: '#0b1120', color: '#f8fafc', confirmButtonColor: '#1a76d1' });
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-arrow-right-to-bracket"></i> <span>Sign In</span>';
        }
    });
</script>
</body>
</html>