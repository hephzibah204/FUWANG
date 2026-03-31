<?php
include_once("db_conn.php");

$sql = "SELECT psb_amount FROM charges LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $psb_amount = $row['psb_amount'];
} else {
    $psb_amount = 0;
}


?>