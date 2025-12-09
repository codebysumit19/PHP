<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}

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
