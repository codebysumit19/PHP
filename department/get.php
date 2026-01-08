<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}

// Auto logout after 5 minutes
$timeout = 5 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_destroy();
    header('Location: ../login.php');
    exit;
}
$_SESSION['last_activity'] = time();

require_once '../db.php';

// Delete department
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: get.php");
    exit;
}

// Update department
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id']) && !isset($_POST['search'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare(
        "UPDATE departments
         SET department_id=?, dname=?, email=?, number=?, nemployees=?, resp=?, budget=?, status=?, description=?
         WHERE id=?"
    );
    $stmt->bind_param(
        "ssssisssss",
        $_POST['department_id'],
        $_POST['dname'],
        $_POST['email'],
        $_POST['number'],
        $_POST['nemployees'],
        $_POST['resp'],
        $_POST['budget'],
        $_POST['status'],
        $_POST['description'],
        $id
    );
    $stmt->execute();
    $stmt->close();
    header("Location: get.php");
    exit;
}

// Search / filter
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = $conn->prepare(
        "SELECT * FROM departments
         WHERE department_id LIKE ? OR dname LIKE ? OR email LIKE ? OR status LIKE ?"
    );
    $stmt->bind_param("ssss", $like, $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM departments");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Department Data</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../fi-snsuxx-php-logo.jpg">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e8f5e9, #fff);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .table-container {
            flex: 1;
            padding: 20px 12px 30px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        th,
        td {
            padding: 10px 8px;
            border: 1px solid #e5e7eb;
            text-align: center;
            font-size: 0.9rem;
        }

        th {
            background: #111827;
            color: #f9fafb;
            font-weight: 600;
        }

        td {
            background: #f9fafb;
        }

        td a {
            color: #111827;
            text-decoration: none;
        }

        td a:hover {
            color: #2563eb;
        }

        i.fas.fa-trash {
            color: #b91c1c;
            cursor: pointer;
        }

        i.fas.fa-trash:hover {
            color: #ef4444;
        }

        i.fas.fa-edit {
            color: #065f46;
        }

        i.fas.fa-edit:hover {
            color: #10b981;
        }

        /* Delete Modal */
        /* Modal overlay */
        .delete-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease forwards;
        }

        /* Modal box */
        .delete-modal {
            background: #fff;
            color: #111827;
            padding: 24px;
            border-radius: 12px;
            min-width: 320px;
            max-width: 400px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.4);
            position: relative;
            transform: translateY(-50px);
            opacity: 0;
            animation: slideIn 0.3s ease forwards;
        }

        /* Close button */
        .delete-close {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            background: #f3f4f6;
            color: #6b7280;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .delete-close:hover {
            background: #ef4444;
            color: #fff;
            transform: scale(1.1);
        }

        /* Icon circle */
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
            font-size: 22px;
            color: #dc2626;
        }

        /* Title and text */
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

        /* Action buttons */
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
            transform: scale(1.05);
        }

        .btn-delete-confirm {
            background: #dc2626;
            border: 1px solid #dc2626;
            color: #fff;
        }

        .btn-delete-confirm:hover {
            background: #b91c1c;
            transform: scale(1.05);
        }

        @media(min-width:768px) {
            .table-container {
                padding: 30px 24px 40px;
            }

            table {
                min-width: 0;
            }
        }

        @media(max-width:480px) {

            th,
            td {
                padding: 8px 6px;
                font-size: 0.8rem;
            }
        }

        /* Animations */
        @keyframes slideIn {
            0% {
                transform: translateY(-50px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 1;
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
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
            <h1 style="margin:0;">Department Data</h1>
            <form method="get" style="margin-bottom:12px;">
                <input type="text" name="search" placeholder="Dept ID/Department Name" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" style="padding:6px 8px;border-radius:4px;border:1px solid #ccc;">
                <button type="submit" style="padding:6px 10px;border-radius:4px;border:1px solid #111827;background:#111827;color:#f9fafb;cursor:pointer;">Search</button>
            </form>
        </div>

        <table>
            <tr>
                <th>Department Name</th>
                <th>Department ID</th>
                <th>Email</th>
                <th>Contact Number</th>
                <th>Employees</th>
                <th>Responsibilities</th>
                <th>Annual Budget</th>
                <th>Status</th>
                <th>Description</th>
                <th>Update</th>
                <th>Delete</th>
                <th>Add New</th>
            </tr>
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $id = htmlspecialchars($row['id']);
                    $dname = htmlspecialchars($row['dname']);
                    echo "<tr>";
                    echo "<td>{$dname}</td>";
                    echo "<td>" . htmlspecialchars($row['department_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['number']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nemployees']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['resp']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['budget']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                    echo "<td><a href='update.php?id={$id}'><i class='fas fa-edit'></i></a></td>";
                    echo "<td><i class='fas fa-trash' onclick='showDeleteModal(\"{$id}\",\"{$dname}\")'></i></td>";
                    echo "<td><a href='form.php'><i class='fas fa-plus'></i></a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='12'>No data found</td><td><a href='form.php'><i class='fas fa-plus'></i></a></td></tr>";
            }
            $conn->close();
            ?>
        </table>
    </div>

    <!-- Delete Modal -->
    <div class="delete-overlay" id="delete-overlay">
        <div class="delete-modal">
            <button class="delete-close" onclick="hideDeleteModal()">&times;</button>
            <div class="delete-modal-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="delete-title">Delete Department?</div>
            <div class="delete-text" id="delete-item-name">Are you sure?</div>
            <div class="delete-actions">
                <button class="btn-delete-cancel" onclick="hideDeleteModal()">Cancel</button>
                <button class="btn-delete-confirm" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>

    <script>
        let deleteId = null;

        function showDeleteModal(id, name) {
            deleteId = id;
            document.getElementById('delete-item-name').textContent =
                `Are you sure you want to delete "${name}"? This action cannot be undone.`;
            const overlay = document.getElementById('delete-overlay');
            overlay.style.display = 'flex';

            // Trigger animation
            const modal = overlay.querySelector('.delete-modal');
            modal.style.animation = 'slideIn 0.3s ease forwards';
        }

        function hideDeleteModal() {
            const overlay = document.getElementById('delete-overlay');
            overlay.style.display = 'none';
            deleteId = null;
        }

        function confirmDelete() {
            if (deleteId) {
                window.location.href = "?id=" + encodeURIComponent(deleteId);
            }
        }
    </script>

    <?php include '../footer.php'; ?>
</body>

</html>