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
$pnumber = htmlspecialchars(trim($_POST['pnumber'] ?? ''), ENT_QUOTES, 'UTF-8');
$address = htmlspecialchars(trim($_POST['address'] ?? ''), ENT_QUOTES, 'UTF-8');
$designation = htmlspecialchars(trim($_POST['designation'] ?? ''), ENT_QUOTES, 'UTF-8');
$salary = trim($_POST['salary'] ?? '');
$joining_date = trim($_POST['joining_date'] ?? '');
$aadhar = htmlspecialchars(trim($_POST['aadhar'] ?? ''), ENT_QUOTES, 'UTF-8');

// Basic validation
if ($department_id === '' || $ename === '' || $dob === '' || $gender === '' || 
    $email === '' || $pnumber === '' || $address === '' || $designation === '' ||
    $salary === '' || $joining_date === '') {
    $_SESSION['error_field'] = 'general';
    $_SESSION['error_message'] = 'All required fields must be filled.';
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

// Phone validation
if (strlen($pnumber) < 10 || strlen($pnumber) > 13) {
    $_SESSION['error_field'] = 'pnumber';
    $_SESSION['error_message'] = 'Phone number must be between 10 and 13 digits!';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

// Salary validation
if (!is_numeric($salary)) {
    $_SESSION['error_field'] = 'salary';
    $_SESSION['error_message'] = 'Salary must be numeric!';
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
         (department_id, ename, dob, gender, email, pnumber, address, designation, salary, joining_date, aadhar)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "sssssssssss",
        $department_id, $ename, $dob, $gender, $email, $pnumber,
        $address, $designation, $salary, $joining_date, $aadhar
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
        header("Location: form.php");
        exit;
    } else {
        $_SESSION['error_field'] = 'general';
        $_SESSION['error_message'] = 'Database error occurred. Please try again.';
        $_SESSION['form_data'] = $_POST;
        header("Location: form.php");
        exit;
    }
}
?>
