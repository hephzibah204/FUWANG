<?php
session_start();
include_once("db_conn.php");

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

      </style>  
      </head>
      <body>

<?php  include_once("reseller_bar.php");?>
     <br><br><br>

<div>
        <center><strong style="color:darkblue;font-size:25px">Customers Prices List<br><center></strong></div>
    
    <div class="menu">
        <center>
            <input style="width:80%" class="form-control" id="myInput" type="text" placeholder="Search available plans....">

  <br>


<div class="menu">
<center>

<?php
// Get reseller_id from session
$reseller_id = $_SESSION['reseller_id'] ?? null;

// Check if reseller_id is set
if ($reseller_id !== null) {
    // Query to fetch data from resellers_price_list for the specific reseller_id
    $query ="SELECT * FROM resellers_price WHERE reseller_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    // Check for SQL errors
    if (!$stmt) {
        die("SQL Error: " . mysqli_error($conn));
    }

    // Bind parameter (reseller_id is likely a string, so use 's')
    mysqli_stmt_bind_param($stmt, "s", $reseller_id);

    // Execute statement
    if (mysqli_stmt_execute($stmt)) {
        // Get result
        $result = mysqli_stmt_get_result($stmt);

        // Check if there are any results
        if (mysqli_num_rows($result) > 0) {
            // Display the table header
            ?>
            <table class="table table-bordered table-striped">
            <thead>
            <tr>
            <th>Plan ID</th>
            <th style="color:black;opacity:0.8">Network</th>
            <th style="color:black;opacity:0.8">Plan Type</th>
            <th style="color:black;opacity:0.8">Plan Name</th>
            <th style="color:black;opacity:0.8">Resellers price</th>
            <th style="color:black;opacity:0.8">Users price</th>
            <th style="color:black;opacity:0.8">Validate</th>
            <th style="color:black;opacity:0.8">Delete</th>
            </tr>
            </thead>
            <tbody>
            <?php
            // Fetch and display each row
            while ($row = mysqli_fetch_assoc($result)) {
                ?>
                <tr>
                <td><?php echo $row["plan_id"]; ?></td>
                <td><?php echo $row["network"]; ?></td>
                <td><?php echo $row["plan_type"]; ?></td>
                <td><?php echo $row["data_plan"]; ?></td>
                <td><?php echo $row["amount"]; ?></td>
                <td><?php echo $row["reseller_amount"]; ?></td>
                <td><?php echo $row["validate"]; ?></td>
                <td>
                    <form action="#" method="post">
                        <input type="hidden" name="delete" value="<?php echo $row['plan_id']; ?>">
                        <button type="submit" name="del" class="fa fa-trash-o"></button>
                    </form>
                </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
            </table>
            <?php
        } else {
            // No service found
            echo "No record found.";
        }
    } else {
        echo "Error executing statement: " . mysqli_stmt_error($stmt);
    }

    // Close statement
    mysqli_stmt_close($stmt);
} else {
    // Reseller ID not set
    echo "Reseller ID not found.";
}

// Close database connection
mysqli_close($conn);
?>

<br><br><br>

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
</body>
</html>


