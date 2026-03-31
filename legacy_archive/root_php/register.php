<?php
// Include configuration file
$config = include('configuress.php');

// Database connection parameters
$host = $config['db_host'];
$db   = $config['db_name'];
$user = $config['db_user'];
$pass = $config['db_password'];

// Create a new PDO instance without setting error mode
$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form inputs without sanitization or escaping
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Directly use user inputs in SQL query (highly insecure)
    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
    
    try {
        // Execute query without error handling
        $pdo->exec($sql);
        $message = 'Registration successful!';
    } catch (PDOException $e) {
        $message = 'Registration failed: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h1>Register</h1>
    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        <br>
        <input type="submit" value="Register">
    </form>

    <?php if (isset($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
</body>
</html>