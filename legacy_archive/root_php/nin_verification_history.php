<?php
session_start();
include_once('db_conn.php');
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
            width:80%;
           margin-left:5%;
            
        }
        </style>
        <body>
            
                <div class="dropdown">
  <div class="menu-container">
      
    <i style="position: absolute;right:4%;margin-top:0%; opacity:0.9;z-index:111;font-size:30px;color:white;"class="fa fa-ellipsis-v" id="menu-icon"></i>
    <div class="dropdown-content">
             <a href="dashboard.php"class="fa fa-dashboard"> Back To Dashboard  </a>
<div style="border:1px solid #f1f1f1;">
 <a href="users_logout.php"class="fa fa-sign-out"> Logout</a>
    </div>
  </div>
</div>
       <center>
<div class="header">
            <h2 style="font-size:20px">Verified history</h2>
</div>
</div>
<br><br><br><br>
            
<?php

if (isset($_SESSION["email"])) {
    // Get the user's email from the session
    $user_email = $_SESSION["email"];

    // Fetch data from id_history table where the email matches the session email
   $sql = "SELECT user_email, transaction_id, response_data, created_at FROM id_history WHERE user_email = ? ORDER BY created_at DESC";
   
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            $response_data = json_decode($row["response_data"], true);

            $idNumber = isset($response_data['idNumber']) ? $response_data['idNumber'] : 'N/A';
            $photo_data = isset($response_data['image']) ? $response_data['image'] : '';

            if ($photo_data) {
                // Check and handle if photo data includes the prefix 'data:image'
                if (strpos($photo_data, 'data:image') !== false) {
                    $photo_base64 = $photo_data;
                } else {
                    $photo_base64 = 'data:image/jpeg;base64,' . $photo_data;
                }

                echo "<div class='content'>";
                echo '<img style="width:70%;border-radius:10px" src="' . $photo_base64 . '" alt="User Photo" /><br><br>';
                echo "NIN: " . $idNumber . "<br><br>";
                echo "
                <form action='history_available_slip.php' method='GET'>";
                echo "<input type='hidden' name='idNumber' value='" . $idNumber . "' />";
                echo "<button type='submit'>Reprint Slip</button>";
                echo "</form></div>";
            } else {
                echo "Photo: Not available<br>";
            }
            echo "<br><div style='bordr:3px solid #f1f1f1;'></div>";
        }
    } else {
        echo "0 results";
    }

    $stmt->close();
}

$conn->close();
?>
