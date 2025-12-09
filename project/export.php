<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}
require_once '../db.php';

$sql    = "SELECT * FROM projects";
$result = $conn->query($sql);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="projects.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, [
    'Project ID','Project Name','Client / Company Name','Project Manager',
    'Start Date','End Date / Deadline','Project Status','Description'
]);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['pname'],
            $row['cname'],
            $row['pmanager'],
            $row['sdate'],
            $row['edate'],
            $row['status'],
            $row["pdescription"]
        ]);
    }
}
fclose($output);
$conn->close();
exit;
