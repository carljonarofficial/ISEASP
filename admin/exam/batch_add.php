<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Batch Add/Edit Exam Results</h1>
    </div>
</div>

<!-- Instructions -->
<div class="row" style="margin-bottom: 20px;">
    <div class="col-lg-12">
        <div class="alert alert-info">
            <h4><i class="fa fa-info-circle"></i> Instructions</h4>
            <ul style="margin-bottom: 0;">
                <li>Select applicants from the list below to add or update their exam results</li>
                <li>You can set the same exam date, passing score, and remarks for all selected applicants</li>
                <li>Individual scores can be entered for each applicant</li>
                <li>Leave score fields empty if you don't want to update existing results</li>
            </ul>
        </div>
    </div>
</div>

<form action="controller.php?action=batch_add_edit" method="POST" id="batchForm">
    <!-- Batch Settings -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-lg-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <i class="fa fa-cogs"></i> Batch Settings (Applied to All Selected)
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Exam Date *</label>
                                <input type="datetime-local" name="exam_date" class="form-control" required
                                       value="<?= date('Y-m-d\TH:i') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Passing Score *</label>
                                <input type="number" name="passing_score" class="form-control" required
                                       min="0" max="100" step="0.01" value="75.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Remarks</label>
                                <input type="text" name="remarks" class="form-control"
                                       placeholder="Optional remarks for all selected results">
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
                    <i class="fa fa-users"></i> Select Applicants
                    <div class="pull-right">
                        <button type="button" class="btn btn-xs btn-info" id="selectAllApplicants">
                            <i class="fa fa-check-square"></i> Select All
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
                                    <th width="3%"><input type="checkbox" id="selectAllCheckbox"></th>
                                    <th>Applicant Name</th>
                                    <th>Municipality</th>
                                    <th>School</th>
                                    <th>Course</th>
                                    <th>Current Status</th>
                                    <th>Exam Score</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                global $mydb;

                                // Get applicants who can take exams (not scholars, not terminated)
                                $sql = "
                                    SELECT
                                        a.*,
                                        er.TOTAL_SCORE as EXISTING_SCORE,
                                        er.PASSING_SCORE as EXISTING_PASSING,
                                        er.EXAM_DATE as EXISTING_DATE,
                                        er.REMARKS as EXISTING_REMARKS,
                                        CASE
                                            WHEN er.EXAM_RESULT_ID IS NOT NULL THEN 'Has Result'
                                            ELSE 'No Result'
                                        END as RESULT_STATUS
                                    FROM tbl_applicants a
                                    LEFT JOIN tbl_exam_results er ON a.APPLICANTID = er.APPLICANTID
                                    WHERE a.STATUS NOT IN ('Scholar', 'Graduated', 'Terminated')
                                    AND a.REQUIREMENT_STATUS = 'Complete'
                                    ORDER BY a.LASTNAME, a.FIRSTNAME
                                ";

                                $mydb->setQuery($sql);
                                $mydb->executeQuery();
                                $applicants = $mydb->loadResultList();

                                foreach ($applicants as $a):
                                    $full_name = htmlspecialchars($a->LASTNAME . ', ' . $a->FIRSTNAME . ' ' . ($a->MIDDLENAME ?? ''));
                                    $has_existing = $a->EXISTING_SCORE !== null;
                                ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_applicants[]" value="<?= $a->APPLICANTID ?>"
                                               class="applicant-checkbox">
                                    </td>
                                    <td>
                                        <strong><?= $full_name ?></strong>
                                        <?php if($a->IS_4PS_BENEFICIARY == 'Yes'): ?>
                                            <span class="label label-success" style="font-size: 10px;">4Ps</span>
                                        <?php endif; ?>
                                        <?php if($a->IS_INDIGENOUS == 'Yes'): ?>
                                            <span class="label label-info" style="font-size: 10px;">IP</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($a->MUNICIPALITY ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($a->SCHOOL ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($a->COURSE ?? 'N/A') ?></td>
                                    <td>
                                        <?php if($has_existing): ?>
                                            <span class="label label-success">Has Result (<?= $a->EXISTING_SCORE ?>%)</span>
                                        <?php else: ?>
                                            <span class="label label-default">No Result</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input type="number" name="scores[<?= $a->APPLICANTID ?>]"
                                               class="form-control input-sm score-input"
                                               min="0" max="100" step="0.01"
                                               placeholder="Enter score"
                                               value="<?= $has_existing ? $a->EXISTING_SCORE : '' ?>">
                                    </td>
                                    <td class="text-center">
                                        <?php if($has_existing): ?>
                                            <button type="button" class="btn btn-info btn-xs view-existing"
                                                    data-id="<?= $a->APPLICANTID ?>"
                                                    data-score="<?= $a->EXISTING_SCORE ?>"
                                                    data-passing="<?= $a->EXISTING_PASSING ?>"
                                                    data-date="<?= $a->EXISTING_DATE ?>"
                                                    data-remarks="<?= htmlspecialchars($a->EXISTING_REMARKS ?? '') ?>">
                                                <i class="fa fa-eye"></i> View
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                                <?php if(empty($applicants)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="alert alert-warning" style="margin: 20px;">
                                            <i class="fa fa-warning"></i> No eligible applicants found.
                                            <br>Applicants must have complete requirements and not be scholars or terminated.
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
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-body text-center">
                    <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                        <i class="fa fa-save"></i> Save Exam Results
                    </button>
                    <a href="index.php?view=results" class="btn btn-default btn-lg">
                        <i class="fa fa-arrow-left"></i> Back to Results
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- View Existing Result Modal -->
<div class="modal fade" id="viewResultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-eye"></i> Existing Exam Result</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Score:</strong> <span id="modal-score"></span></p>
                        <p><strong>Passing Score:</strong> <span id="modal-passing"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Exam Date:</strong> <span id="modal-date"></span></p>
                        <p><strong>Remarks:</strong> <span id="modal-remarks"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo web_root;?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script>
$(document).ready(function() {
    $('#applicant-table').DataTable({
        "pageLength": 25,
        "order": [[1, "asc"]],
        "columnDefs": [
            { "orderable": false, "targets": [0, 6, 7] }
        ]
    });

    // Handle select all checkbox
    $('#selectAllCheckbox').on('change', function() {
        $('.applicant-checkbox').prop('checked', $(this).prop('checked'));
        updateSubmitButton();
    });

    // Handle individual checkboxes
    $(document).on('change', '.applicant-checkbox', function() {
        updateSubmitButton();

        // Update select all checkbox state
        var totalCheckboxes = $('.applicant-checkbox').length;
        var checkedCheckboxes = $('.applicant-checkbox:checked').length;
        $('#selectAllCheckbox').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
    });

    // Select All button
    $('#selectAllApplicants').on('click', function() {
        $('.applicant-checkbox').prop('checked', true);
        $('#selectAllCheckbox').prop('checked', true);
        updateSubmitButton();
    });

    // Clear All button
    $('#clearAllApplicants').on('click', function() {
        $('.applicant-checkbox').prop('checked', false);
        $('#selectAllCheckbox').prop('checked', false);
        updateSubmitButton();
    });

    // View existing result
    $('.view-existing').on('click', function() {
        var score = $(this).data('score');
        var passing = $(this).data('passing');
        var date = $(this).data('date');
        var remarks = $(this).data('remarks');

        $('#modal-score').text(score + '%');
        $('#modal-passing').text(passing + '%');
        $('#modal-date').text(new Date(date).toLocaleString());
        $('#modal-remarks').text(remarks || 'None');

        $('#viewResultModal').modal('show');
    });

    // Form validation
    $('#batchForm').on('submit', function(e) {
        var selectedCount = $('.applicant-checkbox:checked').length;
        if (selectedCount === 0) {
            e.preventDefault();
            alert('Please select at least one applicant.');
            return false;
        }

        return confirm('Are you sure you want to save exam results for ' + selectedCount + ' applicant(s)? Note: Leave score fields empty if you don\'t want to update existing results.');
    });
});

function updateSubmitButton() {
    var selectedCount = $('.applicant-checkbox:checked').length;
    $('#submitBtn').prop('disabled', selectedCount === 0);

    if (selectedCount > 0) {
        $('#submitBtn').html('<i class="fa fa-save"></i> Save Results (' + selectedCount + ')');
    } else {
        $('#submitBtn').html('<i class="fa fa-save"></i> Save Exam Results');
    }
}
</script>