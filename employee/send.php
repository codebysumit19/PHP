<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}

// Auto logout after 50 minutes
$timeout = 50 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    $_SESSION = [];
    session_destroy();
    header('Location: ../login.php');
    exit;
}
$_SESSION['last_activity'] = time();

require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: form.php');
    exit;
}

// Sanitize inputs
$department_id = htmlspecialchars(trim($_POST['department_id'] ?? ''), ENT_QUOTES, 'UTF-8');
$ename = htmlspecialchars(trim($_POST['ename'] ?? ''), ENT_QUOTES, 'UTF-8');
$dob = trim($_POST['dob'] ?? '');
$gender = htmlspecialchars(trim($_POST['gender'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$country_code = htmlspecialchars(trim($_POST['country_code'] ?? '+91'), ENT_QUOTES, 'UTF-8');
$pnumber = htmlspecialchars(trim($_POST['pnumber'] ?? ''), ENT_QUOTES, 'UTF-8');
$address = htmlspecialchars(trim($_POST['address'] ?? ''), ENT_QUOTES, 'UTF-8');
$designation = htmlspecialchars(trim($_POST['designation'] ?? ''), ENT_QUOTES, 'UTF-8');
$currency = htmlspecialchars(trim($_POST['currency'] ?? '₹'), ENT_QUOTES, 'UTF-8');
$salary = trim($_POST['salary'] ?? '');
$joining_date = trim($_POST['joining_date'] ?? '');
$aadhar = htmlspecialchars(trim($_POST['aadhar'] ?? ''), ENT_QUOTES, 'UTF-8');

// Basic validation
if ($department_id === '' || $ename === '' || $dob === '' || $gender === '' || 
    $email === '' || $pnumber === '' || $address === '' || $designation === '' ||
    $salary === '' || $joining_date === '' || $aadhar === '') {
    $_SESSION['error_field'] = 'general';
    $_SESSION['error_message'] = 'All required fields must be filled.';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

// Full Name validation
if (strlen($ename) > 100) {
    $_SESSION['error_field'] = 'ename';
    $_SESSION['error_message'] = 'Full Name cannot exceed 100 characters!';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

if (!preg_match('/^[A-Za-z\s]+$/', $ename)) {
    $_SESSION['error_field'] = 'ename';
    $_SESSION['error_message'] = 'Full Name can only contain letters and spaces!';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

// Designation validation
if (strlen($designation) > 25) {
    $_SESSION['error_field'] = 'designation';
    $_SESSION['error_message'] = 'Designation cannot exceed 25 characters!';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

if (!preg_match('/^[A-Za-z\s]+$/', $designation)) {
    $_SESSION['error_field'] = 'designation';
    $_SESSION['error_message'] = 'Designation can only contain letters and spaces!';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

// DOB validation - must be between 1950 and today
$dobTimestamp = strtotime($dob);
$minDate = strtotime('1950-01-01');
$maxDate = strtotime('today');

if ($dobTimestamp < $minDate) {
    $_SESSION['error_field'] = 'dob';
    $_SESSION['error_message'] = 'Date of Birth must be after January 1, 1950!';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

if ($dobTimestamp > $maxDate) {
    $_SESSION['error_field'] = 'dob';
    $_SESSION['error_message'] = 'Date of Birth cannot be in the future!';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_field'] = 'email';
    $_SESSION['error_message'] = 'Invalid email format!';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

// Phone validation - exactly 10 digits, numeric only
if (!ctype_digit($pnumber)) {
    $_SESSION['error_field'] = 'pnumber';
    $_SESSION['error_message'] = 'Phone Number must contain only digits!';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

if (strlen($pnumber) !== 10) {
    $_SESSION['error_field'] = 'pnumber';
    $_SESSION['error_message'] = 'Phone Number must be exactly 10 digits!';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

// Aadhar validation - exactly 12 digits, numeric only
if ($aadhar !== '' && strlen($aadhar) !== 12) {
    $_SESSION['error_field'] = 'aadhar';
    $_SESSION['error_message'] = 'Aadhar Number must be exactly 12 digits!';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

if ($aadhar !== '' && !ctype_digit($aadhar)) {
    $_SESSION['error_field'] = 'aadhar';
    $_SESSION['error_message'] = 'Aadhar Number must contain only digits!';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

// Salary validation
if (!is_numeric($salary) || $salary <= 0) {
    $_SESSION['error_field'] = 'salary';
    $_SESSION['error_message'] = 'Salary must be a positive number!';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

try {
    // Check if department exists
    $check = $conn->prepare("SELECT department_id FROM departments WHERE department_id = ?");
    $check->bind_param("s", $department_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $check->close();
        $_SESSION['error_field'] = 'department_id';
        $_SESSION['error_message'] = 'Department ID does not exist!';
        $_SESSION['form_data'] = $_POST;
        header('Location: form.php');
        exit;
    }
    $check->close();

    // Insert employee
    $stmt = $conn->prepare(
        "INSERT INTO employees
         (department_id, ename, dob, gender, email, country_code, pnumber, address, designation, currency, salary, joining_date, aadhar)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "sssssssssssss",
        $department_id, $ename, $dob, $gender, $email, $country_code, $pnumber,
        $address, $designation, $currency, $salary, $joining_date, $aadhar
    );

    $stmt->execute();
    $stmt->close();
    $conn->close();

    // Success
    header("Location: ../submit.php");
    exit;

} catch (mysqli_sql_exception $e) {
    $conn->close();
    
    // Check for duplicate entry errors
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        $errorField = '';
        $errorMessage = '';
        
        if (strpos($e->getMessage(), "'email'") !== false) {
            $errorField = 'email';
            $errorMessage = "Email already exists!";
        } elseif (strpos($e->getMessage(), "'pnumber'") !== false) {
            $errorField = 'pnumber';
            $errorMessage = "Phone Number already exists!";
        } elseif (strpos($e->getMessage(), "'aadhar'") !== false) {
            $errorField = 'aadhar';
            $errorMessage = "Aadhar Number already exists!";
        } else {
            $errorField = 'general';
            $errorMessage = "Duplicate entry found!";
        }
        
        $_SESSION['error_field'] = $errorField;
        $_SESSION['error_message'] = $errorMessage;
        $_SESSION['form_data'] = $_POST;
        
        header("Location: form.php");
        exit;
    } elseif (strpos($e->getMessage(), 'foreign key constraint') !== false) {
        $_SESSION['error_field'] = 'department_id';
        $_SESSION['error_message'] = 'Invalid Department ID!';
        $_SESSION['form_data'] = $_POST;
        header('Location: form.php');
        exit;
    } else {
        $_SESSION['error_field'] = 'general';
        $_SESSION['error_message'] = 'Database error occurred. Please try again.';
        $_SESSION['form_data'] = $_POST;
        header('Location: form.php');
        exit;
    }
}
?>
