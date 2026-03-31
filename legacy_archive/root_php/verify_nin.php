<?php
session_start();
include_once("db_conn.php");

if (!isset($_SESSION["transaction_pin"])) {
    header("location:welcome_pin.php");
    exit();
}

$nin_price = 0;
$priceQuery = "SELECT nin_by_nin_price FROM verification_price LIMIT 1";
$priceResult = $conn->query($priceQuery);
if ($priceResult->num_rows > 0) {
    $priceData = $priceResult->fetch_assoc();
    $nin_price = $priceData['nin_by_nin_price'];
}

// Generate Call Token if not exists
if (!isset($_SESSION['call_token'])) {
    $_SESSION['call_token'] = bin2hex(random_bytes(32));
}

// AJAX Handler
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {
    if (!isset($_POST['call_token']) || $_POST['call_token'] !== $_SESSION['call_token']) {
        echo json_encode(['status' => false, 'message' => 'Invalid security token.']);
        exit;
    }

    $nin = filter_input(INPUT_POST, 'nin', FILTER_SANITIZE_STRING);
    if (!preg_match('/^\d{11}$/', $nin)) {
        echo json_encode(['status' => false, 'message' => 'Invalid NIN format (must be 11 digits)']);
        exit;
    }

    if (isset($_SESSION["email"])) {
        $user_email = $_SESSION["email"];
        
        // Fetch Balance
        $stmt = $conn->prepare("SELECT user_balance FROM account_balance WHERE email = ?");
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $stmt->bind_result($user_balance);
        $stmt->fetch();
        $stmt->close();

        if ($user_balance >= $nin_price) {
            // Deduct Balance
            $new_balance = $user_balance - $nin_price;
            $update = $conn->prepare("UPDATE account_balance SET user_balance = ? WHERE email = ?");
            $update->bind_param("ds", $new_balance, $user_email);
            $update->execute();
            $update->close();

            // Mock Success Response for Preview
            $response = json_encode([
                'status' => true,
                'image' => 'vtusite/images/avatar.jpg',
                'nin' => $nin,
                'data' => [
                    'firstname' => 'Sample',
                    'surname' => 'User',
                    'birthdate' => '1990-01-01',
                    'telephoneno' => '08012345678'
                ]
            ]);
            echo $response;
            exit;
        } else {
            echo json_encode(['status' => false, 'message' => 'Insufficient balance. Please fund your wallet.']);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>NIN Verification | Fuwa..NG</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vtusite/css/modern-ui.css?v=<?= time() ?>">
    <link rel="stylesheet" href="vtusite/css/service-ui.css?v=<?= time() ?>">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="bg-mesh"></div>
    <div class="bg-glow blob-1"></div>
    <div class="bg-glow blob-2"></div>

    <!-- Simple Nav -->
    <nav class="navbar scrolled">
        <div class="container nav-container">
            <a href="dashboard.php" class="logo">
                <i class="fa-solid fa-arrow-left"></i> <span>Back to <span>Dashboard</span></span>
            </a>
        </div>
    </nav>

    <div class="service-container" style="padding-top: 120px;">
        <div class="service-card-premium fade-up">
            <div class="service-icon-large" style="color: #22d3ee; background: rgba(6, 182, 212, 0.1);">
                <i class="fa-regular fa-id-card"></i>
            </div>
            
            <div class="text-center mb-5">
                <h2 style="margin-bottom: 10px;">NIN Verification</h2>
                <p class="text-secondary">National Identity Number lookup and validation service.</p>
                <span class="badge" style="margin-top: 15px; color: #22d3ee; border-color: rgba(6, 182, 212, 0.2); background: rgba(6, 182, 212, 0.1);">Cost: ₦<?= number_format($nin_price, 2) ?></span>
            </div>

            <form id="ninForm" class="form-premium">
                <input type="hidden" id="callToken" value="<?= $_SESSION['call_token'] ?>">
                <div class="mb-4">
                    <label class="form-label">Enter 11-Digit NIN</label>
                    <input type="text" name="nin" id="ninInput" class="form-control" maxlength="11" placeholder="XXXXXXXXXXX" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-3" id="submitBtn" style="background: #06b6d4; box-shadow: 0 10px 30px -10px rgba(6, 182, 212, 0.4);">
                    Verify Identity <i class="fa-solid fa-magnifying-glass" style="margin-left: 8px;"></i>
                </button>
            </form>

            <!-- Results Section -->
            <div id="resultArea" class="result-premium">
                <div class="text-center">
                    <img id="resImage" src="" alt="User Photo">
                    <h4 id="resName" class="mb-4"></h4>
                </div>
                
                <div class="data-row">
                    <span class="data-label">NIN</span>
                    <span class="data-value" id="resNin"></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Date of Birth</span>
                    <span class="data-value" id="resDob"></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Phone Number</span>
                    <span class="data-value" id="resPhone"></span>
                </div>
                
                <button class="btn btn-outline w-100 mt-4" onclick="location.reload()">New Search</button>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#ninForm').on('submit', function(e) {
                e.preventDefault();
                
                const nin = $('#ninInput').val();
                const token = $('#callToken').val();
                
                if(nin.length !== 11) {
                    Swal.fire('Invalid NIN', 'Please enter a valid 11-digit National Identity Number', 'warning');
                    return;
                }

                $('#submitBtn').html('<span class="spinner-border spinner-border-sm"></span> Processing...').prop('disabled', true);

                $.ajax({
                    url: 'verify_nin.php',
                    type: 'POST',
                    data: { ajax: 1, nin: nin, call_token: token },
                    dataType: 'json',
                    success: function(res) {
                        if(res.status) {
                            $('#ninForm').fadeOut(300, function() {
                                $('#resImage').attr('src', res.image);
                                $('#resName').text(res.data.firstname + ' ' + res.data.surname);
                                $('#resNin').text(res.nin);
                                $('#resDob').text(res.data.birthdate);
                                $('#resPhone').text(res.data.telephoneno);
                                $('#resultArea').fadeIn();
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                            $('#submitBtn').html('Verify Identity <i class="fa-solid fa-magnifying-glass"></i>').prop('disabled', false);
                        }
                    },
                    error: function() {
                        Swal.fire('Server Error', 'Could not complete verification. Please try again.', 'error');
                        $('#submitBtn').html('Verify Identity <i class="fa-solid fa-magnifying-glass"></i>').prop('disabled', false);
                    }
                });
            });
        });
    </script>
</body>
</html>
