<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    redirect("index.php");
}

global $mydb;

// Get applicant details
$mydb->setQuery("SELECT * FROM tbl_applicants WHERE APPLICANTID = $id");
$mydb->executeQuery();
$applicant = $mydb->loadSingleResult();

if (!$applicant) {
    message("Applicant not found!", "error");
    redirect("index.php");
}

// Handle form submission
if (isset($_POST['update_checklist'])) {
    // Get all requirements to validate required ones
    $mydb->setQuery("SELECT REQUIREMENT_ID, REQUIREMENT_NAME, REQUIRED FROM tbl_requirement");
    $mydb->executeQuery();
    $requirements = $mydb->loadResultList();

    // Count the total required requirements for the applicant
    $total_required = 0;
    foreach ($requirements as $req) {
        if ($req->REQUIRED == 'Yes') {
            $total_required++;
        }
    }

    // Count how many required requirements are submitted
    $submitted_required = 0;

    // Get Total requirements for validation
    foreach ($_POST['requirements'] as $req_id => $values) {
        $is_submitted = isset($values['submitted']) ? 1 : 0;
        $is_verified = isset($values['verified']) ? 1 : 0;
        $remarks = trim($values['remarks']);

        // Check if is submitted or not the required requirements
        if ($is_submitted) {
            $submitted_required++;
        }

        // Update or insert checklist record
        $sql = "UPDATE tbl_applicant_requirement_checklist SET 
                IS_SUBMITTED = $is_submitted,
                IS_VERIFIED = $is_verified,
                REMARKS = '$remarks',
                VERIFIED_BY = " . ($is_verified ? $_SESSION['ADMIN_USERID'] : "NULL") . ",
                VERIFIED_DATE = " . ($is_verified ? "NOW()" : "NULL") . "
                WHERE APPLICANTID = $id AND REQUIREMENT_ID = $req_id";
        
        $mydb->setQuery($sql);
        $mydb->executeQuery();
    }

    // Check if applicant's all required requirements are submitted
    $requirement_status = 'Pending';
    if ($submitted_required == $total_required) {
        $requirement_status = 'Complete';
    } else {
        $requirement_status = 'Incomplete';
    }

    // Update the applicant's requirement status
    $update_sql = "UPDATE tbl_applicants SET REQUIREMENT_STATUS = '$requirement_status' WHERE APPLICANTID = $id";
    $mydb->setQuery($update_sql);
    $mydb->executeQuery();
    
    // Log the action
    $log_sql = "INSERT INTO tbl_application_log 
            (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS)
            SELECT 
                $id, 
                " . $_SESSION['ADMIN_USERID'] . ", 
                USERNAME, 
                ROLE, 
                'Requirements checklist updated',
                'REQUIREMENT',
                'Requirements checklist updated'
            FROM tblusers 
            WHERE USERID = " . $_SESSION['ADMIN_USERID'];
    $mydb->setQuery($log_sql);
    $mydb->executeQuery();
    
    message("Requirements checklist updated successfully!", "success");
    redirect("index.php?view=view&id=$id");
}

// Get requirements with current status
$sql = "
    SELECT 
        r.*,
        COALESCE(c.IS_SUBMITTED, 0) as IS_SUBMITTED,
        COALESCE(c.IS_VERIFIED, 0) as IS_VERIFIED,
        c.REMARKS
    FROM tbl_requirement r
    LEFT JOIN tbl_applicant_requirement_checklist c 
        ON r.REQUIREMENT_ID = c.REQUIREMENT_ID AND c.APPLICANTID = $id
    ORDER BY r.CATEGORY, r.DISPLAY_ORDER
";

$mydb->setQuery($sql);
$mydb->executeQuery();
$requirements = $mydb->loadResultList();
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Manage Requirements - <?= htmlspecialchars($applicant->LASTNAME . ', ' . $applicant->FIRSTNAME) ?></h1>
    </div>
</div>

<!-- Applicant Info Summary -->
<div class="row" style="margin-bottom: 15px;">
    <div class="col-lg-12">
        <div class="alert" style="background-color: #337ab7; color: #fff">
            <!-- <strong>Applicant:</strong> <?= htmlspecialchars($applicant->LASTNAME . ', ' . $applicant->FIRSTNAME . ' ' . ($applicant->MIDDLENAME ?? '')) ?> |  -->
            <strong>School:</strong> <?= htmlspecialchars($applicant->SCHOOL) ?> | 
            <strong>Course:</strong> <?= htmlspecialchars($applicant->COURSE) ?> | 
            <strong>Municipality:</strong> <?= htmlspecialchars($applicant->MUNICIPALITY) ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <i class="fa fa-check-square-o"></i> Requirements Checklist
                <div class="pull-right">
                    <a href="index.php?view=view&id=<?= $id ?>" class="btn btn-default btn-xs">
                        <i class="fa fa-eye"></i> View Only
                    </a>
                    <a href="index.php" class="btn btn-default btn-xs">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="panel-body">
                <form method="POST" action="">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Requirement</th>
                                    <th>Required</th>
                                    <th class="text-center" width="8%">Submitted</th>
                                    <th class="text-center" width="8%">Verified</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $current_category = '';
                                foreach ($requirements as $req): 
                                    if ($current_category != $req->CATEGORY):
                                        $current_category = $req->CATEGORY;
                                ?>
                                <tr class="active">
                                    <td colspan="4"><strong><?= $current_category ?></strong></td>
                                    <td class="text-center"><input type="checkbox" id="checkall_<?= $req->CATEGORY ?>" onchange="checkAllCategoryRequirements('<?= $req->CATEGORY ?>')"></td>
                                    <td></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td></td>
                                    <td>
                                        <?= htmlspecialchars($req->REQUIREMENT_NAME) ?>
                                        <?php if (!empty($req->DESCRIPTION)): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($req->DESCRIPTION) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($req->REQUIRED == 'Yes'): ?>
                                            <span class="label label-danger">Required</span>
                                        <?php else: ?>
                                            <span class="label label-default">Optional</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" name="requirements[<?= $req->REQUIREMENT_ID ?>][submitted]" 
                                               value="1" <?= $req->IS_SUBMITTED ? 'checked' : '' ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" name="requirements[<?= $req->REQUIREMENT_ID ?>][verified]" 
                                               value="1" class="category_<?= $req->CATEGORY ?>" <?= $req->IS_VERIFIED ? 'checked' : '' ?>>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control input-sm" 
                                               name="requirements[<?= $req->REQUIREMENT_ID ?>][remarks]" 
                                               value="<?= htmlspecialchars($req->REMARKS ?? '') ?>" 
                                               placeholder="Optional remarks">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="form-group" style="margin-top: 20px;">
                        <button type="submit" name="update_checklist" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update Checklist
                        </button>
                        <a href="index.php?view=view&id=<?= $id ?>" class="btn btn-default">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo web_root; ?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script>
// Auto-check verified when submitted is checked (optional)
$('input[name$="[submitted]"]').change(function() {
    var verifiedCheckbox = $(this).closest('tr').find('input[name$="[verified]"]');
    if ($(this).is(':checked') && !verifiedCheckbox.is(':checked')) {
        if (confirm('Mark this requirement as verified as well?')) {
            verifiedCheckbox.prop('checked', true);
        }
    }
});

// Per requirement select/deselect all in category (optional)
function checkAllCategoryRequirements(categoryName) {
    var isChecked = $('#checkall_' + categoryName).is(':checked');
    $(".category_" + categoryName).prop('checked', isChecked);
}
</script>