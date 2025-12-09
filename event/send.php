<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: form.php');
    exit;
}

$name    = trim($_POST['name'] ?? '');
$address = trim($_POST['address'] ?? '');
$date    = trim($_POST['date'] ?? '');
$stime   = trim($_POST['stime'] ?? '');
$etime   = trim($_POST['etime'] ?? '');
$type    = trim($_POST['type'] ?? '');
$happend = trim($_POST['happend'] ?? '');

// required
if ($name === '' || $address === '' || $date === '' || $stime === '' ||
    $etime === '' || $type === '' || $happend === '') {
    die('All required fields must be filled correctly.');
}

// simple date format check
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    die('Invalid date format.');
}

$stmt = $conn->prepare(
    "INSERT INTO events (name, address, date, stime, etime, type, happend)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("sssssss", $name, $address, $date, $stime, $etime, $type, $happend);

if ($stmt->execute()) {
    header("Location: ../submit.php");
    exit;
} else {
    echo "Error saving event.";
}
$stmt->close();
$conn->close();
