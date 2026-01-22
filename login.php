<?php
session_start();
require_once 'db.php';

$errorType = ''; // '', 'email', 'password', 'both', 'captcha'

// 1) Handle POST first
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // ---- Captcha check first ----
    $captchaOk = false;
    if (isset($_POST['login_captcha'], $_SESSION['login_captcha_answer'])) {
        $userAns = (int)$_POST['login_captcha'];
        if ($userAns === (int)$_SESSION['login_captcha_answer']) {
            $captchaOk = true;
        }
    }

    if (!$captchaOk) {
        $errorType = 'captcha';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' && $password === '') {
            $errorType = 'both';            // both empty
        } elseif ($email === '') {
            $errorType = 'email';           // only email empty
        } elseif ($password === '') {
            $errorType = 'password';        // only password empty
        } else {
            // Both filled – check DB
            $stmt = $conn->prepare("SELECT userName, password, is_admin FROM signup WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['email']    = $email;
                    $_SESSION['userName'] = $user['userName'];
                    $_SESSION['is_admin'] = (int)$user['is_admin']; // 1 = admin, 0 = normal
                    $stmt->close();
                    $conn->close();
                    header("Location: link.php");
                    exit;
                } else {
                    $errorType = 'password';
                }
            } else {
                $errorType = 'email';
            }


            $stmt->close();
        }
    }
}

// 2) New captcha for current form
$a = random_int(1, 9);
$b = random_int(1, 9);
$_SESSION['login_captcha_answer']   = $a + $b;
$_SESSION['login_captcha_question'] = "$a + $b";

$conn->close();

$isEmailError    = ($errorType === 'email');
$isPasswordError = ($errorType === 'password');
$isCaptchaError  = ($errorType === 'captcha');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="fi-snsuxx-php-logo.jpg">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e8f5e9, #ffffff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333
        }

        h3 {
            font-size: 16px;
            margin-bottom: 8px;
            color: #555
        }

        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        input:focus {
            border-color: #3498db;
            outline: none
        }

        button {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
        }

        button:hover {
            background: #2980b9
        }

        a {
            color: #3498db;
            text-decoration: none
        }

        a:hover {
            text-decoration: underline
        }

        .footer-text {
            text-align: center;
            margin-top: 15px
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>Login</h1>

        <?php if ($errorType === 'both'): ?>
            <div style="margin-bottom:10px;padding:8px;border-radius:4px;background:#fee2e2;color:#b91c1c;font-size:14px;">
                Email and password are incorrect.
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <h3>Email:</h3>
            <input type="email" name="email" placeholder="Email" required>
            <?php if ($isEmailError): ?>
                <div style="margin-top:-10px;margin-bottom:10px;font-size:13px;color:#b91c1c;">
                    Email is incorrect.
                </div>
            <?php endif; ?>

            <h3>Password:</h3>
            <input type="password" name="password" minlength="6"
                title="Password must be at least 6 characters"
                placeholder="Password" required>
            <?php if ($isPasswordError): ?>
                <div style="margin-top:-10px;margin-bottom:10px;font-size:13px;color:#b91c1c;">
                    Password is incorrect.
                </div>
            <?php endif; ?>

            <h3>Captcha:</h3>
            <div style="display:flex;align-items:center;gap:8px;margin:5px 0 4px;">
                <span>
                    <strong><?php echo htmlspecialchars($_SESSION['login_captcha_question'] ?? '', ENT_QUOTES, 'UTF-8'); ?> =</strong>
                </span>
                <input type="number"
                    name="login_captcha"
                    required
                    style="width:80px;padding:8px;border:1px solid #ccc;border-radius:5px;">
            </div>
            <?php if ($isCaptchaError): ?>
                <div style="margin-bottom:10px;font-size:13px;color:#b91c1c;">
                    Captcha is incorrect. Please try again.
                </div>
            <?php else: ?>
                <div style="margin-bottom:10px;"></div>
            <?php endif; ?>

            <button type="submit" name="login">Login</button>
        </form>

        <div class="footer-text">
            Don't have an account? <a href="index.php">Sign up here</a>
        </div>
    </div>
</body>

</html>