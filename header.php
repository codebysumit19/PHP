<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$headerTitle = isset($pageTitle) && $pageTitle !== ''
    ? $pageTitle
    : 'Welcome to Dashboard';

$userName    = $_SESSION['userName'] ?? 'User';
$userInitial = strtoupper(mb_substr($userName, 0, 1, 'UTF-8'));
?>
<header style="
    display:flex;align-items:center;justify-content:space-between;
    padding:10px 16px;background:#68A691;color:#FFFFFF;
    font-family:Arial, sans-serif;flex-wrap:wrap;row-gap:10px;
">
    <div style="display:flex;align-items:center;gap:12px;min-width:180px;">
        <a href="../link.php" style="text-decoration:none;color:white;display:flex;align-items:center;gap:8px;">
            <div style="
                width:40px;height:40px;border-radius:50%;
                background:#38bdf8;display:flex;align-items:center;
                justify-content:center;overflow:hidden;flex-shrink:0;
            ">
                <img src="https://friconix.com/jpg/fi-snsuxx-php-logo.jpg"
                     alt="PHP Logo"
                     style="width:70%;height:70%;object-fit:contain;display:block;">
            </div>
        </a>
        <h3 style="margin:0;font-size:17px;white-space:nowrap;">
            <?php echo htmlspecialchars($headerTitle, ENT_QUOTES, 'UTF-8'); ?>
        </h3>
    </div>

    <nav style="
        display:flex;flex-wrap:wrap;justify-content:center;
        gap:10px 14px;font-size:13px;flex:1;min-width:220px;
        text-align:center;
    ">
        <a href="../link.php"
           style="color:white;text-decoration:none;padding:4px 8px;border-radius:4px;
                  transition:background 0.15s,color 0.15s;">
            Home
        </a>
        <span style="padding:4px 8px;border-radius:4px;">Services / Products</span>
        <span style="padding:4px 8px;border-radius:4px;">Team</span>
        <span style="padding:4px 8px;border-radius:4px;">Careers</span>
        <span style="padding:4px 8px;border-radius:4px;">About</span>
        <span style="padding:4px 8px;border-radius:4px;text-align:center;">
            Privacy Policy &amp; Terms
        </span>
        <span style="padding:4px 8px;border-radius:4px;">Contact Us</span>
    </nav>

    <div style="display:flex;align-items:center;gap:10px;min-width:150px;justify-content:flex-end;position:relative;">
        <?php if (isset($showExport) && $showExport === true): ?>
            <form method="post" style="margin:0;">
                <button type="submit" name="export" class="btn-primary btn-small">
                    Export
                </button>
            </form>
        <?php endif; ?>

        <!-- Avatar trigger -->
        <div id="user-avatar"
             style="width:34px;height:34px;border-radius:50%;
                    background:#FFFFFF;display:flex;align-items:center;
                    justify-content:center;font-weight:600;font-size:16px;
                    color:#68A691;cursor:pointer;user-select:none;
                    box-shadow:0 2px 6px rgba(0,0,0,0.18);">
            <?php echo htmlspecialchars($userInitial, ENT_QUOTES, 'UTF-8'); ?>
        </div>

        <!-- Dropdown profile card -->
        <div id="user-menu">
            <div class="user-card-header">
                <div class="user-card-avatar">
                    <?php echo htmlspecialchars($userInitial, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div class="user-card-info">
                    <div class="user-card-name">
                        <?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="user-card-subtitle">
                        Logged in
                    </div>
                </div>
            </div>
            <hr class="user-card-divider">
            <!-- Logout is now a link, not a form button -->
            <a href="./logout.php" id="logout-link"
               class="btn-primary btn-small user-card-logout"
               style="display:block;text-align:center;">
                Logout
            </a>
        </div>
    </div>

    <!-- Logout confirmation modal -->
    <div class="logout-overlay" id="logout-overlay">
        <div class="logout-modal">
            <button type="button" class="logout-close" id="logout-close">&times;</button>
            <div class="logout-title">Log out?</div>
            <div class="logout-text">
                You will be signed out of your current session.
            </div>
            <div class="logout-actions">
                <button type="button" class="btn-secondary" id="logout-cancel">
                    Cancel
                </button>
                <button type="button" class="btn-primary btn-small" id="logout-confirm">
                    Yes, logout
                </button>
            </div>
        </div>
    </div>

    <style>
        @media (max-width: 768px){
            header nav span,
            header nav a{
                font-size:11px;
            }
        }
        @media (max-width: 480px){
            header{
                flex-direction:column;
                align-items:flex-start;
            }
            header > div,
            header nav{
                justify-content:flex-start;
            }
        }
        header nav a:hover,
        header nav span:hover{
            background:#3A3D3B;
            color:#787E7A;
        }

        /* Primary header buttons: Export + Logout */
        .btn-primary {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:6px;

            padding:8px 16px;
            border-radius:999px;

            border:1px solid #38bdf8;
            background:linear-gradient(135deg,#0f172a,#1f2937);
            color:#e5e7eb;

            font-size:13px;
            font-weight:600;
            letter-spacing:0.02em;
            text-transform:uppercase;

            cursor:pointer;
            text-decoration:none;

            box-shadow:0 4px 10px rgba(15,23,42,0.35);
            transition:
                background 0.22s ease,
                color 0.22s ease,
                border-color 0.22s ease,
                box-shadow 0.18s ease,
                transform 0.18s ease;
        }

        .btn-small {
            padding:6px 14px;
            font-size:12px;
        }

        .btn-primary:hover {
            background:linear-gradient(135deg,#1d4ed8,#3b82f6);
            color:#f9fafb;
            border-color:#60a5fa;
            box-shadow:0 8px 18px rgba(30,64,175,0.55);
            transform:translateY(-1px);
        }

        .btn-primary:active {
            transform:translateY(0);
            box-shadow:0 3px 8px rgba(15,23,42,0.45);
        }

        .btn-primary:focus-visible {
            outline:2px solid #38bdf8;
            outline-offset:2px;
        }

        /* User dropdown as a small profile card */
        #user-menu{
            position:absolute;
            top:48px;
            right:0;
            min-width:220px;
            background:#0b1120;
            color:#e5e7eb;
            border-radius:12px;
            box-shadow:0 14px 35px rgba(15,23,42,0.7);
            padding:10px 12px 12px;
            display:none;
            z-index:999;
            border:1px solid rgba(148,163,184,0.35);
        }

        .user-card-header{
            display:flex;
            align-items:center;
            gap:10px;
            padding:4px 2px;
        }

        .user-card-avatar{
            width:34px;
            height:34px;
            border-radius:50%;
            background:linear-gradient(135deg,#22c55e,#16a34a);
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:700;
            font-size:16px;
            color:#ecfdf5;
            box-shadow:0 4px 10px rgba(22,163,74,0.6);
        }

        .user-card-info{
            display:flex;
            flex-direction:column;
        }

        .user-card-name{
            font-size:14px;
            font-weight:600;
            color:#f9fafb;
        }

        .user-card-subtitle{
            font-size:11px;
            color:#9ca3af;
        }

        .user-card-divider{
            border:0;
            height:1px;
            margin:8px 0 10px;
            background:linear-gradient(to right,transparent,#4b5563,transparent);
        }

        .user-card-logout{
            width:100%;
            justify-content:center;
        }

        .user-card-logout:hover{
            background:linear-gradient(135deg,#b91c1c,#ef4444);
            border-color:#fecaca;
        }

        /* Logout confirmation modal */
        .logout-overlay{
            position:fixed;
            inset:0;
            background:rgba(15,23,42,0.55);
            display:none;
            align-items:center;
            justify-content:center;
            z-index:9999;
        }
        .logout-modal{
            background:#0b1120;
            color:#e5e7eb;
            padding:18px 20px 16px;
            border-radius:14px;
            min-width:260px;
            max-width:320px;
            box-shadow:0 18px 45px rgba(15,23,42,0.9);
            border:1px solid rgba(148,163,184,0.5);
            animation:logoutFadeIn 0.18s ease-out;
            position:relative;
        }
        .logout-close{
            position:absolute;
            top:8px;
            right:10px;
            width:24px;
            height:24px;
            border-radius:999px;
            border:1px solid transparent;
            background:transparent;
            color:#9ca3af;
            font-size:18px;
            line-height:1;
            cursor:pointer;
            display:flex;
            align-items:center;
            justify-content:center;
            transition:background 0.18s,color 0.18s,border-color 0.18s,transform 0.15s;
        }
        .logout-close:hover{
            background:#111827;
            border-color:#4b5563;
            color:#e5e7eb;
            transform:translateY(-1px);
        }
        .logout-title{
            font-size:16px;
            font-weight:600;
            margin-bottom:6px;
            color:#f9fafb;
        }
        .logout-text{
            font-size:13px;
            color:#9ca3af;
            margin-bottom:14px;
        }
        .logout-actions{
            display:flex;
            justify-content:flex-end;
            gap:8px;
        }
        .btn-secondary{
            padding:6px 12px;
            border-radius:999px;
            border:1px solid #4b5563;
            background:#111827;
            color:#e5e7eb;
            font-size:12px;
            cursor:pointer;
            transition:background 0.18s,color 0.18s,border-color 0.18s,transform 0.15s;
        }
        .btn-secondary:hover{
            background:#1f2937;
            border-color:#6b7280;
            transform:translateY(-1px);
        }
        @keyframes logoutFadeIn{
            from{opacity:0;transform:translateY(6px);}
            to{opacity:1;transform:translateY(0);}
        }
    </style>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var avatar   = document.getElementById('user-avatar');
    var menu     = document.getElementById('user-menu');
    var overlay  = document.getElementById('logout-overlay');
    var logoutLn = document.getElementById('logout-link');
    var btnOk    = document.getElementById('logout-confirm');
    var btnNo    = document.getElementById('logout-cancel');
    var btnClose = document.getElementById('logout-close');

    if (avatar && menu) {
        avatar.addEventListener('click', function (e) {
            e.stopPropagation();
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        });

        document.addEventListener('click', function () {
            menu.style.display = 'none';
        });

        menu.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    function hideOverlay() {
        if (overlay) overlay.style.display = 'none';
    }

    // open confirmation when clicking Logout in menu
    if (logoutLn && overlay) {
        logoutLn.addEventListener('click', function (e) {
            e.preventDefault();
            overlay.style.display = 'flex';
        });
    }

    // Cancel / X close actions
    if (btnNo)    btnNo.addEventListener('click', hideOverlay);
    if (btnClose) btnClose.addEventListener('click', hideOverlay);

    // Confirm logout -> redirect to logout.php
    if (btnOk && logoutLn) {
        btnOk.addEventListener('click', function () {
            hideOverlay();
            window.location.href = logoutLn.href;
        });
    }
});
</script>
