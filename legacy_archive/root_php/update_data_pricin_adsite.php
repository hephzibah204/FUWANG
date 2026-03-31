<?php
// update_data.php
include_once("db_conn.php");
$id = $_POST['id'];
$column = $_POST['column'];
$value = $_POST['value'];

$query = "UPDATE dataplan SET $column = ? WHERE did = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('si', $value, $id);

if ($stmt->execute()) {
    echo 'Success';
} else {
    echo 'Error';
}

$stmt->close();
$conn->close();
?>