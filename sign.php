<?php
session_start();
require_once 'db.php';

$signupError = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ---- Captcha check (SIGN UP) ----
$okCaptcha = false;
if (isset($_POST['signup_captcha'], $_SESSION['signup_captcha_answer'])) {
    $userAns = (int)$_POST['signup_captcha'];
    if ($userAns === (int)$_SESSION['signup_captcha_answer']) {
        $okCaptcha = true;
    }
}
unset($_SESSION['signup_captcha_answer'], $_SESSION['signup_captcha_question']);

if (!$okCaptcha) {
    // save error and old form values in session
    $_SESSION['signup_error'] = 'Captcha is incorrect. Please try again.';
    $_SESSION['signup_old'] = [
        'userName' => $_POST['userName'] ?? '',
        'email'    => $_POST['email'] ?? '',
    ];
    $conn->close();
    header('Location: index.php');
    exit;
}

// ---- Existing validation and insert code ----
$user    = trim($_POST['userName'] ?? '');
$email   = trim($_POST['email'] ?? '');
$pass    = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($user === '' || $email === '' || $pass === '') {
    $_SESSION['signup_error'] = 'All fields are required.';
    $_SESSION['signup_old'] = ['userName' => $user, 'email' => $email];
    $conn->close();
    header('Location: index.php');
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['signup_error'] = 'Invalid email format.';
    $_SESSION['signup_old'] = ['userName' => $user, 'email' => $email];
    $conn->close();
    header('Location: index.php');
    exit;
}
if ($pass !== $confirm) {
    $_SESSION['signup_error'] = 'Passwords do not match.';
    $_SESSION['signup_old'] = ['userName' => $user, 'email' => $email];
    $conn->close();
    header('Location: index.php');
    exit;
}

$hash = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO signup (userName, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $user, $email, $hash);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    unset($_SESSION['signup_error'], $_SESSION['signup_old']);
    header("Location: successful.php");
    exit;
} else {
    $_SESSION['signup_error'] = 'Error creating account.';
    $_SESSION['signup_old'] = ['userName' => $user, 'email' => $email];
    $stmt->close();
    $conn->close();
    header('Location: index.php');
    exit;
}
