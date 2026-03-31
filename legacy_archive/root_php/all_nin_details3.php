<?php
     
     include_once("db_conn.php");
     session_start();
if (isset($_GET['nin'])) {
    $nin = $_GET['nin']; // Get the value from the URL parameter
    // Destroy only the 'nin_fetched' session variable

}
    ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="theme-color" content="#15gt44">
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1,minimum-scale=1,width=device-width,interactive-widget=resizes-content,initial-scale=1.0, user-scalable=no">
<meta name="theme-color"content="darkblue">
    <title>NIN verification</title>
       <link rel="icon" href="images/logo2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Ensure the body has no margins or padding */
        body {
            margin: 0;
            padding: 0;
        }

        .print-icon {
            position: fixed;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            right: 5%;
            bottom: 20px; /* Adjust this value as needed */
            font-size: 24px; /* Adjust the size of the icon as needed */
            color: black; /* Adjust the color of the icon as needed */
        }
   
              /* Hide the dropdown content by default */
  .dropdown-content {
            display: none;
            position: fixed;
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
        .header{
color:white;
justify-content: center;
text-align: center;
top:0;
position:fixed;
background-color: #190F92;
width:100%;
}
     body {
           
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
            
        }  
         button {
            width: 50%;
            padding: 10px;
            background-color: #190F92;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }  
            .content {
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            background-color: #fff;
            
        }
        .slip_div{
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding:10%;
            width:70%;
            justify-content: center;
            align-items: center;
             margin:5%;
            
            text-align: center;
        }
        </style>
        <body>
          
       
          
          
            
                <div class="dropdown">
  <div class="menu-container">
      


     <i style="font-size:80px;color:#190F92;border-radius:10px; background-color:darkblu;" class="fa fa-print print-icon"></i>

    <div class="dropdown-content">
          <a href="available_slip_search3.php?nin=<?php echo $nin ?>"class="fa fa-download"> Download PDF</a>
          
           <a href="available_slip_search3.php?nin=<?php echo $nin ?>"class="fa fa-print"> Print Slip</a>
             <a href="nin_history_search3.php"class="fa fa-dashboard"> Go To Verify History </a>
<div style="border:1px solid #f1f1f1;">
 <a href="users_logout.php"class="fa fa-sign-out"> Logout</a>
    </div>
  </div>
</div>
       
<div class="header">
    <center>
            <h2 style="font-size:40px;">Verified history</h2>
            <br>
</div>
</div>
<br><br><br><br>



<?php



if (isset($_GET['nin'])) {
    $nin = $_GET['nin']; // Get the value from the URL parameter

    $sql = "SELECT response_data FROM id_search3_history WHERE response_data LIKE ?";
    $stmt = $conn->prepare($sql);
    $like_nin = '%"nin":"' . $nin . '"%';
    $stmt->bind_param("s", $like_nin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Output data of the first matching row
        $row = $result->fetch_assoc();
        $response_data_json = $row["response_data"];
        $response_data = json_decode($response_data_json, true);

        // Include Bootstrap CSS
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
           
            
            <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <style>
            body{
                
                body {
    transform: scale(0.9); /* Adjust the scale factor as needed */
    transform-origin: 0 0; /* Ensures scaling starts from the top-left corner */
}
                
            }
            </style>
        <body>
        <div class="container mt-5">
            
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>';

        // Iterate over all the keys and values in the decoded response data
        foreach ($response_data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value); // Convert nested arrays to JSON string
            }

            if ($key === 'photo' || $key === 'signature') {
            // Convert base64 image to normal image
            echo '<tr><td>' . ucfirst($key) . '</td><td><img src="data:image/jpeg;base64,' . $value . '" /></td></tr>';
        } else {
            echo '<tr><td>' . ucfirst($key) . '</td><td>' . $value . '</td></tr>';
        }
    }

    echo '</table></center>';
    
}}
?>


