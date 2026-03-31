<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Reseller Dashboard</title>
<link rel="manifest" href="/manifest.json">
   
  <!-- Bootstrap CSS -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>
    /* Custom Styles */
    body {
      
      font-family: Arial, sans-serif;
    }

.header{
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


  <div class="header">

<a href="admin_dashboard.php" style="color:white;Z-index:1;position: absolute ;margin-top:-5%;left:2%;font-size:27px;"class="fa fa-angle-left">

</a>


      

            <h2 style="font-size:20px;color:white; position:;margin-top:-5%;text-align:center"> <center>customers list</h2>
          
  
<h1>

</h1>
 <div class="dropdown">
  <div class="menu-container">
     <span style="position: absolute;right:2%;margin-top:12%;font-size:20px;color:white;"class="fa fa-ellipsis-v" id="menu-icon"></span>
     
    <div class="dropdown-content">
        
      <a href="#" onclick="document.getElementById('id011').style.display='block'" class="fa fa-plus-circle">  Add New User</a>

  <a href="#"class="fa fa-plus"onclick="document.getElementById('id01').style.display='block'"> Funding user</a>
  
  <hr> 
  <a href="admin_dashboard.php"class="fa fa-dashboard"> Back to Dashboard </a> 
  <hr>
      <a href="resellers_logout.php"class="fa fa-sign-out"> Logout</a>


    </div>
  </div>
</div>


</h1>

</div>
        
