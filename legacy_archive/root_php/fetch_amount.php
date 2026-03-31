<?php
include_once("db_conn.php");

// Get the data plan ID from the request
$dataPlanId = $_GET['dataPlanId'];


// Query the database to fetch the amount for the specified data plan ID and session reseller ID
$query = "SELECT amount FROM price_list WHERE plan_id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $dataPlanId);
$stmt->execute();
$result = $stmt->get_result();

// Check if a row is returned
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $amount = $row['amount'];
    echo '₦' .$amount; 
} else {
    echo "Amount not found";
}

// Close prepared statement and database connection
$stmt->close();
$conn->close();
?>
