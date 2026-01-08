<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}

// Auto logout after 50 minutes (3000 seconds) of inactivity
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
    die("No event ID provided.");
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    die("Event not found!");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Event</title>
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

.event-form-card {
    background: #fff;
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

.field-group input:focus {
    outline: none;
    border-color: #68A691;
    background: #fff;
}

.radio-group {
    display: flex;
    gap: 20px;
    margin-top: 6px;
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

.form-buttons {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}

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

/* Responsive */
/* Ensure buttons are equal width and responsive */
@media (max-width: 480px) {
    .form-buttons {
        flex-direction: column;
        gap: 10px;
    }
}

@media (max-width: 768px) {
    .main-wrapper { padding: 20px 12px 60px; }
    .event-form-card { padding: 20px 16px; }
    .event-form-title { font-size: 1.5rem; }
}

@media (max-width: 480px) {
    .main-wrapper { padding: 16px 12px 50px; }
    .event-form-card { padding: 16px 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
    .event-form-title { font-size: 1.3rem; margin-bottom: 16px; }
    .btn { font-size: 1rem; padding: 10px; }
}
</style>
</head>
<body>
<?php
$pageTitle = 'Update Event Data';
$showExport = false;
include '../header.php';
?>

<div class="main-wrapper">
    <div class="event-form-card">
        <h1 class="event-form-title">Update Event Data</h1>
        <form method="POST" action="get.php">

            <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="field-group">
                <label for="department_id">Department ID</label>
                <input type="text" name="department_id" id="department_id"
                       value="<?php echo htmlspecialchars($row['department_id'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="100" required>
            </div>

            <div class="field-group">
                <label for="name">Event Name</label>
                <input type="text" name="name" id="name"
                       value="<?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="address">Address</label>
                <input type="text" name="address" id="address"
                       value="<?php echo htmlspecialchars($row['address'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="date">Date</label>
                <input type="date" name="date" id="date"
                       value="<?php echo htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="stime">Start Time</label>
                <input type="time" name="stime" id="stime"
                       value="<?php echo htmlspecialchars($row['stime'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="etime">End Time</label>
                <input type="time" name="etime" id="etime"
                       value="<?php echo htmlspecialchars($row['etime'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label for="type">Type of Event</label>
                <input type="text" name="type" id="type"
                       value="<?php echo htmlspecialchars($row['type'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="field-group">
                <label>Event Happened</label>
                <div class="radio-group">
                    <label><input type="radio" name="happend" value="Yes" <?php if($row['happend']==='Yes') echo 'checked'; ?> required> Yes</label>
                    <label><input type="radio" name="happend" value="No" <?php if($row['happend']==='No') echo 'checked'; ?> required> No</label>
                </div>
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
