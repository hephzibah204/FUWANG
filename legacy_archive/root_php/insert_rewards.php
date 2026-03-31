<?php
include_once("db_conn.php");

// Check if form is submitted via AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $fullname = $_POST['fullname'];
    $number = $_POST['number'];
    $date = $_POST['created_time'];
    $email = $_POST['email'];
    $amount = $_POST['amount'];
    
    // Prepare user_balance and funding_type
    $user_balance = $amount;
    $funding_type = "Rewards balance";

    // Insert data into rewarded_users table
    $insert_sql = "INSERT INTO rewarded_users (fullname, email, amount, number, date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    
    if ($stmt === false) {
        die('Error preparing statement for rewarded_users: ' . $conn->error);
    }
    
    $stmt->bind_param("ssiss", $fullname, $email, $amount, $number, $date);

    if ($stmt->execute()) {
        // Check if the user already has a balance entry
        $select_sql = "SELECT user_balance FROM account_balance WHERE email = ?";
        $select_stmt = $conn->prepare($select_sql);
        
        if ($select_stmt === false) {
            die('Error preparing statement for account_balance: ' . $conn->error);
        }
        
        $select_stmt->bind_param("s", $email);
        $select_stmt->execute();
        $select_stmt->bind_result($current_balance);

        $has_balance_entry = false;

        if ($select_stmt->fetch()) {
            $has_balance_entry = true;
        }

        // Close the select statement
        $select_stmt->close();

        if ($has_balance_entry) {
            // User already has a balance entry, so update the balance
            $new_balance = $current_balance + $user_balance;
            $update_sql = "UPDATE account_balance SET user_balance = ? WHERE email = ?";
            $update_stmt = $conn->prepare($update_sql);
            
            if ($update_stmt === false) {
                die('Error preparing statement for updating balance: ' . $conn->error);
            }
            
            $update_stmt->bind_param("is", $new_balance, $email);

            if ($update_stmt->execute()) {
                echo "User rewarded and balance updated";
            } else {
                echo "Error in updating balance: " . $update_stmt->error;
            }
            $update_stmt->close();
        } else {
            // No balance entry for this user, so insert a new record
            $insert_balance_sql = "INSERT INTO account_balance (email, user_balance) VALUES (?, ?)";
            $insert_balance_stmt = $conn->prepare($insert_balance_sql);
            
            if ($insert_balance_stmt === false) {
                die('Error preparing statement for inserting balance: ' . $conn->error);
            }
            
            $insert_balance_stmt->bind_param("si", $email, $user_balance);

            if ($insert_balance_stmt->execute()) {
                echo "User rewarded";
            } else {
                echo "Error in inserting balance: " . $insert_balance_stmt->error;
            }
            $insert_balance_stmt->close();
        }

        // Insert into funding_history table
        $insert_funding_history_sql = "INSERT INTO funding_history (email, amount, funding_type) VALUES (?, ?, ?)";
        $insert_funding_history_stmt = $conn->prepare($insert_funding_history_sql);

        if ($insert_funding_history_stmt === false) {
            die('Error preparing statement for inserting funding history: ' . $conn->error);
        }

        $insert_funding_history_stmt->bind_param("sis", $email, $amount, $funding_type);

        if ($insert_funding_history_stmt->execute()) {
            echo "";
        } else {
            echo "Error in inserting funding history: " . $insert_funding_history_stmt->error;
        }

        $insert_funding_history_stmt->close();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>