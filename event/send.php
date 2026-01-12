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
$name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
$address = htmlspecialchars(trim($_POST['address'] ?? ''), ENT_QUOTES, 'UTF-8');
$date = trim($_POST['date'] ?? '');
$stime = trim($_POST['stime'] ?? '');
$etime = trim($_POST['etime'] ?? '');
$type = htmlspecialchars(trim($_POST['type'] ?? ''), ENT_QUOTES, 'UTF-8');
$happend = htmlspecialchars(trim($_POST['happend'] ?? ''), ENT_QUOTES, 'UTF-8');

// Required fields validation
if ($department_id === '' || $name === '' || $address === '' || $date === '' || 
    $stime === '' || $etime === '' || $type === '' || $happend === '') {
    $_SESSION['error_field'] = 'general';
    $_SESSION['error_message'] = 'All required fields must be filled.';
    $_SESSION['form_data'] = $_POST;
    header('Location: form.php');
    exit;
}

// Date format validation
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $_SESSION['error_field'] = 'date';
    $_SESSION['error_message'] = 'Invalid date format!';
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

    // Insert event
    $stmt = $conn->prepare(
        "INSERT INTO events (department_id, name, address, date, stime, etime, type, happend)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssssss", $department_id, $name, $address, $date, $stime, $etime, $type, $happend);
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
        if (strpos($e->getMessage(), "'unique_event_per_dept'") !== false || 
            strpos($e->getMessage(), "'name'") !== false) {
            $_SESSION['error_field'] = 'name';
            $_SESSION['error_message'] = "Event Name already exists in this department!";
        } else {
            $_SESSION['error_field'] = 'general';
            $_SESSION['error_message'] = "Duplicate entry found!";
        }
        
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
