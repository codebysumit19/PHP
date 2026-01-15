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
    $stmt = $conn->prepare("SELECT * FROM employees WHERE id IN ($placeholders) ORDER BY ename ASC");
    $types = str_repeat('s', count($selectedIds));
    $stmt->bind_param($types, ...$selectedIds);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM employees ORDER BY ename ASC");
}

// ===========================================
// CSV EXPORT
// ===========================================
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="employees_' . date('Y-m-d_His') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, [
        'Full Name', 'Department ID', 'DOB', 'Gender', 'Email', 'Country Code', 'Phone', 
        'Address', 'Designation', 'Currency', 'Salary', 'Joining Date', 'Aadhar'
    ]);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['ename'], $row['department_id'], $row['dob'], $row['gender'],
                $row['email'], $row['country_code'] ?? '+91', $row['pnumber'],
                $row['address'], $row['designation'], $row['currency'] ?? '₹',
                $row['salary'], $row['joining_date'], $row['aadhar']
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
    $pdf->SetTitle('Employees Report');
    $pdf->SetSubject('Employee Data Export');
    
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(TRUE, 10);
    $pdf->AddPage();
    
    // Title
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor(104, 166, 145);
    $pdf->Cell(0, 10, 'Employees Report', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(107, 114, 128);
    $pdf->Cell(0, 6, 'Generated on ' . date('F d, Y h:i A'), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Table header
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetFillColor(104, 166, 145);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(30, 7, 'Full Name', 1, 0, 'C', 1);
    $pdf->Cell(20, 7, 'Dept ID', 1, 0, 'C', 1);
    $pdf->Cell(20, 7, 'DOB', 1, 0, 'C', 1);
    $pdf->Cell(15, 7, 'Gender', 1, 0, 'C', 1);
    $pdf->Cell(35, 7, 'Email', 1, 0, 'C', 1);
    $pdf->Cell(28, 7, 'Phone', 1, 0, 'C', 1);
    $pdf->Cell(30, 7, 'Address', 1, 0, 'C', 1);
    $pdf->Cell(25, 7, 'Designation', 1, 0, 'C', 1);
    $pdf->Cell(25, 7, 'Salary', 1, 0, 'C', 1);
    $pdf->Cell(22, 7, 'Joining', 1, 0, 'C', 1);
    $pdf->Cell(25, 7, 'Aadhar', 1, 1, 'C', 1);
    
    // Table data
    $pdf->SetFont('helvetica', '', 6);
    $pdf->SetTextColor(0, 0, 0);
    $fill = false;
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $pdf->SetFillColor(249, 250, 251);
            $pdf->Cell(30, 6, substr($row['ename'], 0, 25), 1, 0, 'L', $fill);
            $pdf->Cell(20, 6, $row['department_id'], 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, $row['dob'], 1, 0, 'C', $fill);
            $pdf->Cell(15, 6, $row['gender'], 1, 0, 'C', $fill);
            $pdf->Cell(35, 6, substr($row['email'], 0, 28), 1, 0, 'L', $fill);
            $pdf->Cell(28, 6, ($row['country_code'] ?? '+91') . ' ' . $row['pnumber'], 1, 0, 'L', $fill);
            $pdf->Cell(30, 6, substr($row['address'], 0, 25), 1, 0, 'L', $fill);
            $pdf->Cell(25, 6, substr($row['designation'], 0, 20), 1, 0, 'L', $fill);
            $pdf->Cell(25, 6, ($row['currency'] ?? '₹') . ' ' . $row['salary'], 1, 0, 'R', $fill);
            $pdf->Cell(22, 6, $row['joining_date'], 1, 0, 'C', $fill);
            $pdf->Cell(25, 6, $row['aadhar'], 1, 1, 'C', $fill);
            $fill = !$fill;
        }
    }
    
    $conn->close();
    $pdf->Output('employees_' . date('Y-m-d_His') . '.pdf', 'D');
    exit;
}

// ===========================================
// EXCEL EXPORT
// ===========================================
elseif ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename=employees_' . date('Y-m-d_His') . '.xls');
    
    echo '<table border="1">
        <thead>
            <tr style="background-color: #68A691; color: white; font-weight: bold;">
                <th>Full Name</th>
                <th>Department ID</th>
                <th>DOB</th>
                <th>Gender</th>
                <th>Email</th>
                <th>Country Code</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Designation</th>
                <th>Currency</th>
                <th>Salary</th>
                <th>Joining Date</th>
                <th>Aadhar</th>
            </tr>
        </thead>
        <tbody>';
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                <td>' . htmlspecialchars($row['ename']) . '</td>
                <td>' . htmlspecialchars($row['department_id']) . '</td>
                <td>' . htmlspecialchars($row['dob']) . '</td>
                <td>' . htmlspecialchars($row['gender']) . '</td>
                <td>' . htmlspecialchars($row['email']) . '</td>
                <td>' . htmlspecialchars($row['country_code'] ?? '+91') . '</td>
                <td>' . htmlspecialchars($row['pnumber']) . '</td>
                <td>' . htmlspecialchars($row['address']) . '</td>
                <td>' . htmlspecialchars($row['designation']) . '</td>
                <td>' . htmlspecialchars($row['currency'] ?? '₹') . '</td>
                <td>' . htmlspecialchars($row['salary']) . '</td>
                <td>' . htmlspecialchars($row['joining_date']) . '</td>
                <td>' . htmlspecialchars($row['aadhar']) . '</td>
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
