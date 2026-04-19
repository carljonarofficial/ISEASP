<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

$ids = isset($_GET['ids']) ? $_GET['ids'] : '';
if (empty($ids)) redirect("index.php");

global $mydb;
$ids_array = array_map('intval', explode(',', $ids));
$ids_str = implode(',', $ids_array);

// Fetch applicants to verify eligibility (Must be in 'For Interview' status)
$mydb->setQuery("SELECT a.*, i.INTERVIEW_DATE, i.INTERVIEW_MODE, i.INTERVIEWER_ID 
                 FROM tbl_applicants a 
                 JOIN tbl_interview i ON a.APPLICANTID = i.APPLICANTID 
                 WHERE a.APPLICANTID IN ($ids_str) AND a.STATUS = 'For Interview'");
$applicants = $mydb->loadResultList();

if (empty($applicants)) {
    message("No eligible applicants found in selection (must be in 'For Interview' status and have a scheduled interview record).", "error");
    redirect("index.php?stage=interview");
}
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Batch Enter Interview Results</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> Enter the interview scores and recommendations for the selected applicants. 
            Applicants marked as <strong>'Pass'</strong> will be updated to 'Pending' status (For Evaluation), while <strong>'Fail'</strong> will be updated to 'Rejected'.
        </div>
        
        <form method="POST" action="controller.php?action=batch_interview_results">
            <input type="hidden" name="ids" value="<?php echo $ids; ?>">
            
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <i class="fa fa-check-circle"></i> Interview Results for <?php echo count($applicants); ?> Applicants
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Applicant Name</th>
                                    <th width="15%">Interview Date</th>
                                    <th width="10%">Score (%)</th>
                                    <th width="15%">Recommendation</th>
                                    <th>Comments</th>
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
                                    <td>
                                        <?php echo $a->INTERVIEW_DATE ? date('M d, Y h:i A', strtotime($a->INTERVIEW_DATE)) : 'Not set'; ?>
                                    </td>
                                    <td>
                                        <input type="number" name="scores[<?php echo $a->APPLICANTID; ?>]" class="form-control input-sm" min="0" max="100" step="0.01" required>
                                    </td>
                                    <td>
                                        <select name="recommendations[<?php echo $a->APPLICANTID; ?>]" class="form-control input-sm" required>
                                            <option value="Pass">Pass</option>
                                            <option value="Fail">Fail</option>
                                            <option value="For Review">For Review</option>
                                        </select>
                                    </td>
                                    <td>
                                        <textarea name="comments[<?php echo $a->APPLICANTID; ?>]" class="form-control input-sm" rows="1" placeholder="Optional comments"></textarea>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Batch Settings for Comments (Optional) -->
            <div class="panel panel-default">
                <div class="panel-heading">Global Remarks (Optional)</div>
                <div class="panel-body">
                    <div class="form-group">
                        <label>Append this to all comments:</label>
                        <input type="text" id="global_comment" class="form-control" placeholder="e.g. Batch interview conducted on <?= date('M d, Y') ?>">
                        <p class="help-block">This text will be added to the end of each applicant's individual comments.</p>
                    </div>
                </div>
            </div>

            <div class="text-center" style="margin-bottom: 30px;">
                <button type="submit" name="save_results" class="btn btn-success btn-lg">
                    <i class="fa fa-save"></i> Save All Results
                </button>
                <a href="index.php?stage=interview" class="btn btn-default btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function() {
    var globalComment = document.getElementById('global_comment').value;
    if (globalComment) {
        document.querySelectorAll('textarea[name^="comments"]').forEach(function(textarea) {
            if (textarea.value) textarea.value += " | " + globalComment;
            else textarea.value = globalComment;
        });
    }
});
</script>