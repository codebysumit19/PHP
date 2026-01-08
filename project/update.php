<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}

// Auto logout after 50 minutes
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
    die("No project ID provided.");
}
$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
if (!$row) { die("Project not found!"); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Project</title>
<link rel="icon" type="image/png" href="../fi-snsuxx-php-logo.jpg">
<style>
* { box-sizing: border-box; margin:0; padding:0; }
body { font-family: Arial,sans-serif; background:linear-gradient(135deg,#e8f5e9,#fff); min-height:100vh; }

.main-wrapper { display:flex; justify-content:center; align-items:flex-start; padding:24px 16px 80px; }
.form-card { background:#fff; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.08); padding:28px 24px; width:100%; max-width:600px; }

.form-card h1 { text-align:center; margin-bottom:20px; font-size:1.75rem; font-weight:700; color:#111827; }

.field-group { margin-bottom:16px; display:flex; flex-direction:column; }
.field-group label { margin-bottom:6px; font-weight:600; color:#111827; font-size:0.95rem; }
.field-group input, .field-group select, .field-group textarea {
    padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; background:#f9fafb; font-size:0.95rem; transition:border-color 0.2s,background 0.2s;
}
.field-group input:focus, .field-group select:focus, .field-group textarea:focus { outline:none; border-color:#68A691; background:#fff; }
.field-group select { cursor:pointer; appearance:none; padding-right:40px; background-image:url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23111827' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e"); background-repeat:no-repeat; background-position:right 10px center; background-size:18px; }
.field-group select option[disabled] { color:#9ca3af; }
.field-group textarea { min-height:100px; resize:vertical; }

.form-buttons { display:flex; gap:12px; margin-top:24px; }
.btn { flex:1; padding:12px; border-radius:6px; font-weight:600; font-size:1rem; text-align:center; cursor:pointer; transition:all 0.2s ease; text-decoration:none; display:inline-block; }
.btn-save { background:#68A691; color:#fff; border:none; }
.btn-save:hover { background:#4a8970; transform:translateY(-1px); box-shadow:0 6px 14px rgba(104,166,145,0.3); }
.btn-cancel { background:#f3f4f6; color:#111827; border:1px solid #d1d5db; text-decoration:none; text-align:center; line-height:1.2; }
.btn-cancel:hover { background:#e5e7eb; }

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
$pageTitle = 'Update Project Data';
$showExport = false;
include '../header.php';
?>

<div class="main-wrapper">
    <div class="form-card">
        <h1>Update Project Data</h1>
        <form method="POST" action="get.php">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="field-group">
                <label for="department_id">Department ID</label>
                <input type="text" name="department_id" id="department_id"
                       value="<?php echo htmlspecialchars($row['department_id'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="100" required>
            </div>

            <div class="field-group">
                <label for="pname">Project Name</label>
                <input type="text" name="pname" id="pname"
                       value="<?php echo htmlspecialchars($row['pname'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="cname">Client / Company Name</label>
                <input type="text" name="cname" id="cname"
                       value="<?php echo htmlspecialchars($row['cname'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="pmanager">Project Manager Name</label>
                <input type="text" name="pmanager" id="pmanager" pattern="[A-Za-z\s]+"
                       title="Only letters and spaces allowed"
                       value="<?php echo htmlspecialchars($row['pmanager'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="sdate">Start Date</label>
                <input type="date" name="sdate" id="sdate"
                       value="<?php echo htmlspecialchars($row['sdate'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="edate">End Date / Deadline</label>
                <input type="date" name="edate" id="edate"
                       value="<?php echo htmlspecialchars($row['edate'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="status">Project Status</label>
                <select name="status" id="status" required>
                    <option disabled>--Select--</option>
                    <option value="Planning"    <?php if($row['status']==='Planning') echo 'selected'; ?>>Planning</option>
                    <option value="In Progress" <?php if($row['status']==='In Progress') echo 'selected'; ?>>In Progress</option>
                    <option value="On Hold"     <?php if($row['status']==='On Hold') echo 'selected'; ?>>On Hold</option>
                    <option value="Completed"   <?php if($row['status']==='Completed') echo 'selected'; ?>>Completed</option>
                </select>
            </div>

            <div class="field-group">
                <label for="pdescription">Description</label>
                <textarea name="pdescription" id="pdescription"><?php echo htmlspecialchars($row['pdescription'], ENT_QUOTES, 'UTF-8'); ?></textarea>
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
