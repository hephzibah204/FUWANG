<?php
include_once("db_conn.php");

$funding_type = "Automatic Funding";
$fullname = "From 9PSB Bank";

// Verify if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit(json_encode(["message" => "Method not allowed"]));
}

// Retrieve the raw POST data
$payload = file_get_contents('php://input');

// Ensure the payload is not empty
if (empty($payload)) {
    http_response_code(400); // Bad Request
    exit(json_encode(["message" => "Empty payload received"]));
} 
$apiQuery = "SELECT payvessel_secret_key FROM api_center LIMIT 1";
    $apiResult = $conn->query($apiQuery);
    
    if ($apiResult->num_rows > 0) {
        $apiData = $apiResult->fetch_assoc();

        $secretKey = $apiData['payvessel_secret_key'];
    } else {
        echo json_encode(array("success" => false, "message" => "API credentials not found."));
        exit();
    }

// Retrieve Payvessel signature and IP address
$payvessel_signature = $_SERVER['HTTP_PAYVESSEL_HTTP_SIGNATURE'];
$ip_address = $_SERVER['REMOTE_ADDR']; // Use 'HTTP_X_FORWARDED_FOR' if needed
$trusted_ips = ["3.255.23.38", "162.246.254.36"];
$secret = $secretKey;

// Calculate HMAC hash
$hashkey = hash_hmac('sha512', $payload, $secret);

// Verify signature and IP address
if ($payvessel_signature !== $hashkey || !in_array($ip_address, $trusted_ips)) {
    http_response_code(403); // Forbidden
    exit(json_encode(["message" => "Permission denied, invalid hash or IP address"]));
}

// Decode JSON payload
$data = json_decode($payload, true);

// Check JSON decoding errors
if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    exit(json_encode(["message" => "Invalid JSON format"]));
}

// Extract necessary data
$transactionReference = $data['transaction']['reference'];
$settlementAmount = floatval($data['order']['settlement_amount']); 
$email = $data['customer']['email'];

// Function to get 9psb_amount from charges table
function get9psbAmount($conn) {
    $sql = "SELECT psb_amount FROM charges WHERE id = 1"; // Adjust the WHERE clause as needed
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Charge amount not found");
    }
    $row = $result->fetch_assoc();
    $stmt->close();

    return floatval($row['psb_amount']);
}

// Function to check if transaction already exists
function transactionExists($conn, $reference) {
    $sql = "SELECT id FROM payment_transactions WHERE reference = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    $result = $stmt->get_result();

    $exists = $result->num_rows > 0;

    $stmt->close();

    return $exists;
}

// Function to fund user wallet
function fundUserWallet($conn, $email, $amount, $reference, $description, $funding_type, $fullname) {
    // Start a transaction
    $conn->begin_transaction();

    try {
        // Check the current balance
        $sql = "SELECT user_balance FROM account_balance WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            // User not found
            throw new Exception("User not found");
        }
        $row = $result->fetch_assoc();
        $current_balance = floatval($row['user_balance']);

        // Update the balance
        $new_balance = $current_balance + $amount;
        $sql = "UPDATE account_balance SET user_balance = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ds", $new_balance, $email);
        $stmt->execute();

        // Log the transaction
        $sql = "INSERT INTO payment_transactions (reference, email, amount, description) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssds", $reference, $email, $amount, $description);
        $stmt->execute();

        // Insert into funding_history
        $sql = "INSERT INTO funding_history (funding_type, email, fullname, amount, date) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssd", $funding_type, $email, $fullname, $amount);
        $stmt->execute();

        // Commit the transaction
        $conn->commit();
    } catch (Exception $e) {
        // Rollback the transaction if something goes wrong
        $conn->rollback();
        echo json_encode(["message" => "Error: " . $e->getMessage()]);
        http_response_code(500);
        exit;
    } finally {
        $stmt->close();
    }
}

try {
    // Get the 9psb_amount
    $psb_amount = get9psbAmount($conn);

    // Deduct the 9psb_amount from the settlement amount
    $adjustedAmount = $settlementAmount - $psb_amount;

    if (!transactionExists($conn, $transactionReference)) {
        fundUserWallet($conn, $email, $adjustedAmount, $transactionReference, 'Funding Wallet', $funding_type, $fullname);
        http_response_code(200); // OK
        echo json_encode(["message" => "Successfully funded"]);
    } else {
        http_response_code(409); // Conflict
        echo json_encode(["message" => "Transaction already processed"]);
    }
} catch (Exception $e) {
    echo json_encode(["message" => "Error: " . $e->getMessage()]);
    http_response_code(500);
}

// Close database connection
$conn->close();
?>