<?php
include_once("db_conn.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#190F92">
    <title>NIN Verification History</title>
    <link rel="icon" href="images/logo2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }
        .header {
            background-color: #190F92;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 24px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .content {
            background-color: #fff;
            margin: 20px auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .content:hover {
            background-color: #f1f1f1;
            transform: translateY(-3px);
        }
        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .user-info img {
            border-radius: 50%;
            width: 70px;
            height: 70px;
            margin-right: 15px;
            border: 2px solid #190F92;
        }
        .user-info h2 {
            margin: 0;
            font-size: 15px;
            color: #190F92;
        }
        .verification-details {
            font-size: 13px;
            color: #777;
        }
        .dropdown {
            display: none;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
            padding: 10px;
        }
        .dropdown a {
            text-decoration: none;
            color: #190F92;
            display: block;
            padding: 10px 5px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .dropdown a:hover {
            background-color: #f4f4f4;
        }
    </style>

    <script>
        function toggleDropdown(nin) {
            const dropdown = document.getElementById('dropdown-' + nin);
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        function goToSlip(nin) {
            window.location.href = 'available_verified_tid_slip.php?nin=' + nin;
        }

        function goToDetails(nin) {
            window.location.href = 'full_verified_tid_nin_details.php?nin=' + nin;
        }
    </script>
       <style>
.loader {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white background */
  display: none; /* Initially hidden */
  justify-content: center;
  align-items: center;
  z-index: 9999; /* Ensure it's on top of other elements */
}

/* CSS for the pulsating animation */
@keyframes rotate {
  0% {
    transform: rotate(0deg); /* Start rotation from 0 degrees */
  }
  100% {
    transform: rotate(360deg); /* End rotation at 360 degrees */
  }
}

.loader::after {
  content: '';
  display: block;
  width: 60px; /* Adjust the size of the spinner */
  height: 60px;
  border-radius: 50%;
  border: 6px solid red; /* Spinner color */
  border-color: <?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?> transparent <?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?> transparent; /* Spinner color pattern */
  animation: rotate 1.5s linear infinite; /* Rotate animation with infinite loop */
}
</style>
    
    <div class="loader" id="loader"></div>

<script>
  let loaderTimeout; // Variable to store the timeout reference

function showLoader() {
  var loader = document.getElementById('loader');
  loader.style.display = 'flex'; // Show the loader

  // Set a timeout to hide the loader after 30 seconds
  loaderTimeout = setTimeout(function () {
    hideLoader();
  }, 1000);
}

function hideLoader() {
  var loader = document.getElementById('loader');
  loader.style.display = 'none'; // Hide the loader

  // Clear the previous timeout if it exists
  clearTimeout(loaderTimeout);
}


  // Show the loader when the page starts loading
    showLoader();


    // Add an event listener for when the page has finished loading
    window.onload = function () {
      hideLoader(); // Hide the loader when the page has finished loading
    }

</script><script>
document.addEventListener('DOMContentLoaded', function () {
  // Get the form and submit button elements
  var form = document.querySelector('form');
  var submitButton = document.querySelector('button[type="submit"]');

  // Add a submit event listener to the form
  form.addEventListener('submit', function () {
    // Add the spinner HTML to the inner HTML of the submit button
    if (submitButton) {
      submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" style="color: #092C9F;"></span> <b style="color: #092C9F;">Processing...</b>';
    }

    // You can also perform additional actions here based on the form submission
    // For example, you can use AJAX to submit the form data asynchronously.
  });
});


document.addEventListener('DOMContentLoaded', function () {
  // Get all <a> elements on the page
  var aElements = document.querySelectorAll('a');

  // Add a click event listener to each <a> element
  aElements.forEach(function(aElement) {
    aElement.addEventListener('click', function (event) {
      // Check if the href attribute is empty or equals to "#"
      if (aElement.getAttribute('href') !== "" && aElement.getAttribute('href') !== "#") {
        showLoader();
      }
      // Prevent the default behavior of the link (e.g., following the href)

    });
  });
});
</script>
</head>
<body>
    <div class="header">Verified History</div> 
    <?php
include_once("db_conn.php");
session_start();

// Sanitize session email (though it's already set by PHP, it's still a good practice)
$user_email = isset($_SESSION["email"]) ? filter_var($_SESSION["email"], FILTER_SANITIZE_EMAIL) : null;

if ($user_email) {
    // Use prepared statement for SQL query
    $sql = "SELECT user_email, transaction_id, response_data, created_at FROM personalized_nin_history WHERE user_email = ? ORDER BY created_at DESC";
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
            $verification_type = "NIN Personalization";  // Example; this could be dynamic
            
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
</body>
</html>