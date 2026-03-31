<?php
include_once ("admin_navbar.php");
include_once ("db_conn.php");
if(isset($_POST["edit"]));

  
$query ="UPDATE* FROM  newuser";
$query_run=mysqli_query($conn,$query);
if(mysqli_num_rows($query_run) > 0)
{
    foreach($query_run as $row)

{
?>
 <?php
        }
        }
    else   {
        echo" <tr>no result found</tr>";
       } 
        
        
        ?>



<!DOCTYPE html>
<html>
<head>
    <meta name="theme-color"content="#15gt44">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>edit_users</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
   </head>
<body>
    <br><br><br>
       <form action="#" method="POST">
        <input type="text" name="edit"value="<?php echo $row["Id"]?>"
        
       

        </form>
       </div>
