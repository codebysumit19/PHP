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
    header('Location: form.php');
    exit;
}

$pname        = trim($_POST['pname'] ?? '');
$cname        = trim($_POST['cname'] ?? '');
$pmanager     = trim($_POST['pmanager'] ?? '');
$sdate        = trim($_POST['sdate'] ?? '');
$edate        = trim($_POST['edate'] ?? '');
$status       = trim($_POST['status'] ?? '');
$pdescription = trim($_POST['pdescription'] ?? '');

// required
if ($pname === '' || $cname === '' || $pmanager === '' ||
    $sdate === '' || $edate === '' || $status === '') {
    die('All required fields must be filled correctly.');
}

// simple date format check
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sdate) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $edate)) {
    die('Invalid date format.');
}

$stmt = $conn->prepare(
    "INSERT INTO projects (pname, cname, pmanager, sdate, edate, status, pdescription)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "sssssss",
    $pname, $cname, $pmanager, $sdate, $edate, $status, $pdescription
);

if ($stmt->execute()) {
    header("Location: ../submit.php");
    exit;
} else {
    echo "Error saving project.";
}
$stmt->close();
$conn->close();
