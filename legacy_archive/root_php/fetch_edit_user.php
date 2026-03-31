<?php
session_start();
include_once("db_conn.php");

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    // Sanitize input to prevent SQL injection
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM customers2 WHERE id = '$id'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
} else {
    echo json_encode(['error' => 'User ID not provided']);
}
?>
