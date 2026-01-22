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
$pname = htmlspecialchars(trim($_POST['pname'] ?? ''), ENT_QUOTES, 'UTF-8');
$cname = htmlspecialchars(trim($_POST['cname'] ?? ''), ENT_QUOTES, 'UTF-8');
$pmanager = htmlspecialchars(trim($_POST['pmanager'] ?? ''), ENT_QUOTES, 'UTF-8');
$sdate = trim($_POST['sdate'] ?? '');
$edate = trim($_POST['edate'] ?? '');
$status = htmlspecialchars(trim($_POST['status'] ?? ''), ENT_QUOTES, 'UTF-8');
$pdescription = htmlspecialchars(trim($_POST['pdescription'] ?? ''), ENT_QUOTES, 'UTF-8');

// Required fields validation
if ($department_id === '' || $pname === '' || $cname === '' || $pmanager === '' ||
    $sdate === '' || $edate === '' || $status === '' || $pdescription === '') {
    $_SESSION['error_field'] = 'general';
    $_SESSION['error_message'] = 'All required fields must be filled.';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

// Date format validation
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sdate)) {
    $_SESSION['error_field'] = 'sdate';
    $_SESSION['error_message'] = 'Invalid start date format!';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $edate)) {
    $_SESSION['error_field'] = 'edate';
    $_SESSION['error_message'] = 'Invalid end date format!';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

// Validate end date is after start date
if (strtotime($edate) < strtotime($sdate)) {
    $_SESSION['error_field'] = 'edate';
    $_SESSION['error_message'] = 'End date must be after start date!';
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

    // Insert project
    $stmt = $conn->prepare(
        "INSERT INTO projects (department_id, pname, cname, pmanager, sdate, edate, status, pdescription)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssssss", $department_id, $pname, $cname, $pmanager, $sdate, $edate, $status, $pdescription);
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
        $_SESSION['error_field'] = 'pname';
        $_SESSION['error_message'] = "Project Name already exists!";
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
