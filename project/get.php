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



// make Export button work
if (isset($_POST['export'])) {
    header('Location: export.php');
    exit;
}

require_once '../db.php';

// Delete
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Update from update.php
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id']) && !isset($_POST['search'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare(
        "UPDATE projects
         SET pname=?, cname=?, pmanager=?, sdate=?, edate=?, status=?, pdescription=?
         WHERE id=?"
    );
    $stmt->bind_param(
        "sssssssi",
        $_POST['pname'], $_POST['cname'], $_POST['pmanager'],
        $_POST['sdate'], $_POST['edate'], $_POST['status'],
        $_POST['pdescription'], $id
    );
    $stmt->execute();
    $stmt->close();
    echo "<script>window.location='get.php';</script>";
    exit;
}

// Search / filter
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = $conn->prepare(
        "SELECT * FROM projects
         WHERE pname LIKE ? OR cname LIKE ? OR status LIKE ?"
    );
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM projects");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Project Data</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
    overflow-y:scroll; /* always show vertical scrollbar */
}
.table-container{
    flex:1;
    padding:20px 12px 30px;
    overflow-x:auto;
}
.table-container table{
    width:100%;
    border-collapse:collapse;
    min-width:800px;
    background:#ffffff;
    box-shadow:0 4px 12px rgba(0,0,0,0.06);
}
.table-container th,
.table-container td{
    padding:10px 8px;
    border:1px solid #e5e7eb;
    text-align:center;
    font-size:0.9rem;
}
.table-container th{
    background:#111827;
    color:#f9fafb;
    font-weight:600;
}
.table-container td{
    background:#f9fafb;
}
.table-container td a{
    color:#111827;
    text-decoration:none;
}
.table-container td a:hover{
    color:#2563eb;
}
.table-container i.fas.fa-trash{
    color:#b91c1c;
    cursor:pointer;
}
.table-container i.fas.fa-trash:hover{
    color:#ef4444;
}
.table-container i.fas.fa-edit{
    color:#065f46;
}
.table-container i.fas.fa-edit:hover{
    color:#10b981;
}
@media (min-width: 768px){
    .table-container{
        padding:30px 24px 40px;
    }
    .table-container table{
        min-width:0;
    }
}
@media (max-width: 480px){
    .table-container th,
    .table-container td{
        padding:8px 6px;
        font-size:0.8rem;
    }
}
    </style>

    <script>
        function confirmDelete(id){
            if(confirm("Are you sure you want to delete this project?")){
                window.location.href = "?id=" + id;
            }
        }
    </script>
</head>
<body>
<?php
$pageTitle = 'Project Data';
$showExport = true;
include '../header.php';
?>

<div class="table-container">
<h1>Project Data</h1>
    <form method="get" style="margin-bottom:12px; text-align:right;">
        
        <input type="text" name="search" placeholder="Search by project/client/status"
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
        <th>Project ID</th>
        <th>Project Name</th>
        <th>Client / Company Name</th>
        <th>Project Manager</th>
        <th>Start Date</th>
        <th>End Date / Deadline</th>
        <th>Project Status</th>
        <th>Description</th>
        <th>Update</th>
        <th>Delete</th>
    </tr>
<?php
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['pname']) . "</td>";
        echo "<td>" . htmlspecialchars($row['cname']) . "</td>";
        echo "<td>" . htmlspecialchars($row['pmanager']) . "</td>";
        echo "<td>" . htmlspecialchars($row['sdate']) . "</td>";
        echo "<td>" . htmlspecialchars($row['edate']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['pdescription']) . "</td>";
        echo "<td><a href='update.php?id=" . $row['id'] . "'><i class='fas fa-edit'></i></a></td>";
        echo "<td><i class='fas fa-trash' onclick='confirmDelete(" . $row['id'] . ")'></i></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='10'>No data found</td></tr>";
}
$conn->close();
?>
</table>
</div>
<?php include '../footer.php'; ?>
</body>
</html>
