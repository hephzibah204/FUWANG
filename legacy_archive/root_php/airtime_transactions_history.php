<?php
include_once("db_conn.php");

session_start();
include_once ("navbar.php");
if (!isset($_SESSION["create_new_pin"])) {
    header("location:welcome_pin.php");
}
          
 
$email = $_SESSION['email'];
$userId = $_SESSION['user_Id'];
$fullname = $_SESSION['fullname'];
$number = $_SESSION['number'];
$password = $_SESSION['password'];
$create_new_pin= $_SESSION["create_new_pin"];

?>




<html>
<head>
    <meta name="theme-color"content="#15gt44">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<head>
	<title>Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
   
  <style>
      
      
       
      </style>
            </head>       
            
            <body>
                <br><br><br>
                <center> <h3>Airtime Transactions History</h3></center>
                <?php
$query ="SELECT * FROM  airtime_transactions_history WHERE email='$email'";
$query_run=mysqli_query($conn,$query);
if(mysqli_num_rows($query_run) > 0)
{
    foreach($query_run as $row)

{
?>
              
             <p>&nbsp;</p>
             <ul class="users-list-wrapper media-list" style="box-sizing: border-box; color: #626262; font-family: Ubuntu, sans-serif; font-size: 14px; letter-spacing: 0.14px; margin: 0px; padding: 0px;">
                 
                 <li class="" style="animation: 0.5s linear 0.1s 1 normal both running fadeIn; box-sizing: border-box; cursor: pointer; position: relative; transition: all 0.2s ease 0s;">
                     <div class="chat-list-item d-flex flex-row p-2 border-bottom" style="-webkit-box-direction: normal !important; -webkit-box-orient: horizontal !important; background: rgb(242, 242, 242); border-bottom: 1px solid; box-sizing: border-box; cursor: pointer; display: flex !important; flex-direction: row !important; padding: 1rem !important;">
                 
                 <div class="customer-content" style="box-sizing: border-box;">
                 
                 <div class="name" style="box-sizing: border-box;"><?php echo $row["network"];?> <?php echo $row["type"];?><span style="box-sizing: border-box; color: black; font-size: 13px; position: absolute; right: 10px; top: 14px;">₦<?php echo $row["amount"];?></span>
                 <span style="box-sizing: border-box; font-size: 11px;"><br style="box-sizing: border-box;" />Status:<span class="badge" style="background: transparent; border-radius: 0.25rem; box-sizing: border-box; color: #29c770; display: inline-block; font-weight: bolder; line-height: 1; padding: 0.35em 0.4em; text-align: center; text-wrap: nowrap; transition: color 0.15s ease-in-out 0s, background-color 0.15s ease-in-out 0s, border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s; vertical-align: baseline;">TRANSACTION SUCCESSFUL</span><br style="box-sizing: border-box;" />Trans. ID:<?php echo $row["request_id"];?> <img src="" alt=""><br style="box-sizing: border-box;" />Date: <?php echo $row["purchase_time"];?></span></div>
                 
                 <div class="small last-message" style="box-sizing: border-box; font-size: smaller; margin-bottom: 15px; padding-top: 5px; white-space-collapse: preserve-breaks;">Your ₦<?php echo $row["amount"];?> purchase on <?php echo $row["phone"];?> was successful. Old balance was ₦251.33 while new balance is <?php echo $balance;?></div>
                 
                  <div class="customer-btns" style="background: rgb(95, 95, 95); border: 0px; bottom: 0px; box-sizing: border-box; color: yellow; font-size: 11px; left: 0px; padding: 3px 3px 3px 10px; position: absolute; right: 0px;">
                                                              </div></div></li>
                             
     
                                                  
              <!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/654b2ddef2439e1631ecf646/1hemqmcm6';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
                    
                    
                    
                    <?php
        }
        }
    else   {
        echo" <tr></tr>";
       } 
        
        
        ?>
