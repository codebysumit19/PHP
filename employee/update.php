<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}

// Auto logout after 50 minutes of inactivity
$timeout = 50 * 60;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    $_SESSION = [];
    session_destroy();
    header('Location: ../login.php');
    exit;
}
$_SESSION['last_activity'] = time();

require_once '../db.php';

if (!isset($_GET['id'])) {
    die("No employee ID provided.");
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) { 
    die("Employee not found!"); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Employee</title>
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
    min-height: 100vh;
}

.main-wrapper {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 24px 16px 80px;
}

.form-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    padding: 28px 24px;
    width: 100%;
    max-width: 600px;
}

.form-card h1 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
}

.field-group {
    margin-bottom: 16px;
    display: flex;
    flex-direction: column;
}

.field-group label {
    font-size: 0.95rem;
    margin-bottom: 6px;
    color: #111827;
    font-weight: 600;
}

.field-group input,
.field-group select {
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.95rem;
    background: #f9fafb;
    transition: border-color 0.2s, background 0.2s;
}

.field-group input:focus,
.field-group select:focus {
    outline: none;
    border-color: #68A691;
    background: #fff;
}

.field-group select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23111827' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 18px;
    padding-right: 40px;
}

.form-buttons {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    flex-wrap: wrap; /* ensures buttons wrap on small screens */
}

.btn {
    flex: 1;
    padding: 12px 0;
    border-radius: 6px;
    font-weight: 600;
    font-size: 1rem;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-save {
    background: #68A691;
    color: #fff;
    border: none;
}

.btn-save:hover {
    background: #4a8970;
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(104, 166, 145, 0.3);
}

.btn-cancel {
    background: #f3f4f6;
    color: #111827;
    border: 1px solid #d1d5db;
    display: inline-block;
    text-decoration: none;
}

.btn-cancel:hover {
    background: #e5e7eb;
}

/* Ensure buttons are equal width and responsive */
@media (max-width: 480px) {
    .form-buttons {
        flex-direction: column;
        gap: 10px;
    }
}


/* Responsive */
@media (max-width: 768px) {
    .main-wrapper { padding: 20px 12px 60px; }
    .form-card { padding: 20px 16px; }
    .form-card h1 { font-size: 1.5rem; }
}

@media (max-width: 480px) {
    .main-wrapper { padding: 16px 12px 50px; }
    .form-card { padding: 16px 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
    .form-card h1 { font-size: 1.3rem; margin-bottom: 16px; }
    .field-group label { font-size: 0.9rem; }
    button[type="submit"], .btn-cancel { font-size: 1rem; padding: 10px; }
}
</style>
</head>
<body>
<?php
$pageTitle = 'Update Employee Data';
$showExport = false;
include '../header.php';
?>

<div class="main-wrapper">
    <div class="form-card">
        <h1>Update Employee Data</h1>
        <form method="POST" action="get.php">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="field-group">
                <label for="department_id">Department ID</label>
                <input type="text" name="department_id" id="department_id"
                       value="<?php echo htmlspecialchars($row['department_id'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="100" required>
            </div>

            <div class="field-group">
                <label for="ename">Full Name</label>
                <input type="text" name="ename" id="ename"
                       value="<?php echo htmlspecialchars($row['ename'], ENT_QUOTES, 'UTF-8'); ?>"
                       pattern="[A-Za-z\s]+" required>
            </div>

            <div class="field-group">
                <label for="dob">Date of Birth</label>
                <input type="date" name="dob" id="dob"
                       value="<?php echo htmlspecialchars($row['dob'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="gender">Gender</label>
                <select name="gender" id="gender" required>
                    <option disabled>--Select--</option>
                    <option value="Male" <?php if($row['gender']==='Male') echo 'selected'; ?>>Male</option>
                    <option value="Female" <?php if($row['gender']==='Female') echo 'selected'; ?>>Female</option>
                </select>
            </div>

            <div class="field-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email"
                       value="<?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="pnumber">Phone Number</label>
                <input type="tel" name="pnumber" id="pnumber" minlength="10" maxlength="13"
                       value="<?php echo htmlspecialchars($row['pnumber'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="address">Address</label>
                <input type="text" name="address" id="address"
                       value="<?php echo htmlspecialchars($row['address'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="designation">Designation</label>
                <input type="text" name="designation" id="designation"
                       value="<?php echo htmlspecialchars($row['designation'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="salary">Salary</label>
                <input type="number" step="0.01" name="salary" id="salary"
                       value="<?php echo htmlspecialchars($row['salary'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="joining_date">Date of Joining</label>
                <input type="date" name="joining_date" id="joining_date"
                       value="<?php echo htmlspecialchars($row['joining_date'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="aadhar">Aadhar Number / ID Proof</label>
                <input type="text" name="aadhar" id="aadhar"
                       value="<?php echo htmlspecialchars($row['aadhar'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn btn-save">Save Changes</button>
                <a href="get.php" class="btn btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>
</body>
</html>
