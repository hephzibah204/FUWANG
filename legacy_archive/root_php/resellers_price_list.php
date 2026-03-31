
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include_once("db_conn.php");

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    // Redirect to login page if not logged in
    header("Location: admin_login.php");
    exit();
}


// Check if form is submitted
if(isset($_POST['submit'])) {
    // Get values from input fields
    $resellerAmount = floatval($_POST['reseller_amount']);
    $planId = $_POST['plan_id'];
    $network = $_POST['network'];
    $planType = $_POST['plan_type'];
    $data_plan = $_POST['data_plan'];
    $amount = floatval($_POST['amount']);
    
    // Check if the plan_id exists for the current reseller_id in resellers_price_list
    // Check if the plan_id exists for the current reseller_id in resellers_price
    $checkQuery = "SELECT * FROM resellers_price WHERE plan_id = '$planId' AND reseller_id = '{$_SESSION['reseller_id']}'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (!$checkResult) {
        // Display error if query fails
        echo "Error checking plan: " . mysqli_error($conn);
    } else {
        if(mysqli_num_rows($checkResult) > 0) {
            // Plan ID exists for the current reseller, update the row
            $updateQuery = "UPDATE resellers_price SET reseller_amount = '$resellerAmount' WHERE plan_id = '$planId' AND reseller_id = '{$_SESSION['reseller_id']}'";
            $updateResult = mysqli_query($conn, $updateQuery);
            
            if (!$updateResult) {
                // Display error if update query fails
                echo "Error updating price: " . mysqli_error($conn);
            } else {
                // Price updated successfully
                echo "Price updated successfully!";
            }
        } else {
            // Plan ID doesn't exist for the current reseller, insert a new row
            $insertQuery = "INSERT INTO resellers_price (plan_id, network, plan_type, data_plan, amount, reseller_id, reseller_amount) VALUES ('$planId', '$network', '$planType', '$data_plan', '$amount', '{$_SESSION['reseller_id']}', '$resellerAmount')";
            $insertResult = mysqli_query($conn, $insertQuery);
            
            if (!$insertResult) {
                // Display error if insert query fails
                echo "Error adding price: " . mysqli_error($conn);
            } else {
                // Row inserted successfully
                echo "Price added successfully!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="theme-color" content="#15gt44">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <style>

  button{
       
       margin-left:33%;
       color:white;
       font-size:5px;
              background: linear-gradient(-45deg, rgb(42, 32, 172), rgb(14, 97, 26));
         }

  
/* Full-width input fields */
input[type=text], input[type=password] {
  width: 70%;
  padding: 5%;
  margin: 5px 0 22px 0;
  display: inline-block;
  border: none;
  background: #f1f1f1;
}

/* Add a background color when the inputs get focus */
input[type=text]:focus, input[type=password]:focus {
  background-color: #ddd;
  outline: none;
}




button:hover {
  opacity:1;
}
button {
  background-color: #04AA6D;
  color: white;
  
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


.container {
  padding:#;
}

.modal {
  display: none; 
  position: fixed; 
  z-index: 1%;
left:6%;
  top: 10%;
  width: 100%;
  height: 100%; 
  overflow: auto;
  background-color:;
position: absolute;


}

.modal-content {
   background-color:white;    
box-shadow: 0 0 10px rgba(0.1, 0.1, 0.1, 0.1);
  width: 90%; 
border-radius:10px;
border:1px solid white;

}
 

hr {
  border: 1px solid #f1f1f1;
  margin-bottom: 25px;
}
 

.close {
  position: absolute;
  right: 10%;
  top: 1%;
display:none;
  font-size: 40px;
  font-weight: bold;
  color: black;
}

.close:hover,
.close:focus {
  color: #f44336;
  cursor: pointer;
}

/* Clear floats */
.clearfix::after {
  content: "";
  clear: both;
  display: table;
}
label{

display:block;
}

@media screen and (max-width: 300px) {
  .cancelbtn, .signupbtn {
     width: 100%;
  }
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
   </style>  
</head>
<body>
<?php  include_once("reseller_bar.php");?>
<br><br><br>


    <div class="menu">
        <center>
            <input style="width:100%" class="form-control" id="myInput" type="text" placeholder="Search available plans....">

<div>
        <center><strong style="color:darkblue;font-size:25px">Resellers Prices List<br><center></strong></div>
    

  <br>

       <table class="table table-bordered table-striped"style="width:100%">
<thead>
<tr>
<center>
                <th style="color: black; opacity: 0.7">Plan ID</th>
                <th style="color: black; opacity: 0.8">Network</th>
                <th style="color: black; opacity: 0.8">Plan Type</th>
                <th style="color: black; opacity: 0.8">Plan Name</th>
                <th style="color: black; opacity: 0.8">Amount</th>
                <th style="color: black; opacity: 0.8">Validate</th>
                <th style="color: black; opacity: 0.8">Edit</th>
                <th style="color: black; opacity: 0.8">Delete</th>
</tr>
               </thead>
<tbody id="myTable"width="100%">
               <?php
include_once("db_conn.php");

// Fetch data from price_list table
$query = "SELECT * FROM price_list";
$query_run = mysqli_query($conn, $query);

// Check if data fetching was successful
if ($query_run) {
    // Display fetched data
    if (mysqli_num_rows($query_run) > 0) {
        foreach ($query_run as $row) {
            ?>

  <tr>

           
                <td style="color: black; opacity: 0.6"><?php echo $row["plan_id"]; ?></td>
                <td style="color: black; opacity: 0.6"><?php echo $row["network"]; ?></td>
                <td style="color: black; opacity: 0.6"><?php echo $row["plan_type"]; ?></td>
                <td style="color: black; opacity: 0.6"><?php echo $row["data_plan"]; ?></th>
                <td style="color: black; opacity: 0.6"><?php echo $row["amount"]; ?></td>
                <td style="color: black; opacity: 0.6"><?php echo $row["validate"]; ?></td>
                <!-- Edit button -->
                <td>
                    <button type="button" class="fa fa-edit edit-btn" data-plan-id="<?php echo $row['plan_id']; ?>"></button>
                </td>
                <!-- Delete button -->
                <td>
                    <form action="#" method="post">
                        <input type="hidden" name="delete" value="<?php echo $row['plan_id']; ?>">
                        <button type="submit"style="width:auto;" name="del" class="fa fa-trash-o"></button>
                    </form>
                </td>
            </tr>

            <?php
        }
    } else {
        echo "<tr></tr>";
    }
} else {
    echo "Error fetching data: " . mysqli_error($conn);
}
?>

                           
  </tbody>   
</table>  
</div>  
<script>
$(document).ready(function(){
  $("#myInput").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#myTable tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});
</script>

            <br><br><br>
    </div>

    <!-- Modal -->
    <div id="editModal" class="modal" style="display: none;">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close">&times;</span>



    <center><strong>Edit Custom Price</strong></center>
    <form id="editForm" action="#" method="post">
        <!-- Input fields -->
<center>
        <label for="plan_id">Plan ID:</label>
        <input type="text" id="plan_id" name="plan_id" readonly><br><br>
<label for="network">Network</label>
        <input type="text" id="network" name="network" readonly><br><br>
        <label for="plan_type">Plan Type:</label>
        <input type="text" id="plan_type" name="plan_type" readonly><br><br>
        <label for="plan_name">Plan Name:</label>
        <input type="text" id="plan_name" name="data_plan" readonly><br><br>
        <label for="amount">Amount:</label>
        <input type="text" id="amount" name="amount" readonly><br><br>
        <!-- Additional input fields -->
         
        <label for="reseller_amount">Reseller Amount:</label>
        <input type="text" id="reseller_amount" name="reseller_amount"><br><br>
        <!-- Submit button -->

        <input type="submit" name="submit" id="submitBtn" style="color:white;box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); background-color:darkblue;padding:6%;border-radius:10px;" value="Add price">

</center>
    </form>

   


    
        </div>
    </div>

    <script>
        // Get the modal
        var modal = document.getElementById("editModal");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks the button, open the modal and fetch data for the selected plan
        var editBtns = document.querySelectorAll('.edit-btn');
        editBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var planId = this.getAttribute('data-plan-id');
                fetchPlanData(planId);
                modal.style.display = "block";
            });
        });

        // Function to fetch plan data from the server
        function fetchPlanData(planId) {
            fetch('fetch_plan_data.php?plan_id=' + planId)
                .then(response => response.json())
                .then(data => {
                    // Populate input fields with fetched data
   
                document.getElementById('plan_id').value = data.plan_id;

 document.getElementById('network').value = data.network;
                    document.getElementById('plan_type').value = data.plan_type;
                    document.getElementById('plan_name').value = data.data_plan;
                    document.getElementById('amount').value = data.amount;
                })
                .catch(error => console.error('Error:', error));
        }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        };

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };

      

    </script>
</body>
</html>
