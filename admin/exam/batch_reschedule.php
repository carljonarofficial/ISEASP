<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

global $mydb;

// Get pre-selected IDs from URL if available
$preSelected = isset($_GET['ids']) ? array_map('intval', explode(',', $_GET['ids'])) : array();
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Batch Reschedule Exams</h1>
    </div>
</div>

<!-- Instructions -->
<div class="row" style="margin-bottom: 20px;">
    <div class="col-lg-12">
        <div class="alert alert-info">
            <h4><i class="fa fa-info-circle"></i> Instructions</h4>
            <ul style="margin-bottom: 0;">
                <li>Modify the new exam schedule details below</li>
                <li>Select or deselect applicants from the list</li>
                <li>The same new exam date, time, and venue will be applied to all selected applicants</li>
                <li>All changes are logged for audit purposes</li>
            </ul>
        </div>
    </div>
</div>

<form action="controller.php?action=batch_reschedule" method="POST" id="batchRescheduleForm">
    <!-- Batch Settings -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-lg-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <i class="fa fa-calendar"></i> New Exam Schedule (Applied to All Selected)
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>New Exam Date *</label>
                                <input type="date" name="new_exam_date" class="form-control" required
                                       min="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>New Exam Time *</label>
                                <input type="time" name="new_exam_time" class="form-control" required
                                       value="08:00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>New Venue *</label>
                                <input type="text" name="new_exam_venue" class="form-control" required
                                       value="Provincial Capitol, Vigan City, Ilocos Sur">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applicant Selection -->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-users"></i> Select Applicants to Reschedule
                    <div class="pull-right">
                        <button type="button" class="btn btn-xs btn-info" id="selectAllApplicants">
                            <i class="fa fa-check-square"></i> Select All Visible
                        </button>
                        <button type="button" class="btn btn-xs btn-warning" id="clearAllApplicants">
                            <i class="fa fa-square-o"></i> Clear All
                        </button>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table id="applicant-table" class="table table-striped table-bordered table-hover" style="font-size:13px">
                            <thead>
                                <tr>
                                    <th width="3%"><input type="checkbox" id="selectAllCheckbox" title="Select all visible on current page"></th>
                                    <th>Applicant Name</th>
                                    <th>Municipality</th>
                                    <th>School</th>
                                    <th>Course</th>
                                    <th>Current Exam Date</th>
                                    <th>Current Exam Time</th>
                                    <th>Current Venue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Get applicants with pending exams
                                $sql = "
                                    SELECT a.*
                                    FROM tbl_applicants a
                                    WHERE a.EXAM_SLIP_GENERATED IS NOT NULL
                                    AND a.EXAM_SLIP_GENERATED != ''
                                    AND a.EXAM_STATUS = 'Pending'
                                    ORDER BY a.EXAM_DATE ASC, a.EXAM_TIME ASC
                                ";

                                $mydb->setQuery($sql);
                                $applicants = $mydb->loadResultList();

                                if (!empty($applicants)) {
                                    foreach ($applicants as $a):
                                        $isPreSelected = in_array($a->APPLICANTID, $preSelected);
                                ?>
                                    <tr>
                                        <td><input type="checkbox" name="applicant_ids[]" value="<?= $a->APPLICANTID ?>" class="applicant-checkbox" <?= $isPreSelected ? 'checked' : '' ?> title="Select this applicant"></td>
                                        <td><?= htmlspecialchars($a->LASTNAME . ', ' . $a->FIRSTNAME . ' ' . ($a->MIDDLENAME ?? '')) ?></td>
                                        <td><?= htmlspecialchars($a->MUNICIPALITY ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($a->SCHOOL ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($a->COURSE ?? 'N/A') ?></td>
                                        <td><?= $a->EXAM_DATE ? date('M d, Y', strtotime($a->EXAM_DATE)) : 'N/A' ?></td>
                                        <td><?= $a->EXAM_TIME ? date('h:i A', strtotime($a->EXAM_TIME)) : 'N/A' ?></td>
                                        <td><?= htmlspecialchars($a->EXAM_VENUE ?? 'N/A') ?></td>
                                    </tr>
                                <?php
                                    endforeach;
                                } else {
                                    echo '<tr><td colspan="8" class="text-center"><div class="alert alert-info" style="margin: 20px;"><i class="fa fa-info-circle"></i> No applicants with pending exams found.</div></td></tr>';
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
        <div class="col-lg-12 text-center" style="margin-top: 20px;">
            <a href="index.php?view=schedule" class="btn btn-default btn-lg">
                <i class="fa fa-arrow-left"></i> Back to Schedule
            </a>
            <button type="submit" class="btn btn-success btn-lg" id="submitReschedule" disabled>
                <i class="fa fa-calendar"></i> Reschedule Selected Exams
            </button>
        </div>
    </div>
</form>

<script src="<?php echo web_root;?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#applicant-table').DataTable({
        "pageLength": 25,
        "order": [[5, "asc"]], // Order by exam date
        "columnDefs": [
            { "orderable": false, "targets": 0 } // Make checkbox column non-sortable
        ]
    });

    updateSubmitButton();

    // Handle select all checkbox
    $('#selectAllCheckbox').on('change', function() {
        $('input.applicant-checkbox:visible').prop('checked', $(this).prop('checked'));
        updateSubmitButton();
    });

    // Handle individual checkboxes
    $(document).on('change', 'input.applicant-checkbox', function() {
        updateSubmitButton();

        // Update select all checkbox state
        var totalCheckboxes = $('input.applicant-checkbox:visible').length;
        var checkedCheckboxes = $('input.applicant-checkbox:visible:checked').length;
        $('#selectAllCheckbox').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
    });

    // Select All button (all visible on current page)
    $('#selectAllApplicants').on('click', function(e) {
        e.preventDefault();
        $('input.applicant-checkbox:visible').prop('checked', true);
        $('#selectAllCheckbox').prop('checked', true);
        updateSubmitButton();
    });

    // Clear All button
    $('#clearAllApplicants').on('click', function(e) {
        e.preventDefault();
        $('input.applicant-checkbox').prop('checked', false);
        $('#selectAllCheckbox').prop('checked', false);
        updateSubmitButton();
    });

    // Form validation
    $('#batchRescheduleForm').on('submit', function(e) {
        var selectedCount = $('input.applicant-checkbox:checked').length;
        if (selectedCount === 0) {
            e.preventDefault();
            alert('Please select at least one applicant to reschedule.');
            return false;
        }

        if (!confirm('Are you sure you want to reschedule exams for ' + selectedCount + ' applicant(s)?')) {
            e.preventDefault();
            return false;
        }
    });
});

function updateSubmitButton() {
    var selectedCount = $('input.applicant-checkbox:checked').length;
    $('#submitReschedule').prop('disabled', selectedCount === 0);

    if (selectedCount > 0) {
        $('#submitReschedule').html('<i class="fa fa-calendar"></i> Reschedule ' + selectedCount + ' Exam(s)');
    } else {
        $('#submitReschedule').html('<i class="fa fa-calendar"></i> Reschedule Selected Exams');
    }
}
</script>