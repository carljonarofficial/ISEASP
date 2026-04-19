<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) redirect("index.php");

global $mydb;

// Get applicant details from database
$sql = "SELECT a.*, u.FULLNAME as CREATED_BY_NAME 
        FROM tbl_applicants a
        LEFT JOIN tblusers u ON a.CREATED_BY = u.USERID
        WHERE a.APPLICANTID = $id";

$mydb->setQuery($sql);
$mydb->executeQuery();
$applicant = $mydb->loadSingleResult();

if (!$applicant) {
    message("Applicant not found!", "error");
    redirect("index.php");
}

// Get requirement summary
$req_sql = "SELECT 
                COUNT(*) as total_requirements,
                SUM(CASE WHEN IS_SUBMITTED = 1 THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN IS_VERIFIED = 1 THEN 1 ELSE 0 END) as verified
            FROM tbl_applicant_requirement_checklist 
            WHERE APPLICANTID = $id";

$mydb->setQuery($req_sql);
$mydb->executeQuery();
$requirements = $mydb->loadSingleResult();

// Get exam results if any
$exam_sql = "SELECT * FROM tbl_exam_results WHERE APPLICANTID = $id ORDER BY EXAM_DATE DESC LIMIT 1";
$mydb->setQuery($exam_sql);
$mydb->executeQuery();
$exam = $mydb->loadSingleResult();

// Get interview if any
$interview_sql = "SELECT i.*, u.FULLNAME as INTERVIEWER_NAME 
                  FROM tbl_interview i
                  LEFT JOIN tblusers u ON i.INTERVIEWER_ID = u.USERID
                  WHERE i.APPLICANTID = $id";
$mydb->setQuery($interview_sql);
$mydb->executeQuery();
$interview = $mydb->loadSingleResult();

// Get audit trail / activity logs
$logs_sql = "SELECT 
                l.ACTION,
                l.LOG_DATE,
                u.FULLNAME as USER_NAME
            FROM tbl_application_log l
            LEFT JOIN tblusers u ON l.USERID = u.USERID
            WHERE l.APPLICANTID = $id
            ORDER BY l.LOG_DATE DESC
            LIMIT 10";
$mydb->setQuery($logs_sql);
$mydb->executeQuery();
$logs = $mydb->loadResultList();
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Applicant Details</h1>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#personal">Personal Info</a></li>
    <li><a data-toggle="tab" href="#address">Address</a></li>
    <li><a data-toggle="tab" href="#educational">Educational</a></li>
    <li><a data-toggle="tab" href="#status">Status</a></li>
    <li><a data-toggle="tab" href="#requirements">Requirements</a></li>
    <li><a data-toggle="tab" href="#exam">Exam & Interview</a></li>
    <li><a data-toggle="tab" href="#history">Activity Log</a></li>
</ul>

<div class="tab-content">
    <!-- Personal Information Tab -->
    <div id="personal" class="tab-pane fade in active">
        <div class="panel panel-info" style="margin-top: 20px;">
            <div class="panel-heading">
                <i class="fa fa-user"></i> Personal Information
                <div class="pull-right">
                    <a href="index.php?view=edit&id=<?= $applicant->APPLICANTID ?>" class="btn btn-primary btn-xs">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                    <a href="index.php" class="btn btn-default btn-xs">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Full Name:</th>
                                <td><strong><?= htmlspecialchars($applicant->LASTNAME . ', ' . $applicant->FIRSTNAME . ' ' . ($applicant->MIDDLENAME ?? '') . ' ' . ($applicant->SUFFIX ?? '')) ?></strong></td>
                            </tr>
                            <tr>
                                <th>School ID Number:</th>
                                <td><?= htmlspecialchars($applicant->LRN ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Birthdate:</th>
                                <td><?= $applicant->BIRTHDATE ? date('F d, Y', strtotime($applicant->BIRTHDATE)) : 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Birthplace:</th>
                                <td><?= htmlspecialchars($applicant->BIRTHPLACE ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Gender:</th>
                                <td><?= htmlspecialchars($applicant->GENDER ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Civil Status:</th>
                                <td><?= htmlspecialchars($applicant->CIVIL_STATUS ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Religion:</th>
                                <td><?= htmlspecialchars($applicant->RELIGION ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Nationality:</th>
                                <td><?= htmlspecialchars($applicant->NATIONALITY ?? 'Filipino') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Contact No.:</th>
                                <td><?= htmlspecialchars($applicant->CONTACT ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?= htmlspecialchars($applicant->EMAIL ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Facebook:</th>
                                <td><?= $applicant->FACEBOOK_URL ? '<a href="' . htmlspecialchars($applicant->FACEBOOK_URL) . '" target="_blank">' . htmlspecialchars($applicant->FACEBOOK_URL) . '</a>' : 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Emergency Contact:</th>
                                <td><?= htmlspecialchars($applicant->EMERGENCY_CONTACT_NAME ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Emergency No.:</th>
                                <td><?= htmlspecialchars($applicant->EMERGENCY_CONTACT_NUMBER ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Relationship:</th>
                                <td><?= htmlspecialchars($applicant->EMERGENCY_CONTACT_RELATION ?? 'N/A') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Address Tab -->
    <div id="address" class="tab-pane fade">
        <div class="panel panel-info" style="margin-top: 20px;">
            <div class="panel-heading">
                <i class="fa fa-map-marker"></i> Address Information
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Permanent Address:</th>
                                <td><?= nl2br(htmlspecialchars($applicant->PERMANENT_ADDRESS ?? 'N/A')) ?></td>
                            </tr>
                            <tr>
                                <th>Current Address:</th>
                                <td><?= nl2br(htmlspecialchars($applicant->CURRENT_ADDRESS ?? 'N/A')) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">District:</th>
                                <td><?= htmlspecialchars($applicant->DISTRICT ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Municipality:</th>
                                <td><?= htmlspecialchars($applicant->MUNICIPALITY ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Barangay:</th>
                                <td><?= htmlspecialchars($applicant->BARANGAY ?? 'N/A') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Educational Tab -->
    <div id="educational" class="tab-pane fade">
        <div class="panel panel-success" style="margin-top: 20px;">
            <div class="panel-heading">
                <i class="fa fa-graduation-cap"></i> Educational Information
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="20%">School:</th>
                        <td width="30%"><?= htmlspecialchars($applicant->SCHOOL ?? 'N/A') ?></td>
                        <th width="20%">Course:</th>
                        <td width="30%"><?= htmlspecialchars($applicant->COURSE ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Year Level:</th>
                        <td><?= htmlspecialchars($applicant->YEARLEVEL ?? 'N/A') ?></td>
                        <th>GPA:</th>
                        <td><strong><?= $applicant->GPA ? $applicant->GPA . '%' : 'N/A' ?></strong></td>
                    </tr>
                    <tr>
                        <th>Application Type:</th>
                        <td><span class="label label-primary"><?= $applicant->APPLICATION_TYPE ?? 'New Applicant' ?></span></td>
                        <th>School Year:</th>
                        <td><?= htmlspecialchars($applicant->SCHOOL_YEAR ?? 'N/A') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Status Tab -->
    <div id="status" class="tab-pane fade">
        <div class="panel panel-warning" style="margin-top: 20px;">
            <div class="panel-heading">
                <i class="fa fa-info-circle"></i> Application Status
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Application Status:</th>
                                <td>
                                    <?php
                                    $status_color = match($applicant->STATUS) {
                                        'Scholar' => 'success',
                                        'Qualified' => 'primary',
                                        'For Interview' => 'info',
                                        'Pending' => 'warning',
                                        'Rejected' => 'danger',
                                        'Graduated' => 'success',
                                        default => 'default'
                                    };
                                    ?>
                                    <span class="label label-<?= $status_color ?>" style="font-size: 14px;"><?= $applicant->STATUS ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>Requirements Status:</th>
                                <td>
                                    <?php
                                    $req_status_color = match($applicant->REQUIREMENT_STATUS) {
                                        'Complete' => 'success',
                                        'Incomplete' => 'danger',
                                        default => 'warning'
                                    };
                                    ?>
                                    <span class="label label-<?= $req_status_color ?>"><?= $applicant->REQUIREMENT_STATUS ?? 'Pending' ?></span>
                                    <?php if ($applicant->REQUIREMENT_DATE): ?>
                                        <br><small class="text-muted">Last updated: <?= date('M d, Y', strtotime($applicant->REQUIREMENT_DATE)) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Exam Status:</th>
                                <td>
                                    <?php
                                    $exam_color = match($applicant->EXAM_STATUS) {
                                        'Passed' => 'success',
                                        'Failed' => 'danger',
                                        default => 'default'
                                    };
                                    ?>
                                    <span class="label label-<?= $exam_color ?>"><?= $applicant->EXAM_STATUS ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">4Ps Beneficiary:</th>
                                <td><?= $applicant->IS_4PS_BENEFICIARY == 'Yes' ? '<span class="label label-success">Yes</span>' : 'No' ?></td>
                            </tr>
                            <tr>
                                <th>Indigenous People:</th>
                                <td><?= $applicant->IS_INDIGENOUS == 'Yes' ? '<span class="label label-info">Yes</span>' : 'No' ?></td>
                            </tr>
                            <tr>
                                <th>Family Income:</th>
                                <td>₱ <?= number_format($applicant->FAMILY_ANNUAL_INCOME ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <th>Parent's Occupation:</th>
                                <td><?= htmlspecialchars($applicant->PARENT_OCCUPATION ?? 'N/A') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="row" style="margin-top: 15px;">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Created By:</th>
                                <td><?= htmlspecialchars($applicant->CREATED_BY_NAME ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Date Created:</th>
                                <td><?= date('F d, Y h:i A', strtotime($applicant->DATECREATED)) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Requirements Tab -->
    <div id="requirements" class="tab-pane fade">
        <div class="panel panel-info" style="margin-top: 20px;">
            <div class="panel-heading">
                <i class="fa fa-check-square-o"></i> Requirements Summary
                <div class="pull-right">
                    <a href="<?php echo web_root;?>admin/checklist/index.php?view=view&id=<?= $applicant->APPLICANTID ?>" class="btn btn-info btn-xs">
                        <i class="fa fa-external-link"></i> Full Checklist
                    </a>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="well">
                            <h1><?= $requirements->total_requirements ?? 0 ?></h1>
                            <p>Total Requirements</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="well">
                            <h1><?= $requirements->submitted ?? 0 ?></h1>
                            <p>Submitted</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="well">
                            <h1><?= $requirements->verified ?? 0 ?></h1>
                            <p>Verified</p>
                        </div>
                    </div>
                </div>
                
                <?php 
                $total = $requirements->total_requirements ?? 1;
                $verified = $requirements->verified ?? 0;
                $percentage = ($total > 0) ? round(($verified / $total) * 100) : 0;
                ?>
                <div class="progress progress-lg" style="height: 30px; margin-top: 20px;">
                    <div class="progress-bar progress-bar-success" style="width: <?= $percentage ?>%; line-height: 30px; font-size: 14px;">
                        <?= $percentage ?>% Complete (<?= $verified ?>/<?= $total ?>)
                    </div>
                </div>
                
                <?php if ($verified < $total): ?>
                <div class="alert alert-warning" style="margin-top: 20px;">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong>Note:</strong> This applicant has incomplete requirements. 
                    They must complete all requirements before an exam slip can be generated.
                </div>
                <?php else: ?>
                <div class="alert alert-success" style="margin-top: 20px;">
                    <i class="fa fa-check-circle"></i>
                    <strong>Complete!</strong> All requirements have been verified. 
                    This applicant is eligible for examination.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Exam & Interview Tab -->
    <div id="exam" class="tab-pane fade">
        <div class="row" style="margin-top: 20px;">
            <div class="col-md-6">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <i class="fa fa-pencil"></i> Examination Result
                    </div>
                    <div class="panel-body">
                        <?php if ($exam): ?>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Exam Date:</th>
                                <td><?= date('F d, Y h:i A', strtotime($exam->EXAM_DATE)) ?></td>
                            </tr>
                            <tr>
                                <th>Score:</th>
                                <td><strong><?= $exam->TOTAL_SCORE ?>%</strong></td>
                            </tr>
                            <tr>
                                <th>Passing Score:</th>
                                <td><?= $exam->PASSING_SCORE ?>%</td>
                            </tr>
                            <tr>
                                <th>Result:</th>
                                <td>
                                    <?php if ($exam->TOTAL_SCORE >= $exam->PASSING_SCORE): ?>
                                        <span class="label label-success" style="font-size: 14px;">PASSED</span>
                                    <?php else: ?>
                                        <span class="label label-danger" style="font-size: 14px;">FAILED</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if (!empty($exam->REMARKS)): ?>
                            <tr>
                                <th>Remarks:</th>
                                <td><?= nl2br(htmlspecialchars($exam->REMARKS)) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                        <?php else: ?>
                        <p class="text-center">No exam result recorded yet.</p>
                        <?php endif; ?>
                        
                        <?php if ($applicant->EXAM_SLIP_GENERATED): ?>
                        <hr>
                        <h5>Exam Slip Information:</h5>
                        <table class="table table-bordered table-condensed">
                            <tr>
                                <th>Slip Number:</th>
                                <td><?= htmlspecialchars($applicant->EXAM_SLIP_NUMBER) ?></td>
                            </tr>
                            <tr>
                                <th>Generated:</th>
                                <td><?= date('M d, Y h:i A', strtotime($applicant->EXAM_SLIP_GENERATED)) ?></td>
                            </tr>
                            <tr>
                                <th>Scheduled Date:</th>
                                <td><?= $applicant->EXAM_DATE ? date('M d, Y', strtotime($applicant->EXAM_DATE)) : 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Venue:</th>
                                <td><?= htmlspecialchars($applicant->EXAM_VENUE ?? 'N/A') ?></td>
                            </tr>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="panel panel-success">
                    <div class="panel-heading">
                        <i class="fa fa-comments"></i> Interview Information
                    </div>
                    <div class="panel-body">
                        <?php if ($interview): ?>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Interview Date:</th>
                                <td><?= date('F d, Y h:i A', strtotime($interview->INTERVIEW_DATE)) ?></td>
                            </tr>
                            <tr>
                                <th>Interviewer:</th>
                                <td><?= htmlspecialchars($interview->INTERVIEWER_NAME ?? 'Not Assigned') ?></td>
                            </tr>
                            <tr>
                                <th>Mode:</th>
                                <td><?= $interview->INTERVIEW_MODE ?></td>
                            </tr>
                            <tr>
                                <th>Score:</th>
                                <td><strong><?= $interview->SCORE ?? 'N/A' ?>%</strong></td>
                            </tr>
                            <tr>
                                <th>Recommendation:</th>
                                <td>
                                    <?php
                                    $rec_color = match($interview->RECOMMENDATION) {
                                        'Pass' => 'success',
                                        'Fail' => 'danger',
                                        default => 'warning'
                                    };
                                    ?>
                                    <span class="label label-<?= $rec_color ?>"><?= $interview->RECOMMENDATION ?? 'For Review' ?></span>
                                </td>
                            </tr>
                            <?php if (!empty($interview->COMMENTS)): ?>
                            <tr>
                                <th>Comments:</th>
                                <td><?= nl2br(htmlspecialchars($interview->COMMENTS)) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                        <?php else: ?>
                        <p class="text-center">No interview scheduled yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Log Tab -->
    <div id="history" class="tab-pane fade">
        <div class="panel panel-default" style="margin-top: 20px;">
            <div class="panel-heading">
                <i class="fa fa-history"></i> Activity Log
            </div>
            <div class="panel-body">
                <?php if (!empty($logs)): ?>
                <div class="timeline">
                    <?php 
                    $current_date = '';
                    foreach ($logs as $log):
                        $log_date = date('Y-m-d', strtotime($log->LOG_DATE));
                        if ($current_date != $log_date):
                            $current_date = $log_date;
                    ?>
                    <div class="time-label">
                        <span class="bg-blue"><?= date('F d, Y', strtotime($log->LOG_DATE)) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div>
                        <i class="fa 
                            <?php 
                            if (strpos($log->ACTION, 'Created') !== false) echo 'fa-plus-circle bg-green';
                            elseif (strpos($log->ACTION, 'Updated') !== false) echo 'fa-edit bg-yellow';
                            elseif (strpos($log->ACTION, 'Deleted') !== false) echo 'fa-trash bg-red';
                            elseif (strpos($log->ACTION, 'Exam') !== false) echo 'fa-pencil bg-aqua';
                            elseif (strpos($log->ACTION, 'Interview') !== false) echo 'fa-users bg-purple';
                            else echo 'fa-info-circle bg-blue';
                            ?>
                        "></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fa fa-clock-o"></i> <?= date('h:i A', strtotime($log->LOG_DATE)) ?></span>
                            <h3 class="timeline-header">
                                <strong><?= htmlspecialchars($log->USER_NAME ?? 'System') ?></strong>
                            </h3>
                            <div class="timeline-body">
                                <?= htmlspecialchars($log->ACTION) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div>
                        <i class="fa fa-clock-o bg-gray"></i>
                    </div>
                </div>
                <?php else: ?>
                <p class="text-center">No activity logs found for this applicant.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row">
    <div class="col-lg-12 text-center" style="margin-bottom: 20px; margin-top: 20px;">
        <div class="btn-group">
            <a href="index.php?view=edit&id=<?= $applicant->APPLICANTID ?>" class="btn btn-primary">
                <i class="fa fa-edit"></i> Edit Applicant
            </a>
            <a href="../checklist/index.php?view=view&id=<?= $applicant->APPLICANTID ?>" class="btn btn-info">
                <i class="fa fa-check-square-o"></i> Manage Requirements
            </a>
            <?php if ($applicant->REQUIREMENT_STATUS == 'Complete' && empty($applicant->EXAM_SLIP_GENERATED)): ?>
            <a href="index.php?view=exam_slip&id=<?= $applicant->APPLICANTID ?>" class="btn btn-warning">
                <i class="fa fa-ticket"></i> Generate Exam Slip
            </a>
            <?php endif; ?>
            <?php if (!empty($applicant->EXAM_SLIP_GENERATED) && $applicant->EXAM_STATUS == 'Pending'): ?>
            <a href="index.php?view=print_slip&id=<?= $applicant->APPLICANTID ?>" class="btn btn-primary" target="_blank">
                <i class="fa fa-print"></i> Print Exam Slip
            </a>
            <?php endif; ?>
            <?php if ($applicant->EXAM_STATUS == 'Passed' && !$interview): ?>
            <a href="<?php echo web_root;?>admin/interview/index.php?view=schedule&id=<?= $applicant->APPLICANTID ?>" class="btn btn-success">
                <i class="fa fa-calendar"></i> Schedule Interview
            </a>
            <?php endif; ?>
            <?php if ($applicant->STATUS == 'Qualified'): ?>
            <a href="index.php?view=convert&id=<?= $applicant->APPLICANTID ?>" class="btn btn-success">
                <i class="fa fa-graduation-cap"></i> Convert to Scholar
            </a>
            <?php endif; ?>
            <a href="index.php" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    margin: 0 0 30px 0;
    padding: 0;
    list-style: none;
}
.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #ddd;
    left: 31px;
    margin: 0;
    border-radius: 2px;
}
.timeline > div {
    position: relative;
    margin-right: 10px;
    margin-bottom: 15px;
}
.timeline > div:before,
.timeline > div:after {
    content: " ";
    display: table;
}
.timeline > div:after {
    clear: both;
}
.timeline > div > .timeline-item {
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 3px;
    margin-top: 0;
    background: #fff;
    color: #444;
    margin-left: 60px;
    margin-right: 15px;
    padding: 0;
    position: relative;
}
.timeline > div > .timeline-item > .time {
    color: #999;
    float: right;
    padding: 10px;
    font-size: 12px;
}
.timeline > div > .timeline-item > .timeline-header {
    margin: 0;
    color: #555;
    border-bottom: 1px solid #f4f4f4;
    padding: 10px;
    font-size: 16px;
    line-height: 1.1;
}
.timeline > div > .timeline-item > .timeline-body {
    padding: 10px;
}
.timeline > div > .fa {
    width: 30px;
    height: 30px;
    font-size: 15px;
    line-height: 30px;
    position: absolute;
    color: #fff;
    border-radius: 50%;
    text-align: center;
    left: 18px;
    top: 0;
}
.time-label {
    margin-bottom: 15px;
    position: relative;
}
.time-label > span {
    border-radius: 4px;
    background-color: #fff;
    display: inline-block;
    padding: 5px 10px;
    font-weight: 600;
}
.bg-blue { background-color: #3c8dbc; }
.bg-green { background-color: #00a65a; }
.bg-yellow { background-color: #f39c12; }
.bg-red { background-color: #dd4b39; }
.bg-aqua { background-color: #00c0ef; }
.bg-purple { background-color: #605ca8; }
.bg-gray { background-color: #d2d6de; }
.well {
    background-color: #f9f9f9;
    border: 1px solid #e3e3e3;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 0;
}
.progress-lg {
    height: 30px;
    border-radius: 15px;
}
</style>

<script>
$(document).ready(function() {
    var hash = window.location.hash;
    if(hash) {
        $('.nav-tabs a[href="' + hash + '"]').tab('show');
    }
    
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        window.location.hash = e.target.hash;
    });
});
</script>