<?php
require_once("../../include/initialize.php");
if (!isset($_SESSION['ADMIN_USERID'])) {
    redirect(web_root."admin/index.php");
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
    case 'update_status':
        updateStatus();
        break;
    case 'update_exam_status':
        updateExamStatus();
        break;
    case 'batch_print_slips':
        doBatchPrintSlips();
        break;
}

// Cache requirements - Moved BEFORE switch for better organization
$cached_requirements = null;
function getRequirements() {
    global $mydb;
    static $requirements = null;
    
    if ($requirements === null) {
        $mydb->setQuery("SELECT REQUIREMENT_ID, REQUIREMENT_NAME FROM tbl_requirement ORDER BY REQUIREMENT_ID");
        $mydb->executeQuery();
        $requirements = $mydb->loadResultList();
    }
    return $requirements;
}

function doInsert() {
    global $mydb;

    if (isset($_POST['save'])) {

        // REQUIRED FIELDS VALIDATION
        // if (
        //     empty($_POST['FIRSTNAME']) ||
        //     empty($_POST['LASTNAME']) ||
        //     empty($_POST['COURSE']) ||
        //     empty($_POST['SCHOOL']) ||
        //     empty($_POST['YEARLEVEL']) ||
        //     empty($_POST['CONTACT']) ||
        //     empty($_POST['EMAIL']) ||
        //     empty($_POST['DISTRICT']) ||
        //     empty($_POST['MUNICIPALITY']) ||
        //     empty($_POST['ID_NUMBER']) ||
        //     empty($_POST['PERM_STREET']) ||
        //     empty($_POST['PERM_BARANGAY']) ||
        //     empty($_POST['PERM_MUNICIPALITY']) ||
        //     empty($_POST['PERM_PROVINCE'])
        // ) {
        //     message("All required fields must be filled out!", "error");
        //     redirect("index.php?view=add");
        //     exit;
        // }

        $created_by   = (int) $_SESSION['ADMIN_USERID'];

        // REQUIRED FIELDS
        $firstname    = addslashes(trim($_POST['FIRSTNAME']));
        $lastname     = addslashes(trim($_POST['LASTNAME']));
        $course       = addslashes(trim($_POST['COURSE']));
        $school       = addslashes(trim($_POST['SCHOOL']));
        $yearlevel    = addslashes(trim($_POST['YEARLEVEL']));
        $contact      = addslashes(trim($_POST['CONTACT']));

        if (!preg_match('/^09\d{9}$/', $contact)) {
            message("Contact number must be 11 digits and start with 09.", "error");
            redirect("index.php?view=add");
            exit;
        }
        $email        = addslashes(trim($_POST['EMAIL']));
        $district     = addslashes(trim($_POST['DISTRICT']));
        $municipality = addslashes(trim($_POST['MUNICIPALITY']));
        $id_number    = addslashes(trim($_POST['ID_NUMBER']));

        // CHECK DUPLICATE ID NUMBER
        $mydb->setQuery("SELECT APPLICANTID FROM tbl_applicants WHERE ID_NUMBER = '$id_number' LIMIT 1");
        $existing_id = $mydb->loadSingleResult();

        if ($existing_id) {
            message("ID Number already exists! Please use a unique ID number.", "error");
            redirect("index.php?view=add");
            exit;
        }

        // NAME / PERSONAL INFO
        $middlename   = isset($_POST['MIDDLENAME']) ? addslashes(trim($_POST['MIDDLENAME'])) : '';
        $suffix       = isset($_POST['SUFFIX']) ? addslashes(trim($_POST['SUFFIX'])) : '';
        $lrn          = isset($_POST['LRN']) ? addslashes(trim($_POST['LRN'])) : '';

        $birthdate    = !empty($_POST['BIRTHDATE']) ? "'" . addslashes(trim($_POST['BIRTHDATE'])) . "'" : "NULL";
        $birthplace   = !empty($_POST['BIRTHPLACE']) ? "'" . addslashes(trim($_POST['BIRTHPLACE'])) . "'" : "NULL";
        $gender       = !empty($_POST['GENDER']) ? "'" . addslashes(trim($_POST['GENDER'])) . "'" : "NULL";
        $civil_status = isset($_POST['CIVIL_STATUS']) ? addslashes(trim($_POST['CIVIL_STATUS'])) : 'Single';
        $religion     = !empty($_POST['RELIGION']) ? "'" . addslashes(trim($_POST['RELIGION'])) . "'" : "NULL";
        $nationality  = "'Filipino'";

        // NEW ADDRESS FIELDS
        $perm_street       = addslashes(trim($_POST['PERM_STREET']));
        $perm_barangay     = addslashes(trim($_POST['PERM_BARANGAY']));
        $perm_municipality = addslashes(trim($_POST['PERM_MUNICIPALITY']));
        $perm_province     = addslashes(trim($_POST['PERM_PROVINCE']));

        $curr_street       = isset($_POST['CURR_STREET']) ? addslashes(trim($_POST['CURR_STREET'])) : '';
        $curr_barangay     = isset($_POST['CURR_BARANGAY']) ? addslashes(trim($_POST['CURR_BARANGAY'])) : '';
        $curr_municipality = isset($_POST['CURR_MUNICIPALITY']) ? addslashes(trim($_POST['CURR_MUNICIPALITY'])) : '';
        $curr_province     = isset($_POST['CURR_PROVINCE']) ? addslashes(trim($_POST['CURR_PROVINCE'])) : '';

        // COMBINED ADDRESS FIELDS FOR BACKWARD COMPATIBILITY
        $permanent_address_text = $perm_street . ', ' . $perm_barangay . ', ' . $perm_municipality . ', ' . $perm_province;
        $permanent_address      = "'" . addslashes($permanent_address_text) . "'";

        $has_current_address = (
            $curr_street !== '' ||
            $curr_barangay !== '' ||
            $curr_municipality !== '' ||
            $curr_province !== ''
        );

        $current_address_text = $has_current_address
            ? $curr_street . ', ' . $curr_barangay . ', ' . $curr_municipality . ', ' . $curr_province
            : '';

        $current_address = $has_current_address
            ? "'" . addslashes($current_address_text) . "'"
            : "NULL";

        // KEEP LEGACY FIELDS ALIGNED
        $barangay = "'" . $perm_barangay . "'";

        $gpa                      = !empty($_POST['GPA']) ? floatval($_POST['GPA']) : "NULL";
        $facebook_url             = !empty($_POST['FACEBOOK_URL']) ? "'" . addslashes(trim($_POST['FACEBOOK_URL'])) . "'" : "NULL";
        $emergency_contact_name   = !empty($_POST['EMERGENCY_CONTACT_NAME']) ? "'" . addslashes(trim($_POST['EMERGENCY_CONTACT_NAME'])) . "'" : "NULL";
        $emergency_contact_number = !empty($_POST['EMERGENCY_CONTACT_NUMBER']) ? "'" . addslashes(trim($_POST['EMERGENCY_CONTACT_NUMBER'])) . "'" : "NULL";
        $emergency_contact_relation = !empty($_POST['EMERGENCY_CONTACT_RELATION']) ? "'" . addslashes(trim($_POST['EMERGENCY_CONTACT_RELATION'])) . "'" : "NULL";

        $application_type = isset($_POST['APPLICATION_TYPE']) ? addslashes(trim($_POST['APPLICATION_TYPE'])) : 'New Applicant';
        $school_year      = isset($_POST['SCHOOL_YEAR']) ? addslashes(trim($_POST['SCHOOL_YEAR'])) : '2025-2026';
        $semester         = isset($_POST['SEMESTER']) ? addslashes(trim($_POST['SEMESTER'])) : '1st Semester';

        $is_4ps        = isset($_POST['IS_4PS_BENEFICIARY']) ? addslashes(trim($_POST['IS_4PS_BENEFICIARY'])) : 'No';
        $is_indigenous = isset($_POST['IS_INDIGENOUS']) ? addslashes(trim($_POST['IS_INDIGENOUS'])) : 'No';

        $family_income     = !empty($_POST['FAMILY_ANNUAL_INCOME']) ? floatval($_POST['FAMILY_ANNUAL_INCOME']) : "NULL";
        $parent_occupation = !empty($_POST['PARENT_OCCUPATION']) ? "'" . addslashes(trim($_POST['PARENT_OCCUPATION'])) . "'" : "NULL";

        $status = "Pending";

        // OPTIONAL CURRENT ADDRESS SQL VALUES
        $curr_street_sql       = ($curr_street !== '') ? "'$curr_street'" : "NULL";
        $curr_barangay_sql     = ($curr_barangay !== '') ? "'$curr_barangay'" : "NULL";
        $curr_municipality_sql = ($curr_municipality !== '') ? "'$curr_municipality'" : "NULL";
        $curr_province_sql     = ($curr_province !== '') ? "'$curr_province'" : "NULL";

        // INSERT APPLICANT
        $sql = "INSERT INTO tbl_applicants (
                    FIRSTNAME, MIDDLENAME, LASTNAME, SUFFIX, LRN, ID_NUMBER, BIRTHDATE, BIRTHPLACE,
                    GENDER, CIVIL_STATUS, RELIGION, NATIONALITY,
                    PERMANENT_ADDRESS, CURRENT_ADDRESS,
                    PERM_STREET, PERM_BARANGAY, PERM_MUNICIPALITY, PERM_PROVINCE,
                    CURR_STREET, CURR_BARANGAY, CURR_MUNICIPALITY, CURR_PROVINCE,
                    DISTRICT, MUNICIPALITY, BARANGAY,
                    COURSE, SCHOOL, YEARLEVEL, GPA, CONTACT, EMAIL,
                    FACEBOOK_URL, EMERGENCY_CONTACT_NAME, EMERGENCY_CONTACT_NUMBER, EMERGENCY_CONTACT_RELATION,
                    APPLICATION_TYPE, SCHOOL_YEAR, SEMESTER, IS_4PS_BENEFICIARY, IS_INDIGENOUS,
                    FAMILY_ANNUAL_INCOME, PARENT_OCCUPATION, STATUS, CREATED_BY
                ) VALUES (
                    '$firstname', '$middlename', '$lastname', '$suffix', '$lrn', '$id_number', $birthdate, $birthplace,
                    $gender, '$civil_status', $religion, $nationality,
                    $permanent_address, $current_address,
                    '$perm_street', '$perm_barangay', '$perm_municipality', '$perm_province',
                    $curr_street_sql, $curr_barangay_sql, $curr_municipality_sql, $curr_province_sql,
                    '$district', '$municipality', $barangay,
                    '$course', '$school', '$yearlevel', $gpa, '$contact', '$email',
                    $facebook_url, $emergency_contact_name, $emergency_contact_number, $emergency_contact_relation,
                    '$application_type', '$school_year', '$semester', '$is_4ps', '$is_indigenous',
                    $family_income, $parent_occupation, '$status', $created_by
                )";

        $mydb->setQuery($sql);
        $result = $mydb->executeQuery();

        if (!$result) {
            // die("SQL ERROR: " . $mydb->getLastError());
        }

        $applicant_id = $mydb->insert_id();

        if ($applicant_id && $applicant_id > 0) {

            // REQUIREMENTS
            $submitted_requirements = isset($_POST['requirements']) ? array_map('intval', $_POST['requirements']) : [];
            $missing_notes = isset($_POST['missing_notes']) ? addslashes(trim($_POST['missing_notes'])) : '';

            $mydb->setQuery("SELECT REQUIREMENT_ID, REQUIRED FROM tbl_requirement ORDER BY REQUIREMENT_ID");
            $all_requirements = $mydb->loadResultList();

            $required_ids = [];
            foreach ($all_requirements as $req) {
                if ($req->REQUIRED === 'Yes') {
                    $required_ids[] = (int)$req->REQUIREMENT_ID;
                }
            }

            $missing_required   = array_diff($required_ids, $submitted_requirements);
            $requirement_status = empty($missing_required) ? 'Complete' : 'Incomplete';

            $update_sql = "UPDATE tbl_applicants
                           SET REQUIREMENT_STATUS = '$requirement_status',
                               REQUIREMENT_DATE = NOW()
                           WHERE APPLICANTID = $applicant_id";
            $mydb->setQuery($update_sql);
            $mydb->executeQuery();

            // INSERT CHECKLIST ROWS
            foreach ($all_requirements as $req) {
                $req_id = (int)$req->REQUIREMENT_ID;
                $is_submitted = in_array($req_id, $submitted_requirements) ? 1 : 0;
                $is_verified  = 0;
                $remarks_sql  = $is_submitted ? "NULL" : (!empty($missing_notes) ? "'$missing_notes'" : "NULL");

                $check_sql = "INSERT INTO tbl_applicant_requirement_checklist
                                (APPLICANTID, REQUIREMENT_ID, IS_SUBMITTED, IS_VERIFIED, REMARKS)
                              VALUES
                                ($applicant_id, $req_id, $is_submitted, $is_verified, $remarks_sql)";
                $mydb->setQuery($check_sql);
                $mydb->executeQuery();
            }


            if ($requirement_status == 'Complete') {
                message("New applicant added successfully with COMPLETE requirements!", "success");
            } else {
                $missing = count($missing_required);
                message("Applicant added with $missing missing required document(s).", "warning");
            }
        }

        redirect("index.php");
    }
}

function doEdit() {
    global $mydb;

    if (isset($_POST['save'])) {
        if (!isset($_POST['id']) || $_POST['id'] == '') {
            message("Invalid applicant ID!", "error");
            redirect("index.php");
            exit;
        }

        $id = intval($_POST['id']);
        
        // SANITIZE INPUTS
        $firstname   = trim($_POST['FIRSTNAME']);
        $middlename  = trim($_POST['MIDDLENAME']);
        $lastname    = trim($_POST['LASTNAME']);
        $suffix      = isset($_POST['SUFFIX']) ? trim($_POST['SUFFIX']) : '';
        $lrn         = isset($_POST['LRN']) ? trim($_POST['LRN']) : '';
        $birthdate   = !empty($_POST['BIRTHDATE']) ? "'".trim($_POST['BIRTHDATE'])."'" : "NULL";
        $birthplace  = !empty($_POST['BIRTHPLACE']) ? "'".trim($_POST['BIRTHPLACE'])."'" : "NULL";
        $gender      = !empty($_POST['GENDER']) ? "'".trim($_POST['GENDER'])."'" : "NULL";
        $civil_status = isset($_POST['CIVIL_STATUS']) ? trim($_POST['CIVIL_STATUS']) : 'Single';
        $religion    = !empty($_POST['RELIGION']) ? "'".trim($_POST['RELIGION'])."'" : "NULL";
        $permanent_address = !empty($_POST['PERMANENT_ADDRESS']) ? "'".trim($_POST['PERMANENT_ADDRESS'])."'" : "NULL";
        $current_address = !empty($_POST['CURRENT_ADDRESS']) ? "'".trim($_POST['CURRENT_ADDRESS'])."'" : "NULL";
        $district    = trim($_POST['DISTRICT']);
        $municipality = trim($_POST['MUNICIPALITY']);
        $barangay    = !empty($_POST['BARANGAY']) ? "'".trim($_POST['BARANGAY'])."'" : "NULL";
        $course      = trim($_POST['COURSE']);
        $school      = trim($_POST['SCHOOL']);
        $yearlevel   = trim($_POST['YEARLEVEL']);
        $gpa         = !empty($_POST['GPA']) ? floatval($_POST['GPA']) : "NULL";
        $contact     = trim($_POST['CONTACT']);
        $email       = trim($_POST['EMAIL']);
        $facebook_url = !empty($_POST['FACEBOOK_URL']) ? "'".trim($_POST['FACEBOOK_URL'])."'" : "NULL";
        $emergency_contact_name = !empty($_POST['EMERGENCY_CONTACT_NAME']) ? "'".trim($_POST['EMERGENCY_CONTACT_NAME'])."'" : "NULL";
        $emergency_contact_number = !empty($_POST['EMERGENCY_CONTACT_NUMBER']) ? "'".trim($_POST['EMERGENCY_CONTACT_NUMBER'])."'" : "NULL";
        $emergency_contact_relation = !empty($_POST['EMERGENCY_CONTACT_RELATION']) ? "'".trim($_POST['EMERGENCY_CONTACT_RELATION'])."'" : "NULL";
        $application_type = isset($_POST['APPLICATION_TYPE']) ? trim($_POST['APPLICATION_TYPE']) : 'New Applicant';
        $school_year = isset($_POST['SCHOOL_YEAR']) ? trim($_POST['SCHOOL_YEAR']) : '2025-2026';
        $semester = isset($_POST['SEMESTER']) ? trim($_POST['SEMESTER']) : '1st Semester';
        $is_4ps = isset($_POST['IS_4PS_BENEFICIARY']) ? trim($_POST['IS_4PS_BENEFICIARY']) : 'No';
        $is_indigenous = isset($_POST['IS_INDIGENOUS']) ? trim($_POST['IS_INDIGENOUS']) : 'No';
        $family_income = !empty($_POST['FAMILY_ANNUAL_INCOME']) ? trim($_POST['FAMILY_ANNUAL_INCOME']) : "NULL";
        $parent_occupation = !empty($_POST['PARENT_OCCUPATION']) ? "'".trim($_POST['PARENT_OCCUPATION'])."'" : "NULL";
        $status = trim($_POST['STATUS']);

        $sql = "UPDATE tbl_applicants SET
            FIRSTNAME = '$firstname',
            MIDDLENAME = '$middlename',
            LASTNAME = '$lastname',
            SUFFIX = '$suffix',
            LRN = '$lrn',
            BIRTHDATE = $birthdate,
            BIRTHPLACE = $birthplace,
            GENDER = $gender,
            CIVIL_STATUS = '$civil_status',
            RELIGION = $religion,
            PERMANENT_ADDRESS = $permanent_address,
            CURRENT_ADDRESS = $current_address,
            DISTRICT = '$district',
            MUNICIPALITY = '$municipality',
            BARANGAY = $barangay,
            COURSE = '$course',
            SCHOOL = '$school',
            YEARLEVEL = '$yearlevel',
            GPA = $gpa,
            CONTACT = '$contact',
            EMAIL = '$email',
            FACEBOOK_URL = $facebook_url,
            EMERGENCY_CONTACT_NAME = $emergency_contact_name,
            EMERGENCY_CONTACT_NUMBER = $emergency_contact_number,
            EMERGENCY_CONTACT_RELATION = $emergency_contact_relation,
            APPLICATION_TYPE = '$application_type',
            SCHOOL_YEAR = '$school_year',
            SEMESTER = '$semester',
            IS_4PS_BENEFICIARY = '$is_4ps',
            IS_INDIGENOUS = '$is_indigenous',
            FAMILY_ANNUAL_INCOME = $family_income,
            PARENT_OCCUPATION = $parent_occupation,
            STATUS = '$status'
            WHERE APPLICANTID = $id";

        $mydb->setQuery($sql);
        $mydb->executeQuery();

        $adminID = $_SESSION['ADMIN_USERID'];
        $logSQL = "INSERT INTO tbl_application_log 
                    (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS)
                    SELECT 
                        $id, 
                        $adminID, 
                        USERNAME, 
                        ROLE, 
                        'Applicant Updated',
                        'UPDATE',
                        CONCAT('Updated applicant: ', '$firstname', ' ', '$lastname')
                    FROM tblusers 
                    WHERE USERID = $adminID";
        $mydb->setQuery($logSQL);
        $mydb->executeQuery();

        message("Applicant updated successfully!", "success");
        redirect("index.php");
    }
}

function doDelete() {
    global $mydb;

    if (!isset($_SESSION['ADMIN_ROLE']) || $_SESSION['ADMIN_ROLE'] !== 'Super Admin') {
        message("Access denied. Super Admin only.", "error");
        redirect("index.php");
        exit;
    }

    if (isset($_POST['selector'])) {
        $ids = implode(",", array_map('intval', $_POST['selector']));
        $mydb->setQuery("DELETE FROM tbl_applicants WHERE APPLICANTID IN ($ids)");
        $mydb->executeQuery();
        message("Applicant(s) deleted successfully!", "success");
        redirect("index.php");
    } elseif (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $mydb->setQuery("DELETE FROM tbl_applicants WHERE APPLICANTID = $id");
        $mydb->executeQuery();
        message("Applicant deleted successfully!", "success");
        redirect("index.php");
    }
}

function updateStatus() {
    global $mydb;
    
    if (isset($_POST['id']) && isset($_POST['status'])) {
        $id = intval($_POST['id']);
        $status = trim($_POST['status']);
        
        $sql = "UPDATE tbl_applicants SET STATUS = '$status' WHERE APPLICANTID = $id";
        $mydb->setQuery($sql);
        $mydb->executeQuery();
        
        $adminID = $_SESSION['ADMIN_USERID'];
        $logSQL = "INSERT INTO tbl_application_log 
                    (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS)
                    SELECT 
                        $id, 
                        $adminID, 
                        USERNAME, 
                        ROLE, 
                        CONCAT('Status changed to $status'),
                        'UPDATE',
                        CONCAT('Status updated to $status')
                    FROM tblusers 
                    WHERE USERID = $adminID";
        $mydb->setQuery($logSQL);
        $mydb->executeQuery();
        
        echo json_encode(['status' => 'success']);
        exit;
    }
}

function updateExamStatus() {
    global $mydb;
    
    if (isset($_POST['id']) && isset($_POST['exam_status'])) {
        $id = intval($_POST['id']);
        $exam_status = trim($_POST['exam_status']);
        
        $sql = "UPDATE tbl_applicants SET EXAM_STATUS = '$exam_status' WHERE APPLICANTID = $id";
        $mydb->setQuery($sql);
        $mydb->executeQuery();
        
        $adminID = $_SESSION['ADMIN_USERID'];
        $logSQL = "INSERT INTO tbl_application_log 
                    (APPLICANTID, USERID, USERNAME, USER_ROLE, ACTION, ACTION_TYPE, DETAILS)
                    SELECT 
                        $id, 
                        $adminID, 
                        USERNAME, 
                        ROLE, 
                        CONCAT('Exam status changed to $exam_status'),
                        'EXAM',
                        CONCAT('Exam status updated to $exam_status')
                    FROM tblusers 
                    WHERE USERID = $adminID";
        $mydb->setQuery($logSQL);
        $mydb->executeQuery();
        
        echo json_encode(['status' => 'success']);
        exit;
    }
}

function doBatchPrintSlips() {
    global $mydb;

    if (isset($_GET['ids']) && !empty($_GET['ids'])) {
        $ids = explode(',', $_GET['ids']);
        $ids = array_map('intval', $ids);

        if (!empty($ids)) {
            // Generate HTML for batch printing
            $html = '<!DOCTYPE html>
<html>
<head>
    <title>Batch Exam Slips</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .slip { page-break-after: always; border: 1px solid #ccc; padding: 20px; margin-bottom: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .info { margin-bottom: 10px; }
        .info strong { display: inline-block; width: 150px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()">Print All Slips</button>
    </div>';

            foreach ($ids as $applicant_id) {
                $mydb->setQuery("SELECT * FROM tbl_applicants WHERE APPLICANTID = $applicant_id");
                $applicant = $mydb->loadSingleResult();

                if ($applicant) {
                    $html .= '
    <div class="slip">
        <div class="header">
            <h2>EXAMINATION SLIP</h2>
            <h3>Provincial Government of Ilocos Sur</h3>
        </div>

        <div class="info">
            <strong>Slip Number:</strong> ' . htmlspecialchars($applicant->EXAM_SLIP_NUMBER ?? 'N/A') . '<br>
            <strong>Name:</strong> ' . htmlspecialchars($applicant->LASTNAME . ', ' . $applicant->FIRSTNAME . ' ' . ($applicant->MIDDLENAME ?? '')) . '<br>
            <strong>Municipality:</strong> ' . htmlspecialchars($applicant->MUNICIPALITY ?? 'N/A') . '<br>
            <strong>School:</strong> ' . htmlspecialchars($applicant->SCHOOL ?? 'N/A') . '<br>
            <strong>Course:</strong> ' . htmlspecialchars($applicant->COURSE ?? 'N/A') . '<br>
            <strong>Exam Date:</strong> ' . ($applicant->EXAM_DATE ? date('F d, Y', strtotime($applicant->EXAM_DATE)) : 'N/A') . '<br>
            <strong>Exam Time:</strong> ' . ($applicant->EXAM_TIME ? date('h:i A', strtotime($applicant->EXAM_TIME)) : 'N/A') . '<br>
            <strong>Venue:</strong> ' . htmlspecialchars($applicant->EXAM_VENUE ?? 'N/A') . '<br>
        </div>

        <div style="margin-top: 30px;">
            <h4>IMPORTANT REMINDERS:</h4>
            <ol>
                <li>Present this slip upon entry to the examination room.</li>
                <li>Bring the following:
                    <ul>
                        <li>Valid ID or Birth Certificate</li>
                        <li>Ballpen and Pencil</li>
                    </ul>
                </li>
                <li>Wear appropriate attire: White shirt and plain pants.</li>
            </ol>
        </div>
    </div>';
                }
            }

            $html .= '</body></html>';

            echo $html;
            exit;
        }
    }

    // If no valid IDs, redirect back
    redirect("index.php?view=results");
}
?>