<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}
require_once '../db.php';

if (!isset($_GET['id'])) {
    die("No department ID provided.");
}
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM departments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
if (!$row) { die("Department not found!"); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Department</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
html, body{height:100%;}
body{
    font-family:Arial,sans-serif;
    background:linear-gradient(135deg,#e8f5e9,#ffffff);
    display:flex;
    flex-direction:column;   /* header, main, footer */
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
input[type="text"],input[type="email"],input[type="number"],input[type="tel"],textarea{
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
    <form action="get.php" method="POST">
        <h1>Update Department Data</h1>
        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">

        <h2>Department Name:
            <input type="text" name="dname"
                   value="<?php echo htmlspecialchars($row['dname'], ENT_QUOTES, 'UTF-8'); ?>"
                   pattern="[A-Za-z\s]+" required>
        </h2>

        <h2>Email:
            <input type="email" name="email"
                   value="<?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>

        <h2>Contact Number:
            <input type="tel" name="number" minlength="10" maxlength="13"
                   value="<?php echo htmlspecialchars($row['number'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>

        <h2>Number of Employees:
            <input type="number" name="nemployees" min="1"
                   value="<?php echo (int)$row['nemployees']; ?>" required>
        </h2>

        <h2>Responsibilities:
            <input type="text" name="resp"
                   value="<?php echo htmlspecialchars($row['resp'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>

        <h2>Annual Budget:
            <input type="text" name="budget"
                   value="<?php echo htmlspecialchars($row['budget'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>

        <h2>Status:
            <label>
                <input type="radio" name="status" value="Active"
                    <?php if($row['status']==='Active') echo 'checked'; ?> required>
                Active
            </label>
            <label>
                <input type="radio" name="status" value="Inactive"
                    <?php if($row['status']==='Inactive') echo 'checked'; ?> required>
                Inactive
            </label>
        </h2>

        <h2>Description:
            <textarea name="description"><?php
                echo htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
            ?></textarea>
        </h2>

        <button type="submit">Save Changes</button>
    </form>
</div>

<?php include '../footer.php'; ?>
</body>
</html>
