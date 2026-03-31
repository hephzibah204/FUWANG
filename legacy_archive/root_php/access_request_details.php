<?php

    $apiQuery = "SELECT dataverify_api_key, dataverify_endpoint_bvn, dataverify_endpoint_nin, dataverify_endpoint_phone, dataverify_endpoint_tid FROM api_center LIMIT 1";
    $apiResult = $conn->query($apiQuery);
    
    if ($apiResult->num_rows > 0) {
        $apiData = $apiResult->fetch_assoc();
        $apiKey = $apiData['dataverify_api_key'];
        $apiUrl = $apiData['dataverify_endpoint_nin'];
        $bvn_url = $apiData['dataverify_endpoint_bvn'];
        $phone_url = $apiData['dataverify_endpoint_phone'];
        $tid_url = $apiData['dataverify_endpoint_tid'];
        
        
    } else {
        echo json_encode(array("success" => false, "message" => "API credentials not found."));
           $_SESSION['attempts']++;
        exit();
    } 