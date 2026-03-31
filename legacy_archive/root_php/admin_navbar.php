<img src="" alt="">           <?php
           session_start();

include_once ("db_conn.php");

           if(!isset($_SESSION["email"]))
{
    
    header("location:log in.php");
}
           ?>      
  <!DOCTYPE html>
<html>
<head>
    <meta name="theme-color"content="darkblue">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Dashboard</title>
	
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
 </head>    
 
   <!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
  font-family: "Lato", sans-serif;
}

.sidenav {
  height: 100%;
  width: 0;
  position: fixed;
  z-index: 1;
  top: 0;
  left: 0;
  background-color: #;
  background: linear-gradient(-45deg, rgb(42, 32, 172), rgb(14, 77, 26));
  overflow-x: hidden;
  transition: 0.5s;
  padding-top: 60px;
}

.sidenav a {
  padding: 8px 8px 8px 32px;
  text-decoration: none;
  font-size: 20px;
  color: black;
  display: block;
  transition: 0.3s;
}

.sidenav a:hover {
  color: #f1f1f1;
}

.sidenav .closebtn {
  position: absolute;
  top: 0;
  right: 25px;
  font-size: 36px;
  margin-left: 50px;
}

@media screen and (max-height: 450px) {
  .sidenav {padding-top: 15px;}
  .sidenav a {font-size: 18px;}
}
.container11{
   background: linear-gradient(-666deg, rgb(4, 32, 172), rgb(14, 77, 26)); 
  width:100%;
  margin-top:-0.6%;
  height:6%;
  left:-0.2%;
  position:fixed;
 
}
t{
    color:green;
    font-size:23px;
    margin-top:60%;
    
    
}
</style>
</head>
<body>

<div id="mySidenav" class="sidenav">
  <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
  <img src="aa.png"width="80%;height:%;margin-top:90%">
  <?php echo $_SESSION["email"];?>
  <a href="admin_dashboard.php" class="fa fa-dashboard"> Dashboard</a>
   <a href="users_list.php" class="fa fa-users"> User Management</a>
   <a href="price.php" class="fa fa-list"> Price List</a>
   <a href="admin_user_balance_list.php" class="fa fa-money-bill"> User Balances</a>
   <a href="admin_audit_logs.php" class="fa fa-history"> Audit Logs</a>
  
  <a href="#"class="fa fa-print""> Print Data Card"</a>
  
  <a href="#"></a>
  <hr>
   <a href="logout.php"class="fa fa-user">  logout</a>
</div>
<div class="container11"> 
<span class="bg">
  <div class="container11">  
    <i style="font-size:30px;cursor:pointer" onclick="openNav()"><i>
        
     <div class="container11">  <strong><a href="#" style="position:fixed;font-size:31px;color:white; margin-top:3%"class="fa fa-bars"></strong></a></span>
     
<center><t><strong>GTECH ADMIN</strong></t></center>
 <a href="admin_profile.php">
    <img src="aa.png"onclick="alert()"style="width:13%;
  overflow:auto;position:fixed;
  height:5%;margin-top:-6%;margin-left:87%;margin-right:31%"<br><tt style="color:black;font-size:11px"></a></t></a></tt></div>    
    </span>

<script>
function openNav() {
  document.getElementById("mySidenav").style.width = "250px";
}

function closeNav() {
  document.getElementById("mySidenav").style.width = "0";
}
</script>
<script>
    function alert();
    document.getElementById("alert") Swal.fire({ title: 'Do you want to save the changes?', showDenyButton: true, showCancelButton: true, confirmButtonText: 'Save', denyButtonText: `Don't save`, }).then((result) => { /* Read more about isConfirmed, isDenied below */ if (result.isConfirmed) { Swal.fire('Saved!', '', 'success') } else if (result.isDenied) { Swal.fire('Changes are not saved', '', 'info') } })
    </script>
</body>
</html> 
         

			</span></div></i></i></tt></tt>
		</st>
	<br><br></div>


 
