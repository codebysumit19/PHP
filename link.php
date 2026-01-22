<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// Auto logout after 5 minutes (300 seconds) of inactivity
$timeout = 50 * 60; // 5 minutes

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    // too long since last activity: destroy session and go to login
    $_SESSION = [];
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// update last activity time stamp
$_SESSION['last_activity'] = time();

$userName = $_SESSION['userName'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="fi-snsuxx-php-logo.jpg">
    <title>Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: Arial, sans-serif;
            color: #111827;
            display: flex;
            flex-direction: column;
            overflow-y: scroll;
            /* always show vertical scrollbar */
            background:
                linear-gradient(135deg, rgba(15, 23, 42, 0.80), rgba(15, 118, 110, 0.75)),
                url("https://images.pexels.com/photos/3184360/pexels-photo-3184360.jpeg?auto=compress&cs=tinysrgb&w=1600") center/cover fixed no-repeat;
        }

       

        @media (max-width:640px) {
            .main-wrapper {
                padding: 18px 10px 22px;
            }

            .dashboard-card {
                padding: 18px 14px 20px;
                border-radius: 14px;
            }

            .dashboard-title {
                text-align: center;
                /* add this */
                font-size: 1.5rem;
            }

            .dashboard-subtitle {
                text-align: center;
                /* add this if you want subtitle centered too */
                margin-bottom: 16px;
            }

            .dashboard-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }
        }

        @media (max-width:420px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (min-width:1024px) {
            .dashboard-title {
                font-size: 2rem;
            }

            .dashboard-card {
                padding: 24px 22px 26px;
            }
        }


   

       
    </style>
</head>

<body>

    <?php
    $pageTitle = 'Dashboard';
    $showExport = false;
    include 'header.php';
    ?>

   

    <?php include 'footer.php'; ?>

</body>

</html>