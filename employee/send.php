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

$ename        = trim($_POST['ename'] ?? '');
$dob          = trim($_POST['dob'] ?? '');
$gender       = trim($_POST['gender'] ?? '');
$email        = trim($_POST['email'] ?? '');
$pnumber      = trim($_POST['pnumber'] ?? '');
$address      = trim($_POST['address'] ?? '');
$designation  = trim($_POST['designation'] ?? '');
$salary       = trim($_POST['salary'] ?? '');
$joining_date = trim($_POST['joining_date'] ?? '');
$aadhar       = trim($_POST['aadhar'] ?? '');

// basic required checks
if ($ename === '' || $dob === '' || $gender === '' || $email === '' ||
    $pnumber === '' || $address === '' || $designation === '' ||
    $salary === '' || $joining_date === '') {
    die('All required fields must be filled correctly.');
}

// email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email format.');
}

// phone length
if (strlen($pnumber) < 10 || strlen($pnumber) > 13) {
    die('Phone number length must be between 10 and 13 characters.');
}

// salary numeric
if (!is_numeric($salary)) {
    die('Salary must be numeric.');
}

$stmt = $conn->prepare(
    "INSERT INTO employees
     (ename, dob, gender, email, pnumber, address, designation, salary, joining_date, aadhar)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "ssssssssss",
    $ename, $dob, $gender, $email, $pnumber,
    $address, $designation, $salary, $joining_date, $aadhar
);

if ($stmt->execute()) {
    header("Location: ../submit.php");
    exit;
} else {
    echo "Error saving employee.";
}
$stmt->close();
$conn->close();
