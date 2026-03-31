<?php

include_once("db_conn.php");

// Check if user is logged in
include_once("whois_admin.php");


// Fetch total number of referred users
$sql_total_users = "SELECT COUNT(*) AS total_users FROM customers2";
$result_total_users = $conn->query($sql_total_users);
if ($result_total_users && $result_total_users->num_rows > 0) {
    $row_total_users = $result_total_users->fetch_assoc();
    $total_users = $row_total_users['total_users'];
} else {
    $total_users = 0;
}

// Prepare SQL query to sum user_balance for all users
$query = "SELECT SUM(user_balance) AS total_balance FROM account_balance";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    // Fetch the total balance
    $row = $result->fetch_assoc();
    $total_balance = $row['total_balance'];
} else {
    $total_balance = 0;
}

// Fetch the count of online users
$sql_online_users = "SELECT COUNT(*) AS online_users FROM customers1 WHERE online_status = 'online'";
$result_online_users = $conn->query($sql_online_users);

if ($result_online_users) {
    $row_online_users = $result_online_users->fetch_assoc();
    $online_users_count = $row_online_users['online_users'];
} else {
    $online_users_count = 0;
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="theme-color" content="#190F92">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Admin Dashboard</title>
<link rel="manifest" href="/manifest.json">
   
  <!-- Bootstrap CSS -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>
    /* Custom Styles */
    body {
      background-color: #f1f1f1;
      font-family: Arial, sans-serif;
    }

    .section-container {
      margin-bottom: 20%;
border-radius:10px;
    }

    .section-title {
      background-color: darkblue;
      color: #fff;
      padding: 10%;
      margin-bottom: 10px;
    
border-radius:10px; 
    }

    .section-content {
      padding: 20%;
      background-color: #fff;
      border: 1px solid #ddd;
border-radius:10px;
      
      box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    }

    .section-content ul {
      list-style: none;
      padding: 0;
border-radius:10px;
    }

    .section-content ul li {
      margin-bottom: 10%;
    }

    .section-content ul li:last-child {
      margin-bottom: 0;
    }
.header{
background-color:#190F92;
box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
position:fixed;
width:100%;
padding:8%;
z-index:1;

}
.interface{
background-image: url('vtusite/images/header.gif'); /* Replace 'your-image-url.jpg' with the URL of your desired image */
      background-size: cover;
      background-position: center;
background-repeat:no-repeate;
box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
background-color:white;
width:90%;
color:white;
padding:12%;
top:13%;
position: absolute;
border-radius:10px;
left:5%;

}
/* Style the dropdown container */
.dropdown {
  
margin-top:-15%;
  justify-content: flex-end; /* Align items to the right */
}

/* Style the menu container */
.menu-container {
  position: relative;
border-radius:10px;
}

/* Style the menu icon */
#menu-icon {
  cursor: pointer;
}

/* Hide the dropdown content by default */
.dropdown-content {
  display: none;
  position: absolute;
  background-color: #f9f9f9;
  min-width: 160px;
font-size:10px;
opacity:;
border-radius:10px;
margin-top:21%;
  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
  z-index: 1;
  right: 0; /* Position the dropdown content to the right */
}

/* Show the dropdown content when the icon is clicked */
.menu-container:hover .dropdown-content {
  display: block;
}

/* Style the dropdown links */
.dropdown-content a {
  color: black;
  padding: 12px 16px;
  text-decoration: none;
  display: block;
}

/* Change the background color of links on hover */
.dropdown-content a:hover {
  background-color: #f1f1f1;
}
a{
        color:white;
    font-weight:bold;
} 
  card-title{
        color:white;
        border:10px;
    }
        .whatsapp-icon {
      position: fixed;
      right: 20px;
      bottom: 20px;
      background-color: darkblue;
      border-radius: 50%;
      padding: 15px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
      cursor: pointer;
      z-index: 999;
    }
    .whatsapp-icon i {
      font-size: 30px;
      color: white;
    }

  </style>
</head>
<body>
<a href="dataverify_api_prices" target="_blank" class="whatsapp-icon">
    <i class="fa fa-credit-card"></i>
  </a>

<div class="header">

<nobr><strong style="width:40%;height:86%;border-radius:80%; position: absolute;top:5%;bold:10px;left:4%;color:white;font-size:25px">Main Dashboard</strong></nobr>
</h1>
 <div class="dropdown">
  <div class="menu-container">
    <i style="position: absolute;right:4%;margin-top:7%;font-size:35px;color:white;"class="fa fa-bars" id="menu-icon"></i>
    <div class="dropdown-content">
      <a href="admin_new_users_rewards.php"class="fa fa-money"> Commission Dashboard</a>
      <a href="site_settings_adsite.php"class="fa fa-gear"> Account Settings</a>
     
<hr>
 <a href="resellers_logout.php"class="fa fa-sign-out"> Logout</a>
    </div>
  </div>
</div>


</h1>

</div>
<br>

<div class="interface">

<center>
<strong style="font-size:20px;color:#;"><nobr>Total Users balance</nobr><br>

<?php 

function getTotalAccountBalance($conn) {
    $sql_total_balance = "SELECT SUM(user_balance) AS total_balance FROM account_balance";
    $result_total_balance = $conn->query($sql_total_balance);

    if ($result_total_balance && $result_total_balance->num_rows > 0) {
        $row_total_balance = $result_total_balance->fetch_assoc();
        return formatNumber($row_total_balance['total_balance']);
    } else {
        return '0'; // If no balance found, return 0
    }
}

function formatNumber($number) {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    } else {
        return $number;
    }
}

// Fetch total account balance
$total_balance = getTotalAccountBalance($conn);

echo"₦$total_balance";

?>
</strong>
</center>
<br><br>

</center>

</div>
<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://sadeeqdata.com.ng/script_request/admin_dash.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
echo $response;

// API Health Monitoring Section
$api_endpoints = [
    'Dataverify' => 'https://dataverify.com.ng',
    'SadeeqData' => 'https://sadeeqdata.com.ng',
    'Monnify' => 'https://api.monnify.com',
    'PHPMailer' => 'mail.futuredigitaltechltd.net.ng'
];

function check_endpoint($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($http_code >= 200 && $http_code < 400);
}
?>
<div class="container mt-4 mb-5">
    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-bold" style="color: #190F92;"><i class="fas fa-heartbeat me-2"></i> API Infrastructure Status</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($api_endpoints as $name => $url): 
                    $is_online = check_endpoint($url);
                ?>
                <div class="col-md-3 col-6">
                    <div class="p-3 border rounded-4 text-center">
                        <div class="small text-muted mb-1"><?= $name ?></div>
                        <div class="fw-bold <?= $is_online ? 'text-success' : 'text-danger' ?>">
                            <i class="fas <?= $is_online ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> me-1"></i>
                            <?= $is_online ? 'Operational' : 'Down' ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php
?>						
 