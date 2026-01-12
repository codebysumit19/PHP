<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Submitted</title>
    <link rel="icon" type="image/png" href="fi-snsuxx-php-logo.jpg">
    <style>
        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #e8f5e9, #ffffff);
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Center only main content between header and footer */
        .main-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px 12px 28px;
        }

        .container {
            text-align: center;
            background: #fff;
            padding: 40px 50px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            max-width: 500px;
            width: 100%;
            animation: fadeIn 0.6s ease-in-out;
        }

        /* Success Icon */
        .success-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #4CAF50;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 20px;
            animation: popIn 0.5s ease-out;
        }

        .success-icon::before {
            content: "✔";
            font-size: 40px;
            color: #fff;
            font-weight: bold;
        }

        h1 {
            font-size: 2rem;
            color: #4CAF50;
            margin-bottom: 10px;
        }
        h2 {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 20px;
        }

        h3 {
            font-size: 1.2rem;
            color: #fff;
            margin-bottom: 20px;
        }

        .btn-link {
            text-decoration: none;
            font-weight: bold;
            padding: 12px 20px;
            border-radius: 8px;
            background-color: #10b981;
            color: #fff;
            display: inline-block;
            font-size: 1rem;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .btn-link:hover {
            background-color: #059669;
            transform: scale(1.05);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes popIn {
            from { transform: scale(0); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .container { padding: 30px; }
            h1 { font-size: 1.6rem; }
            h3 { font-size: 1rem; }
        }
    </style>
</head>
<body>
<?php
$pageTitle = 'Form Submitted';
$showExport = false;
include 'header.php';
?>
<div class="main-wrapper">
    <div class="container">
        <div class="success-icon"></div>
        <h1>Form Submitted Successfully</h1>
        <h2>Go to the</h2>
        <a href="link.php" class="btn-link">Home Page</a>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
