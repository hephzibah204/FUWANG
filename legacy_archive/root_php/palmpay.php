<?php
session_start();
include_once('db_conn.php');

if (isset($_SESSION["email"])) {
    // Get the user's email from the session
    $user_email = $_SESSION["email"];
  
    // Fetch user data from the database
    $query = "SELECT number, fullname FROM customers1 WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $user_email);
    $stmt->execute();
    $stmt->bind_result($number, $fullname);
    $stmt->fetch();
    $stmt->close();

    // Store fetched data in the session
    $_SESSION['number'] = $number;
    $_SESSION['fullname'] = $fullname;
} else {
    echo "User is not logged in.";
    exit();
}

// Query to get API details
$apiQuery = "SELECT paypoint_api_key, paypoint_secret_key, paypoint_businessid, paypoint_endpoint FROM paypoint_details LIMIT 1";
$apiResult = $conn->query($apiQuery);

if ($apiResult->num_rows > 0) {
    $apiData = $apiResult->fetch_assoc();
    $apiKey = $apiData['paypoint_api_key'];
    $secretKey = $apiData['paypoint_secret_key'];
    $url = $apiData['paypoint_endpoint'];
    $businessid = $apiData['paypoint_businessid'];
} else {
    echo "API credentials not found.";
    exit();
}

// PalmPay Headers
$palmpay_headers = [
    "Authorization: Bearer $secretKey", // Include the API key in the Authorization header
    "Content-Type: application/json",
    "api-key: $apiKey" // Make sure to use the correct API key here
];

// PalmPay Endpoint
$palmpay_url = $url;

// Request Body
$palmpay_requestBody = [
    "email" => $user_email,
    "name" => $fullname,
    "phoneNumber" => $number,
    "bankCode" => ["20946"],
    "businessId" => $businessid
];

// Convert request body to JSON
$palmpay_jsonRequestBody = json_encode($palmpay_requestBody);

// Initialize cURL
$palmpay_ch = curl_init($palmpay_url);

// Set cURL options
curl_setopt($palmpay_ch, CURLOPT_HTTPHEADER, $palmpay_headers);
curl_setopt($palmpay_ch, CURLOPT_POST, true);
curl_setopt($palmpay_ch, CURLOPT_POSTFIELDS, $palmpay_jsonRequestBody);
curl_setopt($palmpay_ch, CURLOPT_RETURNTRANSFER, true);
// Ensure SSL verification is enabled (for security reasons)
curl_setopt($palmpay_ch, CURLOPT_SSL_VERIFYHOST, 2); 
curl_setopt($palmpay_ch, CURLOPT_SSL_VERIFYPEER, 1);

// Execute cURL request
$palmpay_response = curl_exec($palmpay_ch);

// Check for cURL errors
if (curl_errno($palmpay_ch)) {
    echo "cURL Error: " . curl_error($palmpay_ch);
    curl_close($palmpay_ch);
    exit;
}

// Close cURL session
curl_close($palmpay_ch);

// Decode JSON response
$palmpay_responseData = json_decode($palmpay_response, true);

// Handle the response
if ($palmpay_responseData) {
    if (isset($palmpay_responseData['status']) && $palmpay_responseData['status'] === "success") {
        // Extract account details
        $accountNumber = $palmpay_responseData['bankAccounts'][0]['accountNumber'];
        $reservedAccountId = $palmpay_responseData['bankAccounts'][0]['Reserved_Account_Id'];

        // Check if the user already exists in the bank_details table
        $checkQuery = "SELECT id FROM bank_details WHERE email = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // If user exists, update the palmpay account
            $updateQuery = "UPDATE bank_details SET palmpay = ? WHERE email = ?";
            $stmt->close(); // Close the previous statement

            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ss", $accountNumber, $user_email);

            if ($stmt->execute()) {
                // Success message with animations
                echo '<div class="congratulations">';
                echo '<i class="fa fa-check-circle"></i><br>';
                echo '<h2>Congratulations, ' . $fullname . '!</h2>';
                echo '<p>Your account number has been successfully generated.</p>';
                echo '<div class="balloon"></div>';
                echo '<div class="balloon"></div>';
                echo '<div class="balloon"></div>';
                echo '<div class="balloon"></div>';
                echo '</div>';

                // Redirect after 1 second
                echo '<script>
                        setTimeout(function() {
                            window.location.href = "dashboard.php"; // Replace with your target page
                        }, 1000); // 1 second
                    </script>';
            } else {
                echo "Error updating data";
            }
        } else {
            // If user doesn't exist, insert a new record
            $insertQuery = "INSERT INTO bank_details (email, palmpay) VALUES (?, ?)";
            $stmt->close(); // Close the previous statement

            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ss", $user_email, $accountNumber);

            if ($stmt->execute()) {
                // Success message with animations
                echo '<div class="congratulations">';
                echo '<i class="fa fa-check-circle"></i><br>';
                echo '<h2>Congratulations, ' . $fullname . '!</h2>';
                echo '<p>Your account number has been successfully generated.</p>';
                echo '<div class="balloon"></div>';
                echo '<div class="balloon"></div>';
                echo '<div class="balloon"></div>';
                echo '<div class="balloon"></div>';
                echo '</div>';

                // Redirect after 1 second
                echo '<script>
                        setTimeout(function() {
                            window.location.href = "dashboard.php"; // Replace with your target page
                        }, 1000); // 1 second
                    </script>';
            } else {
                echo "Error inserting data";
            }
        }

        $stmt->close();
    } else {
        echo "API Error: " . $palmpay_responseData['message'] . "<br>";
        if (!empty($palmpay_responseData['errors'])) {
            echo "Errors: " . json_encode($palmpay_responseData['errors']);
        }
    }
} else {
    echo "Empty or invalid response from API.<br>";
    echo "Raw Response: " . $palmpay_response;
}

// Close database connection
$conn->close();
?>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Add some CSS for the animations and design -->
<style>
/* Global styling */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    text-align: center;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background: linear-gradient(45deg, #ff6ec7, #ffcc00, #ff007f);
    background-size: 400% 400%;
    animation: gradientAnimation 10s ease infinite;
}

@keyframes gradientAnimation {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.congratulations {
    padding: 30px;
    background-color: rgba(255, 255, 255, 0.8);
    border-radius: 8px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    max-width: 500px;
    margin: 20px;
    position: relative;
}

.congratulations i {
    font-size: 60px;
    color: #4CAF50;
    animation: bounce 1s infinite;
}

h2 {
    font-size: 28px;
    margin-top: 10px;
    color: #4CAF50;
}

p {
    font-size: 18px;
    color: #333;
}

.balloon {
    width: 50px;
    height: 70px;
    background-color: #ff007f;
    border-radius: 50%;
    position: absolute;
    bottom: -20px;
    left: 50%;
    margin-left: -25px;
    animation: balloonAnimation 4s ease-in infinite;
}

@keyframes balloonAnimation {
    0% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-300px);
    }
    100% {
        transform: translateY(0);
    }
}

@keyframes bounce {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

/* Responsive styles */
@media (max-width: 768px) {
    .congratulations {
        max-width: 90%;
        padding: 20px;
    }

    .congratulations i {
        font-size: 50px;
    }

    h2 {
        font-size: 22px;
    }

    p {
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .congratulations {
        padding: 15px;
        max-width: 95%;
    }
.congratulations i {
        font-size: 40px;
    }

    h2 {
        font-size: 20px;
    }

    p {
        font-size: 14px;
    }
}
</style>
    