<!DOCTYPE html>
<html>
<head>
    <meta name="theme-color" content="#190F92">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Price list</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .hidden {
            display: none;
        }
        .button {
            background-color: darkblue;
            border-radius: 10px;
            border: 1px solid darkblue;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            transition-duration: 0.4s;
            cursor: pointer;
        }
        .button:hover {
            background-color: #45a049; /* Darker green */
        }
        .container_count {
            margin-bottom: 20px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            background-color: #f9f9f9;
        }
          .count {
            margin-bottom: 20px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            background-color: #f9f9f9;
        }
   .count1{
            margin-bottom: 20px;
            padding:10%;
            width:100%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            background-color: #f9f9f9;
        }
  
    </style>
</head>
<body>
<?php
include_once("db_conn.php");
include_once("bill_price_bar.php");
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    // Redirect to login page if not logged in
    header("Location: admin_login.php");
    exit();
}


// Fetch the current commission value
$query = "SELECT bill_commision FROM api_settings LIMIT 1";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $bill_commision = $row['bill_commision'];
} else {
    $bill_commision = ''; // Set a default value if no record is found
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_commission = mysqli_real_escape_string($conn, $_POST['bill_commision']);

    $check_query = "SELECT * FROM api_settings LIMIT 1";
    $check_result = mysqli_query($conn, $check_query);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        // Update the existing record
        $update_query = "UPDATE api_settings SET bill_commision = '$new_commission' WHERE id = 1";
        mysqli_query($conn, $update_query);
    } else {
        // Insert a new record
        $insert_query = "INSERT INTO api_settings (bill_commision) VALUES ('$new_commission')";
        mysqli_query($conn, $insert_query);
    }

    // Refresh the value after insert/update
    $bill_commision = $new_commission;
}
?>


    <div class="container mt-3">
        <br>
        <center>
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" style="display:one">
                    <a class="nav-link active" data-bs-toggle="tab" href="#home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#menu1">Price settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#menu2">History</a>
                </li>
            </ul>
        </center>

        <div class="tab-content">
            <div id="home" class="container tab-pane active"><br>
                <h3>Overview</h3>
                
                <?php
include_once("db_conn.php");

// Query to select data from bill_transactions_history table grouped by disco
$sql = "SELECT disco, SUM(amount) as total_amount, COUNT(*) as total_transactions, COUNT(DISTINCT email) as total_users FROM bill_transactions_history GROUP BY disco";
$result = mysqli_query($conn, $sql);

// Initialize an array to store the results
$disco_data = array();

// Check if the query was successful
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $disco_data[] = $row;
    }
} else {
    echo "Error: " . mysqli_error($conn);
}
?>

    
        
      <div class="count1">Total Overview is display based on Disco type for your customers Total transactions 
    <div class="count">
 
    <?php if (empty($disco_data)): ?>
        <div class="container_count">
            No transactions found.
        </div>
    <?php else: ?>
        <?php foreach ($disco_data as $data): ?>
            <div class="container">
                <strong><?php echo htmlspecialchars($data['disco']); ?> Disco</strong>
                <br>
                Total Amount: ₦<?php echo number_format($data['total_amount'], 2); ?>
                <br>
                Total Transactions: <?php echo $data['total_transactions']; ?>
                <br>
                Total Users: <?php echo $data['total_users']; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
              </div>
            </div>

            <div id="menu1" class="container tab-pane fade"><br>
      
                <div id="function1">
                 <form method="POST" style="box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1); width: 100%; text-align: center;">
                        <center>
                          
                        <h5 style="color: darkblue">Set Electricity Commission</h5>
                        <small>
                          Here you can set the commission you want get for each customer purchase electricity. The current commission is ₦<?php echo $bill_commision;?></small>
                        <hr>
                        <img src="/vtusite/images/electric1.jpg" width="80%">
                        <br>
                        <input type="text" name="bill_commision" style="width: 80%" value="<?php echo $bill_commision; ?>">
                        <br>
                        <button type="submit" class="button">Update</button>
             </form>

                    <br><br>
                </div>
</div>
       
            <div id="menu2" class="container tab-pane fade">
                <?php include_once("admin_bill_history.php"); ?>
            </div>
        </div>
    </div>

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
</body>
</html>