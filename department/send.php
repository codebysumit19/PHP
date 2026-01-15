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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize inputs
    $department_id = htmlspecialchars(trim($_POST['department_id'] ?? ''), ENT_QUOTES, 'UTF-8');
    $dname = htmlspecialchars(trim($_POST['dname'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
    $country_code = htmlspecialchars(trim($_POST['country_code'] ?? '+91'), ENT_QUOTES, 'UTF-8');
    $number = htmlspecialchars(trim($_POST['number'] ?? ''), ENT_QUOTES, 'UTF-8');
    $nemployees = intval($_POST['nemployees'] ?? 0);
    $resp = htmlspecialchars(trim($_POST['resp'] ?? ''), ENT_QUOTES, 'UTF-8');
    $budget = htmlspecialchars(trim($_POST['budget'] ?? ''), ENT_QUOTES, 'UTF-8');
    $status = htmlspecialchars(trim($_POST['status'] ?? ''), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');

    // Basic required fields validation
    if (
        $department_id === '' || $dname === '' || $email === '' || $number === '' ||
        $resp === '' || $budget === '' || $status === '' || $description === ''
    ) {

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

    // Contact Number validation - exactly 10 digits, numeric only
    if (!ctype_digit($number)) {
        $_SESSION['error_field'] = 'number';
        $_SESSION['error_message'] = 'Contact Number must contain only digits!';
        $_SESSION['form_data'] = $_POST;
        header('Location: form.php');
        exit;
    }

    if (strlen($number) !== 10) {
        $_SESSION['error_field'] = 'number';
        $_SESSION['error_message'] = 'Contact Number must be exactly 10 digits!';
        $_SESSION['form_data'] = $_POST;
        header('Location: form.php');
        exit;
    }

    // Number of Employees validation
    if ($nemployees < 1) {
        $_SESSION['error_field'] = 'nemployees';
        $_SESSION['error_message'] = 'Number of Employees must be at least 1!';
        $_SESSION['form_data'] = $_POST;
        header('Location: form.php');
        exit;
    }

    try {
        $stmt = $conn->prepare(
            "INSERT INTO departments (department_id, dname, email, country_code, number, resp, budget, status, description) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        // Changed from 9 to 8 parameters (removed nemployees)
        $stmt->bind_param("sssssssss", $department_id, $dname, $email, $country_code, $number, $resp, $budget, $status, $description);

        $stmt->execute();
        $stmt->close();
        $conn->close();

        header("Location: get.php");
        exit;
    } catch (mysqli_sql_exception $e) {
        $conn->close();

        // Check for duplicate entry errors
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $errorField = '';
            $errorMessage = '';

            if (
                strpos($e->getMessage(), "for key 'PRIMARY'") !== false ||
                strpos($e->getMessage(), "'department_id'") !== false
            ) {
                $errorField = 'department_id';
                $errorMessage = "Department ID already exists!";
            } elseif (strpos($e->getMessage(), "'dname'") !== false) {
                $errorField = 'dname';
                $errorMessage = "Department Name already exists!";
            } elseif (strpos($e->getMessage(), "'email'") !== false) {
                $errorField = 'email';
                $errorMessage = "Email already exists!";
            } elseif (strpos($e->getMessage(), "'number'") !== false) {
                $errorField = 'number';
                $errorMessage = "Contact Number already exists!";
            } else {
                $errorField = 'general';
                $errorMessage = "Duplicate entry found!";
            }

            $_SESSION['error_field'] = $errorField;
            $_SESSION['error_message'] = $errorMessage;
            $_SESSION['form_data'] = $_POST;

            header("Location: form.php");
            exit;
        } else {
            $_SESSION['error_field'] = 'general';
            $_SESSION['error_message'] = "Database error occurred. Please try again.";
            $_SESSION['form_data'] = $_POST;
            header("Location: form.php");
            exit;
        }
    }
}
