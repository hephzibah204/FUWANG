<?php
session_start();
include_once("db_conn.php");

if (!isset($_SESSION['admin_id'])) {
    echo 'Unauthorized access.';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['otp'])) {
    $otp = htmlspecialchars($_POST['otp']);
    $admin_id = $_SESSION['admin_id'];
    
    // Use prepared statement to fetch OTP and its expiry from the database
    $stmt = $conn->prepare("SELECT otp, expires_at FROM admin_otp_verification WHERE admin_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($stored_otp, $expires_at);
    $stmt->fetch();
    $stmt->close();

    $current_time = date('Y-m-d H:i:s');

    if ($stored_otp && $otp == $stored_otp) {
        if ($current_time < $expires_at) {
               $_SESSION['loggedin'] = true;

            // OTP is verified, redirect to the admin dashboard
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo '<div class="alert alert-danger" role="alert">OTP has expired. Please request a new OTP.</div>';
        }
    } else {
        echo '<div class="alert alert-danger" role="alert">Invalid OTP.</div>';
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <h2 class="text-center">Verify OTP</h2>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="otp">OTP:</label>
                        <input type="text" maxlength="6" class="form-control" id="otp" name="otp" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Submit OTP</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>