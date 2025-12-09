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


// Export button
if (isset($_POST['export'])) {
    header('Location: export.php');
    exit;
}

require_once '../db.php';

// Delete
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id']) && !isset($_POST['search'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare(
        "UPDATE departments
         SET dname=?, email=?, number=?, nemployees=?, resp=?, budget=?, status=?, description=?
         WHERE id=?"
    );
    $stmt->bind_param(
        "sssissssi",
        $_POST['dname'], $_POST['email'], $_POST['number'],
        $_POST['nemployees'], $_POST['resp'], $_POST['budget'],
        $_POST['status'], $_POST['description'], $id
    );
    $stmt->execute();
    $stmt->close();
    echo "<script>window.location='get.php';</script>";
    exit;
}

// Search
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = $conn->prepare(
        "SELECT * FROM departments
         WHERE dname LIKE ? OR email LIKE ? OR status LIKE ?"
    );
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM departments");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Department Data</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../fi-snsuxx-php-logo.jpg">
    <style>
*{box-sizing:border-box;margin:0;padding:0;}
html, body{height:100%;}
body{
    font-family:Arial,sans-serif;
    background:linear-gradient(135deg,#e8f5e9,#ffffff);
    display:flex;flex-direction:column;
    overflow-y:scroll; /* always show vertical scrollbar */
}
.table-container{
    flex:1;padding:20px 12px 30px;overflow-x:auto;
}
.table-container table{
    width:100%;border-collapse:collapse;min-width:850px;
    background:#ffffff;box-shadow:0 4px 12px rgba(0,0,0,0.06);
}
.table-container th,.table-container td{
    padding:10px 8px;border:1px solid #e5e7eb;
    text-align:center;font-size:0.9rem;
}
.table-container th{
    background:#111827;color:#f9fafb;font-weight:600;
}
.table-container td{background:#f9fafb;}
.table-container td a{color:#111827;text-decoration:none;}
.table-container td a:hover{color:#2563eb;}
.table-container i.fas.fa-trash{color:#b91c1c;cursor:pointer;}
.table-container i.fas.fa-trash:hover{color:#ef4444;}
.table-container i.fas.fa-edit{color:#065f46;}
.table-container i.fas.fa-edit:hover{color:#10b981;}
@media (min-width:768px){
    .table-container{padding:30px 24px 40px;}
    .table-container table{min-width:0;}
}
@media (max-width:480px){
    .table-container th,.table-container td{
        padding:8px 6px;font-size:0.8rem;
    }
}
    </style>
</head>
<body>
<?php
$pageTitle = 'Department Data';
$showExport = true;
include '../header.php';
?>

<div class="table-container">
    <h1>Department Data</h1>
    <form method="get" style="margin-bottom:12px; text-align:right;">
        <input type="text" name="search" placeholder="Search by name/email/status"
               value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
               style="padding:6px 8px;border-radius:4px;border:1px solid #ccc;">
        <button type="submit"
                style="padding:6px 10px;border-radius:4px;border:1px solid #111827;
                       background:#111827;color:#f9fafb;cursor:pointer;">
            Search
        </button>
    </form>

<table>
    <tr>
        <th>Department ID</th>
        <th>Department Name</th>
        <th>Email</th>
        <th>Contact Number</th>
        <th>Number of Employees</th>
        <th>Responsibilities</th>
        <th>Annual Budget</th>
        <th>Status</th>
        <th>Description</th>
        <th>Update</th>
        <th>Delete</th>
    </tr>
<?php
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['dname']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['number']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nemployees']) . "</td>";
        echo "<td>" . htmlspecialchars($row['resp']) . "</td>";
        echo "<td>" . htmlspecialchars($row['budget']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
        echo "<td><a href='update.php?id=" . $row['id'] . "'><i class='fas fa-edit'></i></a></td>";
        echo "<td><i class='fas fa-trash' onclick='confirmDelete(" . $row['id'] . ")'></i></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='11'>No data found</td></tr>";
}
$conn->close();
?>
</table>
</div>

<script>
function confirmDelete(id){
    if(confirm("Are you sure you want to delete this department?")){
        window.location.href = "?id=" + id;
    }
}
</script>

<?php include '../footer.php'; ?>
</body>
</html>
