<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit;
}

$timeout = 50 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    $_SESSION = [];
    session_destroy();
    header('Location: ../login.php');
    exit;
}
$_SESSION['last_activity'] = time();

require_once '../db.php';

// Get format and selected IDs
$format = $_GET['format'] ?? 'csv';
$selectedIds = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];

// Get data
if (!empty($selectedIds)) {
    $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id IN ($placeholders) ORDER BY pname ASC");
    $types = str_repeat('s', count($selectedIds));
    $stmt->bind_param($types, ...$selectedIds);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM projects ORDER BY pname ASC");
}

// ===========================================
// CSV EXPORT
// ===========================================
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="projects_' . date('Y-m-d_His') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, [
        'Project Name', 'Department ID', 'Client/Company', 'Project Manager', 
        'Start Date', 'End Date', 'Status', 'Description'
    ]);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['pname'], $row['department_id'], $row['cname'], $row['pmanager'],
                $row['sdate'], $row['edate'], $row['status'], $row['pdescription']
            ]);
        }
    }
    
    fclose($output);
    $conn->close();
    exit;
}

// ===========================================
// PDF EXPORT
// ===========================================
elseif ($format === 'pdf') {
    require_once '../vendor/autoload.php';
    
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    $pdf->SetCreator('PHP CRUD App');
    $pdf->SetAuthor($_SESSION['userName'] ?? 'User');
    $pdf->SetTitle('Projects Report');
    $pdf->SetSubject('Project Data Export');
    
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(TRUE, 10);
    $pdf->AddPage();
    
    // Title
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor(104, 166, 145);
    $pdf->Cell(0, 10, 'Projects Report', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(107, 114, 128);
    $pdf->Cell(0, 6, 'Generated on ' . date('F d, Y h:i A'), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Table header
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor(104, 166, 145);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(40, 7, 'Project Name', 1, 0, 'C', 1);
    $pdf->Cell(22, 7, 'Dept ID', 1, 0, 'C', 1);
    $pdf->Cell(38, 7, 'Client/Company', 1, 0, 'C', 1);
    $pdf->Cell(35, 7, 'Manager', 1, 0, 'C', 1);
    $pdf->Cell(22, 7, 'Start Date', 1, 0, 'C', 1);
    $pdf->Cell(22, 7, 'End Date', 1, 0, 'C', 1);
    $pdf->Cell(28, 7, 'Status', 1, 0, 'C', 1);
    $pdf->Cell(48, 7, 'Description', 1, 1, 'C', 1);
    
    // Table data
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(0, 0, 0);
    $fill = false;
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $pdf->SetFillColor(249, 250, 251);
            $pdf->Cell(40, 6, substr($row['pname'], 0, 32), 1, 0, 'L', $fill);
            $pdf->Cell(22, 6, $row['department_id'], 1, 0, 'C', $fill);
            $pdf->Cell(38, 6, substr($row['cname'], 0, 30), 1, 0, 'L', $fill);
            $pdf->Cell(35, 6, substr($row['pmanager'], 0, 28), 1, 0, 'L', $fill);
            $pdf->Cell(22, 6, $row['sdate'], 1, 0, 'C', $fill);
            $pdf->Cell(22, 6, $row['edate'], 1, 0, 'C', $fill);
            $pdf->Cell(28, 6, $row['status'], 1, 0, 'C', $fill);
            $pdf->Cell(48, 6, substr($row['pdescription'], 0, 40), 1, 1, 'L', $fill);
            $fill = !$fill;
        }
    }
    
    $conn->close();
    $pdf->Output('projects_' . date('Y-m-d_His') . '.pdf', 'D');
    exit;
}

// ===========================================
// EXCEL EXPORT
// ===========================================
elseif ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename=projects_' . date('Y-m-d_His') . '.xls');
    
    echo '<table border="1">
        <thead>
            <tr style="background-color: #68A691; color: white; font-weight: bold;">
                <th>Project Name</th>
                <th>Department ID</th>
                <th>Client/Company Name</th>
                <th>Project Manager</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>';
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                <td>' . htmlspecialchars($row['pname']) . '</td>
                <td>' . htmlspecialchars($row['department_id']) . '</td>
                <td>' . htmlspecialchars($row['cname']) . '</td>
                <td>' . htmlspecialchars($row['pmanager']) . '</td>
                <td>' . htmlspecialchars($row['sdate']) . '</td>
                <td>' . htmlspecialchars($row['edate']) . '</td>
                <td>' . htmlspecialchars($row['status']) . '</td>
                <td>' . htmlspecialchars($row['pdescription']) . '</td>
            </tr>';
        }
    }
    
    echo '</tbody></table>';
    $conn->close();
    exit;
}

header("Location: get.php");
exit;
?>
