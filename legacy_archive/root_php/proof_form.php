 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Proof</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        /* Loader style */
        .loader {
            border: 16px solid #f3f3f3;
            border-radius: 50%;
            border-top: 16px solid #3498db;
            width: 80px;
            height: 80px;
            animation: spin 2s linear infinite;
            display: none; /* Initially hidden */
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000; /* Ensure it stays on top */
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .response-message {
            display: none; /* Initially hidden */
            text-align: center;
            font-size: 16px;
            margin-top: 20px;
        }
    </style>
    <script>
        $(document).ready(function() {
            $("form").submit(function(event) {
                event.preventDefault(); // Prevent the form from submitting via the browser

                // Show loader
                $(".loader").show();

                // Collect form data
                var formData = {
                email: $("input[name='email']").val(),
                    account_number: $("input[name='account_number']").val(),
                    bank_name: $("input[name='bank_name']").val(),
                    amount: $("input[name='amount']").val(),
                    remark: $("input[name='remark']").val()
                };

                // Send data via AJAX
                $.ajax({
                    type: "POST",
                    url: "insert_proof.php",
                    data: formData,
                    dataType: "json",
                    encode: true,
                    success: function(data) {
                        // Hide loader
                        $(".loader").hide();

                        // Hide form and show response message
                        $("form").hide();
                        if (data.success) {
                            $(".response-message").text("Proof sent successfully. make sure to check ur balance changes as soon as possible we confirmed your payment!.").show();
                        } else {
                            $(".response-message").text("Error: " + data.message).show();
                        }
                    },
                    error: function() {
                        // Hide loader
                        $(".loader").hide();

                        // Hide form and show error message
                        $("form").hide();
                        $(".response-message").text("An error occurred while sending proof.").show();
                    }
                });
            });
        });
    </script>
</head>    
<body>
    <form>
        <center>
            <small>Send proof 🧾 with specific bank details you've sent</small>
            <input type="hidden" Maxlength="30"name="email"value="<?php echo $email;?>">
            <input name="account_number" Maxlength="11"type="text" placeholder="account number" required>
            <input name="bank_name" Maxlength="20"type="text" placeholder="bank name" required>
            <input name="amount" Maxlength="10"type="text" placeholder="amount" required>
            <input name="remark" Maxlength="30"type="text" style="padding:10%" placeholder="Remark (optional)">
            <button>Send Proof</button>
        </center>

</form>
    <!-- Loader element -->
    <div class="loader"></div>

    <!-- Response message element -->
    <div class="response-message"></div>

    
