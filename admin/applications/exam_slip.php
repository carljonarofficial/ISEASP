<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) redirect("index.php");

global $mydb;

// Get applicant details
$mydb->setQuery("SELECT * FROM tbl_applicants WHERE APPLICANTID = $id");
$mydb->executeQuery();
$applicant = $mydb->loadSingleResult();

if (!$applicant) {
    message("Applicant not found!", "error");
    redirect("index.php");
}

// Check if requirements are complete
$mydb->setQuery("SELECT COUNT(*) as total FROM tbl_requirement WHERE REQUIRED = 'Yes'");
$mydb->executeQuery();
$total_required = $mydb->loadSingleResult()->total;

$mydb->setQuery("SELECT COUNT(*) as verified FROM tbl_applicant_requirement_checklist 
                 WHERE APPLICANTID = $id AND IS_VERIFIED = 1");
$mydb->executeQuery();
$verified = $mydb->loadSingleResult()->verified;

$requirements_complete = ($verified >= $total_required);

if (!$requirements_complete) {
    message("Cannot generate exam slip. Applicant has incomplete requirements!", "error");
    redirect("index.php?view=requirements&id=$id");
}

// Handle form submission
if (isset($_POST['generate_slip'])) {
    $exam_date = $_POST['exam_date'];
    $exam_time = $_POST['exam_time'];
    $exam_venue = $_POST['exam_venue'];
    $notes = $_POST['notes'];
    
    // Generate exam slip number
    $slip_number = 'EXAM-' . date('Y') . '-' . str_pad($id, 5, '0', STR_PAD_LEFT);
    
    // Update applicant with exam slip info
    $sql = "UPDATE tbl_applicants SET 
            EXAM_SLIP_NUMBER = '$slip_number',
            EXAM_SLIP_GENERATED = NOW(),
            EXAM_DATE = '$exam_date',
            EXAM_TIME = '$exam_time',
            EXAM_VENUE = '$exam_venue',
            EXAM_NOTES = '$notes'
            WHERE APPLICANTID = $id";
    
    $mydb->setQuery($sql);
    $mydb->executeQuery();

    // Add into exam result as pending
    $result_sql = "INSERT INTO tbl_exam_results (APPLICANTID, EXAMINER_ID, EXAM_DATE, TOTAL_SCORE, PASSING_SCORE, REMARKS, CREATED_AT, UPDATED_AT) 
                   VALUES ($id, " . $_SESSION['ADMIN_USERID'] . ", '$exam_date', NULL, 75, '', NOW(), NOW())";
    $mydb->setQuery($result_sql);
    $mydb->executeQuery();

    // FIXED: Log the action with new table structure
    $log_sql = "INSERT INTO tbl_application_log 
                (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS)
                SELECT 
                    $id, 
                    " . $_SESSION['ADMIN_USERID'] . ", 
                    USERNAME, 
                    ROLE, 
                    'Examination slip generated',
                    'EXAM',
                    CONCAT('Exam slip generated: ', '$slip_number')
                FROM tblusers 
                WHERE USERID = " . $_SESSION['ADMIN_USERID'];
    $mydb->setQuery($log_sql);
    $mydb->executeQuery();
    
    message("Examination slip generated successfully!", "success");
    redirect("index.php?view=print_slip&id=$id");
}
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Generate Examination Slip</h1>
    </div>
</div>

<!-- Requirements Status -->
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="alert alert-success">
            <i class="fa fa-check-circle"></i>
            <strong>Requirements Complete!</strong> All required documents have been verified.
            This applicant is now eligible to take the examination.
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-warning">
            <div class="panel-heading">
                <i class="fa fa-ticket"></i> Examination Slip for <?php echo htmlspecialchars($applicant->LASTNAME . ', ' . $applicant->FIRSTNAME); ?>
            </div>
            <div class="panel-body">
                
                <!-- Applicant Summary -->
                <div class="well">
                    <h4>Applicant Information</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Full Name:</th>
                            <td><?php echo htmlspecialchars($applicant->LASTNAME . ', ' . $applicant->FIRSTNAME . ' ' . ($applicant->MIDDLENAME ?? '')); ?></td>
                        </tr>
                        <tr>
                            <th>Municipality:</th>
                            <td><?php echo htmlspecialchars($applicant->MUNICIPALITY ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>School:</th>
                            <td><?php echo htmlspecialchars($applicant->SCHOOL ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Course:</th>
                            <td><?php echo htmlspecialchars($applicant->COURSE ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Year Level:</th>
                            <td><?php echo htmlspecialchars($applicant->YEARLEVEL ?? 'N/A'); ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> 
                    <strong>Remind the applicant to bring the following:</strong>
                    <ul class="list-unstyled" style="margin-top: 10px; margin-left: 20px;">
                        <li><i class="fa fa-check text-success"></i> Printed copy of this exam slip</li>
                        <li><i class="fa fa-check text-success"></i> Valid ID or Birth Certificate</li>
                        <li><i class="fa fa-check text-success"></i> Ballpen and Pencil</li>
                        <li><i class="fa fa-check text-success"></i> Wear appropriate attire: White shirt and plain pants</li>
                    </ul>
                </div>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Examination Date:</label>
                        <input type="date" name="exam_date" class="form-control" required 
                               value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Examination Time:</label>
                        <input type="time" name="exam_time" class="form-control" required value="08:00">
                    </div>
                    
                    <div class="form-group">
                        <label>Examination Venue:</label>
                        <input type="text" name="exam_venue" class="form-control" required 
                               value="Provincial Capitol, Vigan City, Ilocos Sur">
                    </div>
                    
                    <div class="form-group">
                        <label>Additional Notes/Instructions:</label>
                        <textarea name="notes" class="form-control" rows="3">Please arrive at least 30 minutes before the scheduled time. Bring all required materials. Incomplete requirements will not be allowed to take the exam.</textarea>
                    </div>
                    
                    <!-- Requirements Summary -->
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h4 class="panel-title">Verified Requirements</h4>
                        </div>
                        <div class="panel-body">
                            <?php
                            $mydb->setQuery("
                                SELECT r.REQUIREMENT_NAME, c.IS_VERIFIED, c.VERIFIED_DATE, u.FULLNAME as VERIFIER
                                FROM tbl_applicant_requirement_checklist c
                                INNER JOIN tbl_requirement r ON c.REQUIREMENT_ID = r.REQUIREMENT_ID
                                LEFT JOIN tblusers u ON c.VERIFIED_BY = u.USERID
                                WHERE c.APPLICANTID = $id AND c.IS_VERIFIED = 1
                                ORDER BY r.CATEGORY
                            ");
                            $mydb->executeQuery();
                            $verified_reqs = $mydb->loadResultList();
                            ?>
                            
                            <table class="table table-condensed">
                                <?php foreach ($verified_reqs as $req): ?>
                                <tr>
                                    <td><i class="fa fa-check-circle text-success"></i> <?php echo htmlspecialchars($req->REQUIREMENT_NAME); ?></td>
                                    <td><small>Verified by: <?php echo htmlspecialchars($req->VERIFIER ?? 'System'); ?></small></td>
                                    <td><small><?php echo date('M d, Y', strtotime($req->VERIFIED_DATE)); ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                            
                            <p class="text-success">
                                <strong>Total Verified Requirements: <?php echo count($verified_reqs); ?>/<?php echo $total_required; ?></strong>
                            </p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="generate_slip" class="btn btn-primary btn-lg" 
                                onclick="return confirm('Generate examination slip for this applicant?')">
                            <i class="fa fa-ticket"></i> Generate Examination Slip
                        </button>
                        <a href="index.php" class="btn btn-default btn-lg">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Requirements Progress -->
<!-- <div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-info">
            <div class="panel-heading">
                <i class="fa fa-check-square-o"></i> Requirements Verification Progress
            </div>
            <div class="panel-body">
                <?php
                $mydb->setQuery("
                    SELECT r.CATEGORY, 
                           COUNT(*) as total,
                           SUM(CASE WHEN c.IS_VERIFIED = 1 THEN 1 ELSE 0 END) as verified
                    FROM tbl_requirement r
                    LEFT JOIN tbl_applicant_requirement_checklist c 
                        ON r.REQUIREMENT_ID = c.REQUIREMENT_ID AND c.APPLICANTID = $id
                    GROUP BY r.CATEGORY
                ");
                $mydb->executeQuery();
                $category_progress = $mydb->loadResultList();
                
                foreach ($category_progress as $cat):
                    $percentage = ($cat->total > 0) ? round(($cat->verified / $cat->total) * 100) : 0;
                ?>
                <div class="form-group">
                    <label><?php echo $cat->CATEGORY; ?> Requirements:</label>
                    <div class="progress">
                        <div class="progress-bar progress-bar-success" style="width: <?php echo $percentage; ?>%">
                            <?php echo $cat->verified; ?>/<?php echo $cat->total; ?> Verified
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div> -->

<script>
// Auto-calculate end time if needed
document.querySelector('input[name="exam_time"]').addEventListener('change', function() {
    // Optional: Add logic for exam duration
});
</script>