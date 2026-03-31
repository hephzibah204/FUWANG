<?php
session_start();
include_once("db_conn.php");

// Initialize the error array
$errors = array();

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if email and transaction_pin are set and not empty
    if (isset($_POST["email"]) && isset($_POST["transaction_pin"]) && !empty($_POST["email"]) && !empty($_POST["transaction_pin"])) {
        // Retrieve user details from the database using the email
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $transaction_pin = mysqli_real_escape_string($conn, $_POST['transaction_pin']);
        
        // Check if the email is stored in the session
        if (!isset($_SESSION["email"])) {
            $errors[] = "Session email not set.";
        } else {
            // Get the stored transaction PIN from the database based on the email
            $query = "SELECT transaction_pin FROM users WHERE email = '$email'";
            $result = mysqli_query($conn, $query);
            
            if ($result) {
                // Check if user exists
                if (mysqli_num_rows($result) == 1) {
                    $row = mysqli_fetch_assoc($result);
                    $storedTransactionPin = $row['transaction_pin'];
                    
                    // Compare the entered transaction PIN with the stored one
                    if ($transaction_pin == $storedTransactionPin) {
                        // Transaction PIN is valid
                        $response = array("valid" => true);
                    } else {
                        // Transaction PIN is invalid
                        $errors[] = "Incorrect Transaction PIN.";
                    }
                } else {
                    // User not found
                    $errors[] = "User not found.";
                }
            } else {
                // Database error
                $errors[] = "Database error: " . mysqli_error($conn);
            }
        }
    } else {
        // Invalid request
        $errors[] = "Invalid request. Missing email or transaction PIN.";
    }
} else {
    // Method not allowed
    $errors[] = "Method not allowed.";
}

// If there are errors, return them as JSON response
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(array("valid" => false, "errors" => $errors));
} else {
    echo json_encode($response);
}
?>