<?php 
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

$stage = isset($_GET['stage']) ? $_GET['stage'] : 'all';
$district = isset($_GET['district']) ? $_GET['district'] : '';
$municipality = isset($_GET['municipality']) ? $_GET['municipality'] : '';
$filter_school = isset($_GET['school']) ? $_GET['school'] : '';
$filter_school_year = isset($_GET['school_year']) ? $_GET['school_year'] : '';

global $mydb;
$where = array();

// Fetch municipalities grouped by district for the dynamic filter dropdown
$mydb->setQuery("SELECT MUNICIPALITY_ID, MUNICIPALITY_NAME, DISTRICT FROM tbl_municipalities ORDER BY MUNICIPALITY_NAME");
$mun_results = $mydb->loadResultList();
$districts_with_mun = array();
foreach($mun_results as $mun) {
    $districts_with_mun[$mun->DISTRICT][] = $mun->MUNICIPALITY_NAME;
}

// Retain Filter URL Parameters for Stage Tab Links
$filter_url = array();
if (!empty($filter_school)) {
    $filter_url[] = "&school=" . urlencode($filter_school);
}
if (!empty($filter_school_year)) {
    $filter_url[] = "&school_year=" . urlencode($filter_school_year);
}
if (!empty($district)) {
    $filter_url[] = "&district=" . urlencode($district);
}
if (!empty($municipality)) {
    $filter_url[] = "&municipality=" . urlencode($municipality);
}

if(!empty($filter_school)) {
    $where[] = "a.SCHOOL = '$filter_school'";
}

if (!empty($filter_school_year)) {
    $where[] = "a.SCHOOL_YEAR = '$filter_school_year'";
}

switch($stage) {
    case 'new':
        $where[] = "(a.EXAM_SLIP_GENERATED IS NULL OR a.EXAM_SLIP_GENERATED = '') AND a.STATUS = 'Pending'";
        $where[] = "a.APPLICATION_TYPE = 'New Applicant'";
        break;
    case 'requirements':
        $where[] = "(SELECT COUNT(*) FROM tbl_applicant_requirement_checklist WHERE APPLICANTID = a.APPLICANTID AND IS_VERIFIED = 1) < (SELECT COUNT(*) FROM tbl_requirement)";
        $where[] = "a.STATUS NOT IN ('Qualified', 'Scholar')";
        break;
    case 'exam_slip':
        $where[] = "a.EXAM_SLIP_GENERATED IS NOT NULL AND a.EXAM_SLIP_GENERATED != '' AND a.EXAM_STATUS = 'Pending'";
        $where[] = "a.STATUS NOT IN ('Qualified', 'Scholar')";
        break;
    case 'exam':
        $where[] = "a.EXAM_STATUS IN ('Passed', 'Failed')";
        $where[] = "a.STATUS NOT IN ('For Evaluation','Qualified', 'Scholar')";
        break;
    case 'evaluation':
        $where[] = "a.EXAM_STATUS = 'Passed' AND a.STATUS = 'For Evaluation'";
        break;
    case 'interview':
        $where[] = "a.STATUS = 'For Interview'";
        break;
    case 'qualified':
        $where[] = "a.STATUS = 'Qualified'";
        break;
    case 'scholar':
        $where[] = "a.STATUS = 'Scholar'";
        break;
}

if(!empty($district)) {
    $where[] = "a.DISTRICT = '$district'";
}
if(!empty($municipality)) {
    $where[] = "a.MUNICIPALITY = '$municipality'";
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$mydb->setQuery("
    SELECT a.*, sa.AWARD_ID, er.EXAM_RESULT_ID, er.TOTAL_SCORE, u.FULLNAME AS CREATED_BY,
        (SELECT COUNT(*) FROM tbl_applicant_requirement_checklist WHERE APPLICANTID = a.APPLICANTID AND IS_VERIFIED = 1) AS VERIFIED_REQ,
        (SELECT COUNT(*) FROM tbl_requirement) AS TOTAL_REQ,
        (SELECT COUNT(*) FROM tbl_requirement WHERE REQUIRED = 'Yes') AS TOTAL_REQUIRED_REQ,
        (SELECT COUNT(*) FROM tbl_requirement WHERE REQUIRED = 'No') AS TOTAL_OPTIONAL_REQ
    FROM tbl_applicants a
    LEFT JOIN tblusers u ON a.CREATED_BY = u.USERID
    LEFT JOIN tbl_exam_results er ON a.APPLICANTID = er.APPLICANTID
    LEFT JOIN tbl_scholarship_awards sa ON a.APPLICANTID = sa.APPLICANTID
    $where_clause
    ORDER BY a.LASTNAME ASC
");

$applicants = $mydb->loadResultList();
$has_applicants = !empty($applicants);
?>

<style>
.workflow-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    padding: 0;
    list-style: none;
}
.workflow-step {
    flex: 1;
    text-align: center;
    padding: 15px 5px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    position: relative;
}
.workflow-step.active {
    background: #27ae60;
    color: white;
    border-color: #1e8449;
}
.workflow-step.completed {
    background: #d4edda;
    border-color: #c3e6cb;
}
.workflow-step:not(:last-child):after {
    content: '→';
    position: absolute;
    right: -10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 20px;
    color: #6c757d;
    z-index: 1;
}
.stage-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
}
.stage-new { background: #007bff; color: white; }
.stage-requirements { background: #fd7e14; color: white; }
.stage-exam-slip { background: #ffc107; color: black; }
.stage-exam { background: #17a2b8; color: white; }
.stage-evaluation { background: #fd7e14; color: white; }
.stage-interview { background: #6f42c1; color: white; }
.stage-qualified { background: #28a745; color: white; }
.stage-scholar { background: #20c997; color: white; }

.action-buttons {
    display: flex;
    gap: 4px;
    justify-content: flex-end;
    align-items: center;
}

.action-buttons .btn-xs {
    width: 32px;
    height: 28px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s ease;
    border: none;
}

.action-buttons .dropdown .btn-xs {
    padding: 0 8px;
}

.action-buttons .btn-xs:hover {
    /* transform: translateY(-2px); */
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    filter: brightness(1.1);
}

/* Fix for dropdown menus in responsive tables */
.table-responsive { overflow: visible !important; }
.dropdown-menu { z-index: 9999 !important; }
</style>

<!-- <div class="row no-print">
    <div class="col-lg-12" style="margin-bottom: 15px;">
        <a href="index.php?view=add" class="btn btn-primary" style="background-color: #27ae60; border-color: #229954;">
            <i class="fa fa-plus"></i> New Applicant
        </a>
    </div>
</div> -->

<!-- Filter Applicants -->
<div class="row no-print">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: #3E64D6; color: white; border-color: #3E64D6;">
                <i class="fa fa-filter"></i> Filter Applicants
            </div>
            <div class="panel-body">
                <form method="GET" action="index.php" class="form-inline">
                    <input type="hidden" name="view" value="list">
                    <!-- Retain Current Stage Tab -->
                    <input type="hidden" name="stage" value="<?php echo $stage; ?>">

                    <!-- Filter School -->
                    <div class="form-group" style="margin-right: 10px;">
                        <label>School:</label>
                        <select name="school" class="form-control input-sm">
                            <option value="">All Schools</option>
                            <?php
                                $schools = $mydb->setQuery("SELECT DISTINCT SCHOOL FROM tbl_applicants WHERE SCHOOL IS NOT NULL AND SCHOOL != '' ORDER BY SCHOOL");
                                $schools = $mydb->loadResultList();
                                foreach($schools as $school):
                            ?>
                            <option value="<?php echo $school->SCHOOL; ?>" <?php echo ($filter_school == $school->SCHOOL) ? 'selected' : ''; ?>>
                                <?php echo abbreviateSchool($school->SCHOOL); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-right: 10px;">
                        <label>School Year:</label>
                        <select name="school_year" class="form-control input-sm">
                            <option value="">All School Years</option>
                            <!-- Populate School Years using DISTINCT -->
                            <?php
                                $school_years = $mydb->setQuery("SELECT DISTINCT SCHOOL_YEAR FROM tbl_applicants ORDER BY SCHOOL_YEAR DESC");
                                $school_years = $mydb->loadResultList();
                                foreach($school_years as $school_year): 
                            ?>
                            <option value="<?php echo $school_year->SCHOOL_YEAR; ?>" <?php echo ($filter_school_year == $school_year->SCHOOL_YEAR) ? 'selected' : ''; ?>>
                                <?php echo $school_year->SCHOOL_YEAR; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>District:</label>
                        <select name="district" class="form-control input-sm">
                            <option value="">All Districts</option>
                            <option value="1st District" <?php echo ($district == '1st District') ? 'selected' : ''; ?>>1st District</option>
                            <option value="2nd District" <?php echo ($district == '2nd District') ? 'selected' : ''; ?>>2nd District</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>Municipality:</label>
                        <select name="municipality" class="form-control input-sm">
                            <option value="">All Municipalities</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-sm" style="background-color: #27ae60; border-color: #229954;">
                        <i class="fa fa-search"></i> Apply Filter
                    </button>
                    <a href="index.php?view=list" class="btn btn-default btn-sm">
                        <i class="fa fa-refresh"></i> Reset
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if($has_applicants): ?>
<!-- Batch Processing Panel -->
<div class="row no-print">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: #3E64D6; color: white; border-color: #3E64D6;">
                <i class="fa fa-cogs"></i> Batch Processing
            </div>
            <div class="panel-body">
                <div class="row no-print">
                    <div class="col-md-6">
                        <?php if($stage === 'all' || $stage === 'new' || $stage === 'exam_slip'): ?>
                            <button type="button" class="btn btn-warning btn-sm" id="batchGenerateBtn" disabled>
                                <i class="fa fa-ticket"></i> Generate Exam Slips
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="batchPrintBtn" disabled>
                                <i class="fa fa-print"></i> Print Slips
                            </button>
                        <?php endif; ?>
                        <?php if($stage === 'exam_slip' || $stage === 'exam'):?>
                        <button type="button" class="btn btn-<?= ($stage === 'exam_slip') ? 'success' : 'warning'?> btn-sm" id="batchAddResultBtn" disabled>
                            <i class="fa fa-<?= ($stage === 'exam_slip') ? 'plus' : 'edit'?>"></i> Batch <?= ($stage === 'exam_slip') ? "Add" : "Edit" ?> Results
                        </button>
                        <?php endif; ?>
                        <?php if($stage === 'exam' || $stage === 'interview'): ?>
                        <button type="button" class="btn btn-info btn-sm" id="batchInterviewBtn" disabled>
                            <i class="fa fa-calendar"></i> Batch Schedule Interview
                        </button>
                        <?php endif; ?>
                        <?php if($stage === 'interview'): ?>
                        <button type="button" class="btn btn-success btn-sm" id="batchInterviewResultBtn" disabled>
                            <i class="fa fa-check-circle"></i> Batch Interview Results
                        </button>
                        <?php endif; ?>
                        <?php if($stage === 'evaluation'): ?>
                        <button type="button" class="btn btn-warning btn-sm" id="batchEvaluationBtn" disabled>
                            <i class="fa fa-gavel"></i> Batch Final Evaluation
                        </button>
                        <?php endif; ?>
                        <?php if($stage === 'qualified'): ?>
                        <button type="button" class="btn btn-success btn-sm" id="batchConvertBtn" disabled>
                            <i class="fa fa-graduation-cap"></i> Batch Convert to Scholar
                        </button>
                        <?php endif; ?>
                        <?php if($stage === 'scholar'): ?>
                        <!-- <button type="button" class="btn btn-primary btn-sm" id="batchRenewBtn" disabled>
                            <i class="fa fa-refresh"></i> Batch Renew Scholars
                        </button> -->
                        <?php endif; ?>
                        <!-- <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete selected applicants?')">
                            <i class="fa fa-trash"></i> Delete Selected
                        </button> -->
                    </div>
                    <div class="col-md-6 text-right">
                        <strong><span id="selection-info" style="display: none;"><span id="selected-count">0</span> applicants selected |</span> Total Applicants: <?= count($applicants) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($stage === 'scholar'): ?>
<!-- Scholar Action Buttons -->
<div class="row">
    <div class="col-lg-12" style="margin-bottom: 10px;">
        <a href="../scholars/index.php?view=print_masterlist" class="btn btn-success" target="_blank">
            <i class="fa fa-print"></i> Print Master List
        </a>
        <a href="../scholars/index.php?view=payroll" class="btn btn-info">
            <i class="fa fa-money"></i> Payroll Management
        </a>
        <a href="../scholars/index.php?view=graduates" class="btn btn-primary">
            <i class="fa fa-graduation-cap"></i> View Graduates
        </a>
        <a href="../scholars/index.php?view=history" class="btn btn-warning">
            <i class="fa fa-history"></i> Scholarship History
        </a>
    </div>
</div>

<?php endif; ?>

<!-- STATUS TABS -->
<div class="row no-print">
    <div class="col-lg-12">
        <ul class="nav nav-tabs applicant-status-tabs" role="tablist">
            <li role="presentation" class="<?php echo ($stage == 'all') ? 'active' : ''; ?>">
                <a href="index.php?stage=all" class="tab-link">
                    <i class="fa fa-users"></i> All Applicants
                </a>
            </li>
            <li role="presentation" class="<?php echo ($stage == '' || $stage == 'new') ? 'active' : ''; ?>">
                <a href="index.php?stage=new<?php echo implode('&', $filter_url); ?>" class="tab-link">
                    <i class="fa fa-user-plus"></i> New Applicants
                </a>
            </li>
            <!-- <li role="presentation" class="<?php echo ($stage == 'requirements') ? 'active' : ''; ?>">
                <a href="index.php?stage=requirements" class="tab-link">
                    <i class="fa fa-file-text"></i> Missing Requirements
                </a>
            </li> -->
            <li role="presentation" class="<?php echo ($stage == 'exam_slip') ? 'active' : ''; ?>">
                <a href="index.php?stage=exam_slip<?php echo implode('&', $filter_url); ?>" class="tab-link">
                    <i class="fa fa-ticket"></i> Exam Slip
                </a>
            </li>
            <li role="presentation" class="<?php echo ($stage == 'exam') ? 'active' : ''; ?>">
                <a href="index.php?stage=exam<?php echo implode('&', $filter_url); ?>" class="tab-link">
                    <i class="fa fa-pencil-square-o"></i> Exam Results
                </a>
            </li>
            <li role="presentation" class="<?php echo ($stage == 'interview') ? 'active' : ''; ?>">
                <a href="index.php?stage=interview<?php echo implode('&', $filter_url); ?>" class="tab-link">
                    <i class="fa fa-microphone"></i> For Interview
                </a>
            </li>
            <li role="presentation" class="<?php echo ($stage == 'evaluation') ? 'active' : ''; ?>">
                <a href="index.php?stage=evaluation<?php echo implode('&', $filter_url); ?>" class="tab-link">
                    <i class="fa fa-check-square"></i> For Evaluation
                </a>
            </li>
            <li role="presentation" class="<?php echo ($stage == 'qualified') ? 'active' : ''; ?>">
                <a href="index.php?stage=qualified<?php echo implode('&', $filter_url); ?>" class="tab-link">
                    <i class="fa fa-check-circle"></i> Qualified
                </a>
            </li>
            <li role="presentation" class="<?php echo ($stage == 'scholar') ? 'active' : ''; ?>">
                <a href="index.php?stage=scholar<?php echo implode('&', $filter_url); ?>" class="tab-link">
                    <i class="fa fa-graduation-cap"></i> Scholars
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
.applicant-status-tabs {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
    margin: 0;
}

.applicant-status-tabs li {
    margin: 0;
    padding: 0;
}

.applicant-status-tabs .tab-link {
    display: inline-block;
    padding: 10px 15px;
    background-color: #f8f9fa;
    color: #495057;
    text-decoration: none;
    border: 1px solid #dee2e6;
    border-right: none;
    transition: all 0.3s ease;
    font-weight: 500;
    font-size: 13px;
}

.applicant-status-tabs li:last-child .tab-link {
    border-right: 1px solid #dee2e6;
}

.applicant-status-tabs .tab-link:hover {
    background-color: #e9ecef;
    color: #212529;
}

.applicant-status-tabs li.active .tab-link {
    background-color: #3E64D6;
    color: white;
    border-color: #1e8449;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.applicant-status-tabs i {
    margin-right: 5px;
}
</style>

<form action="controller.php?action=delete" method="POST">  
    <div id="print-section">
        <div class="table-responsive">					
            <table id="dash-table" class="table table-striped table-bordered table-hover" style="font-size:13px; width:100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th width="3%" class="no-print" style="text-align: center;"><input type="checkbox" id="chkAll"></th>
                        <th data-priority="1">Applicant Name</th>
                        <th data-priority="2">Municipality</th>
                        <th data-priority="3">School</th>
                        <!-- <th data-priority="4">Applicant Type</th> -->
                        <th data-priority="5">Pipeline Stage</th>
                        <th data-priority="6">GWA</th>
                        <th width="18%" class="no-print" data-priority="7">Actions</th>
                    </tr>	
                </thead>
                <?php
                function getApplicantTypeColor($type) {
                    switch($type) {
                        case 'New ': return 'primary';
                        case 'Applicant': return 'info';
                        case 'Renewal': return 'warning';
                        default: return 'default';
                    }
                }

                function getRequirementStatusColor($requirement_status) {
                    switch($requirement_status) {
                        case 'Complete': return 'success';
                        case 'Incomplete': return 'danger';
                        default: return 'default';
                    }
                    
                }

                function getStageBadge($applicant) {
                    if($applicant->STATUS == 'Scholar') return 'stage-scholar';
                    if($applicant->STATUS == 'Qualified') return 'stage-qualified';
                    if($applicant->STATUS == 'For Interview') return 'stage-interview';
                    if($applicant->EXAM_STATUS == 'Passed' && $applicant->STATUS == 'For Evaluation') return 'stage-evaluation';
                    if(!empty($applicant->EXAM_SLIP_GENERATED) && $applicant->EXAM_STATUS == 'Pending') return 'stage-exam-slip';
                    $verified = isset($applicant->VERIFIED_REQ) ? $applicant->VERIFIED_REQ : 0;
                    $total = isset($applicant->TOTAL_REQ) ? $applicant->TOTAL_REQ : 13;
                    if($verified < $total) return 'stage-requirements';
                    if($applicant->EXAM_STATUS != 'Pending' && $applicant->EXAM_STATUS != '') return 'stage-exam';
                    return 'stage-new';
                }
                
                function getStatusColor($status) {
                    switch($status) {
                        case 'Scholar': return 'success';
                        case 'For Evaluation': return 'default';
                        case 'Qualified': return 'info';
                        case 'For Exam': return 'warning';
                        case 'For Interview': return 'default';
                        case 'Pending': return 'default';
                        case 'Graduated': return 'primary';
                        case 'Terminated': return 'danger';
                        default: return 'default';
                    }
                }

                function getExamStatusLabel($passing_score, $total_score) {
                    if($total_score === null) {
                        $result_label = '<span class="label label-default">EXAM PENDING</span>';
                    } else {
                        $result_label = ($total_score >= $passing_score) ? 
                            '<span class="label label-success">EXAM PASSED | ' . $total_score . '</span>' : 
                            '<span class="label label-danger">EXAM FAILED | ' . $total_score . '</span>';
                    }

                    return $result_label;
                }
                
                function getStageText($applicant) {
                    if($applicant->STATUS == 'Scholar') return 'Scholar';
                    if($applicant->STATUS == 'Qualified') return 'Qualified';
                    if($applicant->STATUS == 'For Interview') return 'For Interview';
                    if($applicant->EXAM_STATUS == 'Passed' && $applicant->STATUS == 'Pending') return 'For Evaluation';
                    if(!empty($applicant->EXAM_SLIP_GENERATED) && $applicant->EXAM_STATUS == 'Pending') return 'Exam Slip';
                    $verified = isset($applicant->VERIFIED_REQ) ? $applicant->VERIFIED_REQ : 0;
                    $total = isset($applicant->TOTAL_REQ) ? $applicant->TOTAL_REQ : 13;
                    if($verified < $total) return 'Missing Requirements';
                    if($applicant->EXAM_STATUS == 'Passed') return 'Passed Exam';
                    if($applicant->EXAM_STATUS == 'Failed') return 'Failed Exam';
                    if(!empty($applicant->EXAM_SLIP_GENERATED)) return 'Exam Slip Generated';
                    return 'New Applicant';
                }

                function abbreviateSchool($school) {
                    $abbreviations = [
                        'UNIVERSITY OF NORTHERN PHILIPPINES' => 'UNP',
                        'ILOCOS SUR COMMUNITY COLLEGE' => 'ISCC',
                        'ILOCOS SUR POLYTECHNIC STATE COLLEGE/UNIVERSITY OF ILOCOS PHILIPPINES' => 'ISPSC/UIP',
                        'ST. PAUL COLLEGE OF ILOCOS SUR' => 'SPCIS',
                        'DIVINE WORLD COLLEGE OF VIGAN' => 'DWCV',
                        'IMMACULATE CONCEPTION SCHOOL OF THEOLOGY' => 'ICST'
                        // Add more as needed
                    ];
                    foreach ($abbreviations as $full => $abbr) {
                        $school = str_replace($full, $abbr, $school);
                    }
                    return $school;
                }
                ?>

                <tbody>
                    <?php 
                    $has_applicants = false;
                    foreach ($applicants as $a): 
                        $has_applicants = true;
                        
                        $stageClass = getStageBadge($a);
                        $stageText = getStageText($a);
                        
                        $examStatus = isset($a->EXAM_STATUS) ? $a->EXAM_STATUS : 'Pending';
                        switch($examStatus) {
                            case 'Passed':
                                $examColor = 'label-success';
                                break;
                            case 'Failed':
                                $examColor = 'label-danger';
                                break;
                            default:
                                $examColor = 'label-default';
                        }
                        
                        $verified_req = isset($a->VERIFIED_REQ) ? $a->VERIFIED_REQ : 0;
                        $total_req = isset($a->TOTAL_REQ) ? $a->TOTAL_REQ : 13;
                        $total_required_req = isset($a->TOTAL_REQUIRED_REQ) ? $a->TOTAL_REQUIRED_REQ : 0;
                        $total_optional_req = isset($a->TOTAL_OPTIONAL_REQ) ? $a->TOTAL_OPTIONAL_REQ : 0;
                        $req_percentage = ($total_req > 0) ? round(($verified_req / $total_req) * 100) : 0;
                        
                        $req_status_color = ($verified_req == $total_req) ? 'success' : 'warning';
                        $can_generate = (empty($a->EXAM_SLIP_GENERATED) && $a->STATUS == 'For Exam' && $verified_req == $total_req);
                        $can_print = (!empty($a->EXAM_SLIP_GENERATED) && $a->EXAM_STATUS == 'Pending');
                        $can_batch_result = !empty($a->EXAM_RESULT_ID);
                        $can_interview = ($a->EXAM_STATUS == 'Passed' && ($a->STATUS == 'For Interview' || $a->STATUS == 'Pending'));
                        $can_interview_result = ($a->STATUS == 'For Interview');
                        $can_evaluation = ($a->EXAM_STATUS == 'Passed' && $a->STATUS == 'For Evaluation');
                        $can_convert = ($a->STATUS == 'Qualified');
                        $can_renew = ($a->STATUS == 'Scholar');
                        
                        $checkbox_classes = array();
                        if ($can_generate) $checkbox_classes[] = 'can-generate-slip';
                        if ($can_print) $checkbox_classes[] = 'can-print-slip';
                        if ($can_batch_result) $checkbox_classes[] = 'can-batch-result';
                        if ($can_interview) $checkbox_classes[] = 'can-batch-interview';
                        if ($can_interview_result) $checkbox_classes[] = 'can-batch-interview-result';
                        if ($can_evaluation) $checkbox_classes[] = 'can-batch-evaluation';
                        if ($can_convert) $checkbox_classes[] = 'can-batch-convert';
                        if ($can_renew) $checkbox_classes[] = 'can-batch-renew';
                        $checkbox_class = implode(' ', $checkbox_classes);
                    ?>
                    <tr onclick="viewApplicant(<?= $a->APPLICANTID ?>)" style="cursor: pointer;">
                        <td class="no-print" onclick="event.cancelBubble=true; event.stopPropagation();"><input type="checkbox" name="selector[]" value="<?= $a->APPLICANTID ?>" class="<?= $checkbox_class ?>" data-resultid="<?= $a->EXAM_RESULT_ID ?? '' ?>"></td>
                        <td>
                            <strong><?= htmlspecialchars($a->LASTNAME . ', ' . $a->FIRSTNAME . ' ' . ($a->MIDDLENAME ?? '')) ?></strong>
                        </td>
                        <td><?= htmlspecialchars(abbreviateSchool($a->MUNICIPALITY ?? 'N/A')) ?></td>
                        <td><?= htmlspecialchars(abbreviateSchool($a->SCHOOL ?? 'N/A')) ?></td>
                        <!-- <td><span class="label label-<?= getApplicantTypeColor($a->APPLICATION_TYPE) ?> status-badge"><?= $a->APPLICATION_TYPE ?? 'New' ?></span></td> -->
                        <td>
                            <?php if(($stage === 'all' || $stage === 'new') && $a->STATUS == 'Pending'): ?>
                                <span class="label label-<?= getRequirementStatusColor($a->REQUIREMENT_STATUS) ?> status-badge"><?= ($a->REQUIREMENT_STATUS === 'Pending' ? '' : $a->REQUIREMENT_STATUS.' ') ?>Requirements<?= ($a->REQUIREMENT_STATUS === 'Complete' && $a->STATUS === 'Pending') ? ' | For Verifiation' : ' for Review' ?></span>
                            <?php endif; ?>
                            <?php if($stage === 'exam'):
                                echo getExamStatusLabel(75, $a->TOTAL_SCORE); ?>
                            <?php endif; ?>
                            <?php if(($stage === 'all' || $stage === 'new') && $a->STATUS !== 'Pending'): ?>
                                <span class="label label-<?= getStatusColor($a->STATUS) ?> status-badge"><?= $a->STATUS ?? 'Pending' ?><?= ($stage === 'all' || $stage === 'new') ? ' Stage' : "" ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= $a->GPA ?></td>
                        <td class="text-center no-print" onclick="event.cancelBubble=true; event.stopPropagation();">
                            <div class="action-buttons">
                                <a href="./index.php?view=view&id=<?= $a->APPLICANTID ?>" 
                                   class="btn btn-info btn-xs" data-toggle="tooltip" title="View Details">
                                    <i class="fa fa-eye"></i>
                                </a>
                                
                                <?php if(empty($a->EXAM_SLIP_GENERATED) && $a->STATUS == 'For Exam' && $verified_req == $total_req): ?>
                                    <a href="./index.php?view=exam_slip&id=<?= $a->APPLICANTID ?>" 
                                       class="btn btn-warning btn-xs" data-toggle="tooltip" title="Generate Exam Slip">
                                        <i class="fa fa-ticket"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if(empty($a->EXAM_SLIP_GENERATED) && $a->STATUS == 'Pending' && $verified_req != $total_req): ?>
                                    <a href="../checklist/index.php?view=view&id=<?= $a->APPLICANTID ?>" 
                                       class="btn btn-info btn-xs" data-toggle="tooltip" title="View Requirements">
                                        <i class="fa fa-list"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if(!empty($a->EXAM_SLIP_GENERATED) && $a->EXAM_STATUS == 'Pending'): ?>
                                    <a href="./index.php?view=print_slip&id=<?= $a->APPLICANTID ?>" 
                                       class="btn btn-primary btn-xs" data-toggle="tooltip" title="Print Exam Slip" target="_blank">
                                        <i class="fa fa-print"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if($a->EXAM_STATUS == 'Passed' && $a->STATUS == 'Pending'): ?>
                                    <!-- <a href="../evaluation/index.php?view=add&id=<?= $a->APPLICANTID ?>" 
                                       class="btn btn-success btn-xs" data-toggle="tooltip" title="Evaluate">
                                        <i class="fa fa-check-square"></i>
                                    </a> -->
                                <?php endif; ?>
                                
                                <?php if($a->STATUS == 'Qualified'): ?>
                                    <a href="./index.php?view=convert&id=<?= $a->APPLICANTID ?>" 
                                       class="btn btn-success btn-xs" data-toggle="tooltip" title="Convert to Scholar">
                                        <i class="fa fa-graduation-cap"></i>
                                    </a>
                                <?php endif; ?>

                                <!-- Scholar Dropdown Actions -->
                                <?php if($a->STATUS == 'Scholar'): ?>
                                    <div class="dropdown" style="display: inline-block;">
                                        <button type="button" class="btn btn-success btn-xs dropdown-toggle" data-toggle="tooltip" title="Scholar Actions" aria-haspopup="true" aria-expanded="false" onclick="var p=jQuery(this).parent(); if(p.hasClass('open')){ p.removeClass('open'); } else { jQuery('.dropdown.open').removeClass('open'); p.addClass('open'); setTimeout(function(){ jQuery(document).one('click', function(){ p.removeClass('open'); }); },0); } event.stopPropagation();">
                                            <i class="fa fa-graduation-cap"></i> <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li class="dropdown-header">Scholar Management</li>
                                            <li><a href="../scholars/index.php?view=view&id=<?= $a->AWARD_ID ?>"><i class="fa fa-eye"></i> View Scholar Profile</a></li>
                                            <li><a href="../scholars/index.php?view=renew&id=<?= $a->AWARD_ID ?>"><i class="fa fa-refresh"></i> Renew</a></li>
                                            <!-- <li role="separator" class="divider"></li> -->
                                        </ul>
                                    </div>

                                <?php endif; ?>
                                
                                <a href="./index.php?view=edit&id=<?= $a->APPLICANTID ?>" 
                                   class="btn btn-primary btn-xs" data-toggle="tooltip" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </a>
                                
                                <?php if (isset($_SESSION['ADMIN_ROLE']) && $_SESSION['ADMIN_ROLE'] === 'Super Admin'): ?>
                                    <a href="controller.php?action=delete&id=<?= $a->APPLICANTID ?>" 
                                       class="btn btn-danger btn-xs" data-toggle="tooltip"
                                       onclick="return confirm('Delete this applicant? This will also delete all related records.')"
                                       title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(!$has_applicants): ?>
                    <tr>
                        <td colspan="10" class="text-center">
                            <div class="alert alert-info" style="margin: 20px;">
                                <i class="fa fa-info-circle"></i> No applicants found.
                                <a href="index.php?view=add" class="alert-link">Click here to add a new applicant</a>.
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            
        </div>
    </div>
</form>

<link rel="stylesheet" href="<?php echo web_root; ?>plugins/datatables/dataTables.bootstrap.css">
<script src="<?php echo web_root; ?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="<?php echo web_root; ?>plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo web_root; ?>plugins/datatables/dataTables.bootstrap.min.js"></script>
<script>
function viewApplicant(id) {
    window.location.href = './index.php?view=view&id=' + id;
}

function printTable() {
    window.print();
}

function updateSelectedCount() {
    var checkboxes = jQuery('input[name="selector[]"]:checked');
    var count = checkboxes.length;

    // Only enable generation if ALL selected applicants are eligible
    var canGenerateCount = jQuery('input[name="selector[]"].can-generate-slip:checked').length;
    var canPrintCount = jQuery('input[name="selector[]"].can-print-slip:checked').length;

    jQuery('#batchGenerateBtn').prop('disabled', count === 0 || canGenerateCount < count);
    jQuery('#batchPrintBtn').prop('disabled', count === 0 || canPrintCount < count);

    var canBatchResultCount = jQuery('input[name="selector[]"].can-batch-result:checked').length;
    jQuery('#batchAddResultBtn').prop('disabled', count === 0 || canBatchResultCount < count);

    var canBatchInterviewCount = jQuery('input[name="selector[]"].can-batch-interview:checked').length;
    jQuery('#batchInterviewBtn').prop('disabled', count === 0 || canBatchInterviewCount < count);

    var canBatchInterviewResultCount = jQuery('input[name="selector[]"].can-batch-interview-result:checked').length;
    jQuery('#batchInterviewResultBtn').prop('disabled', count === 0 || canBatchInterviewResultCount < count);

    var canBatchEvaluationCount = jQuery('input[name="selector[]"].can-batch-evaluation:checked').length;
    jQuery('#batchEvaluationBtn').prop('disabled', count === 0 || canBatchEvaluationCount < count);

    var canBatchConvertCount = jQuery('input[name="selector[]"].can-batch-convert:checked').length;
    jQuery('#batchConvertBtn').prop('disabled', count === 0 || canBatchConvertCount < count);

    var canBatchRenewCount = jQuery('input[name="selector[]"].can-batch-renew:checked').length;
    jQuery('#batchRenewBtn').prop('disabled', count === 0 || canBatchRenewCount < count);

    if (count > 0) {
        jQuery('#selected-count').text(count);
        jQuery('#selection-info').show();
    } else {
        jQuery('#selection-info').hide();
    }
}

<?php

?>

document.getElementById('chkAll').onclick = function() {
    var checkboxes = document.querySelectorAll('input[name="selector[]"]');
    for(var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = this.checked;
    }
    updateSelectedCount();
};

// JS array to store districts with their municipalities
var districtMunicipalities = <?php echo json_encode($districts_with_mun); ?>;

function updateMunicipalityFilter() {
    var selectedDistrict = jQuery('select[name="district"]').val();
    var municipalitySelect = jQuery('select[name="municipality"]');
    var currentMun = "<?php echo $municipality; ?>";

    municipalitySelect.html('<option value="">All Municipalities</option>');

    if (selectedDistrict && districtMunicipalities[selectedDistrict]) {
        districtMunicipalities[selectedDistrict].forEach(function(mun) {
            var isSelected = (mun === currentMun) ? 'selected' : '';
            municipalitySelect.append('<option value="' + mun + '" ' + isSelected + '>' + mun + '</option>');
        });
    } else {
        // If "All Districts" is selected, show all municipalities from all districts
        var allMuns = [];
        for (var dist in districtMunicipalities) {
            allMuns = allMuns.concat(districtMunicipalities[dist]);
        }
        // Sort and remove duplicates to ensure a clean list
        allMuns.sort().filter((v, i, a) => a.indexOf(v) === i).forEach(function(mun) {
            var isSelected = (mun === currentMun) ? 'selected' : '';
            municipalitySelect.append('<option value="' + mun + '" ' + isSelected + '>' + mun + '</option>');
        });
    }
}

// Event listener for district change in filter
jQuery('select[name="district"]').on('change', function() {
    updateMunicipalityFilter();
});

updateMunicipalityFilter();

jQuery(document).ready(function() {
    <?php if($has_applicants): ?>
    jQuery('#dash-table').DataTable({
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "pagingType": "full_numbers",
        "searching": true,
        "info": true,
        "responsive": true,
        "order": [[1, "asc"]],
        "pageLength": 25,
"columnDefs": [
            { 
                "targets": "_all",
                "defaultContent": "N/A"
            }
        ],
        "language": {
            "lengthMenu": "Show _MENU_ applicants per page",
            "zeroRecords": "No applicants found matching your search",
            "info": "Showing page _PAGE_ of _PAGES_ | Displaying _START_ to _END_ of _TOTAL_ applicants",
            "infoEmpty": "No applicants available",
            "infoFiltered": " (filtered from _MAX_ total applicants)",
            "search": "Search applicants:",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            },
            "emptyTable": "No applicants found. <a href='index.php?view=add' class='alert-link'>Click here to add a new applicant</a>."
        }
    });
    
    jQuery('#dash-table').on('change', 'input[name="selector[]"]', function() {
        updateSelectedCount();
    });
    
    jQuery('#dash-table tbody tr').hover({
        function() { 
            if (!jQuery(this).hasClass('selected')) {
                jQuery(this).css('background-color', '#e8f5e9 !important'); 
            }
        },
        function() { 
            if (!jQuery(this).hasClass('selected')) {
                jQuery(this).css('background-color', ''); 
            }
        }
    });

    // Add row click to highlight
    jQuery('#dash-table tbody').on('click', 'tr', function() {
        jQuery(this).toggleClass('selected').css('background-color', jQuery(this).hasClass('selected') ? '#c8e6c9 !important' : '');
    });

    // Batch Generate Handler
    jQuery('#batchGenerateBtn').on('click', function() {
        var selected = [];
        // Only collect IDs that are actually eligible for slip generation
        jQuery('input[name="selector[]"].can-generate-slip:checked').each(function() {
            selected.push(this.value);
        });
        if (selected.length > 0) {
            window.location.href = 'index.php?view=batch_exam_slip&ids=' + selected.join(',');
        }
    });

    // Batch Print Handler
    jQuery('#batchPrintBtn').on('click', function() {
        var selected = [];
        jQuery('input[name="selector[]"].can-print-slip:checked').each(function() {
            selected.push(this.value);
        });
        if (selected.length > 0) {
            window.open('index.php?view=batch_print&ids=' + selected.join(','), '_blank');
        }
    });

    // Batch Add Result Handler
    jQuery('#batchAddResultBtn').on('click', function() {
        var selected = [];
        jQuery('input[name="selector[]"].can-batch-result:checked').each(function() {
            var resId = jQuery(this).data('resultid');
            if (resId) selected.push(resId);
        });
        if (selected.length > 0) {
            window.location.href = '../exam/index.php?view=batch_add&ids=' + selected.join(',');
        }
    });

    // Batch Interview Handler
    jQuery('#batchInterviewBtn').on('click', function() {
        var selected = [];
        jQuery('input[name="selector[]"].can-batch-interview:checked').each(function() {
            selected.push(this.value);
        });
        if (selected.length > 0) {
            window.location.href = 'index.php?view=batch_interview&ids=' + selected.join(',');
        }
    });

    // Batch Interview Result Handler
    jQuery('#batchInterviewResultBtn').on('click', function() {
        var selected = [];
        jQuery('input[name="selector[]"].can-batch-interview-result:checked').each(function() {
            selected.push(this.value);
        });
        if (selected.length > 0) {
            window.location.href = 'index.php?view=batch_interview_result&ids=' + selected.join(',');
        }
    });

    // Batch Evaluation Handler
    jQuery('#batchEvaluationBtn').on('click', function() {
        var selected = [];
        jQuery('input[name="selector[]"].can-batch-evaluation:checked').each(function() {
            selected.push(this.value);
        });
        if (selected.length > 0) {
            window.location.href = 'index.php?view=batch_evaluation&ids=' + selected.join(',');
        }
    });

    // Batch Convert Handler
    jQuery('#batchConvertBtn').on('click', function() {
        var selected = [];
        jQuery('input[name="selector[]"].can-batch-convert:checked').each(function() {
            selected.push(this.value);
        });
        if (selected.length > 0) {
            window.location.href = 'index.php?view=batch_convert&ids=' + selected.join(',');
        }
    });

    // Batch Renew Handler
    jQuery('#batchRenewBtn').on('click', function() {
        var selected = [];
        jQuery('input[name="selector[]"].can-batch-renew:checked').each(function() {
            selected.push(this.value);
        });
        if (selected.length > 0) {
            window.location.href = 'index.php?view=batch_renew&ids=' + selected.join(',');
        }
    });

    <?php endif; ?>
});
</script>