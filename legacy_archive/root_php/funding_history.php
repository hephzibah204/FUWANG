<?php
// Start session
session_start();

// Database connection
include_once("db_conn.php");

// Check if the session is valid (e.g., if a user session variable exists)
if (!isset($_SESSION['email'])) {
    // If no session, redirect to user_logout.php
    header("Location: user_login");
    exit();
}

// Fetch user's email from session
$user_email = $_SESSION['email']; // Ensure this session variable is set

// Fetch data from funding_history table for the specific user
$sql = "SELECT * FROM funding_history WHERE email = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Orders</title> 
     <?php if (!$block_css): ?>
    <link rel="icon" href="images/logo2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
 <meta name="theme-color"content="darkblue">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" type="text/css" href="clients/style/data_history_style.css">
 <link rel="stylesheet" href="https://sadeeqdata.com.ng/assets/css/order_style.min.css">
   
    

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
  border-color: #092C9F transparent #092C9F transparent; /* Spinner color pattern */
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
  }, 60000);
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

</script>
<script>
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
    <div class="header">
        <center>Funding History</center>
    </div>
    
    <div class="container">
        <select id="fundingTypeFilter" class="form-control mb-3">
            <option value="">All Funding Types</option>
            <option value="Manual Funding">Manual Funding</option>
            <option value="Automatic Funding">Automatic Funding</option>
        </select>

        <div class="mb-3">
            <label for="fromDate" class="form-label">From Date:</label>
            <input type="date" id="fromDate" class="form-control">
            <label for="toDate" class="form-label">To Date:</label>
            <input type="date" id="toDate" class="form-control">
        </div>

        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Funding Type</th>
                    <th>Amount</th>
                    <th>Email</th>
                    <th>From</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody> 
            <?php endif; ?>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Format the date for display
                        $date = new DateTime($row["date"]);
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["id"], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row["funding_type"], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>₦" . htmlspecialchars($row["amount"], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row["email"], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row["fullname"], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . $date->format('Y-m-d H:i:s') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            function filterTable() {
                var searchValue = $('#searchInput').val().toLowerCase();
                var selectedType = $('#fundingTypeFilter').val();
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();
                var tableRows = $('tbody tr');

                tableRows.each(function() {
                    var row = $(this);
                    var emailCell = row.find('td:eq(3)').text().toLowerCase();
                    var typeCell = row.find('td:eq(1)').text();
                    var dateCell = new Date(row.find('td:eq(5)').text() + ' UTC');

                    // Check filters
                    var emailMatch = emailCell.includes(searchValue);
                    var typeMatch = (selectedType === '' || typeCell === selectedType);
                    var dateMatch = true;

                    // Handle date filtering
                    if (fromDate) {
                        var fromDateObj = new Date(fromDate + 'T00:00:00Z');
                        dateMatch = dateCell >= fromDateObj;
                    }
                    if (toDate) {
                        var toDateObj = new Date(toDate + 'T23:59:59Z');
                        dateMatch = dateMatch && (dateCell <= toDateObj);
                    }

                    if (emailMatch && typeMatch && dateMatch) {
                        row.show();
                    } else {
                        row.hide();
                    }
                });
            }

            $('#searchInput').on('input', filterTable);
            $('#fundingTypeFilter').on('change', filterTable);
            $('#fromDate').on('change', filterTable);
            $('#toDate').on('change', filterTable);
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>