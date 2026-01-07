<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}

// Auto logout after 50 minutes (300 seconds) of inactivity
$timeout = 50 * 60;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    $_SESSION = [];
    session_destroy();
    header('Location: ../login.php');
    exit;
}
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Project Form</title>
    <link rel="icon" type="image/png" href="../fi-snsuxx-php-logo.jpg">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e8f5e9, #ffffff);
            margin: 0;
            min-height: 100vh;
        }
        
        .main-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 24px 16px 80px;
        }
        
        .main-wrapper > div {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            padding: 28px 24px;
            width: 100%;
            max-width: 600px;
        }
        
        form h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
        }
        
        form h2 {
            font-size: 0.95rem;
            margin-bottom: 16px;
            color: #111827;
            font-weight: 600;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        form h2 input[type="text"],
        form h2 input[type="date"],
        form h2 select,
        form h2 textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #f9fafb;
            font-size: 0.95rem;
            font-family: Arial, sans-serif;
            transition: border-color 0.2s, background 0.2s;
        }
        
        form h2 input[type="text"]:focus,
        form h2 input[type="date"]:focus,
        form h2 select:focus,
        form h2 textarea:focus {
            outline: none;
            border-color: #68A691;
            background: #ffffff;
        }
        
        form h2 select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23111827' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 18px;
            padding-right: 40px;
        }
        
        form h2 select option[disabled] {
            color: #9ca3af;
        }
        
        form h2 textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        button[type="submit"] {
            background: #68A691;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 1.05rem;
            font-weight: 600;
            margin-top: 24px;
            transition: background 0.2s ease, transform 0.15s ease, box-shadow 0.15s ease;
        }
        
        button[type="submit"]:hover {
            background: #4a8970;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(104, 166, 145, 0.3);
        }
        
        button[type="submit"]:active {
            transform: translateY(0);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }
            
            .main-wrapper {
                padding: 20px 12px 60px;
            }
            
            .main-wrapper > div {
                padding: 20px 16px;
            }
            
            form h1 {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding-top: 100px;  /* more space for wrapped header on mobile */
            }
            
            .main-wrapper {
                padding: 16px 12px 50px;
            }
            
            .main-wrapper > div {
                padding: 16px 12px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            }
            
            form h1 {
                font-size: 1.3rem;
                margin-bottom: 16px;
            }
            
            form h2 {
                font-size: 0.9rem;
            }
            
            button[type="submit"] {
                font-size: 1rem;
                padding: 10px;
            }
        }
    </style>
    
</head>

<body>
    <?php
    $pageTitle = 'Project Form';
    $showExport = false;
    include '../header.php';
    ?>

    <div class="main-wrapper">
        <div>
            <form method="POST" action="send.php">
                <h1>Project Form</h1>

                <h2>Department ID:
                    <input type="text" name="department_id"
                        placeholder="Enter Department ID (departments.id)"
                        maxlength="100" required>
                </h2>

                <h2>Project Name:
                    <input type="text" name="pname" placeholder="Project Name" required>
                </h2>
                <h2>Client / Company Name:
                    <input type="text" name="cname" placeholder="Client / Company Name" required>
                </h2>
                <h2>Project Manager Name:
                    <input type="text" name="pmanager" pattern="[A-Za-z\s]+"
                        title="Only letters and spaces allowed"
                        placeholder="Project Manager Name" required>
                </h2>
                <h2>Start Date:
                    <input type="date" name="sdate" required>
                </h2>
                <h2>End Date / Deadline:
                    <input type="date" name="edate" required>
                </h2>
                <h2>Project Status:
                    <select name="status" required>
                        <option disabled selected>--Select--</option>
                        <option>Planning</option>
                        <option>In Progress</option>
                        <option>On Hold</option>
                        <option>Completed</option>
                    </select>
                </h2>
                <h2>Description:
                    <textarea name="pdescription" placeholder="Description"></textarea>
                </h2>

                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>

</html>