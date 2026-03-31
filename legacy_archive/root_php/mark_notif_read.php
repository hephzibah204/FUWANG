<?php
session_start();
include_once("db_conn.php");

if (!isset($_SESSION['email'])) {
    die("Unauthorized");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $email = $_SESSION['email'];
    
    $stmt = $conn->prepare("UPDATE user_notifications SET is_read = 1 WHERE id = ? AND user_email = ?");
    $stmt->bind_param("is", $id, $email);
    $stmt->execute();
    $stmt->close();
}
?>