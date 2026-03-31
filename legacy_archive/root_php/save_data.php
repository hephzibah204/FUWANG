<?php
include_once("db_conn.php");

// Function to check if banner_id exists in the database
function bannerIdExists($bannerId, $conn) {
    $query = "SELECT COUNT(*) AS count FROM banner_ads WHERE banner_id = '$bannerId'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] > 0;
}

// Function to insert data into the database
function insertOrUpdateData($bannerId, $leftTitle, $rightTitle, $aboutBanner, $signUpText, $conn) {
    if (bannerIdExists($bannerId, $conn)) {
        // Update existing row
        $query = "UPDATE banner_ads SET left_title = '$leftTitle', right_title = '$rightTitle', about_banner = '$aboutBanner', sign_up_text = '$signUpText' WHERE banner_id = '$bannerId'";
    } else {
        // Insert new row
        $query = "INSERT INTO banner_ads (banner_id, left_title, right_title, about_banner, sign_up_text) VALUES ('$bannerId', '$leftTitle', '$rightTitle', '$aboutBanner', '$signUpText')";
    }

    $result = mysqli_query($conn, $query);

    // Check for errors
    if (!$result) {
        return "Error: " . mysqli_error($conn);
    }

    return "Data inserted/updated successfully";
}

// Check if AJAX request
if (isset($_POST['action']) && $_POST['action'] == 'insert') {
    // Retrieve data from AJAX request
    $bannerId = $_POST['banner_id'];
    $leftTitle = $_POST['left_title'];
    $rightTitle = $_POST['right_title'];
    $aboutBanner = $_POST['about_banner'];
    $signUpText = $_POST['sign_up_text'];

    // Insert or update data in the database
    $insertOrUpdateResult = insertOrUpdateData($bannerId, $leftTitle, $rightTitle, $aboutBanner, $signUpText, $conn);

    // Return the result
    echo $insertOrUpdateResult;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Wallet To Wallet</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
</head>
<body>

<form id="bannerForm" action="#" method="post">
    <img class="fixed_img" name="logo" src="images/mtn.png" style="width:10%; border-radius:10%; position: absolute; left:14%; top:8%" alt="Poster Image">
    <br>
    <t id="leftTitle" contenteditable="true" style="color:darkblue;font-size:15px; position: absolute;left:10%;top:18%">ZAHRADATA</t>
    <t id="rightTitle" class="draggable draggableText" contenteditable="true" style="position: absolute;top:16%;color:darkblue;right:8%;font-size:15px;">zahradasub.com.ng</t>
    <br>
    <strong id="signUpText" class="draggable" contenteditable="true" style="position: absolute;top:78%;color:darkblue;right:11%;font-size:23px;">Sign up now!</strong>
    <small id="aboutBanner" class="draggable" contenteditable="true" style="position: absolute;top:30%;font-size:15px;color:darkblue;left:8%;font-weight: bold;">UNBEATABLE DATA DEALS <br> WITH ZAHRDATA <br> lets visit Datasmart.com.ng</small>
    <input type="hidden" name="banner_id" id="banner_id_input">
    <input type="hidden" name="left_title" id="left_title_input">
    <input type="hidden" name="right_title" id="right_title_input">
    <input type="hidden" name="about_banner" id="about_banner_input">
    <input type="hidden" name="sign_up_text" id="sign_up_text_input">
    <input type="submit" value="Submit">



</form>

<script>
    // Function to retrieve banner_id from URL
    function getBannerIdFromUrl() {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('banner_id');
    }

    // Function to send AJAX request to insert data
    function sendData() {
        var bannerId = getBannerIdFromUrl();
        var leftTitle = document.getElementById("leftTitle").innerText;
        var rightTitle = document.getElementById("rightTitle").innerText;
        var aboutBanner = document.getElementById("aboutBanner").innerText;
        var signUpText = document.getElementById("signUpText").innerText;

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                } else {
                    console.error("Error:", xhr.status);
                }
            }
        };
        var params = "action=insert&banner_id=" + encodeURIComponent(bannerId) + "&left_title=" + encodeURIComponent(leftTitle) + "&right_title=" + encodeURIComponent(rightTitle) + "&about_banner=" + encodeURIComponent(aboutBanner) + "&sign_up_text=" + encodeURIComponent(signUpText);
        xhr.send(params);
    }

    // Send data every 5 seconds
    setInterval(sendData, 5000);
</script>

</body>
</html>
