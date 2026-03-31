<?php
include_once("db_conn.php");
include_once("whois_admin.php");
// Fetch data
$sql = "
    SELECT 
        id, 
        email, 
        user_balance, 
        created_at
    FROM 
        account_balance
    
    ORDER BY 
        user_balance DESC, created_at DESC
";

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
            <h2>Balance history</h2>
        </center>
    </div>
    <br><br><br><br>
    <div class="container mt-5">
        
        
        <!-- Search Input -->
        <input type="text" id="searchInput" class="form-control search-input" placeholder="Search by Email">

        <!-- Table -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th><nobr>User Balance</th>
                    <th><nobr>Created At</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Format the date
                        $date = new DateTime($row["created_at"]);
                        echo "<tr data-date='" . $date->format('Y-m-d') . "'>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["email"] . "</td>";
                        echo "<td>₦" . $row["user_balance"] . "</td>";
                        echo "<td>" . $date->format('d-M-Y H:i') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            // Search filter
            $('#searchInput').on('input', function() {
                var searchValue = $(this).val().toLowerCase();
                $('#tableBody tr').each(function() {
                    var email = $(this).find('td:eq(1)').text().toLowerCase();
                    $(this).toggle(email.includes(searchValue));
                });
            });

      
    </script>
</body>
</html>

<?php
$conn->close();
?>