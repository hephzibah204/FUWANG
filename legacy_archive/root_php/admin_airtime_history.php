<?php
include_once("db_conn.php");
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    // Redirect to login page if not logged in
    header("Location: admin_login.php");
    exit();
}


// Count total transactions
$sql_count = "SELECT COUNT(*) AS total_transactions FROM airtime_transactions_history";
$result_count = $conn->query($sql_count);
$row_count = $result_count->fetch_assoc();
$total_transactions = $row_count["total_transactions"];

// Fetch transactions with order
$sql = "SELECT * FROM airtime_transactions_history ORDER BY purchase_time DESC";
$result = $conn->query($sql);



echo "";
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color"content="#190F92">
    <title>history </title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            
        }

        .content {
            
        }

      
         .search-container {
            margin: 20px 0;
        }

        .search-container input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .transaction-list {
            list-style-type: none;
            padding: 0;
             font-size: 10px;
        }

        .transaction-item {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 4px;
           font-size: 10px;
           border-radius: 10px;
            
        }
.transaction-item:hover{
    
    cursor:pointer;
    background-color:#f1f1f1;
    
}
        .transaction-item img {
            width: 30%;
            height: 25%;
            border:10px solid #f1f1f1;
            border-radius: 50%;
            margin-right: 0;
        }

        .transaction-details {
            flex-grow: 1;
           
        }

        .transaction-details h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
        }

        .transaction-details p {
            margin: 0;
            color: #666;
            font-size: 10px;
            
        }

        .transaction-status {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php

include_once("history_skeletal_loader.php");
?>


   
<div id="content" class="content">
        <div class="container">
            <div class="header">
              
              
            </div>
            <div class="search-container">
                <input type="text" id="searchInput" onkeyup="searchTransactions()" placeholder="Search transaction...">
            </div>
            <span>Total transactions (<?php echo $total_transactions;?>)
            </span>
            
            <br><br>
            <ul id="transactionList" class="transaction-list">
  <?php
include_once("db_conn.php");

define('MTN', 'MTN'); 
define('GLO', 'GLO');
define('AIRTEL', 'AIRTEL');
define('MOBILE9', 'MOBILE9');


$sql = "SELECT * FROM airtime_transactions_history ORDER BY purchase_time DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    $amount = $row["amount"];
    $phone = $row["phone"];
    $fullname = $row["fullname"];
    $purchase_time = $row["purchase_time"];
    $network = $row["network"]; // Assuming a network column exists

    $network_image = "";

if ($network == MTN) {
  $network_image = "mtn.png";
} else if ($network == GLO) {
  $network_image = "glo_icon.jpg";
} else if ($network == AIRTEL) {
  $network_image = "airtel_icon.jpg";
} else if ($network == MOBILE9) {
  $network_image = "9mobile_icon.jpg";
} else {
 $network_image = "glo_icon.jpg";
}


    // Display transaction in HTML format
    echo '<li class="transaction-item">
        
      <img src="/vtusite/images/' . $network_image . '" alt="' . $network . '">
      <div class="transaction-details">
          <hr>
        <h3>Airtime Purchase</h3>
        <p>₦'.$amount.' Airtime sent to '.$network.' number '.$phone.' <br>from '.$fullname.' on '.$purchase_time.'</p>
      </div>
      <button style="border:1px solid white;background-color:green;color:white;font-size:12px;">success</button>
    </li><hr>';
  }
} else {
  echo "No transactions found";
}

$conn->close();
?>

<script>
    
          function goBack() {
            window.history.back();
        }

        function searchTransactions() {
            var input, filter, ul, li, p, i, txtValue;
            input = document.getElementById('searchInput');
            filter = input.value.toLowerCase();
            ul = document.getElementById('transactionList');
            li = ul.getElementsByTagName('li');

            for (i = 0; i < li.length; i++) {
                p = li[i].getElementsByTagName("p")[0];
                txtValue = p.textContent || p.innerText;
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    li[i].style.display = "";
                } else {
                    li[i].style.display = "none";
                }
            }
        }
        
    </script>

  
</body>
</html>
