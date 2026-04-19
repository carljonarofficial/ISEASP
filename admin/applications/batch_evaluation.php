<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

$ids = isset($_GET['ids']) ? $_GET['ids'] : '';
if (empty($ids)) redirect("index.php");

global $mydb;
$ids_array = array_map('intval', explode(',', $ids));
$ids_str = implode(',', $ids_array);

// Fetch applicants to verify eligibility (Passed exam and in 'Pending' status which is the Evaluation stage)
$mydb->setQuery("SELECT a.*, er.TOTAL_SCORE as EXAM_SCORE, i.SCORE as INTERVIEW_SCORE 
                 FROM tbl_applicants a 
                 LEFT JOIN tbl_exam_results er ON a.APPLICANTID = er.APPLICANTID 
                 LEFT JOIN tbl_interview i ON a.APPLICANTID = i.APPLICANTID 
                 WHERE a.APPLICANTID IN ($ids_str) AND a.EXAM_STATUS = 'Passed' AND a.STATUS = 'Pending'");
$applicants = $mydb->loadResultList();

if (empty($applicants)) {
    message("No eligible applicants found in selection (must have passed exam and be in 'Pending' status).", "error");
    redirect("index.php?stage=evaluation");
}
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Batch Final Evaluation</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> Review scores and set final recommendations for the selected applicants. 
            Applicants marked as <strong>'Qualified'</strong> can then be converted to scholars.
        </div>
        
        <form method="POST" action="controller.php?action=batch_evaluation">
            <input type="hidden" name="ids" value="<?php echo $ids; ?>">
            
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <i class="fa fa-gavel"></i> Final Evaluation for <?php echo count($applicants); ?> Applicants
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Applicant Name</th>
                                    <th width="10%">Exam</th>
                                    <th width="10%">Interview</th>
                                    <th width="20%">Final Status</th>
                                    <th>Feedback / Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applicants as $a): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($a->LASTNAME . ', ' . $a->FIRSTNAME); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($a->MUNICIPALITY); ?></small>
                                        <input type="hidden" name="applicant_ids[]" value="<?php echo $a->APPLICANTID; ?>">
                                    </td>
                                    <td class="text-center">
                                        <span class="label label-success"><?php echo $a->EXAM_SCORE ?? 'N/A'; ?>%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="label label-info"><?php echo $a->INTERVIEW_SCORE ?? 'N/A'; ?>%</span>
                                    </td>
                                    <td>
                                        <select name="final_statuses[<?php echo $a->APPLICANTID; ?>]" class="form-control input-sm" required>
                                            <option value="">-- Select --</option>
                                            <option value="Qualified">Qualified</option>
                                            <option value="Not Qualified">Not Qualified</option>
                                            <option value="For Review">For Review</option>
                                        </select>
                                    </td>
                                    <td>
                                        <textarea name="feedbacks[<?php echo $a->APPLICANTID; ?>]" class="form-control input-sm" rows="1" placeholder="Evaluation notes"></textarea>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">Global Remarks (Optional)</div>
                <div class="panel-body">
                    <div class="form-group">
                        <label>Append this to all feedback:</label>
                        <input type="text" id="global_feedback" class="form-control" placeholder="e.g. Batch evaluation completed on <?= date('M d, Y') ?>">
                    </div>
                </div>
            </div>

            <div class="text-center" style="margin-bottom: 30px;">
                <button type="submit" name="save_evaluations" class="btn btn-success btn-lg">
                    <i class="fa fa-save"></i> Save All Evaluations
                </button>
                <a href="index.php?stage=evaluation" class="btn btn-default btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function() {
    var globalFeedback = document.getElementById('global_feedback').value;
    if (globalFeedback) {
        document.querySelectorAll('textarea[name^="feedbacks"]').forEach(function(textarea) {
            if (textarea.value) textarea.value += " | " + globalFeedback;
            else textarea.value = globalFeedback;
        });
    }
});
</script>