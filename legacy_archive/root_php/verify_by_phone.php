<?php
// Start output buffering to avoid "headers already sent" issues
ob_start();

// Ensure session settings and start are at the top
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);
session_start();
include_once("db_conn.php");
include_once("header_nin.php");
$developer_id = isset($_SESSION['developerId']) ? $_SESSION['developerId'] : null;

// If $developer_id is null, stop further execution and show the message
if ($developer_id === null) {
    die("Sorry, you can't connect securely. Contact the owner at 08113910395.");
}

// Set a flag to conditionally block CSS
$block_css = !$developer_id;  // If there's no developer_id, block CSS
  $nin_price = 0;
        $priceQuery = "SELECT nin_by_number_price FROM verification_price LIMIT 1";
        $priceResult = $conn->query($priceQuery);
        if ($priceResult->num_rows > 0) {
            $priceData = $priceResult->fetch_assoc();
            $nin_price = $priceData['nin_by_number_price'];
        }
// Check if request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include_once("access_request_details.php");
    if (!isset($_POST['call_token']) || $_POST['call_token'] !== $_SESSION['call_token']) {
        echo json_encode(['status' => false, 'message' => 'Invalid request. Please try again.']);
        exit();
    } else {
        if (isset($_POST['ajax'])) {
            $nin = filter_input(INPUT_POST, 'nin', FILTER_SANITIZE_STRING);

            // Validate NIN format
            if (!preg_match('/^\d{11}$/', $nin)) {
                echo json_encode(['status' => false, 'message' => 'Invalid NIN format']);
                exit;
            }
include_once("security_plan.php");
           

            if (isset($_SESSION["email"])) {
                $user_email = $_SESSION["email"];

                function getUserBalance($conn) {
                    if (!isset($_SESSION['email'])) {
                        return 0;
                    }
                    $email = $_SESSION['email'];
                    $query = "SELECT user_balance FROM account_balance WHERE email = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $stmt->bind_result($user_balance);
                    $stmt->fetch();
                    $stmt->close();
                    return $user_balance;
                }

               include_once("deduct_phone.php");
                $user_balance = getUserBalance($conn);
                $nin_by_number_price = getNinPremiumSlipPrice($conn);

                if ($user_balance >= $nin_by_number_price) {
                    // Deduct the balance first
                    $new_balance = $user_balance - $nin_by_number_price;
                    $update_query = "UPDATE account_balance SET user_balance = ? WHERE email = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("ds", $new_balance, $user_email);
                    $update_stmt->execute();
                    $update_stmt->close();

                    // Proceed with the API call
                    $transaction_id = generateTransactionId();
                  include_once("phone_api.php");

                  
                    

                    if ($httpCode == 200 && $response) {
                        $responseData = json_decode($response, true);

$status = "success"; // Set the status as success when the API call is successful
    $balance_before = $user_balance; // The user's balance before the transaction
    $balance_after = $new_balance; // The new balance after the transaction is deducted
    $transaction_id = generateTransactionId(); // Generate the transaction ID

    // Prepare the insert query for the all_orders table
    $insertOrderQuery = "INSERT INTO all_orders (user_email, order_type, balance_before, balance_after, transaction_id, status) 
                         VALUES (?, 'NIN by phone', ?, ?, ?, ?)";

    $stmt = $conn->prepare($insertOrderQuery);
    $stmt->bind_param("sddss", $user_email, $balance_before, $balance_after, $transaction_id, $status);
    $stmt->execute();
    $stmt->close();
                        // Log transaction and response
                        $stmt = $conn->prepare("INSERT INTO nin_history (user_email, transaction_id, response_data) VALUES (?, ?, ?)");
                        $stmt->bind_param("sss", $user_email, $transaction_id, $response);
                        $stmt->execute();
                        $stmt->close();

                        $nin_fetched = $responseData['response'][0]['nin'] ?? 'No record found';
                        $image = $responseData['response'][0]['photo'] ?? '';

                        echo json_encode([
                            'status' => true,
                            'image' => $image,
                            'nin' => $nin_fetched,
                            'data' => $responseData['response']
                        ]);
                        exit;
                    } else { 
                     $status = "failure"; // Set the status as failure when the API call fails
    $balance_before = $user_balance; // The user's balance before the transaction
    $balance_after = $user_balance; // No change in balance since the transaction failed
    $transaction_id = generateTransactionId(); // Generate the transaction ID

    // Insert a failure record into the all_orders table
    $insertOrderQuery = "INSERT INTO all_orders (user_email, order_type, balance_before, balance_after, transaction_id, status) 
                         VALUES (?, 'NIN by phone', ?, ?, ?, ?)";

    $stmt = $conn->prepare($insertOrderQuery);
    $stmt->bind_param("sddss", $user_email, $balance_before, $balance_after, $transaction_id, $status);
    $stmt->execute();
    $stmt->close();
                    $refund_query = "UPDATE account_balance SET user_balance = ? WHERE email = ?";
                    $refund_stmt = $conn->prepare($refund_query);
                    $refund_stmt->bind_param("ds", $user_balance, $user_email);  // Restore original balance
                    $refund_stmt->execute();
                    $refund_stmt->close();
                        // Refund balance if API response is not successful
                        $refund_query = "UPDATE account_balance SET user_balance = ? WHERE email = ?";
                        $refund_stmt = $conn->prepare($refund_query);
                        $refund_stmt->bind_param("ds", $user_balance, $user_email);  // Restore original balance
                        $refund_stmt->execute();
                        $refund_stmt->close();

                        echo json_encode(['status' => false, 'message' => 'No record found']);
                        exit;
                    }
                } else {
                    echo json_encode(['status' => false, 'message' => 'Insufficient balance, please fund your wallet and try again']);
                    exit;
                }
            } else {
                echo json_encode(['status' => false, 'message' => 'User session not found']);
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1,minimum-scale=1,width=device-width,interactive-widget=resizes-content,initial-scale=1.0, user-scalable=no">
     <?php if (!$block_css): ?>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="viewport" content="initial-scale=1,minimum-scale=1,width=device-width,interactive-widget=resizes-content,initial-scale=1.0, user-scalable=no">
<meta name="theme-color"content="#190F92"> 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://sadeeqdata.com.ng/assets/css/nin_style.min.css">
   
       <style> 
       button{
           background-color:<?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?>;
       }
.loader {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white background */
  display: none; /* Initially hidden */
  justify-content: center;
  align-items: center;
  z-index: 9999; /* Ensure it's on top of other elements */
}

/* CSS for the pulsating animation */
@keyframes rotate {
  0% {
    transform: rotate(0deg); /* Start rotation from 0 degrees */
  }
  100% {
    transform: rotate(360deg); /* End rotation at 360 degrees */
  }
}

.loader::after {
  content: '';
  display: block;
  width: 60px; /* Adjust the size of the spinner */
  height: 60px;
  border-radius: 50%;
  border: 6px solid red; /* Spinner color */
  border-color: <?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?> transparent <?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?> transparent; /* Spinner color pattern */
  animation: rotate 1.5s linear infinite; /* Rotate animation with infinite loop */
}
</style>
    
    

<script>
  let loaderTimeout; // Variable to store the timeout reference

function showLoader() {
  var loader = document.getElementById('loader');
  loader.style.display = 'flex'; // Show the loader

  // Set a timeout to hide the loader after 30 seconds
  loaderTimeout = setTimeout(function () {
    hideLoader();
  }, 1000);
}

function hideLoader() {
  var loader = document.getElementById('loader');
  loader.style.display = 'none'; // Hide the loader

  // Clear the previous timeout if it exists
  clearTimeout(loaderTimeout);
}


  // Show the loader when the page starts loading
    showLoader();


    // Add an event listener for when the page has finished loading
    window.onload = function () {
      hideLoader(); // Hide the loader when the page has finished loading
    }

</script><script>
document.addEventListener('DOMContentLoaded', function () {
  // Get the form and submit button elements
  var form = document.querySelector('form');
  var submitButton = document.querySelector('button[type="submit"]');

  // Add a submit event listener to the form
  form.addEventListener('submit', function () {
    // Add the spinner HTML to the inner HTML of the submit button
    if (submitButton) {
      submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" style="color: #092C9F;"></span> <b style="color: #092C9F;">Verifying...</b>';
    }

    // You can also perform additional actions here based on the form submission
    // For example, you can use AJAX to submit the form data asynchronously.
  });
});


document.addEventListener('DOMContentLoaded', function () {
  // Get all <a> elements on the page
  var aElements = document.querySelectorAll('a');

  // Add a click event listener to each <a> element
  aElements.forEach(function(aElement) {
    aElement.addEventListener('click', function (event) {
      // Check if the href attribute is empty or equals to "#"
      if (aElement.getAttribute('href') !== "" && aElement.getAttribute('href') !== "#") {
        showLoader();
      }
      // Prevent the default behavior of the link (e.g., following the href)

    });
  });
});
</script>
<script>
    function toggleDropdown() {
      const dropdown = document.querySelector('.dropdown');
      dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }
  </script>
 <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get references to the input fields
            var ninInput = document.getElementById('nin');
            var ninidInput = document.getElementById('ninid');
            
            // Add an event listener to the nin input field
            ninInput.addEventListener('input', function() {
                // Update idNumber input with the value from nin input
                ninidInput.value = ninInput.value;
            });
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
          
 <?php


// Compare the current host with site_url (only domain part, ignoring scheme)
if ($siteUrl && parse_url($siteUrl, PHP_URL_HOST) === $currentHost) {
    // If the site URL matches the current host, include the external file
    include_once("clients/loader/.php");
} else {
    // Optionally, log or show a message if the URLs don't match
    die("403 Forbidden: The current host does not match the live site URL.");
}

?>
    
</head>
<body> 
 <div class="container">
  <header class="header">
      <h1><a href="dashboard">Dashboard</a> | VerifyNIN</h1>
      <div class="icons">
        <i class="fa fa-bars icon" onclick="toggleDropdown()"></i>
        <!-- Dropdown Menu -->
        <div class="dropdown">
          <div class="dropdown-item">
              <a href="settings">
            <i class="fa fa-cog"></i> Settings
            </a>
          </div> 
          <div class="dropdown-item">
              <a href="profile">
            <i class="fa fa-user"></i> Profile
            </a>
          </div>
          <div class="dropdown-item">
              <a href="logout">
            <i class="fa fa-sign-out-alt"></i> Logout
            </a>
          </div>
        </div>
      </div>
    </header>
    <hr> 
    <center style="color:green"></center><?php echo $user_email;?>
   <br><br><br>
    <?php endif; ?>
     <div class="container">
        <h2>Verify by Number</h2>
<br>
  
    <center>
       
        <div id="error-message" style="color: red;"></div>
        
        
      
            
       <form id="nin-form" class="<?php echo isset($response_data) && $response_data['status'] ? 'hidden' : ''; ?>">
    
    <br> 
     <label for="nin">This service will cost you =₦<?php echo $nin_price;?></label>
       <input type="hidden" name="call_token" value="<?php echo $_SESSION['call_token']; ?>">
    
    <br>
    <input type="text" id="nin" placeholder="Enter your NIN" name="nin" maxlength="11" required>
    <br>
    
    <button type="submit">Verify</button>
     
</form>
  <div class="bottom-menu">
    <a href="#" class="active">
        <i style="color:<?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?>"class="fas fa-fingerprint"></i>
        Verify NIN
    </a>
    <a href="verified_history.php" class="inactive">
        <i class="fas fa-history"></i>
        History
    </a>
</div>

<div class="loader"></div>
<div id="result" class="hidden">
    <div class="content">
        <div class="img">
            <span style="color:green">Response</span>
            <center>
                <div style="width:70%;border-radius:10px" id="image-container"></div>
                <div id="nin-container"></div>
                <form action="verified_history.php"method="GET">
                    <input type="hidden" id="ninid" name="nin" placeholder="Enter your Number" maxlength="11" required>
                    <button type="submit">Print Slip</button>
                </form>
            </center>
        </div>
    </div>
</div>
<script>
	<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://sadeeqdata.com.ng/script_request/nin_js.js");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
echo $response;

?>		
</script>