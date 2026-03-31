<?php
session_start();
include_once ("db_conn.php");
if(isset($_POST["submit"]));
{
    $id= $_POST['delete'];


$query = "DELETE FROM customers2 where id='$id'";
$query_run=mysqli_query ($conn,$query);
if($query_run)

{
    
    header("location:resellers_user_list.php");
}
}
    

?>
