<?php
include_once("db_conn.php");
session_start();

// Function to update user's online status
function updateUserOnlineStatus($id, $status) {
    global $conn;
    $query = "UPDATE customers1 SET online_status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
}

// Check if the user is logged in and their email is set in session
if (isset($_SESSION["email"])) {
    // Get the user's email from the session
    $user_email = $_SESSION["email"];

    // Get user id from session if it's set
    $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

    // Update user's online status to offline
    if ($user_id) {
        updateUserOnlineStatus($user_id, "offline");
    }

    // Unset all session variables
    session_unset();

    // Destroy the session
    session_destroy();

    // Clear the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }

    // Destroy the cookies for email and fullname
    if (isset($_COOKIE['email'])) {
        setcookie('email', '', time() - 3600, "/");  // Expire the cookie by setting it in the past
    }
    if (isset($_COOKIE['fullname'])) {
        setcookie('fullname', '', time() - 3600, "/");  // Expire the cookie by setting it in the past
    }

    // Redirect to the login page after logging out
    header("Location:user_login.php");
    exit();
} else {
    // Redirect to the login page if user is not logged in
    header("Location:user_login.php");
    exit();
}
?>