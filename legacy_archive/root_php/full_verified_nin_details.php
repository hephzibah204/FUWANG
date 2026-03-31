<?php 
include_once("db_conn.php");

     $developer_id = isset($_SESSION['developerId']) ? $_SESSION['developerId'] : null;

// If $developer_id is null, stop further execution and show the message
if ($developer_id === null) {
    die("Sorry, you can't connect securely. Contact the owner at 08113910395.");
}

// Set a flag to conditionally block CSS
$block_css = !$developer_id;  // If there's no developer_id, block CSS
if (isset($_GET['nin'])) {
    // Trim and validate the NIN
    $nin = trim($_GET['nin']);
    
    // Ensure NIN is numeric and exactly 11 digits (modify if necessary)
    if (!preg_match('/^\d{11}$/', $nin)) {
        die("Invalid NIN format.");
    }

    // Sanitize the NIN for the database
    $nin = mysqli_real_escape_string($conn, $nin);

    ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="theme-color" content="#15gt44">
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1,minimum-scale=1,width=device-width,interactive-widget=resizes-content,initial-scale=1.0, user-scalable=no">
     <?php if (!$block_css): ?>
<meta name="theme-color"content="darkblue">
    <title>NIN verification</title>
       <link rel="icon" href="images/logo2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Ensure the body has no margins or padding */
        body {
            margin: 0;
            padding: 0;
        }

        .print-icons {
            position: fixed;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            right: 5%;
            bottom: 20px; /* Adjust this value as needed */
            font-size: 24px; /* Adjust the size of the icon as needed */
            color: black; /* Adjust the color of the icon as needed */
        }
   
              /* Hide the dropdown content by default */
  .dropdown-content {
            display: none;
            position: fixed;
            background-color: #f9f9f9;
            min-width: 160px;
            font-size: 10px;
            opacity: 1;
            border-radius: 10px;
            margin-top: 21%;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            right: 0; /* Position the dropdown content to the right */
        }
        .menu-container:hover .dropdown-content {
            display: block;
        }
        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }
        .header{
color:white;
justify-content: center;
text-align: center;
top:0;
position:fixed;
background-color: <?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?>;
width:100%;
}
     body {
           
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
            
        }  
         button {
            width: 50%;
            padding: 10px;
            background-color: <?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?>;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }  
            .content {
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            background-color: #fff;
            
        }
        .slip_div{
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding:10%;
            width:70%;
            justify-content: center;
            align-items: center;
             margin:5%;
            
            text-align: center;
        }
       
        </style> 
        
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
        <?php endif; ?>
    </head>
        <body>
          
       
          
          
            
                <div class="dropdown">
  <div class="menu-container">
      
<a href="available_verified_slip?nin=<?php echo $nin ?>">

     <i style="font-size:80px;color:<?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?>;border-radius:10px; background-color:darkblu;" class="fa fa-print print-icons"></i>
</a>
    <div class="dropdown-content">
          
    </div>
  </div>
</div>
       
<div class="header">
    <center>
            <h2 style="font-size:30px;">Verified history</h2>
            <br>
</div>
</div>
<br><br><br><br>


</div>
<?php

    // Prepare and execute the SQL query
    $sql = "SELECT response_data FROM nin_history WHERE response_data LIKE ?";
    $stmt = $conn->prepare($sql);
    $like_nin = '%"nin":"' . $nin . '"%';
    $stmt->bind_param("s", $like_nin);
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
            <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <style>
            body {
        
        </style>
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
        foreach ($response_array as $item) {
            foreach (['photo', 'signature'] as $key) {
                if (isset($item[$key])) {
                    $value = $item[$key];
                    
                    // Remove MIME type prefix and whitespace
                    $value = preg_replace('/\s+/', '', $value); // Remove whitespace
                    $value = preg_replace('/^data:image\/[^;]+;base64,/', '', $value); // Remove MIME type prefix
                    
                    // Determine the correct MIME type
                    $imageType = 'jpeg'; // Default type
                    if (strpos($item[$key], 'data:image/png;base64,') === 0) {
                        $imageType = 'png';
                    }
                    
                    // Create image source URL
                    $imageSrc = 'data:image/' . $imageType . ';base64,' . $value;

                    // Display the image with appropriate styles
                    echo '<tr><td>' . ucfirst($key) . '</td><td><img src="' . $imageSrc . '" alt="' . ucfirst($key) . '" style="max-width: 200px; max-height: 200px;" /></td></tr>';
                }
            }

            // Handle other fields in the response array
            foreach ($item as $key => $value) {
                if ($key !== 'photo' && $key !== 'signature') {
                    if (is_array($value)) {
                        $value = json_encode($value); // Convert nested arrays to JSON string
                    }
                    echo '<tr><td>' . ucfirst($key) . '</td><td>' . htmlspecialchars($value) . '</td></tr>';
                }
            }
        }

        echo '</tbody></table></div></body></html>';
    } else {
        echo 'No data found for the provided NIN.';
    }
}
?>