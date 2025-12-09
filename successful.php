<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        .container {
            text-align: center;
            background: #fff;
            padding: 40px 50px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            max-width: 500px;
            width: 90%;
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

        h3 {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 20px;
        }

        a {
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

        a:hover {
            background-color: #059669;
            transform: scale(1.05);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes popIn {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
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
    <div class="container">
        <div class="success-icon"></div>
        <h1>Registration Successful</h1>
        <h3>Go to the</h3>
        <a href="login.php">Login Page</a>
    </div>
</body>
</html>