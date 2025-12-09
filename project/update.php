<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}
require_once '../db.php';

if (!isset($_GET['id'])) {
    die("No project ID provided.");
}
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->bind_param("i", $id);
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
    width:100%;max-width:600px;max-height:80vh;overflow-y:auto;
}
h1{text-align:center;margin-bottom:20px}
h2{font-size:1.1em;margin-top:10px}
input[type="text"],input[type="date"],select,textarea{
    width:100%;padding:10px;margin-top:5px;
    border:1px solid #ccc;border-radius:6px;background:#fafafa;
}
textarea{resize:none;height:80px}
button{
    background:#4CAF50;color:#fff;border:none;padding:12px;
    border-radius:6px;cursor:pointer;width:100%;font-size:1.1em;margin-top:20px;
}
button:hover{background:#249f60}
</style>
</head>
<body>
<?php
$showExport = false;
include '../header.php';
?>

<div class="main-wrapper">
    <form method="POST" action="get.php">
        <h1>Update Project Data</h1>
        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">

        <h2>Project Name:
            <input type="text" name="pname"
                   value="<?php echo htmlspecialchars($row['pname'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>Client / Company Name:
            <input type="text" name="cname"
                   value="<?php echo htmlspecialchars($row['cname'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>Project Manager Name:
            <input type="text" name="pmanager" pattern="[A-Za-z\s]+"
                   title="Only letters and spaces allowed"
                   value="<?php echo htmlspecialchars($row['pmanager'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>Start Date:
            <input type="date" name="sdate"
                   value="<?php echo htmlspecialchars($row['sdate'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>End Date / Deadline:
            <input type="date" name="edate"
                   value="<?php echo htmlspecialchars($row['edate'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>Project Status:
            <select name="status" required>
                <option disabled>--Select--</option>
                <option value="Planning"   <?php if($row['status']==='Planning')   echo 'selected'; ?>>Planning</option>
                <option value="In Progress"<?php if($row['status']==='In Progress')echo 'selected'; ?>>In Progress</option>
                <option value="On Hold"    <?php if($row['status']==='On Hold')    echo 'selected'; ?>>On Hold</option>
                <option value="Completed"  <?php if($row['status']==='Completed')  echo 'selected'; ?>>Completed</option>
            </select>
        </h2>
        <h2>Description:
            <textarea name="pdescription" required><?php
                echo htmlspecialchars($row['pdescription'], ENT_QUOTES, 'UTF-8');
            ?></textarea>
        </h2>
        <button type="submit">Submit</button>
    </form>
</div>

<?php include '../footer.php'; ?>
</body>
</html>
