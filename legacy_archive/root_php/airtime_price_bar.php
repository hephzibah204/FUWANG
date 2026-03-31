<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Reseller Dashboard</title>
<link rel="manifest" href="/manifest.json">
   
  <!-- Bootstrap CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>
    /* Custom Styles */
    body {
      
      font-family: Arial, sans-serif;
    }
    input[type=text],select, input[type=tel] {
  width: 100%;
  padding: 15px;
  margin: 5px 0 22px 0;
  display: inline-block;
  border: none;
  background: #f1f1f1;
}


input[type=text]:focus, input[type=tel]:focus {
  background-color: #;
  outline: none;
}

.headers{
background-color:#190F92;
box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
position:fixed;
width:100%;
top:0%;
padding:5%;
z-index:1;


}

/* Style the dropdown container */
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



</style>
</head>
<body>


  <div class="headers">






      

            <h2 style="font-size:20px;color:white; position:;margin-top:-5%;text-align:center"> <center>Airtime Settings</h2>
          
  
<h1>

</h1>
 <div class="dropdown">
  <div class="menu-container">
     <span style="position: absolute;right:2%;margin-top:6%;font-size:20px;color:white;"class="fa fa-ellipsis-v" id="menu-icon"></span>
     
    <div class="dropdown-content">
        
    

 <a class="fa fa-dashboard"href="admin_dashboard.php">Back To Dashboard</a>
    <a type="button" data-bs-toggle="offcanvas" data-bs-target="#demo"href="#"class="fa fa-code"> API s setting management</a>
  <hr>
      <a href="resellers_logout.php"class="fa fa-sign-out"> Logout</a>


    </div>
  </div>
</div>


</h1>




<div class="offcanvas offcanvas-bottom" id="demo">
  <div class="offcanvas-header">
    <h3 class="offcanvas-title">Change of API settings</h3>
    <hr>
    
  </div>
  <div class="offcanvas-body"style="font-size:15px">
    <p><center>Hey👋 an Admin, here you can change an API for the above services page you're in. if you will change an API here is not mean the remaining services API will change, no! on every service page have specific api filter management.</center></p>
    
    <?php 
// Include the database connection file

include_once("db_conn.php");
// Fetch current data_api_type from the database
$query = "SELECT airtime_api_type FROM api_settings LIMIT 1";
$result = mysqli_query($conn, $query);

// Check if the query was successful and fetch the data
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $airtime_api_type = $row['airtime_api_type'];
} else {
    // Set a default value or handle the error
    $airtime_api_type = '';
    $errors[] ="Error fetching data: " . mysqli_error($conn);
}

// Check if form is submitted to update the data_api_type
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_airtime_api_type = $_POST['airtime_api_type'];
    
    // Update the data_api_type in the database
    $update_query = "UPDATE api_settings SET
    airtime_api_type = '$new_airtime_api_type' WHERE id = 1"; // Assuming there's only one row to update
    if (mysqli_query($conn, $update_query)) {
        $successs[] = "airtime API Type updated successfully!";
        
        $airtime_api_type = $new_airtime_api_type;
    } else {
        $errors[] ="Error updating record: " . mysqli_error($conn);
    }
}

// Close the database connection

?>


    <script type="text/javascript">
        function autoSubmit() {
            document.getElementById('airtimeApiForm').submit();
        }
    </script>
</head>
<body>
    <form method="post" id="airtimeApiForm">
        <select name="airtime_api_type" onchange="autoSubmit()">
            <option selected><?php echo htmlspecialchars($airtime_api_type); ?></option>
            <option value="switch off">Switch Off</option>
            <option value="bilalsadasub">Bilalsadasub</option>
            <option value="maskawasub">Maskawasub</option>
        </select>
        <!-- Remove the submit button -->
        <!-- <button type="submit">Update</button> -->
        <small>The current API is displaying in the above input, so you have a chance to change it on every time.</small>
   

  
  
</div>
</div>


</body>
</html>

</div>
        
        