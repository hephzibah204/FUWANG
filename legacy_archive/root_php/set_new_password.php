<?php
session_start();
include_once("db_conn.php");
if (!isset($_SESSION['verified_email'])) {
    header("Location: index.html"); // Redirect to the login page if not verified
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="theme-color" content="#020617">
    <meta name="description" content="Set a new password for your Fuwa..NG account">
    <title>Set New Password – Fuwa..NG</title>
    <link rel="icon" href="images/icon_logo.png">
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
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

        .submit-btn {
            width: 100%;
            padding: 1.125rem;
            background: var(--primary);
            color: #fff; font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700;
            border: none; border-radius: 16px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 0.75rem;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        .submit-btn:hover { transform: translateY(-4px); background: var(--primary-dark); box-shadow: 0 15px 30px -5px rgba(99, 102, 241, 0.5); }
        .submit-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

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
        Create a <br><span class="gradient-text">New Password</span>
    </h1>
    <p class="left-sub">
        Choose a strong, unique password to keep your Fuwa..NG account and digital assets protected.
    </p>
</div>

<div class="auth-right">
    <div class="form-eyebrow">Final Step</div>
    <h2 class="form-title">Security Update</h2>
    <p class="form-subtitle">Please enter your new password below.</p>

    <form id="password-form">
        <div class="field-group">
            <label class="field-label">New Password</label>
            <div class="field-wrap">
                <i class="fas fa-lock field-icon"></i>
                <input type="password" id="password" name="password" placeholder="••••••••" minlength="6" required>
                <i class="fas fa-eye toggle-eye" id="togglePwd" onclick="togglePassword('password','togglePwd')"></i>
            </div>
        </div>

        <div class="field-group">
            <label class="field-label">Confirm Password</label>
            <div class="field-wrap">
                <i class="fas fa-shield-check field-icon"></i>
                <input type="password" id="confirm_password" placeholder="••••••••" minlength="6" required>
            </div>
        </div>

        <button class="submit-btn" type="submit" id="updateBtn">
            Update Password <i class="fas fa-check-circle"></i>
        </button>
    </form>
</div>

<script>
    window.addEventListener('load', () => {
        const loader = document.getElementById('pageLoader');
        loader.style.opacity = '0';
        setTimeout(() => loader.style.display = 'none', 400);
    });

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

    $(document).ready(function() {
        $("#password-form").on("submit", function(event) {
            event.preventDefault();
            
            const password = $("#password").val();
            const confirm = $("#confirm_password").val();
            const btn = $("#updateBtn");

            if (password !== confirm) {
                Swal.fire({ icon: 'error', title: 'Mismatch', text: 'Passwords do not match.', background: '#0f172a', color: '#f8fafc' });
                return;
            }

            btn.prop('disabled', true);
            btn.html('<i class="fas fa-circle-notch fa-spin"></i> Updating...');

            $.post("update_password.php", { password: password }, function(response) {
                if(response.status == "success") {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Your password has been updated. Please sign in.',
                        background: '#0f172a',
                        color: '#f8fafc',
                        confirmButtonColor: '#6366f1'
                    }).then(() => {
                        window.location.href = "user_login.php";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: response.message,
                        background: '#0f172a',
                        color: '#f8fafc',
                        confirmButtonColor: '#6366f1'
                    });
                    btn.prop('disabled', false);
                    btn.html('Update Password <i class="fas fa-check-circle"></i>');
                }
            }, "json").fail(function() {
                Swal.fire({ title: 'Error', text: 'Network connection failed.', icon: 'error', background: '#0f172a', color: '#f8fafc' });
                btn.prop('disabled', false);
                btn.html('Update Password <i class="fas fa-check-circle"></i>');
            });
        });
    });
</script>
</body>
</html>