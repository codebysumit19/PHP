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
    die("No event ID provided.");
}
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) { die("Event not found!"); }
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Event</title>
<link rel="icon" type="image/png" href="../fi-snsuxx-php-logo.jpg">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
html, body{height:100%;}
body{
    font-family:Arial,sans-serif;
    background:linear-gradient(135deg,#e8f5e9,#fff);
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
input[type="text"],input[type="date"],input[type="time"]{
    width:100%;padding:10px;margin-top:5px;
    border:1px solid #ccc;border-radius:6px;background:#fafafa;
}
button{
    background:#4CAF50;color:#fff;border:none;padding:12px;
    border-radius:6px;cursor:pointer;width:100%;font-size:1.1em;margin-top:20px;
}
button:hover{background:#249f60}
</style>
</head>
<body>
<?php
$pageTitle = 'Update Event Data';
$showExport = false;
include '../header.php';
?>

<div class="main-wrapper">
    <form method="POST" action="get.php">
        <h1>Update Events Data</h1>
        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
        <h2>Event Name:
            <input type="text" name="name"
                   value="<?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>Address:
            <input type="text" name="address"
                   value="<?php echo htmlspecialchars($row['address'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>Date:
            <input type="date" name="date"
                   value="<?php echo htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>Start Time:
            <input type="time" name="stime"
                   value="<?php echo htmlspecialchars($row['stime'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>End Time:
            <input type="time" name="etime"
                   value="<?php echo htmlspecialchars($row['etime'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>Type of Event:
            <input type="text" name="type"
                   value="<?php echo htmlspecialchars($row['type'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </h2>
        <h2>Event Happened:
            Yes <input type="radio" name="happend" value="Yes"
                <?php if($row['happend']==='Yes') echo 'checked'; ?> required>
            No  <input type="radio" name="happend" value="No"
                <?php if($row['happend']==='No')  echo 'checked'; ?> required>
        </h2>
        <button type="submit">Save Changes</button>
    </form>
</div>

<?php include '../footer.php'; ?>
</body>
</html>
