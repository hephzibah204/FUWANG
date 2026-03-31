<?php
session_start();
include_once("db_conn.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $bank_name = $_POST['bank_name'];
    $account_number = $_POST['account_number'];
    $account_name = $_POST['account_name'];

    // Check if bank account already exists
    $check_sql = "SELECT * FROM manual_funding WHERE bank_name = '$bank_name' AND account_number = '$account_number'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // Bank account already exists
        $errors[] ="This bank account already exists.";
    } else {
        // Insert new bank account details into manual_funding table
        $sql = "INSERT INTO manual_funding (bank_name, account_number, account_name) VALUES ('$bank_name', '$account_number', '$account_name')";

        if ($conn->query($sql) === TRUE) {
            // New record created successfully
            echo
           $success[] = "";
        } else {
            // Error inserting record
            $errors[] ="Error: " . $sql . "<br>" . $conn->error;
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="theme-color" content="#190F92">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customers List</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="icon" href="images/logo2.png">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
    <style>
        body {
            background-color: #f1f1f1;
        }
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 0;
            width: 90%;
            margin-left: 5%;
            border: none;
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
            color: darkblue;
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
            color: darkblue;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        .expiry {
            color: darkblue;
            text-align: center;
            font-size: 14px;
        }
        .clipboard-icon {
            position: absolute;
            top: 10px;
            right: 10%;
            cursor: pointer;
            color: darkblue;
            font-size: 20px;
        }
        .main {
            background-color: #f1f1f1;
            padding: 30%;
        }
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
            background-color: #190F92;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            position: fixed;
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
            footer {
                    background-color: darkblue;
                    color: white;
                    padding: 20px 0;
                    margin-top: 50px;
                }

                footer p {
                    margin: 0;
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
           <strong style="width:40%;height:86%;border-radius:80%; position: absolute;top:2%;left:4%;color:white;font-size:20px">Manual Banks Account</strong>
        </h1>
        <div class="dropdown">
            <div class="menu-container">
                <i style="position: absolute;right:4%;margin-top:-10%;font-size:38px;color:white;" class="fa fa-gift" id="menu-icon"></i>
                <div class="dropdown-content">
                    <a href="#" onclick="document.getElementById('id011').style.display='block'" class="fa fa-plus"> Add Bank</a>
                    <a href="admin_account_settings.php" class="fa fa-gear"> Back To Account Settings</a>
                    <a href="admin_dashboard.php" class="fa fa-dashboard"> Back To Main Dashboard</a>
                    <hr>
                    <a href="resellers_logout.php" class="fa fa-sign-out"> Logout</a>
                </div>
            </div>
        </div>
 
        <div id="id011" class="modal" style="display: none; position: absolute; left: 7%; top: 0; width: 85%; height: 850%; border-radius:10px; padding-top: 30%;">
            <div class="modal-content">
                <span class="close" onclick="document.getElementById('id011').style.display='none'">&times;</span>
                <center><strong>Add Bank</strong></center>
                <form id="addBankForm" action="" method="post">
                    <center>
                        <br>
                        <input style="width:90%" type="text" id="bank_name" name="bank_name" placeholder="Bank Name" required><br><br>
                        <input style="width:90%" type="text" id="account_number" name="account_number" placeholder="Account Number" required><br><br>
                        <input style="width:90%" type="text" id="account_name" name="account_name" placeholder="Account Name" required><br><br>
                        <input style="color:white;box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); background-color:darkblue;padding:6%;border-radius:10px;" type="submit" name="add_bank" value="Add Bank">
                    </center>
                </form>
                <br><br>
            </div>
        </div>

       
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

</h1>

</div>

<br><br><br><br><br>

<h6>
         <center>    Add your Bank account here in other users funding they wallet through Manual Funding</center>
         <hr>
                </h6>
<?php
session_start();
include_once("db_conn.php");

// SQL query to fetch all rows from manual_funding table
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
        echo '<i class="fa fa-edit clipboard-icon" onclick="copyAccountNumber()"></i>';
        echo '</div>';
        echo '</div>';
        echo '<br><br>';
    }
} else {
    echo '<center><img src="/vtusite/images/no_data2.jpg" style="width:100%; border-radius:10px; height:10%"><br>';
    echo '<strong style="color:darkblue; position: absolute; top:82%; left:30%; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); background-color:darkblue; border-radius:10px; color:white; align-items:center; padding:4%" onclick="document.getElementById(\'id01\').style.display=\'block\'">No data available</strong></center>';
}

$conn->close();
?>
<?php echo include_once("footer.php");?>
  </body>
  </html>
  
  