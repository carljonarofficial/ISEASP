<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

$ids = isset($_GET['ids']) ? $_GET['ids'] : '';
if (empty($ids)) redirect("index.php");

global $mydb;
$ids_array = array_map('intval', explode(',', $ids));
$ids_str = implode(',', $ids_array);

// Fetch scholars to verify eligibility (Must be Scholars)
$mydb->setQuery("SELECT * FROM tbl_applicants WHERE APPLICANTID IN ($ids_str) AND STATUS = 'Scholar'");
$scholars = $mydb->loadResultList();

if (empty($scholars)) {
    message("No eligible scholars found in selection.", "error");
    redirect("index.php?stage=scholar");
}

// Fetch available school years for renewal
$mydb->setQuery("SELECT * FROM tbl_school_years ORDER BY school_year DESC");
$school_years = $mydb->loadResultList();
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Batch Scholar Renewal</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> This will renew the scholarship for the selected scholars for the specified school year. 
            Renewed scholars will be eligible for payroll processing in the target school year.
        </div>
        
        <form id="renewForm" method="POST">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <i class="fa fa-refresh"></i> Renewal Settings for <?php echo count($scholars); ?> Scholars
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-3">
                            <div class="form-group text-center">
                                <label>Target School Year for Renewal:</label>
                                <select name="school_year_id" class="form-control" required>
                                    <option value="">-- Select School Year --</option>
                                    <?php foreach($school_years as $sy): ?>
                                    <option value="<?= $sy->id ?>"><?= htmlspecialchars($sy->school_year) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">Selected Scholars</div>
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
                            <?php foreach ($scholars as $s): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($s->LASTNAME . ', ' . $s->FIRSTNAME); ?></td>
                                <td><?php echo htmlspecialchars($s->MUNICIPALITY); ?></td>
                                <td><?php echo htmlspecialchars($s->SCHOOL); ?></td>
                                <td><?php echo htmlspecialchars($s->COURSE); ?></td>
                                <input type="hidden" name="scholar_ids[]" value="<?php echo $s->APPLICANTID; ?>">
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" id="submitRenew" class="btn btn-success btn-lg">
                    <i class="fa fa-check"></i> Process Batch Renewal
                </button>
                <a href="index.php?stage=scholar" class="btn btn-default btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
jQuery('#renewForm').on('submit', function(e) {
    e.preventDefault();
    
    var syName = jQuery('select[name="school_year_id"] option:selected').data('name');
    jQuery('#school_year_name').val(syName);

    var formData = jQuery(this).serialize();
    var $btn = jQuery('#submitRenew');
    
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
    
    jQuery.post('../scholars/controller.php?action=renew_multiple', formData, function(response) {
        var res = JSON.parse(response);
        if(res.success) {
            alert('Successfully renewed ' + res.count + ' scholars!');
            window.location.href = 'index.php?stage=scholar';
        } else {
            alert('Error processing renewal.');
            $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Process Batch Renewal');
        }
    });
});
</script>