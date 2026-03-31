<?php
// Database connection
include_once("db_conn.php");


// Fetch data from funding_history table
$sql = "SELECT * FROM funding_history ORDER BY date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link rel="icon" href="images/logo2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="theme-color" content="#190F92">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles.css"> <!-- Your stylesheet -->
    <script src="scripts.js" defer></script> <!-- Your script for copy functionality -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .table {
            margin-top: 20px;
        }
        .no-results {
            text-align: center;
            color: #777;
        }
        .dropdown {
            position: relative;
        }
        .dropdown-content {
            display: none;
            position: absolute;
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
        .header {
            color: white;
            width: 100%;
            top: 0;
            left: -0.1%;
            position: fixed;
            background-color: #190F92;
            padding: 10px 0;
        }
        .header h2 {
            font-size: 30px;
            margin: 0;
        }
        .header .dropdown-content a {
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="dropdown">
            <div class="menu-container">
                <i style="position: absolute; right: 4%; margin-top: 0; opacity: 0.9; z-index: 111; font-size: 30px; color: white;" class="fa fa-ellipsis-v" id="menu-icon"></i>
                <div class="dropdown-content">
                    <a href="admin_account_settings" class="fa fa-home"> Back To Home</a>
                    
                    <div style="border: 1px solid #f1f1f1;">
                        <a href="resellers_logout" class="fa fa-sign-out"> Logout</a>
                    </div>
                </div>
            </div>
        </div>
        <center>
            <h2>Funding History</h2>
        </center>
    </div>
    <br><br><br><br>
    <div class="container mt-5">
        
        
        <!-- Search Input -->
        <input type="text" id="searchInput" class="form-control search-input" placeholder="Search by Email">

        <!-- Table -->
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th><nobr>Funding Type</th>
                    <th>Amount</th>
                    <th>Email</th>
                    <th>From</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
               <?php
if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        // Create DateTime objects
        $date = new DateTime($row["date"]);
        $now = new DateTime();
        
        // Calculate the difference between now and the date
        $interval = $now->diff($date);
        
        // Determine date format based on the interval
        if ($interval->days == 0 && $interval->h == 0) {
            $formattedDate = "Today at " . $date->format('g:i a');
        } elseif ($interval->days == 1 && $interval->h == 0) {
            $formattedDate = "Yesterday at " . $date->format('g:i a');
        } elseif ($interval->y == 0 && $interval->m == 0) {
            $formattedDate = $date->format('d-F \a\t g:i a');
        } elseif ($interval->y > 0) {
            $formattedDate = $date->format('d-M-Y \a\t g:i a');
        }
        
        // Output table row
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["funding_type"] . "</td>";
        echo "<td>" . $row["amount"] . "</td>";
        echo "<td>" . $row["email"] . "</td>";
        echo "<td>" . $row["fullname"] . "</td>";
        echo "<td>" . $formattedDate . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>No records found</td></tr>";
}
?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery -->
    <script>
        document.getElementById('searchInput').addEventListener('input', function() {
            var searchValue = this.value.toLowerCase();
            var tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(function(row) {
                var emailCell = row.cells[3]; // Index of the email column
                var email = emailCell.textContent.toLowerCase();
                
                if (email.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>