<?php
include_once("db_conn.php");

if (isset($_GET['nin'])) {
    // Trim and validate the NIN
    $nin = trim($_GET['nin']);
    
    // Ensure NIN is numeric and exactly 11 digits (modify if necessary)
    if (!preg_match('/^\d{11}$/', $nin)) {
        die("Invalid NIN format.");
    }

    // Sanitize the NIN for the database
    $nin = mysqli_real_escape_string($conn, $nin);

// Check if the session is valid (e.g., if a user session variable exists)
if (!isset($_SESSION['email'])) {
    // If no session, redirect to user_logout.php
    header("Location: user_login");
    exit();
}

    // Fetch data from id_history table where response_data contains the provided NIN
    $sql = "SELECT response_data FROM personalized_nin_history WHERE response_data LIKE ?";
    $stmt = $conn->prepare($sql);
    $like_nin = '%"nin":"' . $nin . '"%';
    $stmt->bind_param("s", $like_nin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response_data = json_decode($row["response_data"], true);

        // Fetch price for different slip types
        $stmt_price = $conn->prepare("SELECT * FROM id_verification_price LIMIT 1");
        $stmt_price->execute();
        $result_price = $stmt_price->get_result();
        $price_row = $result_price->fetch_assoc();
        $stmt_price->close();
        
        $nin_regular_slip_price = $price_row['nin_regular_slip_price'];
        $nin_standard_slip_price = $price_row['nin_standard_slip_price'];
        $nin_premium_slip_price = $price_row['nin_premium_slip_price'];
    } else {
        echo "No record found for NIN: " . $nin;
        exit;
    }
    $stmt->close();
} else {
    echo "NIN not provided.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1.0"> 
    <meta name="theme-color"content="darkblue">
    <title>NIN Verification</title>
    <link rel="icon" href="images/logo2.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script> 
   <style>
              /* Hide the dropdown content by default */
  .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            font-size: 10px;
            opacity: 1;
            border-radius: 10px;
            margin-top: 21%;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            right: 0; /* Position the dropdown content to the right */
        }
        .menu-container:hover .dropdown-content {
            display: block;
        }
        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }
        .header{
color:white;

top:-1%;
left:-0.1%;

position: absolute;
background-color: #190F92;
width:100%;
}
     body {
           
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
            
        }  
         button {
            width: 50%;
            padding: 10px;
            background-color: #190F92;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }  
            .content {
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            background-color: #fff;
            
        }
        .slip_div{
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding:10%;
            width:70%;
            justify-content: center;
            align-items: center;
             margin:5%;
            
            text-align: center;
        }
      
        .loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loader::after {
            content: '';
            display: block;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 6px solid red;
            border-color:  <?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?> transparent  <?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?> transparent;
            animation: rotate 1.5s linear infinite;
        }

        .custom-confirm-button {
            background-color:  <?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?>;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 16px;
        }
    </style>
</head>
<body>
<div class="header">
            <h2 style="font-size:20px">Print slip</h2>
</div> 
<br><br><br><br>



<script>
    // Show the loading spinner
    function showLoader() {
        document.getElementById('loader').style.display = 'flex';
    }

    // Hide the loading spinner
    function hideLoader() {
        document.getElementById('loader').style.display = 'none';
    }

    $(document).ready(function() {
        // Show alert when the page is loaded
        Swal.fire({
            title: "Important Notice!!!",
            text: "Please make sure to confirm the slip you want to download, because once you click the button, we automatically subtract the amount from your account balance, whether the download is complete or not.",
            icon: "warning",
            confirmButtonText: "OK",
            customClass: {
                confirmButton: 'custom-confirm-button'
            }
        });

        // Handle Standard slip download
        $('#standardDownloadBtn').click(function() {
            const nin = new URLSearchParams(window.location.search).get('nin');
            if (!nin) {
                alert('NIN not found in the URL!');
                return;
            }

            showLoader();

            $.ajax({
                url: 'standard_nin_slip.php',
                type: 'GET',
                data: { nin: nin },
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        window.open(response.pdf_url, '_blank'); // Open PDF in a new tab
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    alert('An error occurred. Please try again.');
                }
            });
        });

        // Handle Premium slip download
        $('#premiumDownloadBtn').click(function() {
            const nin = new URLSearchParams(window.location.search).get('nin');
            if (!nin) {
                alert('NIN not found in the URL!');
                return;
            }

            showLoader();

            $.ajax({
                url: 'premium_verified_slip.php',
                type: 'GET',
                data: { nin: nin },
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        window.open(response.pdf_url, '_blank'); // Open PDF in a new tab
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    alert('An error occurred. Please try again.');
                }
            });
        });

        // Handle Regular slip download
        $('#regularDownloadBtn').click(function() {
            const nin = new URLSearchParams(window.location.search).get('nin');
            if (!nin) {
                alert('NIN not found in the URL!');
                return;
            }

            showLoader();

            $.ajax({
                url: 'regular_verified_slip.php',
                type: 'GET',
                data: { nin: nin },
                dataType: 'json',
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        window.open(response.pdf_url, '_blank'); // Open PDF in a new tab
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoader();
                    alert('An error occurred. Please try again.');
                }
            });
        });
    });
</script>

<!-- Loader -->
<div class="loader" id="loader"></div>

<!-- Regular Slip Section -->
<div class="slip_div">
    <h3>Regular Slip</h3>
    <div style="border: 1px solid #f1f1f1;"></div>
    <img src="/vtusite/images/nin.jpg" style="width: 70%">
    <br><br>
    <small>Paid ₦<span><?php echo $nin_regular_slip_price; ?></span> to Print</small><br><br>
    <button id="regularDownloadBtn">Download Regular Slip PDF</button>
</div>
<br><br>

<!-- Standard Slip Section -->
<div class="slip_div">
    <h3>Standard Slip</h3>
    <div style="border: 1px solid #f1f1f1;"></div>
    <img src="/vtusite/images/standard_slip.png" style="width: 70%">
    <br><br>
    <small>Paid ₦<span><?php echo $nin_standard_slip_price; ?></span> to Print</small><br><br>
    <center><small>This slip is not available for personal ID checkup. If you need it, please copy the NIN <?php echo $nin;?> and verify it using the <a href="verify_nin">'Verify by NIN'</a> option, then you will be able to get it</small></center>
</div>
<br><br>

<!-- Premium Slip Section -->
<div class="slip_div">
    <h3>Premium Slip</h3>
    <div style="border: 1px solid #f1f1f1;"></div>
    <img src="/vtusite/images/premium_slip.png" style="width: 70%">
    <br><br>
    <small>Paid ₦<span><?php echo $nin_premium_slip_price; ?></span> to Print</small><br><br>
    <center><small>This slip is not available for personal ID checkup. If you need it, please copy the NIN <?php echo $nin;?> and verify it using the <a href="verify_nin">'Verify by NIN'</a> option, then you will be able to get it</small></center>
</div>

</body>
</html>