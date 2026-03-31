<?php
session_start();
include_once("db_conn.php");

if(!isset($_COOKIE['email'])) {
    header('Location:user_login.php');
    exit;
}

$email = $_COOKIE['email'];

// PIN Validation Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $transaction_pin = $_POST["transaction_pin"];
    $sql = "SELECT * FROM customers1 WHERE email = ? AND transaction_pin = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $transaction_pin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $_SESSION["id"] = $user["id"];
        $_SESSION["fullname"] = $user["fullname"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["image"] = $user["image"];
        $_SESSION["transaction_pin"] = $user["transaction_pin"];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Incorrect transaction pin. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>Secure Access | Fuwa..NG</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    <link rel="stylesheet" href="vtusite/css/modern-ui.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .pin-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .pin-card {
            background: var(--surface-dark);
            border: 1px solid var(--glass-border);
            padding: 3rem;
            border-radius: 32px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-xl);
        }
        .pin-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid var(--primary);
            margin: 0 auto 2rem;
            padding: 4px;
            background: var(--bg-dark);
        }
        .pin-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        .pin-input-group {
            position: relative;
            margin-bottom: 2rem;
        }
        .pin-input {
            width: 100%;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.25rem;
            color: #fff;
            font-size: 1.5rem;
            letter-spacing: 0.5rem;
            text-align: center;
            transition: var(--transition);
        }
        .pin-input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }
    </style>
</head>
<body>
    <div class="bg-mesh"></div>
    <div class="bg-glow blob-1"></div>
    <div class="bg-glow blob-2"></div>

    <div class="pin-container">
        <div class="pin-card fade-up">
            <div class="pin-avatar">
                <?php 
                $img = $_SESSION['image'] ?? 'vtusite/images/avatar.jpg';
                echo "<img src='$img' alt='User'>";
                ?>
            </div>
            
            <h2 style="margin-bottom: 0.5rem;">Security Verification</h2>
            <p class="text-secondary" style="margin-bottom: 2.5rem;">Enter your 4-digit transaction pin to access your dashboard.</p>

            <form action="#" method="POST">
                <div class="pin-input-group">
                    <input type="password" name="transaction_pin" class="pin-input" maxlength="4" placeholder="****" required autofocus>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; font-size: 1.1rem;">
                    Unlock Dashboard <i class="fa-solid fa-lock-open" style="margin-left: 8px;"></i>
                </button>
            </form>

            <div style="margin-top: 2rem;">
                <a href="users_logout.php" class="text-secondary" style="text-decoration: none; font-size: 0.9rem;">
                    Not you? <span style="color: var(--primary-light);">Switch Account</span>
                </a>
            </div>
        </div>
    </div>

    <?php if(isset($error)): ?>
    <script>
        Swal.fire({
            title: 'Access Denied',
            text: '<?= $error ?>',
            icon: 'error',
            confirmButtonColor: '#6366f1'
        });
    </script>
    <?php endif; ?>
</body>
</html>
