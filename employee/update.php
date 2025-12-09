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



require_once '../db.php';

if (!isset($_GET['id'])) {
    die("No employee ID provided.");
}
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
if (!$row) { die("Employee not found!"); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Employee</title>
<link rel="icon" type="image/png" href="../fi-snsuxx-php-logo.jpg">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
html, body{height:100%;}
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
    padding:20px 0;
}
form{
    background:#fff;padding:25px;border-radius:10px;
    box-shadow:0 6px 15px rgba(0,0,0,0.1);
    width:100%;max-width:500px;max-height:80vh;overflow-y:auto;
}
h1{text-align:center;margin-bottom:20px}
h2{font-size:1.1em;margin-top:10px}
input[type="text"],input[type="date"],input[type="email"],
input[type="tel"],select{
    width:100%;padding:10px;margin-top:5px;
    border:1px solid #ccc;border-radius:6px;background:#fafafa;
}
button{
    background:#2E8B57;color:#fff;border:none;padding:12px;
    border-radius:6px;cursor:pointer;width:100%;font-size:1.1em;margin-top:20px;
}
button:hover{background:#249f60}
</style>
</head>
<body>
<?php
$pageTitle = 'Update Employee Data';
$showExport = false;
include '../header.php';
?>

<div class="main-wrapper">
    <form method="POST" action="get.php">
        <h1>Update Employees Data</h1>
        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">

        <h2>Full Name:
            <input type="text" name="ename"
                   value="<?php echo htmlspecialchars($row['ename'], ENT_QUOTES, 'UTF-8'); ?>"
                   pattern="[A-Za-z\s]+" required>
        </h2>
        <h2>Date of Birth:
            <input type="date" name="dob" value="<?php echo htmlspecialchars($row['dob'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>Gender:
            <select name="gender" required>
                <option disabled>--Select--</option>
                <option value="Male"   <?php if($row['gender']==='Male')   echo 'selected'; ?>>Male</option>
                <option value="Female" <?php if($row['gender']==='Female') echo 'selected'; ?>>Female</option>
            </select>
        </h2>
        <h2>Email:
            <input type="email" name="email"
                   value="<?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>Phone Number:
            <input type="tel" name="pnumber" minlength="10" maxlength="13"
                   value="<?php echo htmlspecialchars($row['pnumber'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>Address:
            <input type="text" name="address"
                   value="<?php echo htmlspecialchars($row['address'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>Designation:
            <input type="text" name="designation"
                   value="<?php echo htmlspecialchars($row['designation'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>Salary:
            <input type="text" name="salary"
                   value="<?php echo htmlspecialchars($row['salary'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>Date of Joining:
            <input type="date" name="joining_date"
                   value="<?php echo htmlspecialchars($row['joining_date'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>Aadhar Number / ID Proof:
            <input type="text" name="aadhar"
                   value="<?php echo htmlspecialchars($row['aadhar'], ENT_QUOTES, 'UTF-8'); ?>">
        </h2>
        <button type="submit">Save Changes</button>
    </form>
</div>

<?php include '../footer.php'; ?>
</body>
</html>
