<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}
require_once '../db.php';

$sql    = "SELECT * FROM employees";
$result = $conn->query($sql);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="employees.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, [
    'Employee ID','Full Name','Date of Birth','Gender','Email',
    'Phone Number','Address','Designation','Salary','Date of Joining','Aadhar / ID'
]);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row["id"],
            $row["ename"],
            $row["dob"],
            $row["gender"],
            $row["email"],
            $row['pnumber'],
            $row["address"],
            $row["designation"],
            $row["salary"],
            $row["joining_date"],
            $row['aadhar']
        ]);
    }
}
fclose($output);
$conn->close();
exit;
