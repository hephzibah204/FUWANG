<?php
include_once("db_conn.php");
$apiQuery = "SELECT paypoint_secret_key FROM paypoint_details LIMIT 1";
    $apiResult = $conn->query($apiQuery);
    
    if ($apiResult->num_rows > 0) {
        $apiData = $apiResult->fetch_assoc();

        $secretKey = $apiData['paypoint_secret_key'];
    } else {
        echo json_encode(array("success" => false, "message" => "API credentials not found."));
        exit();
    }
// Step 1: Read the raw POST data from the request body
$inputData = file_get_contents('php://input');

// Step 2: Get the signature from the headers
$signatureHeader = $_SERVER['HTTP_PAYMENTPOINT_SIGNATURE'] ?? '';

// Step 3: Calculate the expected signature using HMAC-SHA256
$calculatedSignature = hash_hmac('sha256', $inputData, $secretKey);

// Step 4: Verify if the calculated signature matches the signature from the header
if (!hash_equals($calculatedSignature, $signatureHeader)) {
    http_response_code(403); // Forbidden
    echo json_encode(["message" => "Invalid signature."]);
    exit;
}

// Step 5: Decode the JSON payload
$webhookData = json_decode($inputData, true);

// Step 6: Ensure the data was successfully decoded
if ($webhookData === null) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Invalid JSON data received."]);
    exit;
}

// Step 7: Extract relevant data from the decoded webhook
$transactionId = $webhookData['transaction_id'] ?? null;
$amountPaid = $webhookData['amount_paid'] ?? null;
$settlementAmount = $webhookData['settlement_amount'] ?? null;
$status = $webhookData['transaction_status'] ?? null;
$customerEmail = $webhookData['customer']['email'] ?? null;
$description = $webhookData['description'] ?? "Payment received";
$deduction = $settlementAmount * 0.01; // 1% fee deduction
$adjustedAmount = $settlementAmount - $deduction;

// Ensure all required fields are present
if (!$transactionId || !$amountPaid || !$settlementAmount || !$status || !$customerEmail) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Missing required data."]);
    exit;
}

// Step 8: Process the transaction (check if already exists and fund wallet)

// Function to check if the transaction already exists
function transactionExists($conn, $transactionId) {
    $sql = "SELECT id FROM payment_transactions WHERE reference = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $transactionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Function to fund the user's wallet
function fundWallet($conn, $email, $amount, $transactionId, $description) {
    $conn->begin_transaction();

    try {
        // Get current balance
        $sql = "SELECT user_balance FROM account_balance WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("User not found");
        }

        $row = $result->fetch_assoc();
        $currentBalance = floatval($row['user_balance']);
        $newBalance = $currentBalance + $amount;

        // Update balance
        $sql = "UPDATE account_balance SET user_balance = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ds", $newBalance, $email);
        $stmt->execute();

        // Insert into funding_history
        $fundingType = 'Automatic Funding';
        $fullname = "palmpay"; // Fetch or use a default value for fullname
        $sql = "INSERT INTO funding_history (funding_type, email, fullname, amount, date) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssd", $fundingType, $email, $fullname, $amount);
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert into funding_history: " . $stmt->error);
        }

        // Log transaction
        $sql = "INSERT INTO payment_transactions (reference, email, amount, description) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssds", $transactionId, $email, $amount, $description);
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert into payment_transactions: " . $stmt->error);
        }

        // Commit transaction
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["message" => "Error: " . $e->getMessage()]);
        http_response_code(500);
        exit;
    } finally {
        $stmt->close();
    }
}

try {
    // Check if the transaction already exists
    if (transactionExists($conn, $transactionId)) {
        http_response_code(409); // Conflict
        echo json_encode(["message" => "Transaction already processed."]);
        exit;
    }

    // Fund the wallet
    fundWallet($conn, $customerEmail, $adjustedAmount, $transactionId, $description);

    http_response_code(200); // OK
    echo json_encode(["message" => "Wallet funded successfully."]);
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Error: " . $e->getMessage()]);
}

// Step 9: Close the database connection
$conn->close();
?>