<?php
session_start();
include_once("db_conn.php");

// Check and perform redirection based on nin_search_type
$apiQuery = "SELECT dataverify_api_key, dataverify_endpoint_phone FROM api_center LIMIT 1";
$apiResult = $conn->query($apiQuery);

if ($apiResult->num_rows > 0) {
    $apiData = $apiResult->fetch_assoc();
    $token = $apiData['dataverify_api_key'];
    $url = $apiData['dataverify_endpoint_phone'];
} else {
    echo json_encode(["success" => false, "message" => "API credentials not found."]);
    exit;
}

// Function to generate a random transaction ID
function generateTransactionId() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $transaction_id = 'dataverify_nin';
    for ($i = 0; $i < 20; $i++) {
        $transaction_id .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $transaction_id;
}

// Initialize response data
$response_data = null;

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $phone = $_POST["phone"];
    $amount = $_POST["amount"];

    if (isset($_SESSION["email"])) {
        $user_email = $_SESSION["email"];

        // Function to get user balance
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

        // Function to get NIN premium slip price
        function getNinPremiumSlipPrice($conn) {
            $query = "SELECT nin_by_number_price FROM verification_price";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $stmt->bind_result($nin_by_number_price);
            $stmt->fetch();
            $stmt->close();
            return $nin_by_number_price;
        }

        // Get user balance and price
        $user_balance = getUserBalance($conn);
        $nin_by_number_price = getNinPremiumSlipPrice($conn);

        // Check if balance is sufficient
        if ($user_balance >= $nin_by_number_price) {
            // Prepare to send request to the API
            $ch = curl_init($url);

            $data = [
                'api_key' => $token,
                'phone' => $phone
            ];

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo json_encode(['status' => false, 'message' => 'Curl error: ' . curl_error($ch)]);
                exit;
            } else {
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $response_data = json_decode($response, true);
                if ($httpCode == 200 && isset($response_data['status']) && $response_data['status'] === true) {
                    // Subtract amount from user balance
                    $query = "UPDATE account_balance SET user_balance = user_balance - ? WHERE email = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ds", $nin_by_number_price, $_SESSION['email']);
                    $stmt->execute();
                    $stmt->close();

                    // Log transaction
                    $transaction_id = generateTransactionId();
                    $query = "INSERT INTO id_history (user_email, transaction_id, response_data) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $response_json = json_encode($response_data['data']);
                    $stmt->bind_param("sss", $user_email, $transaction_id, $response_json);
                    $stmt->execute();
                    $stmt->close();

                    // Fetch specific fields and send response
                    $image = isset($response_data['data']['image']) ? $response_data['data']['image'] : null;
                    $nin_fetched = isset($response_data['data']['nin']) ? $response_data['data']['nin'] : null;

                    echo json_encode([
                        'status' => true,
                        'image' => $image,
                        'nin' => $nin_fetched,
                        'data' => $response_data['data']
                    ]);
                    exit;
                } else {
                    echo json_encode(['status' => false, 'message' => 'API Error: ' . $response_data['message']]);
                    exit;
                }
            }
            curl_close($ch);
        } else {
            echo json_encode(['status' => false, 'message' => 'Insufficient balance, please fund your wallet and try again']);
            exit;
        }
    } else {
        echo json_encode(['status' => false, 'message' => 'Session email not set']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="theme-color" content="#15gt44">
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1,minimum-scale=1,width=device-width,interactive-widget=resizes-content,initial-scale=1.0, user-scalable=no">
<meta name="theme-color"content="darkblue">
    <title>NIN verification</title>
       <link rel="icon" href="images/logo2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>

    <style>
        .hidden {
            display: none;
        }
        .loader {
            display: none;
            border: 4px solid #f3f3f3; /* Light grey */
            border-top: 4px solid #3498db; /* Blue */
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
          
        .content {
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            background-color: #fff;
            width:80%;
            display: flex;
            justify-content: center;
            align-items: center;
            
            margin: 0;
            text-align: center;
        }
        .img {
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
        img{
            
            
           width:70%;
           border-radius:10px;
        }
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
input[type=text], input[type=tel], select, input[type=password] {
            width: 100%;
            padding: 4%;
            margin: 5px 0 22px 0;
            display: inline-block;
            border: none;
            background: #f1f1f1;
        }
input[type=tel] {
            width: 93%;
            padding: 4%;
            margin: 5px 0 22px 0;
            display: inline-block;
            border: none;
            background: #f1f1f1;
        }

        /* Add a background color when the inputs get focus */

        input[type=text]:focus, input[type=password]:focus {
            background-color: #;
            outline: none;
        }
        .error-msg {
            color: red;
        }
   
 
  
     
    </style>
    
     <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get references to the input fields
            var ninInput = document.getElementById('nin');
            var idNumberInput = document.getElementById('idNumber');
            
            // Add an event listener to the nin input field
            ninInput.addEventListener('input', function() {
                // Update idNumber input with the value from nin input
                idNumberInput.value = ninInput.value;
            });
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    
    <div class="dropdown">
  <div class="menu-container">
      
    <i style="position: absolute;right:4%;margin-top:0%; opacity:0.9;z-index:111;font-size:30px;color:white;"class="fa fa-ellipsis-v" id="menu-icon"></i>
    <div class="dropdown-content">
        
      <a href="nin_verification_history.php"class="fa fa-history"> verification history </a>
     <a href="dashboard.php"class="fa fa-dashboard"> Back To Dashboard  </a>
<div style="border:1px solid #f1f1f1;">
 <a href="users_logout.php"class="fa fa-sign-out"> Logout</a>
    </div>
  </div>
</div>
       <center>
<div class="header">
            <h2 style="font-size:20px">Verify NIN</h2>
</div>
</div>
<br><br><br><br>
    <span>
             <table style="position:;margin-top:10%" width="100%">
                <th onclick="selectNetwork('MTN')" style=" background-color:#f1f1f1;border-radius:10px">
                    <img src="/vtusite/images/nin.jpg" style="width:80%;height:85px;border-radius:10px">
                </th>
                
                   <th onclick="selectNetwork('GLO')" style=" background-color:#f1f1f1;border-radius:10px">
                    <img src="/vtusite/images/premium_slip.png" style="width:80%;height:85px;border-radius:10px">
                </th>
                  <th onclick="selectNetwork('AIRTEL')"style=" background-color:#f1f1f1;border-radius:10px">
                    <img src="/vtusite/images/standard_slip.png" style="width:80%;height:85px;border-radius:10px">
                </th>
             
            </table>
        </span>
        <br>
        <div class="line"style="border:1px solid #f1f1f1;"></div>
       <br>
    
  
    <center>
       
        <div id="error-message" style="color: red;"></div>
        
        
              
        <form id="nin-form" class="<?php echo isset($response_data) && $response_data['status'] ? 'hidden' : ''; ?>">
        
        
       

        <select id="methodSelect" name="nn" onchange="redirectBasedOnSelection()">
            <option>Select Method</option>
            
            <option value="PHONE">BY PHONE NUMBER</option>
        </select>
   
            <br>
           
           
           
           
      
                    <select id="slip_type" name="nin_by_number_price">
            <option value="">Select Slip Type</option>
           
            <option value="nin_by_number_price">Amount to Paid</option>
        </select>

 <br>
            <input type="tel" id="phone"placeholder ="Enter your phone "name="phone" maxlength="11"required>
            <br>
        
        
            
            <input id="amount"type="tel" id="amount" name="amount" placeholder="Amount" readonly>
        
   
           
       


    <script>
        document.getElementById('slip_type').addEventListener('change', function() {
            var slipType = this.value;
            if (slipType) {
                // Make AJAX request to fetch the price
                $.ajax({
                    url: 'fetch_nin_slip_amount',
                    type: 'GET',
                    data: { slip_type: slipType },
                    dataType: 'json',
                    success: function(response) {
                        if (response.price !== undefined) {
                            document.getElementById('amount').value = response.price;
                        } else {
                            document.getElementById('amount').value = '';
                            alert(response.error || 'An error occurred');
                        }
                    },
                    error: function() {
                        document.getElementById('amount').value = '';
                        alert('Failed to fetch price');
                    }
                });
            } else {
                document.getElementById('amount').value = '';
            }
        });
    </script>

           
           
                 
           <br>
          
        <button type="submit">Verify NIN</button>
   </form>

 <div class="loader"></div>
        <div id="result" class="hidden">
            <div class="content">
                  <div class="img">
           <span style="color:green">Record Found</span>
            
            <center><div style="width:70%;border-radius:10px"id="image-container"></div>
            
            <div id="nin-container"></div>
            <form action="nin_verification_history.php" method="GET">
        <input type="hidden" id="nin-container" name="idNumber" placeholder="Enter your ID" maxlength="11" required>
 
                <button type="submit">Print Slip</button>
            </form>
        </div>
</div>
       
    </center>

    <script>
        $(document).ready(function() {
            $('#nin-form').on('submit', function(event) {
                event.preventDefault();
                var phone = $('#phone').val();

                // Show loader while waiting for response
                $('.loader').show();

                $.ajax({
                url: '',
                    type: 'POST',
                    data: {
                        phone: phone,
                        ajax: true
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.status) {
                            $('#error-message').text('');
                            $('#result').removeClass('hidden');
                            $('#nin-form').addClass('hidden');

                            if (data.image) {
    $('#image-container').html('<img style="width: 70%; border-radius: 10px;" src="' + data.image + '" alt="User Image">');

                            }
                            if (data.nin) {
                                $('#nin-container').html('<p>NIN: ' + data.nin + '</p>');
                            }

                            $('#pdf-data').val(JSON.stringify(data.data));
                        } else {
                            $('#error-message').text(data.message);
                        }
                    },
                    error: function() {
                        $('#error-message').text('An error occurred while processing your request.');
                    },
                    complete: function() {
                


// Hide loader after request completes
                        $('.loader').hide();
                    }
                });
            });
        });
    </script>
</body>
</html>
 
 