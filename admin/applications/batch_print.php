<?php
require_once("../../include/initialize.php");

if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

global $mydb;

$ids = isset($_GET['ids']) ? $_GET['ids'] : '';
$ids_array = !empty($ids) ? array_map('intval', explode(',', $ids)) : array();

if (empty($ids_array)) {
?>
<div class="row">
    <div class="col-lg-12">
        <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i> No examinees selected for batch printing.
        </div>
        <a href="index.php?view=schedule" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> Back to Exam Schedule
        </a>
    </div>
</div>
<?php
    exit;
}

// Fetch all applicants for batch printing
$sql = "
    SELECT a.*
    FROM tbl_applicants a
    WHERE a.APPLICANTID IN (" . implode(',', $ids_array) . ")
    AND a.EXAM_SLIP_GENERATED IS NOT NULL
    AND a.EXAM_SLIP_GENERATED != ''
    ORDER BY a.EXAM_DATE ASC, a.EXAM_TIME ASC
";
$mydb->setQuery($sql);
$mydb->executeQuery();
$applicants = $mydb->loadResultList();

if (empty($applicants)) {
?>
<div class="row">
    <div class="col-lg-12">
        <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i> No examinees with valid exam slips found.
        </div>
        <a href="index.php?view=schedule" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> Back to Exam Schedule
        </a>
    </div>
</div>
<?php
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Batch Exam Slips - <?php echo count($applicants); ?> Examinees</title>
    <link rel="stylesheet" href="<?php echo web_root;?>bootstrap/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            padding: 10px;
            background: #fff;
        }
        .batch-print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #27ae60;
            padding-bottom: 15px;
        }
        .slip-container {
            border: 1.5px solid #27ae60;
            padding: 12px;
            border-radius: 8px;
            background: white;
            page-break-after: always;
            page-break-inside: avoid;
            width: 100%;
            font-size: 11px;
        }
        .slip-header {
            text-align: center;
            border-bottom: 1px dashed #27ae60;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .slip-header h2 {
            color: #27ae60;
            margin: 2px 0;
            font-size: 13px;
            font-weight: bold;
        }
        .slip-header h3, .slip-header h4 {
            margin: 2px 0;
            font-size: 11px;
        }
        .slip-number {
            text-align: right;
            font-size: 13px;
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 5px;
        }
        .details-table {
            width: 100%;
            margin-bottom: 8px;
        }
        .details-table td {
            padding: 3px;
            border-bottom: 1px solid #dee2e6;
        }
        .details-table td:first-child {
            font-weight: bold;
            width: 35%;
        }
        .reminder-box {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            padding: 8px;
            border-radius: 4px;
            margin: 8px 0;
        }
        .reminder-box h5 {
            margin: 0 0 5px 0;
            font-size: 10px;
            font-weight: bold;
        }
        .reminder-box ol {
            margin: 0;
            padding-left: 15px;
        }
        .reminder-box li {
            margin-bottom: 2px;
        }
        .print-controls {
            text-align: center;
            margin: 10px 0 25px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .print-btn {
            padding: 10px 25px;
            font-size: 14px;
            margin: 0 10px;
        }
        .back-btn {
            background: #6c757d;
        }
        .footer-text {
            text-align: center; 
            margin-top: 10px; 
            font-size: 9px; 
            color: #6c757d;
        }
        @media print {
            body { background: white; padding: 0; }
            .print-controls { display: none !important; }
            .slip-container:last-child { page-break-after: avoid; }
        }
        @page {
            margin: 0.5cm;
            size: A5;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-controls">
        <h3>Batch Exam Slips Printing (<?php echo count($applicants); ?> slips)</h3>
        <button onclick="window.print()" class="btn btn-success btn-lg print-btn">
            <i class="fa fa-print"></i> Print All Exam Slips
        </button>
        <a href="index.php?stage=exam_slip" class="btn btn-default btn-lg print-btn back-btn">
            <i class="fa fa-arrow-left"></i> Back to Schedule
        </a>
        <p><small>Layout optimized for A5 bond paper. Use Ctrl+P to print.</small></p>
    </div>

    <?php foreach ($applicants as $applicant): ?>
    <div class="slip-container">
        <div class="slip-number">
            Slip No: <?php echo htmlspecialchars($applicant->EXAM_SLIP_NUMBER ?? 'N/A'); ?>
        </div>
        
        <div class="slip-header">
            <h2>ILOCOS SUR EDUCATIONAL ASSISTANCE AND SCHOLARSHIP PROGRAM</h2>
            <h3>EXAMINATION SLIP</h3>
        </div>

        <table class="details-table">
            <tr>
                <td>Name:</td>
                <td><strong><?php echo strtoupper(htmlspecialchars($applicant->LASTNAME . ', ' . $applicant->FIRSTNAME . ' ' . ($applicant->MIDDLENAME ?? ''))); ?></strong></td>
            </tr>
            <tr>
                <td>School:</td>
                <td><?php echo htmlspecialchars($applicant->SCHOOL ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td>Course/Program:</td>
                <td><?php echo htmlspecialchars($applicant->COURSE ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td>Year Level:</td>
                <td><?php echo htmlspecialchars($applicant->YEARLEVEL ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td>Municipality:</td>
                <td><?php echo htmlspecialchars($applicant->MUNICIPALITY ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td>Examination Date:</td>
                <td><strong><?php echo $applicant->EXAM_DATE ? date('F d, Y', strtotime($applicant->EXAM_DATE)) : 'N/A'; ?></strong></td>
            </tr>
            <tr>
                <td>Examination Time:</td>
                <td><strong><?php echo $applicant->EXAM_TIME ? date('h:i A', strtotime($applicant->EXAM_TIME)) : 'N/A'; ?></strong></td>
            </tr>
            <tr>
                <td>Venue:</td>
                <td><strong><?php echo htmlspecialchars($applicant->EXAM_VENUE ?? 'N/A'); ?></strong></td>
            </tr>
        </table>

        <div class="reminder-box">
            <h5><strong>IMPORTANT REMINDERS:</strong></h5>
            <ol>
                <li>Present this slip upon entry to the examination room.</li>
                <li>Bring the following:
                    <ul>
                        <li>Valid ID or Birth Certificate</li>
                        <li>Ballpen and Pencil</li>
                    </ul>
                </li>
                <li>Wear appropriate attire: White shirt and plain pants.</li>
                <li>Arrive at least 30 minutes before the scheduled time.</li>
                <li>Cell phones and other electronic devices are not allowed inside the examination room.</li>
                <li>Any form of cheating will result in automatic disqualification.</li>
            </ol>
        </div>

        <div class="footer-text">
            <p>Valid only on scheduled date. Non-transferrable.</p>
        </div>
    </div>
    <?php endforeach; ?>
    </div>

</body>
</html>
