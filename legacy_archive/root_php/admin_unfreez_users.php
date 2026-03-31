<?php
include_once("db_conn.php");

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];
$email = $data['email'];
$fullname = $data['fullname'];
$username = $data['username'];
$password = $data['password'];

// Generate hash for password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Insert data into referral table
$sql = "INSERT INTO referral (id, email, fullname, username, password) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('issss', $id, $email, $fullname, $username, $hashed_password);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$stmt->close();
$conn->close();
?>