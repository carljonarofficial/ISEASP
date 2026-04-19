<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

$ids = isset($_GET['ids']) ? $_GET['ids'] : '';
if (empty($ids)) redirect("index.php");

global $mydb;
$ids_array = array_map('intval', explode(',', $ids));
$ids_str = implode(',', $ids_array);

// Fetch applicants to verify eligibility
$mydb->setQuery("SELECT * FROM tbl_applicants WHERE APPLICANTID IN ($ids_str) AND REQUIREMENT_STATUS = 'Complete' AND (EXAM_SLIP_GENERATED IS NULL OR EXAM_SLIP_GENERATED = '')");
$applicants = $mydb->loadResultList();

if (empty($applicants)) {
    message("No eligible applicants found in selection (must have complete requirements and no existing slip).", "error");
    redirect("index.php");
}
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Batch Generate Examination Slips</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <form method="POST" action="controller.php?action=batch_generate_exam_slips">
            <input type="hidden" name="ids" value="<?php echo $ids; ?>">
            
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <i class="fa fa-calendar"></i> Set Examination Schedule for <?php echo count($applicants); ?> Applicants
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Examination Date:</label>
                                <input type="date" name="exam_date" class="form-control" required value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Examination Time:</label>
                                <input type="time" name="exam_time" class="form-control" required value="08:00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Examination Venue:</label>
                                <input type="text" name="exam_venue" class="form-control" required value="Provincial Capitol, Vigan City, Ilocos Sur">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Additional Notes:</label>
                        <textarea name="notes" class="form-control" rows="2">Please arrive at least 30 minutes before the scheduled time.</textarea>
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
                                <th>School</th>
                                <th>Course</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applicants as $a): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($a->LASTNAME . ', ' . $a->FIRSTNAME); ?></td>
                                <td><?php echo htmlspecialchars($a->MUNICIPALITY); ?></td>
                                <td><?php echo htmlspecialchars($a->SCHOOL); ?></td>
                                <td><?php echo htmlspecialchars($a->COURSE); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" name="generate" class="btn btn-success btn-lg">
                    <i class="fa fa-check"></i> Confirm and Generate Slips
                </button>
                <a href="index.php" class="btn btn-default btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>