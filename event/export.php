<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}
require_once '../db.php';

$sql    = "SELECT * FROM events";
$result = $conn->query($sql);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="events.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID','Name','Address','Date','Start Time','End Time','Type','Happened']);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'], $row['name'], $row['address'],
            $row['date'], $row['stime'], $row['etime'],
            $row['type'], $row['happend']
        ]);
    }
}
fclose($output);
$conn->close();
exit;
