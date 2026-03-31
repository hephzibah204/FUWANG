<?php
session_start();
include_once("db_conn.php");

if (!isset($_SESSION["transaction_pin"])) {
    header("location:welcome_pin.php");
    exit();
}

$bvn_price = 0;
$priceQuery = "SELECT bvn_by_bvn FROM verification_price LIMIT 1";
$priceResult = $conn->query($priceQuery);
if ($priceResult->num_rows > 0) {
    $priceData = $priceResult->fetch_assoc();
    $bvn_price = $priceData['bvn_by_bvn'];
}

// AJAX Handler
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {
    $bvn = filter_input(INPUT_POST, 'bvn', FILTER_SANITIZE_STRING);
    if (!preg_match('/^\d{11}$/', $bvn)) {
        echo json_encode(['status' => false, 'message' => 'Invalid BVN format (must be 11 digits)']);
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

        if ($user_balance >= $bvn_price) {
            // Deduct Balance
            $new_balance = $user_balance - $bvn_price;
            $update = $conn->prepare("UPDATE account_balance SET user_balance = ? WHERE email = ?");
            $update->bind_param("ds", $new_balance, $user_email);
            $update->execute();
            $update->close();

            // Mock Success Response for Preview (In production, include bvn_api.php)
            // include_once("bvn_api.php"); 
            // For now, simulating successful API call
            $response = json_encode([
                'status' => true,
                'image' => 'vtusite/images/avatar.jpg',
                'bvn' => $bvn,
                'data' => [
                    'firstName' => 'Sample',
                    'lastName' => 'User',
                    'dob' => '1990-01-01',
                    'phoneNumber' => '08012345678'
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
    <title>BVN Verification | Fuwa..NG</title>
    
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
            <div class="service-icon-large">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            
            <div class="text-center mb-5">
                <h2 style="margin-bottom: 10px;">BVN Verification</h2>
                <p class="text-secondary">Verify identity instantly using Bank Verification Number.</p>
                <span class="badge" style="margin-top: 15px;">Cost: ₦<?= number_format($bvn_price, 2) ?></span>
            </div>

            <form id="bvnForm" class="form-premium">
                <div class="mb-4">
                    <label class="form-label">Enter 11-Digit BVN</label>
                    <input type="text" name="bvn" id="bvnInput" class="form-control" maxlength="11" placeholder="222XXXXXXXX" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-3" id="submitBtn">
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
                    <span class="data-label">BVN</span>
                    <span class="data-value" id="resBvn"></span>
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
            $('#bvnForm').on('submit', function(e) {
                e.preventDefault();
                
                const bvn = $('#bvnInput').val();
                if(bvn.length !== 11) {
                    Swal.fire('Invalid BVN', 'Please enter a valid 11-digit BVN', 'warning');
                    return;
                }

                $('#submitBtn').html('<span class="spinner-border spinner-border-sm"></span> Processing...').prop('disabled', true);

                $.ajax({
                    url: 'verify_bvn.php',
                    type: 'POST',
                    data: { ajax: 1, bvn: bvn },
                    dataType: 'json',
                    success: function(res) {
                        if(res.status) {
                            $('#bvnForm').fadeOut(300, function() {
                                $('#resImage').attr('src', res.image);
                                $('#resName').text(res.data.firstName + ' ' + res.data.lastName);
                                $('#resBvn').text(res.bvn);
                                $('#resDob').text(res.data.dob);
                                $('#resPhone').text(res.data.phoneNumber);
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
