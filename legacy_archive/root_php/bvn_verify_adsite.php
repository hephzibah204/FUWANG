<?php
  session_start();
  include_once("db_conn.php");
      include_once("admin_bvn_bar.php");
      
      if (!isset($_SESSION['loggedin'])) {
    // Redirect to login page if not logged in
    header("Location: admin_login.php");
    exit();
}
       
       ?>
    
<!DOCTYPE html>
<html>
<head>
     <meta name="theme-color" content="#190F92"><img src="<img src="" alt="">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Price list</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
      .hidden{
display:none;
}  


.button:hover {
  background-color: #45a049; /* Darker green */

}
.submit-button:hover{
    
    background-color:;
    border:3px solid black;
   
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
            .content{
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            background-color: #fff;
            width:100%;
            align-item: center;
            justify-content: center;
           
            
        }
        </style>
        </head>
    <body>
        

  
<div class="container mt-3">
  
  <br>
  <!-- Nav tabs -->
   <center>
  <ul class="nav nav-tabs" role="tablist">

 <li class="nav-item"style="display:one">
      <a class="nav-link active" data-bs-toggle="tab"href="#home">Home</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#menu1">Slip Prices</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#menu2">History</a>
 </li>

</ul>
 
  </center>
  <!-- Tab panes -->
  <div class="tab-content">
    <div id="home" class="container tab-pane active"><br>
    <?php


// Fetch current values from the database
$query = "SELECT * FROM verification_price";
$result = mysqli_query($conn, $query);
$settings = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bvn_by_bvn = mysqli_real_escape_string($conn, $_POST['bvn_by_bvn']);
    
    

    $update_bvn = "UPDATE verification_price
                     SET bvn_by_bvn = '$bvn_by_bvn'";
                        

    mysqli_query($conn, $update_bvn);

    // Refresh the settings after update
    $result = mysqli_query($conn, $query);
    $settings = mysqli_fetch_assoc($result);
}
?>

      <h3>Overview</h3>
      <br><br>
      <form action="#" method="POST">
          <center>
              <small>The current price for BVN verification is  ₦<?php echo $settings['bvn_by_bvn']; ?>. you can change it at any moment</small>
  <input type="text" name="bvn_by_bvn" style="width: 80%" value="<?php echo $settings['bvn_by_bvn']; ?>">
        <br>
        <button type="submit"class="button">update </button>


      </div>
    </div>
    
    
    
    
    <div id="menu1" class="container tab-pane fade"><br>
   
   
      <h1>
       <table style="position:;top:25%" width="100%">
           <center>
                <th  class="button1 "onclick="toggleFunction(1)" style=" background-color:#f1f1f1;border-radius:10px;justify-content:center;align-item: center;margin-left:55%">
                    <i class="submit-button">
                    <img src="/vtusite/images/bvn.jpg" style="width:80%;height:60px;border-radius:10px">
                </th>
              </i>
                  
            </table>
</h1>
<hr>
   <br><br><br>
   
   
   
   <?php


// Fetch current values from the database
$query = "SELECT * FROM id_verification_price";
$result = mysqli_query($conn, $query);
$settings = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bvn_slip_price = mysqli_real_escape_string($conn, $_POST['bvn_slip_price']);
    
    

    $update_query = "UPDATE id_verification_price 
                     SET bvn_slip_price = '$bvn_slip_price'";
                        

    mysqli_query($conn, $update_query);

    // Refresh the settings after update
    $result = mysqli_query($conn, $query);
    $settings = mysqli_fetch_assoc($result);
}
?>


   
   
   
   
    <div id="function1">
      
 
   
        <center>
        <h5 style="color: darkblue">Normal slip</h5>
        <hr>
         <small style="opacity:0.5">
   The current price for this slip is  ₦<?php echo $settings['bvn_slip_price']; ?>. you can update it at any moment</small><br>
        <img src="/vtusite/images/bvn_slip.png" width="50%">
        <br><br>
        <input type="text" name="bvn_slip_price" style="width: 80%" value="<?php echo $settings['bvn_slip_price']; ?>">
        <br>
        <button type="submit"class="button">update </button></soft>
        <br>
       </form>
        <br><br>
    
</div>

<div id="function2" class="hidden">
    
    
</div>

<div id="function3" class="hidden">
    
    
</div>


      
        
    </form>  
</div></div>
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
    </form></soft>
    
    <div id="menu2" class="container tab-pane fade">
      
      <center>     
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#190F92">
    <title>BVN Verification History</title>
    <link rel="icon" href="images/logo2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }
        .header {
            background-color: #190F92;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 24px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .content {
            background-color: #fff;
            margin: 20px auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .content:hover {
            background-color: #f1f1f1;
            transform: translateY(-3px);
        }
        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .user-info img {
            border-radius: 50%;
            width: 70px;
            height: 70px;
            margin-right: 15px;
            border: 2px solid #190F92;
        }
        .user-info h2 {
            margin: 0;
            font-size: 15px;
            color: #190F92;
        }
        .verification-details {
            font-size: 13px;
            color: #777;
        }
        .dropdown {
            display: none;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
            padding: 10px;
        }
        .dropdown a {
            text-decoration: none;
            color: #190F92;
            display: block;
            padding: 10px 5px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .dropdown a:hover {
            background-color: #f4f4f4;
        }
    </style>

    <script>
        function toggleDropdown(bvn) {
            const dropdown = document.getElementById('dropdown-' + bvn);
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        function goToDetails(bvn) {
            window.location.href = 'full_verified_bvn_details.php?bvn=' + bvn;
        }

        function deleteEntry(transactionId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'delete_bvn_history.php',
                        type: 'POST',
                        data: { transaction_id: transactionId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Deleted!', response.message, 'success');
                                $('#dropdown-' + transactionId).closest('.content').remove();
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error!', 'An error occurred while deleting the entry.', 'error');
                        }
                    });
                }
            });
        }
    </script>
</head>
<body>
    <div class="header"> Verified History</div>

<?php
include_once("db_conn.php");
session_start();

// Fetch all rows from the seamfix_bvn_history table
$sql = "SELECT user_email, transaction_id, response_data, created_at FROM bvn_history ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response_data = json_decode($row["response_data"], true);
        $image = isset($response_data['response']['photoId']) ? $response_data['response']['photoId'] : null;
        $bvn_fetched = isset($response_data['response']['bvn']) ? $response_data['response']['bvn'] : null;
        $first_name = isset($response_data['response']['first_name']) ? $response_data['response']['first_name'] : 'N/A';
        $middle_name = isset($response_data['response']['middle_name']) ? $response_data['response']['middle_name'] : 'N/A';
        $last_name = isset($response_data['response']['last_name']) ? $response_data['response']['last_name'] : 'N/A';
        $verification_type = "BVN VERIFICATION";

        if ($image) {
            // Decode the base64 image and save it as a file
            $image_data = base64_decode($image);
            $file_path = 'images/' . $bvn_fetched . '.jpg';
            file_put_contents($file_path, $image_data);

            echo "<div class='content'>";
            echo "<div class='user-info' onclick='toggleDropdown(\"$bvn_fetched\")'>";
            echo '<img src="' . $file_path . '" alt="User Photo" />';
            echo "<div>";
            echo "<h2>$first_name $middle_name $last_name</h2>";
            echo "<div class='verification-details'>Email: {$row['user_email']}<br>$verification_type<br>";
            echo date("l d, M Y H:i:s", strtotime($row["created_at"])) . "</div>";
            echo "</div></div><hr>";

            // Dropdown with actions
            echo "<div class='dropdown' id='dropdown-$bvn_fetched'>";
            echo "<a href='#' onclick='deleteEntry(\"{$row['transaction_id']}\")'>Delete</a>";
            echo "<a href='#' onclick='goToDetails(\"$bvn_fetched\")'>View Full BVN Details</a>";
            echo "</div></div>";
        } else {
            echo "<div class='content'></div>";
        }
    }
} else {
    echo "<p style='text-align:center;'>No BVN verification history found</p>";
}
$stmt->close();
$conn->close();
?>
</body>
</html>
       </div>
   
  </div>
</div>

       
        
        
        
        
        
        
        
        
        
        </body>
        </html>
        