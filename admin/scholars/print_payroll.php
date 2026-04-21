<?php
// No template - standalone print page
if (!isset($_SESSION['ADMIN_USERID'])) {
    session_start();
    if (!isset($_SESSION['ADMIN_USERID'])) {
        header("Location: " . web_root . "admin/index.php");
        exit;
    }
}

global $mydb;

$payroll_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get payroll details
$mydb->setQuery("SELECT * FROM tbl_payroll WHERE id = $payroll_id");
$mydb->executeQuery();
$payroll = $mydb->loadSingleResult();

if(!$payroll) {
    die("Payroll not found!");
}

// Get school year
$mydb->setQuery("SELECT school_year FROM tbl_school_years WHERE id = '{$payroll->school_year_id}'");
$mydb->executeQuery();
$school_year = $mydb->loadSingleResult();

// Get scholars for this payroll with their details
$mydb->setQuery("
    SELECT pd.*, 
           CONCAT(a.LASTNAME, ', ', a.FIRSTNAME, ' ', IFNULL(a.MIDDLENAME, '')) as FULLNAME,
           a.MUNICIPALITY, 
           a.COURSE, 
           a.YEARLEVEL,
           a.ID_NUMBER,
           a.LRN
    FROM tbl_payroll_details pd 
    INNER JOIN tbl_applicants a ON pd.scholar_id = a.APPLICANTID 
    WHERE pd.payroll_id = $payroll_id
    ORDER BY a.LASTNAME ASC
");
$mydb->executeQuery();
$scholars = $mydb->loadResultList();

// Format semester display
$semester_display = $payroll->semester;
$total_amount = 0;
foreach($scholars as $scholar) {
    $total_amount += $scholar->amount;
}

// Calculate pagination
$rows_per_page = 20;
$total_scholars = count($scholars);
$total_pages = ceil($total_scholars / $rows_per_page);
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start_index = ($page - 1) * $rows_per_page;
$page_scholars = array_slice($scholars, $start_index, $rows_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISEASP Allowance Form - <?php echo $school_year ? $school_year->school_year : 'N/A'; ?></title>
    
<style>
    * {
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background: #f2f2f2;
        margin: 0;
        padding: 20px;
    }

    .paper {
        width: 1200px;
        margin: 0 auto;
        background: #fff;
        padding: 35px 40px 60px;
        color: #000;
        border: 1px solid #ccc;
        page-break-after: avoid;
        break-inside: avoid;
    }

    .page-break {
        page-break-before: always;
        margin-top: 20px;
    }

    .header {
        text-align: center;
        margin-bottom: 25px;
        line-height: 1.4;
    }

    .header .title1,
    .header .title2 {
        font-size: 16px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .header .title3 {
        font-size: 18px;
        font-style: italic;
        font-weight: 700;
    }

    .header .payroll-info {
        font-size: 12px;
        margin-top: 5px;
        color: #555;
    }

    .page-info {
        text-align: right;
        font-size: 11px;
        color: #777;
        margin-bottom: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    th, td {
        border: 1px solid #000;
        padding: 3px 3px;
        font-size: 13px;
        vertical-align: top;
    }

    th {
        text-align: center;
        font-weight: 700;
        background-color: #f9f9f9;
    }

    .col-no { 
        width: 30px; 
        text-align: center; 
    }
    .col-name { 
        width: 130px; 
    }
    .col-course { 
        width: 350px; 
    }
    .col-id { 
        width: 100px; 
        text-align: center; 
    }
    .col-amount { 
        width: 100px; 
        text-align: right;
    }
    .col-signature { 
        width: 100px; 
        text-align: center;
    }

    .subtotal-row td {
        font-weight: 700;
        background-color: #f9f9f9;
    }

    .subtotal-label {
        text-align: right;
        font-style: italic;
    }

    .subtotal-amount {
        text-align: right;
        font-weight: 700;
    }

    .notes-section {
        margin-top: 40px;
        display: flex;
        justify-content: space-between;
        gap: 40px;
    }

    .left-notes,
    .right-notes {
        width: 48%;
        font-size: 13px;
    }

    .middle-cert {
        font-size: 13px;
        font-style: italic;
    }

    .signature-grid {
        margin-top: 30px;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 20px;
        align-items: end;
    }

    .sig-block {
        min-height: 140px;
        text-align: left;
    }

    .sig-label {
        margin-bottom: 50px;
        font-size: 12px;
        font-style: italic;
    }

    .sig-name {
        font-weight: 700;
        font-size: 14px;
        text-transform: uppercase;
        margin-top: 10px;
    }

    .sig-title {
        font-size: 11px;
        margin-top: 4px;
    }

    .right-confirmation {
        text-align: left;
        font-size: 13px;
        line-height: 1.5;
    }

    .footer-note {
        margin-top: 30px;
        text-align: center;
        font-size: 10px;
        color: #777;
        border-top: 1px dashed #ccc;
        padding-top: 15px;
    }

    .no-print {
        text-align: center;
        margin-bottom: 20px;
    }

    .no-print button {
        padding: 8px 16px;
        margin: 0 5px;
        font-size: 14px;
        cursor: pointer;
    }
    
    .pagination-controls {
        text-align: center;
        margin-top: 20px;
        padding: 10px;
    }
    
    .pagination-controls a {
        display: inline-block;
        padding: 5px 10px;
        margin: 0 5px;
        background: #27ae60;
        color: white;
        text-decoration: none;
        border-radius: 3px;
    }
    
    .pagination-controls a:hover {
        background: #229954;
    }
    
    .pagination-controls span {
        display: inline-block;
        padding: 5px 10px;
        margin: 0 5px;
        background: #f0f0f0;
        border-radius: 3px;
    }

    @media print {
        body {
            background: #fff;
            padding: 0;
            margin: 0;
        }

        .paper {
            width: 100%;
            border: none;
            padding: 15px;
            margin: 0;
            page-break-after: always;
        }
        
        .paper:last-child {
            page-break-after: auto;
        }

        .no-print {
            display: none;
        }
        
        .pagination-controls {
            display: none;
        }
        
        .col-signature {
            text-align: center;
        }
        
        .page-info {
            display: none;
        }
    }
    
    .text-right {
        text-align: right;
    }
    
    .text-center {
        text-align: center;
    }
    
    .amount-cell {
        text-align: right;
        font-weight: 500;
    }
</style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Print Payroll</button>
        <button onclick="window.close()" class="btn-close">❌ Close</button>
        <button onclick="window.location.href='index.php?view=payroll_details&id=<?php echo $payroll_id; ?>'" class="btn-close">← Back</button>
    </div>

    <?php
    // Generate all pages for printing
    for($current_page = 1; $current_page <= $total_pages; $current_page++):
        $page_start = ($current_page - 1) * $rows_per_page;
        $page_scholars = array_slice($scholars, $page_start, $rows_per_page);
        $page_counter = $page_start + 1;
        $page_total = 0;
        foreach($page_scholars as $scholar) {
            $page_total += $scholar->amount;
        }
    ?>
    <div class="paper <?php echo ($current_page > 1) ? 'page-break' : ''; ?>">
        <div class="header">
            <div class="title1">ILOCOS SUR EDUCATIONAL ASSISTANCE AND SCHOLARSHIP PROGRAM (ISEASP)</div>
            <div class="title2">ILOCOS SUR COMMUNITY COLLEGE</div>
            <div class="title3">Allowance for the <?php echo $semester_display . ' ' . ($school_year ? $school_year->school_year : ''); ?></div>
            <?php if($total_pages > 1): ?>
            <div class="page-info">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></div>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="col-no">#</th>
                    <th class="col-name">NAME</th>
                    <th class="col-course">COURSE</th>
                    <th class="col-id">ID NUMBER</th>
                    <th class="col-amount">AMOUNT</th>
                    <th class="col-signature">SIGNATURE</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $row_counter = 1;
                foreach($page_scholars as $scholar):
                    $student_id = !empty($scholar->LRN) ? $scholar->LRN : $scholar->ID_NUMBER;
                ?>
                <tr>
                    <td class="text-center"><?php echo $page_counter++; ?>.<?php echo "\n"; ?>
                    <td><?php echo strtoupper($scholar->FULLNAME); ?><?php echo "\n"; ?>
                    <td><?php echo $scholar->COURSE; ?><?php echo "\n"; ?>
                    <td class="text-center"><?php echo $student_id ?: 'N/A'; ?><?php echo "\n"; ?>
                    <td class="amount-cell">₱ <?php echo number_format($scholar->amount, 2); ?><?php echo "\n"; ?>
                    <td class="text-center"><?php echo "\n"; ?>
                </tr>
                <?php endforeach; ?>
                
                <?php 
                // Fill remaining rows up to 20 for consistent layout on this page
                $remaining_on_page = $rows_per_page - count($page_scholars);
                for($i = 1; $i <= $remaining_on_page; $i++):
                ?>
                <tr>
                    <td class="text-center"><?php echo $page_counter++; ?>.<?php echo "\n"; ?>
                    <td>&nbsp;<?php echo "\n"; ?>
                    <td>&nbsp;<?php echo "\n"; ?>
                    <td>&nbsp;<?php echo "\n"; ?>
                    <td>&nbsp;<?php echo "\n"; ?>
                    <td>&nbsp;<?php echo "\n"; ?>
                </tr>
                <?php endfor; ?>
                
                <tr class="subtotal-row">
                    <td colspan="4" class="subtotal-label">Sub Total / Grand Total (Page <?php echo $current_page; ?>)<?php echo "\n"; ?>
                    <td class="subtotal-amount">₱ <?php echo number_format($page_total, 2); ?><?php echo "\n"; ?>
                    <td class="text-center"><?php echo "\n"; ?>
                </tr>
            </tbody>
        </table>

        <div class="notes-section">
            <div class="left-notes">
                <div class="middle-cert">
                    ✓ Certified: Supporting documents complete and proper<br>
                    ✓ All scholars have been verified and approved
                </div>
            </div>

            <div class="right-notes">
                <div class="right-confirmation">
                    Each student whose name appears above has been paid the amount indicated opposite his/her name.
                </div>
            </div>
        </div>

        <div class="signature-grid">
            <div class="sig-block">
                <div class="sig-label">Prepared by:</div>
                <div class="sig-name">SYDNEY D. SINOHIN, MPA</div>
                <div class="sig-title">Head</div>
                <div class="sig-title">Education and Scholarship Affairs</div>
            </div>

            <div class="sig-block">
                <div class="sig-name">LEILANI C. ARCE</div>
                <div class="sig-title">Provincial Accountant</div>

                <div class="sig-label" style="margin-top: 30px;">Funds available:</div>
                <div class="sig-name">RONNETTE A. VICTA</div>
                <div class="sig-title">Provincial Treasurer</div>
            </div>

            <div class="sig-block">
                <div class="sig-label">Approved for payment:</div>
                <div class="sig-name">JEREMIAS C. SINGSON</div>
                <div class="sig-title">Governor</div>
            </div>

            <div class="sig-block">
                <div class="sig-name">SYDNEY D. SINOHIN, MPA</div>
                <div class="sig-title">HEAD, PESAO</div>
            </div>
        </div>
    </div>
    <?php endfor; ?>
    
</body>
</html>