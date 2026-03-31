<?php
include_once("db_conn.php");
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    // Redirect to login page if not logged in
    header("Location: admin_login.php");
    exit();
}



// Query to fetch users without referrals, ordered by created_time descending
$sql = "SELECT fullname, reseller_id, created_at, email FROM customers2 WHERE email NOT IN (SELECT email FROM referral_commision_rewards) ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

// Initialize an array to store the results
$users_data = array();

// Check if the query was successful
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users_data[] = $row;
    }
} else {
   $error[]="Error: " . mysqli_error($conn);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the new rewards amount from the POST request
    $new_rewards_amount = $_POST['rewards_amount'];

    // Update the rewards_amount in the database
    $sql = "UPDATE referral_reward SET rewards_amount = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("d", $new_rewards_amount);

    if ($stmt->execute()) {
       $success[]="Record updated successfully";
    } else {
        $error[] ="Error updating record: " . $conn->error;
    }

    $stmt->close();
}

// Retrieve the current rewards_amount
$sql = "SELECT rewards_amount FROM referral_reward LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $rewards_amount = $row["rewards_amount"];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="theme-color"content="#190F92">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rewards list</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
       background-color: #f1f1f1;
      font-family: Arial, sans-serif;
      }
        .user-card {
            margin-bottom: 20px;
        }
        button {
            background-color: darkblue;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
            width: 100%;
        }
        button:hover{
            
            background-color:green;
        }
        
    .reward-btn {
        transition: background-color 0.3s, color 0.3s; /* Smooth transition for color changes */
    }

    .reward-btn:disabled {
        cursor: not-allowed; /* Change cursor to indicate the button is disabled */
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

    </style>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
     
    <div class="header">
<h1>
    <nobr>
<strong style="width:40%;height:86%;border-radius:80%; position: absolute;top:17%;left:4%;color:white;font-size:23px">Referral Rewards</strong></nobr>
</h1>
 <div class="dropdown">
  <div class="menu-container">
      
    <i style="position: absolute;right:4%;margin-top:-6%;font-size:25px;color:white;"class="fa fa-ellipsis-v" id="menu-icon"></i>
    <div class="dropdown-content">
         <a onclick="document.getElementById('id011').style.display='block'"href="#"class="fa fa-money"> Change rewards amount </a>
         <a href="admin_dashboard.php"class="fa fa-dashboard"> Back To Dashboard</a>

     
<hr>
 <a href="resellers_logout.php"class="fa fa-sign-out"> Logout</a>
    </div>
  </div>
</div>

</div>
</h1>

</div>

 <div id="id011" class="modal" style="display: none; position: fixed; left: 7%; top: 0; width: 85%; height: 850%; border-radius:10px; padding-top: 30%;">
<div class="modal-content">
                <span class="close" onclick="document.getElementById('id011').style.display='none'"></span>
                <center><strong>Referral Rewards Amount</strong></center>
                
                <form  action="#" method="post">
    <center>
        <input type="tel"id="notificationInput" name="rewards_amount" style="padding:7%; background-color:#f1f1f1" placeholder="Edit ✍️ referral Rewards amount..."><br>
          <button style ="width:60%"type="submit"value="update">update</button>
          <hr>
        <br
        
        <small>Current Referral Rewards Amount</small>
       
        <hr>
        <div>
            ₦<?php echo $rewards_amount;?>
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


                
               
      
      
      
      
      
      
  
<br><br><br><br><br>
<p style="margin:2%;font-size:10px">Every New user is displaying here including his reseller whose refer him.</p>
<p style="margin:2%;font-size:10px">the rewards button it's show's that the current amount want rewards reseller.</p>
    <div class="container">
        
        <?php if (empty($users_data)): ?>
            <p>No New user available for rewards </p>
        <?php else: ?>
            <?php foreach ($users_data as $user): ?>
                <div class="card user-card">
                    <div class="card-body">
                     <form class="reward-form">
    <input type="hidden" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>">
    
    <input type="hidden" name="created_at" value="<?php echo htmlspecialchars($user['created_at']); ?>">
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
    <input type="hidden" name="amount" value="<?php echo htmlspecialchars($rewards_amount); ?>">
    
   <input type="hidden" name="reseller_id" value="<?php echo htmlspecialchars($user['reseller_id']); ?>">
 
    <h5 class="card-title"><?php echo htmlspecialchars($user['fullname']); ?></h5>
   
    <p class="card-text">Created Time: <?php echo htmlspecialchars($user['created_at']); ?></p>
    <p class="card-text">Email: <?php echo htmlspecialchars($user['email']); ?></p>


 <p class="card-text">Referral 🆔: <?php echo htmlspecialchars($user['reseller_id']); ?></p>

    <!-- Submit button for this user -->
    <button type="button" class="reward-btn">Reward ₦ <?php echo htmlspecialchars($rewards_amount); ?></button>
</form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.reward-btn').forEach(button => {
            button.addEventListener('click', () => {
                const form = button.closest('.reward-form');
                const formData = new FormData(form);

                fetch('insert_referral_rewards.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    console.log(data);
                    button.innerHTML = data.trim(); // Change button text to response message
                    button.disabled = true;
                    button.style.backgroundColor = 'green'; // Change background color to green
                    button.style.color = 'white'; // Change text color to white for better readability
                })
                .catch(error => {
                    console.error('Error:', error);
                    button.innerHTML = 'Reward ₦<?php echo htmlspecialchars($rewards_amount); ?>'; // Reset button text on error
                    button.style.backgroundColor = 'red'; // Change background color to red on error
                });
            });
        });
    });
</script>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>