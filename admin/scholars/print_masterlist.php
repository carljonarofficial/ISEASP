<?php
// No template - standalone print page for master list
if (!isset($_SESSION['ADMIN_USERID'])) {
    session_start();
    if (!isset($_SESSION['ADMIN_USERID'])) {
        header("Location: " . web_root . "admin/index.php");
        exit;
    }
}

global $mydb;

// Get current school year
$mydb->setQuery("SELECT * FROM tbl_school_years WHERE is_active = 1 LIMIT 1");
$mydb->executeQuery();
$active_sy = $mydb->loadSingleResult();

$school_year = $active_sy ? $active_sy->school_year : date('Y') . '-' . (date('Y') + 1);
$semester = isset($_GET['semester']) ? $_GET['semester'] : '1st Semester';

// Get all active scholars
$mydb->setQuery("
    SELECT 
        sa.*,
        a.LASTNAME, a.FIRSTNAME, a.MIDDLENAME, a.SUFFIX,
        a.MUNICIPALITY, a.BARANGAY,
        a.COURSE, a.YEARLEVEL,
        a.ID_NUMBER,
        a.GPA,
        a.STATUS as APPLICANT_STATUS
    FROM tbl_scholarship_awards sa
    INNER JOIN tbl_applicants a ON sa.APPLICANTID = a.APPLICANTID
    WHERE sa.STATUS = 'Active'
    ORDER BY a.LASTNAME ASC
");
$mydb->executeQuery();
$scholars = $mydb->loadResultList();

// Calculate pagination
$rows_per_page = 50;
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
    <title>ISEASP Master List - <?php echo $school_year; ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f3f3f3;
            padding: 30px;
        }

        .page {
            width: 1200px;
            margin: auto;
            background: #fff;
            padding: 30px 35px 50px;
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
            margin-bottom: 18px;
            line-height: 1.4;
        }

        .header .line1,
        .header .line2 {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header .line3 {
            font-size: 16px;
            font-style: italic;
            font-weight: bold;
            margin-top: 4px;
        }

        .header .school-year {
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
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px 3px;
            font-size: 10px;
            vertical-align: top;
        }

        th {
            text-align: center;
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .col-no { 
            width: 30px; 
            text-align: center; 
        }
        .col-name { 
            width: 80px; 
        }
        .col-address { 
            width: 150px; 
        }
        .col-course { 
            width: 200px; 
        }
        .col-id { 
            width: 60px; 
            text-align: center;
        }
        .col-year { 
            width: 70px; 
            text-align: center;
        }
        .col-grades { 
            width: 70px; 
            text-align: center;
        }
        .col-form { 
            width: 45px;
        }

        .certification {
            margin-top: 25px;
            font-size: 13px;
            font-weight: bold;
            text-align: center;
        }

        .signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }

        .signature-block {
            width: 45%;
            text-align: left;
        }

        .signature-label {
            font-size: 12px;
            margin-bottom: 50px;
            font-style: italic;
        }

        .signature-name {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .signature-title {
            font-size: 11px;
            margin-top: 4px;
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
            border: none;
            border-radius: 4px;
        }
        
        .btn-print {
            background-color: #27ae60;
            color: white;
        }
        .btn-print:hover {
            background-color: #229954;
        }
        .btn-close {
            background-color: #e74c3c;
            color: white;
        }
        .btn-close:hover {
            background-color: #c0392b;
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

            .page {
                border: none;
                width: 100%;
                padding: 15px;
                margin: 0;
                page-break-after: always;
            }
            
            .page:last-child {
                page-break-after: auto;
            }

            .no-print {
                display: none;
            }
            
            .pagination-controls {
                display: none;
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
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Print Master List</button>
        <button onclick="window.close()" class="btn-close">❌ Close</button>
        <button onclick="window.location.href='../applications/index.php?stage=scholar'" class="btn-close">← Back</button>
    </div>
    
    <?php if($total_pages > 1): ?>
    <div class="no-print pagination-controls">
        <strong>Page <?php echo $page; ?> of <?php echo $total_pages; ?></strong><br><br>
        <?php if($page > 1): ?>
            <a href="?view=print_masterlist&page=<?php echo $page - 1; ?>&semester=<?php echo $semester; ?>">◀ Previous Page</a>
        <?php endif; ?>
        
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <?php if($i == $page): ?>
                <span><?php echo $i; ?></span>
            <?php else: ?>
                <a href="?view=print_masterlist&page=<?php echo $i; ?>&semester=<?php echo $semester; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if($page < $total_pages): ?>
            <a href="?view=print_masterlist&page=<?php echo $page + 1; ?>&semester=<?php echo $semester; ?>">Next Page ▶</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php
    // Generate pages
    for($current_page = 1; $current_page <= $total_pages; $current_page++):
        $page_start = ($current_page - 1) * $rows_per_page;
        $page_scholars = array_slice($scholars, $page_start, $rows_per_page);
        $page_counter = $page_start + 1;
    ?>
    <div class="page <?php echo ($current_page > 1) ? 'page-break' : ''; ?>">
        <div class="header">
            <div class="line1">ILOCOS SUR EDUCATIONAL ASSISTANCE AND SCHOLARSHIP PROGRAM (ISEASP)</div>
            <div class="line2">ILOCOS SUR COMMUNITY COLLEGE</div>
            <div class="line3">Master List for <?php echo $semester . ' S.Y. ' . $school_year; ?></div>
            <?php if($total_pages > 1): ?>
            <div class="page-info">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></div>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="col-no">#</th>
                    <th class="col-name">NAME</th>
                    <th class="col-address">ADDRESS</th>
                    <th class="col-course">COURSE</th>
                    <th class="col-id">ID NUMBER</th>
                    <th class="col-year">YEAR LEVEL</th>
                    <th class="col-grades">GRADES</th>
                    <th class="col-form">ENROLLMENT FORM</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach($page_scholars as $scholar):
                    $fullname = $scholar->LASTNAME . ', ' . $scholar->FIRSTNAME;
                    if(!empty($scholar->MIDDLENAME)) {
                        $fullname .= ' ' . substr($scholar->MIDDLENAME, 0, 1) . '.';
                    }
                    if(!empty($scholar->SUFFIX)) {
                        $fullname .= ' ' . $scholar->SUFFIX;
                    }
                    
                    $address = $scholar->BARANGAY . ', ' . $scholar->MUNICIPALITY;
                    $student_id = !empty($scholar->ID_NUMBER) ? $scholar->ID_NUMBER : $scholar->LRN;
                    $gpa = $scholar->GPA ? $scholar->GPA . '%' : 'N/A';
                ?>
                <tr>
                    <td class="text-center"><?php echo $page_counter++; ?>.<?php echo "\n"; ?>
                    <td><?php echo strtoupper($fullname); ?><?php echo "\n"; ?>
                    <td><?php echo $address; ?><?php echo "\n"; ?>
                    <td><?php echo $scholar->COURSE; ?><?php echo "\n"; ?>
                    <td class="text-center"><?php echo $student_id ?: 'N/A'; ?><?php echo "\n"; ?>
                    <td class="text-center"><?php echo $scholar->YEARLEVEL; ?><?php echo "\n"; ?>
                    <td class="text-center"><?php echo $gpa; ?><?php echo "\n"; ?>
                    <td class="text-center"></td>
                </tr>
                <?php endforeach; ?>
                
                <?php 
                // Fill remaining rows up to 40 for consistent layout on this page
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
                    <td>&nbsp;<?php echo "\n"; ?>
                    <td>&nbsp;<?php echo "\n"; ?>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <div class="certification">
            This is to certify that the above-mentioned names are ISEASP scholars.
        </div>

        <div class="signatures">
            <div class="signature-block">
                <div class="signature-label">Prepared by:</div>
                <div class="signature-name">DOMINIC T. TANO JR.</div>
                <div class="signature-title">Administrative Aide-II</div>
            </div>

            <div class="signature-block">
                <div class="signature-label">Certified by:</div>
                <div class="signature-name">SYDNEY D. SINOHIN, MPA</div>
                <div class="signature-title">Head, Education and Scholarship Affairs</div>
            </div>
        </div>
    </div>
    <?php endfor; ?>
    
</body>
</html>