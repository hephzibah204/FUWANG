<?php
session_start();
include_once("db_conn.php");
include_once("check_user_account.php");

// Security Check
if (!isset($_SESSION["transaction_pin"])) {
    header("location:welcome_pin.php");
    exit();
}

if (!isset($_SESSION['email'])) {
    header("Location: users_logout.php");
    exit();
}

$email = $_SESSION['email'];
$fullname = $_SESSION['fullname'] ?? 'User';
$user_image = $_SESSION['image'] ?? 'vtusite/images/avatar.jpg';

// Fetch Balance
function get_user_balance($email, $conn) {
    $stmt = $conn->prepare("SELECT user_balance FROM account_balance WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['user_balance'];
    }
    return 0.00;
}

$user_balance = get_user_balance($email, $conn);

// Notification
$sql_notif = "SELECT notification FROM notifying_center LIMIT 1";
$res_notif = $conn->query($sql_notif);
$global_notification = ($res_notif->num_rows > 0) ? $res_notif->fetch_assoc()['notification'] : "";

// Individual User Notifications
$user_notifications = [];
$stmt_unotif = $conn->prepare("SELECT id, title, message FROM user_notifications WHERE user_email = ? AND is_read = 0 ORDER BY created_at DESC");
$stmt_unotif->bind_param("s", $email);
$stmt_unotif->execute();
$res_unotif = $stmt_unotif->get_result();
while ($row = $res_unotif->fetch_assoc()) {
    $user_notifications[] = $row;
}
$stmt_unotif->close();

include_once("fund_wallet_process.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>Dashboard | Fuwa..NG</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vtusite/css/dashboard-ui.css?v=<?= time() ?>">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="bg-glow blob-1"></div>
    <div class="bg-glow blob-2"></div>

    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <a href="index.php" class="sidebar-logo">
                <i class="fa-solid fa-bolt"></i> <span>Fuwa<span>..NG</span></span>
            </a>
            
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item active">
                    <i class="fa-solid fa-house"></i> <span>Overview</span>
                </a>
                <a href="profile" class="nav-item">
                    <i class="fa-solid fa-user"></i> <span>My Profile</span>
                </a>
                <a href="funding_history" class="nav-item">
                    <i class="fa-solid fa-wallet"></i> <span>Transactions</span>
                </a>
                <a href="developer_api" class="nav-item">
                    <i class="fa-solid fa-code"></i> <span>Developer API</span>
                </a>
                <a href="settings" class="nav-item">
                    <i class="fa-solid fa-cog"></i> <span>Settings</span>
                </a>
                <div style="margin-top: auto;">
                    <a href="javascript:void(0)" onclick="confirmLogout()" class="nav-item" style="color: #ef4444;">
                        <i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-header">
                <div class="welcome-text">
                    <h2>Hello, <?= explode(' ', $fullname)[0] ?>!</h2>
                    <p class="text-secondary">Welcome back to your premium hub.</p>
                </div>
                
                <div class="user-profile">
                    <img src="<?= $user_image ?>" alt="Profile">
                    <span class="d-none d-md-inline"><?= $fullname ?></span>
                </div>
            </header>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <p class="stat-label">Wallet Balance</p>
                    <p class="stat-value">₦<?= number_format($user_balance, 2) ?></p>
                    <button class="stat-btn" data-bs-toggle="offcanvas" data-bs-target="#fundWallet">Fund Wallet</button>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Account Status</p>
                    <p class="stat-value" style="color: #4ade80;">Active</p>
                    <p class="text-secondary" style="font-size: 0.8rem;">Premium Verified</p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Support</p>
                    <p class="stat-value">24/7</p>
                    <a href="https://wa.me/2348113910395" class="stat-btn" style="background: #25d366; text-align:center; text-decoration:none;">Chat WhatsApp</a>
                </div>
            </div>

            <!-- Services -->
            <h3 style="margin-bottom: 1.5rem;">Core Services</h3>
            <div class="service-grid">
                <a href="verify_bvn.php" class="service-item">
                    <i class="fa-solid fa-building-columns"></i>
                    <span>BVN Check</span>
                </a>
                <a href="verify_nin.php" class="service-item">
                    <i class="fa-regular fa-id-card"></i>
                    <span>NIN Verification</span>
                </a>
                <a href="airtime.php" class="service-item">
                    <i class="fa-solid fa-phone"></i>
                    <span>Buy Airtime</span>
                </a>
                <a href="welcome_pin" class="service-item">
                    <i class="fa-solid fa-wifi"></i>
                    <span>Data Bundles</span>
                </a>
                <a href="welcome_pin" class="service-item">
                    <i class="fa-solid fa-lightbulb"></i>
                    <span>Electricity</span>
                </a>
                <a href="welcome_pin" class="service-item">
                    <i class="fa-solid fa-tv"></i>
                    <span>Cable TV</span>
                </a>
            </div>
        </main>
    </div>

    <!-- Fund Wallet Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="fundWallet" style="background: var(--surface-dark); color: #fff;">
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title">Fund Your Wallet</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <p class="text-secondary mb-4">Choose your preferred method to add funds to your account.</p>
            <!-- Bank Details or Payment Processor here -->
            <div class="stat-card mb-3">
                <p class="stat-label">Automated Funding</p>
                <p style="font-size: 0.9rem;">Generate a unique bank account for instant wallet crediting.</p>
                <button class="stat-btn mt-3">Generate Account</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Sign Out?',
                text: "Are you sure you want to end your session?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Yes, Sign Out'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        }

        // Show Notifications on Load
        document.addEventListener('DOMContentLoaded', () => {
            const globalNotif = "<?= addslashes($global_notification) ?>";
            const userNotifs = <?= json_encode($user_notifications) ?>;

            if (globalNotif) {
                Swal.fire({
                    title: 'System Update',
                    text: globalNotif,
                    confirmButtonColor: '#6366f1'
                });
            }

            if (userNotifs.length > 0) {
                userNotifs.forEach(async (notif) => {
                    await Swal.fire({
                        title: notif.title,
                        text: notif.message,
                        icon: 'info',
                        confirmButtonColor: '#6366f1'
                    });
                    // Mark as read via Fetch API
                    fetch('mark_notif_read.php?id=' + notif.id);
                });
            }
        });
    </script>
</body>
</html>
