<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}

// Auto logout after 50 minutes
$timeout = 50 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_destroy();
    header('Location: ../login.php');
    exit;
}
$_SESSION['last_activity'] = time();

require_once '../db.php';

// ---------------------------
// BULK DELETE
// ---------------------------
if (isset($_POST['bulk_delete']) && isset($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    foreach ($_POST['selected_ids'] as $id) {
        $stmt->bind_param("s", $id);
        $stmt->execute();
    }
    $stmt->close();
    header("Location: get.php");
    exit;
}

// ---------------------------
// EXPORT TO CSV
// ---------------------------
if (isset($_POST['export'])) {
    if (isset($_POST['selected_ids']) && is_array($_POST['selected_ids']) && count($_POST['selected_ids']) > 0) {
        $placeholders = implode(',', array_fill(0, count($_POST['selected_ids']), '?'));
        $stmt = $conn->prepare("SELECT * FROM departments WHERE id IN ($placeholders)");
        $types = str_repeat('s', count($_POST['selected_ids']));
        $stmt->bind_param($types, ...$_POST['selected_ids']);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query("SELECT * FROM departments");
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=departments_export_' . date('Y-m-d_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Department Name', 'Department ID', 'Email', 'Contact Number', 'Employees', 'Responsibilities', 'Annual Budget', 'Status', 'Description']);
    
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['dname'],
            $row['department_id'],
            $row['email'],
            $row['number'],
            $row['nemployees'],
            $row['resp'],
            $row['budget'],
            $row['status'],
            $row['description']
        ]);
    }
    fclose($output);
    exit;
}

// Initial load
$result = $conn->query("SELECT * FROM departments ORDER BY dname ASC");
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
            flex-wrap: nowrap;
            gap: 16px;
        }

        .table-header h1 {
            margin: 0;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .table-header h1 a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #10b981;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .table-header h1 a:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .table-header h1 a i {
            font-size: 0.9rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: nowrap;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-add {
            background: #68A691;
            color: #fff;
        }

        .btn-add:hover {
            background: #4a8970;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: #dc2626;
            color: #fff;
        }

        .btn-delete:hover:not(:disabled) {
            background: #b91c1c;
            transform: translateY(-1px);
        }

        .btn-delete:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-export {
            background: #111827;
            color: #fff;
        }

        .btn-export:hover {
            background: #374151;
            transform: translateY(-1px);
        }

        .search-wrapper {
            position: relative;
            display: inline-block;
            flex-shrink: 0;
        }

        .search-box {
            padding: 10px 35px 10px 40px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #f3f4f6;
            font-size: 14px;
            min-width: 250px;
            width: 250px;
            color: #6b7280;
            transition: all 0.2s;
        }

        .search-box:focus {
            outline: none;
            border-color: #68A691;
            background: #fff;
        }

        .search-box::placeholder {
            color: #9ca3af;
        }

        .search-box::-webkit-search-cancel-button {
            display: none;
            -webkit-appearance: none;
        }

        .search-box::-webkit-search-decoration {
            display: none;
            -webkit-appearance: none;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: #9ca3af;
            pointer-events: none;
        }

        .clear-search {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            color: #9ca3af;
            cursor: pointer;
            display: none;
            user-select: none;
            line-height: 1;
            width: 20px;
            height: 20px;
            text-align: center;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .clear-search:hover {
            background: #e5e7eb;
            color: #111827;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
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
            cursor: pointer;
        }

        .table-container tr.selected td {
            background: #b2dfdb !important;
        }

        .table-container td a {
            color: #111827;
            text-decoration: none;
        }

        .table-container td a:hover {
            color: #2563eb;
        }

        .table-container i.fas.fa-edit {
            color: #065f46;
        }

        .table-container i.fas.fa-edit:hover {
            color: #10b981;
        }

        .checkbox-cell {
            width: 40px;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .highlight {
            background-color: #ffe58f;
            font-weight: 600;
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
            max-width: 450px;
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

        @media (min-width:768px) {
            .table-container {
                padding: 30px 24px 40px;
            }

            .table-container table {
                min-width: 0;
            }
        }

        @media (max-width:1200px) {
            .table-header {
                flex-wrap: wrap;
            }

            .header-right {
                flex-wrap: wrap;
            }
        }

        @media (max-width:768px) {
            .table-header {
                flex-direction: column;
                align-items: stretch;
            }

            .header-right {
                flex-direction: column;
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

            .table-header h1 a {
                font-size: 1rem;
                padding: 8px 16px;
            }
        }
    </style>
</head>

<body>
    <?php
    $pageTitle = 'Department Data';
    $showExport = false;
    include '../header.php';
    ?>

    <div class="table-container">
        <div class="table-header">
            <h1>
                <a href="../link.php">
                    <i class="fas fa-chevron-left"></i> Home
                </a>
            </h1>
            
            <div class="header-right">
                <a href="form.php" class="btn btn-add">
                    <i class="fas fa-plus"></i> Add New Data
                </a>
                <button type="button" class="btn btn-delete" id="bulk-delete-btn" disabled onclick="showBulkDeleteModal()">
                    <i class="fas fa-trash"></i> Delete Selected (<span id="selected-count">0</span>)
                </button>
                <button type="button" class="btn btn-export" onclick="handleExport()">
                    <i class="fas fa-download"></i> Export Data
                </button>
                
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="search" id="liveSearch" class="search-box" placeholder="Search Department..." autocomplete="off" />
                    <span id="clearSearch" class="clear-search">&times;</span>
                </div>
            </div>
        </div>

        <form method="post" id="bulk-form">
            <table>
                <thead>
                    <tr>
                        <th class="checkbox-cell">
                            <input type="checkbox" id="select-all" onclick="toggleSelectAll()">
                        </th>
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
                    </tr>
                </thead>
                <tbody id="tableData">
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $id = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
                            $dname = htmlspecialchars($row['dname'], ENT_QUOTES, 'UTF-8');

                            echo "<tr class='data-row' data-id='{$id}' data-name='{$dname}'>";
                            echo "<td class='checkbox-cell'><input type='checkbox' name='selected_ids[]' value='{$id}' class='row-checkbox' onchange='updateBulkActions()'></td>";
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
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr id='no-data-row'><td colspan='11' style='text-align:center; padding: 20px;'>No data found.</td></tr>";
                    }
                    $conn->close();
                    ?>
                    <tr id="no-search-row" style="display:none;">
                        <td colspan="11" style="text-align:center; padding: 20px;">No departments found matching your search.</td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>

    <!-- Bulk Delete Modal -->
    <div class="delete-overlay" id="delete-overlay">
        <div class="delete-modal">
            <button type="button" class="delete-close" id="delete-close">&times;</button>
            <div class="delete-modal-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="delete-title">Delete Departments?</div>
            <div class="delete-text" id="delete-dept-text">Are you sure you want to delete the selected departments?</div>
            <div class="delete-actions">
                <button type="button" class="btn-delete-cancel" id="delete-cancel">Cancel</button>
                <button type="button" class="btn-delete-confirm" id="delete-confirm">Delete</button>
            </div>
        </div>
    </div>

    <script>
        // =============================
        // EXPORT FUNCTION
        // =============================
        function handleExport() {
            const form = document.getElementById('bulk-form');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'export';
            input.value = '1';
            form.appendChild(input);
            form.submit();
        }

        // =============================
        // BULK DELETE MODAL
        // =============================
        function showBulkDeleteModal() {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            const count = checkboxes.length;

            if (count === 0) {
                return;
            }

            const selectedNames = [];
            checkboxes.forEach(cb => {
                const row = cb.closest('tr');
                const name = row.getAttribute('data-name');
                if (name) selectedNames.push(name);
            });

            const deleteText = document.getElementById('delete-dept-text');
            
            if (count === 1) {
                deleteText.textContent = `Are you sure you want to delete "${selectedNames[0]}"? This action cannot be undone.`;
            } else if (count <= 3) {
                deleteText.textContent = `Are you sure you want to delete these ${count} departments: ${selectedNames.join(', ')}? This action cannot be undone.`;
            } else {
                deleteText.textContent = `Are you sure you want to delete ${count} departments? This action cannot be undone.`;
            }

            document.getElementById('delete-overlay').style.display = 'flex';
        }

        function hideDeleteModal() {
            document.getElementById('delete-overlay').style.display = 'none';
        }

        document.getElementById('delete-close').onclick = hideDeleteModal;
        document.getElementById('delete-cancel').onclick = hideDeleteModal;

        document.getElementById('delete-confirm').onclick = function() {
            const form = document.getElementById('bulk-form');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'bulk_delete';
            input.value = '1';
            form.appendChild(input);
            form.submit();
        };

        document.getElementById('delete-overlay').addEventListener('click', function(e) {
            if (e.target === this) hideDeleteModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') hideDeleteModal();
        });

        // =============================
        // SELECT ALL / DESELECT ALL
        // =============================
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.row-checkbox');
            const rows = document.querySelectorAll('.data-row');

            checkboxes.forEach((checkbox, index) => {
                if (rows[index] && rows[index].style.display !== 'none') {
                    checkbox.checked = selectAllCheckbox.checked;
                    if (selectAllCheckbox.checked) {
                        rows[index].classList.add('selected');
                    } else {
                        rows[index].classList.remove('selected');
                    }
                }
            });
            updateBulkActions();
        }

        // =============================
        // UPDATE BULK ACTION BUTTONS
        // =============================
        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
            const selectedCount = document.getElementById('selected-count');
            const selectAllCheckbox = document.getElementById('select-all');
            const visibleCheckboxes = Array.from(document.querySelectorAll('.row-checkbox')).filter(cb => {
                return cb.closest('tr').style.display !== 'none';
            });

            selectedCount.textContent = checkboxes.length;
            bulkDeleteBtn.disabled = checkboxes.length === 0;

            const checkedVisible = Array.from(checkboxes).filter(cb => cb.closest('tr').style.display !== 'none').length;
            if (checkedVisible === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedVisible === visibleCheckboxes.length && visibleCheckboxes.length > 0) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }

            document.querySelectorAll('.data-row').forEach(row => {
                const checkbox = row.querySelector('.row-checkbox');
                if (checkbox && checkbox.checked) {
                    row.classList.add('selected');
                } else {
                    row.classList.remove('selected');
                }
            });
        }

        // =============================
        // LIVE SEARCH
        // =============================
        const searchInput = document.getElementById('liveSearch');
        const tableData = document.getElementById('tableData');
        const clearBtn = document.getElementById('clearSearch');
        const noSearchRow = document.getElementById('no-search-row');

        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function resetTable() {
            const rows = tableData.getElementsByClassName('data-row');
            for (let i = 0; i < rows.length; i++) {
                rows[i].style.display = '';
                const cells = rows[i].getElementsByTagName('td');
                for (let c = 1; c < cells.length - 1; c++) {
                    cells[c].innerHTML = cells[c].textContent;
                }
            }
            if (noSearchRow) noSearchRow.style.display = 'none';
            updateBulkActions();
        }

        searchInput.addEventListener('input', function() {
            const value = this.value.trim();
            clearBtn.style.display = value ? 'block' : 'none';

            if (!value) {
                resetTable();
                return;
            }

            const rows = tableData.getElementsByClassName('data-row');
            let visibleCount = 0;

            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let rowMatches = false;

                for (let c = 1; c < cells.length - 1; c++) {
                    const cellText = cells[c].textContent;
                    const regex = new RegExp(`(${escapeRegExp(value)})`, 'gi');
                    const highlighted = cellText.replace(regex, '<span class="highlight">$1</span>');
                    cells[c].innerHTML = highlighted;

                    if (cellText.toLowerCase().includes(value.toLowerCase())) rowMatches = true;
                }

                rows[i].style.display = rowMatches ? '' : 'none';
                if (rowMatches) visibleCount++;

                const checkbox = rows[i].querySelector('.row-checkbox');
                if (!rowMatches && checkbox && checkbox.checked) {
                    checkbox.checked = false;
                }
            }

            if (noSearchRow) {
                noSearchRow.style.display = visibleCount === 0 ? '' : 'none';
            }
            updateBulkActions();
        });

        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearBtn.style.display = 'none';
            resetTable();
            searchInput.focus();
        });

        searchInput.addEventListener('blur', function() {
            if (!this.value.trim()) {
                clearBtn.style.display = 'none';
            }
        });

        // Row click to select
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.data-row');

            rows.forEach(row => {
                row.addEventListener('click', function(e) {
                    if (e.target.closest('a') || e.target.closest('i.fa-edit') || e.target.type === 'checkbox') {
                        return;
                    }

                    const checkbox = this.querySelector('.row-checkbox');
                    if (checkbox && this.style.display !== 'none') {
                        checkbox.checked = !checkbox.checked;
                        updateBulkActions();
                    }
                });
            });
        });
    </script>

    <?php include '../footer.php'; ?>
</body>

</html>
