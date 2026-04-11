<?php
require_once("../../include/initialize.php");
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root . "admin/index.php");
}

$action = (isset($_GET['action']) && $_GET['action'] != '') ? $_GET['action'] : '';

switch ($action) {
    case 'add':
        doInsert();
        break;
    case 'edit':
        doEdit();
        break;
    case 'delete':
        doDelete();
        break;
    case 'batch_delete':
        doBatchDelete();
        break;
    case 'batch_export':
        doBatchExport();
        break;
    case 'batch_print':
        doBatchPrint();
        break;
    case 'batch_add_edit':
        doBatchAddEdit();
        break;
}

function doInsert() {
    global $mydb;



    

    error_log("=== EXAM CONTROLLER doInsert() START ===");
    error_log("POST data: " . print_r($_POST, true));






    
    if (isset($_POST['save'])) {
        $applicant_id = intval($_POST['APPLICANTID']);
        $total_score = floatval($_POST['TOTAL_SCORE']);
        $passing_score = floatval($_POST['PASSING_SCORE']);
        $remarks = trim($_POST['REMARKS']);
        $exam_date = date('Y-m-d H:i:s', strtotime($_POST['EXAM_DATE']));
        $examiner_id = $_SESSION['ADMIN_USERID'];
        
        // Determine exam status
        $exam_status = ($total_score >= $passing_score) ? 'Passed' : 'Failed';
        
        // Insert exam result
        $sql = "INSERT INTO tbl_exam_results 
                (APPLICANTID, EXAMINER_ID, EXAM_DATE, TOTAL_SCORE, PASSING_SCORE, REMARKS)
                VALUES ($applicant_id, $examiner_id, '$exam_date', $total_score, $passing_score, '$remarks')";
        
        $mydb->setQuery($sql);
        $mydb->executeQuery();
        
        // Update applicant's exam status
        $update_sql = "UPDATE tbl_applicants SET EXAM_STATUS = '$exam_status' WHERE APPLICANTID = $applicant_id";
        $mydb->setQuery($update_sql);
        $mydb->executeQuery();
        
        // AUTO-CREATE INTERVIEW RECORD IF EXAM IS PASSED
        if ($exam_status == 'Passed') {
            // Check if interview record already exists
            $check_sql = "SELECT COUNT(*) as count FROM tbl_interview WHERE APPLICANTID = $applicant_id";
            $mydb->setQuery($check_sql);
            $mydb->executeQuery();
            $check_result = $mydb->loadSingleResult();
            
            if ($check_result->count == 0) {
                // Set default interview date (3 days from now at 9:00 AM)
                $default_interview_date = date('Y-m-d H:i:s', strtotime('+3 days 09:00:00'));
                
                // Insert interview record with current admin as default interviewer
                $default_interviewer = $_SESSION['ADMIN_USERID'];
                
                $interview_sql = "INSERT INTO tbl_interview 
                                 (APPLICANTID, INTERVIEW_DATE, INTERVIEW_MODE, RECOMMENDATION, COMMENTS, INTERVIEWER_ID)
                                 VALUES 
                                 ($applicant_id, '$default_interview_date', 'Face-to-face', 'For Review', 'Awaiting interview schedule', $default_interviewer)";
                
                $mydb->setQuery($interview_sql);
                $mydb->executeQuery();
                
                // Update applicant status to 'For Interview'
                $status_sql = "UPDATE tbl_applicants SET STATUS = 'For Interview' WHERE APPLICANTID = $applicant_id";
                $mydb->setQuery($status_sql);
                $mydb->executeQuery();
                
                // Log the interview creation
                $log_sql = "INSERT INTO tbl_application_log 
                            (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS)
                            SELECT 
                                $applicant_id, 
                                $examiner_id, 
                                USERNAME, 
                                ROLE, 
                                'Interview automatically scheduled (exam passed)',
                                'INTERVIEW',
                                'Interview automatically created after passing exam'
                            FROM tblusers 
                            WHERE USERID = $examiner_id";
                $mydb->setQuery($log_sql);
                $mydb->executeQuery();
            }
        }
        
        // Log the exam result action - FIXED
        $log_sql = "INSERT INTO tbl_application_log 
                    (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS)
                    SELECT 
                        $applicant_id, 
                        $examiner_id, 
                        USERNAME, 
                        ROLE, 
                        CONCAT('Exam result recorded: ', '$exam_status'),
                        'EXAM',
                        CONCAT('Score: ', $total_score, '%, ', '$exam_status')
                    FROM tblusers 
                    WHERE USERID = $examiner_id";
        $mydb->setQuery($log_sql);
        $mydb->executeQuery();
        
        message("Exam result saved successfully! " . ($exam_status == 'Passed' ? "Interview has been scheduled." : ""), "success");
        redirect("index.php?view=results");
    }
}

function doEdit() {
    global $mydb;
    
    if (isset($_POST['save'])) {
        $result_id = intval($_POST['id']);
        $total_score = floatval($_POST['TOTAL_SCORE']);
        $passing_score = floatval($_POST['PASSING_SCORE']);
        $remarks = trim($_POST['REMARKS']);
        $exam_date = date('Y-m-d H:i:s', strtotime($_POST['EXAM_DATE']));
        
        // Get applicant ID from result
        $mydb->setQuery("SELECT APPLICANTID FROM tbl_exam_results WHERE EXAM_RESULT_ID = $result_id");
        $mydb->executeQuery();
        $result = $mydb->loadSingleResult();
        
        if (!$result) {
            message("Exam result not found!", "error");
            redirect("index.php?view=results");
            return;
        }
        
        $applicant_id = $result->APPLICANTID;
        
        // Determine exam status
        $exam_status = ($total_score >= $passing_score) ? 'Passed' : 'Failed';
        
        // Get old exam status
        $mydb->setQuery("SELECT EXAM_STATUS FROM tbl_applicants WHERE APPLICANTID = $applicant_id");
        $mydb->executeQuery();
        $old_status_result = $mydb->loadSingleResult();
        $old_status = $old_status_result->EXAM_STATUS;
        
        // Update exam result
        $sql = "UPDATE tbl_exam_results SET
                EXAM_DATE = '$exam_date',
                TOTAL_SCORE = $total_score,
                PASSING_SCORE = $passing_score,
                REMARKS = '$remarks'
                WHERE EXAM_RESULT_ID = $result_id";
        
        $mydb->setQuery($sql);
        $mydb->executeQuery();
        
        // Update applicant's exam status
        $update_sql = "UPDATE tbl_applicants SET EXAM_STATUS = '$exam_status' WHERE APPLICANTID = $applicant_id";
        $mydb->setQuery($update_sql);
        $mydb->executeQuery();
        
        // CHECK IF STATUS CHANGED FROM FAILED TO PASSED
        if ($exam_status == 'Passed' && $old_status != 'Passed') {
            // Check if interview record already exists
            $check_sql = "SELECT COUNT(*) as count FROM tbl_interview WHERE APPLICANTID = $applicant_id";
            $mydb->setQuery($check_sql);
            $mydb->executeQuery();
            $check_result = $mydb->loadSingleResult();
            
            if ($check_result->count == 0) {
                // Set default interview date
                $default_interview_date = date('Y-m-d H:i:s', strtotime('+3 days 09:00:00'));
                $default_interviewer = $_SESSION['ADMIN_USERID'];
                
                $interview_sql = "INSERT INTO tbl_interview 
                                 (APPLICANTID, INTERVIEW_DATE, INTERVIEW_MODE, RECOMMENDATION, COMMENTS, INTERVIEWER_ID)
                                 VALUES 
                                 ($applicant_id, '$default_interview_date', 'Face-to-face', 'For Review', 'Awaiting interview schedule', $default_interviewer)";
                
                $mydb->setQuery($interview_sql);
                $mydb->executeQuery();
                
                // Update applicant status
                $status_sql = "UPDATE tbl_applicants SET STATUS = 'For Interview' WHERE APPLICANTID = $applicant_id";
                $mydb->setQuery($status_sql);
                $mydb->executeQuery();
                
                // Log the interview creation
                $log_sql = "INSERT INTO tbl_application_log 
                            (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS)
                            SELECT 
                                $applicant_id, 
                                " . $_SESSION['ADMIN_USERID'] . ", 
                                USERNAME, 
                                ROLE, 
                                'Interview automatically scheduled (exam passed on update)',
                                'INTERVIEW',
                                'Interview automatically created after passing exam'
                            FROM tblusers 
                            WHERE USERID = " . $_SESSION['ADMIN_USERID'];
                $mydb->setQuery($log_sql);
                $mydb->executeQuery();
            }
        } elseif ($exam_status == 'Failed') {
            // Delete any existing interview for failed applicants
            $delete_interview_sql = "DELETE FROM tbl_interview WHERE APPLICANTID = $applicant_id";
            $mydb->setQuery($delete_interview_sql);
            $mydb->executeQuery();
            
            // Update applicant status back to Pending
            $status_sql = "UPDATE tbl_applicants SET STATUS = 'Pending' WHERE APPLICANTID = $applicant_id";
            $mydb->setQuery($status_sql);
            $mydb->executeQuery();
        }
        
        // Log the action - FIXED
        $log_sql = "INSERT INTO tbl_application_log 
                    (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS)
                    SELECT 
                        $applicant_id, 
                        " . $_SESSION['ADMIN_USERID'] . ", 
                        USERNAME, 
                        ROLE, 
                        CONCAT('Exam result updated: ', '$exam_status'),
                        'EXAM',
                        CONCAT('Score updated to: ', $total_score, '%')
                    FROM tblusers 
                    WHERE USERID = " . $_SESSION['ADMIN_USERID'];
        $mydb->setQuery($log_sql);
        $mydb->executeQuery();
        
        message("Exam result updated successfully!", "success");
        redirect("index.php?view=results");
    }
}

function doDelete() {
    global $mydb;
    
    if (isset($_GET['id'])) {
        $result_id = intval($_GET['id']);
        
        // Get applicant ID before deleting
        $mydb->setQuery("SELECT APPLICANTID FROM tbl_exam_results WHERE EXAM_RESULT_ID = $result_id");
        $mydb->executeQuery();
        $result = $mydb->loadSingleResult();
        
        if (!$result) {
            message("Exam result not found!", "error");
            redirect("index.php?view=results");
            return;
        }
        
        $applicant_id = $result->APPLICANTID;
        
        // Delete exam result
        $sql = "DELETE FROM tbl_exam_results WHERE EXAM_RESULT_ID = $result_id";
        $mydb->setQuery($sql);
        $mydb->executeQuery();
        
        // Reset applicant's exam status to Pending
        $update_sql = "UPDATE tbl_applicants SET EXAM_STATUS = 'Pending' WHERE APPLICANTID = $applicant_id";
        $mydb->setQuery($update_sql);
        $mydb->executeQuery();
        
        // Delete any associated interview record
        $delete_interview_sql = "DELETE FROM tbl_interview WHERE APPLICANTID = $applicant_id";
        $mydb->setQuery($delete_interview_sql);
        $mydb->executeQuery();
        
        // Reset applicant status to Pending
        $status_sql = "UPDATE tbl_applicants SET STATUS = 'Pending' WHERE APPLICANTID = $applicant_id";
        $mydb->setQuery($status_sql);
        $mydb->executeQuery();
        
        // Log the action - FIXED
        $log_sql = "INSERT INTO tbl_application_log 
                    (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS)
                    SELECT 
                        $applicant_id, 
                        " . $_SESSION['ADMIN_USERID'] . ", 
                        USERNAME, 
                        ROLE, 
                        'Exam result deleted',
                        'EXAM',
                        'Exam result deleted'
                    FROM tblusers 
                    WHERE USERID = " . $_SESSION['ADMIN_USERID'];
        $mydb->setQuery($log_sql);
        $mydb->executeQuery();
        
        message("Exam result deleted successfully!", "success");
        redirect("index.php?view=results");
    }
}

function doBatchDelete() {
    global $mydb;
    
    if (isset($_POST['ids']) && is_array($_POST['ids'])) {
        $ids = array_map('intval', $_POST['ids']);
        $deleted_count = 0;
        
        foreach ($ids as $result_id) {
            // Get applicant ID before deleting
            $mydb->setQuery("SELECT APPLICANTID FROM tbl_exam_results WHERE EXAM_RESULT_ID = $result_id");
            $mydb->executeQuery();
            $result = $mydb->loadSingleResult();
            
            if ($result) {
                $applicant_id = $result->APPLICANTID;
                
                // Delete exam result
                $sql = "DELETE FROM tbl_exam_results WHERE EXAM_RESULT_ID = $result_id";
                $mydb->setQuery($sql);
                $mydb->executeQuery();
                
                // Reset applicant's exam status to Pending
                $update_sql = "UPDATE tbl_applicants SET EXAM_STATUS = 'Pending' WHERE APPLICANTID = $applicant_id";
                $mydb->setQuery($update_sql);
                $mydb->executeQuery();
                
                // Delete any associated interview record
                $delete_interview_sql = "DELETE FROM tbl_interview WHERE APPLICANTID = $applicant_id";
                $mydb->setQuery($delete_interview_sql);
                $mydb->executeQuery();
                
                // Reset applicant status to Pending
                $status_sql = "UPDATE tbl_applicants SET STATUS = 'Pending' WHERE APPLICANTID = $applicant_id";
                $mydb->setQuery($status_sql);
                $mydb->executeQuery();
                
                // Log the action
                $log_sql = "INSERT INTO tbl_application_log 
                            (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS)
                            SELECT 
                                $applicant_id, 
                                " . $_SESSION['ADMIN_USERID'] . ", 
                                USERNAME, 
                                ROLE, 
                                'Exam result deleted (batch)',
                                'EXAM',
                                'Exam result deleted as part of batch operation'
                            FROM tblusers 
                            WHERE USERID = " . $_SESSION['ADMIN_USERID'];
                $mydb->setQuery($log_sql);
                $mydb->executeQuery();
                
                $deleted_count++;
            }
        }
        
        message("$deleted_count exam result(s) deleted successfully!", "success");
        redirect("index.php?view=results");
    } else {
        message("No exam results selected for deletion!", "error");
        redirect("index.php?view=results");
    }
}

function doBatchExport() {
    global $mydb;
    
    if (isset($_GET['ids'])) {
        $ids = explode(',', $_GET['ids']);
        $ids = array_map('intval', $ids);
        $ids_str = implode(',', $ids);
        
        // Get selected results
        $sql = "
            SELECT 
                er.*,
                a.LASTNAME, a.FIRSTNAME, a.MIDDLENAME,
                a.MUNICIPALITY, a.SCHOOL, a.COURSE,
                u.FULLNAME as EXAMINER_NAME
            FROM tbl_exam_results er
            INNER JOIN tbl_applicants a ON er.APPLICANTID = a.APPLICANTID
            LEFT JOIN tblusers u ON er.EXAMINER_ID = u.USERID
            WHERE er.EXAM_RESULT_ID IN ($ids_str)
            ORDER BY er.EXAM_DATE DESC
        ";
        
        $mydb->setQuery($sql);
        $mydb->executeQuery();
        $results = $mydb->loadResultList();
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="exam_results_' . date('Y-m-d_H-i-s') . '.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Write CSV headers
        fputcsv($output, array(
            'Exam Date',
            'Applicant Name',
            'Municipality',
            'School',
            'Course',
            'Total Score (%)',
            'Passing Score (%)',
            'Result',
            'Examined By',
            'Remarks'
        ));
        
        // Write data rows
        foreach ($results as $r) {
            $applicant_name = $r->LASTNAME . ', ' . $r->FIRSTNAME . ' ' . ($r->MIDDLENAME ?? '');
            $result_status = ($r->TOTAL_SCORE >= $r->PASSING_SCORE) ? 'PASSED' : 'FAILED';
            
            fputcsv($output, array(
                date('M d, Y', strtotime($r->EXAM_DATE)),
                $applicant_name,
                $r->MUNICIPALITY ?? 'N/A',
                $r->SCHOOL ?? 'N/A',
                $r->COURSE ?? 'N/A',
                $r->TOTAL_SCORE,
                $r->PASSING_SCORE,
                $result_status,
                $r->EXAMINER_NAME ?? 'N/A',
                $r->REMARKS ?? ''
            ));
        }
        
        fclose($output);
        exit();
    } else {
        message("No exam results selected for export!", "error");
        redirect("index.php?view=results");
    }
}

function doBatchPrint() {
    global $mydb;
    
    if (isset($_GET['ids'])) {
        $ids = explode(',', $_GET['ids']);
        $ids = array_map('intval', $ids);
        $ids_str = implode(',', $ids);
        
        // Get selected results
        $sql = "
            SELECT 
                er.*,
                a.LASTNAME, a.FIRSTNAME, a.MIDDLENAME,
                a.MUNICIPALITY, a.SCHOOL, a.COURSE,
                u.FULLNAME as EXAMINER_NAME
            FROM tbl_exam_results er
            INNER JOIN tbl_applicants a ON er.APPLICANTID = a.APPLICANTID
            LEFT JOIN tblusers u ON er.EXAMINER_ID = u.USERID
            WHERE er.EXAM_RESULT_ID IN ($ids_str)
            ORDER BY er.EXAM_DATE DESC
        ";
        
        $mydb->setQuery($sql);
        $mydb->executeQuery();
        $results = $mydb->loadResultList();
        
        // Generate HTML for printing
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Exam Results Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f5f5f5; font-weight: bold; }
                .passed { color: green; font-weight: bold; }
                .failed { color: red; font-weight: bold; }
                .print-date { text-align: right; margin-bottom: 20px; font-size: 12px; }
                @media print { 
                    .no-print { display: none; }
                    body { margin: 0; }
                }
            </style>
        </head>
        <body>
            <div class="print-date">
                Generated on: <?php echo date('F d, Y H:i:s'); ?>
            </div>
            
            <div class="header">
                <h1>ISEASP Exam Results Report</h1>
                <p>Batch Report - <?php echo count($results); ?> Result(s)</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Exam Date</th>
                        <th>Applicant Name</th>
                        <th>Municipality</th>
                        <th>School</th>
                        <th>Course</th>
                        <th>Total Score</th>
                        <th>Passing Score</th>
                        <th>Result</th>
                        <th>Examined By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                        <?php 
                        $result_class = ($r->TOTAL_SCORE >= $r->PASSING_SCORE) ? 'passed' : 'failed';
                        $result_text = ($r->TOTAL_SCORE >= $r->PASSING_SCORE) ? 'PASSED' : 'FAILED';
                        ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($r->EXAM_DATE)); ?></td>
                            <td><?php echo htmlspecialchars($r->LASTNAME . ', ' . $r->FIRSTNAME . ' ' . ($r->MIDDLENAME ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($r->MUNICIPALITY ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($r->SCHOOL ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($r->COURSE ?? 'N/A'); ?></td>
                            <td><strong><?php echo $r->TOTAL_SCORE; ?>%</strong></td>
                            <td><?php echo $r->PASSING_SCORE; ?>%</td>
                            <td class="<?php echo $result_class; ?>"><?php echo $result_text; ?></td>
                            <td><?php echo htmlspecialchars($r->EXAMINER_NAME ?? 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="no-print" style="margin-top: 30px; text-align: center;">
                <button onclick="window.print()">Print Report</button>
                <button onclick="window.close()">Close</button>
            </div>
        </body>
        </html>
        <?php
        exit();
    } else {
        message("No exam results selected for printing!", "error");
        redirect("index.php?view=results");
    }
}

function doBatchAddEdit() {
    global $mydb;

    if (isset($_POST['selected_applicants']) && is_array($_POST['selected_applicants']) && isset($_POST['scores'])) {
        $selected_applicants = $_POST['selected_applicants'];
        $scores = $_POST['scores'];
        $exam_date = date('Y-m-d H:i:s', strtotime($_POST['exam_date']));
        $passing_score = floatval($_POST['passing_score']);
        $remarks = trim($_POST['remarks']);
        $examiner_id = $_SESSION['ADMIN_USERID'];

        $success_count = 0;
        $error_count = 0;

        foreach ($selected_applicants as $applicant_id) {
            $applicant_id = intval($applicant_id);

            $score_provided = isset($scores[$applicant_id]) && !empty($scores[$applicant_id]);

            // Check if result already exists
            $mydb->setQuery("SELECT EXAM_RESULT_ID FROM tbl_exam_results WHERE APPLICANTID = $applicant_id");
            $mydb->executeQuery();
            $existing_result = $mydb->loadSingleResult();

            if ($existing_result) {
                // Update existing result
                $result_id = $existing_result->EXAM_RESULT_ID;

                // Get old exam status
                $mydb->setQuery("SELECT EXAM_STATUS FROM tbl_applicants WHERE APPLICANTID = $applicant_id");
                $mydb->executeQuery();
                $old_status_result = $mydb->loadSingleResult();
                $old_status = $old_status_result->EXAM_STATUS;

                if ($score_provided) {
                    $total_score = floatval($scores[$applicant_id]);

                    // Validate score range
                    if ($total_score < 0 || $total_score > 100) {
                        $error_count++;
                        continue;
                    }

                    // Determine exam status
                    $exam_status = ($total_score >= $passing_score) ? 'Passed' : 'Failed';

                    // Update exam result with new score
                    $sql = "UPDATE tbl_exam_results SET
                            EXAM_DATE = '$exam_date',
                            TOTAL_SCORE = $total_score,
                            PASSING_SCORE = $passing_score,
                            REMARKS = '$remarks'
                            WHERE EXAM_RESULT_ID = $result_id";
                } else {
                    // Update only batch settings, keep existing score
                    $sql = "UPDATE tbl_exam_results SET
                            EXAM_DATE = '$exam_date',
                            PASSING_SCORE = $passing_score,
                            REMARKS = '$remarks'
                            WHERE EXAM_RESULT_ID = $result_id";

                    // Keep existing exam status
                    $exam_status = $old_status;
                }

                $mydb->setQuery($sql);
                $mydb->executeQuery();

                // Update applicant's exam status
                $update_sql = "UPDATE tbl_applicants SET EXAM_STATUS = '$exam_status' WHERE APPLICANTID = $applicant_id";
                $mydb->setQuery($update_sql);
                $mydb->executeQuery();

                // Handle status changes for interviews
                if ($exam_status == 'Passed' && $old_status != 'Passed') {
                    // Check if interview record already exists
                    $check_sql = "SELECT COUNT(*) as count FROM tbl_interview WHERE APPLICANTID = $applicant_id";
                    $mydb->setQuery($check_sql);
                    $mydb->executeQuery();
                    $check_result = $mydb->loadSingleResult();

                    if ($check_result->count == 0) {
                        // Set default interview date
                        $default_interview_date = date('Y-m-d H:i:s', strtotime('+3 days 09:00:00'));
                        $default_interviewer = $_SESSION['ADMIN_USERID'];

                        $interview_sql = "INSERT INTO tbl_interview
                                         (APPLICANTID, INTERVIEW_DATE, INTERVIEW_MODE, RECOMMENDATION, COMMENTS, INTERVIEWER_ID)
                                         VALUES
                                         ($applicant_id, '$default_interview_date', 'Face-to-face', 'For Review', 'Awaiting interview schedule', $default_interviewer)";

                        $mydb->setQuery($interview_sql);
                        $mydb->executeQuery();

                        // Update applicant status
                        $status_sql = "UPDATE tbl_applicants SET STATUS = 'For Interview' WHERE APPLICANTID = $applicant_id";
                        $mydb->setQuery($status_sql);
                        $mydb->executeQuery();
                    }
                } elseif ($exam_status == 'Failed') {
                    // Delete any existing interview for failed applicants
                    $delete_interview_sql = "DELETE FROM tbl_interview WHERE APPLICANTID = $applicant_id";
                    $mydb->setQuery($delete_interview_sql);
                    $mydb->executeQuery();

                    // Reset applicant status to Pending
                    $status_sql = "UPDATE tbl_applicants SET STATUS = 'Pending' WHERE APPLICANTID = $applicant_id";
                    $mydb->setQuery($status_sql);
                    $mydb->executeQuery();
                }

                // Log the update action
                $log_sql = "INSERT INTO tbl_application_log
                            (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS)
                            SELECT
                                $applicant_id,
                                $examiner_id,
                                USERNAME,
                                ROLE,
                                'Exam result updated (batch)',
                                'EXAM',
                                " . ($score_provided ? "CONCAT('Score updated to: ', $total_score, '%')" : "'Batch settings updated'") . "
                            FROM tblusers
                            WHERE USERID = $examiner_id";
                $mydb->setQuery($log_sql);
                $mydb->executeQuery();

            } else {
                // New result - require score
                if (!$score_provided) {
                    continue; // Skip if no score provided for new result
                }

                $total_score = floatval($scores[$applicant_id]);

                // Validate score range
                if ($total_score < 0 || $total_score > 100) {
                    $error_count++;
                    continue;
                }

                // Determine exam status
                $exam_status = ($total_score >= $passing_score) ? 'Passed' : 'Failed';

                // Insert new result
                $sql = "INSERT INTO tbl_exam_results
                        (APPLICANTID, EXAMINER_ID, EXAM_DATE, TOTAL_SCORE, PASSING_SCORE, REMARKS)
                        VALUES ($applicant_id, $examiner_id, '$exam_date', $total_score, $passing_score, '$remarks')";

                $mydb->setQuery($sql);
                $mydb->executeQuery();

                // Update applicant's exam status
                $update_sql = "UPDATE tbl_applicants SET EXAM_STATUS = '$exam_status' WHERE APPLICANTID = $applicant_id";
                $mydb->setQuery($update_sql);
                $mydb->executeQuery();

                // AUTO-CREATE INTERVIEW RECORD IF EXAM IS PASSED
                if ($exam_status == 'Passed') {
                    // Set default interview date
                    $default_interview_date = date('Y-m-d H:i:s', strtotime('+3 days 09:00:00'));
                    $default_interviewer = $_SESSION['ADMIN_USERID'];

                    $interview_sql = "INSERT INTO tbl_interview
                                     (APPLICANTID, INTERVIEW_DATE, INTERVIEW_MODE, RECOMMENDATION, COMMENTS, INTERVIEWER_ID)
                                     VALUES
                                     ($applicant_id, '$default_interview_date', 'Face-to-face', 'For Review', 'Awaiting interview schedule', $default_interviewer)";

                    $mydb->setQuery($interview_sql);
                    $mydb->executeQuery();

                    // Update applicant status to 'For Interview'
                    $status_sql = "UPDATE tbl_applicants SET STATUS = 'For Interview' WHERE APPLICANTID = $applicant_id";
                    $mydb->setQuery($status_sql);
                    $mydb->executeQuery();
                }

                // Log the insert action
                $log_sql = "INSERT INTO tbl_application_log
                            (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS)
                            SELECT
                                $applicant_id,
                                $examiner_id,
                                USERNAME,
                                ROLE,
                                'Exam result recorded (batch)',
                                'EXAM',
                                CONCAT('Score: ', $total_score, '%, ', '$exam_status')
                            FROM tblusers
                            WHERE USERID = $examiner_id";
                $mydb->setQuery($log_sql);
                $mydb->executeQuery();
            }

            $success_count++;
        }

        if ($success_count > 0) {
            message("Successfully processed $success_count exam result(s)!" .
                   ($error_count > 0 ? " $error_count result(s) had errors." : ""), "success");
        } else {
            message("No exam results were processed. Please check your input.", "error");
        }

        redirect("index.php?view=results");
    } else {
        message("No applicants selected or invalid data provided!", "error");
        redirect("index.php?view=batch_add");
    }
}
?>