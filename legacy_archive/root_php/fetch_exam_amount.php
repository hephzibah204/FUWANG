<?php
include_once("db_conn.php");

header('Content-Type: application/json');

$response = array('success' => false, 'amount' => 0, 'message' => '');

if (isset($_GET['exam'])) {
    $exam = $_GET['exam'];

    // Replace with the actual query to get the amount for the exam
    $query = "SELECT amount FROM exam_prices WHERE exam = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $exam);
    $stmt->execute();
    $stmt->bind_result($amount);
    if ($stmt->fetch()) {
        $response['success'] = true;
        $response['amount'] = $amount;
    } else {
        $response['message'] = "Exam not found or amount not available";
    }
    $stmt->close();
} else {
    $response['message'] = "No exam selected";
}

echo json_encode($response);
?>