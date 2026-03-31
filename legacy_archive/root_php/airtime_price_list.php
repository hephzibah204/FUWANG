<?php
  include_once("db_conn.php");
  session_start();
  
// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    // Redirect to login page if not logged in
    header("Location: admin_login.php");
    exit();
}

include_once("airtime_price_bar.php");
      
       
       ?>



<!DOCTYPE html>
<html>
<head>
     <meta name="theme-color" content="#190F92"><img src="<img src="" alt="">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Price list</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
      .hidden{
display:none;
}  
.button{
 background-color:darkblue;
 border-radius:10px;
  border:1px solid darkblue;  
     background-color: darkblue;/* Green */
  border: none;
  color: white;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  border-radius: 8px;
  transition-duration: 0.4s;
  cursor: pointer;
}

.button:hover {
  background-color: #45a049; /* Darker green */

}
.submit-button:hover{
    
    background-color:;
    border:3px solid black;
   
}.container_count {
            margin-bottom: 20px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            background-color: #f9f9f9;
            
        }
   .count {
            margin-bottom: 20px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            background-color: #f9f9f9;
        }
   .count1{
            margin-bottom: 20px;
            padding:10%;
            width:100%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            background-color: #f9f9f9;
        }
   
        </style>
        </head>
    <body>
    
  
<div class="container mt-3">
  
  <br>
  <!-- Nav tabs -->
   <center>
  <ul class="nav nav-tabs" role="tablist">

 <li class="nav-item"style="display:one">
      <a class="nav-link active" data-bs-toggle="tab"href="#home">Home</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#menu1">Api settings</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#menu2">History</a>
 </li>

 </ul>
 </center>
  <!-- Tab panes -->
  <div class="tab-content">
    <div id="home" class="container tab-pane active"><br>
      <h3>Overview</h3>
      
  <?php
include_once("db_conn.php");

// Query to select data from airtime_transactions_history table grouped by network
$sql = "SELECT network, SUM(amount) as total_amount, COUNT(*) as total_transaction FROM airtime_transactions_history GROUP BY network";
$result = mysqli_query($conn, $sql);

// Initialize arrays to store the results
$network_data = array();

// Check if the query was successful
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $network_data[] = $row;
    }
} else {
    echo "Error: " . mysqli_error($conn);
}
?>

        <div class="count1">Total Overview is display based on network for your customers Total transactions 
    <div class="count">
    <?php foreach ($network_data as $data): ?>
        <div class="container_count">
            <span><?php echo htmlspecialchars($data['network']); ?> Network</span>
            <br>
            Total Amount: ₦<?php echo number_format($data['total_amount'], 2); ?>
            <br>
            Total Transactions: <?php echo $data['total_transaction']; ?>
        </div>
    <?php endforeach; ?>
</div>     
      </div>
    </div>
    
    
    
    
    <div id="menu1" class="container tab-pane fade"><br>
   
   
      <h1>
       <table style="position:;top:25%" width="100%">
           
                <th  class="button1 "onclick="toggleFunction(1)" style=" background-color:#f1f1f1;border-radius:10px">
                    <i class="submit-button">
                    <img src="/vtusite/images/mtn.png" style="width:80%;height:60px;border-radius:10px">
                </th>
              </i>
                   <th class="submit-button"id="button2 "onclick="toggleFunction(2)" style=" background-color:#f1f1f1;border-radius:10px">
                    <img src="/vtusite/images/glo_icon.jpg" style="width:80%;height:60px;border-radius:10px">
                </th>
                  <th class="submit-button"id="button3 "onclick="toggleFunction(3)"style=" background-color:#f1f1f1;border-radius:10px">
                    <img src="/vtusite/images/airtel_icon.jpg" style="width:80%;height:60px;border-radius:10px">
                </th>
              <th class="submit-button" id="button4 "onclick="toggleFunction(4)"style=" background-color:#f1f1f1;">
                    <img src="/vtusite/images/9mobile.png" style="width:80%;height:50px;">
                </th>
            </table>
</h1>

<hr>
   <br><br><br>
   
   <?php
include_once("db_conn.php");

// Fetch current values from the database
$query = "SELECT * FROM api_settings";
$result = mysqli_query($conn, $query);
$settings = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mtn_airtime_discount = $_POST['mtn_airtime_discount'];
    $glo_airtime_discount = $_POST['glo_airtime_discount'];
    $airtel_airtime_discount = $_POST['airtel_airtime_discount'];
    $mobile9_airtime_discount = $_POST['mobile9_airtime_discount'];

    $update_query = "UPDATE api_settings SET mtn_airtime_discount = '$mtn_airtime_discount', glo_airtime_discount = '$glo_airtime_discount', airtel_airtime_discount = '$airtel_airtime_discount', mobile9_airtime_discount = '$mobile9_airtime_discount'";
    mysqli_query($conn, $update_query);

    // Refresh the settings after update
    $result = mysqli_query($conn, $query);
    $settings = mysqli_fetch_assoc($result);
}
?>

   
   
   
   
   
   
    
  <div id="function1">
      
    <soft action="#" method="POST" style="box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1); width: 90%; left: 5%; position: absolute; top: 45%; justify-content: center; text-align: center;">
        <h5 style="color: darkblue">MTN</h5>
        <hr>
         <small style="opacity:0.5">
   For this mtn service a customer get ₦<?php echo $settings['mtn_airtime_discount']; ?> for each ₦100 Airtime has buyed</small><br>
        <img src="/vtusite/images/mtn.png" width="50%">
        <br>
        <input type="text" name="mtn_airtime_discount" style="width: 80%" value="<?php echo $settings['mtn_airtime_discount']; ?>">
        <br>
        <button type="submit"class="button">update </button>
        <br>
       
        <br><br>
    
</div>

<div id="function2" class="hidden">
    <soft action="#" method="POST" style="box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1); width: 90%; left: 5%; position: absolute; top: 45%; justify-content: center; text-align: center;">
        <h5 style="color: darkblue">GLO</h5>
        <hr>
          <small style="opacity:0.5">
   For this glo service a customer get ₦<?php echo $settings['glo_airtime_discount']; ?> for each ₦100 Airtime has buyed</small><br>
        <img src="/vtusite/images/glo_icon.jpg" width="50%">
        <br>
        <input type="text" name="glo_airtime_discount" style="width: 80%" value="<?php echo $settings['glo_airtime_discount']; ?>">
        <br>
        <button type="submit"class="button">update </button>
        <br>
        
        <br><br>
    
</div>

<div id="function3" class="hidden">
    <soft action="#" method="POST" style="box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1); width: 90%; left: 5%; position: absolute; top: 45%; justify-content: center; text-align: center;">
        <h5 style="color: darkblue">Airtel</h5>
        <hr>
         <small style="opacity:0.5">
   For this airtel service a customer get ₦<?php echo $settings['airtel_airtime_discount']; ?> for each ₦100 Airtime has buyed</small>
   <br>
        <img src="/vtusite/images/airtel_icon.jpg" width="50%">
        <br>
        <input type="text" name="airtel_airtime_discount" style="width: 80%" value="<?php echo $settings['airtel_airtime_discount']; ?>">
        <br>
        <button type="submit"class="button">update </button>
        <br>
       
        <br><br>
    
</div>

<div id="function4" class="hidden">
    <soft action="#" method="POST" style="box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1); width: 90%; left: 5%; position: absolute; top: 45%; justify-content: center; text-align: center;">
        <h5 style="color: darkblue">9Mobile</h5>
        <hr>
         <small style="opacity:0.5">
   For this 9mobile service a customer get ₦<?php echo $settings['mobile9_airtime_discount']; ?> for each ₦100 Airtime has buyed</small>
   <br>
        <img src="/vtusite/images/9mobile_icon.jpg" width="50%">
        <br>
        <input type="text" name="mobile9_airtime_discount" style="width: 80%" value="<?php echo $settings['mobile9_airtime_discount']; ?>">
        <br>
        <button type="submit"class="button">update </button>
        <br>
      
        <br><br>
    </form>  
</div>
     <script>
        let currentFunction = 1;

        function toggleFunction(newFunction) {
            // Hide the current function
            document.getElementById(`function${currentFunction}`).style.display = "none";

            // Show the new function
            document.getElementById(`function${newFunction}`).style.display = "block";

            currentFunction = newFunction;
        }
        
        

    </script>
    </div>
    
    
    <div id="menu2" class="container tab-pane fade">
      
      <?php
      include_once("admin_airtime_history.php");
      ?>
       </div>
   
  </div>
</div>

       
        
        
        
        
        
        
        
        
        
        </body>
        </html>
