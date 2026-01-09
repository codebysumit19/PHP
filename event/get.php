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

// Export button
if (isset($_POST['export'])) {
    header('Location: export.php');
    exit;
}

require_once '../db.php';

// ---------------------------
// DELETE EVENT
// ---------------------------
if (isset($_GET['id'])) {
    $id = trim($_GET['id']);
    if ($id !== '') {
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: get.php");
        exit;
    }
}

// ---------------------------
// UPDATE EVENT
// ---------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id']) && !isset($_POST['export'])) {
    $id = $_POST['id'];
    $department_id = htmlspecialchars(trim($_POST['department_id']), ENT_QUOTES, 'UTF-8');
    $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
    $address = htmlspecialchars(trim($_POST['address']), ENT_QUOTES, 'UTF-8');
    $date = $_POST['date'];
    $stime = $_POST['stime'];
    $etime = $_POST['etime'];
    $type = htmlspecialchars(trim($_POST['type']), ENT_QUOTES, 'UTF-8');
    $happend = htmlspecialchars(trim($_POST['happend']), ENT_QUOTES, 'UTF-8');

    $stmt = $conn->prepare(
        "UPDATE events
         SET department_id=?, name=?, address=?, date=?, stime=?, etime=?, type=?, happend=?
         WHERE id=?"
    );
    $stmt->bind_param("sssssssss", $department_id, $name, $address, $date, $stime, $etime, $type, $happend, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: get.php");
    exit;
}

// Initial load - get all events
$result = $conn->query("SELECT * FROM events");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Event Data</title>
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
            background: linear-gradient(135deg, #e8f5e9, #fff);
            display: flex;
            flex-direction: column;
            overflow-y: scroll;
        }

        .table-container {
            flex: 1;
            padding: 20px 12px 30px;
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .table-header h1 {
            margin: 0;
            font-size: 1.75rem;
            color: #111827;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
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
            transition: background 0.2s;
        }

        .table-container tbody tr:hover td {
            background: #e0f2f1;
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

        .table-container i.fas.fa-plus {
            color: #68A691;
        }

        .table-container i.fas.fa-plus:hover {
            color: #4a8970;
        }

        /* Delete Modal */
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
            width: 28px;
            height: 28px;
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
            background: #e5e7eb;
            color: #111827;
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
            font-size: 22px;
            color: #dc2626;
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
            line-height: 1.5;
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
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-delete-cancel {
            background: #fff;
            border: 1px solid #d1d5db;
            color: #374151;
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

        .search-wrapper {
            position: relative;
            display: inline-block;
        }

        .search-box {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            min-width: 250px;
        }

        .search-box:focus {
            outline: none;
            border-color: #68A691;
        }

        .clear-search {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: #6b7280;
            cursor: pointer;
            display: none;
            user-select: none;
        }

        .clear-search:hover {
            color: #111827;
        }

        .highlight {
            background-color: #ffe58f;
        }

        input[type="search"]::-webkit-search-cancel-button {
            display: none;
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

        @media (min-width:768px) {
            .table-container {
                padding: 30px 24px 40px;
            }

            .table-container table {
                min-width: 0;
            }
        }

        @media (max-width:768px) {
            .table-header {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: 100%;
            }
        }

        @media (max-width:480px) {
            body {
                padding-top: 100px;
            }

            .table-container th,
            .table-container td {
                padding: 8px 6px;
                font-size: 0.8rem;
            }

            .delete-modal {
                min-width: 280px;
                margin: 0 16px;
            }

            .table-header h1 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>

<body>
    <?php
    $pageTitle = 'Event Data';
    $showExport = true;
    include '../header.php';
    ?>

    <div class="table-container">
        <div class="table-header">
            <h1>Project Data</h1>
            <div class="search-wrapper">
                <input type="search" id="liveSearch" class="search-box" placeholder="Search projects..." autocomplete="off" />
                <span id="clearSearch" class="clear-search">&times;</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Department ID</th>
                    <th>Address</th>
                    <th>Date</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Type</th>
                    <th>Happened</th>
                    <th>Update</th>
                    <th>Delete</th>
                    <th>Add</th>
                </tr>
            </thead>
            <tbody id="tableData">
                <?php



                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {

                        $id   = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
                        $name = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');

                        echo "<tr>";
                        echo "<td>{$name}</td>";
                        echo "<td>" . htmlspecialchars($row['department_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['stime']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['etime']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['type']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['happend']) . "</td>";

                        echo "<td>
                <a href='update.php?id={$id}'>
                    <i class='fas fa-edit'></i>
                </a>
              </td>";

                        echo "<td>
                <i class='fas fa-trash'
                   onclick='showDeleteModal(\"{$id}\", \"{$name}\")'>
                </i>
              </td>";

                        echo "<td>
                <a href='form.php'>
                    <i class='fas fa-plus'></i>
                </a>
              </td>";

                        echo "</tr>";
                    }
                }

                ?>
                <tr id="no-data-row" style="display:none;">
                    <td colspan="11" style="text-align:center; padding: 15px 0;">No data found</td>
                </tr>

            </tbody>


        </table>
    </div>

    <!-- Delete Modal -->
    <div class="delete-overlay" id="delete-overlay">
        <div class="delete-modal">
            <button type="button" class="delete-close" id="delete-close">&times;</button>
            <div class="delete-modal-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="delete-title">Delete Event?</div>
            <div class="delete-text" id="delete-event-name">Are you sure you want to delete this event?</div>
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
            document.getElementById('delete-event-name').textContent =
                `Are you sure you want to delete "${name}"? This action cannot be undone.`;
            document.getElementById('delete-overlay').style.display = 'flex';
        }

        function hideDeleteModal() {
            document.getElementById('delete-overlay').style.display = 'none';
            deleteId = null;
        }

        document.getElementById('delete-close').onclick = hideDeleteModal;
        document.getElementById('delete-cancel').onclick = hideDeleteModal;

        document.getElementById('delete-confirm').onclick = function() {
            if (deleteId) {
                window.location.href = "?id=" + encodeURIComponent(deleteId);
            }
        };

        // Close modal on outside click
        document.getElementById('delete-overlay').addEventListener('click', function(e) {
            if (e.target === this) hideDeleteModal();
        });

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') hideDeleteModal();
        });

        // =============================
        // LIVE SEARCH (AJAX)
        // =============================
        const searchInput = document.getElementById('liveSearch');
        const tableData = document.getElementById('tableData');
        const clearBtn = document.getElementById('clearSearch');
        const noDataRow = document.getElementById('no-data-row');

        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function resetTable() {
            const rows = tableData.getElementsByTagName('tr');
            for (let i = 0; i < rows.length; i++) {
                rows[i].style.display = '';
                const cells = rows[i].getElementsByTagName('td');
                for (let c = 0; c < cells.length - 3; c++) { // only data columns
                    cells[c].innerHTML = cells[c].textContent;
                }
            }
            noDataRow.style.display = 'none';
        }

        searchInput.addEventListener('input', function() {
            const value = this.value.trim();
            clearBtn.style.display = value ? 'block' : 'none';

            if (!value) {
                resetTable();
                return;
            }

            const rows = tableData.getElementsByTagName('tr');
            let visibleCount = 0;

            for (let i = 0; i < rows.length; i++) {
                if (rows[i] === noDataRow) continue;
                const cells = rows[i].getElementsByTagName('td');
                let rowMatches = false;

                for (let c = 0; c < cells.length - 3; c++) { // only data columns
                    const cellText = cells[c].textContent;
                    const regex = new RegExp(`(${escapeRegExp(value)})`, 'gi');
                    const highlighted = cellText.replace(regex, '<span class="highlight">$1</span>');
                    cells[c].innerHTML = highlighted;

                    if (cellText.toLowerCase().includes(value.toLowerCase())) rowMatches = true;
                }

                rows[i].style.display = rowMatches ? '' : 'none';
                if (rowMatches) visibleCount++;
            }

            noDataRow.style.display = visibleCount === 0 ? '' : 'none';
        });

        // Clear button
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearBtn.style.display = 'none';
            resetTable();
            searchInput.focus();
        });
    </script>
    <?php include '../footer.php'; ?>
</body>

</html>