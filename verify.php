<?php
require 'db.php';

if (!isset($_GET['token']) || $_GET['token'] === '') {
    echo 'Invalid verification link.';
    exit;
}

$token = $_GET['token'];

$stmt = $conn->prepare(
    "SELECT id FROM signup WHERE verify_token = ? AND is_verified = 0 LIMIT 1"
);
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    $stmt->close();

    $stmt = $conn->prepare(
        "UPDATE signup SET is_verified = 1, verify_token = NULL WHERE id = ?"
    );
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $stmt->close();

    echo 'Email verified successfully. You can now log in.';
} else {
    echo 'This verification link is invalid or already used.';
}

$conn->close();
