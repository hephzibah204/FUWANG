<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once("db_conn.php");

session_start();

$url = $_SERVER['REQUEST_URI'];

// Split the URL by '/' and remove empty elements
$parts = array_filter(explode('/', trim($url, '/')));

// Extract the reseller ID, assuming it's the first element after index.php
$reseller_id = isset($parts[1]) ? $parts[1] : null;

if (!empty($reseller_id)) {
    // Store reseller ID in session
    $_SESSION['reseller_id'] = $reseller_id;

    // Check if the session reseller ID exists in the resellers table
    $query = "SELECT * FROM resellers WHERE reseller_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $reseller_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        // Reseller ID found in the resellers table
        // Proceed with your code here
    } else {
        // No reseller ID found in the resellers table
        header('HTTP/1.0 404 Not Found');
        header('Location: /vtusite/404.html');
        exit; // Terminate script execution after redirection
    }
} else {
    // Redirect to 404.html if no reseller ID was found
    header('HTTP/1.0 404 Not Found');
    header('Location: /vtusite/404.html');
    exit; // Terminate script execution after redirection
}


$sql_total_users = "SELECT COUNT(*) AS total_users FROM referral WHERE reseller_id = '$reseller_id'";
$result_total_users = $conn->query($sql_total_users);
if ($result_total_users && $result_total_users->num_rows > 0) {
    $row_total_users = $result_total_users->fetch_assoc();
    $total_users = $row_total_users['total_users'];
} else {
    $total_users = 0;
}

$sql = "SELECT COUNT(*) AS online_users FROM users WHERE reseller_id = ? AND online_status = 'online'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $reseller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $row = $result->fetch_assoc();
    $online_users_count = $row['online_users'];
    echo "";
} else {
    // If query execution fails, output an error message
    echo "Error executing query: " . $conn->error;
}





function countVisitorsByResellerId($conn, $reseller_id) {
    // Prepare SQL statement
    $sql = "SELECT COUNT(*) AS total_visitors FROM visitors WHERE reseller_id = ? AND reseller_id IS NOT NULL";
    $stmt = $conn->prepare($sql);

    // Check for SQL errors
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    // Bind parameter
    $stmt->bind_param("s", $reseller_id);

    // Execute statement
    if (!$stmt->execute()) {
        die("SQL Error: " . $stmt->error);
    }

    // Get result
    $result = $stmt->get_result();

    // Fetch row
    $row = $result->fetch_assoc();

    // Close statement
    $stmt->close();

    // Return total visitors
    return $row['total_visitors'];
}

// Get reseller_id from session
$reseller_id = $_SESSION['reseller_id'] ?? null;

// Check if reseller_id is set
if ($reseller_id !== null) {
    // Call countVisitorsByResellerId function
    $total_visitors = countVisitorsByResellerId($conn, $reseller_id);

    // Output total visitors
    echo "";
} else {
    echo "";
}





?>
<!doctype html>
<html  lang="en">
    <head>
        <!-- Meta Tags -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="keywords" content="Site keywords here">
		<meta name="description" content="">
		<meta name='copyright' content=''>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Title -->
        <title>The number 1 vtu site</title>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Check if the page exists
        if (document.title === "404 Error: Page Not Found") {
            // Redirect to the 404 page
            window.location.replace("404.html");
        }
    });
</script>
		
		<!-- Favicon -->
        <link rel="icon" href="images/gtech1.jpg">
		
		<!-- Google Fonts -->
		<link href="https://fonts.googleapis.com/css?family=Poppins:200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap" rel="stylesheet">

		<!-- Bootstrap CSS -->
	
			<link rel="stylesheet" href="/vtusite/css/bootstrap.min.css">
<!-- Nice Select CSS -->
<link rel="stylesheet" href="/vtusite/css/nice-select.css">
<!-- Font Awesome CSS -->
<link rel="stylesheet" href="/vtusite/css/font-awesome.min.css">
<!-- icofont CSS -->
<link rel="stylesheet" href="/vtusite/css/icofont.css">
<!-- Slicknav -->
<link rel="stylesheet" href="/vtusite/css/slicknav.min.css">
<!-- Owl Carousel CSS -->
<link rel="stylesheet" href="/vtusite/css/owl-carousel.css">
<!-- Datepicker CSS -->
<link rel="stylesheet" href="/vtusite/css/datepicker.css">
<!-- Animate CSS -->
<link rel="stylesheet" href="/vtusite/css/animate.min.css">
<!-- Magnific Popup CSS -->
<link rel="stylesheet" href="/vtusite/css/magnific-popup.css">

<!-- Medipro CSS -->
<link rel="stylesheet" href="/vtusite/css/normalize.css">
<link rel="stylesheet" href="/vtusite/style.css">
<link rel="stylesheet" href="/vtusite/css/responsive.css">
<style>
.loader {
    border: 16px solid #f3f3f3; /* Light grey */
    border-top: 16px solid darkblue; /* Blue */
    border-radius: 50%;
    width: 10%;
    height: 11%;
    animation: spin 2s linear infinite;
    position: fixed;
    left: 38%;
    top: 50%;
    transform: translate(-50%, -50%);
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

</style>
    </head>
    <body>
	
		<!-- Preloader -->
        <div class="preloader">
            <div class="loader">
                <div class="loader-outter"></div>
                <div class="loader-inner"></div>

                <div class="indicator"> 
                    <svg width="16px" height="12px">
                        <polyline id="back" points="1 6 4 6 6 11 10 1 12 6 15 6"></polyline>
                        <polyline id="front" points="1 6 4 6 6 11 10 1 12 6 15 6"></polyline>
                    </svg>
                </div>
            </div>
        </div>


<center><div id="loader" class="loader"style="display:none"></div></center>



        <!-- End Preloader -->
		
		<!-- Get Pro Button -->
		<ul class="pro-features">
			
			
			<li class="title">Pro Version Features</li>
			<li>have right to set your own price</li>
			<li>get right to change your site template</li>
			<li>no night mode lock</li>
			<li>No commission ending </li>
			<li>set premium futures </li>
			<div class="button">
				<a href="" target="_blank" class="btn">start as free</a>
				<a href="#" target="_blank" class="btn">Buy Pro Version</a>
			</div>
		</ul>
	
		<!-- Header Area -->
		<header class="header" >
			<!-- Topbar -->
			<div class="topbar">
				<div class="container">
					<div class="row">
						<div class="col-lg-6 col-md-5 col-12">
							<!-- Contact -->
							
							<!-- End Contact -->
						</div>
						<div class="col-lg-6 col-md-7 col-12">
							<!-- Top  -->
							<ul class="top-contact">
								
							</ul>
							<!-- End Top Contact -->
						</div>
					</div>
				</div>
			</div>
			<!-- End Topbar -->
			<!-- Header Inner -->
			<div class="header-inner">
				<div class="container">
					<div class="inner">
						<div class="row">
							<div class="col-lg-3 col-md-3 col-12">
								<!-- Start Logo -->
								<div class="logo">
									<a href="index.php"style="color:blue;font-size:27px">
<span style="text-shadw: 2px 2px 4px rgba(0, 0, 0, 0.5);font-weight: bold;">

<?php echo $reseller_id;?></a></span>
								</div>
								<!-- End Logo -->
								<!-- Mobile Nav -->
								<div class="mobile-nav"></div>
								<!-- End Mobile Nav -->
							</div>
							<div class="col-lg-7 col-md-9 col-12">
								<!-- Main Menu -->
								<div class="main-menu">
									<nav class="navigation">
										<ul class="nav menu">
											<li class="active"><a href="index.php">Home</i></a>
												
						
											</li>
											<li><a href="#">About us </a></li>
											<li><a href="#">Services </a></li>
							<li><a href="#">pricing</a></li>
							
				<li><a href="#">policy <i class="icofont-rounded-down"></i></a>
												<ul class="dropdown">
													<li><a href="404.html">Terms and conditions</a></li>
	<li><a href="404.html">Privacy and policy</a></li>
	<li><a href="404.html">disclaimer</a></li>
												</ul>
											</li>
											
											<li><a href="contact.html">Contact Us</a></li>
<ul>
    <?php
    if(isset($_SESSION['email'])) {
       echo '<li>
<a href="http://localhost:8000/vtusite/dashboard.php/' . $reseller_id .' ">

<center style="background-color: blue; border-radius:10px; color:white">My Account</center></a></li>';

    } else {
        echo '<li>
<a href="http://localhost:8000/vtusite/user_register.php/' . $reseller_id .' "><center style="background-color: blue; border-radius:10px; color:white">Register</center></a></li>';
    }
    ?>
</ul>
<hr>

										</ul>
									</nav>
								</div>
								<!--/ End Main Menu -->
							</div>
							<div class="col-lg-2 col-12">
								<div class="get-quote">
									<a href="appointment.html" class="btn"></a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!--/ End Header Inner -->
		</header>
		<!-- End Header Area -->
		
		<!-- Slider Area -->
		<section class="slider">
			<div class="hero-slider">
				<!-- Start Single Slider -->
				<div class="single-slider" style="background-image: url('/vtusite/img/client-bg.jpg');">

					<div class="container">
						<div class="row">
							<div class="col-lg-7">
								<div class="text">
									<h1>Welcome to<span> <?php echo $reseller_id;?> </span> our services is <span>Trust!</span></h1>
									<p>We are a registered telecommunication company that provide voice or data transmission services, such as; Mobile Data And Airtime (VTU) . </p>
									<div class="button">
    <?php
    if(isset($_SESSION['email'])) {
        echo '<a href="http://localhost:8000/vtusite/dashboard.php/' . $reseller_id .' "class="btn">Go To Dashboard</a>';
    } else {
        echo '<a href="http://localhost:8000/vtusite/user_register.php/' . $reseller_id .' "class="btn">Register</a>';
    }
    ?>

										<a href="#" class="btn primary">Learn More</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- End Single Slider -->
				<!-- Start Single Slider -->
				<div class="single-slider" style="background-image:url('/vtusite/images/nin1.jpg')">
					<div class="container">
						<div class="row">
							<div class="col-lg-7">
								<div class="text">
								<h1>We Provided you services <span> To print</span> your <span> NIN</span> Slip<span></span> Details<span></span></h1>
									<p>You can also print you <span></span> Slip either Premium slip or normal slip and we offer you to print your NIN details</p>
									<div class="button">
										 <?php
    if(isset($_SESSION['email'])) {
        echo '<a href="http://localhost:8000/vtusite/dashboard.php/' . $reseller_id .' "class="btn">Lets Get</a>';
    } else {
        echo '<a href="http://localhost:8000/vtusite/user_register.php/' . $reseller_id .' "class="btn">Sign Up & Print</a>';
    }
    ?>

										<a href="#" class="btn primary">Learn more about</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- Start End Slider -->
				<!-- Start Single Slider -->
				<div class="single-slider" style="background-image:url('/vtusite/images/bvn1.jpg')">
					<div class="container">
						<div class="row">
							<div class="col-lg-7">
								<div class="text">
									<h1>We Also Provide services To Print your <span> BVN</span> Details</h1>
									<p>You can also print your <span></span> BVN details incase of loose so you can retrieve all details in this site</p>
									<div class="button">
										 <?php
    if(isset($_SESSION['email'])) {
        echo '<a href="http://localhost:8000/vtusite/dashboard.php/' . $reseller_id .' "class="btn">Go To Print</a>';
    } else {
        echo '<a href="http://localhost:8000/vtusite/user_register.php/' . $reseller_id .' "class="btn">Sign Up & Print</a>';
    }
    ?>

										<a href="#" class="btn primary">Learn more about</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- End Single Slider -->
			</div>
		</section>
		<!--/ End Slider Area -->
		
		<!-- Start Schedule Area -->
		<section class="schedule">
			<div class="container">
				<div class="schedule-inner">
					<div class="row">
						<div class="col-lg-4 col-md-6 col-12 ">
							<!-- single-schedule -->
							<div class="single-schedule first">
								<div class="inner">
									<div class="icon">
										<i class="fa fa-whatsapp"></i>
									</div>
									<div class="single-content">
									
										<h4>About Us</h4>
										<p>we're always available for your complaint, advices for the important futures you want us to set</p>
										<a href="<?php echo $row_reseller["whatsapp_url"]?>">Join our WhatsApp group<i class="fa fa-long-arrow-right"></i></a>
									</div>
								</div>
							</div>
						</div>
						
					</div>
				</div>
			</div>
		</section>
		<!--/End Start schedule Area -->

		<!-- Start Feautes -->
		<section class="Feautes section">
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<div class="section-title">
							<h2>Start Printing NIN slip and BVN details to get money.</h2>
							<img src="/vtusite/images/nin_and_bvn.jpg" alt="#">
							<p>You can print your premium plastic Nation ID card, Bvn details, NIN details like date of birth, phone number, etc</p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-4 col-12">
						<!-- Start Single features -->
						<div class="single-features">
							<div class="signle-icon">
								<i class="icofont"><img src="/vtusite/images/nin1.jpg"width="100%"style="height:100%;"></i>
							</div>
							<h3>Print Plastic Nation Id card Slip</h3>
							<p>You can print your Id card as low as concern in zahradatasub</p>
						</div>
						<!-- End Single features -->
					</div>
					<div class="col-lg-4 col-12">
						<!-- Start Single features -->
						<div class="single-features">
							<div class="signle-icon">
								<i class="icofont"><img src="/vtusite/images/nin2.jpg"style="width:100%;height:100%;border-radius:100%"></i>
				
							</div>
							<h3>Verifying National Id card Details</h3>
							<p>Here you can print your details like fullname, phone number, date of birth, adress, etc</p>
						</div>
						<!-- End Single features -->
					</div>
					<div class="col-lg-4 col-12">
						<!-- Start Single features -->
						<div class="single-features last">
							<div class="signle-icon">
								<i class="icofont"><img src="/vtusite/images/bvn1.jpg"style="width:100%;border-radius:100%;height:100%"></i>
							</div>
							<h3>Print My BVN details</h3>
							<p>Also Here you can get you BVN details </p>
						</div>
						<!-- End Single features -->
					</div>
				</div>
			</div>
		</section>
		<!--/ End Feautes -->
		
		<!-- Start Fun-facts -->
		<div id="fun-facts" class="fun-facts section overlay">
			<div class="container">
				<div class="row">
					<div class="col-lg-3 col-md-6 col-12">
						<!-- Start Single Fun -->
						<div class="single-fun">
							<i class="fa fa-eye"></i>
							<div class="content">
								<span class="counter"><?php echo $total_visitors;?></span>
								<p>Total Visitor</p>
							</div>
						</div>
						<!-- End Single Fun -->
					</div>
					<div class="col-lg-3 col-md-6 col-12">
						<!-- Start Single Fun -->
						<div class="single-fun">
							<i class="icofont icofont-user-alt-3"></i>
							<div class="content">
								<span class="counter"><?php echo $total_users;?></span>
								<p>Total Users</p>
							</div>
						</div>
						<!-- End Single Fun -->
					</div>
					<div class="col-lg-3 col-md-6 col-12">
						<!-- Start Single Fun -->
						<div class="single-fun">
							<i class="icofont-simple-smile"></i>
							<div class="content">
								<span class="counter"><?php echo $online_users_count;?></span>
								<p>Total Active users</p>
							</div>
						</div>
						<!-- End Single Fun -->
					</div>
					<div class="col-lg-3 col-md-6 col-12">
						<!-- Start Single Fun -->
						<div class="single-fun">
							<i class="fa fa-level-up"></i>
							<div class="content">
								<span class="counter">32</span>
								<p>Total premium Users</p>
							</div>
						</div>
						<!-- End Single Fun -->
					</div>
				</div>
			</div>
		</div>
		<!--/ End Fun-facts -->
		
		<!-- Start Why choose -->
		<section class="why-choose section" >
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<div class="section-title">
							<h2>We Offer Different Services To Improve Your rest</h2>
							<img src="/vtusite/img/section-img.png" alt="#">
							<p><?php echo $reseller_id?> is the number one biggest vtusite in africa.</p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-6 col-12">
						<!-- Start Choose Left -->
						<div class="choose-left">
							<h3>Who We Are</h3>
							<p>You can now buy airtime, data, pay tv bills, electricity bills and more. The process is very straight forward with dedicated customer service to attend to you at any given time</p>
							<p>We Also provide the fastest and easiest ways to fund your wallet for transaction. You can use wallet to wallet method or card payment or transfer to dedicated virtual account. You can also convert airtime to cash</p>
							<div class="row">
								<div class="col-lg-6">
									<ul class="list">
									
										<li><i class="fa fa-caret-right"></i>we provided us fastest and easier services</li>
										<li><i class="fa fa-caret-right"></i>We always available for your complains</li>
									</ul>
								</div>
								<div class="col-lg-6">
									<ul class="list">
										<li><i class="fa fa-caret-right"></i>we provide you method to fund you wallet </li>
										<li><i class="fa fa-caret-right"></i>we also sell data as affordable price</li>
										<li><i class="fa fa-caret-right"></i>we also add you method to earn money </li>
									</ul>
								</div>
							</div>
						</div>
						<!-- End Choose Left -->
					</div>
					<div class="col-lg-6 col-12">
						<!-- Start Choose Rights -->
						<div class="choose-right">
							<div class="video-image">
								<!-- Video Animation -->
								<div class="promo-video">
									<div class="waves-block">
										<div class="waves wave-1"></div>
										<div class="waves wave-2"></div>
										<div class="waves wave-3"></div>
									</div>
								</div>
								<!--/ End Video Animation -->
								<a href="https://youtu.be/mKXAGKuzFN8?si=RBaMusic5VGGmYdv" class="video video-popup mfp-iframe"><i class="fa fa-play"></i></a>
							</div>
						</div>
						<!-- End Choose Rights -->
					</div>
				</div>
			</div>
		</section>
		<!--/ End Why choose -->
		
		<!-- Start Call to action -->
		<section class="call-action overlay" data-stellar-background-ratio="0.5">
			<div class="container">
				<div class="row">
					<div class="col-lg-12 col-md-12 col-12">
						<div class="content">
							<h2>Do you need to become resseler </h2>
							<p>We provide you a way to become data reseller or site owner that offer you to get your vtusite dor free and its easier and settled by your own</p>
							<div class="button">
								<a href="#" class="btn">i want get please</a>
								<a href="#" class="btn second">Learn More<i class="fa fa-long-arrow-right"></i></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!--/ End Call to action -->
		
		<!-- Start portfolio -->
		<section class="portfolio section" >
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<div class="section-title">
							<h2>We're add the ways to generate your business banner</h2>
							<img src="/vtusite/img/section-img.png" alt="#">
							<p>You can generate your own business banner for free and premium method and you generate it for your business to get money with </p>
						</div>
					</div>
				</div>
			</div>
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12 col-12">
						<div class="owl-carousel portfolio-slider">
				
	<div class="single-pf">

								<img src="/vtusite/images/vtu5.jpg" alt="#">
								<a href="portfolio-details.html" class="btn">View Details</a>
							</div>
							<div class="single-pf">
								<img src="/vtusite/images/aa.png" alt="#">
								<a href="portfolio-details.html" class="btn">View Details</a>
							</div>
							<div class="single-pf">
								<img src="/vtusite/images/11t.png" alt="#">
								<a href="portfolio-details.html" class="btn">View Details</a>
							</div>
							<div class="single-pf">
								<img src="/vtusite/images/vtu2.jpg" alt="#">
								<a href="portfolio-details.html" class="btn">View Details</a>
							</div>
							<div class="single-pf">
								<img src="/vtusite/images/woman.png" alt="#">
								<a href="portfolio-details.html" class="btn">View Details</a>
							</div>
							
						</div>
					</div>
				</div>
			</div>
		</section>
		<!--/ End portfolio -->
		
		<!-- Start service -->
		<section class="services section">
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<div class="section-title">
							<h2>We Offer Different Services To Improve Your dreams.</h2>
							<img src="/vtusite/img/section-img.png" alt="#">
							<p>Zahradatasub has set you cheapest services with affordable prices </p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-4 col-md-6 col-12">
						<!-- Start Single Service -->
						<div class="single-service">
							<i class="icofont"><img src="/vtusite/images/data2.jpg"style="width:30%;"></i>
				
							<h4><a href="service-details.html">Buy Data Bundles</a></h4>
							<p>You can buy oneoff Data bundles as long as monthly  </p>	
						</div>
						<!-- End Single Service -->
					</div>
					<div class="col-lg-4 col-md-6 col-12">
						<!-- Start Single Service -->
						<div class="single-service">
								<i class="icofont"><img src="/vtusite/images/airtime2.jpg"style="width:25%;"></i>
				
							<h4><a href="service-details.html">Buy Airtime card</a></h4>
							<p>You can buy Airtime card as low as 95%  </p>	
						</div>
						<!-- End Single Service -->
					</div>
					<div class="col-lg-4 col-md-6 col-12">
						<!-- Start Single Service -->
						<div class="single-service">
								<i class="icofont"><img src="/vtusite/images/edu1.jpg"style="width:20%;"></i>
				
							<h4><a href="service-details.html">Buy Education Pin</a></h4>
							<p>You can Also Buy Education pin like result checker pin for Neco and Waec  </p>	
						</div>
						<!-- End Single Service -->
					</div>
					<div class="col-lg-4 col-md-6 col-12">
						<!-- Start Single Service -->
						<div class="single-service">
								<i class="icofont"><img src="/vtusite/images/electric1.jpg"style="width:20%;"></i>
				
							<h4><a href="service-details.html">Buy Electricity Bill</a></h4>
							<p>You can Also buy Electricity💡 bill</p>	
						</div>
						<!-- End Single Service -->
					</div>
					<div class="col-lg-4 col-md-6 col-12">
						<!-- Start Single Service -->
						<div class="single-service">
								<i class="icofont"><img src="/vtusite/images/airtime1.jpg"style="width:20%;"></i>
				
							<h4><a href="service-details.html">Convert Excess airtime to cash</a></h4>
							<p>You can also convert you excess airtime to cash  </p>	
						
						</div>
						<!-- End Single Service -->
					</div>
					<div class="col-lg-4 col-md-6 col-12">
						<!-- Start Single Service -->
						<div class="single-service">
							<i class="icofont"><img src="/vtusite/images/cable1.jpg"style="width:20%;"></i>
				
							<h4><a href="service-details.html">Buy Cable subscription</a></h4>
							<p>You can Also subscript your cable </p>	
						</div>
						<!-- End Single Service -->
					</div>
				</div>
			</div>
		</section>
		<!--/ End service -->
		
		<!-- Pricing Table -->
		<section class="pricing-table section">
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<div class="section-title">
							<h2>We Provide You The cheapest Resonable Price</h2>
							<img src="/vtusite/img/section-img.png" alt="#">
							<p>Our price is different to any other vtu providers, scroll down to see our services and price list to start enjoying.</p>
						</div>
					</div>
				</div>
        <div class="row">
            <div class="col-lg-4 col-md-12 col-12">
                <!-- Single Table -->
                <div class="single-table">
                    <!-- Table Head -->
                    <div class="table-head">
                        <div class="icon">
                            <img src="/vtusite/images/mtn.png" width="50%"alt="MTN">
                        </div>
                       
                                <?php
// Include database connection file
include_once("db_conn.php");

// Assuming you have established a database connection already

$sql_resellers_price = "SELECT * FROM resellers_price WHERE network = 'MTN' AND reseller_id = '$reseller_id'";

// Execute the query
$result_resellers_price = mysqli_query($conn, $sql_resellers_price);

// Check if query was successful
if ($result_resellers_price) {
    // Start HTML table
    echo '<h4 class="title"style="color:blue;opacity:0.4">Mtn Bundles Prices list</h4>';
    echo '<table style="width:100%">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Data Plan</th>';
    echo '<th>Amount</th>';
    echo '<th>Validity</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Fetch and display data from resellers_price table
   while ($row = mysqli_fetch_assoc($result_resellers_price)) {
    echo '<tr>';
    echo '<td style="font-size:10px">' . $row['data_plan'] . '</td>';
    echo '<td style="font-size:10px">₦' . $row['reseller_amount'] . '</td>';
    echo '<td style="font-size:10px">' . $row['validate'] . '</td>';
    echo '</tr>';
    echo '<tr><td colspan="3"><hr></td></tr>'; // Add horizontal line after each row
}


    echo '</tbody>';
    echo '</table>';

} else {
    // Handle query execution errors
    echo 'Error executing SQL query: ' . mysqli_error($conn);
}
?>

                    </div>
                    <!-- Table Bottom -->
                    <div class="table-bottom">
                        <a class="btn" href="#">Buy Now</a>
                    </div>
                </div>
                <!-- End Single Table -->
            </div>
<div class="col-lg-4 col-md-12 col-12">

<div class="single-table">
                    <!-- Table Head -->
                    <div class="table-head">
                        <div class="icon">
                            <img src="/vtusite/images/glo_icon.jpg" width="50%"alt="Glo">
                        </div>
                       
                                <?php
// Include database connection file
include_once("db_conn.php");

// Assuming you have established a database connection already

$sql_resellers_price = "SELECT * FROM resellers_price WHERE network = 'GLO' AND reseller_id = '$reseller_id'";

// Execute the query
$result_resellers_price = mysqli_query($conn, $sql_resellers_price);

// Check if query was successful
if ($result_resellers_price) {
    // Start HTML table
   echo '<h4 class="title"style="color:blue;opacity:0.4">Glo Bundles Prices list</h4>';
    echo '<table style="width:100%">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Data Plan</th>';
    echo '<th>Amount</th>';
    echo '<th>Validity</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Fetch and display data from resellers_price table
   while ($row = mysqli_fetch_assoc($result_resellers_price)) {
    echo '<tr>';
    echo '<td style="font-size:10px">' . $row['data_plan'] . '</td>';
    echo '<td style="font-size:10px">₦' . $row['reseller_amount'] . '</td>';
    echo '<td style="font-size:10px">' . $row['validate'] . '</td>';
    echo '</tr>';
    echo '<tr><td colspan="3"><hr></td></tr>'; // Add horizontal line after each row
}


    echo '</tbody>';
    echo '</table>';

} else {
    // Handle query execution errors
    echo 'Error executing SQL query: ' . mysqli_error($conn);
}
?>

                    </div>
                    <!-- Table Bottom -->
                    <div class="table-bottom">
                        <a class="btn" href="#">Buy Now</a>
                    </div>
                </div>
                <!-- End Single Table -->
            </div>
<div class="col-lg-4 col-md-12 col-12">

<div class="single-table">
                    <!-- Table Head -->
                    <div class="table-head">
                        <div class="icon">
                            <img src="/vtusite/images/mtn.png" width="50%"alt="MTN">
                        </div>
                       
                                <?php
// Include database connection file
include_once("db_conn.php");

// Assuming you have established a database connection already

$sql_resellers_price = "SELECT * FROM resellers_price WHERE network = 'Airtel' AND reseller_id = '$reseller_id'";

// Execute the query
$result_resellers_price = mysqli_query($conn, $sql_resellers_price);

// Check if query was successful
if ($result_resellers_price) {
    // Start HTML table
    echo '<h4 class="title"style="color:blue;opacity:0.4">Airtel Bundles Prices list</h4>';
    echo '<table style="width:100%">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Data Plan</th>';
    echo '<th>Amount</th>';
    echo '<th>Validity</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Fetch and display data from resellers_price table
   while ($row = mysqli_fetch_assoc($result_resellers_price)) {
    echo '<tr>';
    echo '<td style="font-size:10px">' . $row['data_plan'] . '</td>';
    echo '<td style="font-size:10px">₦' . $row['reseller_amount'] . '</td>';
    echo '<td style="font-size:10px">' . $row['validate'] . '</td>';
    echo '</tr>';
    echo '<tr><td colspan="3"><hr></td></tr>'; // Add horizontal line after each row
}


    echo '</tbody>';
    echo '</table>';

} else {
    // Handle query execution errors
    echo 'Error executing SQL query: ' . mysqli_error($conn);
}
?>

                    </div>
                    <!-- Table Bottom -->
                    <div class="table-bottom">
                        <a class="btn" href="#">Buy Now</a>
                    </div>
                </div>
                <!-- End Single Table -->
            </div>

            <!-- Repeat the above structure for the remaining two tables -->

        </div>
    </div>
</section>
<!--/ End Pricing Table -->

		
		<!-- Start Blog Area -->
		
		<!-- End Blog Area -->
		
		<!-- Start clients -->
		<div class="clients overlay">
			<div class="container">
				<div class="row">

					<div class="col-lg-12 col-md-12 col-12">
						<span style="color:;text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);background-color:black;font-size:40px;"><center>Our Clients</center></span>
<br><br>
		
<div class="owl-carousel clients-slider">
					

		<div class="single-clients">
								<img src="/vtusite/img/client1.png"width="30%" alt="#"style="border-radius:80%">
							</div>

<div class="single-clients">
								<img src="/vtusite/images/mtn.png" alt="#"style="border-radius:80%">
							</div>
							<div class="single-clients">
								<img src="/vtusite/img/client2.png" alt="#"style="border-radius:80%;height:220px;width:90%">

							</div>
							
							<div class="single-clients">
								<img src="/vtusite/images/glo_icon.jpg" alt="#"style="border-radius:80%">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--/Ens clients -->
		
		<!-- Start Appointment -->
		<section class="appointment">
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<div class="section-title">
							<h2>We Are Always Ready to Help You.</h2>
							<img src="/vtusite/img/section-img.png" alt="#">
							<p>Wrote your complaint here</p>

						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-6 col-md-12 col-12">
						<form class="form" action="#">
							<div class="row">
								<div class="col-lg-6 col-md-6 col-12">
									<div class="form-group">
										<input name="name" type="text" placeholder="Name">
									</div>
								</div>
								<div class="col-lg-6 col-md-6 col-12">
									<div class="form-group">
										<input name="email" type="email" placeholder="Email">
									</div>
								</div>
								<div class="col-lg-6 col-md-6 col-12">
									<div class="form-group">
										<input name="phone" type="text" placeholder="Phone">
									</div>
								</div>
							
								<div class="col-lg-6 col-md-6 col-12">
									<div class="form-group">
										<input type="text" placeholder="Date" id="datepicker">
									</div>
								</div>
								<div class="col-lg-12 col-md-12 col-12">
									<div class="form-group">
										<textarea name="message" placeholder="Write Your Message Here....."></textarea>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-5 col-md-4 col-12">
									<div class="form-group">
										<div class="button">
											<button type="submit" class="btn">Submit</button>
										</div>
									</div>
								</div>
								<div class="col-lg-7 col-md-8 col-12">
									<p></p>
								</div>
							</div>
						</form>
					</div>
					<div class="col-lg-6 col-md-12 ">
						<div class="appointment-image">

						<?php
// Assuming you have established a database connection already
include_once("db_conn.php");


    $sql_reseller = "SELECT * FROM resellers WHERE reseller_id = '$reseller_id'";
    $result_reseller = mysqli_query($conn, $sql_reseller);

    // Check if query was successful
    if ($result_reseller && mysqli_num_rows($result_reseller) > 0) {
        // Fetch data from the result
        $row_reseller = mysqli_fetch_assoc($result_reseller);

        // Extract image source
        $photo = $row_reseller['photo'];

        // Construct the image URL
        $image_url = "images/" . $photo;

        // Display HTML with fetched image source
        echo '<center><img src="/vtusite/' . $image_url . '" alt="">';
?>
   


						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- End Appointment -->
		
		<!-- Start Newsletter Area -->
		<section class="newsletter section">
			<div class="container">
				<div class="row ">
					<div class="col-lg-6  col-12">
						<!-- Start Newsletter Form -->
						<div class="subscribe-text ">
							<h6>Sign up As Reseller</h6>
							<p class="">Get your own vtu site for free by submitting your email address to admin</p>
						</div>
						<!-- End Newsletter Form -->
					</div>
					<div class="col-lg-6  col-12">
						<!-- Start Newsletter Form -->
						<div class="subscribe-form ">
							<form action="mail/mail.php" method="get" target="_blank" class="newsletter-inner">
								<input name="EMAIL" placeholder="Your email address" class="common-input" onfocus="this.placeholder = ''"
									onblur="this.placeholder = 'Your email address'" required="" type="email">
								<button class="btn">Let's Go</button>
							</form>
						</div>
						<!-- End Newsletter Form -->
					</div>
				</div>
			</div>
		</section>
		<!-- /End Newsletter Area -->
		
		<!-- Footer Area -->
		<footer id="footer" class="footer ">
			<!-- Footer Top -->
			<div class="footer-top">
				<div class="container">
					<div class="row">
						<div class="col-lg-3 col-md-6 col-12">
							<div class="single-footer">
							<?php

    $sql_reseller = "SELECT * FROM resellers WHERE reseller_id = '$reseller_id'";
    $result_reseller = mysqli_query($conn, $sql_reseller);

    // Check if query was successful
    if ($result_reseller && mysqli_num_rows($result_reseller) > 0) {
        // Fetch data from the result
        $row_reseller = mysqli_fetch_assoc($result_reseller);

        // Extract information
        $about_us = $row_reseller['about_us'];
        $facebook_url = $row_reseller['facebook_url'];
        $whatsapp_url = $row_reseller['whatsapp_url'];
        $twitter_url = $row_reseller['twitter_url'];

        // Display HTML with fetched data
        echo '<h2>About Us</h2>';
        echo '<p>' . $about_us . '</p>';
        echo '<ul class="social">';
        echo '<li><a href="' . $facebook_url . '"><i class="icofont-facebook"></i></a></li>';
        echo '<li><a href="' . $whatsapp_url . '"><i class="icofont-whatsapp"></i></a></li>';
        echo '<li><a href="' . $twitter_url . '"><i class="icofont-twitter"></i></a></li>';
        echo '</ul>';
    } else {
        // Handle case where no data is found
        echo 'No data found';
   
}
?>

								</ul>
								<!-- End Social -->
							</div>
						</div>
						<div class="col-lg-3 col-md-6 col-12">
							<div class="single-footer f-link">
								<h2>Quick Links</h2>
								<div class="row">
									<div class="col-lg-6 col-md-6 col-12">
										<ul>
											<li><a href="#"><i class="fa fa-caret-right" aria-hidden="true"></i>Home</a></li>
											<li><a href="#"><i class="fa fa-caret-right" aria-hidden="true"></i>About Us</a></li>
											<li><a href="#"><i class="fa fa-caret-right" aria-hidden="true"></i>Services</a></li>
											<li><a href="user_login.php"><i class="fa fa-caret-right" aria-hidden="true"></i>login</a></li>
											<li><a href="user_register.php"><i class="fa fa-caret-right" aria-hidden="true"></i>Register</a></li>	
										</ul>
									</div>
									<div class="col-lg-6 col-md-6 col-12">
										<ul>
											
										</ul>
									</div>
								</div>
							</div>
						</div>
						
						<div class="col-lg-3 col-md-6 col-12">
							<div class="single-footer">
<h2>Address</h2>
<ul style="color:white">
<li>Adrress: 
<?php echo $row_reseller["presidential_address"]?>
</li>
<br>
<li>Country:
<?php echo $row_reseller["country"]?>
</li>
<br>
<li>Number:
<?php echo $row_reseller["phone_number"]?>
</li>
<br>
<li>Email Address:
<?php echo $row_reseller["gmail"]?>
					</li>
</ul>
</div>
							</div>
					</div>
				</div>
			</div>
			<!--/ End Footer Top -->
			<!-- Copyright -->
			<div class="copyright">
				<div class="container">
					<div class="row">
						<div class="col-lg-12 col-md-12 col-12">
							<div class="copyright-content">
								<p>© Copyright 2025 FUWA..NG  |  All Rights Reserved </p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!--/ End Copyright -->
		</footer>
		<!--/ End Footer Area -->
		<script>
// Check if the page exists
if (document.title === "404 Error: Page Not Found") {
  // Redirect to the 404 page
  window.location.replace("404.html");
}

</script>
		<!-- jquery Min JS -->


       <script src="/vtusite/js/jquery.min.js"></script>
<!-- jquery Migrate JS -->
<script src="/vtusite/js/jquery-migrate-3.0.0.js"></script>
<!-- jquery Ui JS -->
<script src="/vtusite/js/jquery-ui.min.js"></script>
<!-- Easing JS -->
<script src="/vtusite/js/easing.js"></script>
<!-- Color JS -->
<script src="/vtusite/js/colors.js"></script>
<!-- Popper JS -->
<script src="/vtusite/js/popper.min.js"></script>
<!-- Bootstrap Datepicker JS -->
<script src="/vtusite/js/bootstrap-datepicker.js"></script>
<!-- Jquery Nav JS -->
<script src="/vtusite/js/jquery.nav.js"></script>
<!-- Slicknav JS -->
<script src="/vtusite/js/slicknav.min.js"></script>
<!-- ScrollUp JS -->
<script src="/vtusite/js/jquery.scrollUp.min.js"></script>
<!-- Niceselect JS -->
<script src="/vtusite/js/niceselect.js"></script>
<!-- Tilt Jquery JS -->
<script src="/vtusite/js/tilt.jquery.min.js"></script>
<!-- Owl Carousel JS -->
<script src="/vtusite/js/owl-carousel.js"></script>
<!-- counterup JS -->
<script src="/vtusite/js/jquery.counterup.min.js"></script>
<!-- Steller JS -->
<script src="/vtusite/js/steller.js"></script>
<!-- Wow JS -->
<script src="/vtusite/js/wow.min.js"></script>
<!-- Magnific Popup JS -->
<script src="/vtusite/js/jquery.magnific-popup.min.js"></script>
<!-- Counter Up CDN JS -->
<script src="http://cdnjs.cloudflare.com/ajax/libs/waypoints/2.0.3/waypoints.min.js"></script>
<!-- Bootstrap JS -->
<script src="/vtusite/js/bootstrap.min.js"></script>
<!-- Main JS -->
<script src="/vtusite/js/main.js"></script>

    </body>
</html>
<?php
 } else {
        // Handle case where no data is found
        echo 'No data found';
  }

?>




















