<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Ormawa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <div class="login-container">
        <h2>Login ORMAWA</h2>
        <form id="loginForm" method="POST" action="ceklogin.php" onsubmit="return validateForm()">
            <input type="text" name="username" id="username" placeholder="Username" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p id="errorMessage" class="error-message">
            <a href="registrasi.php">Klik untuk Registrasi</a></p>
    </div>
    <script src="script.js"></script>
</body>
</html>