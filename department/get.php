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
            padding: 0 2px;
            border-radius: 2px;
        }

        input[type="search"]::-webkit-search-cancel-button {
            display: none;
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
            transition: background 0.2s;
        }

        tbody tr:hover td {
            background: #e0f2f1;
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

        i.fas.fa-plus {
            color: #68A691;
        }

        i.fas.fa-plus:hover {
            color: #4a8970;
        }

        /* Delete modal */
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

            .delete-modal {
                min-width: 280px;
                margin: 0 16px;
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
        <div class="table-header">
            <h1>Department Data</h1>
            <div class="search-wrapper">
                <input type="text" id="liveSearch" class="search-box" placeholder="Search department data" autocomplete="off">
                <span id="clearSearch" class="clear-search">&times;</span>
            </div>
        </div>

        <table>
            <thead>
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
                    <th>Add</th>
                </tr>
            </thead>
            <tbody id="tableData">
                <?php
                $result = $conn->query("SELECT * FROM departments ORDER BY dname ASC");
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $id = htmlspecialchars($row['id'], ENT_QUOTES);
                        $dname = htmlspecialchars($row['dname'], ENT_QUOTES);
                        echo "<tr>
                    <td>{$dname}</td>
                    <td>" . htmlspecialchars($row['department_id']) . "</td>
                    <td>" . htmlspecialchars($row['email']) . "</td>
                    <td>" . htmlspecialchars($row['number']) . "</td>
                    <td>" . htmlspecialchars($row['nemployees']) . "</td>
                    <td>" . htmlspecialchars($row['resp']) . "</td>
                    <td>" . htmlspecialchars($row['budget']) . "</td>
                    <td>" . htmlspecialchars($row['status']) . "</td>
                    <td>" . htmlspecialchars($row['description']) . "</td>
                    <td><a href='update.php?id={$id}'><i class='fas fa-edit'></i></a></td>
                    <td><i class='fas fa-trash' onclick='showDeleteModal(\"{$id}\", \"{$dname}\")'></i></td>
                    <td><a href='form.php'><i class='fas fa-plus'></i></a></td>
                </tr>";
                    }
                } else {
                    echo "<tr><td colspan='12'>No data found</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <!-- Delete Modal -->
    <div class="delete-overlay" id="delete-overlay">
        <div class="delete-modal">
            <button class="delete-close" id="delete-close">&times;</button>
            <div class="delete-modal-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="delete-title">Delete Department?</div>
            <div class="delete-text" id="delete-item-name">Are you sure you want to delete this department?</div>
            <div class="delete-actions">
                <button class="btn-delete-cancel" id="delete-cancel">Cancel</button>
                <button class="btn-delete-confirm" id="delete-confirm">Delete</button>
            </div>
        </div>
    </div>

    <script>
        let deleteId = null;

        function showDeleteModal(id, name) {
            deleteId = id;
            document.getElementById('delete-item-name').textContent = `Are you sure you want to delete "${name}"? This action cannot be undone.`;
            document.getElementById('delete-overlay').style.display = 'flex';
        }

        function hideDeleteModal() {
            document.getElementById('delete-overlay').style.display = 'none';
            deleteId = null;
        }
        document.getElementById('delete-close').onclick = hideDeleteModal;
        document.getElementById('delete-cancel').onclick = hideDeleteModal;
        document.getElementById('delete-confirm').onclick = function() {
            if (deleteId) window.location.href = "?id=" + encodeURIComponent(deleteId);
        };
        document.getElementById('delete-overlay').addEventListener('click', function(e) {
            if (e.target === this) hideDeleteModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') hideDeleteModal();
        });

        // ===========================
        // LIVE SEARCH
        // ===========================
        // ===========================
        // LIVE SEARCH with "No data found" row
        // ===========================
        const searchInput = document.getElementById('liveSearch');
        const tableData = document.getElementById('tableData');
        const clearBtn = document.getElementById('clearSearch');

        // Create "No data found" row
        const noDataRow = document.createElement('tr');
        noDataRow.id = 'no-data-row';
        noDataRow.style.display = 'none';
        noDataRow.innerHTML = `
    <td colspan="12" style="text-align:center; padding:15px 0;">
        No data found
    </td>`;
        tableData.appendChild(noDataRow);

        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function resetTable() {
            const rows = tableData.querySelectorAll('tr');
            rows.forEach(row => {
                if (row.id === 'no-data-row') return;
                row.style.display = '';
                const cells = row.querySelectorAll('td');
                for (let i = 0; i < cells.length - 3; i++) {
                    cells[i].innerHTML = cells[i].textContent;
                }
            });
            noDataRow.style.display = 'none';
        }

        searchInput.addEventListener('input', function() {
            const value = this.value.trim().toLowerCase();
            clearBtn.style.display = value ? 'block' : 'none';

            if (!value) {
                resetTable();
                return;
            }

            const rows = tableData.querySelectorAll('tr');
            let visibleCount = 0;
            const regex = new RegExp(`(${escapeRegExp(value)})`, 'gi');

            rows.forEach(row => {
                if (row.id === 'no-data-row') return;

                let rowMatch = false;
                const cells = row.querySelectorAll('td');

                for (let i = 0; i < cells.length - 3; i++) {
                    const text = cells[i].textContent;
                    if (text.toLowerCase().includes(value)) {
                        rowMatch = true;
                        cells[i].innerHTML = text.replace(regex, '<span class="highlight">$1</span>');
                    } else {
                        cells[i].innerHTML = text;
                    }
                }

                row.style.display = rowMatch ? '' : 'none';
                if (rowMatch) visibleCount++;
            });

            noDataRow.style.display = visibleCount === 0 ? '' : 'none';
        });

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