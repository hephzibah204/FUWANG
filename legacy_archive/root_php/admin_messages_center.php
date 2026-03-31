<?php
include_once("db_conn.php");
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    // Redirect to login page if not logged in
    header("Location: admin_login.php");
    exit();
}




?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="theme-color"content="#190F92">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
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

button {
  background-color: darkblue;
  color: white;
  padding: 14px 20px;
  margin: 8px 0;
  border: none;
  cursor: pointer;
  width: 100%;
}
    </style>
</head>
<body>
    
    <div class="header">
<h1>
<strong style="width:40%;height:86%;border-radius:80%; position: absolute;top:2%;left:4%;color:white;font-size:20px">Manual messages</strong>
</h1>
 <div class="dropdown">
  <div class="menu-container">
      
    <i style="position: absolute;right:4%;margin-top:7%;font-size:35px;color:white;"class="fa fa-gift"class="fa fa-gift" id="menu-icon"></i>
    <div class="dropdown-content">
         <a href="admin_account_settings.php"class="fa fa-dashboard"> Back To Account Settings</a>
      <a href="admin_dashboard.php"class="fa fa-dashboard"> Back To Main Dashboard</a>
     
<hr>
 <a href="resellers_logout.php"class="fa fa-sign-out"> Logout</a>
    </div>
  </div>
</div>


</h1>

</div>

<br><br><br>

<p style="font-size:8px">
    The messages for proof customer is showing below based on the recent proof comes first.
    <br>Before you confirmed customer check ✅ your Bank account if you received payment for that account then you click on confirm.
    
    </p>
<?php
include_once("db_conn.php");

$today = date('Y-m-d');
$sql = "SELECT * FROM manual_proof_messages WHERE DATE(created_at) = '$today' ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '<div class="card">';
        echo '<div class="card">';
        echo '<div class="card-header">Message</div>';
        echo '<div class="card-body">';
        echo '<p style="display: none;">Email: ' . $row['email'] . '</p>';
        echo '<p>Account Number: ' . $row['account_number'] . '</p>';
        echo '<p>Bank Name: ' . $row['bank_name'] . '</p>';
        echo '<p>Amount: ' . $row['amount'] . '</p>';
        echo '<p>Remark: ' . $row['remark'] . '</p>';
        echo '<p>Date: ' . $row['created_at'] . '</p>';
        echo '</div>';
        echo '</div>';
        echo '<center>';
        echo '<form id="confirmForm_' . $row['id'] . '" method="post" action="insert_account_balance.php">';
        echo '<input type="hidden" name="email" value="' . $row['email'] . '">';
        echo '<input type="hidden" name="user_balance" value="' . $row['amount'] . '">';
        echo '<button type="submit" id="confirmBtn_' . $row['id'] . '">Confirm</button>';
        echo '</form>';
        echo '</center>';
        echo '<br>';
    }
} else {
    echo '<div class="card">';
    echo '<div class="card-header">Message</div>';
    echo '<div class="card-body">';
    echo '<p>No messages found.</p>';
    echo '</div>';
    echo '</div>';
}

$conn->close();
?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const confirmForms = document.querySelectorAll('form[id^="confirmForm_"]');
    confirmForms.forEach(form => {
        const buttonId = form.getAttribute('id').replace('confirmForm_', 'confirmBtn_');
        const confirmButton = document.getElementById(buttonId);

        // Check if this message has been confirmed
        const confirmed = localStorage.getItem('confirmed_' + buttonId);
        console.log('Button ID:', buttonId, 'Confirmed:', confirmed);
        if (confirmed) {
            confirmButton.style.display = 'none'; // Hide the button if confirmed
        }

        form.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent form submission
            
            // Show loader or any indication of processing
            confirmButton.innerHTML = 'Processing...'; // Change button text
            
            // Perform AJAX request
            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log(data); // Output response from insert_account_balance.php
                
                // Update button text to 'Confirmed'
                confirmButton.innerHTML = 'Confirmed';
                confirmButton.disabled = true; // Disable the button
                localStorage.setItem('confirmed_' + buttonId, 'true'); // Store confirmation status
            })
            .catch(error => {
                console.error('Error:', error);
                confirmButton.innerHTML = 'Confirm'; // Reset button text on error
            });
        });
    });
});
</script>
<?php echo include_once("footer.php")?>
</html>
