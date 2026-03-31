<!DOCTYPE html>
<html lang="en">
<head>
  <title>Dataverify|Dashboard</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
<style>
 body {
      background-color:;
      
    }
    .card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      padding:0;
      width:110%;
left:-5%;
border:none;

      height: 23%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .logo {
      width: 40%;
      margin-left: auto;
      margin-right: auto;
    }
    .account-info {
      color:darkblue;
      text-align: center;
      font-size: 16px;
      margin-bottom: 10px;
      position: relative;
    }
    .account-number {
      font-weight: bold;
      font-size: 18px;
    }
    .name {
 color:darkblue;
      text-align: center;
      font-size: 18px;
      font-weight: bold;
    }
    .expiry {
      color:darkblue;
      text-align: center;
      font-size: 14px;
    }
    .clipboard-icon {
      position: absolute;
      top: 10px;
      right: 10px;
      cursor: pointer;
      color:darkblue;
      font-size: 20px;
    }

.main{
background-color:#f1f1f1;
padding:30%;
}

.nav nav-tabs{

position: absolute;
top:100%;
}
.offcanvas-header{

top:2%;
}


/* Full-width input fields */
input[type=text], input[type=password] {
  width: 100%;
  padding: 12px 20px;
  margin: 8px 0;
  display: inline-block;
  border: 1px solid #ccc;
  box-sizing: border-box;
background: #f1f1f1;
}

/* Set a style for all buttons */
button {
  background-color: darkblue;
  color: white;
  padding: 14px 20px;
  margin: 8px 0;
  border: none;
  cursor: pointer;
  width: 100%;
}

/* Extra styles for the cancel button */
.cancelbtn {
  width: auto;
  padding: 10px 18px;
  background-color: #f44336;
}

/* Center the image and position the close button */
.imgcontainer {
  text-align: center;
  margin: 24px 0 12px 0;
  position: relative;
}

img.avatar {
  width: 40%;
  border-radius: 50%;
}

.container {
  padding: 16px;
}

span.psw {
  float: right;
  padding-top: 16px;
}

/* The Modal (background) */
.modal {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 111; /* Sit on top */
  left: 0;
background-color:#f1f1f1;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: rgb(0,0,0); /* Fallback color */
  background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
  padding-top: 30%;
}

/* Modal Content/Box */
.modal-content {
  background-color: ;
  margin: 5% auto 15% auto; /* 5% from the top, 15% from the bottom and centered */
  border: 1px solid #888;
  width: 80%; /* Could be more or less, depending on screen size */
}

/* The Close Button (x) */
.close {
  position: absolute;
  right: 25px;
  top: 0;
  color:white;
  font-size: 35px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: red;
  cursor: pointer;
}

/* Add Zoom Animation */
.animate {
  -webkit-animation: animatezoom 0.6s;
  animation: animatezoom 0.6s
}

@-webkit-keyframes animatezoom {
  from {-webkit-transform: scale(0)} 
  to {-webkit-transform: scale(1)}
}
  
@keyframes animatezoom {
  from {transform: scale(0)} 
  to {transform: scale(1)}
}

/* Change styles for span and cancel button on extra small screens */
@media screen and (max-width: 300px) {
  span.psw {
     display: block;
     float: none;
  }
  .cancelbtn {
     width: 100%;
  }
.hidden{
    
    display:none;
}
</style>
</head>
<body>

<div style="background-color:#f1f1f1;padding:0;height:50vh"class="offcanvas offcanvas-bottom" id="demo">

  <div class="offcanvas-header">
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <div class="container mt-3">
  
  <!-- Nav tabs -->
  <ul class="nav nav-tabs" style="background-color:#f1f1f1" role="tablist">
    <li class="nav-item">
<nobr><center>
      <a class="nav-link active" data-bs-toggle="tab" href="#home">Bank</a>
</li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#menu1">Manual</a>
    </li>
<nobr>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#menu2">Paystack</a>
</nobr></center>
    </li>

  </ul>

  
  <div class="tab-content"style="background-color:#f1f1f1;width:100%;">
    <div id="home" class="container tab-pane active">
      <center>




<?php
// Include database connection
session_start();

 include_once("db_conn.php");
// Start or resume the session


// Get the email from the session
$email = $_SESSION['email'];

// SQL query to fetch bank details based on the session email
$sql = "SELECT * FROM bank_details WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        // Generate HTML code for each bank
        echo '<div class="card">';
        
   


echo '<center style="color:darkblue;font-size:25px;font-weight:bold">Palmpay</center>';
echo '<img class="logo" src="/vtusite/images/mastercard_logo.jpg" alt="' . $row['Wema_account'] . ' Logo">';
echo '<div class="account-info">';
echo '<div class="account-number" id="accountNumber1">' . $row['palmpay'] . '</div>';
echo '<div class="name">Fuwa..NG</div>';
echo '<div class="expiry">Charges:2%</div>';
echo '<br>';
echo '<i class="fa fa-clone clipboard-icon" onclick="copyAccountNumber(\'accountNumber1\')"></i>';
echo '</div>';
echo '</div>';
echo '<br><br>';


echo '<div class="card">';
echo '<center> Sterling Bank</center>';
echo '<img class="logo" src="/vtusite/images/mastercard_logo.jpg" alt="' . $row['Sterling_account'] . ' Logo">';
echo '<div class="account-info">';
echo '<div class="account-number" id="accountNumber3"></div>';
echo '<div class="name">' . $row['account_name'] . '</div>';
echo '<div class="expiry">Charges:1% </div>';
echo '<br>';
echo '<i class="fa fa-clone clipboard-icon" onclick="copyAccountNumber(\'accountNumber3\')"></i>';
echo '</div>';
echo '</div>';
echo '<br><br>';

echo '<div class="card">';
echo '<center> Moniepoint Bank</center>';
echo '<img class="logo" src="/vtusite/images/mastercard_logo.jpg" alt="' . $row['Moniepoint_account'] . ' Logo">';
echo '<div class="account-info">';
echo '<div class="account-number" id="accountNumber4">' . $row['Moniepoint_account'] . '</div>';
echo '<div class="name">' . $row['account_name'] . '</div>';
echo '<div class="expiry">Charges:1% </div>';
echo '<br>';
echo '<i class="fa fa-clone clipboard-icon" onclick="copyAccountNumber(\'accountNumber4\')"></i>';
echo '</div>';
echo '</div>';
echo '<br><br>';

echo '<div class="card">';
echo '<center> GT Bank</center>';
echo '<img class="logo" src="/vtusite/images/mastercard_logo.jpg" alt="' . $row['GTBank_account'] . ' Logo">';
echo '<div class="account-info">';
echo '<div class="account-number" id="accountNumber5"></div>';
echo '<div class="name">' . $row['account_name'] . '</div>';
echo '<div class="expiry">Charges:1% </div>';
echo '<br>';
echo '<i class="fa fa-clone clipboard-icon" onclick="copyAccountNumber(\'accountNumber5\')"></i>';
echo '</div>';
echo '</div>';
echo '<br><br>';



    }
} else {
    echo '<center><img src="/vtusite/images/no_data2.jpg"style="width:100%;border-radius:10px;height:10%"><br><strong data-bs-dismiss="offcanvas" style="color:darkblue;position: absolute;top:82%;left:30%;box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);background-color:darkblue;border-radius:10px;color:white;align-items:center;padding:4%" onclick="document.getElementById(\'id01\').style.display=\'block\'">Generate Account Number </strong></center>';
}

?>

  

    <script>
        function copyAccountNumber(elementId) {
            var copyText = document.getElementById(elementId).textContent;
            navigator.clipboard.writeText(copyText).then(function() {
                Swal.fire({
                    icon: 'success',
                    text: 'Account number copied: ' + copyText,
                    confirmButtonColor: '#007bff'
                });
            }, function(err) {
                Swal.fire({
                    icon: 'error',
                    text: 'Failed to copy account number',
                    confirmButtonColor: '#190F92'
                });
            });
        }
    </script>
</center>
    </div>
     <div id="function1">
    <div id="menu1" class="container tab-pane fade">
      <center>


<?php
session_start();

include_once("db_conn.php");
if (isset($_SESSION["email"])) {
        // Get the user's email from the session
        $user_email = $_SESSION["email"];
      
    $query = "SELECT number, fullname FROM customers1 WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $user_email);
    $stmt->execute();
    $stmt->bind_result($number, $fullname);
    $stmt->fetch();
    $stmt->close();

    // Store fetched data in the session
    $_SESSION['number'] = $number;
    $_SESSION['fullname'] = $fullname;
    }
    
    
$sql = "SELECT * FROM manual_funding";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        // Generate HTML code for each row
        echo '<div class="card">';
        echo '<center>' . $row['bank_name'] . '</center>';
        echo '<img class="logo" src="/vtusite/images/mastercard_logo.jpg" alt="' . $row['account_number'] . ' Logo">';
        echo '<div class="account-info">';
        echo '<div class="account-number" id="accountNumber">' . $row['account_number'] . '</div>';
        echo '<div class="name">' . $row['account_name'] . '</div>';
        echo '<div class="expiry">Charges: free</div>';
        echo '<br>';
        echo '<i class="fa fa-clone clipboard-icon" onclick="copyAccountNumber()"></i>';
        echo '</div>';
        echo '</div>';
        echo '<br><br>';
      
    }
   
     echo '<small>After Sending money using this method click the button below to send proof <button id="button2" onclick="toggleFunction(2)">proof </button>
</small>';
  
} else {
    echo '<center><img src="/vtusite/images/no_data2.jpg" style="width:100%; border-radius:10px; height:10%"><br>';
    echo '<strong   data-bs-dismiss="offcanvas" style="color:darkblue; position: absolute; top:82%; left:30%; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); background-color:darkblue; border-radius:10px; color:white; align-items:center; padding:4%" onclick="document.getElementById(\'id01\').style.display=\'block\'">No data available</strong></center>';
}

?>

</div>
 
</div>
</form>
</form>
<br>
<div id="function2" class="hidden"style="display:none">
  <?php include_once("proof_form.php");
  ?>
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
</center>
      <p></p>
    </div>
    <div id="menu2" class="container tab-pane fade"><br>
      <span style="background-color:#;box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1">Not yet</span>
      <p></p>
    </div>
  </div>

</div>
  


  </div>
</div>



</div>
</div>
</div>
</center></center>
<!-- add account name code -->
</form> </form> 
<div id="id01" class="modal"> 

<form id="accountForm" class="modal-content animate">
    
    <div class="imgcontainer">
        <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
        <img src="/vtusite/images/nibss.png" width="50%" alt="Avatar" class="avatar">
        <h6>According To NIBSS Regulations</h6>
        <small style="font-size:10px">
In order to generate your account, you need to link a valid ID, BVN, or NIN. Rest assured, we do not store your ID in our database. Your ID information is securely transmitted to NIBSS for the account generation process.</small> </center>
    </div>

    <div class="containere">
        <center>
            
            
            <input type="text" maxlength="30"id="name" name="name" value="<?php echo $_SESSION['fullname']; ?>">
            <input type="hidden" Maxlength="40"id="email" name="email" value="<?php echo $_SESSION['email']; ?>"><br>
            <input type="hidden" Maxlength="11"id="phoneNumber" name="phoneNumber" value="<?php echo $_SESSION['number']; ?>">
            <input type="hidden" Maxlength="20"id="bankcode" name="bankcode" value="120001,101">
            <input type="hidden" Maxlength="30"id="account_type" name="account_type" value="static">
            <input type="hidden" Maxlength="40"id="businessid" name="businessid" value="">
            <input type="text" Maxlength="11"id="bvn" name="bvn" placeholder="Enter NIN/BVN Here">
        </center>
        
        <center>
            <small style="font-size: 10px">Ensure that your fullname match the ID provide us</p></small>
        </center>
    </div>
    <div class="container" style="background-color: #f1f1f1">
        <center>
            <button type="button" onclick="submitAccountForm()">Let's Go</button>
        </center>
    </div>
    
</form>

<!-- Spinner Loader -->
<div id="loaderr" class="loaderr" style="display: none;"></div>
</div>
<script>
function submitAccountForm() {
    var email = document.getElementById("email").value;
    var name = document.getElementById("name").value;
    var phoneNumber = document.getElementById("phoneNumber").value;
    var bankcode = document.getElementById("bankcode").value;
    var account_type = document.getElementById("account_type").value;
    var businessid = document.getElementById("businessid").value;
    var bvn = document.getElementById("bvn").value;

    // Show loader
    showLoaderr();

    // Create an XMLHttpRequest object
    var xhttp = new XMLHttpRequest();

    // Define the function to be executed when the server response is ready
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            // Hide loader
            hideLoaderr();

            // Show SweetAlert based on the response
            var response = JSON.parse(this.responseText);
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    text: response.message,
                    confirmButtonColor: '#190F92'
                }).then((result) => {
                    window.location.href = 'dashboard.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    text: response.message,
                    confirmButtonColor: 'red'
                });
            }
        }
    };

    // Send the POST request
    xhttp.open("POST", "generate.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("email=" + encodeURIComponent(email) + "&name=" + encodeURIComponent(name) + "&phoneNumber=" + encodeURIComponent(phoneNumber) + "&bankcode=" + encodeURIComponent(bankcode) + "&account_type=" + encodeURIComponent(account_type) + "&businessid=" + encodeURIComponent(businessid) + "&bvn=" + encodeURIComponent(bvn));
}

function showLoaderr() {
    var loaderr = document.getElementById("loaderr");
    loaderr.style.display = "block";
}

function hideLoaderr() {
    var loaderr = document.getElementById("loaderr");
    loaderr.style.display = "none";
}

function redirectToDashboard() {
    setTimeout(function() {
        window.location.href = 'monnify.php';
    }, 2000); // 2 seconds
}
</script>

<style>
/* Spinner Loader CSS */
.loaderr{
    border: 16px solid #f3f3f3; /* Light grey */
    border-top: 16px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 70px;
    height: 70px;
    animation: spin 2s linear infinite;
    position: fixed;
    top: 50%;
    left: 40%;
    transform: translate(-50%, -50%);
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
<script>
// Get the modal
var modal = document.getElementById('id01');

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>


