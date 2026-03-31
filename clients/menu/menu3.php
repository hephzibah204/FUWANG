<div id="mySidenav" class="sidenav">

 <div class="avatar">

   <?php

if(isset($_SESSION["image"]) && !empty($_SESSION["image"])) {
    // If session image is set and not empty, display it
    echo '<img style="width:100%;height:100%;border-radius:50%;border:5px solid #f1f1f1" src="/vtusite/'.$_SESSION["image"].'">';
} else {
    // If session image is not set or empty, display the default image
    echo '<img style="width:100%;height:100%;border-radius:50%;border:5px solid #f1f1f1" src="/vtusite/images/vtu1.jpg">';
}

?>


</div>



<a href="javascript:void(0)" class="closebtn"onclick="closeNav()"style="
  border-radius: 5px;
">&times;</a>

<h5 style="color:white; font-size:12px;opacity:0.6;position: absolute;left:5%;position:absolute;top:27%"><nobr><center> <?echo $fullname;?></h5><i style="right:4%;font-size:12px;color:white;opacity:0.5;top:28%;position:absolute;"></i></center></h5>

<div class="line"></div>
  <a href="#"><i class="fa fa-dashboard"></i>                  Dashboard</a>
  <a href="airtime"><i class="fa fa-wifi"></i> Data</a>
  <a href="data"><i class="fa fa-phone"></i> Airtime</a>
  <a href="verify_nin.php"><i class="fa fa-fingerprint"></i> Verify NIN</a>
 <a href="#" onclick="toggleSubMenu(event)"><i class="fas fa-history"></i> History<span id="submenu-icon">&#9660;</span></a>
  <div id="submenu" style="width:2%;margin-left:40%;display: none;">
   <center>
 <a href="history"class="fa fa-services">History</a>
    <a href="#">Pending</a>
    <a href="#">Completed</a>
  </div>
  <a href="#"><i class="fas fa-database"></i> Banner Ads </a>
  <a href="#"><i class="fas fa-user"></i> Account Settings</a>
  <div class="line"></div> <!-- Line at the bottom -->
  <a href="#"class="fa fa-code"> Developers API</a> <!-- My Profile -->
<div class="line"style="border:0.01px solid #f1f1f1;opacity:0.3"></div>
  <a href="#" class="fa fa-power-off" onclick="confirmLogout()"> Logout</a>

  <script>
    function confirmLogout() {
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, logout!'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = 'logout.php';
        }
      });
    }
  </script>
</div>
