<?php
session_start();
include_once("db_conn.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the POST request
    $plan_id = $_POST['plan_id'];
    $plan_type = $_POST['plan_type'];
    $plan_name = $_POST['plan_name'];
    $amount = $_POST['amount'];
    $reseller_id = $_POST['reseller_id'];
    $reseller_amount = $_POST['reseller_amount'];

    // Check if all required fields are present
    if (!empty($plan_id) && !empty($plan_type) && !empty($plan_name) && !empty($amount) && !empty($reseller_id) && !empty($reseller_amount)) {
        // Prepare and execute the SQL statement
        $query = "INSERT INTO resellers_prices (reseller_id, plan_id, reseller_amount) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iii", $reseller_id, $plan_id, $reseller_amount);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "Data inserted successfully!";
        } else {
            echo "Error inserting data: " . mysqli_error($conn);
        }

        // Close statement
        mysqli_stmt_close($stmt);
    } else {
        echo "All fields are required!";
    }

    // Close database connection
    mysqli_close($conn);
} else {
    echo "Invalid request!";
}
?>
