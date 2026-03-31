<?php
session_start();
include_once("db_conn.php");
if (!isset($_SESSION["email"])) {
    // Redirect to welcome_pin.php with reseller ID if transaction pin is not set
 $_SESSION['email'] = $email;
    header("location:welcome_pin.php");
    exit(); // Add exit to prevent further execution
}

// Check if the email session is set
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Prepare the SQL query to check if the email exists in the referral table
    $stmt = $conn->prepare("SELECT email FROM customers2 WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Check if any row was returned
    if ($stmt->num_rows > 0) {
        // Email exists in the referral table, user can stay on the page
        $stmt->close();
    } else {
        // Email does not exist, redirect to users_logout.php
        $stmt->close();
        header("Location: users_logout.php");
        exit();
    }
} else {
    // Email session is not set, redirect to users_logout.php
    header("Location: users_logout.php");
    exit();
}



$sql_count = "SELECT COUNT(*) AS total_transactions FROM funding_history WHERE email = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("s", $email);
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$row_count = $result_count->fetch_assoc();
$total_transactions = $row_count["total_transactions"];

// Fetch transactions for the specific email with order
$sql = "SELECT * FROM funding_history WHERE email = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="theme-color"content="#190F92">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        
       
.content {
            padding: 20px;
            display: none;
            
        }

      
        .search-container {
            margin: 20px 0;
        }

        .search-container input {
            width: 80%;
            padding: 10px;
            background-color:#f1f1f1;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .transaction-list {
            list-style-type: none;
            padding: 0;
        }

        .transaction-item {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 4px;
         
           border-radius: 10px;
            
        }
.transaction-item:hover{
    
    cursor:pointer;
    background-color:#f1f1f1;
    
}
        .transaction-item img {
            width: 25%;
            height: 25%;
            border-radius: 50%;
            margin-right: 0;
        }

        .transaction-details {
            flex-grow: 1;
        }

        .transaction-details h3 {
            margin: 0 0 6px 10px;
            font-size: 14px;
        }

        .transaction-details p {
           margin: 0 0 6px 10px;
            color: #666;
            font-size: 10px;
            
        }

        .transaction-status {
            color: green;
            font-weight: bold;
        } 
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
.header{
background-color:#190F92;
box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
position:fixed;
width:100%;
padding:8%;
z-index:1;

}
/* CSS for skeleton loader */

        </style>
   
</head>

<body>
 
    <div class="header">
<h1>
    <nobr>
<strong style="width:40%;height:86%;border-radius:80%; position: absolute;top:14%;left:4%;color:white;font-size:23px">Funding History</strong></nobr>
</h1>
 <div class="dropdown">
  <div class="menu-container">
      
    <i style="position: absolute;right:4%;margin-top:7%;font-size:30px;color:white;"class="fa fa-ellipsis-v" id="menu-icon"></i>
    <div class="dropdown-content">
         <a href="dashboard.php"class="fa fa-dashboard"> Back To Dashboard</a>

     
<hr>
 <a href="users_logout.php"class="fa fa-sign-out"> Logout</a>
    </div>
  </div>
</div>

</div>
</h1>

</div>

<br><br><br><br>

<div class="search-container">
    <center>
                <input type="text" id="searchInput" onkeyup="searchTransactions()" placeholder="Search transaction...">
               
                </center>
                <br><br>
                  <span style="margin:5%">Total transactions (<?php echo $total_transactions;?>)
        <hr style="border:2px solid #f1f1f1">          
<br>

<?php


$sql = "SELECT * FROM funding_history WHERE email = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);

// Check if preparation was successful
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind the parameter and execute the statement
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $amount = $row["amount"];
         $fullname = $row["fullname"];
        $date = $row["date"];
$funding_type = $row["funding_type"];
        echo '<li class="transaction-item">
                <img src="/vtusite/images/logo2.png" style="width:20%;border-radius:80%;border:15px solid #f1f1f1;height:70px" alt="">
                <div class="transaction-details">
                    <h3> '.$funding_type.' </h3>
                    <p>₦' . number_format($amount, 2) . ' has been funded into<br> your account from '.$fullname.'<br> on ' . date("F j, Y", strtotime($date)) . '</p>
                </div>
                <button style="margin-right:5%;border:1px solid white;background-color:green;color:white;font-size:15px;opacity:0.9">Success</button>
              </li>
              <hr style="border:2px solid #f1f1f1">';
    }
} else {
    echo '<center><img src="/vtusite/images/no_data2.jpg" alt="No data available"></center>';
}

// Close the statement
$stmt->close();

// Close the database connection
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
