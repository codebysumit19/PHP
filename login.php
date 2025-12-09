<?php
session_start();
require_once 'db.php';

$errorType = ''; // '', 'email', 'password', 'both'

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // CASE 1: both empty
    if ($email === '' && $password === '') {
        $errorType = 'both';

    // CASE 2: email empty only
    } elseif ($email === '') {
        $errorType = 'email';

    // CASE 3: password empty only
    } elseif ($password === '') {
        $errorType = 'password';

    // CASE 4: both filled – check DB
    } else {
        // IMPORTANT: select userName column as well
        $stmt = $conn->prepare("SELECT userName, password FROM signup WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // email exists
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['email']    = $email;
                $_SESSION['userName'] = $user['userName']; // <-- from DB signup.userName
                header("Location: link.php");
                exit;
            } else {
                // email correct, password wrong
                $errorType = 'password';
            }
        } else {
            // email not found AND password not empty → treat as BOTH wrong
            $errorType = 'both';
        }

        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{
            font-family:Arial,sans-serif;
            background:linear-gradient(135deg,#e8f5e9,#ffffff);
            display:flex;justify-content:center;align-items:center;
            height:100vh;
        }
        .card{
            background:#fff;padding:30px;border-radius:8px;
            box-shadow:0 4px 8px rgba(0,0,0,0.1);
            width:100%;max-width:400px;
        }
        h1{text-align:center;margin-bottom:20px;color:#333}
        h3{font-size:16px;margin-bottom:8px;color:#555}
        input[type="text"],input[type="password"]{
            width:100%;padding:10px;margin:5px 0 15px;
            border:1px solid #ccc;border-radius:5px;font-size:16px;
        }
        input:focus{border-color:#3498db;outline:none}
        button{
            width:100%;padding:12px;background:#3498db;
            color:#fff;border:none;border-radius:5px;
            font-size:18px;cursor:pointer;
        }
        button:hover{background:#2980b9}
        a{color:#3498db;text-decoration:none}
        a:hover{text-decoration:underline}
        .footer-text{text-align:center;margin-top:15px}
    </style>
</head>
<body>
<div class="card">
    <h1>Login</h1>

    <?php if ($errorType === 'email'): ?>
        <script>alert("Email is incorrect.");</script>
    <?php elseif ($errorType === 'password'): ?>
        <script>alert("Password is incorrect.");</script>
    <?php elseif ($errorType === 'both'): ?>
        <script>
            alert("Email and password are incorrect.");
            window.location.href = "invalid.php";
        </script>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <h3>Email:</h3>
        <input type="text" name="email" placeholder="Email" required>

        <h3>Password:</h3>
        <input type="password" name="password" minlength="6"
               title="Password must be at least 6 characters"
               placeholder="Password" required>

        <button type="submit" name="login">Login</button>
    </form>

    <div class="footer-text">
        Don't have an account? <a href="index.php">Sign up here</a>
    </div>
</div>
</body>
</html>
