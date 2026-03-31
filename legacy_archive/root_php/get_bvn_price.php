<?php
include_once("db_conn.php");

$sql = "SELECT bvn_by_bvn FROM verification_price LIMIT 1";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die(json_encode(['error' => 'Error preparing statement: ' . $conn->error]));
}

$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die(json_encode(['error' => 'Error executing query: ' . $stmt->error]));
}

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['value' => '₦' . $row['bvn_by_bvn']]);

} else {
    echo json_encode(['value' => '0']); // Default value if no rows found
}

$stmt->close();
$conn->close();
?>
