<?php
require_once("../../include/initialize.php");

if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

global $mydb;
?>

<!-- Filter Section -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-filter"></i> Filter Schedule
            </div>
            <div class="panel-body">
                <form method="GET" action="index.php" class="form-inline">
                    <input type="hidden" name="view" value="schedule">
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>Exam Date:</label>
                        <input type="date" name="exam_date" class="form-control input-sm" 
                               value="<?php echo isset($_GET['exam_date']) ? $_GET['exam_date'] : ''; ?>">
                    </div>
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>Venue:</label>
                        <input type="text" name="venue" class="form-control input-sm" 
                               value="<?php echo isset($_GET['venue']) ? $_GET['venue'] : ''; ?>" 
                               placeholder="Enter venue">
                    </div>
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>Show:</label>
                        <select name="show" class="form-control input-sm">
                            <option value="pending" <?= !isset($_GET['show']) || $_GET['show'] == 'pending' ? 'selected' : '' ?>>Pending Exams Only</option>
                            <option value="all" <?= isset($_GET['show']) && $_GET['show'] == 'all' ? 'selected' : '' ?>>All Exams (Including Completed)</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-search"></i> Apply Filter
                    </button>
                    <a href="index.php?view=schedule" class="btn btn-default btn-sm">
                        <i class="fa fa-refresh"></i> Reset
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Batch Actions Panel -->
<form id="batchForm" method="POST">
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default" style="margin-bottom: 20px;">
                <div class="panel-heading">
                    <i class="fa fa-cogs"></i> Batch Actions
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6 text-left">
                            <button type="button" class="btn btn-info btn-sm" id="batchPrintBtn" disabled>
                                <i class="fa fa-print"></i> Batch Print Exam Slips
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="batchRescheduleBtn" disabled>
                                <i class="fa fa-calendar"></i> Reschedule Selected
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" id="batchCancelBtn" disabled>
                                <i class="fa fa-times-circle"></i> Cancel Selected
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Notice about pending exams -->
<div class="alert alert-info bg-blue">
    <i class="fa fa-info-circle"></i> 
    <strong>Note:</strong> Only applicants with pending exams are shown by default. 
    Once an exam result is recorded, the applicant will be removed from this list.
</div>

<div class="table-responsive">
    <table id="dash-table" class="table table-striped table-bordered table-hover" style="font-size:13px">
        <thead>
                <th width="3%"><input type="checkbox" id="selectAllCheckbox" title="Select all on this page"></th>
                <th>Exam Slip #</th>
                <th>Applicant Name</th>
                <th>Municipality</th>
                <th>School</th>
                <th>Course</th>
                <th>Exam Date</th>
                <th>Exam Time</th>
                <th>Venue</th>
                <th>Status</th>
                <th width="12%">Action</th>
            </thead>
        <tbody>
            <?php
            // Build query with filters
            $where = array();
            
            // Default to showing only pending exams
            $show_all = isset($_GET['show']) && $_GET['show'] == 'all';
            
            if (!$show_all) {
                $where[] = "a.EXAM_STATUS = 'Pending'";
            }
            
            $where[] = "a.EXAM_SLIP_GENERATED IS NOT NULL";
            $where[] = "a.EXAM_SLIP_GENERATED != ''";
            
            if (isset($_GET['exam_date']) && !empty($_GET['exam_date'])) {
                $exam_date = $_GET['exam_date'];
                $where[] = "a.EXAM_DATE = '$exam_date'";
            }
            
            if (isset($_GET['venue']) && !empty($_GET['venue'])) {
                $venue = $_GET['venue'];
                $where[] = "a.EXAM_VENUE LIKE '%$venue%'";
            }
            
            $where_clause = "WHERE " . implode(" AND ", $where);
            
            $sql = "
                SELECT a.*
                FROM tbl_applicants a
                $where_clause
                ORDER BY a.EXAM_DATE ASC, a.EXAM_TIME ASC
            ";
            
            $mydb->setQuery($sql);
            $mydb->executeQuery();
            $applicants = $mydb->loadResultList();
            
            foreach ($applicants as $a):
                $status_text = $a->EXAM_STATUS;
                $status_color = 'label-warning';
                if ($status_text == 'Passed') {
                    $status_color = 'label-success';
                } elseif ($status_text == 'Failed') {
                    $status_color = 'label-danger';
                }
            ?>
             <tr>
                <td><input type="checkbox" class="batch-checkbox" value="<?= $a->APPLICANTID ?>" title="Select this applicant"></td>
                <td><?= htmlspecialchars($a->EXAM_SLIP_NUMBER ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($a->LASTNAME . ', ' . $a->FIRSTNAME . ' ' . ($a->MIDDLENAME ?? '')) ?></td>
                <td><?= htmlspecialchars($a->MUNICIPALITY ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($a->SCHOOL ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($a->COURSE ?? 'N/A') ?></td>
                <td><?= $a->EXAM_DATE ? date('M d, Y', strtotime($a->EXAM_DATE)) : 'N/A' ?></td>
                <td><?= $a->EXAM_TIME ? date('h:i A', strtotime($a->EXAM_TIME)) : 'N/A' ?></td>
                <td><?= htmlspecialchars($a->EXAM_VENUE ?? 'N/A') ?></td>
                <td><span class="label <?= $status_color ?>"><?= $status_text ?></span></td>
                <td class="text-center">
                    
                    <a href="../applications/index.php?view=print_slip&id=<?= $a->APPLICANTID ?>" 
                       class="btn btn-info btn-xs" title="Print Exam Slip" target="_blank">
                        <i class="fa fa-print"></i> Print Exam SLip
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($applicants)): ?>
            <tr>
                <td colspan="11" class="text-center">
                    <div class="alert alert-warning" style="margin: 20px;">
                        <i class="fa fa-info-circle"></i> No pending exams found.
                    </div>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="<?php echo web_root;?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#dash-table').DataTable({
        "pageLength": 25,
        "order": [[6, "asc"], [7, "asc"]],
        "columnDefs": [
            { "orderable": false, "targets": 0 },
            { "targets": "_all", "defaultContent": "" } // <--- Add this line
        ]
    });

    // Handle master checkbox
    $('#selectAllCheckbox').on('change', function() {
        $('input.batch-checkbox:visible').prop('checked', $(this).prop('checked'));
        updateBatchUI();
    });

    // Handle individual checkboxes
    $(document).on('change', 'input.batch-checkbox', function() {
        updateBatchUI();
    });

    // Select All button
    $('#selectAllBtn').on('click', function(e) {
        e.preventDefault();
        $('input.batch-checkbox:visible').prop('checked', true);
        $('#selectAllCheckbox').prop('checked', true);
        updateBatchUI();
    });

    // Clear All button
    $('#clearAllBtn').on('click', function(e) {
        e.preventDefault();
        $('input.batch-checkbox').prop('checked', false);
        $('#selectAllCheckbox').prop('checked', false);
        updateBatchUI();
    });

    // Batch Print
    $('#batchPrintBtn').on('click', function(e) {
        e.preventDefault();
        var selected = getSelectedIds();
        if(selected.length === 0) {
            alert('Please select at least one applicant');
            return;
        }
        if(confirm('Proceed to batch exam slip print page for ' + selected.length + ' examinees?')) {
            window.location.href = 'index.php?view=batch_print&ids=' + selected.join(',');
        }
    });

    // Batch Reschedule
    $('#batchRescheduleBtn').on('click', function(e) {
        e.preventDefault();
        var selected = getSelectedIds();
        if(selected.length === 0) {
            alert('Please select at least one applicant');
            return;
        }
        window.location.href = 'index.php?view=batch_reschedule&ids=' + selected.join(',');
    });

    // Batch Cancel
    $('#batchCancelBtn').on('click', function(e) {
        e.preventDefault();
        var selected = getSelectedIds();
        if(selected.length === 0) {
            alert('Please select at least one applicant');
            return;
        }
        if(confirm('Cancel exams for ' + selected.length + ' applicant(s)? This action cannot be undone.')) {
            window.location.href = 'index.php?view=batch_cancel&ids=' + selected.join(',');
        }
    });

    function getSelectedIds() {
        var ids = [];
        $('input.batch-checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        return ids;
    }

    function updateBatchUI() {
        var count = $('input.batch-checkbox:checked').length;
        $('#selectedCount').text(count);
        
        var disabled = count === 0;
        $('#batchPrintBtn').prop('disabled', disabled);
        $('#batchRescheduleBtn').prop('disabled', disabled);
        $('#batchCancelBtn').prop('disabled', disabled);
    }
});
</script>