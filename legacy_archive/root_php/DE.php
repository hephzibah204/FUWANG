<?php

include_once ("db_conn.php");
if(isset($_POST["submit"]));
{
    $user_Id= $_POST['delete'];

$query = "DELETE FROM newuser where user_Id='$user_Id'";
$query_run=mysqli_query ($conn,$query);
if($query_run)

{
    
    header("location:users_list.php");}
    


$query = "DELETE FROM users_profile where user_Id='$user_Id";
$query_run=mysqli_query ($conn,$query);
if($query_run)

{
    
    header("location:users_list.php");}
    
}



;

?>
