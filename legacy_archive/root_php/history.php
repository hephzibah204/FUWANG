<?php
include_once("db_conn.php");


include_once ("navbar.php");
?>





<!DOCTYPE html>
<html lang="en">
<head>
 <meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color"content="darkblue">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

.nav-item{

padding:3%;
color:white;
margin-left:1%;
 
}
.nav-link{
color:white;
opacity:0.5;
background-color: darkblue;
}
.header{
color:white;

top:0;
left:-0.1%;
position: absolute;
background-color: #190F92;
width:100%;
}
.container1{
height:25%;
width:100%;
left:-1%;
position: absolute;
top:0%;
}
a{
text-decoration:none;

}
    </style>
</head>
<body>
    <div class="container1">
        <br>
<a href="dashboard.php" style="color:white;Z-index:1;position: absolute ;margin-top:-1%;left:2%;font-size:25px;"class="fa fa-arrow-left">

</a>

</div>
       <center>
<div class="header">
    <br>
            <h2 style="font-size:20px">Transactions History</h2>
</div>
</div>
<br><br><br>
Transactions is showing based on the selected transaction you want look
        </center>

<div class="container mt-3">
  <br>
  <!-- Nav pills -->
  <ul class="nav nav-pills" role="tablist">
    <li class="nav-item">
<center>
      <a class="nav-link active" data-bs-toggle="pill" href="#home"style="display:none"></a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="pill" href="#menu1">Airtime history</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="pill" href="#menu2">Data history</a>
    </li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div id="home" class="container tab-pane active"><br>
<hr>


<?php
// Initialize arrays to store network names and corresponding total amounts
$networks = array();
$total_amounts = array();

// SQL query to calculate total amount purchased for each network
$total_query = "SELECT network, SUM(amount) AS total_amount FROM airtime_transactions_history WHERE email='$email' GROUP BY network";
$total_query_run = mysqli_query($conn, $total_query);

if($total_query_run) {
    // Loop through the results to populate arrays with data for each network
    while($row = mysqli_fetch_assoc($total_query_run)) {
        $networks[] = $row['network'];
        $total_amounts[] = $row['total_amount'];
    }
} else {
    echo "Error: " . mysqli_error($conn);
}
?>


  
    <!-- HTML canvas element to draw the chart -->
    <canvas id="airtimeChart"></canvas>

    <script>
    // JavaScript code to draw the chart using Chart.js
    var ctx = document.getElementById('airtimeChart').getContext('2d');
    var airtimeChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($networks); ?>,
            datasets: [{
                label: 'Total Airtime Sell ',
                data: <?php echo json_encode($total_amounts); ?>,
                backgroundColor: [
                    'rgba(0, 0, 139, 0.5)', // Dark blue
                    'rgba(0, 100, 0, 0.5)', // Dark green
                    'rgba(139, 69, 19, 0.5)', // Saddle brown
                    'rgba(192, 192, 192, 0.5)', // Light grey
                    'rgba(139, 0, 0, 0.5)' // Dark red
                ],
                borderColor: [
                    'rgba(0, 0, 139, 1)',
                    'rgba(0, 100, 0, 1)',
                    'rgba(139, 69, 19, 1)',
                    'rgba(192, 192, 192, 1)',
                    'rgba(139, 0, 0, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
    </script>
</body>
</html>
<br>
<hr>

<?php
// Query to fetch total amount of data purchased by the user for each network
$query = "SELECT network, SUM(amount) AS total_amount FROM data_transactions_history WHERE email='$email' GROUP BY network";
$query_run = mysqli_query($conn, $query);

// Define specific background colors for each network
$networkColors = array(
    'Network1' => 'rgba(139, 69, 19, 0.2)',   // SaddleBrown
    'Network2' => 'rgba(255, 99, 71, 0.2)',   // Tomato
    'Network3' => 'rgba(160, 82, 45, 0.2)',   // Sienna
    'Network4' => 'rgba(205, 133, 63, 0.2)',  // Peru
    // Add more networks and colors as needed
);

// Initialize an array to store the results
$data = array();

// Fetch data from the query result and store it in the $data array
while($row = mysqli_fetch_assoc($query_run)) {
    $data[$row['network']] = $row['total_amount'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Purchases by User</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <canvas id="dataChart" width="400" height="400"></canvas>

    <script>
        var ctx = document.getElementById('dataChart').getContext('2d');
        var data = <?php echo json_encode($data); ?>;
        
        var labels = Object.keys(data);
        var amounts = Object.values(data);
        
        // Define background colors for each network
        var backgroundColors = [
            <?php foreach ($networkColors as $color) {
                echo "'$color',";
            } ?>
        ];

        var chartData = {
            labels: labels,
            datasets: [{
                label: 'Total Data Sold',
                backgroundColor: backgroundColors,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                data: amounts
            }]
        };

        var options = {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value, index, values) {
                            return '₦' + value; // Add ₦ sign to the label
                        }
                    }
                }]
            }
        };

        var barChart = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: options
        });
    </script>
</body>
</html>


    </div>
    <div id="menu1" class="container tab-pane fade"><br>



      


<?php
$query = "SELECT * FROM airtime_transactions_history WHERE email='$email' ORDER BY purchase_time DESC";
$query_run = mysqli_query($conn, $query);
if(mysqli_num_rows($query_run) > 0) {
    foreach($query_run as $row) {
?>
    <p>&nbsp;</p>
    <ul class="users-list-wrapper media-list" style="box-sizing: border-box; color: black;font-family: Ubuntu, sans-serif; font-size: 14px; letter-spacing: 0.14px; margin: 0px; padding: 0px;">
        <li class="" style="animation: 0.5s linear 0.1s 1 normal both running fadeIn; box-sizing: border-box; cursor: pointer; position: relative; transition: all 0.2s ease 0s;">
            <div class="chat-list-item d-flex flex-row p-2 border-bottom" style="-webkit-box-direction: normal !important; -webkit-box-orient: horizontal !important;background-color:#f1f1f1; border-radius:10px;cursor:pointer; border-bottom: 1px solid; box-sizing: border-box; cursor: pointer; display: flex !important; flex-direction: row !important; padding: 1rem !important;">
                <div class="customer-content" style="box-sizing: border-box;">
                    <div class="name" style="box-sizing: border-box;"><?php echo $row["network"];?> <?php echo $row["type"];?><span style="box-sizing: border-box; color: black; font-size: 13px; position: absolute; right: 10px; top: 14px;">₦<?php echo $row["amount"];?></span>
                    <span style="box-sizing: border-box; font-size: 11px;"><br style="box-sizing: border-box;" />Status:<span class="badge" style="background: transparent; border-radius: 0.25rem; box-sizing: border-box; color: #29c770; display: inline-block; font-weight: bolder; line-height: 1; padding: 0.35em 0.4em; text-align: center; text-wrap: nowrap; transition: color 0.15s ease-in-out 0s, background-color 0.15s ease-in-out 0s, border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s; vertical-align: baseline;">TRANSACTION SUCCESSFUL</span><br style="box-sizing: border-box;" />Trans. ID:<?php echo $row["request_id"];?> <img src="" alt=""><br style="box-sizing: border-box;" />Date: <?php echo $row["purchase_time"];?></span></div>
                    <div class="small last-message" style="box-sizing: border-box; font-size: smaller; margin-bottom: 15px; padding-top: 5px; white-space-collapse: preserve-breaks;">Your ₦<?php echo $row["amount"];?> purchase on <?php echo $row["phone"];?> was successful and your new balance is ₦<?php echo $user_balance;?></div>
                    <div class="customer-btns" style="background: darkblue; border: 0px; bottom: 0px; box-sizing: border-box; color: yellow; font-size: 11px; left: 0px; padding: 3px 3px 3px 10px; position: absolute; right: 0px;"></div>
                </div>
            </div>
        </li>
    </ul>
<?php
    }
} else {
    echo "<tr>No transaction found</tr>";
} 
?>

    </div>



<!--data history-->
    <div id="menu2" class="container tab-pane fade"><br>



       <?php
$query = "SELECT * FROM data_transactions_history WHERE email='$email' ORDER BY purchase_datetime DESC";
$query_run = mysqli_query($conn, $query);
if(mysqli_num_rows($query_run) > 0) {
    foreach($query_run as $row) {
?>
              
             <p>&nbsp;</p>
             <ul class="users-list-wrapper media-list" style="box-sizing: border-box; color: #626262; font-family: Ubuntu, sans-serif; font-size: 14px; letter-spacing: 0.14px; margin: 0px; padding: 0px;">
                 
                 <li class="" style="animation: 0.5s linear 0.1s 1 normal both running fadeIn; box-sizing: border-box; cursor: pointer; position: relative; transition: all 0.2s ease 0s;">
                     <div class="chat-list-item d-flex flex-row p-2 border-bottom" style="-webkit-box-direction: normal !important; -webkit-box-orient: horizontal !important; background-color:#f1f1f1; border-radius:10px;cursor:pointer;border-bottom: 1px solid; box-sizing: border-box; cursor: pointer; display: flex !important; flex-direction: row !important; padding: 1rem !important;">
                 
                 <div class="customer-content" style="box-sizing: border-box;">
                 
                 <div class="name" style="box-sizing: border-box;"><?php echo $row["network"];?> DATA<span style="box-sizing: border-box; color: black; font-size: 13px; position: absolute; right: 10px; top: 14px;">₦<?php echo $row["reseller_amount"];?></span>
                 <span style="box-sizing: border-box; font-size: 11px;"><br style="box-sizing: border-box;" />Status:<span class="badge" style="background: transparent; border-radius: 0.25rem; box-sizing: border-box; color: #29c770; display: inline-block; font-weight: bolder; line-height: 1; padding: 0.35em 0.4em; text-align: center; text-wrap: nowrap; transition: color 0.15s ease-in-out 0s, background-color 0.15s ease-in-out 0s, border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s; vertical-align: baseline;">TRANSACTION SUCCESSFUL</span><br style="box-sizing: border-box;" />Trans. ID:<?php echo $row["request_id"];?> <img src="" alt=""><br style="box-sizing: border-box;" />Date: <?php echo $row["purchase_datetime"];?></span></div>
                 
                 <div class="small last-message" style="box-sizing: border-box; font-size: smaller; margin-bottom: 15px; padding-top: 5px; white-space-collapse: preserve-breaks;">Your <?php echo $row["plan_type"];?> purchase on <?php echo $row["phone"];?> was successful and your New balance is ₦<?php echo $user_balance;?></div>
                 
                  <div class="customer-btns" style="background:darkblue; border: 0px; bottom: 0px; box-sizing: border-box; color: yellow; font-size: 11px; left: 0px; padding: 3px 3px 3px 10px; position: absolute; right: 0px;">
                                                              </div></div></li>
                             
     
  <?php
        }
} else {
    echo "<tr>No transaction found</tr>";
} 
?>



    </div>
  </div>
</div>

</body>
</html>
