<?php
include_once("db_conn.php"); // Include your database connection file

$response = [];

if (isset($_POST['transaction_id'])) {
    $transaction_id = $_POST['transaction_id'];

    // Prepare the DELETE statement
    $sql = "DELETE FROM nin_history WHERE transaction_id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $transaction_id);

        // Execute the statement
        if ($stmt->execute()) {
            // Record deleted successfully
            $response['status'] = 'success';
            $response['message'] = 'Deleted successfully';
        } else {
            // Handle error
            $response['status'] = 'error';
            $response['message'] = 'Error deleting record: ' . $stmt->error;
        }

        $stmt->close();
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error preparing statement: ' . $conn->error;
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'No transaction ID provided.';
}

$conn->close();
echo json_encode($response);
?>