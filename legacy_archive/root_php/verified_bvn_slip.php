<?php

include_once("db_conn.php");
session_start();  

$response_data = []; // Initialize variables
$photo_data = ''; // Initialize variable
$first_name = 'N/A';
$middle_name = 'N/A';

if (isset($_GET['bvn'])) {
    $bvn = $_GET['bvn'];

    // Fetch data from seamfix_bvn_history table
    $sql = "SELECT response_data FROM bvn_history WHERE response_data LIKE ?";
    $stmt = $conn->prepare($sql);
    $like_bvn = '%"bvn":"' . $bvn . '"%';
    $stmt->bind_param("s", $like_bvn);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Output data of the first matching row
        $row = $result->fetch_assoc();
        $response_data = json_decode($row["response_data"], true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // Debugging output to check the structure
       

            // Accessing the response array
            $response_array = isset($response_data['response']) ? $response_data['response'] : [];

            // Accessing the photo data
            $photo_data = isset($response_array['photoId']) ? $response_array['photoId'] : '';

            // Ensure photo data includes the base64 prefix
            if ($photo_data && strpos($photo_data, 'data:image') === false) {
                $photo_data = 'data:image/jpeg;base64,' . $photo_data;
            }

            // Set names if available
            $first_name = isset($response_array['first_name']) ? htmlspecialchars($response_array['first_name']) : 'N/A';
            $middle_name = isset($response_array['middle_name']) ? htmlspecialchars($response_array['middle_name']) : 'N/A';
            
            
          $last_name = isset($response_array['last_name']) ? htmlspecialchars($response_array['last_name']) : 'N/A';
          
             $dob = isset($response_array['dob']) ? htmlspecialchars($response_array['dob']) : 'N/A';
             
             
                $marital_status = isset($response_array['marital_status']) ? htmlspecialchars($response_array['marital_status']) : 'N/A';
                
                   $phone_number = isset($response_array['phone_number']) ? htmlspecialchars($response_array['phone_number']) : 'N/A';
                   
                      $gender= isset($response_array['gender']) ? htmlspecialchars($response_array['gender']) : 'N/A';
                      
                         $lga_of_origin = isset($response_array['lga_of_origin']) ? htmlspecialchars($response_array['lga_of_origin']) : 'N/A';
                         
                         
                            $lga_of_Residence = isset($response_array['lga_of_Residence']) ? htmlspecialchars($response_array['lga_of_Residence']) : 'N/A';
                            
                               $state_of_origin = isset($response_array['state_of_origin']) ? htmlspecialchars($response_array['state_of_origin']) : 'N/A';
                               
                                  $nin = isset($response_array['nin']) ? htmlspecialchars($response_array['nin']) : 'N/A';
        } else {
            echo 'JSON decoding error: ' . json_last_error_msg();
        }
    } else {
        echo 'No records found.';
    }

    $stmt->close();
}


if (isset($_SESSION["email"])) {
    // Get the user's email from the session
    $user_email = $_SESSION["email"];
    
   
    // Fetch the user's balance
    $stmt = $conn->prepare("SELECT user_balance FROM account_balance WHERE email = ?");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $stmt->bind_result($user_balance);
    $stmt->fetch();
    $stmt->close();
    
    // Fetch the BVN slip price
    $stmt = $conn->prepare("SELECT bvn_slip_price FROM id_verification_price LIMIT 1");
    $stmt->execute();
    $stmt->bind_result($bvn_slip_price);
    $stmt->fetch();
    $stmt->close();
    
    // Check if user balance is sufficient
    if ($user_balance >= $bvn_slip_price) {
        // Subtract the price from user balance
        $new_balance = $user_balance - $bvn_slip_price;
        
        // Update the user's balance in the database
        $stmt = $conn->prepare("UPDATE account_balance SET user_balance = ? WHERE email = ?");
        $stmt->bind_param("ds", $new_balance, $user_email);
        $stmt->execute();
        $stmt->close();
        
 ?>
        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verified BVN Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        
        
        body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f7f7f7;
}

.container {
    width: 800px;
    margin: 20px auto;
    background-color: #fff;
    padding: 20px;
    border: 1px solid #ddd;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ddd;
    padding-bottom: 20px;
    margin-bottom: 20px;
}

.header .logo {
    width: 100px;
}

.header .bvn-logo {
    width: 50px;
}

.header-text {
    text-align: center;
}

.header h1, .header h2 {
    margin: 5px 0;
}

.content {
    display: flex;
    justify-content: space-between;
}

.content .left, .content .right {
    width: 30%;
}

.content .center {
    width: 35%;
    text-align: center;
}

.content .photo {
    width: 150px;
    height: 150px;
    
    margin-bottom: 10px;
}

.right h3 {
    color: green;
    margin-left:20%;
    font-size:30px;
    margin-top:30%;
}

.right ol {
    margin: 0;
    padding-left: 20px;
    margin-top:-15%;
}

.right ol li {
    margin-bottom: 10px;
    font-size:10px;
}

      </style>
</head>
<body>
   
   
   
   
   
   
   
       <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Card Design</title>
  
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</head>
<body>
    
    
    <div class="container">
        <div class="header">
            <img src="/vtusite/images/coat_of_arm.png" alt="Nigeria Logo" class="logo">
            <div class="header-text">
                <h1>Federal Republic of Nigeria</h1>
                <h2>Verified BVN Details</h2>
            </div>
            <img src="/vtusite/images/bvn.jpg" style="width:20%"alt="BVN Logo" class="bvn-logo">
        </div>
        <div class="content">
            <div class="left">
                <p><strong>First Name:</strong><?php
                echo $first_name;?> </p>
                <p><strong>Middle Name:</strong> 
                <?php
                echo $middle_name;?>
                </p>
                <p><strong>Last Name:</strong><?php
                echo $last_name;?></p>
                <p><strong>Date of birth:</strong>
                 <?php 
                echo $dob;?>
                </p>
                <p><strong>Gender:</strong> 
                   <?php
                echo $gender;?>
                
                </p>
                <p><strong>Marital Status:</strong> 
                   <?php
                echo $marital_status;?>
                </p>
                <p><strong>Phone Number:</strong> 
                   <?php
                echo $phone_number;?>
                </p>
                <p><strong>Enrollment Institution:</strong> 033</p>
                
                <p><strong>Origin State:</strong> 
                
                  <?php
                echo $state_of_origin;?>
                
                </p>
                
                <p><strong>Residence State:</strong> 
                
                  <?php
                  echo
                $state_of_origin;?>
                </p>
                
                <p><strong>Residential Address:</strong>
                  <?php
                $lga_of_origin;?>
                </p>
            </div>
            <div class="center">
                
             
        
           <?php 
    if ($photo_data) {
        echo '<img src="' . $photo_data . '" alt="Photo" class="photo" />';
    } else {
        echo 'No photo available.';
    }
    ?>
    
                <p><strong>BVN:</strong> 
                  <?php
$bvn = $bvn; 
// Check if $idNumber is set and has at least 11 characters (for safety)
if (isset($bvn) && strlen($bvn) >= 11) {
    $formattedId = substr($bvn, 0, 4) . ' ' . substr($bvn, 4, 3) . ' ' . substr($bvn, 7);
    echo $formattedId; // Output: formatted ID number with spaces
} else {
    echo "Invalid ID number format"; // Handle error if necessary
}
?>
                
                </p>
                <p><strong>NIN:</strong>
                
                     <?php
                echo $nin;?>
                
                </p>
                <p><strong>Enrollment Branch:</strong> 00
                
                  
                </p>
                <p><strong>Origin LGA:</strong> 
                
                   <?php
                echo $lga_of_origin;?>
                </p>
                <p><strong>Residence LGA:</strong>
                  <?php
                echo $lga_of_Residence;?>
                
                </p>
            </div>
            <div class="right">
                <h3>Verified</h3>
                
                <ol>
                    
                    <span style="margin-left:20%;font-size:10px">Please do note that;</span>
                    <li>The information on this slip remains valid until altered/modified where necessary by an authorized body</li>
                    <li>Any person/authority using the information should verify it at oneyverify.com.ng or any other channel approved by the federal government of Nigeria.</li>
                    <li>The information shown on this slip is valid for the lifetime of the holder and DOES NOT EXPIRE.</li>
                    <li>AnyVerify should not be blamed for any unauthorized alteration/copy/erasure etc done on this slip.</li>
                </ol>
            </div>
        </div>
    </div>

    
    
    
    
    <div class="hidden_print">
   
           
         
    </div>
  <?php
   // Rest of your code to process the image and other data

    } else {
        // Insufficient balance
        
        echo'
        
        
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
        .slip_div{
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding:10%;
            width:70%;
            justify-content: center;
            align-items: center;
             margin:5%;
            
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
        .swal2-popup {
            background-color: #f0f0f0;
        }
        .swal2-title {
            color: darkblue; /* Change to your desired title color */
            font-size: 25px;
            font-family: Arial, sans-serif;
        }
        .swal2-content {
            color: green;
            font-size: 10px; /* Change to your desired font size */
            font-family: Arial, sans-serif; /* Change to your desired font family */
        }
        .swal2-icon {
            color: darkblue;
        }
           </style>

        <body>
            
            <script>           document.addEventListener("DOMContentLoaded", (event) => {
            Swal.fire({
                title: "",
                text: "Insufficient Fund to Perform this Action.Please Fund you Wallet and Try Again.",
                icon: "",
                confirmButtonText: "OK"
            });
        });
    </script>
                <div class="dropdown">
  <div class="menu-container">
      



    <i style="position: absolute;right:4%;margin-top:0%; opacity:0.9;z-index:111;font-size:30px;color:white;"class="fa fa-ellipsis-v" id="menu-icon"></i>
    <div class="dropdown-content">
             <a href="bvn_available_slip.php"class="fa fa-dashboard"> Back To Verify History </a>
<div style="border:1px solid #f1f1f1;">
 <a href="users_logout.php"class="fa fa-sign-out"> Logout</a>
    </div>
  </div>
</div>
       <center>
<div class="header">
            <h2 style="font-size:20px">BVN Slip</h2>
</div>
</div>
<br><br><br><br>
        
        
   <div class="slip_div">
 
    <center><h3>BVN Slip</h3></center>
    <div style="border:1px solid #f1f1f1"></div>
     <img src="images/insufficient_fund.png"style="width:70%">
     <br><br>
    <small></small><br><br>
    <a href="dashboard.php">
    <button type="submit">Fund Wallet</button></a>
   </form>
    </div>
<br><br>
     
        
        ';

    }
} else {
    
    echo"user not logged in";
    }
    ?>
</body>
</html>
