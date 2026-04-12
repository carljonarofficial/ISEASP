<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}
?>

<!-- Summary Stats -->
<!-- <div class="row">
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <?php
                global $mydb;
                $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_exam_results");
                $mydb->executeQuery();
                $total = $mydb->loadSingleResult();
                ?>
                <h3><?= $total->total ?? 0 ?></h3>
                <p>Total Exam Taken</p>
            </div>
            <div class="icon">
                <i class="fa fa-pencil"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <?php
                $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_exam_results WHERE TOTAL_SCORE >= PASSING_SCORE");
                $mydb->executeQuery();
                $passed = $mydb->loadSingleResult();
                ?>
                <h3><?= $passed->total ?? 0 ?></h3>
                <p>Passed</p>
            </div>
            <div class="icon">
                <i class="fa fa-check-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
            <div class="inner">
                <?php
                $mydb->setQuery("SELECT COUNT(*) as total FROM tbl_exam_results WHERE TOTAL_SCORE < PASSING_SCORE");
                $mydb->executeQuery();
                $failed = $mydb->loadSingleResult();
                ?>
                <h3><?= $failed->total ?? 0 ?></h3>
                <p>Failed</p>
            </div>
            <div class="icon">
                <i class="fa fa-times-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner">
                <?php
                $mydb->setQuery("SELECT AVG(TOTAL_SCORE) as average FROM tbl_exam_results");
                $mydb->executeQuery();
                $avg = $mydb->loadSingleResult();
                ?>
                <h3><?= round($avg->average ?? 0) ?>%</h3>
                <p>Average Score</p>
            </div>
            <div class="icon">
                <i class="fa fa-line-chart"></i>
            </div>
        </div>
    </div>
</div> -->

<!-- Filter Section -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-filter"></i> Filter Results
            </div>
            <div class="panel-body">
                <form method="GET" action="index.php" class="form-inline">
                    <input type="hidden" name="view" value="results">
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>Result:</label>
                        <select name="result" class="form-control input-sm">
                            <option value="">All Results</option>
                            <option value="passed" <?= isset($_GET['result']) && $_GET['result'] == 'passed' ? 'selected' : '' ?>>Passed</option>
                            <option value="failed" <?= isset($_GET['result']) && $_GET['result'] == 'failed' ? 'selected' : '' ?>>Failed</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>Date From:</label>
                        <input type="date" name="date_from" class="form-control input-sm" 
                               value="<?= isset($_GET['date_from']) ? $_GET['date_from'] : '' ?>">
                    </div>
                    
                    <div class="form-group" style="margin-right: 10px;">
                        <label>Date To:</label>
                        <input type="date" name="date_to" class="form-control input-sm" 
                               value="<?= isset($_GET['date_to']) ? $_GET['date_to'] : '' ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-search"></i> Apply Filter
                    </button>
                    <a href="index.php?view=results" class="btn btn-default btn-sm">
                        <i class="fa fa-refresh"></i> Reset
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Batch Actions Panel -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default" style="margin-bottom: 20px;">
            <div class="panel-heading">
                <i class="fa fa-cogs"></i> Batch Actions
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-lg-12">
                        <!-- <a href="index.php?view=batch_add" class="btn btn-sm btn-primary">
                            <i class="fa fa-plus"></i> Batch Add/Edit
                        </a> -->
                        <button type="button" class="btn btn-sm btn-primary" id="batchAddEditBtn" disabled>
                            Batch Add/Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-success" id="batchExportBtn" disabled>
                            <i class="fa fa-download"></i> Export
                        </button>
                        <button type="button" class="btn btn-smbtn-warning" id="batchPrintBtn" disabled>
                            <i class="fa fa-print"></i> Print
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" id="batchDeleteBtn" disabled onclick="confirmBatchDelete()">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                        <!-- <a href="#" onclick="window.print()" class="btn btn-default">
                            <i class="fa fa-print"></i> Print Results
                        </a> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table id="dash-table" class="table table-striped table-bordered table-hover" style="font-size:13px">
        <thead>
            <tr>
                <th width="3%"><input type="checkbox" id="selectAllCheckbox"></th>
                <th>Exam Date</th>
                <th>Applicant Name</th>
                <th>Municipality</th>
                <th>School</th>
                <th>Course</th>
                <th>Total Score</th>
                <th>Passing Score</th>
                <th>Result</th>
                <th>Examined By</th>
                <th width="10%">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Build query with filters
            $where = array();
            
            if (isset($_GET['result']) && $_GET['result'] == 'passed') {
                $where[] = "er.TOTAL_SCORE >= er.PASSING_SCORE";
            } elseif (isset($_GET['result']) && $_GET['result'] == 'failed') {
                $where[] = "er.TOTAL_SCORE < er.PASSING_SCORE";
            }
            
            if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
                $date_from = $mydb->escape_value($_GET['date_from']);
                $where[] = "DATE(er.EXAM_DATE) >= '$date_from'";
            }
            
            if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
                $date_to = $mydb->escape_value($_GET['date_to']);
                $where[] = "DATE(er.EXAM_DATE) <= '$date_to'";
            }
            $where[] = "a.STATUS != 'Scholar'"; // Exclude scholars
            
            $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            
            $sql = "
                SELECT 
                    er.*,
                    a.LASTNAME, a.FIRSTNAME, a.MIDDLENAME,
                    a.MUNICIPALITY, a.SCHOOL, a.COURSE,
                    u.FULLNAME as EXAMINER_NAME
                FROM tbl_exam_results er
                INNER JOIN tbl_applicants a ON er.APPLICANTID = a.APPLICANTID
                LEFT JOIN tblusers u ON er.EXAMINER_ID = u.USERID
                $where_clause
                ORDER BY er.EXAM_DATE DESC
            ";
            
            $mydb->setQuery($sql);
            $mydb->executeQuery();
            $results = $mydb->loadResultList();
            
            foreach ($results as $r):
                if($r->TOTAL_SCORE === null) {
                    $result_label = '<span class="label label-default">PENDING</span>';
                } else {
                    $result_label = ($r->TOTAL_SCORE >= $r->PASSING_SCORE) ? 
                        '<span class="label label-success">PASSED</span>' : 
                        '<span class="label label-danger">FAILED</span>';
                }
            ?>
            <tr>
                <td><input type="checkbox" class="result-checkbox" value="<?= $r->EXAM_RESULT_ID ?>"></td>
                <td><?= date('M d, Y', strtotime($r->EXAM_DATE)) ?></td>
                <td><?= htmlspecialchars($r->LASTNAME . ', ' . $r->FIRSTNAME . ' ' . ($r->MIDDLENAME ?? '')) ?></td>
                <td><?= htmlspecialchars($r->MUNICIPALITY ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($r->SCHOOL ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($r->COURSE ?? 'N/A') ?></td>
                <td><strong><?= ($r->TOTAL_SCORE === null) ? "Pending" : $r->TOTAL_SCORE."%" ?></strong></td>
                <td><?= $r->PASSING_SCORE ?>%</td>
                <td><?= $result_label ?></td>
                <td><?= htmlspecialchars($r->EXAMINER_NAME ?? 'N/A') ?></td>
                <td class="text-center">
                    <a href="index.php?view=view&id=<?= $r->EXAM_RESULT_ID ?>" 
                       class="btn btn-info btn-xs" title="View Details">
                        <i class="fa fa-eye"></i>
                    </a>
                    <a href="index.php?view=edit&id=<?= $r->EXAM_RESULT_ID ?>" 
                       class="btn btn-primary btn-xs" title="Edit">
                        <i class="fa fa-edit"></i>
                    </a>
                    <a href="controller.php?action=delete&id=<?= $r->EXAM_RESULT_ID ?>" 
                       class="btn btn-danger btn-xs" 
                       onclick="return confirm('Delete this exam result?')"
                       title="Delete">
                        <i class="fa fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($results)): ?>
            <tr>
                <td colspan="11" class="text-center">
                    <div class="alert alert-info" style="margin: 20px;">
                        <i class="fa fa-info-circle"></i> No exam results found.
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
    $('#dash-table').DataTable({
        "pageLength": 25,
        "order": [[1, "desc"]], // Changed to column 1 since checkbox is now column 0
        "columnDefs": [
            { "orderable": false, "targets": 0 } // Make checkbox column non-sortable
        ]
    });

    // Handle select all checkbox
    $('#selectAllCheckbox').on('change', function() {
        $('.result-checkbox').prop('checked', $(this).prop('checked'));
        updateBatchButtons();
    });
    
    // Handle individual checkboxes
    $(document).on('change', '.result-checkbox', function() {
        updateBatchButtons();
        
        // Update select all checkbox state
        var totalCheckboxes = $('.result-checkbox').length;
        var checkedCheckboxes = $('.result-checkbox:checked').length;
        $('#selectAllCheckbox').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
    });
    
    // Select All button
    $('#selectAllBtn').on('click', function() {
        var allChecked = $('.result-checkbox:checked').length === $('.result-checkbox').length;
        $('.result-checkbox').prop('checked', !allChecked);
        $('#selectAllCheckbox').prop('checked', !allChecked);
        updateBatchButtons();
    });

    // Batch add or edit
    $('#batchAddEditBtn').on('click', function() {
        var selectedIds = getSelectedIds();
        if (selectedIds.length > 0) {
            var url = 'index.php?view=batch_add&ids=' + selectedIds.join(',');
            window.location.href = url;
        }
    });
    
    // Batch export
    $('#batchExportBtn').on('click', function() {
        var selectedIds = getSelectedIds();
        if (selectedIds.length > 0) {
            var url = 'controller.php?action=batch_export&ids=' + selectedIds.join(',');
            window.open(url, '_blank');
        }
    });
    
    // Batch print
    $('#batchPrintBtn').on('click', function() {
        var selectedIds = getSelectedIds();
        if (selectedIds.length > 0) {
            var url = 'controller.php?action=batch_print&ids=' + selectedIds.join(',');
            window.open(url, '_blank');
        }
    });
});

function getSelectedIds() {
    var selectedIds = [];
    $('.result-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });
    return selectedIds;
}

function updateBatchButtons() {
    var selectedCount = $('.result-checkbox:checked').length;
    $('#batchAddEditBtn, #batchExportBtn, #batchPrintBtn, #batchDeleteBtn').prop('disabled', selectedCount === 0);
    
    // Update button text to show count
    if (selectedCount > 0) {
        $('#batchAddEditBtn').html('<i class="fa fa-edit"></i> Add/Edit (' + selectedCount + ')');
        $('#batchExportBtn').html('<i class="fa fa-download"></i> Export (' + selectedCount + ')');
        $('#batchPrintBtn').html('<i class="fa fa-print"></i> Print (' + selectedCount + ')');
        $('#batchDeleteBtn').html('<i class="fa fa-trash"></i> Delete (' + selectedCount + ')');
    } else {
        $('#batchAddEditBtn').html('<i class="fa fa-edit"></i> Add/Edit');
        $('#batchExportBtn').html('<i class="fa fa-download"></i> Export');
        $('#batchPrintBtn').html('<i class="fa fa-print"></i> Print');
        $('#batchDeleteBtn').html('<i class="fa fa-trash"></i> Delete');
    }
}

function confirmBatchDelete() {
    var selectedCount = $('.result-checkbox:checked').length;
    if (selectedCount === 0) {
        alert('Please select exam results to delete.');
        return;
    }
    
    if (confirm('Are you sure you want to delete ' + selectedCount + ' exam result(s)? This action cannot be undone.')) {
        var selectedIds = getSelectedIds();
        var form = $('<form action="controller.php?action=batch_delete" method="POST"></form>');
        for (var i = 0; i < selectedIds.length; i++) {
            form.append('<input type="hidden" name="ids[]" value="' + selectedIds[i] + '">');
        }
        $('body').append(form);
        form.submit();
    }
}
</script>