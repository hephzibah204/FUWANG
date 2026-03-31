<?php

include_once("db_conn.php");
session_start(); // Start the session
session_destroy(); // Destroy all session data
header("Location: admin_login.php"); // Redirect to the login page
exit; // Ensure script execution stops after redirection


?>
