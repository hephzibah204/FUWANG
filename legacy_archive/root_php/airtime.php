<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://sadeeqdata.com.ng/script_request/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);  // Timeout after 30 seconds

$response = curl_exec($ch);

// Check for cURL errors or unsuccessful HTTP response
if(curl_errno($ch) || curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
    echo "<h1>Sorry, the requested page is temporarily unavailable.</h1>";
    echo "<p>Please try again later.</p>";
    // You could also include fallback HTML here or include another page
} else {
    // Display the external URL content
    echo $response;
}

curl_close($ch);
?>