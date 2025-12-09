<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}

// Auto logout after 5 minutes (300 seconds) of inactivity
$timeout = 5 * 60; // 5 minutes

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    // too long since last activity: destroy session and go to login
    $_SESSION = [];
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit;
}

// update last activity time stamp
$_SESSION['last_activity'] = time();


require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: form.php'); // adjust if your form file has another name
    exit;
}

$dname       = trim($_POST['dname'] ?? '');
$email       = trim($_POST['email'] ?? '');
$number      = trim($_POST['number'] ?? '');
$nemployees  = (int)($_POST['nemployees'] ?? 0);
$resp        = trim($_POST['resp'] ?? '');
$budget      = trim($_POST['budget'] ?? '');
$status      = trim($_POST['status'] ?? '');
$description = trim($_POST['description'] ?? '');

// simple required checks
if ($dname === '' || $email === '' || $number === '' || $nemployees <= 0 ||
    $resp === '' || $budget === '' || $status === '') {
    die('All required fields must be filled correctly.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email format.');
}

$stmt = $conn->prepare(
    "INSERT INTO departments (dname, email, number, nemployees, resp, budget, status, description)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "sssissss",
    $dname, $email, $number, $nemployees, $resp, $budget, $status, $description
);

if ($stmt->execute()) {
    header("Location: ../submit.php");
    exit;
} else {
    echo "Error saving department.";
}

$stmt->close();
$conn->close();
