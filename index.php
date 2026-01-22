<?php
session_start();

$errorMsg = $_SESSION['signup_error'] ?? '';
$old      = $_SESSION['signup_old'] ?? ['userName' => '', 'email' => ''];
unset($_SESSION['signup_error'], $_SESSION['signup_old']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $a = random_int(1, 9);
    $b = random_int(1, 9);
    $_SESSION['signup_captcha_answer']   = $a + $b;
    $_SESSION['signup_captcha_question'] = "$a + $b";
}

$isCaptchaError = ($errorMsg === 'Captcha is incorrect. Please try again.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="icon" type="image/png" href="fi-snsuxx-php-logo.jpg">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{
            font-family:Arial,sans-serif;
            background:linear-gradient(135deg,#e8f5e9,#fff);
            display:flex;justify-content:center;align-items:center;
            height:100vh;
        }
        .card{
            background:#fff;border-radius:8px;padding:30px;
            box-shadow:0 4px 8px rgba(0,0,0,0.1);
            width:100%;max-width:400px;
        }
        h1{
            text-align:center;font-size:28px;margin-bottom:20px;color:#333;
        }
        h3{
            font-size:16px;margin-bottom:8px;color:#555;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"]{
            width:100%;padding:10px;margin-bottom:15px;
            border:1px solid #ccc;border-radius:5px;
            font-size:16px;color:#333;
        }
        input:focus{
            border-color:#3498db;outline:none;
        }
        a{
            color:#3498db;text-decoration:none;
        }
        a:hover{
            text-decoration:underline;
        }
        .footer-text{
            text-align:center;margin-top:15px;
        }
        .btn-signup{
            display:inline-block;
            width:100%;
            padding:12px 0;
            border-radius:5px;
            background:#3498db;
            color:#fff;
            text-decoration:none;
            font-size:18px;
            border:none;
            text-align:center;
            cursor:pointer;
            transition:background 0.2s ease;
        }
        .btn-signup:hover{
            background:#2980b9;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>Sign Up</h1>

    <?php if ($errorMsg && !$isCaptchaError): ?>
        <div style="margin-bottom:10px;padding:8px;border-radius:4px;background:#fee2e2;color:#b91c1c;font-size:14px;">
            <?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="sign.php">
        <h3>Full Name:</h3>
        <input type="text" name="userName"
               value="<?php echo htmlspecialchars($old['userName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
               pattern="[A-Za-z\s]+"
               title="Only letters and spaces allowed" placeholder="Full Name" required>

        <h3>Email:</h3>
        <input type="email" name="email"
               value="<?php echo htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="Email" required>

        <h3>Password:</h3>
        <input type="password" name="password" minlength="6"
               title="At least 6 characters" placeholder="Password" required>

        <h3>Confirm Password:</h3>
        <input type="password" name="confirm_password" minlength="6"
               title="At least 6 characters" placeholder="Confirm Password" required>

        <h3>Captcha:</h3>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
            <span>
                <strong><?php echo htmlspecialchars($_SESSION['signup_captcha_question'] ?? '', ENT_QUOTES, 'UTF-8'); ?> =</strong>
            </span>
            <input type="number"
                   name="signup_captcha"
                   required
                   style="width:80px;padding:8px;border:1px solid #ccc;border-radius:5px;">
        </div>

        <?php if ($isCaptchaError): ?>
            <div style="margin-bottom:10px;font-size:13px;color:#b91c1c;">
                <?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn-signup">Sign Up</button>
    </form>

    <div class="footer-text">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>
</body>
</html>
