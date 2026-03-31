<?php
include_once("db_conn.php");
session_start();


if (isset($_GET['idNumber'])) {
    $idNumber = $_GET['idNumber'];

    // Fetch data from id_history table where response_data contains the provided NIN
    $sql = "SELECT response_data FROM id_history WHERE response_data LIKE ?";
    $stmt = $conn->prepare($sql);
    $like_nin = '%"idNumber":"' . $idNumber . '"%';
    $stmt->bind_param("s", $like_nin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Output data of the first matching row
        $row = $result->fetch_assoc();
        $response_data = json_decode($row["response_data"], true);
echo "<form action='regular_nin_slip.php?idNumber=$idNumber' method='POST'>";

        
        echo "";
        
        if ($photo_data) {
            // Check and handle if photo data includes the prefix 'data:image'
            if (strpos($photo_data, 'data:image') !== false) {
                $photo_base64 = $photo_data;
            } else {
                $photo_base64 = 'data:image/jpeg;base64,' . $photo_data;
            }

            echo " <br>";

        } else {
            echo "<br>";
        }

    } else {
        echo "";
    }

    $stmt->close();
} else {
    echo "";
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
        </style>
        <body>
          
        <script>           document.addEventListener("DOMContentLoaded", (event) => {
            Swal.fire({
                title: "Important Notice!!!",
                text: "Please make sure to confirm the slip you want download, because once you clicked the button we automatically subtract the amount from your account balance either download complete or not.",
                icon: "notification",
                confirmButtonText: "OK"
            });
        });
    </script>  
          
          
          
            
                <div class="dropdown">
  <div class="menu-container">
      



    <i style="position: absolute;right:4%;margin-top:-2%; opacity:0.9;z-index:111;font-size:30px;color:white;"class="fa fa-ellipsis-v" id="menu-icon"></i>
    <div class="dropdown-content">
             <a href="nin_verification_history.php"class="fa fa-dashboard"> Back To Verify History </a>
<div style="border:1px solid #f1f1f1;">
 <a href="users_logout.php"class="fa fa-sign-out"> Logout</a>
    </div>
  </div>
</div>
       <center>
<div class="header">
            <h2 style="font-size:20px">Verify slip</h2>
</div>
</div>
<br><br><br><br>
<?php
// Prepare and execute the query to fetch the data
$stmt = $conn->prepare("SELECT * FROM id_verification_price LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
$id_verification_price_row = $result->fetch_assoc();
$stmt->close();

// Now you can use the data in your HTML

$nin_regular_slip_price = $id_verification_price_row['nin_regular_slip_price'];

$nin_standard_slip_price = $id_verification_price_row['nin_standard_slip_price'];


$nin_premium_slip_price = $id_verification_price_row['nin_premium_slip_price'];
?>









<div class="slip_div">
   <for action='regular_nin_slip.php?idNumber=<?php echo $idNumber;?>' method='POST'>
    <center><h3>Regular Slip</h3></center>
    <div style="border:1px solid #f1f1f1"></div>
     <img src="/vtusite/images/nin.jpg"style="width:70%">
     <br><br>
    <small>Paid ₦<span><?php echo $nin_regular_slip_price; ?>

    </span> to Print </small><br><br>
    <button type="submit">Download PDF</button>
    </for>
   </form>
    </div>
<br><br>






<?php


if (isset($_GET['idNumber'])) {
    $idNumber = $_GET['idNumber'];

    // Fetch data from id_history table where response_data contains the provided NIN
    $sql = "SELECT response_data FROM id_history WHERE response_data LIKE ?";
    $stmt = $conn->prepare($sql);
    $like_nin = '%"idNumber":"' . $idNumber . '"%';
    $stmt->bind_param("s", $like_nin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Output data of the first matching row
        $row = $result->fetch_assoc();
        $response_data = json_decode($row["response_data"], true);
        
echo "<form action='standard_nin_slip.php?idNumber=$idNumber' method='POST'>";

      
        
        
        if ($photo_data) {
            // Check and handle if photo data includes the prefix 'data:image'
            if (strpos($photo_data, 'data:image') !== false) {
                $photo_base64 = $photo_data;
            } else {
                $photo_base64 = 'data:image/jpeg;base64,' . $photo_data;
            }

          

        } else {
            echo "";
        }

    } else {
        echo "No record found for NIN: " . $idNumber;
    }

    $stmt->close();
} else {
    echo "NIN not provided.";
}


?>


<div class="slip_div">
    <center><h3>Standard Slip</h3></center>
    <div style="border:1px solid #f1f1f1"></div>
     <img src="/vtusite/images/standard_slip.png"style="width:70%">
     <br><br>
     
   
    <small>Paid ₦<span><?php echo $nin_standard_slip_price; ?></span> to Print </small><br><br>
    <button type="submit">Download PDF</button>
   </form>
    </div>
<br><br>







<?php


if (isset($_GET['idNumber'])) {
    $idNumber = $_GET['idNumber'];

    // Fetch data from id_history table where response_data contains the provided NIN
    $sql = "SELECT response_data FROM id_history WHERE response_data LIKE ?";
    $stmt = $conn->prepare($sql);
    $like_nin = '%"idNumber":"' . $idNumber . '"%';
    $stmt->bind_param("s", $like_nin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Output data of the first matching row
        $row = $result->fetch_assoc();
        $response_data = json_decode($row["response_data"], true);
echo "<form action='premium_nin_slip.php?idNumber=$idNumber' method='POST'>";
      
        
        
        if ($photo_data) {
            // Check and handle if photo data includes the prefix 'data:image'
            if (strpos($photo_data, 'data:image') !== false) {
                $photo_base64 = $photo_data;
            } else {
                $photo_base64 = 'data:image/jpeg;base64,' . $photo_data;
            }

            

        } else {
            echo "";
        }

    } else {
        echo "No record found for NIN: " . $idNumber;
    }

    $stmt->close();
} else {
    echo "NIN not provided.";
}


?>


<div class="slip_div">
    <center><h3>Premium Slip</h3></center>
    <div style="border:1px solid #f1f1f1"></div>
     <img src="/vtusite/images/premium_slip.png"style="width:70%">
   
     <br><br>
    <small>Paid ₦<span><?php echo $nin_premium_slip_price; ?></span> To Print </small><br><br>
    <button type="submit">Download PDF</button>
   </form>
    </div>
<br><br>
            





