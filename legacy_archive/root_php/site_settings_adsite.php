<?php

session_start();
include_once("db_conn.php");

if (!isset($_SESSION['loggedin'])) {
    // Redirect to login page if not logged in
    header("Location: admin_login");
    exit();
}
include_once("whois_admin.php");

$sql = "SELECT COUNT(*) AS total_rows FROM manual_funding";
$result = $conn->query($sql);

if ($result) {
    // Fetch total number of rows
    $row = $result->fetch_assoc();
    $totalRows = $row['total_rows'];

    // Display or use the total count
    echo " ";
} else {
    // Error in query execution
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Query to count users in the users table that are not in the referral table
$sql = "SELECT COUNT(email) as total_users FROM customers1 WHERE email NOT IN (SELECT email FROM customers2)";
$result = mysqli_query($conn, $sql);

// Initialize a variable to store the total count
$total_users = 0;

// Check if the query was successful
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_users = $row['total_users'];
} else {
    echo "Error: " . mysqli_error($conn);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
     <meta name="theme-color" content="#190F92">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customers List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .content {
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
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
        .header {
            background-color:#190F92;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            position: ;
            width: 100%;
            padding: 8%;
            z-index: 1;
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
        button {
            margin-left: 20%;
            color: white;
            font-size: 5px;
            border-radius: 10px;
            background: linear-gradient(-45deg, rgb(42, 32, 172), rgb(14, 97, 26));
        }
        input[type=text], input[type=password], input[type=tel] {
            width: 80%;
            padding: 6%;
            margin: 5px 0 22px 0;
            display: inline-block;
            border: none;
            border-radius: 10px;
            background: #f1f1f1;
        }
        input[type=text]:focus, input[type=password]:focus, input[type=tel]:focus {
            background-color: #ddd;
            outline: none;
        }
        button:hover {
            opacity: 1;
        }
        button {
            background-color: #04AA6D;
            color: white;
            border-radius: 10px;
            opacity: 0.9;
        }
        .cancelbtn {
            padding: 14px 20px;
            background-color: #f44336;
        }
        .cancelbtn, .signupbtn {
            float: left;
            width: 50%;
        }
         
        /* Style for the off-canvas */
        #offcanvas {
            position: fixed;
            bottom: -100%;
            left: 0;
            width: 100%;
             background: #f1f1f1;
            transition: bottom 0.3s;
            padding: 30px;
            box-shadow: 0px -2px 10px rgba(0,0,0,0.2);
        }

        #offcanvas.show {
            bottom: 0;
        }
       
          input[type=textt] {
            width: 90%;
            padding: 6%;
            margin: 5px 0 22px 0;
            display: inline-block;
            border: none;
            border-radius: 10px;
            background: #ddd;
        }
        input[type=textt]:focus {
            background-color: #ddd;
            outline: none;
        }
        
    

    </style>
</head>
<body>
    <?php
                if(isset($success)) {
                    foreach($success as $successMessage) {
                        // Echo JavaScript to trigger SweetAlert
                        echo "<script>Swal.fire({ title: 'Successfully Added New Bank Account', text: '$successMessage', icon: 'success' });</script>";
                    }
                }
            ?>
        </center>
 
   <?php
                if(isset($errors)) {
                    foreach($errors as $errorMessage) {
                        // Echo JavaScript to trigger SweetAlert
                        echo "<script>Swal.fire({ title: 'something wrong happen', text: '$errorMessage', icon: 'error' });</script>";
                    }
                }
            ?>
    
    
    
    <div class="header">
<h1>
    <nobr>
<strong style="width:40%;height:86%;border-radius:80%; position: absolute;top:3%;left:4%;color:white;font-size:20px">Account Settings</strong></nobr>
</h1>
 <div class="dropdown">
  <div class="menu-container">
      
    <i style="position: absolute;right:4%;margin-top:9%;font-size:20px;color:white;"class="fa fa-gift" id="menu-icon"></i>
    <div class="dropdown-content">
        <a onclick="document.getElementById('id011').style.display='block'"href="#"class="fa fa-plus"> Add welcoming message </a>
        
        
    <a href="#" class="fa fa-money" id="set_charges_link"> Set charges</a>

    <div id="offcanvas">

        
       ?>
       <h6><center> Set Charges <hr></h6><br>
       
       
       </center>
       <p>
       if you set the charges amount here, is mean you can deduct the exact amount has set on every user automatic funding.</p>
       
       <img src="/vtusite/images/commission1.jpg"style="width:80%;border-radius:10px;align-items: center;text-align:center;jsutify-content: center;margin-left:10%">
      
       <br>
       <center>
        <input type="number" name="psb_amount"value="<?php echo $psb_amount; ?>" id="psb_amount_input">
       
         <p>The current charges amount is display in the above input</p>
         <br>
        <buttoon style="
            color: white;
            font-size: 15px;
            padding:10px;
           background: linear-gradient(-45deg, rgb(42, 32, 172), rgb(14, 97, 26));
            border-radius:10px;
           
            background-color:#190F92;"
            id="update_charges_button">Update Charges</buttoon></center>
    </div>

    <script>
    document.getElementById('set_charges_link').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('offcanvas').classList.toggle('show');
    });

    document.getElementById('update_charges_button').addEventListener('click', function() {
        var psb_amount = document.getElementById('psb_amount_input').value;

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "update_psb_amount.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                alert(xhr.responseText);
            }
        };
        xhr.send("psb_amount=" + psb_amount);
    });
    </script>
      <a href="admin_dashboard.php"class="fa fa-dashboard"> Back To Main Dashboard</a>
     
<hr>
 <a href="resellers_logout.php"class="fa fa-sign-out"> Logout</a>
    </div>
  </div>
</div>


</h1>

</div>

<br><br><br>

 <div id="id011" class="modal" style="display: none; position: fixed; left: 7%; top: 0; width: 85%; height: 850%; border-radius:10px; padding-top: 30%;">
<div class="modal-content">
                <span class="close" onclick="document.getElementById('id011').style.display='none'"></span>
                <center><strong>Notification Center</strong></center>
                <?php
include_once("db_conn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notification = $_POST['notification'];

    // Check if notification exists
    $check_sql = "SELECT * FROM notifying_center LIMIT 1";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // Notification exists, update
        $update_sql = "UPDATE notifying_center SET notification = '$notification' WHERE id = 1";
        if ($conn->query($update_sql) === TRUE) {
            echo "";
        } else {
            echo "Error updating notification: " . $conn->error;
        }
    } else {
        // No notification exists, insert
        $insert_sql = "INSERT INTO notifying_center (notification) VALUES ('$notification')";
        if ($conn->query($insert_sql) === TRUE) {
            echo "";
        } else {
            echo "Error inserting notification: " . $conn->error;
        }
    }

    
}
?>
                
                <?php
include_once("db_conn.php");

// Assuming you have already fetched $notification from the database
$sql = "SELECT notification FROM notifying_center LIMIT 1"; // Query to fetch the notification
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $notification = $row['notification'];
} else {
    $notification = "No notifications available";
}


?>


<form  action="#" method="post">
    <center>
        <textarea id="notificationInput" name="notification" style="padding:10%; background-color:#f1f1f1" placeholder="Write ✍️ message..."></textarea><br>
          <input type="submit"value="update">
          <hr>
        <br
        
        <small>Current Notification</small>
       
        <hr>
        <div>
            <?php echo $notification;?>
        </div>
    </center>
  
</form>
</div> </div>      
<script>
// Get the modal
var modal = document.getElementById('id011');

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>
</div>
    <div class="content">
        <div class="container-fluid">
          
            <div class="row">
                
                
                <div class="col-lg-3 col-md-6">
                    <a href="admin_new_users_rewards.php"style="color:white">
                    <div class="card text-white bg-primary">
                       
                        <div class="card-body">
                             
                          <div class="card-title">New user reward</div>
                            <p class="card-text"><?php echo $totalRows;?></p></a>
                        </div>
                    </div>
                </div>
               
               <div class="col-lg-3 col-md-6">
                    <a href="admin_funding_history"style="color:white">
                    <div class="card text-white"style="background-color:violet">
                       
                        <div class="card-body">
                             
                          <div class="card-title">Funding history</div>
                            <p class="card-text"><?php echo $totalRows;?></p></a>
                        </div>
                    </div>
                </div>
               
               <div class="col-lg-3 col-md-6">
                    <a href="admin_user_balance_list"style="color:white">
                    <div class="card text-white"style="background-color:green">
                       
                        <div class="card-body">
                             
                          <div class="card-title">Users Balance list</div>
                            <p class="card-text"><?php echo $totalRows;?></p></a>
                        </div>
                    </div>
                </div>
               
               <div class="col-lg-3 col-md-6">
                    <a href="admin_user_login_attempts"style="color:white">
                    <div class="card text-white"style="background-color:purple">
                       
                        <div class="card-body">
                             
                          <div class="card-title">Restricted Attempts</div>
                            <p class="card-text"><?php echo $totalRows;?></p></a>
                        </div>
                    </div>
                </div>
               
                <div class="col-lg-3 col-md-6">
                    <a href="admin_orders"style="color:white">
                    <div class="card text-white"style="background-color:tomato">
                       
                        <div class="card-body">
                             
                          <div class="card-title">My orders</div>
                            <p class="card-text"><?php echo $totalRows;?></p></a>
                        </div>
                    </div>
                </div>
               
               
               
                 <div class="col-lg-3 col-md-6">
                    <a href="admin_new_users_rewards.php"style="color:white">
                    <div class="card text-white"style="background-color:green">
                       
                        <div class="card-body">
                             
                          <div class="card-title">Welcoming Reward</div>
                            <p class="card-text"><?php echo $totalRows;?></p></a>
                        </div>
                    </div>
                </div>
               
               
               
               
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <a href="admin_user_referral_rewards"style="color:white">
                            <div class="card-title">Referral program</div>
                            <p class="card-text">5</p>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white"style="background-color:darkblue">
                        <div class="card-body">
                            <?php

// SQL query to count rows in manual_proof_messages
$sql = "SELECT COUNT(*) AS total_rows FROM manual_proof_messages";
$result = $conn->query($sql);

// Check if the query was successful
if ($result->num_rows > 0) {
    // Fetch the result
    $row = $result->fetch_assoc();
    echo "";
} else {
    echo "";
}

// Close connection

?>
                            <a href="admin_messages_center"style="color:white">
                            <div class="card-title"> Proof Request Center</div>
                            <p class="card-text"><?php echo $row["total_rows"];?></p></a>
                        </div>
                    </div>
                </div>
                 <div class="col-lg-3 col-md-6">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                             <a href="admin_freezing_users"style="color:white">
                            <div class="card-title">Restricted Users</div>
                            <p class="card-text"><?php echo $total_users;?></p>
                        </div>
                    </div>
                </div>
                </a>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <a href="admin_messages_center"style="color:black;text-decoration:none">
                      <?php
include_once("db_conn.php");

$sql = "SELECT * FROM manual_proof_messages ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '<div class="card-header">Recent message</div>';
        echo '<div class="card-body">';
        echo '<p>Account Number: ' . $row['account_number'] . '</p>';
        echo '<p>Bank Name: ' . $row['bank_name'] . '</p>';
        echo '<p>Amount: ' . $row['amount'] . '</p>';
        echo '<p>Remark: ' . $row['remark'] . '</p>';
        echo '<p>Date: ' . $row['created_at'] . '</p>';
        echo '</div>';
    }
} else {
    echo '<div class="card-header">Recent message</div>';
    echo '<div class="card-body">';
    echo '<p>No recent messages.</p>';
    echo '</div>';
}

$conn->close();
?>

</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php echo include_once("footer.php");?>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
