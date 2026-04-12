<?php
require_once("../../include/initialize.php");

if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

global $mydb;
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Final Evaluation</h1>
    </div>
</div>

<!-- Summary Stats -->
<!-- <div class="row" style="margin-bottom: 15px;">
    <div class="col-lg-4 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <?php
                $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_applicants WHERE STATUS = 'For Interview' AND EXAM_STATUS = 'Passed'");
                $mydb->executeQuery();
                $for_interview = $mydb->loadSingleResult();
                ?>
                <h3><?= $for_interview->total ?? 0 ?></h3>
                <p>For Interview</p>
            </div>
            <div class="icon">
                <i class="fa fa-users"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <?php
                $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_applicants WHERE STATUS = 'Qualified'");
                $mydb->executeQuery();
                $qualified = $mydb->loadSingleResult();
                ?>
                <h3><?= $qualified->total ?? 0 ?></h3>
                <p>Qualified</p>
            </div>
            <div class="icon">
                <i class="fa fa-star"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner">
                <?php
                $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_scholarship_awards WHERE STATUS = 'Active'");
                $mydb->executeQuery();
                $scholars = $mydb->loadSingleResult();
                ?>
                <h3><?= $scholars->total ?? 0 ?></h3>
                <p>Active Scholars</p>
            </div>
            <div class="icon">
                <i class="fa fa-graduation-cap"></i>
            </div>
        </div>
    </div>
</div> -->

<!-- Tabs for different stages -->
<ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#forEvaluation">For Evaluation (Post-Interview)</a></li>
    <li><a data-toggle="tab" href="#qualified">Qualified</a></li>
</ul>

<div class="tab-content">
    <!-- For Evaluation Tab -->
    <div id="forEvaluation" class="tab-pane fade in active">
        <div class="panel panel-default" style="margin-top: 15px;">
            <div class="panel-heading">
                <i class="fa fa-check-square"></i> Applicants Ready for Final Evaluation
                <!-- <div class="pull-right">
                    <a href="#" onclick="window.print()" class="btn btn-default btn-xs">
                        <i class="fa fa-print"></i> Print
                    </a>
                </div> -->
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="dash-table" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Applicant Name</th>
                                <th>Municipality</th>
                                <th>School</th>
                                <th>Course</th>
                                <th>Exam Score</th>
                                <th>Interview Score</th>
                                <th>Requirements</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "
                                SELECT 
                                    a.*,
                                    er.TOTAL_SCORE as EXAM_SCORE,
                                    i.SCORE as INTERVIEW_SCORE,
                                    i.RECOMMENDATION,
                                    (SELECT COUNT(*) FROM tbl_applicant_requirement_checklist 
                                     WHERE APPLICANTID = a.APPLICANTID AND IS_VERIFIED = 1) as VERIFIED_REQ,
                                    (SELECT COUNT(*) FROM tbl_requirement WHERE REQUIRED = 'Yes') as TOTAL_REQ
                                FROM tbl_applicants a
                                LEFT JOIN tbl_exam_results er ON a.APPLICANTID = er.APPLICANTID
                                LEFT JOIN tbl_interview i ON a.APPLICANTID = i.APPLICANTID
                                WHERE a.EXAM_STATUS = 'Passed' 
                                AND i.RECOMMENDATION = 'Pass'
                                AND a.STATUS NOT IN ('Qualified', 'Scholar', 'Graduated', 'Rejected')
                                ORDER BY a.LASTNAME ASC
                            ";
                            
                            $mydb->setQuery($sql);
                            $mydb->executeQuery();
                            $applicants = $mydb->loadResultList();
                            
                            foreach ($applicants as $a):
                                $req_percentage = ($a->TOTAL_REQ > 0) ? round(($a->VERIFIED_REQ / $a->TOTAL_REQ) * 100) : 0;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($a->LASTNAME . ', ' . $a->FIRSTNAME . ' ' . ($a->MIDDLENAME ?? '')) ?></td>
                                <td><?= htmlspecialchars($a->MUNICIPALITY ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($a->SCHOOL ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($a->COURSE ?? 'N/A') ?></td>
                                <td><span class="label label-success"><?= $a->EXAM_SCORE ?? 'N/A' ?>%</span></td>
                                <td><span class="label label-info"><?= $a->INTERVIEW_SCORE ?? 'N/A' ?>%</span></td>
                                <td>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar progress-bar-success" style="width: <?= $req_percentage ?>%"></div>
                                    </div>
                                    <small><?= $a->VERIFIED_REQ ?>/<?= $a->TOTAL_REQ ?> verified</small>
                                </td>
                                <td>
                                    <a href="index.php?view=add&id=<?= $a->APPLICANTID ?>" class="btn btn-success btn-xs">
                                        <i class="fa fa-gavel"></i> Evaluate
                                    </a>
                                    <a href="../applications/index.php?view=view&id=<?= $a->APPLICANTID ?>" class="btn btn-info btn-xs">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($applicants)): ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="alert alert-info" style="margin: 10px;">
                                        <i class="fa fa-info-circle"></i> No applicants pending final evaluation.
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Qualified Tab -->
    <div id="qualified" class="tab-pane fade">
        <div class="panel panel-default" style="margin-top: 15px;">
            <div class="panel-heading">
                <i class="fa fa-star"></i> Qualified Applicants
                <!-- <div class="pull-right">
                    <a href="../evaluation/qualified.php" class="btn btn-primary btn-xs">
                        <i class="fa fa-eye"></i> View All Qualified
                    </a>
                </div> -->
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Applicant Name</th>
                                <th>Municipality</th>
                                <th>School</th>
                                <th>Course</th>
                                <th>Evaluation Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $mydb->setQuery("
                                SELECT a.*, e.EVALUATION_DATE
                                FROM tbl_applicants a
                                LEFT JOIN tbl_evaluation e ON a.APPLICANTID = e.APPLICANTID
                                WHERE a.STATUS = 'Qualified'
                                ORDER BY e.EVALUATION_DATE DESC
                                LIMIT 10
                            ");
                            $mydb->executeQuery();
                            $qualified_list = $mydb->loadResultList();
                            
                            foreach ($qualified_list as $q):
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($q->LASTNAME . ', ' . $q->FIRSTNAME . ' ' . ($q->MIDDLENAME ?? '')) ?></td>
                                <td><?= htmlspecialchars($q->MUNICIPALITY ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($q->SCHOOL ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($q->COURSE ?? 'N/A') ?></td>
                                <td><?= $q->EVALUATION_DATE ? date('M d, Y', strtotime($q->EVALUATION_DATE)) : 'N/A' ?></td>
                                <td>
                                    <a href="../applications/index.php?view=convert&id=<?= $q->APPLICANTID ?>" class="btn btn-success btn-xs">
                                        <i class="fa fa-graduation-cap"></i> Convert
                                    </a>
                                    <a href="../applications/index.php?view=view&id=<?= $q->APPLICANTID ?>" class="btn btn-info btn-xs">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($qualified_list)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No qualified applicants yet.<?php echo "\n"; ?>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo web_root; ?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script>
    $(document).ready(function () {
        $('#dash-table').DataTable({
            "pageLength": 25,
            "order": [[0, "desc"]],
            "columnDefs": [
                { "targets": "_all", "defaultContent": "" } // <--- Add this line
            ]
        });
    });
</script>