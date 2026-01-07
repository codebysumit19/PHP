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
<title>Event Form</title>
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
    
    .event-form-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        padding: 28px 24px;
        width: 100%;
        max-width: 600px;
    }
    
    .event-form-title {
        text-align: center;
        margin-bottom: 20px;
        font-size: 1.75rem;
        font-weight: 700;
        color: #111827;
    }
    
    .field-group {
        margin-bottom: 16px;
    }
    
    .field-group label {
        display: block;
        font-size: 0.95rem;
        margin-bottom: 6px;
        color: #111827;
        font-weight: 600;
    }
    
    .field-group input[type="text"],
    .field-group input[type="date"],
    .field-group input[type="time"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #f9fafb;
        font-size: 0.95rem;
        transition: border-color 0.2s, background 0.2s;
    }
    
    .field-group input[type="text"]:focus,
    .field-group input[type="date"]:focus,
    .field-group input[type="time"]:focus {
        outline: none;
        border-color: #68A691;
        background: #ffffff;
    }
    
    .radio-group {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-top: 8px;
    }
    
    .radio-group label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 400;
        cursor: pointer;
    }
    
    .radio-group input[type="radio"] {
        cursor: pointer;
        width: 16px;
        height: 16px;
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
        
        .event-form-card {
            padding: 20px 16px;
        }
        
        .event-form-title {
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
        
        .event-form-card {
            padding: 16px 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        
        .event-form-title {
            font-size: 1.3rem;
            margin-bottom: 16px;
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
$pageTitle = 'Event Form';
$showExport = false;
include '../header.php';
?>

<div class="main-wrapper">
    <div class="event-form-card">
        <h1 class="event-form-title">Event Form</h1>
        <form method="POST" action="send.php">
            <div class="field-group">
                <label for="department_id">Department ID:</label>
                <input type="text" id="department_id" name="department_id"
                       maxlength="100" required>
            </div>

            <div class="field-group">
                <label for="name">Event Name:</label>
                <input type="text" id="name" name="name"
                       pattern="[A-Za-z\s]+"
                       title="Only letters and spaces allowed" required>
            </div>

            <div class="field-group">
                <label for="address">Event Address:</label>
                <input type="text" id="address" name="address" required>
            </div>

            <div class="field-group">
                <label for="date">Event Date:</label>
                <input type="date" id="date" name="date" required>
            </div>

            <div class="field-group">
                <label for="stime">Event Start Time:</label>
                <input type="time" id="stime" name="stime" required>
            </div>

            <div class="field-group">
                <label for="etime">Event End Time:</label>
                <input type="time" id="etime" name="etime" required>
            </div>

            <div class="field-group">
                <label for="type">Type of Event:</label>
                <input type="text" id="type" name="type" required>
            </div>

            <div class="field-group">
                <label>Event Happened:</label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="happend" value="Yes" required> Yes
                    </label>
                    <label>
                        <input type="radio" name="happend" value="No" required> No
                    </label>
                </div>
            </div>

            <button type="submit">Submit</button>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>
</body>
</html>
