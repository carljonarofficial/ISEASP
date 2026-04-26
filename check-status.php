<?php
require_once("include/initialize.php");

header('Content-Type: application/json');

if (!isset($_POST['lrn']) || empty($_POST['lrn'])) {
    echo json_encode(['status' => 'error', 'message' => 'LRN is required']);
    exit;
}

$lrn = trim($_POST['lrn']);

// Validate LRN format (12 digits)
// if (!preg_match('/^\d{12}$/', $lrn)) {
//     echo json_encode(['status' => 'error', 'message' => 'Invalid LRN format. Please enter a valid 12-digit LRN.']);
//     exit;
// }

global $mydb;

// Query to get applicant status by LRN
$sql = "SELECT 
            a.APPLICANTID,
            a.FIRSTNAME,
            a.MIDDLENAME,
            a.LASTNAME,
            a.LRN,
            a.SCHOOL,
            a.COURSE,
            a.YEARLEVEL,
            a.STATUS,
            a.EXAM_STATUS,
            a.REQUIREMENT_STATUS,
            a.APPLICATION_TYPE,
            (SELECT SEMESTER FROM tbl_renewal_applications WHERE APPLICANTID = a.APPLICANTID ORDER BY CREATED_AT DESC LIMIT 1) as RENEWED_SEMESTER,
            (SELECT SCHOOL_YEAR FROM tbl_renewal_applications WHERE APPLICANTID = a.APPLICANTID ORDER BY CREATED_AT DESC LIMIT 1) as RENEWED_SCHOOL_YEAR,
            (SELECT STATUS FROM tbl_renewal_applications WHERE APPLICANTID = a.APPLICANTID ORDER BY CREATED_AT DESC LIMIT 1) as RENEWAL_STATUS,
            a.DATECREATED,
            (SELECT COUNT(*) FROM tbl_applicant_requirement_checklist 
             WHERE APPLICANTID = a.APPLICANTID AND IS_VERIFIED = 1) as VERIFIED_REQ,
            (SELECT COUNT(*) FROM tbl_requirement WHERE REQUIRED = 'Yes') as TOTAL_REQ
        FROM tbl_applicants a
        LEFT JOIN tbl_renewal_applications ra ON a.APPLICANTID = ra.APPLICANTID
        WHERE a.LRN = '$lrn'";

$mydb->setQuery($sql);
$mydb->executeQuery();
$applicant = $mydb->loadSingleResult();

if (!$applicant) {
    echo json_encode(['status' => 'error', 'message' => 'No applicant found with this LRN']);
    exit;
}

// Build full name properly
$fullname = '';
if ($applicant->LASTNAME) {
    $fullname = $applicant->LASTNAME;
    if ($applicant->FIRSTNAME) {
        $fullname = $applicant->FIRSTNAME . ' ' . $fullname;
        if ($applicant->MIDDLENAME) {
            $fullname = $applicant->FIRSTNAME . ' ' . $applicant->MIDDLENAME . ' ' . $applicant->LASTNAME;
        }
    }
} else {
    $fullname = 'Applicant';
}

// Prepare response data
$response = [
    'status' => 'success',
    'data' => [
        'id' => $applicant->APPLICANTID,
        'fullname' => $fullname,
        'firstname' => $applicant->FIRSTNAME ?? '',
        'middlename' => $applicant->MIDDLENAME ?? '',
        'lastname' => $applicant->LASTNAME ?? '',
        'lrn' => $applicant->LRN,
        'school' => $applicant->SCHOOL ?? 'N/A',
        'course' => $applicant->COURSE ?? 'N/A',
        'year_level' => $applicant->YEARLEVEL ?? 'N/A',
        'status' => $applicant->STATUS ?? 'Pending',
        'exam_status' => $applicant->EXAM_STATUS ?? 'Pending',
        'requirement_status' => $applicant->REQUIREMENT_STATUS ?? 'Pending',
        'application_type' => $applicant->APPLICATION_TYPE ?? 'New Applicant',
        'date_applied' => $applicant->DATECREATED ? date('F d, Y', strtotime($applicant->DATECREATED)) : 'N/A',
        'semester' => $applicant->SEMESTER ?? 'N/A',
        'school_year' => $applicant->SCHOOL_YEAR ?? 'N/A',
        'requirements_progress' => ($applicant->TOTAL_REQ > 0) ? round(($applicant->VERIFIED_REQ / $applicant->TOTAL_REQ) * 100) : 0,
        'verified_requirements' => $applicant->VERIFIED_REQ ?? 0,
        'total_requirements' => $applicant->TOTAL_REQ ?? 0,
        'renewal_semester' => $applicant->RENEWED_SEMESTER ?? 'N/A',
        'renewal_school_year' => $applicant->RENEWED_SCHOOL_YEAR ?? 'N/A',
        'renewal_status' => $applicant->RENEWAL_STATUS ?? 'N/A'
    ]
];

// Add status-specific messages
switch($applicant->STATUS) {
    case 'Scholar':
        $response['data']['message'] = 'Congratulations! You are an active scholar of ISEASP. Please maintain your grades and comply with program requirements.';
        break;
    case 'Qualified':
        $response['data']['message'] = 'You are qualified for the scholarship! Please wait for further instructions from the ISEASP office.';
        break;
    case 'For Evaluation':
        $response['data']['message'] = 'Your application is currently under evaluation. Please check back later for updates.';
        break;
    case 'For Interview':
        $response['data']['message'] = 'You are scheduled for an interview. Please check your email for the schedule and venue details.';
        break;
    case 'For Exam':
        $response['data']['message'] = 'You are scheduled for an entrance exam. Please check the FB Page for the schedule and venue details.';
        break;
    case 'Pending':
        if ($applicant->REQUIREMENT_STATUS == 'Incomplete') {
            $response['data']['message'] = 'Your application is pending. Please submit all required documents to complete your application.';
        } else {
            $response['data']['message'] = 'Your application is being processed. Please check back later for updates.';
        }
        break;
    case 'Rejected':
        $response['data']['message'] = 'We regret to inform you that your application was not approved for this school year. You may reapply next year.';
        break;
    case 'Graduated':
        $response['data']['message'] = 'Congratulations on your graduation! Thank you for being part of ISEASP.';
        break;
    default:
        $response['data']['message'] = 'Your application is currently being reviewed. Please check back later.';
}

echo json_encode($response);
?>