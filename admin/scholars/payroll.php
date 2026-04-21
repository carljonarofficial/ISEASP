<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

global $mydb;

// Get active school year
function getActiveSchoolYear() {
    global $mydb;
    $mydb->setQuery("SELECT * FROM tbl_school_years WHERE is_active = 1 LIMIT 1");
    $mydb->executeQuery();
    $result = $mydb->loadSingleResult();
    return $result;
}

function getSchoolYears() {
    global $mydb;
    $mydb->setQuery("SELECT * FROM tbl_school_years ORDER BY school_year DESC");
    $mydb->executeQuery();
    return $mydb->loadResultList();
}

function getSYById($id) {
    global $mydb;
    $mydb->setQuery("SELECT * FROM tbl_school_years WHERE id = '$id'");
    $mydb->executeQuery();
    return $mydb->loadSingleResult();
}

// Get renewed scholars count for a specific school year
function getRenewedScholarsCount($sy_id) {
    global $mydb;
    if(!$sy_id) return 0;
    $mydb->setQuery("SELECT COUNT(*) as count FROM tbl_scholar_renewals 
                     WHERE school_year_id = '$sy_id' AND status = 'approved'");
    $mydb->executeQuery();
    $result = $mydb->loadSingleResult();
    return $result ? $result->count : 0;
}

function getTotalDisbursed($sy_id) {
    global $mydb;
    if(!$sy_id) return 0;
    $mydb->setQuery("SELECT SUM(total_amount) as total FROM tbl_payroll 
                     WHERE school_year_id = '$sy_id' AND status = 'disbursed'");
    $mydb->executeQuery();
    $result = $mydb->loadSingleResult();
    return number_format($result->total ?? 0, 2);
}

function getPendingPayrollCount($sy_id) {
    global $mydb;
    if(!$sy_id) return 0;
    $mydb->setQuery("SELECT COUNT(*) as count FROM tbl_payroll 
                     WHERE school_year_id = '$sy_id' AND status = 'pending'");
    $mydb->executeQuery();
    $result = $mydb->loadSingleResult();
    return $result->count ?? 0;
}

function getPayrollList($sy_id) {
    global $mydb;
    if(!$sy_id) return [];
    $mydb->setQuery("
        SELECT p.*, sy.school_year,
            (SELECT COUNT(*) FROM tbl_payroll_details WHERE payroll_id = p.id) as scholar_count 
        FROM tbl_payroll p 
        LEFT JOIN tbl_school_years sy ON p.school_year_id = sy.id
        WHERE p.school_year_id = '$sy_id'
        ORDER BY p.payment_date DESC
    ");
    $mydb->executeQuery();
    $payrolls = $mydb->loadResultList();
    return $payrolls ? $payrolls : [];
}

$active_sy = getActiveSchoolYear();
$all_sy = getSchoolYears();
$selected_sy_id = isset($_GET['school_year_id']) ? intval($_GET['school_year_id']) : ($active_sy ? $active_sy->id : 0);
$selected_sy = getSYById($selected_sy_id);

$renewed_count = getRenewedScholarsCount($selected_sy_id);
$total_disbursed = getTotalDisbursed($selected_sy_id);
$pending_count = getPendingPayrollCount($selected_sy_id);
$payrolls = getPayrollList($selected_sy_id);
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Payroll Management - <?php echo $selected_sy ? $selected_sy->school_year : 'No School Year Selected'; ?></h1>
    </div>
</div>

<!-- Filter Section -->
<div class="row" style="margin-bottom: 20px;">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <form method="GET" action="index.php" class="form-inline">
                    <input type="hidden" name="view" value="payroll">
                    <div class="form-group">
                        <label>Select School Year: </label>
                        <select name="school_year_id" class="form-control" onchange="this.form.submit()">
                            <?php foreach($all_sy as $sy): ?>
                            <option value="<?php echo $sy->id; ?>" <?php echo $selected_sy_id == $sy->id ? 'selected' : ''; ?>>
                                <?php echo $sy->school_year; ?> <?php echo $sy->is_active ? '(Active)' : ''; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row" style="margin-bottom: 20px;">
    <div class="col-md-3">
        <div class="small-box bg-green">
            <div class="inner">
                <h3>₱ <?php echo $total_disbursed; ?></h3>
                <p>Total Disbursed</p>
                <small>For <?php echo $selected_sy ? $selected_sy->school_year : 'N/A'; ?></small>
            </div>
            <div class="icon">
                <i class="fa fa-money"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3><?php echo $renewed_count; ?></h3>
                <p>Renewed Scholars</p>
                <small>For <?php echo $selected_sy ? $selected_sy->school_year : 'N/A'; ?></small>
            </div>
            <div class="icon">
                <i class="fa fa-users"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3><?php echo $pending_count; ?></h3>
                <p>Pending Payrolls</p>
                <small>Awaiting approval</small>
            </div>
            <div class="icon">
                <i class="fa fa-clock-o"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="small-box bg-red">
            <div class="inner">
                <h3><?php echo count($payrolls); ?></h3>
                <p>Total Payrolls</p>
                <small>Generated</small>
            </div>
            <div class="icon">
                <i class="fa fa-file-text-o"></i>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row">
    <div class="col-lg-12" style="margin-bottom: 10px;">
        <?php if($selected_sy && $renewed_count > 0): ?>
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#generatePayrollModal">
            <i class="fa fa-plus"></i> Generate School Year Payroll
        </button>
        <?php endif; ?>
        <!-- <a href="index.php?view=payroll_reports" class="btn btn-info">
            <i class="fa fa-bar-chart"></i> Payroll Reports
        </a> -->
        <a href="index.php?view=disbursement" class="btn btn-default">
            <i class="fa fa-credit-card"></i> Disbursement Records
        </a>
    </div>
</div>

<!-- Alert Messages -->
<?php if(!$selected_sy): ?>
<div class="alert alert-warning">
    <i class="fa fa-warning"></i> No school year found. Please add school years in System Settings.
</div>
<?php elseif($renewed_count == 0): ?>
<div class="alert alert-info">
    <i class="fa fa-info-circle"></i> No renewed scholars for <?php echo $selected_sy->school_year; ?>. 
    Please renew scholars first before generating payroll.
    <a href="../applications/index.php?stage=scholar" class="alert-link">Manage Scholars</a>
</div>
<?php endif; ?>

<!-- Payroll List -->
<div class="panel panel-success">
    <div class="panel-heading">
        <i class="fa fa-list"></i> Payroll Batches - <?php echo $selected_sy ? $selected_sy->school_year : 'No School Year Selected'; ?>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table id="payroll-table" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Payment Date</th>
                        <th>Semester</th>
                        <th>Total Scholars</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Generated By</th>
                        <th width="20%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payrolls as $row): 
                        $status_class = '';
                        $status_text = '';
                        switch($row->status) {
                            case 'pending':
                                $status_class = 'warning';
                                $status_text = 'Pending';
                                break;
                            case 'approved':
                                $status_class = 'info';
                                $status_text = 'Approved';
                                break;
                            case 'disbursed':
                                $status_class = 'success';
                                $status_text = 'Disbursed';
                                break;
                            case 'cancelled':
                                $status_class = 'danger';
                                $status_text = 'Cancelled';
                                break;
                        }
                        
                        // Get creator name
                        $mydb->setQuery("SELECT FULLNAME FROM tblusers WHERE USERID = '{$row->created_by}'");
                        $mydb->executeQuery();
                        $creator = $mydb->loadSingleResult();
                    ?>
                    <tr>
                        <td><?php echo date('F d, Y', strtotime($row->payment_date)); ?></td>
                        <td><?php echo $row->semester; ?></td>
                        <td><?php echo $row->scholar_count; ?></td>
                        <td>₱ <?php echo number_format($row->total_amount, 2); ?></td>
                        <td><span class="label label-<?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                        <td><?php echo $creator ? $creator->FULLNAME : 'N/A'; ?></td>
                        <td class="text-center">
                            <a href="index.php?view=payroll_details&id=<?php echo $row->id; ?>" class="btn btn-info btn-xs">
                                <i class="fa fa-eye"></i> View
                            </a>
                            <?php if($row->status == 'pending' && ($_SESSION['ADMIN_ROLE'] == 'Super Admin' || $_SESSION['ADMIN_ROLE'] == 'Admin')): ?>
                            <a href="#" onclick="approvePayroll(<?php echo $row->id; ?>)" class="btn btn-success btn-xs">
                                <i class="fa fa-check"></i> Approve
                            </a>
                            <?php endif; ?>
                            <?php if($row->status == 'pending'): ?>
                            <a href="controller.php?action=sync_payroll_scholars&id=<?php echo $row->id; ?>" class="btn btn-warning btn-xs" onclick="return confirm('Search for missing renewed scholars and add them to this batch?')">
                                <i class="fa fa-refresh"></i> Sync
                            </a>
                            <?php endif; ?>
                            <?php if($row->status == 'approved' && ($_SESSION['ADMIN_ROLE'] == 'Super Admin' || $_SESSION['ADMIN_ROLE'] == 'Admin')): ?>
                            <a href="#" onclick="disbursePayroll(<?php echo $row->id; ?>)" class="btn btn-primary btn-xs">
                                <i class="fa fa-money"></i> Disburse
                            </a>
                            <?php endif; ?>
                            <a href="index.php?view=print_payroll&id=<?php echo $row->id; ?>" class="btn btn-default btn-xs" target="_blank">
                                <i class="fa fa-print"></i> Print
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($payrolls)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No payroll records found for <?php echo $selected_sy ? $selected_sy->school_year : 'selected school year'; ?>.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Generate Payroll Modal -->
<div class="modal fade" id="generatePayrollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #27ae60; color: white;">
                <button type="button" class="close" data-dismiss="modal" style="color: white;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-plus"></i> Generate School Year Payroll</h4>
            </div>
            <form action="controller.php?action=generate_payroll" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>School Year</label>
                        <input type="text" class="form-control" value="<?php echo $selected_sy->school_year; ?>" readonly disabled>
                        <input type="hidden" name="school_year_id" value="<?php echo $selected_sy->id; ?>">
                    </div>
                    <div class="form-group">
                        <label>Payment/Schedule Date</label>
                        <input type="date" name="payment_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                        <small class="help-block">Date when stipends will be released/scheduled</small>
                    </div>
                    <div class="form-group">
                        <label>Semester</label>
                        <select name="semester" class="form-control" required>
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Stipend Amount per Scholar</label>
                        <input type="number" name="stipend_amount" class="form-control" required step="0.01" value="5000.00">
                        <small class="help-block">Stipend amount for this payment batch</small>
                    </div>
                    <div class="form-group">
                        <label>Remarks (Optional)</label>
                        <textarea name="remarks" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> This payroll will be generated for <strong><?php echo $renewed_count; ?></strong> renewed scholars for <?php echo $selected_sy->school_year; ?>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Generate Payroll</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#payroll-table').DataTable({
        "pageLength": 25,
        "order": [[0, "desc"]],
        "columnDefs": [
            { "orderable": false, "targets": [6] }
        ]
    });
});

function approvePayroll(id) {
    if(confirm('Approve this payroll?')) {
        window.location.href = 'controller.php?action=approve_payroll&id=' + id;
    }
}

function disbursePayroll(id) {
    var reference = prompt("Enter Reference Number:", "PAY-" + new Date().toISOString().slice(0,10).replace(/-/g,'') + "-" + id);
    if(reference) {
        var form = $('<form action="controller.php?action=disburse_payroll&id=' + id + '" method="POST">' +
            '<input type="hidden" name="reference_no" value="' + reference + '">' +
            '</form>');
        $('body').append(form);
        form.submit();
    }
}
</script>