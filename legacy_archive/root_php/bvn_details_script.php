<?php
if (isset($_GET['bvn'])) {
    $bvn = $_GET['bvn']; // Get the value from the URL parameter

    // Prepare and execute the SQL query
    $sql = "SELECT response_data FROM bvn_history WHERE response_data LIKE ?";
    $stmt = $conn->prepare($sql);
    $like_bvn = '%"bvn":"' . $bvn . '"%';
    $stmt->bind_param("s", $like_bvn);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Output data of the first matching row
        $row = $result->fetch_assoc();
        $response_data_json = $row["response_data"];
        $response_data = json_decode($response_data_json, true);

        // Extract 'response' array from the JSON
        $response_array = isset($response_data['response']) ? $response_data['response'] : [];

        // Include Bootstrap CSS
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Response Data</title>
            <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body {
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
        <div class="container mt-5">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>';

        // Iterate over each item in the response array
        foreach ($response_array as $key => $value) {
            if ($key === 'photoId') {
                // Handle the Base64 image
                $imageType = 'jpeg'; // Default type
                if (strpos($value, 'data:image/png;base64,') === 0) {
                    $imageType = 'png';
                }
                
                $imageSrc = 'data:image/' . $imageType . ';base64,' . preg_replace('/^data:image\/[^;]+;base64,/', '', $value);

                echo '<tr><td>' . ucfirst($key) . '</td><td><img src="' . $imageSrc . '" alt="' . ucfirst($key) . '" style="max-width: 200px; max-height: 200px;" /></td></tr>';
            } else {
                // Handle other fields
                if (is_array($value)) {
                    $value = json_encode($value); // Convert nested arrays to JSON string
                }
                echo '<tr><td>' . ucfirst($key) . '</td><td>' . htmlspecialchars($value) . '</td></tr>';
            }
        }

        echo '</tbody></table></div></body></html>';
    } else {
        echo 'No data found for the provided BVN.';
    }
} else {
    echo 'No BVN parameter provided.';
}