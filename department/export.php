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

// Calculate employee count dynamically with JOIN
if (!empty($selectedIds)) {
    $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
    $stmt = $conn->prepare("
        SELECT d.*, COALESCE(COUNT(e.id), 0) as nemployees
        FROM departments d
        LEFT JOIN employees e ON d.department_id = e.department_id
        WHERE d.id IN ($placeholders)
        GROUP BY d.id, d.department_id, d.dname, d.email, d.country_code, d.number, d.resp, d.budget, d.status, d.description
        ORDER BY d.dname ASC
    ");
    $types = str_repeat('s', count($selectedIds));
    $stmt->bind_param($types, ...$selectedIds);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("
        SELECT d.*, COALESCE(COUNT(e.id), 0) as nemployees
        FROM departments d
        LEFT JOIN employees e ON d.department_id = e.department_id
        GROUP BY d.id, d.department_id, d.dname, d.email, d.country_code, d.number, d.resp, d.budget, d.status, d.description
        ORDER BY d.dname ASC
    ");
}

// ===========================================
// CSV EXPORT
// ===========================================
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="departments_' . date('Y-m-d_His') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, [
        'Department ID', 'Department Name', 'Email', 'Country Code', 'Contact Number',
        'Employees', 'Responsibilities', 'Annual Budget', 'Status', 'Description'
    ]);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['department_id'], $row['dname'], $row['email'],
                $row['country_code'] ?? '+91', $row['number'], $row['nemployees'],
                $row['resp'], $row['budget'], $row['status'], $row['description']
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
    
    // Create TCPDF instance
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('PHP CRUD App');
    $pdf->SetAuthor($_SESSION['userName'] ?? 'User');
    $pdf->SetTitle('Departments Report');
    $pdf->SetSubject('Department Data Export');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(TRUE, 10);
    
    // Add a page
    $pdf->AddPage();
    
    // Title
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor(104, 166, 145);
    $pdf->Cell(0, 10, 'Departments Report', 0, 1, 'C');
    
    // Date
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(107, 114, 128);
    $pdf->Cell(0, 6, 'Generated on ' . date('F d, Y h:i A'), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Table header
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor(104, 166, 145);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(35, 7, 'Department Name', 1, 0, 'C', 1);
    $pdf->Cell(20, 7, 'Dept ID', 1, 0, 'C', 1);
    $pdf->Cell(40, 7, 'Email', 1, 0, 'C', 1);
    $pdf->Cell(28, 7, 'Phone', 1, 0, 'C', 1);
    $pdf->Cell(15, 7, 'Emp', 1, 0, 'C', 1);
    $pdf->Cell(45, 7, 'Responsibilities', 1, 0, 'C', 1);
    $pdf->Cell(30, 7, 'Budget', 1, 0, 'C', 1);
    $pdf->Cell(20, 7, 'Status', 1, 0, 'C', 1);
    $pdf->Cell(42, 7, 'Description', 1, 1, 'C', 1);
    
    // Table data
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(0, 0, 0);
    $fill = false;
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $pdf->SetFillColor(249, 250, 251);
            $pdf->Cell(35, 6, $row['dname'], 1, 0, 'L', $fill);
            $pdf->Cell(20, 6, $row['department_id'], 1, 0, 'C', $fill);
            $pdf->Cell(40, 6, $row['email'], 1, 0, 'L', $fill);
            $pdf->Cell(28, 6, ($row['country_code'] ?? '+91') . ' ' . $row['number'], 1, 0, 'L', $fill);
            $pdf->Cell(15, 6, $row['nemployees'], 1, 0, 'C', $fill);
            $pdf->Cell(45, 6, substr($row['resp'], 0, 40), 1, 0, 'L', $fill);
            $pdf->Cell(30, 6, $row['budget'], 1, 0, 'L', $fill);
            $pdf->Cell(20, 6, $row['status'], 1, 0, 'C', $fill);
            $pdf->Cell(42, 6, substr($row['description'], 0, 35), 1, 1, 'L', $fill);
            $fill = !$fill;
        }
    }
    
    $conn->close();
    
    // Output PDF
    $pdf->Output('departments_' . date('Y-m-d_His') . '.pdf', 'D');
    exit;
}

// ===========================================
// EXCEL EXPORT
// ===========================================
elseif ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename=departments_' . date('Y-m-d_His') . '.xls');
    
    echo '<table border="1">
        <thead>
            <tr style="background-color: #68A691; color: white; font-weight: bold;">
                <th>Department Name</th>
                <th>Department ID</th>
                <th>Email</th>
                <th>Country Code</th>
                <th>Contact Number</th>
                <th>Employees</th>
                <th>Responsibilities</th>
                <th>Annual Budget</th>
                <th>Status</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>';
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                <td>' . htmlspecialchars($row['dname']) . '</td>
                <td>' . htmlspecialchars($row['department_id']) . '</td>
                <td>' . htmlspecialchars($row['email']) . '</td>
                <td>' . htmlspecialchars($row['country_code'] ?? '+91') . '</td>
                <td>' . htmlspecialchars($row['number']) . '</td>
                <td style="text-align: center;">' . $row['nemployees'] . '</td>
                <td>' . htmlspecialchars($row['resp']) . '</td>
                <td>' . htmlspecialchars($row['budget']) . '</td>
                <td>' . htmlspecialchars($row['status']) . '</td>
                <td>' . htmlspecialchars($row['description']) . '</td>
            </tr>';
        }
    }
    
    echo '</tbody></table>';
    
    $conn->close();
    exit;
}

// Default - redirect back if invalid format
header("Location: get.php");
exit;
?>
