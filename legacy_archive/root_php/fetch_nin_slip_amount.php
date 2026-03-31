<?php
include_once("db_conn.php");

if (isset($_GET['slip_type'])) {
    $slipType = $_GET['slip_type'];

    // Fetch the price from the database based on the slip type
    $sql = "SELECT $slipType as price FROM verification_price LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
       echo json_encode(['price' => "₦" . $row['price']]);
    } else {
        echo json_encode(['error' => 'Price not found']);
    }

    $result->free();
    $conn->close();
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
