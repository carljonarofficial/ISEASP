<?php 
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

$stage = isset($_GET['stage']) ? $_GET['stage'] : '';
$district = isset($_GET['district']) ? $_GET['district'] : '';
$municipality = isset($_GET['municipality']) ? $_GET['municipality'] : '';
$school_year = isset($_GET['school_year']) ? $_GET['school_year'] : '';
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
</style>

<div class="row no-print">
    <div class="col-lg-12" style="margin-bottom: 15px;">
        <a href="index.php?view=add" class="btn btn-primary" style="background-color: #27ae60; border-color: #229954;">
            <i class="fa fa-plus"></i> New Applicant
        </a>
    </div>
</div>

<div class="row no-print">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: #27ae60; color: white; border-color: #229954;">
                <i class="fa fa-filter"></i> Filter Applicants
            </div>
            <div class="panel-body">
                <form method="GET" action="index.php" class="form-inline">
                    <input type="hidden" name="view" value="list">
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>School Year:</label>
                        <select name="school_year" class="form-control input-sm">
                            <option value="">All School Years</option>
                            <option value="2024-2025" <?php echo ($school_year == '2024-2025') ? 'selected' : ''; ?>>2024-2025</option>
                            <option value="2025-2026" <?php echo ($school_year == '2025-2026') ? 'selected' : ''; ?>>2025-2026</option>
                            <option value="2026-2027" <?php echo ($school_year == '2026-2027') ? 'selected' : ''; ?>>2026-2027</option>
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
                            <?php
                            $municipalities = $mydb->setQuery("SELECT * FROM tbl_municipalities WHERE IS_ACTIVE = 'Yes' ORDER BY MUNICIPALITY_NAME");
                            $municipalities = $mydb->loadResultList();
                            foreach($municipalities as $town):
                            ?>
                            <option value="<?php echo $town->MUNICIPALITY_NAME; ?>" <?php echo ($municipality == $town->MUNICIPALITY_NAME) ? 'selected' : ''; ?>>
                                <?php echo $town->MUNICIPALITY_NAME; ?>
                            </option>
                            <?php endforeach; ?>
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

<!-- STATUS TABS -->
<div class="row no-print">
    <div class="col-lg-12">
        <ul class="nav nav-tabs applicant-status-tabs" role="tablist">
            <li role="presentation" class="<?php echo ($stage == '' || $stage == 'new') ? 'active' : ''; ?>">
                <a href="index.php?stage=new" class="tab-link">
                    <i class="fa fa-user-plus"></i> New Applicants
                </a>
            </li>
            <li role="presentation" class="<?php echo ($stage == 'requirements') ? 'active' : ''; ?>">
                <a href="index.php?stage=requirements" class="tab-link">
                    <i class="fa fa-file-text"></i> Missing Requirements
                </a>
            </li>
            <li role="presentation" class="<?php echo ($stage == 'exam_slip') ? 'active' : ''; ?>">
                <a href="index.php?stage=exam_slip" class="tab-link">
                    <i class="fa fa-ticket"></i> Exam Slip
                </a>
            </li>
            <li role="presentation" class="<?php echo ($stage == 'exam') ? 'active' : ''; ?>">
                <a href="index.php?stage=exam" class="tab-link">
                    <i class="fa fa-pencil-square-o"></i> Exam Results
                </a>
            </li>
            <li role="presentation" class="<?php echo ($stage == 'evaluation') ? 'active' : ''; ?>">
                <a href="index.php?stage=evaluation" class="tab-link">
                    <i class="fa fa-check-square"></i> For Evaluation
                </a>
            </li>
            <li role="presentation" class="<?php echo ($stage == 'interview') ? 'active' : ''; ?>">
                <a href="index.php?stage=interview" class="tab-link">
                    <i class="fa fa-microphone"></i> For Interview
                </a>
            </li>
            <li role="presentation" class="<?php echo ($stage == 'qualified') ? 'active' : ''; ?>">
                <a href="index.php?stage=qualified" class="tab-link">
                    <i class="fa fa-check-circle"></i> Qualified
                </a>
            </li>
            <li role="presentation" class="<?php echo ($stage == 'scholar') ? 'active' : ''; ?>">
                <a href="index.php?stage=scholar" class="tab-link">
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
    background-color: #27ae60;
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
                        <th>LRN or ID No.</th>
                        <th>Full Name</th>
                        <th>Municipality</th>
                        <th>School</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Exam Status</th>
                        <th width="18%" class="text-center no-print">Actions</th>
                    </tr>	
                </thead> 
                <?php
                $where = array();
                
                // CRITICAL FIX: Only show new applicants, exclude scholars and graduates
                // By default, exclude scholars, graduates, and terminated
                if ($stage != 'scholar' && $stage != 'qualified') {
                    $where[] = "a.STATUS NOT IN ('Scholar', 'Graduated', 'Terminated')";
                }
                
                if (!empty($school_year)) {
                    $where[] = "a.SCHOOL_YEAR = '$school_year'";
                }
                
                switch($stage) {
                    case 'new':
                        $where[] = "(a.EXAM_SLIP_GENERATED IS NULL OR a.EXAM_SLIP_GENERATED = '') AND a.STATUS = 'Pending'";
                        $where[] = "a.APPLICATION_TYPE = 'New Applicant'";
                        break;
                    case 'requirements':
                        $where[] = "a.REQUIREMENT_STATUS = 'Incomplete' OR a.REQUIREMENT_STATUS IS NULL";
                        $where[] = "a.STATUS NOT IN ('Qualified', 'Scholar')";
                        break;
                    case 'exam_slip':
                        $where[] = "a.EXAM_SLIP_GENERATED IS NOT NULL AND a.EXAM_SLIP_GENERATED != '' AND a.EXAM_STATUS = 'Pending'";
                        $where[] = "a.STATUS NOT IN ('Qualified', 'Scholar')";
                        break;
                    case 'exam':
                        $where[] = "a.EXAM_STATUS IN ('Passed', 'Failed')";
                        $where[] = "a.STATUS NOT IN ('Qualified', 'Scholar')";
                        break;
                    case 'evaluation':
                        $where[] = "a.EXAM_STATUS = 'Passed' AND a.STATUS = 'Pending'";
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
                
                $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "WHERE a.STATUS NOT IN ('Scholar', 'Graduated', 'Terminated')";
                
                $mydb->setQuery("
                    SELECT 
                        a.*,
                        u.FULLNAME AS CREATED_BY,
                        (SELECT COUNT(*) FROM tbl_applicant_requirement_checklist 
                         WHERE APPLICANTID = a.APPLICANTID AND IS_VERIFIED = 1) AS VERIFIED_REQ,
                        (SELECT COUNT(*) FROM tbl_requirement WHERE REQUIRED = 'Yes') AS TOTAL_REQ
                    FROM tbl_applicants a
                    LEFT JOIN tblusers u ON a.CREATED_BY = u.USERID
                    $where_clause
                    ORDER BY a.DATECREATED DESC
                ");

                $applicants = $mydb->loadResultList();
                
                function getStageBadge($applicant) {
                    if($applicant->STATUS == 'Scholar') return 'stage-scholar';
                    if($applicant->STATUS == 'Qualified') return 'stage-qualified';
                    if($applicant->STATUS == 'For Interview') return 'stage-interview';
                    if($applicant->EXAM_STATUS == 'Passed' && $applicant->STATUS == 'Pending') return 'stage-evaluation';
                    if(!empty($applicant->EXAM_SLIP_GENERATED) && $applicant->EXAM_STATUS == 'Pending') return 'stage-exam-slip';
                    if(isset($applicant->REQUIREMENT_STATUS) && $applicant->REQUIREMENT_STATUS == 'Incomplete') return 'stage-requirements';
                    if($applicant->EXAM_STATUS != 'Pending' && $applicant->EXAM_STATUS != '') return 'stage-exam';
                    return 'stage-new';
                }
                
                function getStageText($applicant) {
                    if($applicant->STATUS == 'Scholar') return 'Scholar';
                    if($applicant->STATUS == 'Qualified') return 'Qualified';
                    if($applicant->STATUS == 'For Interview') return 'For Interview';
                    if($applicant->EXAM_STATUS == 'Passed' && $applicant->STATUS == 'Pending') return 'For Evaluation';
                    if(!empty($applicant->EXAM_SLIP_GENERATED) && $applicant->EXAM_STATUS == 'Pending') return 'Exam Slip';
                    if(isset($applicant->REQUIREMENT_STATUS) && $applicant->REQUIREMENT_STATUS == 'Incomplete') return 'Missing Requirements';
                    if($applicant->EXAM_STATUS == 'Passed') return 'Passed Exam';
                    if($applicant->EXAM_STATUS == 'Failed') return 'Failed Exam';
                    if(!empty($applicant->EXAM_SLIP_GENERATED)) return 'Exam Slip Generated';
                    return 'New Applicant';
                }
                ?>

                <tbody>
                    <?php 
                    $has_applicants = false;
                    foreach ($applicants as $a): 
                        // Skip scholars, graduates, and terminated if not specifically selected
                        if ($stage != 'scholar' && $stage != 'qualified') {
                            if (in_array($a->STATUS, ['Scholar', 'Graduated', 'Terminated'])) {
                                continue;
                            }
                        }
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
                        $req_percentage = ($total_req > 0) ? round(($verified_req / $total_req) * 100) : 0;
                        
                        $req_status_color = ($verified_req == $total_req) ? 'success' : 'warning';
                    ?>
                    <tr onclick="viewApplicant(<?= $a->APPLICANTID ?>)" style="cursor: pointer;">
                        <td class="no-print" onclick="event.cancelBubble=true; event.stopPropagation();"><input type="checkbox" name="selector[]" value="<?= $a->APPLICANTID ?>"></td>
                        <td><?= htmlspecialchars($a->LRN ?? 'N/A') ?></td>
                        <td>
                            <strong><?= htmlspecialchars($a->LASTNAME . ', ' . $a->FIRSTNAME . ' ' . ($a->MIDDLENAME ?? '')) ?></strong>
                            <div style="margin-top: 3px;">
                                <?php if(isset($a->IS_4PS_BENEFICIARY) && $a->IS_4PS_BENEFICIARY == 'Yes'): ?>
                                    <span class="label label-success status-badge" title="4Ps Beneficiary">4Ps</span>
                                <?php endif; ?>
                                <?php if(isset($a->IS_INDIGENOUS) && $a->IS_INDIGENOUS == 'Yes'): ?>
                                    <span class="label label-info status-badge" title="Indigenous People">IP</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($a->MUNICIPALITY ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($a->SCHOOL ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($a->COURSE ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($a->YEARLEVEL ?? 'N/A') ?></td>
                        <td><span class="label <?= $examColor ?> status-badge"><?= $examStatus ?></span></td>
                        <td class="text-center no-print" onclick="event.cancelBubble=true; event.stopPropagation();">
                            <div class="action-buttons">
                                <a href="./index.php?view=view&id=<?= $a->APPLICANTID ?>" 
                                   class="btn btn-info btn-xs" title="View Details">
                                    <i class="fa fa-eye"></i>
                                </a>
                                
                                <?php if(empty($a->EXAM_SLIP_GENERATED) && $a->STATUS == 'Pending' && $a->REQUIREMENT_STATUS == 'Complete'): ?>
                                    <a href="./index.php?view=exam_slip&id=<?= $a->APPLICANTID ?>" 
                                       class="btn btn-warning btn-xs" title="Generate Exam Slip">
                                        <i class="fa fa-ticket"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if(empty($a->EXAM_SLIP_GENERATED) && $a->STATUS == 'Pending' && $a->REQUIREMENT_STATUS != 'Complete'): ?>
                                    <a href="../checklist/index.php?view=view&id=<?= $a->APPLICANTID ?>" 
                                       class="btn btn-danger btn-xs" title="Missing Requirements">
                                        <i class="fa fa-exclamation-triangle"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if(!empty($a->EXAM_SLIP_GENERATED) && $a->EXAM_STATUS == 'Pending'): ?>
                                    <a href="./index.php?view=print_slip&id=<?= $a->APPLICANTID ?>" 
                                       class="btn btn-primary btn-xs" title="Print Exam Slip" target="_blank">
                                        <i class="fa fa-print"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if($a->EXAM_STATUS == 'Passed' && $a->STATUS == 'Pending'): ?>
                                    <a href="../evaluation/index.php?view=add&id=<?= $a->APPLICANTID ?>" 
                                       class="btn btn-success btn-xs" title="Evaluate">
                                        <i class="fa fa-check-square"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if($a->STATUS == 'Qualified'): ?>
                                    <a href="./index.php?view=convert&id=<?= $a->APPLICANTID ?>" 
                                       class="btn btn-success btn-xs" title="Convert to Scholar">
                                        <i class="fa fa-graduation-cap"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <a href="./index.php?view=edit&id=<?= $a->APPLICANTID ?>" 
                                   class="btn btn-primary btn-xs" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </a>
                                
                                <?php if (isset($_SESSION['ADMIN_ROLE']) && $_SESSION['ADMIN_ROLE'] === 'Super Admin'): ?>
                                    <a href="controller.php?action=delete&id=<?= $a->APPLICANTID ?>" 
                                       class="btn btn-danger btn-xs"
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
                        <td colspan="11" class="text-center">
                            <div class="alert alert-info" style="margin: 20px;">
                                <i class="fa fa-info-circle"></i> No applicants found.
                                <a href="index.php?view=add" class="alert-link">Click here to add a new applicant</a>.
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if($has_applicants): ?>
            <div class="row no-print" style="margin-top: 15px; padding: 0 15px;">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete selected applicants?')">
                        <i class="fa fa-trash"></i> Delete Selected
                    </button>
                </div>
                <div class="col-md-6 text-right">
                    <strong>Total Applicants: <?= count($applicants) ?></strong>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
function viewApplicant(id) {
    window.location.href = './index.php?view=view&id=' + id;
}

function printTable() {
    window.print();
}

document.getElementById('chkAll').onclick = function() {
    var checkboxes = document.querySelectorAll('input[name="selector[]"]');
    for(var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = this.checked;
    }
};

$(document).ready(function() {
    $('#dash-table').DataTable({
        "pageLength": 25,
        "order": [[1, "asc"]],
        "columnDefs": [
            { "orderable": false, "targets": [0, 10] }
        ],
        "language": {
            "emptyTable": "No applicants found",
            "info": "Showing _START_ to _END_ of _TOTAL_ applicants",
            "infoEmpty": "Showing 0 to 0 of 0 applicants",
            "infoFiltered": "(filtered from _MAX_ total applicants)"
        }
    });
    
    $('#dash-table tbody tr').hover(
        function() { $(this).css('background-color', '#e8f5e9'); },
        function() { $(this).css('background-color', ''); }
    );
});
</script>