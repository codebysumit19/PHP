<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}

// Auto logout after 5 minutes (300 seconds) of inactivity
$timeout = 5 * 60;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    $_SESSION = [];
    session_destroy();
    header('Location: ../login.php');
    exit;
}
$_SESSION['last_activity'] = time();

require_once '../db.php';

if (!isset($_GET['id'])) {
    die("No department ID provided.");
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM departments WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    die("Department not found!");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Department</title>
<link rel="icon" type="image/png" href="../fi-snsuxx-php-logo.jpg">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; background: linear-gradient(135deg,#e8f5e9,#fff); min-height:100vh; }

.main-wrapper {
    display: flex; justify-content: center; align-items: flex-start; padding: 24px 16px 80px;
}
.form-card {
    background:#fff; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.08); padding:28px 24px; width:100%; max-width:600px;
}
.form-card h1 { text-align:center; margin-bottom:20px; font-size:1.75rem; font-weight:700; color:#111827; }

.field-group { margin-bottom:16px; display:flex; flex-direction:column; }
.field-group label { margin-bottom:6px; font-weight:600; color:#111827; font-size:0.95rem; }
.field-group input, .field-group textarea, .field-group select {
    padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; background:#f9fafb; font-size:0.95rem; transition:border-color 0.2s,background 0.2s;
}
.field-group input:focus, .field-group textarea:focus, .field-group select:focus {
    outline:none; border-color:#68A691; background:#fff;
}
.field-group textarea { min-height:100px; resize:vertical; }

.radio-group { display:flex; gap:20px; align-items:center; }
.radio-group label { font-weight:400; font-size:0.95rem; cursor:pointer; display:flex; align-items:center; gap:6px; }

.form-buttons { display:flex; gap:12px; margin-top:24px; }
.btn {
    flex: 1;
    padding: 12px;
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
    text-decoration: none;
    display: inline-block;
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

@media(max-width:768px){ .main-wrapper{padding:20px 12px 60px;} .form-card{padding:20px 16px;} .form-card h1{font-size:1.5rem;} }
@media(max-width:480px){ .main-wrapper{padding:16px 12px 50px;} .form-card{padding:16px 12px; box-shadow:0 4px 10px rgba(0,0,0,0.08);} .form-card h1{font-size:1.3rem; margin-bottom:16px;} .field-group label{font-size:0.9rem;} .btn{font-size:1rem; padding:10px;} }
</style>
</head>
<body>
<?php
$pageTitle = 'Update Department Data';
$showExport = false;
include '../header.php';
?>

<div class="main-wrapper">
    <div class="form-card">
        <h1>Update Department Data</h1>
        <form action="get.php" method="POST" id="update-department-form">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="field-group">
                <label for="department-deptid">Department ID</label>
                <input type="text" name="department_id" id="department-deptid" value="<?php echo htmlspecialchars($row['department_id'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="100" required>
            </div>

            <div class="field-group">
                <label for="department-name">Department Name</label>
                <input type="text" name="dname" id="department-name" value="<?php echo htmlspecialchars($row['dname'], ENT_QUOTES, 'UTF-8'); ?>" pattern="[A-Za-z\s]+" required>
            </div>

            <div class="field-group">
                <label for="department-email">Email</label>
                <input type="email" name="email" id="department-email" value="<?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="department-number">Contact Number</label>
                <input type="tel" name="number" id="department-number" minlength="10" maxlength="13" value="<?php echo htmlspecialchars($row['number'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="department-nemployees">Number of Employees</label>
                <input type="number" name="nemployees" id="department-nemployees" min="1" value="<?php echo (int)$row['nemployees']; ?>" required>
            </div>

            <div class="field-group">
                <label for="department-resp">Responsibilities</label>
                <input type="text" name="resp" id="department-resp" value="<?php echo htmlspecialchars($row['resp'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="department-budget">Annual Budget</label>
                <input type="text" name="budget" id="department-budget" value="<?php echo htmlspecialchars($row['budget'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label>Status</label>
                <div class="radio-group">
                    <label><input type="radio" name="status" value="Active" <?php if($row['status']==='Active') echo 'checked'; ?> id="status-active"> Active</label>
                    <label><input type="radio" name="status" value="Inactive" <?php if($row['status']==='Inactive') echo 'checked'; ?> id="status-inactive"> Inactive</label>
                </div>
            </div>

            <div class="field-group">
                <label for="department-description">Description</label>
                <textarea name="description" id="department-description"><?php echo htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
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
