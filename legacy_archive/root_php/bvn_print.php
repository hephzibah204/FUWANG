<?php
session_start();
include_once("db_conn.php");


// Function to generate a random transaction ID
function generateTransactionId() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $transaction_id = 'sadeeqdata_bvn';
    for ($i = 0; $i < 20; $i++) {
        $transaction_id .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $transaction_id;
}

$response_data = null; // Initialize response data

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    // Retrieve NIN from POST data
    $bvn = $_POST["bvn"];
    $amount = $_POST["amount"];
    
    if (isset($_SESSION["email"])) {
        // Get the user's email from the session
        $email = $_SESSION["email"];

        // Function to get user balance
        function getUserBalance($conn) {
            // Check if session is set
            if (!isset($_SESSION['email'])) {
                $GLOBALS['error'][] = "Session email not set";
                return 0; // Return default balance if session email is not set
            }
            
            // Get email from session
            $email = $_SESSION['email'];

            // Prepare and execute SQL query to retrieve user's balance
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
            $query = "SELECT bvn_by_bvn FROM verification_price";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $stmt->bind_result($bvn_by_bvn);
            $stmt->fetch();
            $stmt->close();

            return $bvn_by_bvn;
        }

        // Get user balance
        $user_balance = getUserBalance($conn);

        // Get NIN premium slip price
        $bvn_by_bvn = getNinPremiumSlipPrice($conn);

        // Check if user balance is sufficient
        if ($user_balance >= $bvn_by_bvn) {
            // Prepare to send request to the API
            $url = 'https://ninprint.com.ng/api/bvn-search/';
            $token = '91ed87b5856c52a8a942f24d6dff5f1832ee6008';
            $data = json_encode(["bvn" => "$bvn"]);

            $headers = [
                'Authorization: Token ' . $token,
                'Content-Type: application/json'
            ];

            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => $headers,
                CURLINFO_HEADER_OUT => true, // Get request header info
            ]);

            $response = curl_exec($ch);

            if ($response === false) {
                curl_close($ch);
                die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
            }

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $request_headers = curl_getinfo($ch, CURLINFO_HEADER_OUT);
            curl_close($ch);

            $response_data = json_decode($response, true);

            if (isset($response_data['status']) && $response_data['status'] === true) {
                // Start transaction
                $conn->begin_transaction();

                try {
                    // Subtract the amount from user balance
                    $query = "UPDATE account_balance SET user_balance = user_balance - ? WHERE email = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ds", $bvn_by_bvn, $_SESSION['email']);
                    $stmt->execute();
                    $stmt->close();

                    // Generate a random transaction ID
                    $transaction_id = generateTransactionId();

                    // Insert response data into id_history table
                    $query = "INSERT INTO id_history_bvn (email, transaction_id, response_data) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $response_json = json_encode($response_data['data']);
                    $stmt->bind_param("sss", $email, $transaction_id, $response_json);
                    $stmt->execute();
                    $stmt->close();

                    // Commit transaction
                    $conn->commit();

                    // Fetch specific fields from the response data
                    $image = isset($response_data['data']['base64Image']) ? $response_data['data']['base64Image'] : null;
                    $bvn_fetched = isset($response_data['data']['bvn']) ? $response_data['data']['bvn'] : null;

 
                $_SESSION['bvn_fetched'] = $bvn_fetched;
                
                    // Send JSON response back to AJAX request
                    echo json_encode([
                        'status' => true,
                        'image' => $image,
                        'bvn' => $bvn_fetched,
                        'data' => $response_data['data']
                    ]);
                    exit;
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollback();
                    echo json_encode([
                        'status' => false,
                        'message' => 'Transaction failed: ' . $e->getMessage()
                    ]);
                    exit;
                }
            } else {
                // Send JSON response back to AJAX request
                echo json_encode([
                    'status' => false,
                    'message' => 'Error: ' . $response_data['message']
                ]);
                exit;
            }
        } else {
            // Send JSON response back to AJAX request
            echo json_encode([
                'status' => false,
                'message' => 'Insufficient balance, please fund your wallet and try again'
            ]);
            exit;
        }
    } else {
        // Send JSON response back to AJAX request
        echo json_encode([
            'status' => false,
            'message' => 'Session email not set'
        ]);
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
    <title>NIN BVN</title>
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
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#slip_type').change(function() {
                var selectedMethod = $(this).val();

                if (selectedMethod) {
                    $.ajax({
                        type: 'POST',
                        url: 'get_bvn_price.php',
                        data: {method: selectedMethod},
                        dataType: 'json',
                        success: function(response) {
                            if (response.error) {
                                alert('Error: ' + response.error);
                            } else {
                                $('#amount').val(response.value);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            alert('AJAX error: ' + textStatus + ' : ' + errorThrown);
                        }
                    });
                } else {
                    $('#amount').val('');
                }
            });
        });
    </script>
     <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get references to the input fields
            var bvnInput = document.getElementById('bvn');
            var bvnidInput = document.getElementById('bvnid');
            
            // Add an event listener to the nin input field
            bvnInput.addEventListener('input', function() {
                // Update idNumber input with the value from nin input
                bvnidInput.value = bvnInput.value;
            });
        });
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    
    <div class="dropdown">
  <div class="menu-container">
      
    <i style="position: absolute;right:4%;margin-top:0%; opacity:0.9;z-index:111;font-size:30px;color:white;"class="fa fa-ellipsis-v" id="menu-icon"></i>
    <div class="dropdown-content">
        
      <a href="bvn_verification_history.php"class="fa fa-history"> verification history </a>
     <a href="dashboard.php"class="fa fa-dashboard"> Back To Dashboard  </a>
<div style="border:1px solid #f1f1f1;">
 <a href="users_logout.php"class="fa fa-sign-out"> Logout</a>
    </div>
  </div>
</div>
       <center>
<div class="header">
            <h2 style="font-size:20px">Verify BVN</h2>
</div>
</div>
<br><br><br><br>
    <span>
             <table style="position:;margin-top:10%" width="100%">
                
                  <th onclick="selectNetwork('AIRTEL')"style=" background-color:#f1f1f1;border-radius:10px">
                    <img src="/vtusite/images/bvn.jpg" style="width:80%;height:85px;border-radius:10px">
                </th>
             
            </table>
        </span>
        <br>
        <div class="line"style="border:1px solid #f1f1f1;"></div>
       <br>
    
  
    <center>
       
        <div id="error-message" style="color: red;"></div>
      
    <form id="bvn-form" method="post" class="<?php echo isset($response_data) && $response_data['status'] ? 'hidden' : ''; ?>">
        
        <select id="slip_type" name="bvn_premiun_slip_price">
            <option value="">Select Method</option>
            <option value="bvn_premium_slip_price">BVN</option>
        </select>
        <br>
        <input type="tel" id="bvn" placeholder="Enter your BVN" name="bvn" maxlength="11" required>
        <br>
        <input type="tel" id="amount" name="amount" placeholder="Amount" readonly>
        <br>
        <button type="submit">Verify BVN</button>
    </form>
 
 <div class="loader"></div>
        <div id="result" class="hidden">
            <div class="content">
                  <div class="img">
           <span style="color:green">Record Found</span>
            
            <center><div id="image-container"></div>
            
            <div id="nin-container"></div>
             
            <form action="bvn_available_slip.php"method="GET">
           
               <input type="hidden" id="bvnid" name="bvn" placeholder="Enter your ID" maxlength="11" required>
                <button type="submit">Print Slip</button>
            </form>
        </div>
</div>
       
    </center>

    <script>
        $(document).ready(function() {
            $('#bvn-form').on('submit', function(event) {
                event.preventDefault();
                var bvn = $('#bvn').val();

                // Show loader while waiting for response
                $('.loader').show();

                $.ajax({
                    url: '',
                    type: 'POST',
                    data: {
                        bvn: bvn,
                        ajax: true
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.status) {
                            $('#error-message').text('');
                            $('#result').removeClass('hidden');
                            $('#bvn-form').addClass('hidden');

                            if (data.image) {
                                $('#image-container').html('<img style="width:70%;border-radius:10px" src="data:image/jpeg;base64,' + data.image + '" alt="User Image" />');
                            }
                            if (data.bvn) {
                                $('#nin-container').html('<p>BVN: ' + data.bvn + '</p>');
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