<?php
include_once("db_conn.php");
session_start();

// Sanitize session email (though it's already set by PHP, it's still a good practice)
$user_email = isset($_SESSION["email"]) ? filter_var($_SESSION["email"], FILTER_SANITIZE_EMAIL) : null;

if ($user_email) {
    // Use prepared statement for SQL query
    $sql = "SELECT user_email, transaction_id, response_data, created_at FROM nin_history WHERE user_email = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response_data = json_decode($row["response_data"], true);

            // Sanitize and validate the data
            $image = isset($response_data['response'][0]['photo']) ? filter_var($response_data['response'][0]['photo'], FILTER_SANITIZE_STRING) : null;
            $nin_fetched = isset($response_data['response'][0]['nin']) ? filter_var($response_data['response'][0]['nin'], FILTER_SANITIZE_STRING) : null;
            $first_name = isset($response_data['response'][0]['firstname']) ? filter_var($response_data['response'][0]['firstname'], FILTER_SANITIZE_STRING) : 'N/A';
            $middle_name = isset($response_data['response'][0]['middlename']) ? filter_var($response_data['response'][0]['middlename'], FILTER_SANITIZE_STRING) : 'N/A';
            $last_name = isset($response_data['response'][0]['surname']) ? filter_var($response_data['response'][0]['surname'], FILTER_SANITIZE_STRING) : 'N/A';
            $verification_type = "NIN VERIFICATION";  // Example; this could be dynamic
            
            if ($image) {
                // Sanitize base64 image data
                $image_data = base64_decode($image, true);

                if ($image_data === false) {
                    // Handle invalid base64 data (image not decoded properly)
                    echo "Invalid image data.";
                    continue;
                }

                // Ensure the image is stored in a safe location
                $file_path = 'images/' . basename($nin_fetched) . '.jpg';  // Use basename to avoid path traversal

                // Save the image to the file system
                if (!file_put_contents($file_path, $image_data)) {
                    echo "Failed to save image.";
                    continue;
                }

                // Safely use the file path in HTML
                echo "<div class='content'>";
                echo "<div class='user-info' onclick='toggleDropdown(\"$nin_fetched\")'>";
                echo '<img src="' . htmlspecialchars($file_path, ENT_QUOTES, 'UTF-8') . '" alt="User Photo" />';
                echo "<div>";
                echo "<h2>" . htmlspecialchars($first_name . ' ' . $middle_name . ' ' . $last_name, ENT_QUOTES, 'UTF-8') . "</h2>";
                echo "<div class='verification-details'>$verification_type<br>";
                echo date("l d, M Y H:i:s", strtotime($row["created_at"])) . "</div>";
                echo "</div></div>";

                // Dropdown content that shows up when clicked
                echo "<div class='dropdown' id='dropdown-$nin_fetched'>";
                echo "<a href='#' onclick='goToSlip(\"$nin_fetched\")'>Reprint Slip</a>";
                echo "<a href='#' onclick='goToDetails(\"$nin_fetched\")'>View Full NIN Details</a>";  // New option for full NIN details
                echo "</div></div>";
            } else {
                echo "<div class='content'></div>";
            }
        }
    } else {
        echo "<p style='text-align:center;'>No verification history found</p>";
    }
    $stmt->close();
}
$conn->close();
?>