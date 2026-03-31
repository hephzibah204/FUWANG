<?php
include_once("db_conn.php");

// Check if form is submitted via AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $fullname = $_POST['fullname'];
    $date = $_POST['created_at'];
    $reseller_id = $_POST['reseller_id'];
    $amount = $_POST['amount'];
    $email = $_POST['email'];

    // Prepare user_balance and funding_type
    $user_balance = $amount;
    $funding_type = "Referral commission";

    // Insert data into referral_commision_rewards table
    $insert_sql = "INSERT INTO referral_commision_rewards (fullname, email, reseller_id, amount, date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);

    if ($stmt === false) {
        die('Error preparing statement for referral_commision_rewards: ' . $conn->error);
    }

    $stmt->bind_param("sssds", $fullname, $email, $reseller_id, $amount, $date);

    if ($stmt->execute()) {
        // Check if referral_id is the same as reseller_id in referral table and fetch email
        $select_email_sql = "SELECT email FROM referral WHERE referral_id = ?";
        $select_email_stmt = $conn->prepare($select_email_sql);

        if ($select_email_stmt === false) {
            die('Error preparing statement for fetching email: ' . $conn->error);
        }

        $select_email_stmt->bind_param("s", $reseller_id);
        $select_email_stmt->execute();
        $select_email_stmt->bind_result($email);
        
        if ($select_email_stmt->fetch()) {
            $select_email_stmt->close();

            // Debugging: Check the fetched email
            echo "Reseller referred this user is: $email<br>";

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

            $select_stmt->close();

            if ($has_balance_entry) {
                // User already has a balance entry, so update the balance
                $new_balance = $current_balance + $user_balance;
                $update_sql = "UPDATE account_balance SET user_balance = ? WHERE email = ?";
                $update_stmt = $conn->prepare($update_sql);

                if ($update_stmt === false) {
                    die('Error preparing statement for updating balance: ' . $conn->error);
                }

                $update_stmt->bind_param("ds", $new_balance, $email);

                if ($update_stmt->execute()) {
                    echo "And his balance updated ";
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

                $insert_balance_stmt->bind_param("sd", $email, $user_balance);

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

            $insert_funding_history_stmt->bind_param("sds", $email, $amount, $funding_type);

            if ($insert_funding_history_stmt->execute()) {
                echo "";
            } else {
                echo "Error in inserting funding history: " . $insert_funding_history_stmt->error;
            }

            $insert_funding_history_stmt->close();
        } else {
            echo "This user is not registered under any referral id.";
            // Debugging: Check the value of reseller_id being searched
            echo "";
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>