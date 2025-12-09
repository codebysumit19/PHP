<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invalid Credentials</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{
            font-family:Arial,sans-serif;
            background:linear-gradient(135deg,#fee2e2,#ffffff);
            display:flex;justify-content:center;align-items:center;
            height:100vh;
        }
        .card{
            background:#fff;padding:30px;border-radius:10px;
            box-shadow:0 8px 24px rgba(0,0,0,0.12);
            text-align:center;max-width:400px;width:90%;
        }
        h1{color:#b91c1c;margin-bottom:10px}
        p{color:#4b5563;margin-bottom:20px}
        a{
            display:inline-block;padding:10px 18px;border-radius:8px;
            background:#3b82f6;color:#fff;text-decoration:none;
        }
        a:hover{background:#2563eb}
    </style>
</head>
<body>
<div class="card">
    <h1>Invalid Login</h1>
    <p>Email and password are incorrect. Please try again.</p>
    <a href="login.php">Back to Login</a>
</div>
</body>
</html>
