<?php

session_start();
require 'db_conn.php'; // Include database connection

// Fetch user details and funded balance
$email = $_SESSION['email']; // Get the user's email from session

// Fetch user data from 'referral' table
$stmt = $conn->prepare("SELECT fullname, created_time, number FROM customers1 WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($fullname, $created_time, $number);
$stmt->fetch();
$stmt->close();

// Fetch the total funded balance from 'funding_history' table
$funded_balance_stmt = $conn->prepare("SELECT SUM(amount) FROM funding_history WHERE email = ?");
$funded_balance_stmt->bind_param("s", $email);
$funded_balance_stmt->execute();
$funded_balance_stmt->bind_result($total_funded_balance);
$funded_balance_stmt->fetch();
$funded_balance_stmt->close();

// Format total funded balance to a readable format
$total_funded_balance = $total_funded_balance ? "₦" . number_format($total_funded_balance, 2) : "
₦0.00";

// Format registration date
$formatted_registration_date = date("F j, Y", strtotime($created_time));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    
    <style>
        /* Global Styles */
        body {
            font-family: 'Poppins', sans-serif;
            
            margin: 0;
            padding: 0;
        }

        header {
            background-color: white;
            color: <?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?>;
            padding: 15px 0;
            text-align: center;
        }

        .navbar h2 {
            margin: 0;
            font-size: 24px;
        }

        /* Profile Page Styles */
        .profile-container {
            display: flex;
            justify-content: center;
            padding: 40px 15px;
        }

        .profile-card {
            background-color: white;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 20px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .profile-header h3 {
            font-size: 22px;
            margin: 0;
            color: #333;
        }

        .profile-header p {
            font-size: 16px;
            color: #777;
        }

        /* Profile Information Styles */
        .profile-info {
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .info-title {
            font-size: 16px;
            color: #555;
        }

        .info-detail {
            font-size: 16px;
            font-weight: 500;
            color: #333;
        }

        /* Button Styles */
        .update-profile-btn {
            background-color: <?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?>;
            color: white;
            padding: 12px 20px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .update-profile-btn:hover {
            background-color: #45a049;
        }

        /* Footer Styles */
        footer {
            text-align: center;
            background-color: #333;
            color: white;
            padding: 15px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        } 
        .modal{
    display: none; /* Hidden by default */
    position: fixed; 
    z-index: 1000; 
    left: 0;
    bottom: 0; /* Align to bottom */
    width: 100%; 
    background-color: ; 
    transition: all 0.3s ease-in-out;
    display:; /* Enable flexbox */
    justify-content: center; /* Center contents horizontally */
    align-items: flex-end; /* Align contents to the bottom */
}

.modal-content {
    background-color: #fff;
    padding: 25px;
    border: 1px solid #888;
    width: 90%; /* Responsive width */
    max-width: 500px; 
    border-radius: 10px;
    position: relative;
    animation: slideUp 0.5s;
    margin-bottom: 0; /* No space from the bottom */
}

@keyframes slideUp {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}

.modal-header{
    font-size: 20px;
    margin-bottom: 15px; /* Space below header */
    color: #333;
    text-align: center;
}

.modal-close {
    position: absolute;
    top: 1px;
    right: 10px;
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.modal-close:hover,
.modal-close:focus {
    color: #000;
    text-decoration: none;
}

.modal-btn {
    background-color: <?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?>;
    color: #fff;
    padding: 10px 18px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin: 10px 5px 0 5px;
    transition: background-color 0.3s ease;
}

.modal-btn:hover {
    background-color: #003380;
}
  input[type=tel], input[type=email], input[type=password] {
            width: 60%;
            padding: 7%;
            margin: 5px 0 22px 0;
            display: inline-block;
            border: 2px solid #ccc;
            background: #f1f1f1;
            border-radius: 10px;
        }
        
    </style>
</head>
<body>

<header>
    <div class="navbar">
        <h2>Profile</h2>
    </div>
</header>

<div class="profile-container">
    <div class="profile-card">
        <div class="profile-header">
            <img src="https://dataverify.com.ng/vtusite/images/Logo.png" alt="User Profile Picture" class="profile-pic">
            <h3 id="user-name"><?php echo $fullname; ?></h3>
            <p id="user-email"><?php echo $email; ?></p>
        </div>

        <div class="profile-info">
            <div class="info-row">
                <p class="info-title">Total Funded Balance:</p>
                <p class="info-detail" id="funded-balance"><?php echo $total_funded_balance; ?></p>
            </div>

            <div class="info-row">
                <p class="info-title">Registration Date:</p>
                <p class="info-detail" id="registration-date"><?php echo $formatted_registration_date; ?></p>
            </div>

            <div class="info-row">
                <p class="info-title">Phone Number:</p>
                <p class="info-detail" id="user-phone"><?php echo $number; ?></p>
            </div>

            <div class="info-row">
                <p class="info-title">Address:</p>
                <p class="info-detail" id="user-address">Nigeria</p>
            </div>
        </div>

        <button class="update-profile-btn" onclick="openModal('password')">Edit Password</button>
        <button class="update-profile-btn" onclick="openModal('pin')">Edit Pin</button>
    </div>
</div>

<!-- Modal for editing password or pin --> 
<center>
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span id="modalTitle"></span>
        </div>
        <div class="modal-body">
            <input type="password" id="current-password" placeholder="Current Password" required>
            <input type="password" id="new-password" placeholder="New Password" required>
        </div>
        <div class="modal-footer">
            <button id="submit-btn" onclick="submitChange()">Submit</button>
            <button onclick="closeModal()">Cancel</button>
        </div>
    </div>
</div>

<!-- Footer -->

<script>
    // Open the modal for editing password or pin
    function openModal(type) {
        document.getElementById("editModal").style.display = "block";
        document.getElementById("modalTitle").textContent = "Edit " + (type === 'password' ? 'Password' : 'Pin');
        document.getElementById("submit-btn").setAttribute('data-type', type);
    }

    // Close the modal
    function closeModal() {
        document.getElementById("editModal").style.display = "none";
    }

    // Handle form submission for updating password or pin
    function submitChange() {
        var type = document.getElementById("submit-btn").getAttribute('data-type');
        var currentPassword = document.getElementById("current-password").value;
        var newPassword = document.getElementById("new-password").value;

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "update-profile.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        // Send the current and new password/pin to the server for validation and update
        xhr.send("action=" + type + "&current_password=" + currentPassword + "&new_password=" + newPassword);

        xhr.onload = function () {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                alert("Update successful!");
                closeModal();
            } else {
                alert("Error: " + response.message);
            }
        };
    }
</script>

</body>
</html>