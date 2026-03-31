<?php
include_once("db_conn.php");
include_once("whois_admin.php");
$apiQuery = "SELECT dataverify_api_key FROM api_center LIMIT 1";
    $apiResult = $conn->query($apiQuery);
    
    if ($apiResult->num_rows > 0) {
        $apiData = $apiResult->fetch_assoc();

        $apiKey = $apiData['dataverify_api_key'];
    } else {
        echo'no id found';
    }
$apiUrl = "http://dataverify.com.ng/developers/fetch_script_prices/index.php?api_key=" . $apiKey; // Replace with the correct URL of the PHP file

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects if any
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Disable SSL certificate verification (for testing)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL peer verification (for testing)

// Execute cURL request and get the response
$response = curl_exec($ch);

// Check for errors
if ($response === false) {
    echo "cURL Error: " . curl_error($ch);
} else {
    // Handle the response (it should be a JSON object)
    $data = json_decode($response, true);

    // Check if decoding was successful
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON Error: " . json_last_error_msg();
    } else {
        // Process and display the data in a styled container
        if (isset($data['balance'])) {
            $balance = $data['balance'];
            $prices = [
                'NIN1_price' => $data['NIN1_price'],
                'NIN2_price' => $data['NIN2_price'],
                'bvn1_price' => $data['bvn1_price'],
                'bvn2_price' => $data['bvn2_price'],
                'phone1_price' => $data['phone1_price'],
                'phone2_price' => $data['phone2_price'], 
                'slip price' => $data['slip_price'],
            ];
        } else {
            $errorMessage = isset($data['error']) ? $data['error'] : 'No data found';
        }
    }
}

// Close cURL session
curl_close($ch);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Dataverify Price Details</title>
      <meta name="theme-color" content="darkblue">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: white;
            color: #fff;
            margin:0;
    padding:0;
        }
        .container {
            max-width: 1200px;
        
            padding: 20px;
            background-color:white;
            border-radius: 10px;
            box-shadow: ;
        }
    
        .price-card {
            background-color:darkblue;
            margin: 10px 0;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .price-card h3 {
            margin: 0;
            color: white;
            font-size: 1.2em;
        }
        .price-card span {
            font-size: 1.5em;
            font-weight: bold;
            color: #00d2ff;
        }
        .error {
            color: #ff4d4d;
            font-size: 1.2em;
            text-align: center;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 1em;
            color: #fff;
        }
        .header {
            background-color:darkblue; 
            color: white;
            padding: 15px; 
            text-align: center; 
            font-size: 20px; 
            position: fixed; 
            top: 0; 
            width: 100%; 
            z-index: 1000;
            font-weight: bold;
        }
        
    </style>
</head>
<body>

<center>
    <div class="header">
   Dataverify api</center> 
    </div>
    <br><br>
<div class="container">
    <?php if (isset($errorMessage)): ?>
        <div class="error">
            <p><?= $errorMessage ?></p>
        </div>
    <?php else: ?>
        <div class="price-card">
            <h3>Balance</h3>
            <span>₦<?= number_format($balance, 2) ?></span>
        </div>
        <?php foreach ($prices as $priceName => $priceValue): ?>
            <div class="price-card">
                <h3><?= ucfirst(str_replace('_', ' ', $priceName)) ?> </h3>
                <span>₦<?= number_format($priceValue, 2) ?></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="footer">
        <p>&copy; 2024 Price Display. All rights reserved.</p>
    </div>
</div>

</body>
</html>