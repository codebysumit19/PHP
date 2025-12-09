<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}

// Auto logout after 5 minutes (300 seconds) of inactivity
$timeout = 5 * 60; // 5 minutes

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    // too long since last activity: destroy session and go to login
    $_SESSION = [];
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit;
}

// update last activity time stamp
$_SESSION['last_activity'] = time();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Department Form</title>
<link rel="icon" type="image/png" href="../fi-snsuxx-php-logo.jpg">

<style>
*{box-sizing:border-box;margin:0;padding:0;}
html, body{
    height:100%;
}
body{
    font-family:Arial,sans-serif;
    background:linear-gradient(135deg,#e8f5e9,#ffffff);
    display:flex;
    flex-direction:column;
}
.main-wrapper{
    flex:1;
    display:flex;
    justify-content:center;
    align-items:flex-start;
    padding:20px 12px;
}
form{
    background:#fff;padding:20px;border-radius:10px;
    box-shadow:0 6px 15px rgba(0,0,0,0.1);
    width:100%;max-width:600px;max-height:80vh;overflow-y:auto;
}
h1{text-align:center;margin-bottom:16px;font-size:1.6rem;}
h2{font-size:1rem;margin-top:10px}
input[type="text"],input[type="email"],input[type="number"],
input[type="tel"],textarea{
    width:100%;padding:10px;margin-top:5px;
    border:1px solid #ccc;border-radius:6px;background:#fafafa;
    font-size:0.95rem;
}
textarea{resize:none;height:80px}
button{
    background:#4CAF50;color:#fff;border:none;padding:12px;
    border-radius:6px;cursor:pointer;width:100%;font-size:1.05rem;margin-top:20px;
}
button:hover{background:#249f60}
.bottom-nav{
    text-align:center;
    margin:15px 0;
}

/* Tablets and up */
@media (min-width: 768px){
    .main-wrapper{
        padding:40px 16px;
    }
    form{
        padding:24px;
    }
    h1{font-size:1.8rem;}
}

/* Very small phones */
@media (max-width: 480px){
    form{
        padding:16px;
        max-height:none;
        box-shadow:0 4px 10px rgba(0,0,0,0.08);
    }
    h1{font-size:1.4rem;}
    h2{font-size:0.95rem;}
    button{font-size:1rem;padding:10px;}
}
</style>

</head>
<body>

<?php
$pageTitle = 'Department Form';
$showExport = false;
include '../header.php';
?>


<div class="main-wrapper">
    
    <div>
        <form action="send.php" method="POST">
            <h1>Department Form</h1>
            <h2>Department Name:
                <input type="text" name="dname" placeholder="Enter Department" pattern="[A-Za-z\s]+"
                       title="Only letters and spaces allowed" required>
            </h2>

            <h2>Email:
                <input type="email" name="email" placeholder="Enter Email" required>
            </h2>

            <h2>Contact Number:
                <input type="tel" name="number" minlength="10" maxlength="13" placeholder="Enter Number" required>
            </h2>

            <h2>Number of Employees:
                <input type="number" name="nemployees" min="1" placeholder="Enter Total number of Employees" required>
            </h2>

            <h2>Department Responsibilities:
                <input type="text" name="resp" placeholder="Enter Responsibilities" required>
            </h2>

            <h2>Annual Budget:
                <input type="text" name="budget" value="₹" placeholder="Enter Annual Budget" required>
            </h2>

            <h2>Department Status:
                <label><input type="radio" name="status" value="Active" required> Active</label>
                <label><input type="radio" name="status" value="Inactive" required> Inactive</label>
            </h2>

            <h2>Description:
                <textarea name="description" placeholder="Write Description"></textarea>
            </h2>

            <button type="submit">Submit</button>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>

</body>
</html>
