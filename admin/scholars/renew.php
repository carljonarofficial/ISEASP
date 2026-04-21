<?php
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) redirect("index.php");

global $mydb;

// Get scholar award details
$sql = "
    SELECT 
        sa.*,
        a.LASTNAME, a.FIRSTNAME, a.MIDDLENAME, a.SUFFIX,
        a.SCHOOL, a.COURSE, a.YEARLEVEL, a.GPA,
        a.MUNICIPALITY
    FROM tbl_scholarship_awards sa
    INNER JOIN tbl_applicants a ON sa.APPLICANTID = a.APPLICANTID
    WHERE sa.AWARD_ID = $id AND sa.STATUS = 'Active'
";

$mydb->setQuery($sql);
$mydb->executeQuery();
$scholar = $mydb->loadSingleResult();

if (!$scholar) {
    message("Scholar record not found or not active!", "error");
    redirect("index.php");
}


if (isset($_POST['renew'])) {
    $school_year = $_POST['school_year'];
    $semester = $_POST['semester'];
    $gpa = floatval($_POST['gpa']);
    $units = intval($_POST['units_completed']);
    $status = $_POST['renewal_status'];
    $remarks = trim($_POST['remarks']);
    
    // Check if renewal already exists
    $check_sql = "SELECT COUNT(*) as count FROM tbl_renewal_applications 
                  WHERE APPLICANTID = " . $scholar->APPLICANTID . " 
                  AND SCHOOL_YEAR = '$school_year' 
                  AND SEMESTER = '$semester'";
    
    $mydb->setQuery($check_sql);
    $mydb->executeQuery();
    $check = $mydb->loadSingleResult();
    
    if ($check->count > 0) {
        message("Renewal for this school year and semester already exists!", "error");
        redirect("index.php?view=renew&id=$id");
    }
    
    // Insert renewal application
    $renew_sql = "INSERT INTO tbl_renewal_applications 
                  (APPLICANTID, SCHOOL_YEAR, SEMESTER, PREVIOUS_GPA, UNITS_COMPLETED, STATUS, REVIEWED_BY, REVIEW_DATE, REMARKS)
                  VALUES (" . $scholar->APPLICANTID . ", '$school_year', '$semester', $gpa, $units, '$status', " . $_SESSION['ADMIN_USERID'] . ", NOW(), '$remarks')";
    
    $mydb->setQuery($renew_sql);
    $mydb->executeQuery();

    // Insert or Update Scholar Renewal Table
    $renewal_check_sql = "SELECT COUNT(*) as count FROM tbl_scholar_renewals WHERE scholar_id = " . $scholar->APPLICANTID;
    $mydb->setQuery($renewal_check_sql);
    $mydb->executeQuery();
    $renewal_check = $mydb->loadSingleResult();
    
    // This is the SQL for tbl_scholar_renewals and school year, which will be used to track the latest renewal status for each scholar
    // SELECT `id`, `scholar_id`, `school_year_id`, `renewal_date`, `status`, `approved_by`, `approved_date`, `remarks` FROM `tbl_scholar_renewals`
    if ($renewal_check->count > 0) {
        $update_sql = "UPDATE tbl_scholar_renewals SET 
                       school_year_id = (SELECT id FROM tbl_school_years WHERE school_year = '$school_year),
                          renewal_date = NOW(),
                            status = '".strtolower($status)."',
                            approved_by = " . $_SESSION['ADMIN_USERID'] . ",
                            approved_date = NOW(),
                            remarks = '$remarks'
                          WHERE scholar_id = " . $scholar->APPLICANTID;
        $mydb->setQuery($update_sql);
        $mydb->executeQuery();
    } else {
        $insert_sql = "INSERT INTO tbl_scholar_renewals 
                       (scholar_id, school_year_id, renewal_date, status, approved_by, approved_date, remarks)
                       VALUES (
                           " . $scholar->APPLICANTID . ", 
                           (SELECT id FROM tbl_school_years WHERE school_year = '$school_year'),
                           NOW(),
                           '".strtolower($status)."',
                           " . $_SESSION['ADMIN_USERID'] . ",
                           NOW(),
                           '$remarks'
                       )";
        $mydb->setQuery($insert_sql);
        $mydb->executeQuery();
    }
    
    // Update scholarship award if approved
    if ($status == 'Approved') {
        $update_sql = "UPDATE tbl_scholarship_awards SET 
                       SCHOOL_YEAR = '$school_year',
                       SEMESTER = '$semester'
                       WHERE AWARD_ID = $id";
        
        $mydb->setQuery($update_sql);
        $mydb->executeQuery();
        
        // Update applicant's year level
        $new_year = '';
        switch ($scholar->YEARLEVEL) {
            case '1st Year': $new_year = '2nd Year'; break;
            case '2nd Year': $new_year = '3rd Year'; break;
            case '3rd Year': $new_year = '4th Year'; break;
            case '4th Year': $new_year = '5th Year'; break;
            default: $new_year = $scholar->YEARLEVEL;
        }
        
        $year_sql = "UPDATE tbl_applicants SET YEARLEVEL = '$new_year', GPA = $gpa WHERE APPLICANTID = " . $scholar->APPLICANTID;
        $mydb->setQuery($year_sql);
        $mydb->executeQuery();
    }
    
    // Insert into scholarship history
    $history_sql = "INSERT INTO tbl_scholarship_history 
                    (APPLICANTID, SCHOOL_YEAR, SEMESTER, STATUS, GPA, REMARKS, UPDATED_BY)
                    VALUES (" . $scholar->APPLICANTID . ", '$school_year', '$semester', 'Renewed', $gpa, 'Renewal $status', " . $_SESSION['ADMIN_USERID'] . ")";
    
    $mydb->setQuery($history_sql);
    $mydb->executeQuery();
    
    // FIXED: Log the action with new table structure
    $log_sql = "INSERT INTO tbl_application_log 
                (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS)
                SELECT 
                    " . $scholar->APPLICANTID . ", 
                    " . $_SESSION['ADMIN_USERID'] . ", 
                    USERNAME, 
                    ROLE, 
                    CONCAT('Scholarship renewed: ', '$status'),
                    'SCHOLAR',
                    CONCAT('Renewal for school year: ', '$school_year', ', Semester: ', '$semester', ', GPA: ', $gpa)
                FROM tblusers 
                WHERE USERID = " . $_SESSION['ADMIN_USERID'];
    $mydb->setQuery($log_sql);
    $mydb->executeQuery();
    
    message("Renewal processed successfully!", "success");
    redirect("index.php?view=view&id=$id");
}
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Process Scholarship Renewal</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-warning">
            <div class="panel-heading">
                <i class="fa fa-refresh"></i> Renewal for: <?= htmlspecialchars($scholar->LASTNAME . ', ' . $scholar->FIRSTNAME . ' ' . ($scholar->MIDDLENAME ?? '')) ?>
            </div>
            <div class="panel-body">
                
                <!-- Scholar Information -->
                <div class="well">
                    <h4>Current Scholarship Information</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">School:</th>
                            <td><?= htmlspecialchars($scholar->SCHOOL) ?></td>
                        </tr>
                        <tr>
                            <th>Course:</th>
                            <td><?= htmlspecialchars($scholar->COURSE) ?></td>
                        </tr>
                        <tr>
                            <th>Current Year Level:</th>
                            <td><strong><?= $scholar->YEARLEVEL ?></strong></td>
                        </tr>
                        <tr>
                            <th>Current GPA:</th>
                            <td><strong><?= $scholar->GPA ?? 'N/A' ?>%</strong></td>
                        </tr>
                        <tr>
                            <th>Current School Year:</th>
                            <td><?= $scholar->SCHOOL_YEAR ?> - <?= $scholar->SEMESTER ?></td>
                        </tr>
                    </table>
                </div>
                
                <form method="POST" action="" class="form-horizontal">
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Next School Year:</label>
                        <div class="col-md-6">
                            <select name="school_year" class="form-control" required>
                                <option value="">-- Select School Year --</option>
                                <?php
                                $current_year = $scholar->SCHOOL_YEAR;
                                $years = explode('-', $current_year);
                                $next_start = intval($years[0]) + 1;
                                $next_end = intval($years[1]) + 1;
                                $next_year = $next_start . '-' . $next_end;
                                ?>
                                <option value="<?= $next_year ?>"><?= $next_year ?> (Next Year)</option>
                                <option value="<?= $current_year ?>"><?= $current_year ?> (Same Year - Summer)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Semester:</label>
                        <div class="col-md-6">
                            <select name="semester" class="form-control" required>
                                <option value="">-- Select Semester --</option>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Current GPA (%):</label>
                        <div class="col-md-6">
                            <input type="number" name="gpa" class="form-control" 
                                   value="<?= $scholar->GPA ?>" min="0" max="100" step="0.01" required>
                            <span class="help-block">Enter the scholar's current GPA (0-100)</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Units Completed:</label>
                        <div class="col-md-6">
                            <input type="number" name="units_completed" class="form-control" 
                                   value="24" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Renewal Status:</label>
                        <div class="col-md-6">
                            <select name="renewal_status" class="form-control" required>
                                <option value="Approved">APPROVED - Continue Scholarship</option>
                                <option value="Denied">DENIED - Terminate Scholarship</option>
                                <option value="Pending">PENDING - For Review</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Remarks/Notes:</label>
                        <div class="col-md-6">
                            <textarea name="remarks" class="form-control" rows="4" 
                                      placeholder="Enter any remarks about this renewal">GPA meets requirement. Renewal approved.</textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Maintain Same Amount:</label>
                        <div class="col-md-6">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="same_amount" value="1" checked>
                                    Keep scholarship amount (₱ <?= number_format($scholar->AMOUNT, 2) ?>)
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-md-offset-3 col-md-6">
                            <button type="submit" name="renew" class="btn btn-success" onclick="return confirm('Process this renewal?')">
                                <i class="fa fa-save"></i> Process Renewal
                            </button>
                            <a href="index.php?view=view&id=<?= $id ?>" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>