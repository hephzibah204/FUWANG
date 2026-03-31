<?php 

if (isset($_SESSION["email"])) {
    $user_email = $_SESSION["email"];
    
    // SQL query to fetch all response_data from seamfix_bvn_history
    $sql = "SELECT user_email, transaction_id, response_data, created_at FROM bvn_history WHERE user_email = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response_data = json_decode($row["response_data"], true);
            $image = isset($response_data['response']['photoId']) ? $response_data['response']['photoId'] : null;
            $bvn_fetched = isset($response_data['searchParameter']) ? $response_data['searchParameter'] : null;
            $first_name = isset($response_data['response']['first_name']) ? $response_data['response']['first_name'] : 'N/A';
            $middle_name = isset($response_data['response']['middle_name']) ? $response_data['response']['middle_name'] : ''; // Add middle_name
            $last_name = isset($response_data['response']['last_name']) ? $response_data['response']['last_name'] : 'N/A';
            $verification_type = "BVN VERIFICATION";  // Dynamic for BVN

            if ($image) {
                // Decode base64 image and save to a file
                $image_data = base64_decode($image);
                $file_path = 'images/' . $bvn_fetched . '.jpg';
                file_put_contents($file_path, $image_data);

                // Display the image and verification details
                echo "<div class='content'>";
                echo "<div class='user-info' onclick='toggleDropdown(\"$bvn_fetched\")'>";
                echo '<img src="' . $file_path . '" alt="User Photo" />';
                echo "<div>";
                echo "<h2>$first_name $middle_name $last_name</h2>";  // Display middle_name
                echo "<div class='verification-details'>$verification_type<br>";
                echo date("l d, M Y H:i:s", strtotime($row["created_at"])) . "</div>";
                echo "</div></div>";

                // Dropdown with actions
                echo "<div class='dropdown' id='dropdown-$bvn_fetched'>";
                echo "<a href='#' onclick='goToSlip(\"$bvn_fetched\")'>Reprint Slip</a>";
                echo "<a href='#' onclick='goToDetails(\"$bvn_fetched\")'>View Full BVN Details</a>";
                echo "</div></div>";
            } else {
                echo "<div class='content'><p></p></div>";
            }
        }
    } else {
        echo "<p style='text-align:center;'>No BVN verification history found</p>";
    }
    $stmt->close();
}
$conn->close();