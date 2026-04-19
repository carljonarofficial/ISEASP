<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

$ids = isset($_GET['ids']) ? $_GET['ids'] : '';
if (empty($ids)) redirect("index.php");

global $mydb;
$ids_array = array_map('intval', explode(',', $ids));
$ids_str = implode(',', $ids_array);

// Fetch applicants to verify eligibility (Passed exam and not already Scholar/Qualified)
$mydb->setQuery("SELECT * FROM tbl_applicants WHERE APPLICANTID IN ($ids_str) AND EXAM_STATUS = 'Passed' AND STATUS NOT IN ('Scholar', 'Qualified')");
$applicants = $mydb->loadResultList();

if (empty($applicants)) {
    message("No eligible applicants found in selection (must have passed exam).", "error");
    redirect("index.php?stage=exam");
}

// Fetch available interviewers (users)
$mydb->setQuery("SELECT USERID, FULLNAME FROM tblusers WHERE ROLE != 'Applicant' ORDER BY FULLNAME");
$interviewers = $mydb->loadResultList();
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Batch Schedule Interview</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <form method="POST" action="controller.php?action=batch_schedule_interview">
            <input type="hidden" name="ids" value="<?php echo $ids; ?>">
            
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <i class="fa fa-calendar"></i> Set Interview Schedule for <?php echo count($applicants); ?> Applicants
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Interview Date & Time:</label>
                                <input type="datetime-local" name="interview_date" class="form-control" required value="<?php echo date('Y-m-d\T09:00', strtotime('+3 days')); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Interview Mode:</label>
                                <select name="interview_mode" class="form-control" required>
                                    <option value="Face-to-face">Face-to-face</option>
                                    <option value="Online">Online / Virtual</option>
                                    <option value="Phone">Phone Call</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Interviewer:</label>
                                <select name="interviewer_id" class="form-control" required>
                                    <option value="">Select Interviewer</option>
                                    <?php foreach($interviewers as $u): ?>
                                    <option value="<?= $u->USERID ?>" <?= ($u->USERID == $_SESSION['ADMIN_USERID']) ? 'selected' : '' ?>><?= htmlspecialchars($u->FULLNAME) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">Selected Applicants</div>
                <div class="panel-body">
                    <table class="table table-condensed table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Municipality</th>
                                <th>Exam Result</th>
                                <th>Current Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applicants as $a): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($a->LASTNAME . ', ' . $a->FIRSTNAME); ?></td>
                                <td><?php echo htmlspecialchars($a->MUNICIPALITY); ?></td>
                                <td><span class="label label-success"><?php echo $a->EXAM_STATUS; ?></span></td>
                                <td><?php echo $a->STATUS; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" name="schedule" class="btn btn-success btn-lg">
                    <i class="fa fa-check"></i> Confirm and Schedule Interviews
                </button>
                <a href="index.php?stage=exam" class="btn btn-default btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>