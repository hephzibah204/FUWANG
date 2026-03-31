<?php


                $data = [
                    'api_key' => $apiKey,
                    'bvn' => $bvn
                ];

                $ch = curl_init($bvn_url);
                $payload = json_encode($data);

                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if (curl_errno($ch)) {
                    error_log('cURL Error: ' . curl_error($ch));
                    curl_close($ch);
                    echo json_encode([
                        'status' => false,
                        'message' => 'cURL Error: ' . curl_error($ch)
                    ]);
                    exit;
                }

                curl_close($ch);

                error_log("Raw API Response: " . $response);