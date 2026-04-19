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
$mydb->setQuery("SELECT * FROM tbl_applicants WHERE APPLICANTID IN ($ids_str) AND STATUS = 'Qualified'");
$applicants = $mydb->loadResultList();

if (empty($applicants)) {
    message("No eligible applicants found in selection (must be in 'Qualified' status).", "error");
    redirect("index.php?stage=qualified");
}
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Batch Convert to Scholar</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> Set the scholarship parameters for the selected applicants. 
            This will update their status to <strong>'Scholar'</strong> and create initial award records.
        </div>

        <form method="POST" action="controller.php?action=batch_convert_to_scholar">
            <input type="hidden" name="ids" value="<?php echo $ids; ?>">
            
            <div class="panel panel-success">
                <div class="panel-heading">
                    <i class="fa fa-graduation-cap"></i> Scholarship Award Configuration for <?php echo count($applicants); ?> Applicants
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>School Year:</label>
                                <select name="school_year" class="form-control" required>
                                    <option value="2024-2025">2024-2025</option>
                                    <option value="2025-2026" selected>2025-2026</option>
                                    <option value="2026-2027">2026-2027</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Semester:</label>
                                <select name="semester" class="form-control" required>
                                    <option value="1st Semester">1st Semester</option>
                                    <option value="2nd Semester">2nd Semester</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Award Amount (₱):</label>
                                <input type="number" name="amount" class="form-control" value="10000.00" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Remarks / Notes:</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Initial conversion from qualified pool">Qualified scholar for the school year</textarea>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applicants as $a): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($a->LASTNAME . ', ' . $a->FIRSTNAME); ?>
                                    <input type="hidden" name="applicant_ids[]" value="<?php echo $a->APPLICANTID; ?>">
                                </td>
                                <td><?php echo htmlspecialchars($a->MUNICIPALITY); ?></td>
                                <td><?php echo htmlspecialchars($a->SCHOOL); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-center" style="margin-bottom: 30px;">
                <button type="submit" name="convert" class="btn btn-success btn-lg" onclick="return confirm('Convert these applicants to scholars? This action cannot be undone.')">
                    <i class="fa fa-check"></i> Confirm and Convert
                </button>
                <a href="index.php?stage=qualified" class="btn btn-default btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>