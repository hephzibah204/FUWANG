<?php
session_start();
include_once("db_conn.php");

header('Content-Type: application/json');

// Function to sanitize input data
function sanitize_input($data) {
    // Trim whitespace from the beginning and end
    $data = trim($data);
    // Remove backslashes
    $data = stripslashes($data);
    // Convert special characters to HTML entities
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate input data and check for forbidden characters
function validate_input($data) {
    // Define forbidden characters (example: SQL special characters)
    $forbidden_characters = '/[\'"\\;]/';
    
    // Check if input contains forbidden characters
    if (preg_match($forbidden_characters, $data)) {
        return false;
    }
    return true;
}

// Check if the AJAX request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize form data
    $email = sanitize_input($_POST['email']);
    $account_number = sanitize_input($_POST['account_number']);
    $bank_name = sanitize_input($_POST['bank_name']);
    $amount = sanitize_input($_POST['amount']);
    $remark = sanitize_input($_POST['remark']);

    // Validate the sanitized data
    if (!validate_input($email) || !validate_input($account_number) || !validate_input($bank_name) || !validate_input($amount) || !validate_input($remark)) {
        echo json_encode(array('success' => false, 'message' => 'Input contains forbidden characters.'));
        exit;
    }

    // Prepare the SQL query with placeholders
    $stmt = $conn->prepare("INSERT INTO manual_proof_messages (email, account_number, bank_name, amount, remark) VALUES (?, ?, ?, ?, ?)");

    // Bind the parameters
    $stmt->bind_param("sssss", $email, $account_number, $bank_name, $amount, $remark);

    // Execute the query and check for success
    if ($stmt->execute()) {
        echo json_encode(array('success' => true, 'message' => 'Proof sent successfully!'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Error: ' . $stmt->error));
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(array('success' => false, 'message' => 'Invalid request method.'));
}

// Close the database connection
$conn->close();
?>