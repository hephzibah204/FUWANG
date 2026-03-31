<?php
include_once("db_conn.php");
ini_set('display_errors', 1); error_reporting(E_ALL);
require 'PHPMailer/PHPMailerAutoload.php';

$developer_id = isset($_SESSION['developerId']) ? $_SESSION['developerId'] : null;
if ($developer_id === null) {
    die("Sorry, you can't connect securely. Contact the owner at 07065371155.");
}
$block_css = !$developer_id;
$errors  = [];
$success = [];

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (isset($_COOKIE['email'])) {
    header("Location: welcome_pin.php"); exit();
}
if (isset($_GET['reseller_id'])) {
    $reseller_id = htmlspecialchars($_GET['reseller_id']);
    $_SESSION['reseller_id'] = $reseller_id;
} else {
    $reseller_id = $developer_id;
}
function generateReferralID($length = 8) { return bin2hex(random_bytes($length)); }

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["sign"])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid CSRF token.";
    }
    $image            = "default.png";
    $fullname         = htmlspecialchars($_POST['fullname']);
    $username         = htmlspecialchars($_POST['username']);
    $email            = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $number           = preg_replace('/\D/', '', $_POST['number']);
    $password         = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $transaction_pin  = htmlspecialchars($_POST['transaction_pin']);
    $device_fingerprint = isset($_POST['device_fingerprint']) ? htmlspecialchars($_POST['device_fingerprint']) : null;

    $forbiddenChars = ['&','$','<','>','{','}','\\',';','!','?','[',']'];
    foreach ($_POST as $key => $value) {
        if ($key !== 'password') {
            foreach ($forbiddenChars as $char) {
                if (strpos($value, $char) !== false) { $errors[] = "Forbidden character detected in $key input."; }
            }
        }
    }
    if (empty($errors)) {
        $default_image = 'default.png';
        $stmt_insert_user = $conn->prepare("INSERT INTO customers1 (reseller_id, fullname, username, email, number, password, transaction_pin, device_fingerprint, image, is_reseller) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, FALSE)");
        $stmt_insert_user->bind_param("sssssssss", $reseller_id, $fullname, $username, $email, $number, $password, $transaction_pin, $device_fingerprint, $default_image);
        if ($stmt_insert_user->execute()) {
            $user_id = $stmt_insert_user->insert_id;
            $_SESSION['fullname']         = $fullname;
            $_SESSION['email']            = $email;
            $_SESSION['transaction_pin']  = $transaction_pin;
            $_SESSION['reseller_id']      = $reseller_id;
            $cookie_expire_time = time() + (86400 * 30);
            setcookie("email",    $email,    $cookie_expire_time, "/");
            setcookie("fullname", $fullname, $cookie_expire_time, "/");
            $referral_id = generateReferralID();
            $stmt_insert_referral = $conn->prepare("INSERT INTO customers2 (reseller_id, referral_id, fullname, username, email, password, transaction_pin, referred_user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert_referral->bind_param("sssssssi", $reseller_id, $referral_id, $fullname, $username, $email, $password, $transaction_pin, $user_id);
            if ($stmt_insert_referral->execute()) {
                $mail = new PHPMailer;
                $mail->isSMTP(); $mail->Host = 'mail.futuredigitaltechltd.net.ng'; $mail->SMTPAuth = true;
                $mail->Username = 'futuretechimk@futuredigitaltechltd.net.ng'; $mail->Password = 'Futuretechimk';
                $mail->SMTPSecure = 'tls'; $mail->Port = 587;
                $mail->setFrom('support@futuredigitaltechltd.net.ng', 'Fuwa..NG Support');
                $mail->addAddress($email, $fullname); $mail->isHTML(true);
                $mail->Subject = 'Welcome to Fuwa..NG!';
                $mail->Body    = "<h1>Welcome, $fullname!</h1><p>Thanks for registering. You can now buy airtime, data, verify NIN/BVN and more.</p><p>If you have any questions, <a href='https://wa.me/2347072199487'>contact us on WhatsApp</a>.</p><p>Regards,<br>Fuwa..NG Support Team</p>";
                $mail->AltBody = "Welcome $fullname! Thanks for registering at Fuwa..NG.";
                if (!$mail->send()) { $errors[] = 'Mailer Error: ' . $mail->ErrorInfo; }
                else {
                    $success[] = "Account created! Redirecting to login…";
                    echo "<script>setTimeout(()=>window.location.href='user_login.php',4000);</script>";
                }
            } else { $errors[] = "Error: " . $stmt_insert_referral->error; }
        } else { $errors[] = "Error: " . $stmt_insert_user->error; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="theme-color" content="#020617">
    <meta name="description" content="Create your Fuwa..NG account – VTU, NIN & BVN Verification">
    <title>Create Account – Fuwa..NG</title>
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
        
        .left-sub { color: var(--text-secondary); font-size: 1.1rem; line-height: 1.6; max-width: 420px; margin-bottom: 3rem; }
        
        .benefit-list { display: flex; flex-direction: column; gap: 1.5rem; }
        .benefit-item { display: flex; align-items: center; gap: 1.25rem; }
        .benefit-icon { 
            width: 48px; height: 48px; border-radius: 14px; 
            background: var(--glass-bg); border: 1px solid var(--glass-border);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0; 
        }
        .benefit-icon i { font-size: 1.25rem; color: var(--primary-light); }
        .benefit-text h5 { font-size: 1rem; font-weight: 700; margin-bottom: 0.25rem; }
        .benefit-text p { font-size: 0.875rem; color: var(--text-secondary); }

        /* ── RIGHT PANEL ── */
        .auth-right {
            width: 540px; min-height: 100vh;
            display: flex; flex-direction: column; justify-content: center;
            padding: 4rem;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(20px);
            border-left: 1px solid var(--glass-border);
            position: relative;
        }
        .form-top-link { position: absolute; top: 2.5rem; right: 2.5rem; font-size: 0.875rem; color: var(--text-secondary); }
        .form-top-link a { color: var(--primary-light); text-decoration: none; font-weight: 600; }

        /* ── STEP INDICATOR ── */
        .step-indicator { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2.5rem; }
        .step-dot { 
            width: 36px; height: 36px; border-radius: 12px; 
            border: 1px solid var(--glass-border); background: var(--glass-bg);
            display: flex; align-items: center; justify-content: center; 
            font-size: 0.875rem; font-weight: 700; color: var(--text-secondary); 
            transition: all 0.4s ease; 
        }
        .step-dot.active { border-color: var(--primary); background: rgba(99, 102, 241, 0.1); color: var(--primary-light); transform: scale(1.1); }
        .step-dot.done { border-color: var(--success); background: rgba(74, 222, 128, 0.1); color: var(--success); }
        .step-line { flex: 1; height: 2px; background: var(--glass-border); border-radius: 4px; }
        .step-line.done { background: var(--primary); }

        .form-eyebrow { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--primary-light); margin-bottom: 0.75rem; }
        .form-title { font-size: 2.25rem; font-weight: 800; margin-bottom: 0.5rem; letter-spacing: -0.02em; }
        .form-subtitle { color: var(--text-secondary); font-size: 1rem; margin-bottom: 2.5rem; }

        .form-step { display: none; }
        .form-step.active { display: block; animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }

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

        /* strength bar */
        .strength-bar { display: flex; gap: 6px; margin-top: 0.75rem; }
        .strength-bar span { flex: 1; height: 4px; border-radius: 4px; background: var(--glass-border); transition: all 0.3s; }

        /* ── BUTTONS ── */
        .btn-row { display: flex; gap: 1rem; margin-top: 2rem; }
        .submit-btn {
            flex: 1; padding: 1.125rem;
            background: var(--primary);
            color: #fff; font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700;
            border: none; border-radius: 16px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 0.75rem;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
            transition: all 0.3s ease;
        }
        .submit-btn:hover { transform: translateY(-4px); background: var(--primary-dark); box-shadow: 0 15px 30px -5px rgba(99, 102, 241, 0.5); }
        .back-btn {
            padding: 1.125rem 1.5rem;
            background: var(--glass-bg); border: 1px solid var(--glass-border);
            color: var(--text-secondary); font-family: 'Outfit', sans-serif;
            border-radius: 16px; cursor: pointer; transition: all 0.3s ease;
        }
        .back-btn:hover { border-color: #fff; color: #fff; }

        /* ── PIN INPUT ── */
        .pin-wrapper { display: flex; gap: 0.75rem; justify-content: center; }
        .pin-wrapper input {
            width: 64px; height: 64px; text-align: center; font-size: 1.5rem; font-weight: 800;
            background: var(--glass-bg); border: 1px solid var(--glass-border);
            border-radius: 16px; color: #fff; font-family: 'Outfit', sans-serif;
            outline: none; transition: all 0.3s ease;
        }
        .pin-wrapper input:focus { border-color: var(--primary); background: rgba(99, 102, 241, 0.05); }

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

<?php
if (!empty($success)) {
    foreach ($success as $msg) {
        echo "<script>document.addEventListener('DOMContentLoaded',()=>Swal.fire({title:'Account Created!',text:'".addslashes($msg)."',icon:'success',background:'#0f172a',color:'#f8fafc',timer:4000,showConfirmButton:false}));</script>";
    }
}
if (!empty($errors)) {
    foreach ($errors as $msg) {
        echo "<script>document.addEventListener('DOMContentLoaded',()=>Swal.fire({title:'Oops!',text:'".addslashes($msg)."',icon:'error',background:'#0f172a',color:'#f8fafc',confirmButtonColor:'#6366f1'}));</script>";
    }
}
?>

<div class="auth-left">
    <a href="index.php" class="left-brand">
        <i class="fa-solid fa-bolt"></i> Fuwa<span>..NG</span>
    </a>
    <h1 class="left-headline">
        Join the <br><span class="gradient-text">Next Generation</span>
    </h1>
    <p class="left-sub">
        Create a free account to access premium digital services, bank-grade ID verification, and automated telecom solutions.
    </p>
    <div class="benefit-list">
        <div class="benefit-item">
            <div class="benefit-icon"><i class="fas fa-bolt"></i></div>
            <div class="benefit-text"><h5>Instant Infrastructure</h5><p>Automated VTU & bill payments in milliseconds.</p></div>
        </div>
        <div class="benefit-item">
            <div class="benefit-icon"><i class="fas fa-shield-halved"></i></div>
            <div class="benefit-text"><h5>Secured Identity</h5><p>NIMC-licensed NIN and BVN validation protocols.</p></div>
        </div>
        <div class="benefit-item">
            <div class="benefit-icon"><i class="fas fa-gift"></i></div>
            <div class="benefit-text"><h5>Growth Rewards</h5><p>Earn passive income through our referral ecosystem.</p></div>
        </div>
    </div>
</div>

<div class="auth-right">
    <div class="form-top-link">Already have an account? <a href="user_login.php">Sign in →</a></div>

    <div class="step-indicator">
        <div class="step-dot active" id="dot1">1</div>
        <div class="step-line" id="line1"></div>
        <div class="step-dot" id="dot2">2</div>
        <div class="step-line" id="line2"></div>
        <div class="step-dot" id="dot3">3</div>
    </div>

    <div class="form-eyebrow">Registration</div>
    <h2 class="form-title">Create Account</h2>
    <p class="form-subtitle" id="stepSubtitle">Step 1 of 3 – Personal Information</p>

    <form action="#" method="POST" id="registerForm" novalidate>
        <input type="hidden" name="sign" value="1">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="reseller_id" value="<?php echo htmlspecialchars($reseller_id); ?>">
        <input type="hidden" name="bio" value="">
        <input type="hidden" name="device_fingerprint" id="deviceFingerprint">

        <div class="form-step active" id="step1">
            <div class="field-group">
                <label class="field-label">Full Name</label>
                <div class="field-wrap">
                    <i class="fas fa-user field-icon"></i>
                    <input type="text" id="fullname" name="fullname" placeholder="John Doe" required>
                </div>
            </div>
            <div class="field-group">
                <label class="field-label">Username</label>
                <div class="field-wrap">
                    <i class="fas fa-at field-icon"></i>
                    <input type="text" id="username" name="username" placeholder="johndoe123" maxlength="20" required>
                </div>
            </div>
            <div class="field-group">
                <label class="field-label">Phone Number</label>
                <div class="field-wrap">
                    <i class="fas fa-phone field-icon"></i>
                    <input type="tel" id="number" name="number" placeholder="08012345678" minlength="11" maxlength="11" required>
                </div>
            </div>
            <div class="btn-row">
                <button type="button" class="submit-btn" onclick="goStep(2)">
                    Next Step <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <div class="form-step" id="step2">
            <div class="field-group">
                <label class="field-label">Email Address</label>
                <div class="field-wrap">
                    <i class="fas fa-envelope field-icon"></i>
                    <input type="email" id="email" name="email" placeholder="john@example.com" required>
                </div>
            </div>
            <div class="field-group">
                <label class="field-label">Password</label>
                <div class="field-wrap">
                    <i class="fas fa-lock field-icon"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required oninput="checkStrength(this.value)">
                    <i class="fas fa-eye toggle-eye" id="togglePwd" onclick="togglePassword('password','togglePwd')"></i>
                </div>
                <div class="strength-bar">
                    <span id="s1"></span><span id="s2"></span><span id="s3"></span><span id="s4"></span>
                </div>
            </div>
            <div class="btn-row">
                <button type="button" class="back-btn" onclick="goStep(1)"><i class="fas fa-arrow-left"></i></button>
                <button type="button" class="submit-btn" onclick="goStep(3)">
                    Next Step <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <div class="form-step" id="step3">
            <p style="color:var(--text-secondary);font-size:0.9375rem;margin-bottom:2rem;line-height:1.6;text-align:center;">
                Secure your account with a <strong style="color:#fff">4-digit PIN</strong> for all financial transactions.
            </p>
            <div class="field-group">
                <div class="pin-wrapper">
                    <input type="tel" class="pin-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" id="pin1">
                    <input type="tel" class="pin-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" id="pin2">
                    <input type="tel" class="pin-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" id="pin3">
                    <input type="tel" class="pin-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" id="pin4">
                </div>
                <input type="hidden" name="transaction_pin" id="transaction_pin">
                <p id="pinError" style="color:var(--error); font-size:0.8rem; text-align:center; margin-top:10px; display:none;">Please enter a 4-digit security PIN.</p>
            </div>

            <label style="display:flex;align-items:center;gap:0.75rem;margin-top:2rem;cursor:pointer;background:var(--glass-bg);padding:1rem;border-radius:12px;border:1px solid var(--glass-border)">
                <input type="checkbox" id="agreeTerms" style="width:18px;height:18px;accent-color:var(--primary)">
                <span style="font-size:0.875rem;color:var(--text-secondary)">
                    I agree to the <a href="#" style="color:var(--primary-light);text-decoration:none;font-weight:600">Terms & Conditions</a>
                </span>
            </label>
            <p id="termsError" style="color:var(--error); font-size:0.8rem; margin-top:10px; display:none;">You must agree to the terms to continue.</p>

            <div class="btn-row">
                <button type="button" class="back-btn" onclick="goStep(2)"><i class="fas fa-arrow-left"></i></button>
                <button type="submit" class="submit-btn" id="submitBtn">
                    Complete Registration <i class="fas fa-check"></i>
                </button>
            </div>
        </div>

        <?php
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://sadeeqdata.com.ng/script_request/index_register.php");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $remote = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            echo "<script>
                document.addEventListener('DOMContentLoaded', () => {
                    Swal.fire({
                        title: 'Connection Issue',
                        text: 'We are having trouble connecting to our registration server. Please check your internet or try again later.',
                        icon: 'info',
                        background: '#0f172a',
                        color: '#f8fafc',
                        confirmButtonColor: '#6366f1'
                    });
                });
            </script>";
        } else {
            // Remove the specific licensed link while keeping the footer structure
            $filtered_remote = preg_replace('/<a[^>]*href=[^>]*>.*?LICENSED BY GSOFT CREATIVE SERVICES.*?<\/a>/is', '', $remote);
            echo $filtered_remote;
        }
        curl_close($ch);
        ?>
    </form>

    <p class="login-link" style="margin-top:1.5rem">Already have an account? <a href="user_login.php">Sign in here</a></p>
</div>

<script>
    // ── Page Loader ──
    window.addEventListener('load', () => {
        const loader = document.getElementById('pageLoader');
        loader.style.opacity = '0';
        setTimeout(() => loader.style.display = 'none', 400);
    });

    // ── Device Fingerprint ──
    document.getElementById('deviceFingerprint').value = navigator.userAgent + '|' + screen.width + 'x' + screen.height;

    // ── Toggle Password ──
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        if (input.type === 'password') { input.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
        else { input.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
    }

    // ── Password Strength ──
    function checkStrength(val) {
        const bars = [document.getElementById('s1'),document.getElementById('s2'),document.getElementById('s3'),document.getElementById('s4')];
        let score = 0;
        if (val.length >= 6) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;
        const colors = ['#f87171','#fb923c','#facc15','#4ade80'];
        bars.forEach((b, i) => b.style.background = i < score ? colors[score - 1] : 'rgba(255,255,255,0.1)');
    }

    // ── PIN Box Flow ──
    document.querySelectorAll('.pin-digit').forEach((input, idx, all) => {
        input.addEventListener('input', e => {
            if (e.target.value && idx < all.length - 1) all[idx + 1].focus();
            compilePIN();
        });
        input.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !e.target.value && idx > 0) all[idx - 1].focus();
        });
    });
    function compilePIN() {
        const val = [...document.querySelectorAll('.pin-digit')].map(i => i.value).join('');
        document.getElementById('transaction_pin').value = val;
    }

    // ── Step Navigation ──
    const subtitles = ['Step 1 of 3 – Personal information', 'Step 2 of 3 – Account credentials', 'Step 3 of 3 – Transaction PIN'];
    let currentStep = 1;

    function goStep(n) {
        console.log('Navigating to step:', n);
        if (n > currentStep) {
            if (!validateStep(currentStep)) return;
        }
        
        const currentStepEl = document.getElementById('step' + currentStep);
        const nextStepEl = document.getElementById('step' + n);
        
        if (currentStepEl && nextStepEl) {
            currentStepEl.classList.remove('active');
            nextStepEl.classList.add('active');
            currentStep = n;
            const subtitleEl = document.getElementById('stepSubtitle');
            if (subtitleEl) subtitleEl.textContent = subtitles[n - 1];
            updateIndicator(n);
        }
    }

    function updateIndicator(active) {
        for (let i = 1; i <= 3; i++) {
            const dot = document.getElementById('dot' + i);
            if (dot) {
                dot.className = 'step-dot ' + (i < active ? 'done' : i === active ? 'active' : '');
                dot.innerHTML = i < active ? '<i class="fas fa-check" style="font-size:.65rem"></i>' : i;
            }
        }
        [1, 2].forEach(i => {
            const line = document.getElementById('line' + i);
            if (line) {
                line.className = 'step-line ' + (i < active ? 'done' : '');
            }
        });
    }

    function validateStep(n) {
        let ok = true;
        let msg = "";

        try {
            if (n === 1) {
                const fn = document.getElementById('fullname').value.trim();
                const un = document.getElementById('username').value.trim();
                const ph = document.getElementById('number').value.replace(/\D/g, '');

                if (!fn) { msg = "Please enter your full name."; ok = false; }
                else if (!un) { msg = "Please pick a username."; ok = false; }
                else if (ph.length !== 11) { msg = "Enter a valid 11-digit Nigerian phone number."; ok = false; }
            } else if (n === 2) {
                const em = document.getElementById('email').value.trim();
                const pw = document.getElementById('password').value;
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)) { msg = "Please enter a valid email address."; ok = false; }
                else if (pw.length < 6) { msg = "Password must be at least 6 characters."; ok = false; }
            }
        } catch (e) {
            console.error('Validation error:', e);
            return false;
        }

        if (!ok) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Wait a moment',
                    text: msg,
                    background: '#0f172a',
                    color: '#f8fafc',
                    confirmButtonColor: '#6366f1'
                });
            } else {
                alert(msg);
            }
        }
        return ok;
    }

    // ── Form Submit ──
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        // Validate PIN
        compilePIN();
        const pin   = document.getElementById('transaction_pin').value;
        const terms = document.getElementById('agreeTerms').checked;
        let ok = true;
        if (pin.length !== 4 || !/^\d{4}$/.test(pin)) {
            document.getElementById('pinError').style.display = 'block'; ok = false;
        } else { document.getElementById('pinError').style.display = 'none'; }
        if (!terms) { document.getElementById('termsError').style.display = 'block'; ok = false; }
        else { document.getElementById('termsError').style.display = 'none'; }
        if (!ok) { e.preventDefault(); return; }

        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Creating account…</span>';
    });
</script>
</body>
</html>