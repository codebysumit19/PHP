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
    *{box-sizing:border-box;margin:0;padding:0;}
    html, body{height:100%;}

    body{
        font-family:Arial,sans-serif;
        color:#111827;
        display:flex;
        flex-direction:column;
        overflow-y:scroll; /* always show vertical scrollbar */
        background:
           linear-gradient(135deg, rgba(15,23,42,0.80), rgba(15,118,110,0.75)),
           url("https://images.pexels.com/photos/3184360/pexels-photo-3184360.jpeg?auto=compress&cs=tinysrgb&w=1600") center/cover fixed no-repeat;
    }

    .main-wrapper{
        flex:1;
        display:flex;
        justify-content:center;
        align-items:stretch;
        padding:24px 12px 28px;
    }

    .dashboard-card{
        /* background:rgba(249,250,251,0.96); */
        /* backdrop-filter:blur(8px); */
        border-radius:18px;
        /* box-shadow:0 18px 45px rgba(15,23,42,0.55),
                   0 0 0 1px rgba(148,163,184,0.45); */
        padding:20px 18px 22px;
        width:100%;
        max-width:980px;
        display:flex;
        flex-direction:column;
    }

    .dashboard-title{
    font-size:1.8rem;
    font-weight:700;
    text-align:center;   /* was left */
    margin-bottom:4px;
    color:#020617;
}

    .dashboard-subtitle{
        text-align:left;
        color:#6b7280;
        margin-bottom:18px;
        font-size:0.95rem;
    }

    .dashboard-grid{
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:10px;
        width:100%;
    }
    .dashboard-item{
        background:#111827;
        color:#e5e7eb;
        border-radius:12px;
        padding:10px 8px;
        text-align:center;
        box-shadow:0 6px 16px rgba(15,23,42,0.55);
        transition:transform 0.18s ease,
                   box-shadow 0.18s ease,
                   background 0.2s ease;
        cursor:pointer;
    }
    .dashboard-item a{
        display:block;
        text-decoration:none;
        color:inherit;
        font-weight:600;
        font-size:0.9rem;
        padding:6px 4px;
        cursor:pointer;
    }

    .dashboard-item:hover{
        transform:translateY(-2px);
        box-shadow:0 10px 22px rgba(15,23,42,0.75);
        background:#1d4ed8;
    }

    @media (max-width:640px){
    .main-wrapper{padding:18px 10px 22px;}
    .dashboard-card{padding:18px 14px 20px;border-radius:14px;}
    .dashboard-title{
        text-align:center;      /* add this */
        font-size:1.5rem;
    }
    .dashboard-subtitle{
        text-align:center;      /* add this if you want subtitle centered too */
        margin-bottom:16px;
    }
    .dashboard-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;}
}

    @media (max-width:420px){
        .dashboard-grid{grid-template-columns:1fr;}
    }
    @media (min-width:1024px){
        .dashboard-title{font-size:2rem;}
        .dashboard-card{padding:24px 22px 26px;}
    }

    /* Loading overlay */
    #page-loader{
        position:fixed;
        inset:0;
        background:rgba(15,23,42,0.65);
        display:none;
        align-items:center;
        justify-content:center;
        z-index:9999;
    }
    .loader-spinner{
        width:52px;
        height:52px;
        border-radius:50%;
        border:4px solid rgba(148,163,184,0.4);
        border-top-color:#38bdf8;
        animation:spin 0.8s linear infinite;
    }
    .loader-text{
        margin-top:10px;
        font-size:13px;
        color:#e5e7eb;
    }
    @keyframes spin{
        to{transform:rotate(360deg);}
    }
    </style>
</head>
<body>
<div id="page-loader">
    <div style="text-align:center;">
        <div class="loader-spinner"></div>
        <div class="loader-text">Loading...</div>
    </div>
</div>

<?php
$pageTitle = 'Dashboard';
$showExport = false;
include 'header.php';
?>

<!-- <div class="main-wrapper">
    <div class="dashboard-card">
        <h1 class="dashboard-title">
            Welcome, <?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?>
        </h1>
        <p class="dashboard-subtitle">Choose a section to manage data</p>

        <div class="dashboard-grid">
            <div class="dashboard-item">
                <a href="event/form.php" class="nav-link">Event Form</a>
            </div>
            <div class="dashboard-item">
                <a href="employee/form.php" class="nav-link">Employees Form</a>
            </div>
            <div class="dashboard-item">
                <a href="department/form.php" class="nav-link">Departments Form</a>
            </div>
            <div class="dashboard-item">
                <a href="project/form.php" class="nav-link">Project Form</a>
            </div>

            <div class="dashboard-item">
                <a href="event/get.php" class="nav-link">Event Data</a>
            </div>
            <div class="dashboard-item">
                <a href="employee/get.php" class="nav-link">Employees Data</a>
            </div>
            <div class="dashboard-item">
                <a href="department/get.php" class="nav-link">Departments Data</a>
            </div>
            <div class="dashboard-item">
                <a href="project/get.php" class="nav-link">Project Data</a>
            </div>
        </div>
    </div>
</div> -->

<?php include 'footer.php'; ?>

<!-- <script>
document.addEventListener('DOMContentLoaded', function () {
    var links  = document.querySelectorAll('.nav-link');
    var loader = document.getElementById('page-loader');

    if (!loader) return;

    // Always hide loader on load / when coming back from history
    loader.style.display = 'none';

    // Some browsers fire pageshow from bfcache; ensure hidden there too
    window.addEventListener('pageshow', function () {
        loader.style.display = 'none';
    });

    // show loader when any dashboard link is clicked
    links.forEach(function (a) {
        a.addEventListener('click', function () {
            loader.style.display = 'flex';
        });
    });
});
</script> -->
</body>
</html>
