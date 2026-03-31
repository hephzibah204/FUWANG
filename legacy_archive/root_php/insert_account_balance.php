<?php
include_once("db_conn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $additional_balance = $_POST['user_balance'];

    // Begin a transaction for atomicity
    $conn->begin_transaction();

    try {
        // SQL query to update user_balance by adding the additional balance
        $update_sql = "UPDATE account_balance SET user_balance = user_balance + '$additional_balance' WHERE email = '$email'";

        if ($conn->query($update_sql) !== TRUE) {
            throw new Exception("Error updating user balance: " . $conn->error);
        }

        // SQL query to insert into funding_history
        $insert_sql = "INSERT INTO funding_history (email, amount) VALUES ('$email', '$additional_balance')";

        if ($conn->query($insert_sql) !== TRUE) {
            throw new Exception("Error inserting into funding history: " . $conn->error);
        }

        // Commit the transaction if all queries succeed
        $conn->commit();
        echo "User balance updated successfully and inserted into funding history";
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        echo "Transaction failed: " . $e->getMessage();
    }

    $conn->close();
}
?>