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
    $stmt = $conn->prepare("SELECT * FROM events WHERE id IN ($placeholders) ORDER BY date DESC, stime DESC");
    $types = str_repeat('s', count($selectedIds));
    $stmt->bind_param($types, ...$selectedIds);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM events ORDER BY date DESC, stime DESC");
}

// ===========================================
// CSV EXPORT
// ===========================================
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="events_' . date('Y-m-d_His') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, [
        'Event Name', 'Department ID', 'Address', 'Date', 'Start Time', 'End Time', 'Type', 'Happened'
    ]);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['name'], $row['department_id'], $row['address'],
                $row['date'], $row['stime'], $row['etime'], $row['type'], $row['happend']
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
    $pdf->SetTitle('Events Report');
    $pdf->SetSubject('Event Data Export');
    
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(TRUE, 10);
    $pdf->AddPage();
    
    // Title
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor(104, 166, 145);
    $pdf->Cell(0, 10, 'Events Report', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(107, 114, 128);
    $pdf->Cell(0, 6, 'Generated on ' . date('F d, Y h:i A'), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Table header
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor(104, 166, 145);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(40, 7, 'Event Name', 1, 0, 'C', 1);
    $pdf->Cell(25, 7, 'Dept ID', 1, 0, 'C', 1);
    $pdf->Cell(45, 7, 'Address', 1, 0, 'C', 1);
    $pdf->Cell(25, 7, 'Date', 1, 0, 'C', 1);
    $pdf->Cell(20, 7, 'Start', 1, 0, 'C', 1);
    $pdf->Cell(20, 7, 'End', 1, 0, 'C', 1);
    $pdf->Cell(35, 7, 'Type', 1, 0, 'C', 1);
    $pdf->Cell(20, 7, 'Happened', 1, 1, 'C', 1);
    
    // Table data
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor(0, 0, 0);
    $fill = false;
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $pdf->SetFillColor(249, 250, 251);
            $pdf->Cell(40, 6, substr($row['name'], 0, 30), 1, 0, 'L', $fill);
            $pdf->Cell(25, 6, $row['department_id'], 1, 0, 'C', $fill);
            $pdf->Cell(45, 6, substr($row['address'], 0, 35), 1, 0, 'L', $fill);
            $pdf->Cell(25, 6, $row['date'], 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, $row['stime'], 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, $row['etime'], 1, 0, 'C', $fill);
            $pdf->Cell(35, 6, substr($row['type'], 0, 25), 1, 0, 'L', $fill);
            $pdf->Cell(20, 6, $row['happend'], 1, 1, 'C', $fill);
            $fill = !$fill;
        }
    }
    
    $conn->close();
    $pdf->Output('events_' . date('Y-m-d_His') . '.pdf', 'D');
    exit;
}

// ===========================================
// EXCEL EXPORT
// ===========================================
elseif ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename=events_' . date('Y-m-d_His') . '.xls');
    
    echo '<table border="1">
        <thead>
            <tr style="background-color: #68A691; color: white; font-weight: bold;">
                <th>Event Name</th>
                <th>Department ID</th>
                <th>Address</th>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Type</th>
                <th>Happened</th>
            </tr>
        </thead>
        <tbody>';
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                <td>' . htmlspecialchars($row['name']) . '</td>
                <td>' . htmlspecialchars($row['department_id']) . '</td>
                <td>' . htmlspecialchars($row['address']) . '</td>
                <td>' . htmlspecialchars($row['date']) . '</td>
                <td>' . htmlspecialchars($row['stime']) . '</td>
                <td>' . htmlspecialchars($row['etime']) . '</td>
                <td>' . htmlspecialchars($row['type']) . '</td>
                <td>' . htmlspecialchars($row['happend']) . '</td>
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
