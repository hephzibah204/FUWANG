<?php
session_start();
include_once("db_conn.php");

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    // Redirect to login page if not logged in
    header("Location: admin_login.php");
    exit();
}


$error = [];
$success = [];

?>

<!DOCTYPE html>
<html>
<head>
    <meta name="theme-color" content="#190F92">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customers List</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
      <link rel="icon" href="images/logo2.png">
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
    <style>
        body{
            overflow:;
            
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

        /* Add your styles here */
button{
       
       margin-left:20%;
       color:white;
       font-size:5px;
       border-radius:10px;
              background: linear-gradient(-45deg, rgb(42, 32, 172), rgb(14, 97, 26));
         }

  
/* Full-width input fields */
input[type=text], input[type=password] {
  width: 80%;
  padding: 6%;
  margin: 5px 0 22px 0;
  display: inline-block;
  border: none;
  border-radius:10px;
  background:#f1f1f1;
}
input[type=tel] {
  width: 80%;
  padding: 12px 20px;
  margin: 8px 0;
  border-radius:10px;
  display: inline-block;
  border: 1px solid #ccc;
  box-sizing: border-box;
background: #f1f1f1;
}


/* Add a background color when the inputs get focus */
input[type=text]:focus, input[type=password]:focus, input[type=tel]:focus {
  background-color: #ddd;
  outline: none;
}





button:hover {
  opacity:1;
}
button {
  background-color: #04AA6D;
  color: white;
  border-radius:10px;
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

        /* Add your styles here */
button{
       
       margin-left:20%;
       color:white;
       font-size:5px;
       border-radius:10px;
              background: linear-gradient(-45deg, rgb(42, 32, 172), rgb(14, 97, 26));
         }

  
/* Full-width input fields */
input[type=text], input[type=password] {
  width: 80%;
  padding: 6%;
  margin: 5px 0 22px 0;
  display: inline-block;
  border: none;
  border-radius:10px;
  background:#f1f1f1;
}
input[type=tel] {
  width: 80%;
  padding: 12px 20px;
  margin: 8px 0;
  border-radius:10px;
  display: inline-block;
  border: 1px solid #ccc;
  box-sizing: border-box;
background: #f1f1f1;
}


/* Add a background color when the inputs get focus */
input[type=text]:focus, input[type=password]:focus, input[type=tel]:focus {
  background-color: #ddd;
  outline: none;
}





button:hover {
  opacity:1;
}
button {
  background-color: #04AA6D;
  color: white;
  border-radius:10px;
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

label{

display:block;
}

@media screen and (max-width: 300px) {
  .cancelbtn, .signupbtn {
     width: 100%;
  }

.search-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .search-container input {
            width: 90%;
        }

        .fa-plus {
            margin-left: 10px;
            cursor: pointer;
        }
        .dropdown {
  
margin-top:-1%;
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



/* Set a style for all buttons */
button {
  background-color: darkblue;
  color: white;
  padding: 14px 20px;
  margin: 8px 0;
  border: none;
  border-radius:10px;
  cursor: pointer;
  width: 10%;
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

.containr {
  padding: 16px;
}

span.psw {
  float: right;
  padding-top: 16px;
}

/* The Modal (background) */
  
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
.clearfix::after {
  content: "";
  clear: both;
  display: table;
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
t{
    
width:200px;  
padding:80%;
position: absolute;  
}
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
.modal-content {
  background-color: ;
  margin: 5% auto 15% auto; /* 5% from the top, 15% from the bottom and centered */
  border: 1px solid #888;
  width: 80%; /* Could be more or less, depending on screen size */
}

    </style>
</head>
<body>
 <?php
                if(isset($error)) {
                    foreach($error as $errorrMessage) {
                        // Echo JavaScript to trigger SweetAlert
                        echo "<script>Swal.fire({ title: 'something went wrong', text: '$errorrMessage', icon: 'error' });</script>";
                    }
                }
            ?>
        </center>
 
   <?php
                if(isset($errors)) {
                    foreach($errors as $errorMessage) {
                        // Echo JavaScript to trigger SweetAlert
                        echo "<script>Swal.fire({ title: 'what do you mean??', text: '$errorMessage', icon: 'info' });</script>";
                    }
                }
            ?>
 
 <?php
 
 include_once("reseller_bar.php");
 ?>



<div class="model">

<div id="id011" class="modal">
  <span onclick="document.getElementById('id0 1').style.display='none'" class="close" title="Close Modal"></span>
<?php
if (isset($_POST["newuser"])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($email) || empty($password)) {
        $error[] = "Email and password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = "Invalid email format.";
    } else {
        // Check if email already exists in users table
        $checkEmailQuery = "SELECT * FROM customers1 WHERE email = ?";
        $stmt = $conn->prepare($checkEmailQuery);

        if ($stmt === false) {
            die("Error preparing the statement: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            die("Error executing the statement: " . $stmt->error);
        }

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error[] = "Email already exists.";
        } else {
            // Insert into users table with default values for other columns
            $insertUserQuery = "INSERT INTO customers1 (reseller_id, fullname, username, email, number, password, transaction_pin, is_reseller, image, device_fingerprint, online_status, created_time) 
                                VALUES ('default', 'default', 'default', ?, '00000000000', ?, '0000', 0, 'default.jpg', 'default', 'offline', CURRENT_TIMESTAMP)";
            $stmt = $conn->prepare($insertUserQuery);

            if ($stmt === false) {
                die("Error preparing the statement: " . $conn->error);
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("ss", $email, $hashedPassword);

            if ($stmt->execute()) {
                $success[] = "You have successfully added $email as a new user and this user is listed in restricted user, so you have to go account settings and update his full details in either user can Login his account";
            } else {
                $error[] = "Error executing the statement: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        }
    }
}

// Close the database connection

?>


   <?php
                if(isset($error)) {
                    foreach($error as $errorrMessage) {
                        // Echo JavaScript to trigger SweetAlert
                        echo "<script>Swal.fire({ title: 'something went wrong', text: '$errorrMessage', icon: 'error' });</script>";
                    }
                }
            ?>
            
             <?php
                if(isset($success)) {
                    foreach($success as $successMessage) {
                        // Echo JavaScript to trigger SweetAlert
                        echo "<script>Swal.fire({ title: 'Request success', text: '$successMessage', icon: 'success' });</script>";
                    }
                }
            ?>
        </center>
 
 
  <form class="modal-content" action="#" method="POST"style="position : absolute ;left: 7%;

  top: 20%;
  width: 85%;
  height: 50%; 
  
 border-radius:10px;
  padding-top: 15%;
  
  ">
    
    <center> <h6>Add New User</h6>
      <p style="font-size:10px">You can add user as much as you can.After added user, the user must enter his full details like his name and number during login</p>
      
      <hr>

 <?php
if (!empty($success)) {
    echo '<div class="success-message">';
    foreach ($success as $message) {
        echo '<p>' . $message . '</p>';
    }
    echo '</div>';
}

if (!empty($error)) {
    echo '<div class="error-message">';
    foreach ($error as $message) {
        echo '<p>' . $message . '</p>';
    }
    echo '</div>';
}
?>
      <input type="text" placeholder="Enter Email" name="email">

    
      <input type="password" placeholder="Enter Password" name="password">
      
  
 
   </label>
 <br>
  
      <div class="clearfix">
        <input style="color:white;box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); background-color:darkblue;padding:6%;border-radius:10px;"type="submit" name="newuser" value="Add user">
<br><br><br>
</div>
</center>
      </div>
    </div>
  </form>
</div>

<!-- add account name code -->


<?php

if (isset($_POST["fund_user"])) {
    $email = trim($_POST['email']);
    $amount = trim($_POST['amount']);

    // Basic validation
    if (empty($email) || empty($amount)) {
        $error[] = "Email and amount are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = "Invalid email format.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error[] = "Amount must be a positive number.";
    } else {
        // Check if the user exists in the account_balance table
        $sql_check_user = "SELECT * FROM account_balance WHERE email = ?";
        $stmt_check_user = mysqli_prepare($conn, $sql_check_user);
        mysqli_stmt_bind_param($stmt_check_user, 's', $email);
        mysqli_stmt_execute($stmt_check_user);
        $result_check_user = mysqli_stmt_get_result($stmt_check_user);

        if ($result_check_user && mysqli_num_rows($result_check_user) > 0) {
            // User exists, fetch the current balance
            $row = mysqli_fetch_assoc($result_check_user);
            $current_balance = $row["user_balance"];

            // Calculate the new balance by adding the provided amount
            $new_balance = $current_balance + $amount;

            // Update the user's balance
            $sql_update_balance = "UPDATE account_balance SET user_balance = ? WHERE email = ?";
            $stmt_update_balance = mysqli_prepare($conn, $sql_update_balance);
            mysqli_stmt_bind_param($stmt_update_balance, 'ds', $new_balance, $email);
            
            if (mysqli_stmt_execute($stmt_update_balance)) {
                // User balance updated successfully
                // Now insert into funding_history table
                $sql_insert_history = "INSERT INTO funding_history (email, amount) VALUES (?, ?)";
                $stmt_insert_history = mysqli_prepare($conn, $sql_insert_history);
                mysqli_stmt_bind_param($stmt_insert_history, 'sd', $email, $amount);
                
                if (mysqli_stmt_execute($stmt_insert_history)) {
                   
$success[] = "You have successfully funded $email with ₦$amount.";

// Display the success message
echo "<script>alert('" . $success[0] . "');</script>";

// Redirect after 2 seconds
echo "<script>setTimeout(function(){ window.location.href = 'resellers_user_list.php'; }, 2000);</script>";

                } else {
                    $error[] = "Error inserting funding history at this moment.";
                }
            } else {
                // Error updating user balance
                $error[] = "Error updating user balance at this moment.";
            }
        } else {
            // User not found
            $error[] = "No user with this email found on server.";
        }
    }
}
?>


<div id="id01" class="modal">
 
  <form class="modal-content animate" action="#" method="POST"style="position: absolute;  left: 7%;

  top: 20%;
  width: 85%;
  height: 50%;
  border-radius:10px;
 
  padding-top: 7%;
  ">

    
<div class="imgcontainer">
      <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal"></span>
    <center> <img src="/vtusite/images/send_money.jpg"width="50%"alt="Avatar" class="avatar"></center>
      <br>
      <center>
      <small>Here you can funding user manually using his email address.</small>
      </center>
    </div>

   
<center>
      
 
<input type="text" placeholder="Enter User Email" name="email" id="emailInput" onkeyup="checkAtSymbol(this.value)">

 <input type="tel" name="amount"placeholder="Enter Amount"id="emailHint" style=" display: none;">
 
   <div class="container" style="background-color: #f1f1f1">
                <center>
 <input style="color:white;box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); background-color:darkblue;padding:6%;border-radius:10px;"type="submit" name="fund_user"value="Fund user">
</center></form>





<script>
document.getElementById('emailInput').addEventListener('input', function(event) {
    var email = event.target.value;
    if (email.endsWith('@gmail.com')) {
        // Show loader
        

        // Send AJAX request to the server
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'check_email.php?email=' + email, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                // Hide loader
                document.getElementById('loader').style.display = 'none';
                
                if (xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.fullname) {
                        // If fullname exists in the response, display it
                        document.getElementById('fullnameDisplay').innerText = response.fullname;
                    } else {
                        // If fullname is not found, display an error message
                        document.getElementById('fullnameDisplay').style.color = 'tomato';
document.getElementById('fullnameDisplay').innerText = 'unknown user';

                    }
                } else {
                    // If there was an error with the request, display an error message
                    console.error('Error: ' + xhr.status);
                }
            }
        };
        xhr.send();
    }
});
</script>
            

             

            
              </div>
            
          </div>
        </div>
      </div>
    </div>
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




  
<?php

$query = "SELECT * FROM customers2 ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();


?>
<br><br><br>
<div>
        <br><br>
  <center>  <input style="width:90%; margin-top:1%;text-align:center"class="form-control" id="myInput" type="text" placeholder="Search user...."></center>

  
  
</nobr>
    <br>

    <table style="position: absolute;top:21%;"class="table table-bordered table-striped">
        <thead> 
  
            <tr>
                <th>Id</th>
                <th>Names</th>
                <th>Email</th>
                <th>Date</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody id="myTable">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
            ?>
                    <tr>
                        <td><?php echo $row["id"]; ?></td>
                        <td><?php echo $row["fullname"]; ?></td>
                        <td><?php echo $row["email"]; ?></td>
                        <td><?php echo $row["created_at"]; ?></td>
                        <td>
                          
                                <button type="button" class="fa fa-edit edit-btn" data-plan-id="<?php echo $row['id']; ?>"></button>
                            </form>
                        </td>
                        <td>
                        
<form id="deleteForm_<?php echo $row['id']; ?>" action="reseller_del_user.php" method="post">
    <input type="hidden" name="delete" value="<?php echo $row['id']; ?>">
    <button type="button" class="fa fa-trash-o deleteButton" data-id="<?php echo $row['id']; ?>"></button>
</form>


<script>
    $(document).ready(function() {
        // Add click event listener to delete buttons
        $('.deleteButton').click(function() {
            var id = $(this).data('id');
            // Display SweetAlert confirmation dialog
            Swal.fire({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover this user!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "No, cancel!",
                dangerMode: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    // If user confirms deletion, submit the form
                    $('#deleteForm_' + id).submit();
                }
            });
        });
    });
</script>

 <?php
                }
            } else {
                echo "<tr></tr>";
            }
            ?>
        </tbody>
       
    </table>
    </form>

  <div id="editModal" class="modal" style="display: none; position: absolute;  left: 7%;

  top: 0;
  width: 85%;
  height: 850%; 
  border-radius:10px;
  padding-top: 30%;
    
  ">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close"></span>


<?php
// Include the database connection file
include_once("db_conn.php");

// Initialize variables for form values
$id = "";
$fullname = "";
$email = "";
$password = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    // Retrieve the form values
    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the ID is valid
    if (!empty($id)) {
        // Check if the password field is not empty to update it
        if (!empty($password)) {
            // Hash the password (optional but recommended for security)
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Prepare the SQL update query
            $query = "UPDATE customers2 SET fullname = ?, email = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $fullname, $email, $hashedPassword, $id);
        } else {
            // Prepare the SQL update query without password update
            $query = "UPDATE customers2 SET fullname = ?, email = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $fullname, $email, $id);
        }

        // Execute the query
        if ($stmt->execute()) {
            $success[] = "User updated successfully.";
        } else {
            echo "Error updating user: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Invalid user ID.";
    }
}

// Close the database connection
$conn->close();
?>

<center><strong>Edit Customer</strong></center>
<form id="editForm" action="#" method="post">
    <center>
        <br>
        <!-- Input fields -->
        <input style="width:90%" type="text" id="id" name="id" value="<?php echo htmlspecialchars($id); ?>" readonly><br><br>
        <input style="width:90%" type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>"><br><br>
        <input style="width:90%" type="text" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly><br><br>
        <input style="width:90%" type="password" id="password" name="password" placeholder="Enter update password"><br><br>
        <input style="color:white;box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); background-color:darkblue;padding:6%;border-radius:10px;" type="submit" name="update_user" id="submitBtn" value="Edit user">
    </center>
</form>

     <br><br>

</div>
    
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
            fetch('fetch_edit_user.php?id=' + planId)
                .then(response => response.json())
                .then(data => {
                    // Populate input fields with fetched data

   document.getElementById('id').value = data.id;

                document.getElementById('fullname').value = data.fullname;

 document.getElementById('email').value = data.email;
                    
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


                        </td>
                    </tr>
           
    <script>
        $(document).ready(function() {
            $("#myInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#myTable tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
    
    
 <script>

function checkFields() {
  var emailInput = document.getElementById('emailInput').value;
  var amountInput = document.getElementById('amountInput').value;
  var pinInput = document.getElementById('pinInput').value;

  if (emailInput !== '' && amountInput !== '' && pinInput.length === 4) {
    document.getElementById('paymentForm').submit();
  }
}

     
    // Function to check if "@" symbol is present in the email input
    function checkAtSymbol(value) {
      const hint = document.getElementById("emailHint");
      const modal = document.getElementById("myModal");
      const amountInput = document.getElementById("amountInput");
      if (value.includes('@')) {
        hint.style.display = "block";
        modal.classList.add("flip");
        animateAmountInput(true);
      } else {
        hint.style.display = "none";
        modal.classList.remove("flip");
        animateAmountInput(false);
      }
    }

    // Function to animate the amount input field
    function animateAmountInput(show) {
      const amountInput = document.getElementById("amountInput");
      if (show) {
        amountInput.style.display = "block";
        amountInput.classList.add("animate__animated", "animate__bounceIn");
      } else {
        amountInput.classList.remove("animate__bounceIn");
        amountInput.style.display = "none";
      }
    }

 
  </script>

</html>
