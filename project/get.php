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

// ---------------------------
// DELETE PROJECT (via GET)
// ---------------------------
if (isset($_GET['id'])) {
    $id = trim($_GET['id']);
    if ($id !== '') {
        $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: get.php");
        exit;
    }
}

// ---------------------------
// UPDATE PROJECT
// ---------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id']) && !isset($_POST['search'])) {
    $id = $_POST['id'];

    // Sanitize inputs
    $department_id = htmlspecialchars(trim($_POST['department_id']), ENT_QUOTES, 'UTF-8');
    $pname = htmlspecialchars(trim($_POST['pname']), ENT_QUOTES, 'UTF-8');
    $cname = htmlspecialchars(trim($_POST['cname']), ENT_QUOTES, 'UTF-8');
    $pmanager = htmlspecialchars(trim($_POST['pmanager']), ENT_QUOTES, 'UTF-8');
    $sdate = $_POST['sdate'];
    $edate = $_POST['edate'];
    $status = htmlspecialchars(trim($_POST['status']), ENT_QUOTES, 'UTF-8');
    $pdescription = htmlspecialchars(trim($_POST['pdescription']), ENT_QUOTES, 'UTF-8');

    $stmt = $conn->prepare(
        "UPDATE projects
         SET department_id=?, pname=?, cname=?, pmanager=?, sdate=?, edate=?, status=?, pdescription=?
         WHERE id=?"
    );
    $stmt->bind_param(
        "sssssssss",
        $department_id,
        $pname,
        $cname,
        $pmanager,
        $sdate,
        $edate,
        $status,
        $pdescription,
        $id
    );
    $stmt->execute();
    $stmt->close();

    header("Location: get.php");
    exit;
}

// ---------------------------
// SEARCH / FILTER
// ---------------------------
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = $conn->prepare(
        "SELECT * FROM projects
         WHERE pname LIKE ? OR cname LIKE ? OR status LIKE ? OR department_id LIKE ?"
    );
    $stmt->bind_param("ssss", $like, $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM projects");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Project Data</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../fi-snsuxx-php-logo.jpg">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e8f5e9, #ffffff);
            display: flex;
            flex-direction: column;
            overflow-y: scroll;
        }

        .table-container {
            flex: 1;
            padding: 20px 12px 30px;
            overflow-x: auto;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        .table-container th,
        .table-container td {
            padding: 10px 8px;
            border: 1px solid #e5e7eb;
            text-align: center;
            font-size: 0.9rem;
        }

        .table-container th {
            background: #111827;
            color: #f9fafb;
            font-weight: 600;
        }

        .table-container td {
            background: #f9fafb;
        }

        .table-container td a {
            color: #111827;
            text-decoration: none;
        }

        .table-container td a:hover {
            color: #2563eb;
        }

        .table-container i.fas.fa-trash {
            color: #b91c1c;
            cursor: pointer;
        }

        .table-container i.fas.fa-trash:hover {
            color: #ef4444;
        }

        .table-container i.fas.fa-edit {
            color: #065f46;
        }

        .table-container i.fas.fa-edit:hover {
            color: #10b981;
        }

        /* Delete modal styles */
        .delete-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .delete-modal {
            background: #fff;
            color: #111827;
            padding: 24px;
            border-radius: 12px;
            min-width: 320px;
            max-width: 400px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.4);
            position: relative;
            animation: modalSlideIn 0.2s ease-out;
        }

        .delete-close {
            position: absolute;
            top: 12px;
            right: 12px;
            border: none;
            background: #f3f4f6;
            font-size: 20px;
            cursor: pointer;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            transition: all 0.2s ease;
        }

        .delete-close:hover {
            background: #ef4444;
            color: #fff;
        }

        .delete-modal-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #fef2f2;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .delete-modal-icon i {
            color: #dc2626;
            font-size: 22px;
        }

        .delete-title {
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 8px;
        }

        .delete-text {
            font-size: 14px;
            text-align: center;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .delete-actions {
            display: flex;
            gap: 10px;
        }

        .btn-delete-cancel,
        .btn-delete-confirm {
            flex: 1;
            padding: 10px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-delete-cancel {
            background: #fff;
            border: 1px solid #d1d5db;
        }

        .btn-delete-cancel:hover {
            background: #f3f4f6;
        }

        .btn-delete-confirm {
            background: #dc2626;
            border: 1px solid #dc2626;
            color: #fff;
        }

        .btn-delete-confirm:hover {
            background: #b91c1c;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (min-width: 768px) {
            .table-container {
                padding: 30px 24px 40px;
            }

            .table-container table {
                min-width: 0;
            }
        }

        @media (max-width:480px) {

            .table-container th,
            .table-container td {
                padding: 8px 6px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>
    <?php
    $pageTitle = 'Project Data';
    $showExport = true;
    include '../header.php';
    ?>

    <div class="table-container">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
            <h1 style="margin:0;">Project Data</h1>
            <form method="get" style="margin-bottom:12px; text-align:right;">
                <input type="text" name="search" placeholder="Dept ID / Project Name"
                    value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
                    style="padding:6px 8px;border-radius:4px;border:1px solid #ccc;">
                <button type="submit"
                    style="padding:6px 10px;border-radius:4px;border:1px solid #111827;
                       background:#111827;color:#f9fafb;cursor:pointer;">
                    Search
                </button>
            </form>
        </div>

        <table>
            <tr>
                <th>Project Name</th>
                <th>Department ID</th>
                <th>Client / Company Name</th>
                <th>Project Manager</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Project Status</th>
                <th>Description</th>
                <th>Update</th>
                <th>Delete</th>
                <th>Add New</th>
            </tr>

            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['pname']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['department_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['cname']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['pmanager']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['sdate']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['edate']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['pdescription']) . "</td>";
                    echo "<td><a href='update.php?id=" . rawurlencode($row['id']) . "'><i class='fas fa-edit'></i></a></td>";
                    echo "<td><i class='fas fa-trash' onclick='showDeleteModal(\"" . addslashes($row['id']) . "\", \"" . addslashes($row['pname']) . "\")'></i></td>";
                    echo "<td><a href='form.php' title='Add New Project'><i class='fas fa-plus'></i></a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr>
                    <td colspan='10'>No data found</td>
                    <td><a href='form.php'><i class='fas fa-plus'></i></a></td>
                  </tr>";
            }
            $conn->close();
            ?>
        </table>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="delete-overlay" id="delete-overlay">
        <div class="delete-modal">
            <button type="button" class="delete-close" id="delete-close">&times;</button>
            <div class="delete-modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="delete-title">Delete Project?</div>
            <div class="delete-text" id="delete-item-name">Are you sure you want to delete this project?</div>
            <div class="delete-actions">
                <button type="button" class="btn-delete-cancel" id="delete-cancel">Cancel</button>
                <button type="button" class="btn-delete-confirm" id="delete-confirm">Delete</button>
            </div>
        </div>
    </div>

    <script>
        let deleteId = null;

        function showDeleteModal(id, name) {
            deleteId = id;
            document.getElementById('delete-item-name').textContent =
                `Are you sure you want to delete "${name}"? This action cannot be undone.`;
            document.getElementById('delete-overlay').style.display = 'flex';
        }

        function hideDeleteModal() {
            document.getElementById('delete-overlay').style.display = 'none';
            deleteId = null;
        }

        // Modal buttons
        document.getElementById('delete-close').onclick = hideDeleteModal;
        document.getElementById('delete-cancel').onclick = hideDeleteModal;
        document.getElementById('delete-confirm').onclick = function() {
            if (deleteId) window.location.href = "?id=" + encodeURIComponent(deleteId);
        };

        // Prevent closing when clicking outside the modal
        document.getElementById('delete-overlay').onclick = function(e) {
            if (e.target === this) {
                // Do nothing
            }
        };
    </script>
    <?php include '../footer.php'; ?>
</body>

</html>