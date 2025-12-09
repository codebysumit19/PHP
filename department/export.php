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

$sql    = "SELECT * FROM departments";
$result = $conn->query($sql);

// CSV headers (must be before any output)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="departments.csv"');

// open output stream
$output = fopen('php://output', 'w');

// optional: UTF-8 BOM for Excel
// fwrite($output, "\xEF\xBB\xBF");

// header row
fputcsv($output, [
    'Department ID',
    'Department Name',
    'Email',
    'Contact Number',
    'Number of Employees',
    'Department Responsibilities',
    'Annual Budget',
    'Department Status',
    'Description'
]);

// data rows
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['dname'],
            $row['email'],
            $row['number'],
            $row['nemployees'],
            $row['resp'],
            $row['budget'],
            $row['status'],
            $row['description'],
        ]);
    }
}

fclose($output);
$conn->close();
exit;
