<?php
session_start();
include_once("db_conn.php");

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Prepare the SQL query to check if the email exists in the referral table
    $stmt = $conn->prepare("SELECT referral_id FROM customers2 WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the row as an associative array
        $row = $result->fetch_assoc();
        // Get the referral_id
        $referral_id = $row['referral_id'];
    } else {
        echo "No user found with that email.";
        exit(); // Stop further execution if no user found
    }

    // Close the statement
    $stmt->close();
} else {
    // Email session is not set, redirect to users_logout.php
    header("Location: users_logout.php");
    exit();
}


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refer a Friend</title>
    <link rel="stylesheet" href="styles.css">
  <meta name="viewport" content="initial-scale=1,minimum-scale=1,width=device-width,interactive-widget=resizes-content,initial-scale=1.0, user-scalable=no">
<meta name="theme-color"content="#190F92">
           <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            
             <script>
        function copyToClipboard() {
            // Create a temporary input element
            var tempInput = document.createElement("input");
            
            // Set the input's value to the referral link
            tempInput.value = "<?php echo 'https://www.dataverify.com.ng/user_register?reseller_id=' . $referral_id; ?>";
            
            // Append the input to the body
            document.body.appendChild(tempInput);
            
            // Select the input's value
            tempInput.select();
            
            // Copy the selected text to the clipboard
            document.execCommand("copy");
            
            // Remove the temporary input from the body
            document.body.removeChild(tempInput);
            
            // Show the custom alert
            var copyAlert = document.getElementById("copyAlert");
            copyAlert.className = "show";
            setTimeout(function() { copyAlert.className = copyAlert.className.replace("show", ""); }, 3000);
        }
    </script>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f8f9fa;
                }

                .container {
                    width: 80%;
                    margin: 0 auto;
                }

                header {
                    background-color:darkblue;
                    color: white;
                    padding: 20px 0;
                }

                header h1 {
                    margin: 0;
                    font-size: 2rem;
                }

                .main-content {
                    padding: 50px 0;
                }

                .main-content p {
                    font-size: 1.2rem;
                }

                .cta-button {
                    display: inline-block;
                    padding: 10px 20px;
                    background-color: #007bff;
                    color: #fff;
                    text-decoration: none;
                    border-radius: 5px;
                    font-size: 1.1rem;
                    margin-top: 20px;
                }

                .testimonials {
                    background-color: #f9f9f9;
                    padding: 50px 0;
                }

                .testimonial {
                    border: 1px solid #ccc;
                    border-radius: 5px;
                    padding: 20px;
                    margin-bottom: 20px;
                }

                .testimonial p {
                    font-size: 1.1rem;
                }

                .testimonial .author {
                    margin-top: 10px;
                    font-style: italic;
                }

                

                input[type=text], input[type=tel], select, input[type=password] {
                    width: 65%;
                    padding: 3%;
                    margin: 5px 0 22px 0;
                    display: inline-block;
                    border: none;
                    background: #f1f1f1;
                }

                input[type=submit] {
                    width: 45%;
                    background-color: darkblue;
                    color: white;
                    padding: 3%;
                    margin: 5px 0 22px 0;
                    display: inline-block;
                    border-radius: 10px;
                    background: darkblue;
                }
                /* Hide the dropdown content by default */
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
        }.header{
color:white;

top:-1%;
left:-0.1%;
position: absolute;
background-color: #190F92;
width:100%;
}
* {box-sizing: border-box;}
body {font-family: Verdana, sans-serif;}
.mySlides {display: none;}
img {vertical-align: middle;}

/* Slideshow container */
.slideshow-container {
  max-width: 1000px;
  position: relative;
  margin: auto;
}

/* Caption text */
.text {
  color: #f2f2f2;
  font-size: 15px;
  padding: 8px 12px;
  position: absolute;
  bottom: 8px;
  width: 100%;
  text-align: center;
}

/* Number text (1/3 etc) */
.numbertext {
  color: #f2f2f2;
  font-size: 12px;
  padding: 8px 12px;
  position: absolute;
  top: 0;
}

/* The dots/bullets/indicators */
.dot {
  height: 15px;
  width: 15px;
  margin: 0 2px;
  background-color: #bbb;
  border-radius: 50%;
  display: inline-block;
  transition: background-color 0.6s ease;
}

.active {
  background-color: #717171;
}

/* Fading animation */
.fade {
  animation-name: fade;
  animation-duration: 1.5s;
}

@keyframes fade {
  from {opacity: .4} 
  to {opacity: 1}
}

/* On smaller screens, decrease text size */
@media only screen and (max-width: 300px) {
  .text {font-size: 11px}
}

 #copyAlert {
            visibility: hidden;
            min-width: 50%;
            background-color:#190F92 ;
            color: #fff;
            text-align: center;
            border-radius: 2px;
            padding: 3%;
            position: fixed;
            z-index: 1;
            left: 50%;
            bottom: 30px;
            font-size: 10px;
            transform: translateX(-50%);
        }

        #copyAlert.show {
            visibility: visible;
            -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
            animation: fadein 0.5s, fadeout 0.5s 2.5s;
        }

        @-webkit-keyframes fadein {
            from {bottom: 0; opacity: 0;}
            to {bottom: 30px; opacity: 1;}
        }

        @keyframes fadein {
            from {bottom: 0; opacity: 0;}
            to {bottom: 30px; opacity: 1;}
        }

        @-webkit-keyframes fadeout {
            from {bottom: 30px; opacity: 1;}
            to {bottom: 0; opacity: 0;}
        }

        @keyframes fadeout {
            from {bottom: 30px; opacity: 1;}
            to {bottom: 0; opacity: 0;}
        }
button {
  background-color: darkblue;/* Green */
  border: none;
  color: white;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  border-radius: 8px;
  transition-duration: 0.4s;
  cursor: pointer;
}

button:hover {
  background-color: #45a049; /* Darker green */
}

            </style>
</head>
<body>
      <div class="dropdown">
  <div class="menu-container">
      
    <i style="position: absolute;right:4%;margin-top:5%; opacity:0.9;z-index:111;font-size:30px;color:white;"class="fa fa-ellipsis-v" id="menu-icon"></i>
    <div class="dropdown-content">
        
    <a href="user_referral_history.php"class="fa fa-dashboard"> Referral commission history</a>
     <a href="dashboard.php"class="fa fa-dashboard"> Back To Dashboard  </a>
<div style="border:1px solid #f1f1f1;">
 <a href="users_logout.php"class="fa fa-sign-out"> Logout</a>
    </div>
  </div>
</div>
    
    
    
    <header>
        <div class="container">
            <h4>Refer a Friend</h4>
        </div>
    </header>

   <div class="slideshow-container">

<div class="mySlides fade">
  <div class="numbertext">2 / 3</div>
  <img src="/vtusite/images/refer10.jpg" style="width:100%">
  <div class="text"></div>
</div>

<div class="mySlides fade">
  <div class="numbertext">1 / 3</div>
  <img src="/vtusite/images/refer1.jpg" style="width:100%">
  <div class="text"></div>
</div>

<div class="mySlides fade">
  <div class="numbertext">2 / 3</div>
  <img src="/vtusite/images/refer2.jpg" style="width:100%">
  <div class="text"></div>
</div>



<div class="mySlides fade">
  <div class="numbertext">3 / 3</div>
  <img src="/vtusite/images/refer6.jpg" style="width:100%">
  <div class="text"></div>
</div>

</div>
<br>

<div style="text-align:center">
  <span class="dot"></span> 
  <span class="dot"></span> 
  <span class="dot"></span> 
   <span class="dot"></span> 

</div>
<div style="border:3px solid #f1f1f1;"></div>
<script>
let slideIndex = 0;
showSlides();

function showSlides() {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";  
  }
  slideIndex++;
  if (slideIndex > slides.length) {slideIndex = 1}    
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex-1].style.display = "block";  
  dots[slideIndex-1].className += " active";
  setTimeout(showSlides, 2000); // Change image every 2 seconds
}
</script>




    

    

    <section class="main-content">
       
        <div class="container">
            <p>Refer a friend as much as you can, you and your friends both get rewarded.</p>
             <p><center>Refer and get ₦200 instantly and your friend get ₦100 </center></p>
            <button onclick="copyToClipboard()">Copy Referral ID</button>
    <div id="copyAlert">Copied!</div>
          
<br><br><br>
            <button id="shareButton">Share Referral Link</button>
            
            
        </div>
    </section>

    <footer>
        <?php
        
// Fetch the reseller_id of the current user
$query = $conn->prepare("SELECT referral_id FROM customers2 WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$query->bind_result($referral_id);
$query->fetch();
$query->close();

// Fetch the referral users' details
$query = $conn->prepare("
    SELECT fullname, email, created_at
    FROM customers2
    WHERE reseller_id = ?
");
$query->bind_param("s", $referral_id);
$query->execute();
$query->bind_result($fullname, $email, $created_at);

$referral_users = [];
while ($query->fetch()) {
    $referral_users[] = [
        'fullname' => $fullname,
        'email' => $email,
        'created_at' => $created_at
    ];
}
?>
<center><h2>Referral list</h2></center>

       
            <?php if (!empty($referral_users)): ?>
                <?php foreach ($referral_users as $user): ?>
                
              <table class="table table-bordered table-striped"style="width:100%">
<thead>
<tr>
<center>
    
                <th style="color: black; opacity: 0.8">name</th>
                <th style="color: black; opacity: 0.8">email</th>
                <th style="color: black; opacity: 0.8">Registered date</th>
             </tr>
               </thead>
<tbody id="myTable"width="100%">
                <tr>
                    <td style="color: black; opacity: 0.6"><?= htmlspecialchars($user['fullname']) ?></td>
                <td style="color: black; opacity: 0.6"> <?= htmlspecialchars($user['email']) ?></td>
                <td style="color: black; opacity: 0.6"> <?= htmlspecialchars($user['created_at']) ?></td>
                    
                    
                   </hr>
                    
                <?php endforeach; ?>
            <?php else: ?>
              <center><img src="/vtusite/images/no_data2.jpg"width="100%"></center>
            <?php endif; ?>
            </tbody>   
</table>  
        </div>
    </div>
    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
    </footer>

    <!-- Bootstrap JS (optional, if needed for your site) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.getElementById('shareButton').addEventListener('click', function() {
            // Replace with your referral link
            const referralLink = 'https://www.dataverify.com.ng/user_register.php?reseller_id=<?php echo $referral_id; ?>';

            if (navigator.share) {
                navigator.share({
                    title: 'Refer a Friend',
                    text: 'Share this link 🔗 to your love ones, if you refer somebody both get rewarded',
                    url: referralLink,
                })
                .then(() => console.log('Successful share'))
                .catch((error) => console.log('Error sharing', error));
            } else {
                // Fallback for browsers that do not support navigator.share
                alert('Sharing is not supported in this browser. Copy this link: ' + referralLink);
            }
        });
    </script>
    
    
    
    
        <script>
        document.getElementById('copyButton').addEventListener('click', function() {
            var referralLink = document.getElementById('referralLink');
            referralLink.select();
            referralLink.setSelectionRange(0, 99999); // For mobile devices

            document.execCommand('copy');
            showNotification();
        });

        function showNotification() {
            var notification = document.getElementById('notification');
            notification.className = 'notification show';
            setTimeout(function() {
                notification.className = notification.className.replace('show', '');
            }, 3000);
        }
        </script>
</body>
</html>
