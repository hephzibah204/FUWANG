<?php
 $data = [
                        'api_key' => $apiKey,
                        'trackingID' => $trackingID
                    ];

                    $ch = curl_init($tid_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json'
                    ]);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                      if (curl_errno($ch)) {
                        $error = 'cURL Error: ' . curl_error($ch);
                        curl_close($ch);

                        // Refund balance if API call fails
                        $refund_query = "UPDATE account_balance SET user_balance = ? WHERE email = ?";
                        $refund_stmt = $conn->prepare($refund_query);
                        $refund_stmt->bind_param("ds", $user_balance, $user_email);  // Restore original balance
                        $refund_stmt->execute();
                        $refund_stmt->close();

                        echo json_encode(['status' => false, 'message' => $error]);
                        exit;
                    }
curl_close($ch);