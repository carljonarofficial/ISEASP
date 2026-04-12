<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

global $mydb;

$ids = isset($_GET['ids']) ? $_GET['ids'] : '';
$ids_array = !empty($ids) ? array_map('intval', explode(',', $ids)) : array();
$ids_str = implode(',', $ids_array);
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Cancel Exams - Confirmation</h1>
    </div>
</div>

<!-- Confirmation Alert -->
<div class="row" style="margin-bottom: 20px;">
    <div class="col-lg-12">
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-ban"></i> Warning!</h4>
            You are about to cancel exam schedules for <strong><?php echo count($ids_array); ?> applicant(s)</strong>.
            This action will clear all exam-related information including date, time, venue, and exam slip number.
        </div>
    </div>
</div>

<?php if (!empty($ids_array)): ?>

<!-- Applicants to be cancelled -->
<div class="row" style="margin-bottom: 20px;">
    <div class="col-lg-12">
        <div class="panel panel-danger">
            <div class="panel-heading">
                <i class="fa fa-list"></i> Applicants With Cancelled Exams
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th>Applicant Name</th>
                                <th>Municipality</th>
                                <th>School</th>
                                <th>Current Exam Date</th>
                                <th>Current Venue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($ids_str)) {
                                $sql = "
                                    SELECT a.APPLICANTID, a.LASTNAME, a.FIRSTNAME, a.MIDDLENAME,
                                           a.MUNICIPALITY, a.SCHOOL, a.EXAM_DATE, a.EXAM_VENUE
                                    FROM tbl_applicants a
                                    WHERE a.APPLICANTID IN ($ids_str)
                                    ORDER BY a.EXAM_DATE ASC
                                ";
                                
                                $mydb->setQuery($sql);
                                $mydb->executeQuery();
                                $applicants = $mydb->loadResultList();
                                
                                $counter = 1;
                                foreach ($applicants as $a):
                            ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo htmlspecialchars($a->LASTNAME . ', ' . $a->FIRSTNAME . ' ' . ($a->MIDDLENAME ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars($a->MUNICIPALITY ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($a->SCHOOL ?? 'N/A'); ?></td>
                                    <td><?php echo $a->EXAM_DATE ? date('M d, Y H:i', strtotime($a->EXAM_DATE . ' ' . $a->EXAM_TIME)) : 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($a->EXAM_VENUE ?? 'N/A'); ?></td>
                                </tr>
                            <?php
                                endforeach;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row">
    <div class="col-lg-12 text-center">
        <form action="controller.php?action=batch_cancel_exams" method="POST" style="display:inline;">
            <input type="hidden" name="ids" value="<?php echo implode(',', $ids_array); ?>">
            
            <a href="index.php?view=schedule" class="btn btn-default btn-lg">
                <i class="fa fa-arrow-left"></i> Back to Schedule
            </a>
            
            <button type="submit" class="btn btn-danger btn-lg" onclick="return confirm('This action cannot be undone. Cancel exams for all selected applicants?');">
                <i class="fa fa-times-circle"></i> Confirm Cancellation
            </button>
        </form>
    </div>
</div>

<?php else: ?>

<div class="row">
    <div class="col-lg-12">
        <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i> No applicants selected for cancellation.
        </div>
        <a href="index.php?view=schedule" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> Back to Schedule
        </a>
    </div>
</div>

<?php endif; ?>
