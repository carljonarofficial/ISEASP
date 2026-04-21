<?php
require_once("../../include/initialize.php");
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

global $mydb;

$action = (isset($_GET['action']) && $_GET['action'] != '') ? $_GET['action'] : '';

switch ($action) {
    // Existing actions
    case 'delete':
        doDelete();
        break;
    case 'terminate':
        doTerminate();
        break;
    case 'graduate':
        doGraduate();
        break;
    
    // Payroll actions
    case 'generate_payroll':
        generatePayroll();
        break;
    case 'approve_payroll':
        approvePayroll();
        break;
    case 'disburse_payroll':
        disbursePayroll();
        break;
    case 'sync_payroll_scholars':
        syncPayrollScholars();
        break;
    case 'mark_stipend_paid':
        markStipendPaid();
        break;
    case 'renew_scholar':
        renewScholar();
        break;
    case 'renew_multiple':
        renewMultipleScholars();
        break;
    
    default:
        redirect(web_root . "admin/scholars/");
}

// Log function for all actions
function logActivity($action, $details, $applicant_id = null) {
    global $mydb;
    
    // Get user details
    $mydb->setQuery("SELECT USERNAME, ROLE FROM tblusers WHERE USERID = " . $_SESSION['ADMIN_USERID']);
    $mydb->executeQuery();
    $user = $mydb->loadSingleResult();
    
    $log_sql = "INSERT INTO tbl_application_log 
                (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS, LOG_DATE)
                VALUES (
                    " . ($applicant_id ? $applicant_id : 'NULL') . ", 
                    " . $_SESSION['ADMIN_USERID'] . ", 
                    '" . addslashes($user->USERNAME) . "', 
                    '" . addslashes($user->ROLE) . "', 
                    '" . addslashes($action) . "',
                    'PAYROLL',
                    '" . addslashes($details) . "',
                    NOW()
                )";
    
    $mydb->setQuery($log_sql);
    $mydb->executeQuery();
}

// Existing functions
function doDelete() {
    global $mydb;
    
    if (isset($_GET['id']) && $_SESSION['ADMIN_ROLE'] == 'Super Admin') {
        $award_id = intval($_GET['id']);
        
        // Get applicant ID before deleting
        $mydb->setQuery("SELECT APPLICANTID FROM tbl_scholarship_awards WHERE AWARD_ID = $award_id");
        $mydb->executeQuery();
        $result = $mydb->loadSingleResult();
        $applicant_id = $result->APPLICANTID;
        
        // Get scholar name for logging
        $mydb->setQuery("SELECT CONCAT(LASTNAME, ', ', FIRSTNAME, ' ', IFNULL(MIDDLENAME, '')) as FULLNAME FROM tbl_applicants WHERE APPLICANTID = $applicant_id");
        $mydb->executeQuery();
        $scholar = $mydb->loadSingleResult();
        
        // Delete scholarship award
        $sql = "DELETE FROM tbl_scholarship_awards WHERE AWARD_ID = $award_id";
        $mydb->setQuery($sql);
        $mydb->executeQuery();
        
        // Update applicant status back to Qualified
        $update_sql = "UPDATE tbl_applicants SET STATUS = 'Qualified' WHERE APPLICANTID = $applicant_id";
        $mydb->setQuery($update_sql);
        $mydb->executeQuery();
        
        // Log the action
        logActivity("Deleted Scholarship Award", "Deleted scholarship award for scholar: " . ($scholar ? $scholar->FULLNAME : "ID: $applicant_id"), $applicant_id);
        
        message("Scholarship award deleted successfully!", "success");
        redirect("index.php");
    } else {
        message("Access denied!", "error");
        redirect("index.php");
    }
}

function doTerminate() {
    global $mydb;
    
    if (isset($_POST['id'])) {
        $award_id = intval($_POST['id']);
        $reason = trim($_POST['reason']);
        
        // Get applicant ID
        $mydb->setQuery("SELECT APPLICANTID FROM tbl_scholarship_awards WHERE AWARD_ID = $award_id");
        $mydb->executeQuery();
        $result = $mydb->loadSingleResult();
        $applicant_id = $result->APPLICANTID;
        
        // Get scholar name for logging
        $mydb->setQuery("SELECT CONCAT(LASTNAME, ', ', FIRSTNAME, ' ', IFNULL(MIDDLENAME, '')) as FULLNAME FROM tbl_applicants WHERE APPLICANTID = $applicant_id");
        $mydb->executeQuery();
        $scholar = $mydb->loadSingleResult();
        
        // Update award status
        $sql = "UPDATE tbl_scholarship_awards SET 
                STATUS = 'Terminated',
                REMARKS = CONCAT(IFNULL(REMARKS, ''), ' | TERMINATED: $reason')
                WHERE AWARD_ID = $award_id";
        
        $mydb->setQuery($sql);
        $mydb->executeQuery();
        
        // Update applicant status
        $update_sql = "UPDATE tbl_applicants SET STATUS = 'Rejected' WHERE APPLICANTID = $applicant_id";
        $mydb->setQuery($update_sql);
        $mydb->executeQuery();
        
        // Add to history
        $history_sql = "INSERT INTO tbl_scholarship_history 
                        (APPLICANTID, SCHOOL_YEAR, SEMESTER, STATUS, GPA, REMARKS, UPDATED_BY)
                        SELECT 
                            $applicant_id,
                            SCHOOL_YEAR,
                            SEMESTER,
                            'Terminated',
                            GPA,
                            '$reason',
                            " . $_SESSION['ADMIN_USERID'] . "
                        FROM tbl_scholarship_awards 
                        WHERE AWARD_ID = $award_id";
        
        $mydb->setQuery($history_sql);
        $mydb->executeQuery();
        
        // Log the action
        logActivity("Terminated Scholarship", "Terminated scholarship for scholar: " . ($scholar ? $scholar->FULLNAME : "ID: $applicant_id") . ". Reason: $reason", $applicant_id);
        
        echo json_encode(['status' => 'success']);
        exit;
    }
}

function doGraduate() {
    global $mydb;
    
    // Log that the function was called
    error_log("doGraduate() function called");
    
    if (isset($_POST['id'])) {
        $award_id = intval($_POST['id']);
        $final_gpa = floatval($_POST['final_gpa']);
        $honors = isset($_POST['honors']) ? $_POST['honors'] : '';
        $graduation_date = isset($_POST['graduation_date']) ? $_POST['graduation_date'] : date('Y-m-d');
        
        // Log the received data
        error_log("Award ID: $award_id, GPA: $final_gpa, Honors: $honors, Date: $graduation_date");
        
        // Get applicant ID and details
        $mydb->setQuery("SELECT APPLICANTID, SCHOOL_YEAR, SEMESTER FROM tbl_scholarship_awards WHERE AWARD_ID = $award_id");
        $mydb->executeQuery();
        $result = $mydb->loadSingleResult();
        
        if(!$result) {
            error_log("Scholarship award not found for Award ID: $award_id");
            echo json_encode(['status' => 'error', 'message' => 'Scholarship award not found']);
            exit;
        }
        
        $applicant_id = $result->APPLICANTID;
        $school_year = $result->SCHOOL_YEAR;
        $semester = $result->SEMESTER;
        
        error_log("Found Applicant ID: $applicant_id, School Year: $school_year, Semester: $semester");
        
        // Build remarks with honors if any
        $remarks = "Successfully graduated";
        if (!empty($honors)) {
            $remarks .= " with honors: $honors";
        }
        
        // Update scholarship award status to Graduated
        $sql = "UPDATE tbl_scholarship_awards SET 
                STATUS = 'Graduated',
                REMARKS = CONCAT(IFNULL(REMARKS, ''), ' | GRADUATED: $remarks')
                WHERE AWARD_ID = $award_id";
        $mydb->setQuery($sql);
        $result1 = $mydb->executeQuery();
        error_log("Update scholarship award result: " . ($result1 ? "Success" : "Failed"));
        
        // Update applicant status to Graduated and update GPA
        $update_sql = "UPDATE tbl_applicants SET 
                       STATUS = 'Graduated', 
                       GPA = $final_gpa 
                       WHERE APPLICANTID = $applicant_id";
        $mydb->setQuery($update_sql);
        $result2 = $mydb->executeQuery();
        error_log("Update applicant result: " . ($result2 ? "Success" : "Failed"));
        
        // Add to scholarship history
        $history_sql = "INSERT INTO tbl_scholarship_history 
                        (APPLICANTID, SCHOOL_YEAR, SEMESTER, STATUS, GPA, REMARKS, UPDATED_BY, UPDATED_AT) 
                        VALUES (
                            $applicant_id, 
                            '$school_year', 
                            '$semester', 
                            'Graduated', 
                            $final_gpa, 
                            '$remarks', 
                            {$_SESSION['ADMIN_USERID']}, 
                            NOW()
                        )";
        $mydb->setQuery($history_sql);
        $result3 = $mydb->executeQuery();
        error_log("Insert history result: " . ($result3 ? "Success" : "Failed"));
        
        // Check if alumni record already exists
        $mydb->setQuery("SELECT ALUMNI_ID FROM tbl_alumni WHERE APPLICANTID = $applicant_id");
        $mydb->executeQuery();
        $existing_alumni = $mydb->loadSingleResult();
        
        if(!$existing_alumni) {
            $alumni_sql = "INSERT INTO tbl_alumni 
                           (APPLICANTID, GRADUATION_DATE, FINAL_GPA, HONORS, UPDATED_AT) 
                           VALUES (
                               $applicant_id, 
                               '$graduation_date', 
                               $final_gpa, 
                               " . ($honors ? "'$honors'" : "NULL") . ", 
                               NOW()
                           )";
            $mydb->setQuery($alumni_sql);
            $result4 = $mydb->executeQuery();
            error_log("Insert alumni result: " . ($result4 ? "Success" : "Failed"));
        } else {
            $alumni_sql = "UPDATE tbl_alumni SET 
                           GRADUATION_DATE = '$graduation_date',
                           FINAL_GPA = $final_gpa,
                           HONORS = " . ($honors ? "'$honors'" : "NULL") . ",
                           UPDATED_AT = NOW()
                           WHERE APPLICANTID = $applicant_id";
            $mydb->setQuery($alumni_sql);
            $result4 = $mydb->executeQuery();
            error_log("Update alumni result: " . ($result4 ? "Success" : "Failed"));
        }
        
        // Log the action
        $log_sql = "INSERT INTO tbl_application_log 
                    (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS, LOG_DATE)
                    SELECT 
                        $applicant_id,
                        {$_SESSION['ADMIN_USERID']},
                        USERNAME,
                        ROLE,
                        'Graduated Scholar',
                        'SCHOLAR',
                        'Scholar graduated with GPA: $final_gpa%. Honors: $honors',
                        NOW()
                    FROM tblusers 
                    WHERE USERID = {$_SESSION['ADMIN_USERID']}";
        $mydb->setQuery($log_sql);
        $result5 = $mydb->executeQuery();
        error_log("Insert log result: " . ($result5 ? "Success" : "Failed"));
        
        echo json_encode(['status' => 'success', 'message' => 'Scholar marked as graduated successfully!']);
        exit;
    }
    
    error_log("No POST id received");
    echo json_encode(['status' => 'error', 'message' => 'Invalid request - No ID provided']);
    exit;
}

// Payroll Functions
function generatePayroll() {
    global $mydb;
    
    $payment_date = isset($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d');
    $school_year_id = isset($_POST['school_year_id']) ? intval($_POST['school_year_id']) : 0;
    $semester = isset($_POST['semester']) ? $_POST['semester'] : '';
    $stipend_amount = isset($_POST['stipend_amount']) ? floatval($_POST['stipend_amount']) : 0;
    $remarks = $_POST['remarks'];
    
    // Check if payroll already exists for this school year and semester
    $mydb->setQuery("SELECT id FROM tbl_payroll WHERE school_year_id = '$school_year_id' AND semester = '$semester'");
    $mydb->executeQuery();
    $result = $mydb->loadSingleResult();
    if($result) {
        message("Payroll for this semester already exists!", "error");
        redirect(web_root . "admin/scholars/index.php?view=payroll");
        return;
    }
    
    // Get school year name for matching and logging
    $mydb->setQuery("SELECT school_year FROM tbl_school_years WHERE id = '$school_year_id'");
    $mydb->executeQuery();
    $sy = $mydb->loadSingleResult();
    if (!$sy) {
        message("Selected School Year not found!", "error");
        redirect(web_root . "admin/scholars/index.php?view=payroll");
        return;
    }
    $sy_name = $sy->school_year;

    // Get all approved renewals for this specific school year and semester
    $mydb->setQuery("
        SELECT sr.scholar_id, sa.AMOUNT as monthly_stipend 
        FROM tbl_scholar_renewals sr
        LEFT JOIN tbl_scholarship_awards sa ON sr.scholar_id = sa.APPLICANTID AND sa.STATUS = 'Active'
        WHERE sr.school_year_id = '$school_year_id' AND sr.status = 'approved'
        AND sa.SCHOOL_YEAR = '$sy_name' AND sa.SEMESTER = '$semester'
    ");
    $mydb->executeQuery();
    $renewals = $mydb->loadResultList();
    
    if(!$renewals || count($renewals) == 0) {
        message("No renewed scholars found for this school year!", "error");
        redirect(web_root . "admin/scholars/index.php?view=payroll");
        return;
    }
    
    $total_amount = 0;
    $scholar_list = [];
    
    foreach($renewals as $renewal) {
        $scholar_stipend = ($renewal->monthly_stipend && $renewal->monthly_stipend > 0) ? $renewal->monthly_stipend : $stipend_amount;
        $scholar_list[] = [
            'scholar_id' => $renewal->scholar_id,
            'amount' => $scholar_stipend
        ];
        $total_amount += $scholar_stipend;
    }
    
    // Create payroll record
    $mydb->setQuery("
        INSERT INTO tbl_payroll (payment_date, school_year_id, semester, total_amount, remarks, status, created_at, created_by) 
        VALUES ('$payment_date', '$school_year_id', '$semester', '$total_amount', '$remarks', 'pending', NOW(), '{$_SESSION['ADMIN_USERID']}')
    ");
    $mydb->executeQuery();
    $payroll_id = $mydb->insert_id();
    
    // Add payroll details for each scholar
    foreach($scholar_list as $scholar) {
        $mydb->setQuery("
            INSERT INTO tbl_payroll_details (payroll_id, scholar_id, amount, payment_status) 
            VALUES ('$payroll_id', '{$scholar['scholar_id']}', '{$scholar['amount']}', 'pending')
        ");
        $mydb->executeQuery();
    }
    
    // Log activity
    logActivity("Generated Payroll", "Generated payroll for $semester, " . date('F d, Y', strtotime($payment_date)) . " with " . count($scholar_list) . " scholars for " . ($sy ? $sy->school_year : "School Year ID: $school_year_id"));
    
    message("Payroll generated successfully for $semester with " . count($scholar_list) . " renewed scholars!", "success");
    redirect(web_root . "admin/scholars/index.php?view=payroll");
}

function syncPayrollScholars() {
    global $mydb;
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    // Get current payroll metadata
    $mydb->setQuery("SELECT p.*, sy.school_year FROM tbl_payroll p 
                     LEFT JOIN tbl_school_years sy ON p.school_year_id = sy.id 
                     WHERE p.id = $id");
    $payroll = $mydb->loadSingleResult();
    
    if (!$payroll) {
        message("Payroll not found!", "error");
        redirect("index.php?view=payroll");
        return;
    }
    
    if ($payroll->status != 'pending') {
        message("Only pending payrolls can be synced!", "error");
        redirect("index.php?view=payroll_details&id=$id");
        return;
    }

    $sy_id = $payroll->school_year_id;
    $sy_name = $payroll->school_year;
    $semester = $payroll->semester;

    // Get a default amount from existing entries in this batch for fallback
    $mydb->setQuery("SELECT amount FROM tbl_payroll_details WHERE payroll_id = $id LIMIT 1");
    $default_row = $mydb->loadSingleResult();
    $fallback_amount = $default_row ? floatval($default_row->amount) : 5000.00;

    // Find approved renewals for this SY/Semester that are NOT in the payroll details
    $mydb->setQuery("
        SELECT sr.scholar_id, sa.AMOUNT as monthly_stipend 
        FROM tbl_scholar_renewals sr
        INNER JOIN tbl_scholarship_awards sa ON sr.scholar_id = sa.APPLICANTID AND sa.STATUS = 'Active'
        WHERE sr.school_year_id = '$sy_id' AND sr.status = 'approved'
        AND sa.SCHOOL_YEAR = '$sy_name' AND sa.SEMESTER = '$semester'
        AND sr.scholar_id NOT IN (SELECT scholar_id FROM tbl_payroll_details WHERE payroll_id = $id)
    ");
    $missing_scholars = $mydb->loadResultList();
    
    if (!$missing_scholars || count($missing_scholars) == 0) {
        message("No additional renewed scholars found to add.", "info");
        redirect("index.php?view=payroll_details&id=$id");
        return;
    }

    $added_count = 0;
    $added_amount = 0;
    foreach ($missing_scholars as $scholar) {
        $amount = ($scholar->monthly_stipend && $scholar->monthly_stipend > 0) ? $scholar->monthly_stipend : $fallback_amount;
        $mydb->setQuery("INSERT INTO tbl_payroll_details (payroll_id, scholar_id, amount, payment_status) 
                         VALUES ($id, '{$scholar->scholar_id}', '$amount', 'pending')");
        $mydb->executeQuery();
        $added_count++;
        $added_amount += $amount;
    }

    $mydb->setQuery("UPDATE tbl_payroll SET total_amount = total_amount + $added_amount WHERE id = $id");
    $mydb->executeQuery();

    logActivity("Synced Payroll", "Added $added_count scholars to payroll ID: $id. Total amount increased by ₱" . number_format($added_amount, 2));
    message("Successfully added $added_count renewed scholars to the payroll!", "success");
    redirect("index.php?view=payroll_details&id=$id");
}

function approvePayroll() {
    global $mydb;
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    // Get payroll details before update for logging
    $mydb->setQuery("
        SELECT p.*, sy.school_year 
        FROM tbl_payroll p
        LEFT JOIN tbl_school_years sy ON p.school_year_id = sy.id
        WHERE p.id = $id
    ");
    $mydb->executeQuery();
    $payroll = $mydb->loadSingleResult();
    
    $mydb->setQuery("
        UPDATE tbl_payroll 
        SET status = 'approved', approved_by = '{$_SESSION['ADMIN_USERID']}', approved_date = NOW() 
        WHERE id = $id
    ");
    $mydb->executeQuery();
    
    // Log activity
    logActivity("Approved Payroll", "Approved payroll for " . ($payroll ? $payroll->semester . ", " . date('F Y', strtotime($payroll->payment_date)) . " for " . $payroll->school_year : "ID: $id"));
    
    message("Payroll approved successfully!", "success");
    redirect(web_root . "admin/scholars/index.php?view=payroll");
}

function disbursePayroll() {
    global $mydb;
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $reference_no = isset($_POST['reference_no']) ? $_POST['reference_no'] : 'PAY-' . date('Ymd') . '-' . $id;
    
    // Get payroll details before update for logging
    $mydb->setQuery("
        SELECT p.*, sy.school_year 
        FROM tbl_payroll p
        LEFT JOIN tbl_school_years sy ON p.school_year_id = sy.id
        WHERE p.id = $id
    ");
    $mydb->executeQuery();
    $payroll = $mydb->loadSingleResult();
    
    $mydb->setQuery("
        UPDATE tbl_payroll 
        SET status = 'disbursed', disbursed_by = '{$_SESSION['ADMIN_USERID']}', 
            disbursement_date = NOW(), reference_no = '$reference_no' 
        WHERE id = $id
    ");
    $mydb->executeQuery();
    
    // Log activity
    logActivity("Disbursed Payroll", "Disbursed payroll with reference: $reference_no for " . ($payroll ? $payroll->semester . ", " . date('F Y', strtotime($payroll->payment_date)) . " for " . $payroll->school_year : "ID: $id") . ". Total amount: ₱" . number_format($payroll->total_amount, 2));
    
    message("Payroll disbursed successfully!", "success");
    redirect(web_root . "admin/scholars/index.php?view=payroll");
}

function markStipendPaid() {
    global $mydb;
    
    $detail_id = isset($_GET['detail_id']) ? intval($_GET['detail_id']) : 0;
    $payroll_id = isset($_GET['payroll_id']) ? intval($_GET['payroll_id']) : 0;
    
    // Get scholar details before update for logging
    $mydb->setQuery("
        SELECT pd.*, 
               CONCAT(a.LASTNAME, ', ', a.FIRSTNAME, ' ', IFNULL(a.MIDDLENAME, '')) as FULLNAME,
               a.APPLICANTID
        FROM tbl_payroll_details pd
        INNER JOIN tbl_applicants a ON pd.scholar_id = a.APPLICANTID
        WHERE pd.id = $detail_id
    ");
    $mydb->executeQuery();
    $detail = $mydb->loadSingleResult();
    
    $mydb->setQuery("
        UPDATE tbl_payroll_details 
        SET payment_status = 'paid', payment_date = NOW() 
        WHERE id = $detail_id
    ");
    $mydb->executeQuery();
    
    // Log activity
    if($detail) {
        logActivity("Marked Stipend Paid", "Marked stipend as paid for scholar: " . $detail->FULLNAME . " - Amount: ₱" . number_format($detail->amount, 2), $detail->APPLICANTID);
    } else {
        logActivity("Marked Stipend Paid", "Marked stipend as paid for detail ID: $detail_id");
    }
    
    message("Stipend marked as paid!", "success");
    redirect(web_root . "admin/scholars/index.php?view=payroll_details&id=$payroll_id");
}

function renewScholar() {
    global $mydb;
    
    $scholar_id = isset($_GET['scholar_id']) ? intval($_GET['scholar_id']) : 0;
    $school_year_id = isset($_GET['school_year_id']) ? intval($_GET['school_year_id']) : 0;
    
    // Get scholar details for logging
    $mydb->setQuery("SELECT CONCAT(LASTNAME, ', ', FIRSTNAME, ' ', IFNULL(MIDDLENAME, '')) as FULLNAME FROM tbl_applicants WHERE APPLICANTID = $scholar_id");
    $mydb->executeQuery();
    $scholar = $mydb->loadSingleResult();
    
    // Check if already renewed
    $mydb->setQuery("SELECT id FROM tbl_scholar_renewals WHERE scholar_id = '$scholar_id' AND school_year_id = '$school_year_id'");
    $mydb->executeQuery();
    $result = $mydb->loadSingleResult();
    
    if($result) {
        // Update existing
        $mydb->setQuery("
            UPDATE tbl_scholar_renewals 
            SET status = 'approved', approved_by = '{$_SESSION['ADMIN_USERID']}', approved_date = NOW() 
            WHERE scholar_id = '$scholar_id' AND school_year_id = '$school_year_id'
        ");
    } else {
        // Insert new
        $mydb->setQuery("
            INSERT INTO tbl_scholar_renewals (scholar_id, school_year_id, status, approved_by, approved_date) 
            VALUES ('$scholar_id', '$school_year_id', 'approved', '{$_SESSION['ADMIN_USERID']}', NOW())
        ");
    }
    $mydb->executeQuery();
    
    // Get school year name for logging
    $mydb->setQuery("SELECT school_year FROM tbl_school_years WHERE id = $school_year_id");
    $mydb->executeQuery();
    $sy = $mydb->loadSingleResult();
    
    // Log activity
    logActivity("Renewed Scholar", "Renewed scholar: " . ($scholar ? $scholar->FULLNAME : "ID: $scholar_id") . " for school year: " . ($sy ? $sy->school_year : "ID: $school_year_id"), $scholar_id);
    
    message("Scholar renewed successfully!", "success");
    redirect(web_root . "admin/scholars/index.php?view=view&id=$scholar_id");
}

function renewMultipleScholars() {
    global $mydb;
    
    $scholar_ids    = isset($_POST['scholar_ids']) ? $_POST['scholar_ids'] : [];
    $school_year_id = isset($_POST['school_year_id']) ? intval($_POST['school_year_id']) : 0;
    $school_year    = isset($_POST['school_year']) ? $_POST['school_year'] : '';
    $semester       = isset($_POST['semester']) ? $_POST['semester'] : '';
    $status         = isset($_POST['renewal_status']) ? $_POST['renewal_status'] : 'Approved';
    $remarks        = isset($_POST['remarks']) ? trim($_POST['remarks']) : 'Batch renewal';
    $admin_id       = $_SESSION['ADMIN_USERID'];

    if(!is_array($scholar_ids) || count($scholar_ids) == 0) {
        echo json_encode(['success' => false, 'message' => 'No scholars selected']);
        return;
    }
    
    $success_count = 0;
    $scholar_names = [];
    
    foreach($scholar_ids as $scholar_id) {
        // Fetch scholar details to verify eligibility (Active Scholarship Award)
        $sql = "
            SELECT 
                a.*, sa.AWARD_ID, sa.AMOUNT
            FROM tbl_applicants a
            INNER JOIN tbl_scholarship_awards sa ON a.APPLICANTID = sa.APPLICANTID
            WHERE a.APPLICANTID = $scholar_id AND sa.STATUS = 'Active'
        ";
        $mydb->setQuery($sql);
        $scholar = $mydb->loadSingleResult();
        
        if (!$scholar) continue;

        $scholar_names[] = $scholar->LASTNAME . ', ' . $scholar->FIRSTNAME;
        $gpa = $scholar->GPA ?? 0;

        // 1. Check if renewal already exists in tbl_renewal_applications (Logic from renew.php)
        $check_sql = "SELECT COUNT(*) as count FROM tbl_renewal_applications 
                      WHERE APPLICANTID = $scholar_id 
                      AND SCHOOL_YEAR = '$school_year' 
                      AND SEMESTER = '$semester'";
        $mydb->setQuery($check_sql);
        $check = $mydb->loadSingleResult();
        
        if ($check->count > 0) continue;

        // 2. Insert into tbl_renewal_applications
        $renew_sql = "INSERT INTO tbl_renewal_applications 
                      (APPLICANTID, SCHOOL_YEAR, SEMESTER, PREVIOUS_GPA, UNITS_COMPLETED, STATUS, REVIEWED_BY, REVIEW_DATE, REMARKS)
                      VALUES ($scholar_id, '$school_year', '$semester', $gpa, 24, '$status', $admin_id, NOW(), '$remarks')";
        $mydb->setQuery($renew_sql);
        $mydb->executeQuery();

        // 3. Update scholarship award and applicant details if approved
        if ($status == 'Approved') {
            $update_award = "UPDATE tbl_scholarship_awards SET 
                             SCHOOL_YEAR = '$school_year',
                             SEMESTER = '$semester'
                             WHERE AWARD_ID = " . $scholar->AWARD_ID;
            $mydb->setQuery($update_award);
            $mydb->executeQuery();
            
            // Increment Year Level
            $new_year = '';
            switch ($scholar->YEARLEVEL) {
                case '1st Year': $new_year = '2nd Year'; break;
                case '2nd Year': $new_year = '3rd Year'; break;
                case '3rd Year': $new_year = '4th Year'; break;
                case '4th Year': $new_year = '5th Year'; break;
                default: $new_year = $scholar->YEARLEVEL;
            }
            
            $year_sql = "UPDATE tbl_applicants SET YEARLEVEL = '$new_year' WHERE APPLICANTID = $scholar_id";
            $mydb->setQuery($year_sql);
            $mydb->executeQuery();

            // 4. Update tbl_scholar_renewals (Payroll Tracking logic)
            $mydb->setQuery("SELECT id FROM tbl_scholar_renewals WHERE scholar_id = '$scholar_id' AND school_year_id = '$school_year_id'");
            $res_renew = $mydb->loadSingleResult();
            
            if($res_renew) {
                $mydb->setQuery("UPDATE tbl_scholar_renewals SET status = 'approved', approved_by = '$admin_id', approved_date = NOW() 
                                 WHERE scholar_id = '$scholar_id' AND school_year_id = '$school_year_id'");
            } else {
                $mydb->setQuery("INSERT INTO tbl_scholar_renewals (scholar_id, school_year_id, status, approved_by, approved_date) 
                                 VALUES ('$scholar_id', '$school_year_id', 'approved', '$admin_id', NOW())");
            }
            $mydb->executeQuery();
        }

        // 5. Insert into scholarship history
        $history_sql = "INSERT INTO tbl_scholarship_history 
                        (APPLICANTID, SCHOOL_YEAR, SEMESTER, STATUS, GPA, REMARKS, UPDATED_BY)
                        VALUES ($scholar_id, '$school_year', '$semester', 'Renewed', $gpa, 'Bulk Renewal $status', $admin_id)";
        $mydb->setQuery($history_sql);
        $mydb->executeQuery();

        // 6. Log activity
        $log_sql = "INSERT INTO tbl_application_log 
                    (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS)
                    SELECT 
                        $scholar_id, $admin_id, USERNAME, ROLE, 
                        CONCAT('Bulk Scholarship renewed: ', '$status'), 'SCHOLAR',
                        CONCAT('Renewal for: ', '$school_year', ' ', '$semester')
                    FROM tblusers WHERE USERID = $admin_id";
        $mydb->setQuery($log_sql);
        $mydb->executeQuery();

        $success_count++;
    }
    
    // Get school year name for logging
    $mydb->setQuery("SELECT school_year FROM tbl_school_years WHERE id = $school_year_id");
    $mydb->executeQuery();
    $sy = $mydb->loadSingleResult();
    
    // Log activity
    $names_list = implode(", ", array_slice($scholar_names, 0, 5));
    if(count($scholar_names) > 5) {
        $names_list .= " and " . (count($scholar_names) - 5) . " more";
    }
    logActivity("Bulk Renewal", "Renewed $success_count scholars for school year " . ($sy ? $sy->school_year : "ID: $school_year_id") . ". Scholars: $names_list");
    
    echo json_encode(['success' => true, 'count' => $success_count]);
}
?>