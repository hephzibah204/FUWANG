<?php 


$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$db   = getenv('DB_DATABASE') ?: 'gsoft_db';

$conn = mysqli_connect($host, $user, $pass, $db);
if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
