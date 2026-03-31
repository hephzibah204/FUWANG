<?php
include_once("db_conn.php");
session_start();

if (isset($_GET['nin'])) {
    $nin = $_GET['nin'];
    $apiQuery = "SELECT dataverify_api_key, dataverify_endpoint_premium_slip FROM api_center LIMIT 1";
    $apiResult = $conn->query($apiQuery);

    if ($apiResult->num_rows > 0) {
        $apiData = $apiResult->fetch_assoc();
        $apiKey = $apiData['dataverify_api_key'];
        $url = $apiData['dataverify_endpoint_premium_slip'];
    } else {
        echo json_encode(array("success" => false, "message" => "API credentials not found."));
        $_SESSION['attempts']++;
        exit();
    }

    // Sanitize NIN
    $nin = filter_var($nin, FILTER_SANITIZE_STRING);

    if (empty($nin)) {
        echo json_encode(array("success" => false, "message" => "Error: NIN is empty."));
        exit();
    }

    if (isset($_SESSION["email"])) {
        $user_email = $_SESSION["email"];

        // Fetch the user's balance
        $stmt = $conn->prepare("SELECT user_balance FROM account_balance WHERE email = ?");
        $stmt->bind_param("s", $user_email);
        if ($stmt->execute()) {
            $stmt->bind_result($user_balance);
            $stmt->fetch();
            $stmt->close();
        } else {
            echo json_encode(array("success" => false, "message" => "Error: Unable to fetch user balance."));
            exit();
        }

        // Fetch the NIN premium slip price
        $stmt = $conn->prepare("SELECT nin_premium_slip_price FROM id_verification_price LIMIT 1");
        if ($stmt->execute()) {
            $stmt->bind_result($nin_premium_slip_price);
            $stmt->fetch();
            $stmt->close();
        } else {
            echo json_encode(array("success" => false, "message" => "Error: Unable to fetch NIN premium slip price."));
            exit();
        }

        if ($user_balance >= $nin_premium_slip_price) {
            $new_balance = $user_balance - $nin_premium_slip_price;

            // Update the user's balance
            $stmt = $conn->prepare("UPDATE account_balance SET user_balance = ? WHERE email = ?");
            $stmt->bind_param("ds", $new_balance, $user_email);
            if (!$stmt->execute()) {
                echo json_encode(array("success" => false, "message" => "Error: Unable to update user balance."));
                exit();
            }

            // Prepare API request
            $data = [
                'api_key' => $apiKey,
                'nin' => $nin
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                echo json_encode(array("success" => false, "message" => "cURL Error: " . curl_error($ch)));
                curl_close($ch);
                exit();
            }

            curl_close($ch);
            $apiResponse = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(array("success" => false, "message" => "Error decoding JSON response."));
                exit();
            }

            if (isset($apiResponse['status']) && $apiResponse['status'] === 'success') {
                // File name and path
    $fileName = $nin . '_' . time() . '.pdf';
                $filePath = "pdf/" . $fileName;

                if (isset($apiResponse['pdf_base64'])) {
                    // Save the file
                    file_put_contents($filePath, base64_decode($apiResponse['pdf_base64']));
                    $pdfUrl = "" . $filePath;

                    // Transaction success
                    $status = "success";
                    $balance_before = $user_balance;
                    $balance_after = $new_balance;
                    $transaction_id = strtoupper(uniqid('GSOFT'));

                    $insertOrderQuery = "INSERT INTO all_orders (user_email, order_type, balance_before, balance_after, transaction_id, status) 
                                         VALUES (?, 'Premium Slip', ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insertOrderQuery);
                    $stmt->bind_param("sddss", $user_email, $balance_before, $balance_after, $transaction_id, $status);
                    $stmt->execute();
                    $stmt->close();

                    echo json_encode(array(
                        "success" => true,
                        "message" => "PDF saved successfully.",
                        "pdf_url" => $pdfUrl,
                        "file_name" => $fileName,
                        "new_balance" => number_format($new_balance, 2)
                    ));
                    exit();
                } else {
                    echo json_encode(array("success" => false, "message" => "PDF data not available."));
                    exit();
                }
            } else {
                echo json_encode(array("success" => false, "message" => $apiResponse['message'] ?? "API error."));
                exit();
            }
        } else {
            echo json_encode(array("success" => false, "message" => "Insufficient balance."));
            exit();
        }
    } else {
        echo json_encode(array("success" => false, "message" => "Please log in to continue."));
        exit();
    }
} else {
    echo json_encode(array("success" => false, "message" => "No NIN provided."));
    exit();
}
?>