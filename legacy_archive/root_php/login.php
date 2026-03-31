<?php
session_start();
include_once("db_conn.php");

if (isset($_POST["login"])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to fetch user details based on email
    $query = "SELECT id, email, fullname, reseller_id, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Email exists, fetch user details
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['reseller_id'] = $user['reseller_id'];

            // Update user online status to "online"
            updateUserOnlineStatus($user['id'], "online");

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            // Incorrect password
            $error = "Incorrect email or password";
        }
    } else {
        // Email not found
        $error = "Incorrect email or password";
    }

    $stmt->close();
}

function updateUserOnlineStatus($user_id, $status) {
    global $conn;
    $query = "UPDATE users SET online_status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $user_id);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
    <form action="#" method="post">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" name="login" value="Login">
    </form>
</body>
</html>
