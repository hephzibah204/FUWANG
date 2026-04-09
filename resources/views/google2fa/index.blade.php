<!DOCTYPE html>
<html>
<head>
    <title>Two-Factor Authentication</title>
</head>
<body>
    <h1>Two-Factor Authentication</h1>

    <p>Please enter the code from your authenticator app.</p>

    <form action="{{ route('admin.login') }}" method="POST">
        @csrf

        <label for="one_time_password">One-Time Password:</label>
        <input type="text" name="one_time_password" id="one_time_password" required>

        <button type="submit">Authenticate</button>
    </form>
</body>
</html>
