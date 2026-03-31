<?php
session_start();
include_once("db_conn.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data
    $plan_id = mysqli_real_escape_string($conn, $_POST['plan_id']);
    $reseller_id = mysqli_real_escape_string($conn, $_POST['reseller_id']);
    $reseller_amount = mysqli_real_escape_string($conn, $_POST['reseller_amount']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);

    // Check if both reseller_id and plan_id exist in resellers_price table
    $query_check = "SELECT * FROM resellers_price WHERE reseller_id = ? AND plan_id = ?";
    $stmt_check = mysqli_prepare($conn, $query_check);
    mysqli_stmt_bind_param($stmt_check, "ii", $reseller_id, $plan_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if ($result_check && mysqli_num_rows($result_check) > 0) {
        // Both reseller_id and plan_id exist, update the existing record
        $query_update = "UPDATE resellers_price SET reseller_amount = ?, amount = ? WHERE reseller_id = ? AND plan_id = ?";
        $stmt_update = mysqli_prepare($conn, $query_update);
        mysqli_stmt_bind_param($stmt_update, "ddii", $reseller_amount, $amount, $reseller_id, $plan_id);

        if (mysqli_stmt_execute($stmt_update)) {
            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }

        // Close statement
        mysqli_stmt_close($stmt_update);
    } else {
        // Check if reseller_id exists in resellers_price table
        $query_check_reseller = "SELECT * FROM resellers_price WHERE reseller_id = ?";
        $stmt_check_reseller = mysqli_prepare($conn, $query_check_reseller);
        mysqli_stmt_bind_param($stmt_check_reseller, "i", $reseller_id);
        mysqli_stmt_execute($stmt_check_reseller);
        $result_check_reseller = mysqli_stmt_get_result($stmt_check_reseller);

        if ($result_check_reseller && mysqli_num_rows($result_check_reseller) == 0) {
            // Only insert if reseller_id does not exist, regardless of plan_id
            $query_insert = "INSERT INTO resellers_price (reseller_id, plan_id, reseller_amount, amount) VALUES (?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $query_insert);
            mysqli_stmt_bind_param($stmt_insert, "iidd", $reseller_id, $plan_id, $reseller_amount, $amount);

            if (mysqli_stmt_execute($stmt_insert)) {
                echo "New record inserted successfully";
            } else {
                echo "Error inserting record: " . mysqli_error($conn);
            }

            // Close statement
            mysqli_stmt_close($stmt_insert);
        } else {
            echo "Record with reseller ID $reseller_id already exists.";
        }

        // Close statement
        mysqli_stmt_close($stmt_check_reseller);
    }

    // Close database connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Custom Price</title>
</head>
<body>
    <center><strong>Edit Custom Price</strong></center>
    <form id="editForm" action="#" method="post">
        <!-- Input fields -->
        <label for="plan_id">Plan ID:</label>
        <input type="text" id="plan_id" name="plan_id" readonly><br><br>
        <label for="plan_type">Plan Type:</label>
        <input type="text" id="plan_type" name="plan_type" readonly><br><br>
        <label for="plan_name">Plan Name:</label>
        <input type="text" id="plan_name" name="plan_name" readonly><br><br>
        <label for="amount">Amount:</label>
        <input type="text" id="amount" name="amount" readonly><br><br>
        <!-- Additional input fields -->
        <label for="reseller_id">Reseller ID:</label>
        <input type="text" id="reseller_id" value="<?php echo $_SESSION["reseller_id"];?>" name="reseller_id" readonly><br><br>
        <label for="reseller_amount">Reseller Amount:</label>
        <input type="text" id="reseller_amount" name="reseller_amount"><br><br>
        <!-- Submit button -->
        <button type="button" id="submitBtn">Submit</button>
    </form>

    <script>
        document.getElementById("submitBtn").addEventListener("click", function() {
            var plan_id = document.getElementById("plan_id").value;
            var reseller_amount = document.getElementById("reseller_amount").value;
            var amount = document.getElementById("amount").value;
            var reseller_id = document.getElementById("reseller_id").value;

            // AJAX request to submit form data
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "your_php_script.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        // Update UI based on response
                        alert(xhr.responseText);
                        // You can update the UI further as needed
                    } else {
                        alert('Error: ' + xhr.statusText);
                    }
                }
            };
            xhr.send("plan_id=" + plan_id + "&reseller_amount=" + reseller_amount + "&amount=" + amount + "&reseller_id=" + reseller_id);
        });
    </script>
</body>
</html>
