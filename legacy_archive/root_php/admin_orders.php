<?php
// Start session
session_start();

// Include the database connection
include_once("db_conn.php");
include_once("whois_admin.php");
// Fetch all data from the all_orders table
$sql = "SELECT * FROM all_orders ORDER BY create_date DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title> 
     <?php if (!$block_css): ?>
    <link rel="icon" href="images/logo2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
 <meta name="theme-color"content="darkblue">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" type="text/css" href="clients/style/_style.css">
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
    <div class="header">
        <center>Admin Orders</center>
    </div>

    <div class="container">
        <!-- Search Filters -->
        <input type="text" id="searchInput" class="form-control mb-3" placeholder="Search by Order Type or Transaction ID">
        <select id="statusFilter" class="form-control mb-3">
            <option value="">All Statuses</option>
            <option value="success">Success</option>
            <option value="failed">Failed</option>
        </select>

        <div class="mb-3">
            <label for="fromDate" class="form-label">From Date:</label>
            <input type="date" id="fromDate" class="form-control">
            <label for="toDate" class="form-label">To Date:</label>
            <input type="date" id="toDate" class="form-control">
        </div>

        <!-- Orders Table -->
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr> <?php endif; ?>
                
                    <th>ID</th>
                    <th>Order Type</th>
                    <th>Balance Before</th>
                    <th>Balance After</th>
                    <th>Transaction ID</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Format the balance columns to 2 decimal places
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["order_type"] . "</td>";
                        echo "<td>₦" . number_format($row["balance_before"], 2) . "</td>";
                        echo "<td>₦" . number_format($row["balance_after"], 2) . "</td>";
                        echo "<td>" . $row["transaction_id"] . "</td>";
                        echo "<td>" . $row["status"] . "</td>";
                        echo "<td>" . (new DateTime($row["create_date"]))->format('Y-m-d H:i:s') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>No orders found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            function filterTable() {
                var searchValue = $('#searchInput').val().toLowerCase();
                var selectedStatus = $('#statusFilter').val();
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();
                var tableRows = $('tbody tr');

                tableRows.each(function() {
                    var row = $(this);
                    var orderTypeCell = row.find('td:eq(1)').text().toLowerCase();
                    var transactionIdCell = row.find('td:eq(4)').text().toLowerCase();
                    var statusCell = row.find('td:eq(5)').text().toLowerCase();
                    var dateCell = new Date(row.find('td:eq(6)').text() + ' UTC');

                    // Check filters
                    var orderTypeMatch = orderTypeCell.includes(searchValue) || transactionIdCell.includes(searchValue);
                    var statusMatch = (selectedStatus === '' || statusCell.includes(selectedStatus));
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

                    if (orderTypeMatch && statusMatch && dateMatch) {
                        row.show();
                    } else {
                        row.hide();
                    }
                });
            }

            // Bind filter events
            $('#searchInput').on('input', filterTable);
            $('#statusFilter').on('change', filterTable);
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