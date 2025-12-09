<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:Arial,sans-serif;background:linear-gradient(135deg,#e8f5e9,#fff);display:flex;justify-content:center;align-items:center;height:100vh}
        .card{background:#fff;border-radius:8px;padding:30px;box-shadow:0 4px 8px rgba(0,0,0,0.1);width:100%;max-width:400px}
        h1{text-align:center;font-size:28px;margin-bottom:20px;color:#333}
        h3{font-size:16px;margin-bottom:8px;color:#555}
        input[type="text"],input[type="email"],input[type="password"]{width:100%;padding:10px;margin-bottom:15px;border:1px solid #ccc;border-radius:5px;font-size:16px;color:#333}
        input:focus{border-color:#3498db;outline:none}
        button{width:100%;padding:12px;background:#3498db;color:#fff;border:none;border-radius:5px;font-size:18px;cursor:pointer}
        button:hover{background:#2980b9}
        a{color:#3498db;text-decoration:none}
        a:hover{text-decoration:underline}
        .footer-text{text-align:center;margin-top:15px}
    </style>
</head>
<body>
<div class="card">
    <h1>Sign Up</h1>
    <form method="POST" action="sign.php">
        <h3>Username:</h3>
        <input type="text" name="userName" pattern="[A-Za-z\s]+" title="Only letters and spaces allowed" placeholder="Username" required>
        <h3>Email:</h3>
        <input type="email" name="email" placeholder="Email" required>
        <h3>Password:</h3>
        <input type="password" name="password" minlength="6" title="At least 6 characters" placeholder="Password" required>
        <h3>Confirm Password:</h3>
        <input type="password" name="confirm_password" minlength="6" title="At least 6 characters" placeholder="Confirm Password" required>
        <button type="submit">Sign Up</button>
    </form>
    <div class="footer-text">
        Already have an account? <a href="submit.php">Login here</a>
    </div>
</div>
</body>
</html>
