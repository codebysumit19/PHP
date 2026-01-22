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
// UPDATE DEPARTMENT
// ---------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id']) && !isset($_POST['bulk_delete'])) {
    $id = $_POST['id'];
    $country_code = htmlspecialchars(trim($_POST['country_code'] ?? '+91'), ENT_QUOTES, 'UTF-8');

    $stmt = $conn->prepare(
        "UPDATE departments
         SET dname=?, email=?, country_code=?, number=?, resp=?, budget=?, status=?, description=?
         WHERE department_id=?"
    );
    $stmt->bind_param(
        "sssssssss",
        $_POST['dname'],
        $_POST['email'],
        $country_code,
        $_POST['number'],
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

// --- PAGINATION CONFIG ---
$perPage = 10; // rows per page
$page = isset($_GET['page']) && ctype_digit($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Count total departments
$countResult = $conn->query("SELECT COUNT(*) AS total FROM departments");
$rowCount = $countResult->fetch_assoc();
$totalRows = (int)$rowCount['total'];
$totalPages = $totalRows > 0 ? (int)ceil($totalRows / $perPage) : 1;
if ($page > $totalPages) $page = $totalPages;

// Calculate offset
$offset = ($page - 1) * $perPage;

// Main query with LIMIT/OFFSET + employee count
$result = $conn->query("
    SELECT 
        d.id, d.department_id, d.dname, d.email, d.country_code, d.number,
        d.resp, d.budget, d.status, d.description,
        COALESCE(COUNT(e.id), 0) as nemployees
    FROM departments d
    LEFT JOIN employees e ON d.department_id = e.department_id
    GROUP BY d.id, d.department_id, d.dname, d.email, d.country_code, d.number, 
             d.resp, d.budget, d.status, d.description
    ORDER BY d.dname ASC
    LIMIT $perPage OFFSET $offset
");

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

        /* Export Dropdown */
        .export-dropdown {
            position: relative;
            display: inline-block;
        }

        .export-dropdown-content {
            display: none;
            position: absolute;
            background: #fff;
            min-width: 140px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 6px;
            overflow: hidden;
            top: calc(100% + 6px);
            right: 0;
            z-index: 1000;
        }

        .export-dropdown-content a {
            color: #111827;
            padding: 10px 14px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            transition: background 0.2s;
        }

        .export-dropdown-content a i {
            width: 16px;
            font-size: 14px;
        }

        .export-dropdown-content a:hover {
            background: #e5e7eb;
        }

        .export-dropdown.active .export-dropdown-content {
            display: block;
        }

        /* Combined Search Box */
        .search-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 8px 12px;
            min-width: 350px;
            gap: 8px;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .search-wrapper:focus-within {
            border-color: #68A691;
            box-shadow: 0 0 0 3px rgba(104, 166, 145, 0.1);
        }

        .search-icon {
            font-size: 16px;
            color: #9ca3af;
            flex-shrink: 0;
        }

        .search-box {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 14px;
            color: #111827;
            outline: none;
            padding: 0;
            min-width: 0;
        }

        .search-box::placeholder {
            color: #9ca3af;
        }

        .search-box::-webkit-search-cancel-button,
        .search-box::-webkit-search-decoration {
            display: none;
            -webkit-appearance: none;
        }

        /* Search Controls - Right side */
        .search-controls {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
            flex-shrink: 0;
        }

        .clear-btn {
            background: transparent;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .clear-btn:hover {
            background: #f3f4f6;
            color: #111827;
        }

        .clear-btn i {
            font-size: 14px;
        }

        .search-counter {
            font-size: 13px;
            color: #6b7280;
            font-weight: 600;
            min-width: 40px;
            text-align: center;
            padding: 0 4px;
        }

        .nav-arrow {
            background: transparent;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 4px 6px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .nav-arrow:hover:not(:disabled) {
            background: #f3f4f6;
            color: #111827;
        }

        .nav-arrow:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .nav-arrow i {
            font-size: 14px;
        }

        /* Match highlighting */
        .highlight {
            background-color: #fef3c7;
            font-weight: 600;
            padding: 2px 4px;
            border-radius: 3px;
        }

        .highlight-current {
            background-color: #fbbf24 !important;
            font-weight: 700;
            color: #000;
            padding: 2px 4px;
            border-radius: 3px;
        }

        .current-match {
            background: #dbeafe !important;
        }

        .current-match td {
            background: #dbeafe !important;
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



        .pagination {
    margin-top: 16px;
    display: flex;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}

/* Buttons: Prev, Next, numbers, Go */
.pagination-link {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    text-decoration: none;
    margin: 0 2px;
    background: #fff;
    color: #111827;
    font-size: 14px;
    transition: background-color 0.2s ease, color 0.2s ease,
                box-shadow 0.2s ease, transform 0.1s ease;
}

/* Hover (for Go, numbers, Prev/Next) */
.pagination-link:hover:not(.disabled):not(.active) {
    background-color: #e5e7eb;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transform: translateY(-1px);
}

/* Current page */
.pagination-link.active {
    background: #111827;
    color: #f9fafb;
}

/* Disabled Prev/Next */
.pagination-link.disabled {
    background: #f9fafb;
    color: #9ca3af;
    pointer-events: none;
}

/* Optional: Go-to-page input look */
.pagination input[type="number"] {
    width: 60px;
    padding: 4px;
    margin: 0 4px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
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

            .search-wrapper {
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

                <div class="export-dropdown">
                    <button type="button" class="btn btn-export" id="export-btn">
                        <i class="fas fa-download"></i> Export
                        <i class="fas fa-chevron-down" style="font-size: 10px;"></i>
                    </button>
                    <div class="export-dropdown-content" id="export-menu">
                        <a href="#" onclick="handleExport('csv'); return false;">
                            <i class="fas fa-file-csv"></i> CSV
                        </a>
                        <a href="#" onclick="handleExport('pdf'); return false;">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                        <a href="#" onclick="handleExport('excel'); return false;">
                            <i class="fas fa-file-excel"></i> Excel
                        </a>
                    </div>
                </div>

                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="search" id="liveSearch" class="search-box" placeholder="Search..." autocomplete="off" maxlength="100" />

                    <div class="search-controls">
                        <button type="button" id="clearSearch" class="clear-btn" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                        <span class="search-counter" id="search-counter" style="display: none;">0/0</span>
                        <button type="button" class="nav-arrow" id="search-prev" title="Previous" disabled style="display: none;">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                        <button type="button" class="nav-arrow" id="search-next" title="Next" disabled style="display: none;">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
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
                            $country_code = htmlspecialchars($row['country_code'] ?? '+91', ENT_QUOTES, 'UTF-8');
                            $contact = htmlspecialchars($row['number'], ENT_QUOTES, 'UTF-8');

                            echo "<tr class='data-row' data-id='{$id}' data-name='{$dname}'>";
                            echo "<td class='checkbox-cell'><input type='checkbox' name='selected_ids[]' value='{$id}' class='row-checkbox' onchange='updateBulkActions()'></td>";
                            echo "<td>{$dname}</td>";
                            echo "<td>" . htmlspecialchars($row['department_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td>{$country_code} {$contact}</td>";
                            echo "<td>" . htmlspecialchars($row['nemployees']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['resp']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['budget']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                            echo "<td><a href='update.php?id={$id}'><i class='fas fa-edit'></i></a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr id='no-data-row'><td colspan='11' style='text-align:center; padding: 20px;'>No data found. <a href='form.php' style='color: #2563eb;'><i class='fas fa-plus'></i> Add your first department</a></td></tr>";
                    }
                    $conn->close();
                    ?>
                    <tr id="no-search-row" style="display:none;">
                        <td colspan="11" style="text-align:center; padding: 20px;">No departments found matching your search.</td>
                    </tr>
                </tbody>
            </table>
            <?php if ($totalRows > 0): ?>
    <div class="pagination">
        <!-- Go to page -->
        <span style="font-size:13px; color:#6b7280; margin-right:8px;">
            Go to page:
            <input
                type="number"
                id="goto-page"
                min="1"
                max="<?= $totalPages ?>"
                value="<?= $page ?>"
                style="width:60px; padding:4px; margin:0 4px; border:1px solid #d1d5db; border-radius:6px; font-size:13px;"
            >
            <button
                type="button"
                id="goto-btn"
                class="pagination-link"
            >
                Go
            </button>
        </span>

        <!-- Prev -->
        <a href="?page=<?= max(1, $page - 1) ?>"
           class="pagination-link <?= $page <= 1 ? 'disabled' : '' ?>">
            &laquo; Prev
        </a>

        <!-- Page numbers -->
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="?page=<?= $p ?>"
               class="pagination-link <?= $p == $page ? 'active' : '' ?>">
                <?= $p ?>
            </a>
        <?php endfor; ?>

        <!-- Next -->
        <a href="?page=<?= min($totalPages, $page + 1) ?>"
           class="pagination-link <?= $page >= $totalPages ? 'disabled' : '' ?>">
            Next &raquo;
        </a>

        <!-- Info -->
        <span style="font-size:13px; color:#6b7280; margin-left:8px;">
            Page <?= $page ?> of <?= $totalPages ?> · <?= $totalRows ?> departments
        </span>
    </div>
<?php endif; ?>

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
        const exportBtn = document.getElementById('export-btn');
        const exportMenu = document.getElementById('export-menu');
        const exportDropdown = document.querySelector('.export-dropdown');

        if (exportBtn && exportMenu) {
            exportBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                exportDropdown.classList.toggle('active');
            });

            document.addEventListener('click', function(e) {
                if (!exportDropdown.contains(e.target)) {
                    exportDropdown.classList.remove('active');
                }
            });
        }

        function handleExport(format) {
            const selectedIds = [];
            document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
                selectedIds.push(cb.value);
            });

            let url = 'export.php?format=' + format;

            if (selectedIds.length > 0) {
                url += '&ids=' + selectedIds.join(',');
            }

            window.location.href = url;
            exportDropdown.classList.remove('active');
        }

        // =============================
        // BULK DELETE MODAL
        // =============================
        function showBulkDeleteModal() {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            const count = checkboxes.length;

            if (count === 0) return;

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
        // LIVE SEARCH WITH NAVIGATION
        // =============================
        const searchInput = document.getElementById('liveSearch');
        const tableData = document.getElementById('tableData');
        const clearBtn = document.getElementById('clearSearch');
        const noSearchRow = document.getElementById('no-search-row');
        const searchCounter = document.getElementById('search-counter');
        const searchPrev = document.getElementById('search-prev');
        const searchNext = document.getElementById('search-next');

        let matchedRows = [];
        let currentMatchIndex = -1;

        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function resetTable() {
            const rows = tableData.getElementsByClassName('data-row');
            for (let i = 0; i < rows.length; i++) {
                rows[i].style.display = '';
                rows[i].classList.remove('current-match');
                const cells = rows[i].getElementsByTagName('td');
                for (let c = 1; c < cells.length - 1; c++) {
                    cells[c].innerHTML = cells[c].textContent;
                }
            }
            if (noSearchRow) noSearchRow.style.display = 'none';
            matchedRows = [];
            currentMatchIndex = -1;
            updateSearchNav();
            updateBulkActions();
        }

        function updateSearchNav() {
            const hasMatches = matchedRows.length > 0;
            const hasSearchValue = searchInput.value.trim() !== '';
            
            // Show/hide controls
            if (hasSearchValue) {
                clearBtn.style.display = 'flex';
            } else {
                clearBtn.style.display = 'none';
            }
            
            if (hasMatches) {
                searchCounter.style.display = 'block';
                searchPrev.style.display = 'flex';
                searchNext.style.display = 'flex';
                searchCounter.textContent = `${currentMatchIndex + 1}/${matchedRows.length}`;
            } else if (hasSearchValue) {
                searchCounter.style.display = 'block';
                searchPrev.style.display = 'none';
                searchNext.style.display = 'none';
                searchCounter.textContent = '0/0';
            } else {
                searchCounter.style.display = 'none';
                searchPrev.style.display = 'none';
                searchNext.style.display = 'none';
            }
            
            searchPrev.disabled = matchedRows.length === 0 || currentMatchIndex === 0;
            searchNext.disabled = matchedRows.length === 0 || currentMatchIndex === matchedRows.length - 1;
        }

        function highlightCurrentMatch() {
            // Remove previous highlight
            document.querySelectorAll('.highlight-current').forEach(el => {
                el.classList.remove('highlight-current');
            });
            
            document.querySelectorAll('.current-match').forEach(row => {
                row.classList.remove('current-match');
            });
            
            if (currentMatchIndex >= 0 && currentMatchIndex < matchedRows.length) {
                const row = matchedRows[currentMatchIndex];
                row.classList.add('current-match');
                
                // Highlight the first match in this row
                const cells = row.getElementsByTagName('td');
                for (let c = 1; c < cells.length - 1; c++) {
                    const highlights = cells[c].getElementsByClassName('highlight');
                    if (highlights.length > 0) {
                        highlights[0].classList.add('highlight-current');
                        break;
                    }
                }
                
                // Scroll to current match
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        searchInput.addEventListener('input', function() {
            const value = this.value.trim();

            if (!value) {
                resetTable();
                return;
            }

            const rows = tableData.getElementsByClassName('data-row');
            matchedRows = [];
            let visibleCount = 0;

            for (let i = 0; i < rows.length; i++) {
                rows[i].classList.remove('current-match');
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
                if (rowMatches) {
                    visibleCount++;
                    matchedRows.push(rows[i]);
                }

                const checkbox = rows[i].querySelector('.row-checkbox');
                if (!rowMatches && checkbox && checkbox.checked) {
                    checkbox.checked = false;
                }
            }

            if (noSearchRow) {
                noSearchRow.style.display = visibleCount === 0 ? '' : 'none';
            }
            
            // Set first match as current
            currentMatchIndex = matchedRows.length > 0 ? 0 : -1;
            highlightCurrentMatch();
            updateSearchNav();
            updateBulkActions();
        });

        // Previous match
        if (searchPrev) {
            searchPrev.addEventListener('click', function() {
                if (currentMatchIndex > 0) {
                    currentMatchIndex--;
                    highlightCurrentMatch();
                    updateSearchNav();
                }
            });
        }

        // Next match
        if (searchNext) {
            searchNext.addEventListener('click', function() {
                if (currentMatchIndex < matchedRows.length - 1) {
                    currentMatchIndex++;
                    highlightCurrentMatch();
                    updateSearchNav();
                }
            });
        }

        // Keyboard navigation
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && matchedRows.length > 0) {
                e.preventDefault();
                if (e.shiftKey) {
                    // Shift+Enter = Previous
                    if (currentMatchIndex > 0) {
                        currentMatchIndex--;
                    } else {
                        currentMatchIndex = matchedRows.length - 1;
                    }
                } else {
                    // Enter = Next
                    if (currentMatchIndex < matchedRows.length - 1) {
                        currentMatchIndex++;
                    } else {
                        currentMatchIndex = 0;
                    }
                }
                highlightCurrentMatch();
                updateSearchNav();
            }
        });

        // Clear button
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                this.style.display = 'none';
                resetTable();
                searchInput.focus();
            });
        }

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

            // =============================
    // GO TO PAGE (pagination)
    // =============================
    document.addEventListener('DOMContentLoaded', function () {
        const gotoInput = document.getElementById('goto-page');
        const gotoBtn   = document.getElementById('goto-btn');
        if (!gotoInput || !gotoBtn) return;

        const maxPageAttr = gotoInput.getAttribute('max');
        const maxPage = maxPageAttr ? parseInt(maxPageAttr, 10) : 1;

        function goToPage() {
            let p = parseInt(gotoInput.value, 10);
            if (isNaN(p)) return;

            if (p < 1) p = 1;
            if (p > maxPage) p = maxPage;

            window.location.href = '?page=' + p;
        }

        gotoBtn.addEventListener('click', function (e) {
            e.preventDefault();
            goToPage();
        });

        gotoInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                goToPage();
            }
        });
    });

    </script>

    <?php include '../footer.php'; ?>
</body>

</html>
