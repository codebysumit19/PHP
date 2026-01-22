<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Adjust this to your real base path
define('BASE_URL', '/PHP/');

$currentPath = $_SERVER['REQUEST_URI'];
$currentPage = basename($_SERVER['PHP_SELF']);
$headerTitle = isset($pageTitle) && $pageTitle !== '' ? $pageTitle : 'PHP CRUD Dashboard';
$userName    = $_SESSION['userName'] ?? 'User';
$userInitial = strtoupper(mb_substr($userName, 0, 1, 'UTF-8'));

$userIsAdmin = !empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
?>
<header class="site-header">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <div class="header-left">
        <a href="<?php echo BASE_URL; ?>link.php" class="logo-link">
            <div class="logo-circle">
                <img src="https://friconix.com/jpg/fi-snsuxx-php-logo.jpg" alt="PHP Logo" class="logo-img">
            </div>
        </a>
        <h3 class="header-title">
            <?php echo htmlspecialchars($headerTitle, ENT_QUOTES, 'UTF-8'); ?>
        </h3>
    </div>

    <nav class="main-nav">
        <a href="<?php echo BASE_URL; ?>link.php"
           class="nav-link <?php echo ($currentPage === 'link.php') ? 'active' : ''; ?>">
            Home
        </a>

        <div class="nav-dropdown">
            <button class="nav-dropbtn <?php echo (strpos($currentPath, '/event/') !== false) ? 'active' : ''; ?>">
                Event
            </button>
            <div class="nav-dropdown-content">
                <a href="<?php echo BASE_URL; ?>event/form.php">Event Form</a>
                <a href="<?php echo BASE_URL; ?>event/get.php">Event Data</a>
            </div>
        </div>

        <div class="nav-dropdown">
    <button class="nav-dropbtn <?php echo (strpos($currentPath, '/employee/') !== false) ? 'active' : ''; ?>">
        Employees
    </button>
    <div class="nav-dropdown-content">
        <!-- Everyone can see the form -->
        <a href="<?php echo BASE_URL; ?>employee/form.php">Employees Form</a>

        <!-- Only admin sees Data link -->
        <?php if ($userIsAdmin): ?>
            <a href="<?php echo BASE_URL; ?>employee/get.php">Employees Data</a>
        <?php endif; ?>
    </div>
</div>

        <div class="nav-dropdown">
            <button class="nav-dropbtn <?php echo (strpos($currentPath, '/department/') !== false) ? 'active' : ''; ?>">
                Department
            </button>
            <div class="nav-dropdown-content">
                <a href="<?php echo BASE_URL; ?>department/form.php">Departments Form</a>
                <a href="<?php echo BASE_URL; ?>department/get.php">Departments Data</a>
            </div>
        </div>

        <div class="nav-dropdown">
            <button class="nav-dropbtn <?php echo (strpos($currentPath, '/project/') !== false) ? 'active' : ''; ?>">
                Project
            </button>
            <div class="nav-dropdown-content">
                <a href="<?php echo BASE_URL; ?>project/form.php">Project Form</a>
                <a href="<?php echo BASE_URL; ?>project/get.php">Project Data</a>
            </div>
        </div>

        <a href="<?php echo BASE_URL; ?>privacy.php"
           class="nav-link <?php echo (strpos($currentPath, 'privacy.php') !== false) ? 'active' : ''; ?>">
            Privacy &amp; Terms
        </a>

        <a href="<?php echo BASE_URL; ?>contact.php"
           class="nav-link <?php echo (strpos($currentPath, 'contact.php') !== false) ? 'active' : ''; ?>">
            Contact
        </a>
    </nav>

    <div class="header-right">
        <div id="user-avatar" class="user-avatar">
            <?php echo htmlspecialchars($userInitial, ENT_QUOTES, 'UTF-8'); ?>
        </div>

        <div id="user-menu" class="user-menu">
            <div class="user-card-header">
                <div class="user-card-avatar">
                    <?php echo htmlspecialchars($userInitial, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div class="user-card-info">
                    <div class="user-card-name">
                        <?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?>
                        <?php if ($userIsAdmin): ?>
                            <span style="margin-left:6px;padding:2px 6px;font-size:10px;
                                         border-radius:999px;background:#f97316;color:#fff;">
                                Admin
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="user-card-subtitle">Logged in</div>
                </div>
            </div>

            <hr class="user-divider">

            <a href="<?php echo BASE_URL; ?>link.php" class="user-menu-item">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            <a href="<?php echo BASE_URL; ?>profile.php" class="user-menu-item">
                <i class="fas fa-user-circle"></i>
                <span>My Account</span>
            </a>

            <a href="<?php echo BASE_URL; ?>settings.php" class="user-menu-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>

            <hr class="user-divider">

            <a href="<?php echo BASE_URL; ?>logout.php"
               id="logout-link"
               class="user-menu-item logout-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>


    <!-- Logout confirmation -->
    <div class="logout-overlay" id="logout-overlay">
        <div class="logout-modal">
            <button type="button" class="logout-close" id="logout-close">&times;</button>
            <div class="logout-title">Log out?</div>
            <div class="logout-text">You will be signed out of your current session.</div>
            <div class="logout-actions">
                <button type="button" class="btn-secondary" id="logout-cancel">Cancel</button>
                <button type="button" class="btn-confirm" id="logout-confirm">Yes, logout</button>
            </div>
        </div>
    </div>

     <style>
        .site-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            background: #68A691;
            color: #fff;
            font-family: Arial, sans-serif;
            flex-wrap: wrap;
            gap: 10px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 180px;
        }

        .logo-link {
            text-decoration: none;
            color: white;
        }

        .logo-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo-img {
            width: 80%;
            height: 80%;
            object-fit: contain;
            border-radius: 50%;
        }

        .header-title {
            margin: 0;
            font-size: 17px;
            white-space: nowrap;
        }

        .main-nav {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            font-size: 13px;
            flex: 1;
            min-width: 220px;
        }

        /* Navigation Links */
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 4px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .nav-link:hover {
            background: #4a8970;
        }

        .nav-link.active {
            background: #3d6b5a;
        }

        /* Navigation Dropdowns */
        .nav-dropdown {
            position: relative;
            display: inline-block;
        }

        .nav-dropbtn {
            background: transparent;
            border: none;
            color: #fff;
            padding: 6px 10px;
            border-radius: 4px;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .nav-dropbtn:hover {
            background: #4a8970;
        }

        .nav-dropbtn.active {
            background: #3d6b5a;
        }

        /* Add invisible bridge to prevent gap */
        .nav-dropdown::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            height: 8px;
            background: transparent;
            display: none;
        }

        .nav-dropdown:hover::after {
            display: block;
        }

        .nav-dropdown-content {
            display: none;
            position: absolute;
            background: #fff;
            min-width: 160px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 6px;
            overflow: hidden;
            top: calc(100% + 4px);
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
        }

        .nav-dropdown-content a {
            color: #111827;
            padding: 10px 14px;
            text-decoration: none;
            display: block;
            font-size: 13px;
            transition: background 0.2s;
        }

        .nav-dropdown-content a:hover {
            background: #e5e7eb;
        }

        .nav-dropdown:hover .nav-dropdown-content {
            display: block;
        }

        .nav-dropdown:hover .nav-dropbtn {
            background: #4a8970;
        }


        .header-right {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            color: #68A691;
            cursor: pointer;
            user-select: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s;
        }

        .user-avatar:hover {
            transform: scale(1.05);
        }

        .user-menu {
            position: absolute;
            top: 50px;
            right: 0;
            min-width: 240px;
            background: #0b1120;
            color: #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 14px 35px rgba(15, 23, 42, 0.7);
            padding: 10px 8px;
            display: none;
            z-index: 999;
            border: 1px solid rgba(148, 163, 184, 0.35);
        }

        .user-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
        }

        .user-card-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            color: #ecfdf5;
            box-shadow: 0 4px 10px rgba(22, 163, 74, 0.6);
        }

        .user-card-info {
            flex: 1;
        }

        .user-card-name {
            font-size: 14px;
            font-weight: 600;
            color: #f9fafb;
        }

        .user-card-subtitle {
            font-size: 11px;
            color: #9ca3af;
        }

        .user-divider {
            border: 0;
            height: 1px;
            margin: 8px 0;
            background: linear-gradient(to right, transparent, #4b5563, transparent);
        }

        .user-menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            color: #e5e7eb;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .user-menu-item i {
            width: 18px;
            font-size: 14px;
            color: #9ca3af;
        }

        .user-menu-item:hover {
            background: #1f2937;
            color: #f9fafb;
        }

        .user-menu-item:hover i {
            color: #60a5fa;
        }

        .logout-item {
            color: #fca5a5;
        }

        .logout-item i {
            color: #ef4444;
        }

        .logout-item:hover {
            background: #7f1d1d;
            color: #fef2f2;
        }

        .logout-item:hover i {
            color: #fef2f2;
        }

        .logout-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .logout-modal {
            background: #0b1120;
            color: #e5e7eb;
            padding: 20px 24px;
            border-radius: 14px;
            min-width: 280px;
            max-width: 340px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.5);
            animation: fadeIn 0.2s ease-out;
            position: relative;
        }

        .logout-close {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: none;
            background: transparent;
            color: #9ca3af;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .logout-close:hover {
            background: #1f2937;
            color: #e5e7eb;
        }

        .logout-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #f9fafb;
        }

        .logout-text {
            font-size: 14px;
            color: #9ca3af;
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .logout-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn-secondary {
            padding: 8px 16px;
            border-radius: 6px;
            border: 1px solid #4b5563;
            background: #1f2937;
            color: #e5e7eb;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background: #374151;
            transform: translateY(-1px);
        }

        .btn-confirm {
            padding: 8px 16px;
            border-radius: 6px;
            border: 1px solid #ef4444;
            background: #dc2626;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-confirm:hover {
            background: #b91c1c;
            transform: translateY(-1px);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .main-nav {
                font-size: 11px;
                gap: 8px;
            }

            .header-title {
                font-size: 15px;
            }
        }

        @media (max-width: 480px) {
            .site-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .main-nav {
                width: 100%;
                justify-content: flex-start;
            }

            .user-menu {
                right: 0;
                left: auto;
            }
        }
    </style>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const avatar     = document.getElementById('user-avatar');
    const menu       = document.getElementById('user-menu');
    const overlay    = document.getElementById('logout-overlay');
    const logoutLink = document.getElementById('logout-link');
    const btnConfirm = document.getElementById('logout-confirm');
    const btnCancel  = document.getElementById('logout-cancel');
    const btnClose   = document.getElementById('logout-close');

    // Toggle user menu
    if (avatar && menu) {
        avatar.addEventListener('click', function (e) {
            e.stopPropagation();
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        });

        document.addEventListener('click', function (e) {
            if (!menu.contains(e.target) && e.target !== avatar) {
                menu.style.display = 'none';
            }
        });

        menu.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    function hideOverlay() {
        if (overlay) overlay.style.display = 'none';
        if (menu)    menu.style.display    = 'none';
    }

    // Show logout confirmation
    if (logoutLink && overlay) {
        logoutLink.addEventListener('click', function (e) {
            e.preventDefault();
            overlay.style.display = 'flex';
        });
    }

    if (btnCancel) btnCancel.addEventListener('click', hideOverlay);
    if (btnClose)  btnClose.addEventListener('click', hideOverlay);

    // Confirm logout
    if (btnConfirm && logoutLink) {
        btnConfirm.addEventListener('click', function () {
            window.location.href = logoutLink.href;
        });
    }

    // Close overlay on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') hideOverlay();
    });

    // Close overlay on outside click
    if (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === this) hideOverlay();
        });
    }
});
</script>
